<?php
require "../config_session.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo boomError('error');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo boomError('error');
    exit();
}

if (isset(
    $_POST['admin_set_room_id'],
    $_POST['admin_set_room_name'],
    $_POST['admin_set_room_description'],
    $_POST['admin_set_room_password'],
    $_POST['admin_set_room_access']
)) {
    if (!canEditRoom()) {
        echo boomError('cannot_action');
        die;
    }

    $room_id = escape($_POST['admin_set_room_id']);
    $name = escape($_POST['admin_set_room_name']);
    $description = escape($_POST['admin_set_room_description']);
    $password = escape($_POST['admin_set_room_password']);
    $access = (int) escape($_POST['admin_set_room_access']);
    $player_id = 0;

    if (strlen($name) > 100) {
        echo boomError('room_name');
        die;
    }
    if (strlen($description) > 350) {
        echo boomError('room_description');
        die;
    }

    $get = $mysqli->query("SELECT * FROM boom_rooms WHERE room_id = '{$room_id}'");
    if ($get->num_rows === 0) {
        echo boomError('no_result');
        die;
    }

    $room = $get->fetch_assoc();
    if (roomExist($name, $room_id)) {
        echo boomError('room_exist');
        die;
    }

    if ($room_id == 1) {
        $password = '';
    }

    if (isset($_POST['admin_set_room_player'])) {
        $player = (int) escape($_POST['admin_set_room_player']);
        if ($player !== 0 && $player !== $room['room_player_id']) {
            $cp = $mysqli->query("SELECT id FROM boom_radio_stream WHERE id = '{$player}'");
            $player_id = $cp->num_rows > 0 ? $cp->fetch_assoc()['id'] : $room['room_player_id'];
        } else {
            $player_id = $room['room_player_id'];
        }
    }

    $mysqli->query("
        UPDATE boom_rooms
        SET room_name         = '{$name}',
            description       = '{$description}',
            password          = '{$password}',
            access            = '{$access}',
            room_player_id    = '{$player_id}'
        WHERE room_id = '{$room_id}'
    ");

    redisUpdateRoom($room_id);
    echo boomSuccess('updated');
    die;
}

if (isset($_POST['room'], $_POST['join_room'])) {
    $target = (int) escape($_POST['room']);
    $muted = 0;
    $role = 0;
    $data['user_role'] = 0;

    $sql = "
        SELECT
            r.room_id,
            r.room_name,
            r.room_icon,
            r.topic          AS room_topic,
            r.room_action,
            r.access,
            r.password,
            (SELECT COUNT(id) FROM boom_room_action WHERE action_room=? AND action_user=? AND action_muted=1) AS is_muted,
            (SELECT COUNT(id) FROM boom_room_action WHERE action_room=? AND action_user=? AND action_blocked=1) AS is_blocked,
            (SELECT room_rank FROM boom_room_staff WHERE room_id=? AND room_staff=?) AS room_status
        FROM boom_rooms r
        WHERE r.room_id=?
    ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo boomError('error');
        die;
    }

    $userId = $data['user_id'];
    $stmt->bind_param("iiiiiii", $target, $userId, $target, $userId, $target, $userId, $target);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo boomError('no_result');
        die;
    }

    $room = $res->fetch_assoc();
    if ((int) $room['is_muted'] > 0) {
        $muted = 1;
    }
    if ((int) $room['is_blocked'] === 1) {
        echo boomError('room_block');
        die;
    }
    if (mustVerify()) {
        echo boomError('something_wrong');
        die;
    }

    if (!is_null($room['room_status'])) {
        $role = (int) $room['room_status'];
        $data['user_role'] = $role;
    }

    if (!boomAllow($room['access'])) {
        echo boomError('access_requirement');
        die;
    }

    if ($room['password'] !== '') {
        if (!isset($_POST['pass'])) {
            echo boomCode(4, array('message' => boomError('empty_field')));
            die;
        }
        $pass = escape($_POST['pass']);
        if ($pass !== $room['password'] && !canRoomPassword()) {
            echo boomError('wrong_pass');
            die;
        }
    }

    $now = time();
    $mysqli->query("
        UPDATE boom_users
        SET user_move     = {$now},
            user_roomid   = {$room['room_id']},
            last_action   = {$now},
            user_role     = {$role},
            room_mute     = {$muted}
        WHERE user_id     = {$data['user_id']}
    ");

    $mysqli->query("UPDATE boom_rooms SET room_action = {$now} WHERE room_id = {$room['room_id']}");

    $logs = getChatHistory($room['room_id']);
    $room_icon = $room['room_icon'] === 'default_room.png' 
        ? 'default_images/rooms/default_room.png' 
        : 'room_icon/' . $room['room_icon'];

    $payload = [
        'room_id'       => $room['room_id'],
        'room_name'     => $room['room_name'],
        'room_icon'     => $room_icon,
        'room_action'   => $room['room_action'],
        'room_role'     => $role,
        'room_logs'     => $logs
    ];

    if (empty(trim($room['room_topic']))) {
        $payload['room_topic'] = '';
    } else {
        $parsed_topic = str_replace('%user%', $data['user_name'], $room['room_topic']);
        $payload['room_topic'] = [
            'content' => $parsed_topic,
            'title'   => $lang['topic'],
            'icon'    => 'default_images/special/topic.svg'
        ];
    }

    redisUpdateUser($data['user_id']);
    redisUpdateRoom($room['room_id']);
    echo boomCode(10, array('data' => $payload, 'message' => boomSuccess('action_complete')));
    die;
}

