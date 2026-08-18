<?php
require(__DIR__ . '/../config_session.php');

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo boomError('error');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo boomError('error');
    exit();
}

function validateUserInput($input, $maxLength = 1000) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = validateUserInput($value, $maxLength);
        }
        return $input;
    }
    
    $input = trim($input);
    if (strlen($input) > $maxLength) {
        $input = substr($input, 0, $maxLength);
    }
    
    $input = str_replace("\0", '', $input);
    $input = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);
    $input = strip_tags($input);
    
    return $input;
}

foreach ($_POST as $key => $value) {
    $_POST[$key] = validateUserInput($value);
}

if (isset($_POST['set_user_id'], $_POST['set_user_email'])) {
    $target_id = escape($_POST['set_user_id']);
    $new_email = escape($_POST['set_user_email']);
    $user = userDetails($target_id);
    
    if (!canModifyEmail($user)) {
        echo boomError('cannot_user');
        die();
    }
    
    if (!validEmail($new_email)) {
        echo boomError('invalid_email');
        die();
    }
    
    if (!checkEmail($new_email)) {
        echo boomError('email_exist');
        die();
    }
    
    $mysqli->query("UPDATE boom_users SET user_email = '$new_email' WHERE user_id = '$target_id'");
    redisUpdateUser($target_id);
    echo boomSuccess('saved');
    die();
}

