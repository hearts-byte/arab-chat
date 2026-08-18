<?php
require_once '../config.php';

if (!boomLogged()) {
    boomError('error');
    exit;
}

if (isset($_POST['share_gold']) && isset($_POST['shared_gold'])) {
    shareGold();
    exit;
}

if (isset($_POST['share_ruby']) && isset($_POST['shared_ruby'])) {
    shareRuby();
    exit;
}

if (isset($_POST['add_ignore'])) {
    addIgnore();
    exit;
}

if (isset($_POST['remove_ignore'])) {
    removeIgnore();
    exit;
}

if (isset($_POST['valid_code']) && isset($_POST['verify_code'])) {
    verifyCode();
    exit;
}

if (isset($_POST['send_verify'])) {
    sendVerify();
    exit;
}

if (isset($_POST['new_guest_name']) && isset($_POST['new_guest_password']) && isset($_POST['new_guest_email'])) {
    registerGuest();
    exit;
}

if (isset($_POST['clear_private'])) {
    clearPrivate();
    exit;
}

if (isset($_POST['read_private'])) {
    readPrivate();
    exit;
}

if (isset($_POST['like_profile'])) {
    likeProfile();
    exit;
}

if (isset($_POST['clear_notification'])) {
    clearNotification();
    exit;
}

if (isset($_POST['accept_warn'])) {
    acceptWarn();
    exit;
}

boomError('error');

function shareGold() {
    global $mysqli, $data, $setting;

    $target = escape($_POST['share_gold'], true);
    $amount = intval($_POST['shared_gold']);

    if ($amount <= 0) {
        boomError('invalid_amount');
        return;
    }

    if ($target == $data['user_id']) {
        boomError('cannot_action');
        return;
    }

    $targetUser = userDetails($target);
    if (empty($targetUser)) {
        boomError('no_user');
        return;
    }

    if ($data['user_gold'] < $amount) {
        boomError('low_balance');
        return;
    }

    $mysqli->query("UPDATE boom_users SET user_gold = user_gold - {$amount} WHERE user_id = '{$data['user_id']}'");
    $mysqli->query("UPDATE boom_users SET user_gold = user_gold + {$amount} WHERE user_id = '{$target}'");

    boomNotify('gold_share', [
        'hunter' => $data['user_id'],
        'target' => $target,
        'data'   => $amount,
		'icon'  => 'gold'

    ]);

    boomConsole('share_gold', [
        'from'   => $data['user_id'],
        'to'     => $target,
        'amount' => $amount
    ]);

    echo boomSuccess('action_complete');
}

function shareRuby() {
    global $mysqli, $data, $setting;

    $target = escape($_POST['share_ruby'], true);
    $amount = intval($_POST['shared_ruby']);

    if ($amount <= 0) {
        boomError('invalid_amount');
        return;
    }

    if ($target == $data['user_id']) {
        boomError('cannot_action');
        return;
    }

    $targetUser = userDetails($target);
    if (empty($targetUser)) {
        boomError('no_user');
        return;
    }

    if ($data['user_ruby'] < $amount) {
        boomError('low_balance');
        return;
    }

    $mysqli->query("UPDATE boom_users SET user_ruby = user_ruby - {$amount} WHERE user_id = '{$data['user_id']}'");
    $mysqli->query("UPDATE boom_users SET user_ruby = user_ruby + {$amount} WHERE user_id = '{$target}'");

    boomNotify('ruby_share', [
        'hunter' => $data['user_id'],
        'target' => $target,
        'data'   => $amount,
		'icon'  => 'ruby'
    ]);

    boomConsole('share_ruby', [
        'from'   => $data['user_id'],
        'to'     => $target,
        'amount' => $amount
    ]);

    echo boomSuccess('action_complete');
}

function addIgnore() {
    global $mysqli, $data;

    $target = escape($_POST['add_ignore'], true);
    $targetUser = userDetails($target);

    if (empty($targetUser) || !canIgnore($targetUser)) {
        boomError('cannot_action');
        return;
    }

    $check = $mysqli->query("SELECT ignore_id FROM boom_ignore WHERE ignorer = '{$data['user_id']}' AND ignored = '{$target}'");
    if ($check && $check->num_rows > 0) {
        boomError('already_action');
        return;
    }

    $mysqli->query("INSERT INTO boom_ignore (ignorer, ignored, ignore_date) VALUES ('{$data['user_id']}', '{$target}', '" . time() . "')");

	redisUpdateIgnore($data['user_id']);
    echo boomSuccess('action_complete');
}

function removeIgnore() {
    global $mysqli, $data;

    $target = escape($_POST['remove_ignore'], true);

    $mysqli->query("DELETE FROM boom_ignore WHERE ignorer = '{$data['user_id']}' AND ignored = '{$target}'");

	redisUpdateIgnore($data['user_id']);
    echo boomSuccess('action_complete');
}

