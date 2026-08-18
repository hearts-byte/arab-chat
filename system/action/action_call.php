<?php
require '../config.php';

if (!isset($_POST) || !boomLogged()) {
    die();
}

if (!useCall()) {
    die();
}

$call_action = isset($_POST['init_call']) ? escape($_POST['init_call'], 0) : 0;
$call_cancel = isset($_POST['cancel_call']) ? escape($_POST['cancel_call'], 0) : 0;
$call_accept = isset($_POST['accept_call']) ? escape($_POST['accept_call'], 0) : 0;
$call_decline = isset($_POST['decline_call']) ? escape($_POST['decline_call'], 0) : 0;
$call_update = isset($_POST['update_call']) ? escape($_POST['update_call'], 0) : 0;
$incoming_update = isset($_POST['update_incoming_call']) ? escape($_POST['update_incoming_call'], 0) : 0;
$call_upgrade = isset($_POST['upgrade_call']) ? escape($_POST['upgrade_call'], 0) : 0;
$call_user = isset($_POST['call_user']) ? escape($_POST['call_user'], 0) : 0;
$call_open = isset($_POST['open_call']) ? escape($_POST['open_call'], 0) : 0;
$call_type = isset($_POST['call_type']) ? escape($_POST['call_type'], 1) : 0;
$call_check = isset($_POST['check_call']) ? escape($_POST['check_call'], 0) : 0;