if (isset($_POST['room'], $_POST['join_room_pass'], $_POST['pass'])) {
    $roomId = (int) escape($_POST['room']);
    $pass   = escape($_POST['pass']);
    $userId = $data['user_id'];
    $muted  = 0;
    $role   = 0;
    $data['user_role'] = 0;

    $sql = "
      SELECT
        r.room_id,
        r.room_name,
        r.room_icon,
        r.topic      AS room_topic,
        r.room_action,
        r.access,
        r.password,
        (SELECT COUNT(id) FROM boom_room_action WHERE action_room=? AND action_user=? AND action_muted=1)   AS is_muted,
        (SELECT COUNT(id) FROM boom_room_action WHERE action_room=? AND action_user=? AND action_blocked=1) AS is_blocked,
        (SELECT room_rank  FROM boom_room_staff  WHERE room_id=?     AND room_staff=?)       AS room_status
      FROM boom_rooms r
      WHERE r.room_id=?
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo boomError('error');
        die;
    }
    $stmt->bind_param('iiiiiii',
        $roomId, $userId,
        $roomId, $userId,
        $roomId, $userId,
        $roomId
    );
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo boomError('no_result');
        die;
    }
    $room = $res->fetch_assoc();

    if ((int)$room['is_muted'] > 0)   $muted = 1;
    if ((int)$room['is_blocked']===1) {
        echo boomError('room_block');
        die;
    }
    if (mustVerify()) {
        echo boomError('something_wrong');
        die;
    }

    if (!is_null($room['room_status'])) {
        $role = (int)$room['room_status'];
        $data['user_role'] = $role;
    }

    if (!boomAllow($room['access'])) {
        echo boomError('access_requirement');
        die;
    }

    if ($room['password'] === '' || ($pass === $room['password'] && canRoomPassword())) {
    } else {
        echo boomError('wrong_pass');
        die;
    }

    $now = time();
    $mysqli->query("
      UPDATE boom_users SET
        user_move   = {$now},
        user_roomid = {$room['room_id']},
        last_action = {$now},
        user_role   = {$role},
        room_mute   = {$muted}
      WHERE user_id = {$userId}
    ");
    $mysqli->query("UPDATE boom_rooms SET room_action = {$now} WHERE room_id = {$room['room_id']}");

    $logs     = getChatHistory($room['room_id']);
    $room_icon= $room['room_icon']==='default_room.png'
               ? 'default_images/rooms/default_room.png'
               : '/room_icon/'.$room['room_icon'];

    $payload = [
      'room_id'     => $room['room_id'],
      'room_name'   => $room['room_name'],
      'room_icon'   => $room_icon,
      'room_action' => $room['room_action'],
      'room_role'   => $role,
      'room_logs'   => $logs
    ];
    if (trim($room['room_topic'])!=='') {
    $parsed_topic = str_replace('%user%', $data['user_name'], $room['room_topic']);
      $payload['room_topic'] = [
        'content'=> $parsed_topic,
        'title'  => $lang['topic'],
        'icon'   => 'default_images/special/topic.svg'
      ];
    } else {
      $payload['room_topic'] = '';
    }

    redisUpdateUser($data['user_id']);
    redisUpdateRoom($room['room_id']);
    echo boomCode(10, array('data'=>$payload, 'message' => boomSuccess('action_complete')));
    die;
}