function verifyCode() {
    global $mysqli, $data, $setting;

    $code = escape($_POST['valid_code']);

    if ($data['user_verify'] == 1) {
        boomError('already_action');
        return;
    }

    if ($data['valid_key'] != $code) {
        boomError('invalid_code');
        return;
    }

    $mysqli->query("UPDATE boom_users SET user_verify = 1, valid_key = '' WHERE user_id = '{$data['user_id']}'");
    redisUpdateUser($data['user_id']);

    echo boomCode(1);
}

function sendVerify() {
    global $data;

    if ($data['user_verify'] == 1) {
        boomError('already_action');
        return;
    }

    $result = sendActivation($data);
    if ($result == 1) {
        echo boomCode(1);
    } else {
        boomError('error');
    }
}


function registerGuest() {
    global $mysqli, $setting;

    if (!boomCheckRecaptcha()) {
        echo boomError('missing_recaptcha');
        return;
    }

    $name     = escape($_POST['new_guest_name']);
    $password = escape($_POST['new_guest_password']);
    $email    = escape($_POST['new_guest_email']);

    if (!validName($name)) {
        echo boomError('invalid_username');
        return;
    }
    if (!validEmail($email)) {
        echo boomError('invalid_email');
        return;
    }
    if (!checkEmail($email) || !checkSmail($email)) {
        echo boomError('email_exist');
        return;
    }
    if (!validPassword($password)) {
        echo boomError('invalid_pass');
        return;
    }

    $user = [
        'name'     => $name,
        'password' => encrypt($password),
        'email'    => $email,
        'language' => getLanguage(),
        'ip'       => getIp(),
        'rank'     => 1,
        'avatar'   => 'default_avatar.png',
        'age'      => 28,
        'gender'   => '1',
        'birth'    => '1998-01-01'
    ];

    $newUser = boomInsertUser($user);
    if (empty($newUser)) {
        echo boomCode(0, ['message' => boomError('error')]);
        return;
    }

    $_SESSION['user_id'] = $newUser['user_id'];
    $_SESSION['user_name'] = $newUser['user_name'];
    $_SESSION['user_rank'] = $newUser['user_rank'];

    echo boomCode(1);
}
function readPrivate() {
    global $mysqli, $data;
    $mysqli->query("UPDATE boom_conversation SET unread = 0 WHERE target = '{$data['user_id']}'");
    updateNotify($data['user_id']);

    echo boomSuccess('action_complete');
}

function likeProfile() {
    global $mysqli, $data;

    $target = escape($_POST['like_profile'], true);
    if ($target == $data['user_id']) {
        boomError('cannot_action');
        return;
    }

    $targetUser = userDetails($target);
    if (empty($targetUser)) {
        boomError('no_user');
        return;
    }

    $check = $mysqli->query("SELECT id FROM boom_pro_like WHERE hunter = '{$data['user_id']}' AND target = '{$target}'");
    if ($check && $check->num_rows > 0) {
        $mysqli->query("DELETE FROM boom_pro_like WHERE hunter = '{$data['user_id']}' AND target = '{$target}'");
    } else {
        $mysqli->query("INSERT INTO boom_pro_like (hunter, target, like_date) VALUES ('{$data['user_id']}', '{$target}', '" . time() . "')");
        boomNotify('prolike', [
            'hunter' => $data['user_id'],
            'target' => $target,
			'icon'  => 'like'
        ]);
    }

    $countResult = $mysqli->query("SELECT COUNT(*) as cnt FROM boom_pro_like WHERE target = '{$target}'");
    $likeCount = $countResult->fetch_assoc()['cnt'];

    $liked = $mysqli->query("SELECT id FROM boom_pro_like WHERE hunter = '{$data['user_id']}' AND target = '{$target}'")->num_rows > 0;

    ob_start();
    ?>
	<div id="plikepro" class="lite_olay plike_item plikes" onclick="proLike(<?php echo $target; ?>);">
	<img src="default_images/prolike/proliked.svg"> <span class="plike_count"> <?php echo $likeCount; ?> </span>
	</div>
    <?php
    $html = ob_get_clean();
    echo boomCode(1, array('data' => $html));
}

function clearNotification() {
    global $mysqli, $data;
    $mysqli->query("DELETE FROM boom_notification WHERE notified = '{$data['user_id']}'");
    updateNotify($data['user_id']);

    echo boomSuccess('action_complete');
}

function acceptWarn() {
    global $mysqli, $data;
    $mysqli->query("UPDATE boom_users SET warn_msg = '' WHERE user_id = '{$data['user_id']}'");
    redisUpdateUser($data['user_id']);

    echo boomCode(1);
}
?>