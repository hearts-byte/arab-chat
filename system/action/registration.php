<?php
require __DIR__ . "/../config.php";

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo boomError('error');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo boomError('error');
    exit();
}

if (isset($_POST["username"], $_POST["password"], $_POST["email"], $_POST["birth"], $_POST["gender"])) {
    echo userRegistration();
    die;
}

echo boomError('invalid_command');

function userRegistration() {
    global $mysqli, $setting;
    
    $user_ip = getIp();
    $user_name = escape($_POST["username"]);
    $user_password = escape($_POST["password"]);
    $dlang = getLanguage();
    $user_email = escape($_POST["email"]);
    $user_gender = escape($_POST["gender"]);
    $birth = escape($_POST["birth"]);

    if (!boomCheckRecaptcha()) {
        return boomCode(7, array('message' => boomError('missing_recaptcha')));
    }
    
    if (!validName($user_name)) {
        return boomCode(4, array('message' => boomError('invalid_username')));
    }
    
    if (!validEmail($user_email)) {
        return boomCode(6, array('message' => boomError('invalid_email')));
    }
    
    if (!checkEmail($user_email) || !checkSmail($user_email)) {
        return boomCode(10, array('message' => boomError('email_exist')));
    }
    
    if (!validPassword($user_password)) {
        return boomCode(17, array('message' => boomError('invalid_pass')));
    }
    
    if (!validDateAge($birth)) {
        return boomCode(13, array('message' => boomError('sel_age')));
    }
    
    $age = getDateAge($birth);
    
    if ($age < $setting['min_age']) {
        return boomCode(99, array('message' => boomError('coppa')));
    }
    
    if (!validGender($user_gender)) {
        return boomCode(14, array('message' => boomError('invalid_data')));
    }
    
    if (!boomOkRegister($user_ip)) {
        return boomCode(16, array('message' => boomError('max_reg')));
    }
    
    if (!boomUsername($user_name)) {
        return boomCode(5, array('message' => boomError('username_exist')));
    }

    $user_password = encrypt($user_password);
    
    $system_user = array(
        "name" => $user_name,
        "password" => $user_password,
        "email" => $user_email,
        "language" => $dlang,
        "gender" => $user_gender,
        "avatar" => genderAvatar($user_gender),
        "age" => $age,
        "birth" => $birth,
        "ip" => $user_ip
    );
    
    $user = boomInsertUser($system_user);
    
    if (empty($user)) {
        return boomCode(2, array('message' => boomError('error')));
    }
    
    return boomCode(1, array('message' => boomSuccess('action_complete')));
}
?>