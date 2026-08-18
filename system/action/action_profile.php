<?php
require('../config.php');

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo boomError('error');
    exit();
}
if (!boomLogged()) {
    echo boomError('site_connect');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo boomError('error');
    exit();
}

function validateInput($input, $maxLength = 1000) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = validateInput($value, $maxLength);
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
    $_POST[$key] = validateInput($value);
}

if (isset($_POST['update_status'])) {
    $status = escape($_POST['update_status']);
    if (!validStatus($status)) {
        $status = 1;
    }
    $mysqli->query("UPDATE boom_users SET user_status = '$status' WHERE user_id = '{$data['user_id']}'");
    redisUpdateUser($data['user_id']);
    echo boomCode(1, array('text' => statusTitle($status), 'icon' => newStatusIcon($status)));
    die();
}

if (isset($_POST['edit_username'], $_POST['new_name'])) {
    $new_name = escape($_POST['new_name']);
    
    if (!canName()) {
        echo boomError('cannot_action');
        die();
    }
    
    if ($new_name == $data['user_name']) {
        echo boomSuccess('action_complete');
        die();
    }
    
    if (!validName($new_name)) {
        echo boomError('invalid_username');
        die();
    }
    
    if (!boomSame($new_name, $data['user_name'])) {
        if (!boomUsername($new_name)) {
            echo boomError('username_exist');
            die();
        }
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_name = '$new_name' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows > 0) {
        boomConsole('change_name', array('custom' => $data['user_name']));
        changeNameLog($data, $new_name);
        redisUpdateUser($data['user_id']);
        echo boomSuccess('updated');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['save_color'], $_POST['save_bold'], $_POST['save_font'])) {
    $c = escape($_POST['save_color']);
    $b = escape($_POST['save_bold']);
    $f = escape($_POST['save_font']);
    
    if (!validTextColor($c) || !validTextWeight($b) || !validTextFont($f)) {
        echo boomError('invalid_data');
        die();
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET bccolor = '$c', bcbold = '$b', bcfont = '$f' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['save_mood'])) {
    $mood = escape($_POST['save_mood']);
    
    if (!canMood()) {
        echo boomError('cannot_action');
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
    
    if ($mood == $data['user_mood']) {
        echo boomSuccess('action_complete');
        die();
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_mood = '$mood' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('action_complete');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['save_pstyle'])) {
    $pstyle = escape($_POST['save_pstyle']);
    
    if(!canProfileStyle()){
        die();
    }
    
    if ($pstyle == $data['user_pstyle']) {
        echo boomSuccess('action_complete');
        die();
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_pstyle = '$pstyle' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('action_complete');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['remove_pstyle'])) {
    if(!canProfileStyle()){
        die();
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_pstyle = '' WHERE user_id = '$user_id'");
    
    if($mysqli->affected_rows > 0){
        redisUpdateUser($data['user_id']);
        echo boomSuccess('action_complete');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['save_info'], $_POST['birth'], $_POST['gender'])) {
    $birth = escape($_POST['birth']);
    $gender = escape($_POST['gender']);
    
    if (!validGender($gender) || !validDateAge($birth)) {
        echo boomCode(0, array('message' => boomError('error')));
        die();
    }
    
    $age = getDateAge($birth);
    
    if ($age == 0) {
        echo boomCode(0, array('message' => boomError('error')));
        die();
    }
    
    $data['user_sex'] = $gender;
    if (defaultAvatar($data['user_tumb'])) {
        $avatar = myAvatar(resetAvatar($data));
    } else {
        $avatar = myAvatar($data['user_tumb']);
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_birth = '$birth', user_age = '$age', user_sex = '$gender' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('saved');
    } else {
        echo boomCode(0, array('message' => boomError('error')));
    }
    die();
}

if (isset($_POST['save_about'], $_POST['about'])) {
    $about = clearBreak($_POST['about']);
    $about = escape($about);
    
    if (isTooLong($about, 900)) {
        echo boomError('error');
        die();
    }
    
    if (isBadText($about)) {
        echo boomError('restricted_content');
        die();
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users_data SET user_about = '$about' WHERE uid = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['my_username_color'], $_POST['my_username_font'])) {
    $color = escape($_POST['my_username_color']);
    $font = escape($_POST['my_username_font']);
    
    if (!validNameColor($color) || !validNameFont($font)) {
        echo boomError('invalid_data');
        die();
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_color = '$color', user_font = '$font' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['change_sound'], $_POST['chat_sound'], $_POST['private_sound'], $_POST['notify_sound'], $_POST['name_sound'])) {
    $chat_sound = escape($_POST['chat_sound']);
    $private_sound = escape($_POST['private_sound']);
    $notify_sound = escape($_POST['notify_sound']);
    $name_sound = escape($_POST['name_sound']);
    $call_sound = isset($_POST['call_sound']) ? escape($_POST['call_sound']) : '0';
    
    $sound = soundCode('chat', $chat_sound) . soundCode('private', $private_sound) . soundCode('notify', $notify_sound) . soundCode('name', $name_sound) . soundCode('call', $call_sound);
    
    if ($sound == '') {
        $sound = '0';
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_sound = '$sound' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomCode(1, array('data' => $sound, 'message' => boomSuccess('saved')));
    } else {
        echo boomCode(0, array('message' => boomError('error')));
    }
    die();
}

if (isset($_POST['save_preference'])) {
    $required = ['set_pmusic', 'set_private_mode', 'save_ulogin', 'set_user_call', 'set_ufriend', 'set_user_bubble'];
    
    foreach ($required as $field) {
        if (!isset($_POST[$field])) {
            echo boomError('empty_field');
            die();
        }
    }
    
    $set_pmusic = (int)escape($_POST['set_pmusic']);
    $set_private_mode = (int)escape($_POST['set_private_mode']);
    $save_ulogin = (int)escape($_POST['save_ulogin']);
    $set_user_call = (int)escape($_POST['set_user_call']);
    $set_ufriend = (int)escape($_POST['set_ufriend']);
    $set_user_bubble = (int)escape($_POST['set_user_bubble']);
    $user_id = (int)$data['user_id'];

    $mysqli->query("UPDATE boom_users SET pmusic = '$set_pmusic', user_private = '$set_private_mode', ulogin = '$save_ulogin', user_call = '$set_user_call', ufriend = '$set_ufriend', user_bubble = '$set_user_bubble' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['user_timezone'], $_POST['user_language'], $_POST['user_country'])) {
    $user_timezone = escape($_POST['user_timezone']);
    $user_language = escape($_POST['user_language']);
    $user_country = escape($_POST['user_country']);
    
    if (!validCountry($user_country)) {
        echo boomError('invalid_data');
        die();
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_timezone = '$user_timezone', user_language = '$user_language', country = '$user_country' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['relationship'])) {
    $relationship = escape($_POST['relationship']);
    
    if (!validRelation($relationship)) {
        echo boomError('invalid_data');
        die();
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_relation = '$relationship' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['save_shared'])) {
    $ashare = isset($_POST['ashare']) ? (int)escape($_POST['ashare']) : 0;
    $sshare = isset($_POST['sshare']) ? (int)escape($_POST['sshare']) : 0;
    $fshare = isset($_POST['fshare']) ? (int)escape($_POST['fshare']) : 0;
    $gshare = isset($_POST['gshare']) ? (int)escape($_POST['gshare']) : 0;
    $lshare = isset($_POST['lshare']) ? (int)escape($_POST['lshare']) : 0;
    $user_id = (int)$data['user_id'];
    
    $mysqli->query("UPDATE boom_users SET ashare = '$ashare', sshare = '$sshare', fshare = '$fshare', gshare = '$gshare', lshare = '$lshare' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        redisUpdateUser($data['user_id']);
        echo boomSuccess('saved');
    } else {
        echo boomError('error');
    }
    die();
}

if (isset($_POST['set_user_theme'])) {
    $theme = escape($_POST['set_user_theme']);
    
    if (!canTheme()) {
        echo boomError('invalid_data');
        die();
    }
    
    $user_id = (int)$data['user_id'];
    $mysqli->query("UPDATE boom_users SET user_theme = '$theme' WHERE user_id = '$user_id'");
    
    if ($mysqli->affected_rows >= 0) {
        $logo = getLogo($theme);
        redisUpdateUser($data['user_id']);
        echo boomCode(1, array('theme' => $theme, 'logo' => $logo, 'message' => boomSuccess('saved')));
    } else {
        echo boomError('error');
    }
    die();
}

echo boomError('error');
?>