<?php
require '../config.php';

if (!isset($_POST) || !boomLogged()) {
    die();
}

if (!useCall()) {
    die();
}

$add_group_call = isset($_POST['add_group_call']) ? escape($_POST['add_group_call'], 0) : 0;
$save_group_call = isset($_POST['save_group_call']) ? escape($_POST['save_group_call'], 0) : 0;
$join_group_call = isset($_POST['join_group_call']) ? escape($_POST['join_group_call'], 0) : 0;
$open_group_call = isset($_POST['open_group_call']) ? escape($_POST['open_group_call'], 0) : 0;
$upgrade_group_call = isset($_POST['upgrade_group_call']) ? escape($_POST['upgrade_group_call'], 0) : 0;
$call_ban = isset($_POST['call_ban']) ? escape($_POST['call_ban'], 0) : 0;
$call_group_user = isset($_POST['call_group_user']) ? $_POST['call_group_user'] : 0;

$call_name = isset($_POST['call_name']) ? escape($_POST['call_name']) : '';
$call_password = isset($_POST['call_password']) ? escape($_POST['call_password']) : '';
$call_access = isset($_POST['call_access']) ? escape($_POST['call_access'], 0) : 0;
$call_type = isset($_POST['call_type']) ? escape($_POST['call_type'], 1) : 0;
$call_key = isset($_POST['call_key']) ? escape($_POST['call_key']) : '';
$call_id = isset($_POST['call_id']) ? escape($_POST['call_id'], 0) : 0;