if ($call_action > 0 && $call_type > 0) {
    $target = $call_action;
    $caller = $data['user_id'];

    $target_user = userDetails($target);
    if (empty($target_user)) {
        echo boomCode(2, $lang['invalid_data']);
        die();
    }

    if (!canCallUser($target_user)) {
        echo boomCode(2, $lang['cannot_call']);
        die();
    }
    if (!canInitCall($call_type)) {
        echo boomCode(2, $lang['cannot_call']);
        die();
    }
    if (!callBalance($call_type)) {
        echo boomCode(2, $lang['call_fund']);
        die();
    }

    $room = mt_rand(100000, 999999);
    $current_time = time();

    $mysqli->query("DELETE FROM boom_call WHERE (call_hunter = '$caller' OR call_target = '$caller') AND call_status < 2");
    $mysqli->query("DELETE FROM boom_call WHERE (call_hunter = '$target' OR call_target = '$target') AND call_status < 2");

    $insert = $mysqli->query("INSERT INTO boom_call (call_hunter, call_target, call_type, call_time, call_room) VALUES ('$caller', '$target', '$call_type', '$current_time', '$room')");

    if ($insert) {
        $call_id = $mysqli->insert_id;

        $mysqli->query("UPDATE boom_users SET ucall = '$call_id' WHERE user_id = '$caller'");
        $mysqli->query("UPDATE boom_users SET ucall = '$call_id' WHERE user_id = '$target'");
        redisUpdateUser($caller);
        redisUpdateUser($target);

        $call_data = callData($call_type);
        $call_data['call_id'] = $call_id;

        setUserCall(array('call_id' => $call_id));
        echo boomCode(1, $call_data);
    } else {
        echo boomCode(2, $lang['call_error']);
    }
} elseif ($call_cancel > 0) {
    $call = callDetails($call_cancel);
    if (!empty($call) && mySelf($call['call_hunter'])) {
        $hunter = $call['call_hunter'];
        $target = $call['call_target'];

        $mysqli->query("DELETE FROM boom_call WHERE call_id = '{$call['call_id']}'");
        $mysqli->query("DELETE FROM boom_call_action WHERE call_room = '{$call['call_room']}'");
        $mysqli->query("UPDATE boom_users SET ucall = 0 WHERE user_id = '$hunter' OR user_id = '$target'");
        redisUpdateUser($hunter);
        redisUpdateUser($target);
    }
    echo boomCode(1);
} elseif ($call_accept > 0) {
    $call = callDetails($call_accept);
    if (!empty($call) && mySelf($call['call_target']) && callActive($call)) {
        $current_time = time();
        $mysqli->query("UPDATE boom_call SET call_status = 1, call_active = '$current_time' WHERE call_id = '{$call['call_id']}'");

        setUserCall($call);
        echo boomCode(1, callData($call['call_type']));
    } else {
        echo boomCode(2, $lang['call_ended']);
    }
} elseif ($call_decline > 0) {
    $call = callDetails($call_decline);
    if (!empty($call) && mySelf($call['call_target'])) {
        $hunter = $call['call_hunter'];
        $target = $call['call_target'];

        $mysqli->query("DELETE FROM boom_call WHERE call_id = '{$call['call_id']}'");
        $mysqli->query("DELETE FROM boom_call_action WHERE call_room = '{$call['call_room']}'");
        $mysqli->query("UPDATE boom_users SET ucall = 0 WHERE user_id = '$hunter' OR user_id = '$target'");
        redisUpdateUser($hunter);
        redisUpdateUser($target);
    }
    echo boomCode(1);
} elseif ($call_update > 0) {
    $call = callDetails($call_update);
    if (!empty($call) && validCall($call) && callActive($call)) {
        if ($call['call_status'] == 1) {
            echo boomCode(1, callData($call['call_type']));
        } else {
            echo boomCode(0);
        }
    } else {
        echo boomCode(0);
    }
} elseif ($incoming_update > 0) {
    $call = incomingCallDetails();
    if (!empty($call) && callActive($call)) {
        echo boomCode(1);
    } else {
        echo boomCode(99);
    }
} elseif ($call_check > 0) {
    $call = incomingCallDetails();
    if (!empty($call) && callActive($call)) {
        $hunter = userDetails($call['call_hunter']);
        if (!empty($hunter)) {
            $call['call_username'] = $hunter['user_name'];
            $call['call_avatar'] = myAvatar($hunter['user_tumb']);
            echo boomCode(1, array('data' => $call));
        } else {
            echo boomCode(99);
        }
    } else {
        echo boomCode(99);
    }
} elseif ($call_upgrade > 0) {
    $call = callDetails($call_upgrade);
    if (!empty($call) && validCall($call) && callActive($call)) {
        if (callExpired($call)) {
            endCall($call, 'expired');
            $hunter = $call['call_hunter'];
            $target = $call['call_target'];
            $mysqli->query("UPDATE boom_users SET ucall = 0 WHERE user_id = '$hunter' OR user_id = '$target'");
            redisUpdateUser($hunter);
            redisUpdateUser($target);
            echo boomCode(2);
        } else {
            $mysqli->query("UPDATE boom_call SET call_active = '" . time() . "' WHERE call_id = '{$call['call_id']}'");
            echo boomCode(0);
        }
    } else {
        if (!empty($call)) {
            endCall($call, 'timeout');
            $hunter = $call['call_hunter'];
            $target = $call['call_target'];
            $mysqli->query("UPDATE boom_users SET ucall = 0 WHERE user_id = '$hunter' OR user_id = '$target'");
            redisUpdateUser($hunter);
            redisUpdateUser($target);
        }
        echo boomCode(2);
    }
} elseif ($call_user > 0) {
    $user = userDetails($call_user);
    if (!empty($user)) {
        echo $user['user_name'];
    } else {
        echo '0';
    }
} elseif ($call_open > 0) {
    $call = callDetails($call_open);
    if (empty($call) || !validCall($call) || !callActive($call)) {
        echo boomCode(2, $lang['call_ended']);
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
                'apptoken' => $token,
                'appurl' => $setting['live_url']
            );
        } catch (Exception $e) {
            error_log("LiveKit token error: " . $e->getMessage());
            echo boomCode(2, $lang['call_error']);
            die();
        }
    }

    echo boomCode(1, array('data' => $response));
}
?>