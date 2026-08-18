<?php
require_once '../config.php';

if (!boomLogged()) {
    boomError('error');
    exit;
}

if (isset($_POST['del_priv'])) {
    $privateId = escape($_POST['del_priv'], true);
    $query = "SELECT * FROM boom_private WHERE id = '{$privateId}' AND (hunter = '{$data['user_id']}' OR target = '{$data['user_id']}')";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0) {
        $private = $result->fetch_assoc();
        if ($private['hunter'] == $data['user_id'] || $private['target'] == $data['user_id'] || canDeletePrivate()) {
            $mysqli->query("DELETE FROM boom_private WHERE id = '{$privateId}'");
            if ($mysqli->affected_rows > 0) {
                if ($private['file'] != '0' && $private['file'] != '') {
                    unlinkUpload('private', $private['file']);
                }
                echo boomCode(1);
            } else {
                boomError('error');
            }
        } else {
            boomError('cannot_action');
        }
    } else {
        boomError('no_result');
    }
    exit;
}

if (isset($_POST['del_private']) && isset($_POST['target'])) {
    $target = escape($_POST['target'], true);
    $mysqli->query("DELETE FROM boom_private WHERE (hunter = '{$data['user_id']}' AND target = '{$target}') OR (hunter = '{$target}' AND target = '{$data['user_id']}')");
    echo boomCode(1);
    exit;
}

if (isset($_POST['del_post'])) {
    $postId = escape($_POST['del_post'], true);
    $log = logDetails($postId);
    if (empty($log)) {
        boomError('no_result');
        exit;
    }
    $success = false;
    if ($log['user_id'] == $data['user_id']) {
        if (canDeleteSelfLog($log)) {
            $success = executeDeleteLog($log, $postId);
        }
    } else {
        if (canDeleteContent()) {
            $success = executeDeleteLog($log, $postId);
        }
    }
    if ($success) {
        echo boomCode(1);
    } else {
        boomError('error');
    }
    exit;
}

function executeDeleteLog($log, $postId) {
    global $mysqli, $data;
    $room = roomDetails($data["user_roomid"]);
    if (empty($room)) {
        return false;
    }
    $mysqli->query("DELETE FROM boom_chat WHERE post_id = '{$postId}' AND post_roomid = '{$data['user_roomid']}'");
    $deleted1 = $mysqli->affected_rows;
    $mysqli->query("DELETE FROM boom_report WHERE report_post = '{$postId}' AND report_type = '1' AND report_room = '{$data['user_roomid']}'");
    $deleted2 = $mysqli->affected_rows;
    if ($deleted1 + $deleted2 == 0) {
        return false;
    }
    updateStaffNotify();
    $now = time();
    if (!delExpired($room["rltime"])) {
        $mysqli->query("UPDATE boom_rooms SET rldelete = CONCAT(rldelete, ',{$postId}'), rltime = '{$now}' WHERE room_id = '{$data['user_roomid']}'");
    } else {
        $mysqli->query("UPDATE boom_rooms SET rldelete = '{$postId}', rltime = '{$now}' WHERE room_id = '{$data['user_roomid']}'");
    }
    boomConsole("delete_log", [
        "target" => $log["user_id"],
        "room" => $data["user_roomid"],
        "reason" => strip_tags($log["post_message"])
    ]);
    if ($log['file'] != '0' && $log['file'] != '') {
        unlinkUpload('chat', $log['file']);
    }
    return true;
}

boomError('error');
exit;
?>