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

if (isset($_POST['upload_music'])) {
    if (!canProfileMusic()) {
        echo boomCode(0, array('message' => boomError('cannot_action')));
        exit();
    }
    
    if (!isset($_FILES['file'])) {
        echo boomCode(0, array('message' => boomError('no_file')));
        exit();
    }
    
    if (fileError(1)) {
        echo boomCode(0, array('message' => boomError('file_big')));
        exit();
    }
    
    $allowed = array('audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/webm');
    $file_type = $_FILES['file']['type'];
    
    if (!in_array($file_type, $allowed)) {
        echo boomCode(0, array('message' => boomError('wrong_file')));
        exit();
    }
    
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = 'music_' . $data['user_id'] . '_' . time() . '.' . $ext;
    $target = BOOM_PATH . '/music/' . $filename;
    
    $current_music = $data['user_pmusic'];
    if ($current_music != '' && file_exists(BOOM_PATH . '/music/' . $current_music)) {
        unlinkProfileMusic($current_music);
    }
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $mysqli->query("UPDATE boom_users SET user_pmusic = '$filename' WHERE user_id = '{$data['user_id']}'");
        redisUpdateUser($data['user_id']);
        echo boomCode(1, array('message' => boomSuccess('action_complete')));
    } else {
        echo boomCode(0, array('message' => boomError('error')));
    }
    exit();
}

if (isset($_POST['remove_pmusic'])) {
    if (!canProfileMusic()) {
        echo boomError('cannot_action');
        exit();
    }
    
    $current_music = $data['user_pmusic'];
    if ($current_music != '' && file_exists(BOOM_PATH . '/music/' . $current_music)) {
        unlinkProfileMusic($current_music);
    }
    
    $mysqli->query("UPDATE boom_users SET user_pmusic = '' WHERE user_id = '{$data['user_id']}'");
    redisUpdateUser($data['user_id']);
    echo boomCode(1, array('message' => boomSuccess('action_complete')));
    exit();
}

echo boomError('invalid_command');
?>