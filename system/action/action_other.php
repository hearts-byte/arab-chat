<?php
require __DIR__ . "/../config.php";

if (isset($_POST['change_rank'], $_POST['target'])) {
	$result = boomChangeUserRank();
	if ($result) {
		if (function_exists('redisFlushAll')) redisFlushAll();
		if (function_exists('boomCacheUpdate')) boomCacheUpdate();
		if (function_exists('opcache_reset')) opcache_reset();
		echo 1;
	} else {
		echo 0;
	}
}

if (isset($_POST['del_post'])) {
	$postId = escape($_POST['del_post'], true);
	$log = logDetails($postId);
	if (empty($log)) {
		echo json_encode(['success' => false]);
		exit;
	}
	$success = false;
	if ($log['user_id'] == $data['user_id']) {
		$success = chatDeletePost();
	} else {
		$success = chatDeleteOtherPost();
	}
	if (function_exists('redisFlushAll')) redisFlushAll();
	if (function_exists('opcache_reset')) opcache_reset();
	echo json_encode(['success' => $success]);
	exit;
}

if(isset($_POST['edit_username'], $_POST['new_name'])){
	$new_name = escape($_POST['new_name']);
	if(!canName()){
		die();
	}
	if($new_name == $data['user_name']){
		echo 1;
		die();
	}
	if(!validName($new_name)){
		echo 2;
		die();
	}
	if(!boomSame($new_name, $data['user_name'])){
		if(!boomUsername($new_name)){
			echo 3;
			die();
		}
	}
	$mysqli->query("UPDATE boom_users SET user_name = '$new_name' WHERE user_id = '{$data['user_id']}'");
	boomConsole('change_name', array('custom'=>$data['user_name']));
	changeNameLog($data, $new_name);
	redisUpdateUser($target);
	if (function_exists('redisFlushAll')) redisFlushAll();
	if (function_exists('opcache_reset')) opcache_reset();
	echo 1;
	die();
}

if(isset($_POST['target_id'], $_POST['user_new_name'])){
	$targetid = escape($_POST['target_id']);
	$new_name = escape($_POST['user_new_name']);
	$user = userDetails($targetid);
	if(!canName()){
		die();
	}
	if($new_name == $user['user_name']){
		echo 1;
		die();
	}
	if(!validName($new_name)){
		echo 2;
		die();
	}
	if(!boomSame($new_name, $user['user_name'])){
		if(!boomUsername($new_name)){
			echo 3;
			die();
		}
	}
	$mysqli->query("UPDATE boom_users SET user_name = '$new_name' WHERE user_id = '{$user['user_id']}'");
	boomConsole('change_name', array('custom'=>$user['user_name']));
	changeNameLog($user, $new_name);
	redisUpdateUser($targetid);
	if (function_exists('redisFlushAll')) redisFlushAll();
	if (function_exists('opcache_reset')) opcache_reset();
	echo 1;
	die();
}

if(isset($_POST['create_user']) && isset($_POST['create_name']) && isset($_POST['create_password']) && isset($_POST['create_email']) && isset($_POST['create_gender']) && isset($_POST['create_age'])){
	echo staffCreateUser();
}

if (isset($_POST["delete_user_account"])) {
    echo boomDeleteAccount();
     exit;
}

function boomChangeUserRank() {
    global $mysqli, $data;
    $target = escape($_POST["target"]);
    $rank = escape($_POST["change_rank"]);
    $user = userDetails($target);
    if (empty($user)) {
        return 3; 
    }
    if (!canRankUser($user)) {
        return 99; 
    }
    if ($user["user_rank"] == $rank) {
        return 2;
    }
    userReset($user, $rank);
    boomNotify("rank_change", ["target" => $target, "source" => "rank_change", "rank" => $rank]);
    if (isStaff($user)) {
        $mysqli->query("UPDATE boom_users SET room_mute = '0', user_private = 1, user_mute = 0, user_regmute = 0 WHERE user_id = '" . $target . "'");
        $mysqli->query("DELETE FROM boom_room_action WHERE action_user = '" . $target . "'");
        $mysqli->query("DELETE FROM boom_ignore WHERE ignored = '" . $target . "'");
    }
    boomConsole("change_rank", ["target" => $user["user_id"], "rank" => $rank]);
    return 1;
}

