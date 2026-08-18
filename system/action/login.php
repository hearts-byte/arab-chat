<?php
require __DIR__ . "/../config.php";
if (isset($_POST["password"], $_POST["username"])) {
    $password = encrypt(escape($_POST["password"]));
    $username = escape($_POST["username"]);
    echo chatLogin($username, $password);
    exit;
}

if (isset($_POST["gusername"])) {
    if (guestForm()) {
        if (!isset($_POST["ggender"]) || !isset($_POST["gbirth"])) {
            echo boomCode(0, boomError('invalid_data'));
            exit;
        }
    }
    echo guestNameLogin();
    exit;
}

function guestNameLogin() {
    global $mysqli, $setting;

    if (!allowGuest()) {
        return boomCode(0, boomError('error'));
    }
    if (!boomCheckRecaptcha()) {
        return boomCode(6, boomError('missing_recaptcha'));
    }
    if (!okGuest(getIp())) {
        return boomCode(16, boomError('max_reg'));
    }

    $name = escape($_POST["gusername"]);

    if (!validName($name)) {
        return boomCode(4);
    }
    if (!boomUsername($name)) {
        return boomCode(5, boomError('username_exist'));
    }

    $uniqueEmail = 'guest_' . uniqid() . '@local';

    $guest = [
        "name"     => $name,
        "password" => randomPass(),
        "email"    => $uniqueEmail,
        "language" => getLanguage(),
        "ip"       => getIp(),
        "rank"     => 0,
        "avatar"   => "default_guest.png"
    ];

    if (guestForm()) {
        $gender = escape($_POST["ggender"]);
        $birth  = escape($_POST["gbirth"]);

        if (!validDateAge($birth)) {
            return boomCode(13, ['message' => boomError('sel_age')]);
        }
        $age = getDateAge($birth);
        if ($age < $setting['min_age']) {
            return boomCode(99, ['message' => boomError('coppa')]);
        }
        if (!validGender($gender)) {
            return boomCode(14, ['message' => boomError('invalid_data')]);
        }

        $guest["gender"] = $gender;
        $guest["age"]    = $age;
        $guest["birth"]  = $birth;
    } else {
        $guest["gender"] = 1;
        $guest["age"]    = 20;
        $guest["birth"]  = '1998-01-01';
    }

    $user = boomInsertUser($guest);
    if (empty($user)) {
        return boomCode(0, ['message' => boomError('error')]);
    }
    return boomCode(1);
}

function chatLogin($username, $password)
{
    global $mysqli;
    $ip = getIp();
    if (empty($username) || empty($password)) return 3;
    if (isEmail($username)) {
        $sql = "
            SELECT * FROM boom_users
             WHERE user_email    = '{$username}'
               AND user_password = '{$password}'
        ";
    } else {
        $sql = "
            SELECT * FROM boom_users
             WHERE user_name     = '{$username}'
               AND user_password = '{$password}'
        ";
    }
    $res = $mysqli->query($sql);
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $sid = $row["session_id"] + 1;
        $id  = $row["user_id"];
        $mysqli->query("
            UPDATE boom_users
               SET user_ip     = '{$ip}',
                   user_roomid = '1',
				   user_move = '". time() ."'
                   session_id  = '{$sid}'
             WHERE user_id    = '{$id}'
        ");
        $user = userDetails($id);
        if (!empty($user)) {
            setBoomCookie($user);
            return boomCode(3);
        }
        return boomCode(2);
    }
    return boomCode(2);
}