if ($add_group_call > 0 && $call_type > 0) {
    if (!canGroupCallType($call_type)) {
        echo boomError($lang['cannot_call']);
        die();
    }
    if (!callBalance($call_type)) {
        echo boomError($lang['call_fund']);
        die();
    }

    $creator = $data['user_id'];
    $room = mt_rand(100000, 999999);
    $time = time();

    $insert = $mysqli->query("INSERT INTO boom_group_call 
        (call_name, call_creator, call_type, call_time, call_room, call_password, call_access, call_active, call_date) 
        VALUES ('$call_name', '$creator', '$call_type', '$time', '$room', '$call_password', '$call_access', '$time', '$time')");

    if ($insert) {
        $call_id = $mysqli->insert_id;
        setUserGroupCall(array('call_id' => $call_id, 'call_password' => $call_password));
        $mysqli->query("INSERT INTO boom_call_user (croom, cuser, cdate) VALUES ('$room', '$creator', '$time')");
        echo boomCode(1, array('room' => $call_id, 'rank' => $call_access));
    } else {
        echo boomError($lang['call_error']);
    }
} elseif ($save_group_call > 0 && $call_id > 0) {
    $call = groupCallDetails($call_id);
    if (empty($call)) {
        echo boomError($lang['invalid_data']);
        die();
    }
    if (!canEditCall($call)) {
        echo boomError($lang['access_denied']);
        die();
    }

    $mysqli->query("UPDATE boom_group_call SET call_name = '$call_name', call_password = '$call_password', call_access = '$call_access' WHERE call_id = '$call_id'");
    echo boomCode(1);
} elseif ($join_group_call > 0) {
    $call = groupCallDetails($join_group_call);
    if (empty($call) || !groupCallActive($call) || expiredCall($call)) {
        echo boomError($lang['call_expired']);
        die();
    }

    if (!canGroupCallType($call['call_type'])) {
        echo boomError($lang['cannot_call']);
        die();
    }
    if (!callAccess($call)) {
        echo boomError($lang['access_denied']);
        die();
    }
    if ($call['call_password'] != '' && $call['call_password'] != $call_key) {
        echo boomError($lang['invalid_password']);
        die();
    }
    if (callBanned($call['call_room'])) {
        echo boomError($lang['call_banned']);
        die();
    }

    $time = time();
    $mysqli->query("INSERT INTO boom_call_user (croom, cuser, cdate) VALUES ('{$call['call_room']}', '{$data['user_id']}', '$time') ON DUPLICATE KEY UPDATE cdate = '$time'");
    setUserGroupCall($call);
    echo boomCode(1, groupCallData($call['call_type']));
} elseif ($open_group_call > 0) {
    $call = groupCallDetails($open_group_call);
    if (empty($call) || !groupCallActive($call) || expiredCall($call)) {
        echo boomError($lang['call_ended']);
        die();
    }
    if (!validGroupCall($call)) {
        echo boomError($lang['access_denied']);
        die();
    }

    $room = $call['call_room'];
    $uid = $data['user_id'];

    if (agoraCall()) {
        require '../agora/src/RtcTokenBuilder.php';

        $appID = $setting['call_appid'];
        $appCertificate = $setting['call_secret'];
        $role = RtcTokenBuilder::RolePublisher;
        $expireTimeInSeconds = $setting['call_max'] * 60;
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $room, $uid, $role, $privilegeExpiredTs);

        $response = array(
            'appid' => $appID,
            'approom' => $room,
            'appowner' => $call['call_creator'],
            'apptoken' => $token,
            'appurl' => ''
        );
    } elseif (livekitCall()) {
        require '../livekit/autoload.php';

        try {
            $roomName = $room;
            $ttl = $setting['call_max'] * 60;

            $tokenOptions = new \Agence104\LiveKit\AccessTokenOptions([
                'identity' => (string)$uid,
                'name' => $data['user_name'],
                'ttl' => $ttl
            ]);

            $videoGrant = new \Agence104\LiveKit\VideoGrant();
            $videoGrant->setRoomJoin(true);
            $videoGrant->setRoomName($roomName);
            $videoGrant->setCanPublish(true);
            $videoGrant->setCanSubscribe(true);

            $token = (new \Agence104\LiveKit\AccessToken($setting['live_appid'], $setting['live_secret']))
                ->init($tokenOptions)
                ->setGrant($videoGrant)
                ->toJwt();

            $response = array(
                'appid' => $setting['live_appid'],
                'approom' => $roomName,
                'appowner' => $call['call_creator'],
                'apptoken' => $token,
                'appurl' => $setting['live_url']
            );
        } catch (Exception $e) {
            error_log("LiveKit token error: " . $e->getMessage());
            echo boomError($lang['call_error']);
            die();
        }
    }

    echo boomCode(1, array('data' => $response));
} elseif ($upgrade_group_call > 0) {
    $call = groupCallDetails($upgrade_group_call);
    if (empty($call)) {
        echo boomError($lang['call_error']);
        die();
    }
    if (!groupCallActive($call) || expiredCall($call)) {
        $mysqli->query("DELETE FROM boom_group_call WHERE call_id = '{$call['call_id']}'");
        $mysqli->query("DELETE FROM boom_call_user WHERE croom = '{$call['call_room']}'");
        echo boomCode(2);
    } else {
        $mysqli->query("UPDATE boom_group_call SET call_active = '" . time() . "' WHERE call_id = '{$call['call_id']}'");
        echo boomCode(0);
    }
} elseif ($call_ban > 0 && $call_id > 0) {
    $call = groupCallDetails($call_id);
    if (empty($call)) {
        echo boomError($lang['call_error']);
        die();
    }
    if (!canEditCall($call)) {
        echo boomError($lang['access_denied']);
        die();
    }

    $mysqli->query("INSERT INTO boom_call_action (call_room, hunter, target, action_time) VALUES ('{$call['call_room']}', '{$data['user_id']}', '$call_ban', '" . time() . "')");
    $mysqli->query("DELETE FROM boom_call_user WHERE croom = '{$call['call_room']}' AND cuser = '$call_ban'");
    echo boomCode(1);
} elseif ($call_group_user && is_array($call_group_user)) {
    $users = array();
    foreach ($call_group_user as $uid) {
        $uid = escape($uid, 0);
        if ($uid > 0) {
            $user = userDetails($uid);
            if (!empty($user)) {
                $users[] = array(
                    'user_id' => $user['user_id'],
                    'user_name' => $user['user_name'],
                    'avatar' => myAvatar($user['user_tumb'])
                );
            }
        }
    }
    if (count($users) > 0) {
        echo boomCode(1, array('data' => $users));
    } else {
        echo boomCode(0);
    }
}
?>