function chatDeletePost(): bool {
	global $mysqli, $data;
	$postId = escape($_POST['del_post'], true);
	$log = logDetails($postId);
	if (empty($log)) {
		return false;
	}
	if (!canDeleteSelfLog($log)) {
		return false;
	}
	return executeDeleteLog($log, $postId);
}

function chatDeleteOtherPost(): bool {
	global $mysqli, $data;
	$postId = escape($_POST['del_post'], true);
	$log = logDetails($postId);
	if (empty($log)) {
		return false;
	}
	if ($log['user_id'] == $data['user_id']  || !canDeleteContent()) {
		return false;
	}
	return executeDeleteLog($log, $postId);
}

function executeDeleteLog($log, $postId): bool {
	global $mysqli, $data;
	$room = roomDetails($data["user_roomid"]);
	if (empty($room)) {
		return false;
	}
	$mysqli->query("
		DELETE FROM boom_chat
		WHERE post_id = '{$postId}'
		  AND post_roomid = '{$data['user_roomid']}'
	");
	$deleted1 = $mysqli->affected_rows;
	$mysqli->query("
		DELETE FROM boom_report
		WHERE report_post = '{$postId}'
		  AND report_type = '1'
		  AND report_room = '{$data['user_roomid']}'
	");
	$deleted2 = $mysqli->affected_rows;
	if ($deleted1 + $deleted2 > 0) {
		updateStaffNotify();
		$now = time();
		if (!delExpired($room["rltime"])) {
			$mysqli->query("
				UPDATE boom_rooms
				SET rldelete = CONCAT(rldelete, ',{$postId}'), rltime = '{$now}'
				WHERE room_id = '{$data['user_roomid']}'
			");
		} else {
			$mysqli->query("
				UPDATE boom_rooms
				SET rldelete = '{$postId}', rltime = '{$now}'
				WHERE room_id = '{$data['user_roomid']}'
			");
		}
		boomConsole("delete_log", [
			"target" => $log["user_id"],
			"room" => $data["user_roomid"],
			"reason" => strip_tags($log["post_message"])
		]);
		removeRelatedFile($postId, "chat");
	if (function_exists('redisFlushAll')) redisFlushAll();
	if (function_exists('opcache_reset')) opcache_reset();
		return true;
	}
	return false;
}

function staffCreateUser() {
    global $mysqli, $data;
    if (!boomAllow(10)) {
        return 2;
    }
    $user_ip = getIp();
    $user_name = escape($_POST["create_name"]);
    $user_password = escape($_POST["create_password"]);
    $dlang = $data["language"];
    $user_email = escape($_POST["create_email"]);
    $user_gender = escape($_POST["create_gender"]);
    $user_age = escape($_POST["create_age"]);
    if ($user_name == "" || $user_password == "" || $user_email == "") {
        return 2;
    }
    if (!validName($user_name)) {
        return 3;
    }
    if (!boomUsername($user_name)) {
        return 4;
    }
    if (!validEmail($user_email)) {
        return 5;
    }
    if (!checkEmail($user_email) || !checkSmail($user_email)) {
        return 6;
    }
    if (!validAge($user_age)) {
        return 13;
    }
    if (!validGender($user_gender)) {
        $user_gender = 1;
    }
    $user_password = encrypt($user_password);
    $system_user = array(
        "name" => $user_name,
        "password" => $user_password,
        "email" => $user_email,
        "language" => $dlang,
        "verified" => 1,
        "cookie" => 0,
        "gender" => $user_gender,
        "avatar" => genderAvatar($user_gender),
        "age" => $user_age,
        "ip" => $user_ip
    );
    $user = boomInsertUser($system_user);
    boomConsole("create_user", array("target" => $user["user_id"]));
    return 1;
}

function boomDeleteAccount()
{
    global $mysqli;
    global $data;
    global $cody;
    $id = escape($_POST["delete_user_account"]);
    $user = userDetails($id);
    if (empty($user)) {
        return 3;
    }
    if (!canDeleteUser($user)) {
        return 0;
    }
    clearUserData($user);
    boomConsole("delete_account", ["target" => $id, "custom" => $user["user_name"]]);
    return 1;
}