if (isset($_POST['leave_room'])) {
    echo boomSuccess('action_complete');
    die;
}

if (isset($_POST['target'], $_POST['room_staff_rank'])) {
    if (!canEditRoom()) {
        echo boomError('cannot_action');
        die;
    }

    $target = escape($_POST['target']);
    $rank = escape($_POST['room_staff_rank']);
    $user = userRoomDetails($target);

    if (empty($target)) {
        echo boomError('no_result');
        die;
    }
    
    if (!canRoomAction($user, 6)) {
        echo boomError('cannot_user');
        die;
    }

    if ($rank > 0) {
        if (checkMod($user['user_id'])) {
            $mysqli->query("INSERT INTO boom_room_staff(room_id, room_staff, room_rank) VALUES('{$data['user_roomid']}','{$user['user_id']}','{$rank}')");
        } else {
            $mysqli->query("UPDATE boom_room_staff SET room_rank='{$rank}' WHERE room_id='{$data['user_roomid']}' AND room_staff='{$user['user_id']}'");
        }
        $mysqli->query("DELETE FROM boom_room_action WHERE action_user='{$user['user_id']}' AND action_room='{$data['user_roomid']}'");
        $mysqli->query("UPDATE boom_users SET user_role='{$rank}', room_mute=0 WHERE user_id='{$user['user_id']}' AND user_roomid='{$data['user_roomid']}'");
    } else {
        $mysqli->query("DELETE FROM boom_room_staff WHERE room_staff='{$user['user_id']}' AND room_id='{$data['user_roomid']}'");
        $mysqli->query("UPDATE boom_users SET user_role=0 WHERE user_id='{$user['user_id']}' AND user_roomid='{$data['user_roomid']}'");
    }

    redisUpdateUser($user['user_id']);
    boomConsole('change_room_rank', ['target' => $user['user_id'], 'rank' => $rank]);
    echo boomSuccess('action_complete');
    die;
}

if (
    isset(
        $_POST['admin_add_room'],
        $_POST['admin_set_name'],
        $_POST['admin_set_pass'],
        $_POST['admin_set_type'],
        $_POST['admin_set_description']
    ) && 
    boomAllow(100) && 
    canRoom()
) {
    $n = escape($_POST['admin_set_name']);
    $p = escape($_POST['admin_set_pass']);
    $t = escape($_POST['admin_set_type']);
    $d = escape($_POST['admin_set_description']);

    if (!validRoomName($n) || strlen($d) > 25 || mb_strlen($p) > 20) {
        echo boomError('invalid_data');
        die;
    }

    global $setting;
    $max = $mysqli->query("SELECT 1 FROM boom_rooms WHERE room_creator='{$data['user_id']}'");
    if ($max->num_rows >= $setting['max_room'] && !boomAllow(8)) {
        echo boomError('max_room');
        die;
    }

    if ($mysqli->query("SELECT 1 FROM boom_rooms WHERE room_name='{$n}'")->num_rows) {
        echo boomError('room_exist');
        die;
    }

    $sf = boomAllow(100) ? 1 : 0;
    $now = time();
    $mysqli->query("
        INSERT INTO boom_rooms(
            room_name,
            access,
            description,
            password,
            room_system,
            room_creator,
            room_action
        ) VALUES(
            '{$n}',
            '{$t}',
            '{$d}',
            '{$p}',
            '{$sf}',
            '{$data['user_id']}',
            '{$now}'
        )
    ");

    $id = $mysqli->insert_id;
    $mysqli->query("DELETE FROM boom_room_staff WHERE room_id='{$id}'");

    boomConsole('create_room', ['room' => $id]);
    redisUpdateRoom($id);
    echo boomSuccess('action_complete');
    die;
}

if (isset($_POST['pin_room'])) {
    $rid = escape($_POST['pin_room']);
    $mysqli->query("UPDATE boom_rooms SET pinned=IF(pinned=1,0,1) WHERE room_id='{$rid}'");
    redisUpdateRoom($rid);
    echo boomSuccess('action_complete');
    die;
}

if (isset($_POST['delete_room'])) {
    $rid = escape($_POST['delete_room']);
    $mysqli->query("DELETE FROM boom_rooms WHERE room_id='{$rid}'");
    echo boomSuccess('action_complete');
    die;
}

echo boomError('error');
die;
?>