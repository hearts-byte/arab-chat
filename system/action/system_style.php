<?php
require_once('../config_session.php');

if(!boomAllow(100)){
    die();
}

if(isset($_POST['install_style'])){
    $ref = escape($_POST['install_style']);
    $install_file = BOOM_PATH . "/pstyle/$ref/install.php";
    if(!file_exists($install_file)){
        echo boomError('error');
        exit();
    }
    require($install_file);
    if(!isset($ps) || !is_array($ps)){
        echo boomError('error');
        exit();
    }
    $check = $mysqli->query("SELECT id FROM boom_style WHERE style_ref = '$ref'");
    if($check->num_rows > 0){
        echo boomError('error');
        exit();
    }
    $style_name = escape($ps['style_name'] ?? $ref);
    $style_wrap = escape($ps['style_wrap'] ?? '');
    $style_top = escape($ps['style_top'] ?? '');
    $style_avatar = escape($ps['style_avatar'] ?? '');
    $style_menu = escape($ps['style_menu'] ?? '');
    $style_content = escape($ps['style_content'] ?? '');
    $style_custom = escape($ps['style_custom'] ?? '');
    $mysqli->query("INSERT INTO boom_style (style_ref, style_name, style_active, style_wrap, style_top, style_avatar, style_menu, style_content, style_custom) VALUES ('$ref', '$style_name', 1, '$style_wrap', '$style_top', '$style_avatar', '$style_menu', '$style_content', '$style_custom')");
    if($mysqli->affected_rows > 0){
        echo boomSuccess('action_complete');
    } else {
        echo boomError('error');
    }
    exit();
}

if(isset($_POST['create_style'])){
    $name = escape($_POST['create_style']);
    if(empty($name)){
        echo boomError('error');
        exit();
    }
    $ref = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($name)));
    $check = $mysqli->query("SELECT id FROM boom_style WHERE style_ref = '$ref'");
    if($check->num_rows > 0){
        $ref .= '_' . time();
    }
    $mysqli->query("INSERT INTO boom_style (style_ref, style_name, style_active, style_wrap, style_top, style_avatar, style_menu, style_content, style_custom) VALUES ('$ref', '$name', 1, '', '', '', '', '', '')");
    if($mysqli->affected_rows > 0){
        echo boomSuccess('action_complete');
    } else {
        echo boomError('error');
    }
    exit();
}

if(isset($_POST['save_style'])){
    $id = (int)$_POST['save_style'];
    $name = escape($_POST['style_name'] ?? '');
    $active = (int)($_POST['style_active'] ?? 1);
    $wrap = escape($_POST['style_wrap'] ?? '');
    $top = escape($_POST['style_top'] ?? '');
    $avatar = escape($_POST['style_avatar'] ?? '');
    $menu = escape($_POST['style_menu'] ?? '');
    $content = escape($_POST['style_content'] ?? '');
    $custom = escape($_POST['style_custom'] ?? '');
    if(empty($name) || $id <= 0){
        echo boomError('error');
        exit();
    }
    $mysqli->query("UPDATE boom_style SET style_name='$name', style_active='$active', style_wrap='$wrap', style_top='$top', style_avatar='$avatar', style_menu='$menu', style_content='$content', style_custom='$custom' WHERE id='$id'");
    if($mysqli->affected_rows >= 0){
        echo boomSuccess('action_complete');
    } else {
        echo boomError('error');
    }
    exit();
}

if(isset($_POST['delete_style'])){
    $id = (int)$_POST['delete_style'];
    if($id <= 0){
        echo boomError('error');
        exit();
    }
    $mysqli->query("DELETE FROM boom_style WHERE id='$id'");
    if($mysqli->affected_rows > 0){
        echo boomSuccess('action_complete');
    } else {
        echo boomError('error');
    }
    exit();
}

echo boomError('error');
?>