if (isset($_POST['target_about'], $_POST['set_user_about'])) {
    $target_id = escape($_POST['target_about']);
    $about = clearBreak($_POST['set_user_about']);
    $about = escape($about);
    $user = userDetails($target_id);
    
    if (!canModifyAbout($user)) {
        echo boomError('cannot_user');
        die();
    }
    
    if (isTooLong($about, 800)) {
        echo boomError('error');
        die();
    }
    
    if (isBadText($about)) {
        echo boomError('restricted_content');
        die();
    }
    
    $stmt = $mysqli->prepare("UPDATE boom_users_data SET user_about = ? WHERE uid = ?");
    $stmt->bind_param("si", $about, $target_id);
    
    if ($stmt->execute()) {
        redisUpdateUser($target_id);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    $stmt->close();
    die();
}

if (isset($_POST['target_id'], $_POST['user_new_name'])) {
    $targetid = escape($_POST['target_id']);
    $new_name = escape($_POST['user_new_name']);
    $user = userDetails($targetid);
    
    if (!canModifyName($user)) {
        echo boomError('cannot_user');
        die();
    }
    
    if ($new_name == $user['user_name']) {
        echo boomSuccess('action_complete');
        die();
    }
    
    if (!validName($new_name)) {
        echo boomError('invalid_username');
        die();
    }
    
    if (!boomSame($new_name, $user['user_name'])) {
        if (!boomUsername($new_name)) {
            echo boomError('username_exist');
            die();
        }
    }
    
    $mysqli->query("UPDATE boom_users SET user_name = '$new_name' WHERE user_id = '{$user['user_id']}'");
    boomConsole('change_name', array('custom' => $user['user_name']));
    changeNameLog($user, $new_name);
    redisUpdateUser($targetid);
    echo boomSuccess('saved');
    die();
}

if (isset($_POST['target_id'], $_POST['user_new_mood'])) {
    $target_id = escape($_POST['target_id']);
    $mood = escape($_POST['user_new_mood']);
    $user = userDetails($target_id);
    
    if (!canModifyMood($user)) {
        echo boomError('cannot_user');
        die();
    }
    
    if (isBadText($mood)) {
        echo boomError('restricted_content');
        die();
    }
    
    if (isTooLong($mood, 40)) {
        echo boomError('error');
        die();
    }
    
    if ($mood == $user['user_mood']) {
        echo boomSuccess('action_complete');
        die();
    }
    
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_mood = ? WHERE user_id = ?");
    $stmt->bind_param("si", $mood, $target_id);
    
    if ($stmt->execute()) {
        redisUpdateUser($target_id);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    $stmt->close();
    die();
}

if (isset($_POST['target_id'], $_POST['user_new_password'])) {
    $target_id = escape($_POST['target_id']);
    $new_password = escape($_POST['user_new_password']);
    $user = userDetails($target_id);
    
    if (!canModifyPassword($user)) {
        echo boomError('cannot_user');
        die();
    }
    
    if (!validPassword($new_password)) {
        echo boomError('invalid_pass');
        die();
    }
    
    $encrypted_password = encrypt($new_password);
    $mysqli->query("UPDATE boom_users SET user_password = '$encrypted_password' WHERE user_id = '$target_id'");
    redisUpdateUser($target_id);
    echo boomSuccess('saved');
    die();
}

if (isset($_POST['target'], $_POST['set_bupload'], $_POST['set_bnews'], $_POST['set_bcall'])) {
    $target_id = escape($_POST['target']);
    $set_bupload = escape($_POST['set_bupload']);
    $set_bnews = escape($_POST['set_bnews']);
    $set_bcall = escape($_POST['set_bcall']);
    $user = userDetails($target_id);
    
    if (!canBlockUser($user)) {
        echo boomError('cannot_user');
        die();
    }
    
	$mysqli->query("UPDATE boom_users SET bupload='$set_bupload', bcall=$set_bcall, bnews=$set_bnews WHERE user_id='$target_id'");
    redisUpdateUser($target_id);
    echo boomSuccess('saved');
    die();
}

if (isset($_POST['change_rank'], $_POST['target'])) {
   	echo boomChangeUserRank();
    die();
}

if (isset($_POST['verify_member'])) {
    $target_id = escape($_POST['verify_member']);
    $user = userDetails($target_id);
    
    if (!canVerify()) {
        echo boomError('cannot_user');
        die();
    }
    
    $current_status = $user['user_verified'];
    $new_status = $current_status == 1 ? 0 : 1;
    
    $mysqli->query("UPDATE boom_users SET user_verify = '$new_status' WHERE user_id = '$target_id'");
    redisUpdateUser($target_id);
    echo boomSuccess('saved');
    die();
}

if (isset($_POST['auth_member'])) {
    $target_id = escape($_POST['auth_member']);
    $user = userDetails($target_id);
    
    if (!canAuth()) {
        echo boomError('cannot_user');
        die();
    }
    
    $current_status = $user['user_auth'];
    $new_status = $current_status == 1 ? 0 : 1;
    
    $mysqli->query("UPDATE boom_users SET user_auth = '$new_status' WHERE user_id = '$target_id'");
    redisUpdateUser($target_id);
    echo boomSuccess('saved');
    die();
}

if (isset($_POST['set_user_vpn'], $_POST['target'])) {
    $target_id = escape($_POST['target']);
    $vpn_status = escape($_POST['set_user_vpn']);
    $user = userDetails($target_id);
    
    if (!canVpn()) {
        echo boomError('cannot_user');
        die();
    }
    
    if ($vpn_status != 0 && $vpn_status != 1 && $vpn_status != 2) {
        echo boomError('invalid_data');
        die();
    }
    
    $mysqli->query("UPDATE boom_users SET uvpn = '$vpn_status' WHERE user_id = '$target_id'");
    redisUpdateUser($target_id);
    echo boomSuccess('saved');
    die();
}

if (isset($_POST["delete_user_account"])) {
    echo boomDeleteAccount();
    exit();
}

if (isset($_POST['create_user'], $_POST['create_name'], $_POST['create_password'], $_POST['create_email'], $_POST['create_gender'], $_POST['create_age'])) {
    staffCreateUser();
    die();
}

if (isset($_POST['user_color'], $_POST['user_font'], $_POST['user'])) {
    $target_id = escape($_POST['user']);
    $color = escape($_POST['user_color']);
    $font = escape($_POST['user_font']);
    $user = userDetails($target_id);
    
    if (!canModifyColor($user)) {
        echo boomError('cannot_user');
        die();
    }
    
    if (!validNameColor($color) || !validNameFont($font)) {
        echo boomError('invalid_data');
        die();
    }
    
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_color = ?, user_font = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $color, $font, $target_id);
    
    if ($stmt->execute()) {
        redisUpdateUser($target_id);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    $stmt->close();
    die();
}

function boomChangeUserRank() {
    global $mysqli, $data;
    $target = escape($_POST["target"]);
    $rank = escape($_POST["change_rank"]);
    $user = userDetails($target);
    
    if (empty($user)) {
        echo boomError('no_user');
    }
    
    if (!canRankUser($user)) {
        echo boomError('cannot_user');
    }
    
    if ($user["user_rank"] == $rank) {
        echo boomSuccess('action_complete');
    }
    
    userReset($user, $rank);
    boomNotify("rank_change", ["target" => $target, "source" => "rank_change", "rank" => $rank]);
    
    if (isStaff($user)) {
        $mysqli->query("UPDATE boom_users SET room_mute = '0', user_private = 1, user_mute = 0, user_regmute = 0 WHERE user_id = '$target'");
        $mysqli->query("DELETE FROM boom_room_action WHERE action_user = '$target'");
        $mysqli->query("DELETE FROM boom_ignore WHERE ignored = '$target'");
    }
    
    boomConsole("change_rank", ["target" => $user["user_id"], "rank" => $rank]);
    redisUpdateUser(escape($_POST['target']));
    echo boomSuccess('action_complete');
}

function staffCreateUser() {
    global $mysqli, $data, $setting;

    if (!boomAllow(100)) {
        echo boomError('empty_field');
        return;
    }

    $user_ip = getIp();
    $user_name = escape($_POST["create_name"]);
    $user_password = escape($_POST["create_password"]);
    $dlang = $data["user_language"];
    $user_email = escape($_POST["create_email"]);
    $user_gender = escape($_POST["create_gender"]);
    $user_age = escape($_POST["create_age"]);

    if ($user_name == "" || $user_password == "" || $user_email == "") {
        echo boomError('empty_field');
        return;
    }

    if (!validName($user_name)) {
        echo boomError('invalid_username');
        return;
    }

    if (!boomUsername($user_name)) {
        echo boomError('username_exist');
        return;
    }

    if (!validEmail($user_email)) {
        echo boomError('invalid_email');
        return;
    }

    if (!checkEmail($user_email) || !checkSmail($user_email)) {
        echo boomError('email_exist');
        return;
    }

    if (!validAge($user_age)) {
        echo boomError('sel_age');
        return;
    }

    if ($user_age < $setting['min_age']) {
        echo boomError('coppa');
        return;
    }

    if (!validGender($user_gender)) {
        $user_gender = 1;
    }

    $user_password = encrypt($user_password);
    $birth_date = getAgeDate($user_age);

    $system_user = [
        "name" => $user_name,
        "password" => $user_password,
        "email" => $user_email,
        "language" => $dlang,
        "verified" => 1,
        "cookie" => 0,
        "gender" => $user_gender,
        "avatar" => genderAvatar($user_gender),
        "age" => $user_age,
        "birth" => $birth_date,
        "ip" => $user_ip
    ];
    
    $user = boomInsertUser($system_user);
    boomConsole("create_user", ["target" => $user["user_id"]]);
    echo boomSuccess('action_complete');
}

function getAgeDate($age) {
    if (!is_numeric($age) || $age <= 0) {
        return '0000-00-00';
    }
    
    try {
        $now = new DateTime();
        $birth_date = clone $now;
        $birth_date->sub(new DateInterval('P' . intval($age) . 'Y'));
        if ($now->format('m-d') == '02-29' && !$birth_date->format('L')) {
            $birth_date->modify('-1 day');
        }
        
        return $birth_date->format('Y-m-d');
    } catch (Exception $e) {
        return '0000-00-00';
    }
}

function boomDeleteAccount() {
    global $mysqli;
    global $data;
    $id = escape($_POST["delete_user_account"]);
    $user = userDetails($id);
    
    if (empty($user)) {
        echo boomError('no_user');
    }
    
    if (!canDeleteUser($user)) {
        echo boomError('cannot_user');
    }
    
    clearUserData($user);
    boomConsole("delete_account", ["target" => $id, "custom" => $user["user_name"]]);
    echo boomSuccess('action_complete');

}

echo boomError('error');
?>