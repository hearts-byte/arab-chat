<?php
require_once('../config_install.php');

if ($check_install != 0) {
    exit;
}

if (!(isset($_POST["db_host"], $_POST["db_name"], $_POST["db_user"], $_POST["db_pass"], $_POST["username"], $_POST["password"], $_POST["email"], $_POST["repeat"], $_POST["domain"], $_POST["title"], $_POST["language"]))) {
    echo boomCode(0, ["error" => "An error occurred. Please try again or contact us."]);
    exit;
}

if (empty($_POST["db_host"]) || empty($_POST["db_name"]) || empty($_POST["db_user"]) || empty($_POST["username"]) || empty($_POST["password"]) || empty($_POST["email"]) || empty($_POST["repeat"]) || empty($_POST["domain"]) || empty($_POST["title"]) || empty($_POST["language"])) {
    echo boomCode(0, ["error" => "Please fill in all information."]);
    exit;
}

$DB_HOST = $_POST["db_host"];
$DB_NAME = $_POST["db_name"];
$DB_USER = $_POST["db_user"];
$DB_PASS = $_POST["db_pass"];
$HT_DOM  = $_POST["domain"];

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (mysqli_connect_errno()) {
    echo boomCode(0, ["error" => "Unable to connect to database please check your database information."]);
    exit;
}

echo processinstall();
exit;

function processInstall()
{
    global $mysqli, $HT_LIC;
    require "../../system/template/data_template.php";
    $username = escape($_POST["username"]);
    $email = escape($_POST["email"]);
    $password = escape($_POST["password"]);
    $repeat = escape($_POST["repeat"]);
    $domain = escape($_POST["domain"]);
    $title = escape($_POST["title"]);
    $language = escape($_POST["language"]);
	$parsedUrl = parse_url($domain);
	$host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
	$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
	$prefixBase = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $host . $path));
	$prefix = trim($prefixBase, '_') . '_';	$__SECURE_HUNTER = ["lic" => $HT_LIC];
    if ($password != $repeat) {
        return boomCode(0, ["error" => "Password are not matching please verify and try again"]);
    }
    if (mb_strlen($username) < 2 || 18 < mb_strlen($username)) {
        return boomCode(0, ["error" => "Invalid username, username must be between 2 and 18 characters long."]);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return boomCode(0, ["error" => "Invalid email please provide a valid email."]);
    }
    if (substr($domain, -1) == "/" || $domain == "" || !preg_match("@https?[\\w_-]*@i", $domain)) {
        return boomCode(0, ["error" => "Invalid domain please make sure domain do not end with a / and try again."]);
    }
    if (!file_exists(BOOM_PATH . "/system/language/" . $language . "/language.php")) {
        $language = "English";
    }
    $time = time();
    $encrypt = str_shuffle("HUNTER---" . md5(rand(1000000, 9999999)));
    $password = boomEncrypt($password, $encrypt);
    $check = json_decode($result);
    $dbcode = $check->config;
$mysqli->query("CREATE TABLE `boom_act` (`act_user` INT NOT NULL DEFAULT '0', `act_name` varchar(100) NOT NULL DEFAULT '', `act_time` INT NOT NULL DEFAULT '0', KEY `act_name` (`act_name`), KEY `act_user` (`act_user`), KEY `act_time` (`act_time`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_addons` (`addons_id` INT NOT NULL AUTO_INCREMENT, `addons` varchar(100) NOT NULL DEFAULT '', `addons_load` INT NOT NULL DEFAULT '0', `addons_key` varchar(100) NOT NULL DEFAULT '', `addons_access` INT NOT NULL DEFAULT '0', `bot_name` varchar(100) NOT NULL DEFAULT '', `bot_id` INT NOT NULL DEFAULT '0', `custom1` varchar(1000) NOT NULL DEFAULT '', `custom2` varchar(1000) NOT NULL DEFAULT '', `custom3` varchar(1000) NOT NULL DEFAULT '', `custom4` varchar(1000) NOT NULL DEFAULT '', `custom5` varchar(1000) NOT NULL DEFAULT '', `custom6` varchar(1000) NOT NULL DEFAULT '', `custom7` varchar(1000) NOT NULL DEFAULT '', `custom8` varchar(1000) NOT NULL DEFAULT '', `custom9` varchar(1000) NOT NULL DEFAULT '', `custom10` varchar(4000) NOT NULL DEFAULT '', PRIMARY KEY (`addons_id`)) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_banned` (`id` INT NOT NULL AUTO_INCREMENT, `ip` varchar(100) NOT NULL DEFAULT '', `ban_user` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `ip` (`ip`), KEY `ban_user` (`ban_user`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_call` (`call_id` INT NOT NULL AUTO_INCREMENT, `call_hunter` INT NOT NULL DEFAULT '0', `call_target` INT NOT NULL DEFAULT '0', `call_type` INT NOT NULL DEFAULT '0', `call_status` INT NOT NULL DEFAULT '0', `call_reason` INT NOT NULL DEFAULT '0', `call_method` INT NOT NULL DEFAULT '1', `call_paid` INT NOT NULL DEFAULT '0', `call_time` INT NOT NULL DEFAULT '0', `call_last` INT NOT NULL DEFAULT '0', `call_active` INT NOT NULL DEFAULT '0', `call_room` varchar(100) NOT NULL DEFAULT '', PRIMARY KEY (`call_id`), KEY `call_hunter` (`call_hunter`), KEY `call_target` (`call_target`), KEY `call_status` (`call_status`), KEY `call_time` (`call_time`), KEY `call_active` (`call_active`)) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_call_action` (`id` INT NOT NULL AUTO_INCREMENT, `call_room` INT NOT NULL DEFAULT '0', `hunter` INT NOT NULL DEFAULT '0', `target` INT NOT NULL DEFAULT '0', `action_time` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `target` (`target`), KEY `action_time` (`action_time`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_call_user` (`id` INT NOT NULL AUTO_INCREMENT, `croom` INT NOT NULL DEFAULT '0', `cuser` INT NOT NULL DEFAULT '0', `cdate` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `croom` (`croom`), KEY `cuser` (`cuser`), KEY `cdate` (`cdate`)) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_chat` (`post_id` INT NOT NULL AUTO_INCREMENT, `user_id` INT NOT NULL DEFAULT '0', `post_date` INT NOT NULL DEFAULT '0', `post_message` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `post_roomid` INT NOT NULL DEFAULT '1', `type` varchar(50) NOT NULL DEFAULT '', `log_rank` INT NOT NULL DEFAULT '999', `file` INT NOT NULL DEFAULT '0', `quser` INT NOT NULL DEFAULT '0', `qpost` INT NOT NULL DEFAULT '0', `pghost` INT NOT NULL DEFAULT '0', `syslog` INT NOT NULL DEFAULT '0', `log_uid` INT NOT NULL DEFAULT '0', `tid` INT NOT NULL DEFAULT '0', `tname` varchar(60) NOT NULL DEFAULT '', `custom` varchar(2000) NOT NULL DEFAULT '', PRIMARY KEY (`post_id`), KEY `post_roomid` (`post_roomid`), KEY `user_id` (`user_id`), KEY `post_date` (`post_date`), KEY `quser` (`quser`), KEY `qpost` (`qpost`), KEY `pghost` (`pghost`)) ENGINE=InnoDB AUTO_INCREMENT=9873 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_clean` (`id` INT NOT NULL AUTO_INCREMENT, `last_clean` INT NOT NULL DEFAULT '0', `last_expw` INT NOT NULL DEFAULT '0', `last_expm` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_console` (`id` INT NOT NULL AUTO_INCREMENT, `hunter` INT NOT NULL DEFAULT '0', `target` INT NOT NULL DEFAULT '0', `room` INT NOT NULL DEFAULT '0', `ctype` varchar(200) NOT NULL DEFAULT '', `crank` INT NOT NULL DEFAULT '0', `delay` INT NOT NULL DEFAULT '0', `reason` varchar(2000) NOT NULL DEFAULT '', `ctext` varchar(400) NOT NULL DEFAULT '', `custom` varchar(2000) NOT NULL DEFAULT '', `custom2` varchar(2000) NOT NULL DEFAULT '', `cdate` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `hunter` (`hunter`), KEY `target` (`target`), KEY `room` (`room`)) ENGINE=InnoDB AUTO_INCREMENT=340 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_contact` (`id` INT NOT NULL AUTO_INCREMENT, `cname` varchar(100) NOT NULL DEFAULT '0', `cmessage` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `cemail` varchar(100) NOT NULL DEFAULT '', `cip` varchar(100) NOT NULL DEFAULT '', `cdate` INT NOT NULL DEFAULT '0', `cview` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `cip` (`cip`), KEY `cview` (`cview`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_conversation` (`cid` varchar(30) NOT NULL DEFAULT '', `hunter` INT NOT NULL DEFAULT '0', `target` INT NOT NULL DEFAULT '0', `unread` INT NOT NULL DEFAULT '0', `cdate` INT NOT NULL DEFAULT '1', PRIMARY KEY (`cid`), KEY `hunter` (`hunter`), KEY `target` (`target`), KEY `cdate` (`cdate`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_data` (`id` INT NOT NULL AUTO_INCREMENT, `data_user` INT NOT NULL DEFAULT '0', `data_key` varchar(100) NOT NULL DEFAULT '', `data_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, PRIMARY KEY (`id`), KEY `data_user` (`data_user`), KEY `data_key` (`data_key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_exp` (`uid` INT NOT NULL AUTO_INCREMENT, `exp_current` INT NOT NULL DEFAULT '0', `exp_week` INT NOT NULL DEFAULT '0', `exp_month` INT NOT NULL DEFAULT '0', `exp_total` INT NOT NULL DEFAULT '0', PRIMARY KEY (`uid`)) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_filter` (`id` INT NOT NULL AUTO_INCREMENT, `word` varchar(100) NOT NULL DEFAULT '', `word_type` varchar(12) NOT NULL DEFAULT 'word', PRIMARY KEY (`id`), KEY `word_type` (`word_type`)) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_friends` (`id` INT NOT NULL AUTO_INCREMENT, `hunter` INT NOT NULL DEFAULT '0', `target` INT NOT NULL DEFAULT '0', `fstatus` INT NOT NULL DEFAULT '1', `viewed` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `hunter` (`hunter`), KEY `target` (`target`)) ENGINE=InnoDB AUTO_INCREMENT=457 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_gift` (`id` INT NOT NULL AUTO_INCREMENT, `gift_image` varchar(100) NOT NULL DEFAULT '', `gift_title` varchar(300) NOT NULL DEFAULT 'Gift', `gift_method` INT NOT NULL DEFAULT '1', `gift_cost` INT NOT NULL DEFAULT '0', `gift_rank` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `gift_rank` (`gift_rank`)) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_group_call` (`call_id` INT NOT NULL AUTO_INCREMENT, `call_name` varchar(100) NOT NULL DEFAULT '', `call_creator` INT NOT NULL DEFAULT '0', `call_type` INT NOT NULL DEFAULT '1', `call_active` INT NOT NULL DEFAULT '0', `call_time` INT NOT NULL DEFAULT '0', `call_paid` INT NOT NULL DEFAULT '0', `call_method` INT NOT NULL DEFAULT '0', `call_room` varchar(100) NOT NULL DEFAULT '', `call_password` varchar(40) NOT NULL DEFAULT '', `call_date` INT NOT NULL DEFAULT '0', `call_access` INT NOT NULL DEFAULT '0', PRIMARY KEY (`call_id`), KEY `call_date` (`call_date`)) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_history` (`id` INT NOT NULL AUTO_INCREMENT, `hunter` INT NOT NULL DEFAULT '0', `target` INT NOT NULL DEFAULT '0', `htype` varchar(30) NOT NULL DEFAULT '', `reason` varchar(2000) NOT NULL DEFAULT '', `delay` INT NOT NULL DEFAULT '0', `history_date` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `hunter` (`hunter`), KEY `target` (`target`), KEY `htype` (`htype`)) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_ignore` (`ignore_id` INT NOT NULL AUTO_INCREMENT, `ignorer` INT NOT NULL DEFAULT '0', `ignored` INT NOT NULL DEFAULT '0', `ignore_date` INT NOT NULL DEFAULT '0', PRIMARY KEY (`ignore_id`), KEY `ignorer` (`ignorer`), KEY `ignored` (`ignored`), KEY `ignore_date` (`ignore_date`)) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_login` (`id` INT NOT NULL AUTO_INCREMENT, `logip` varchar(50) NOT NULL DEFAULT '', `logdate` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `logip` (`logip`), KEY `logdate` (`logdate`)) ENGINE=InnoDB AUTO_INCREMENT=237 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_mail` (`id` INT NOT NULL AUTO_INCREMENT, `mail_user` INT NOT NULL DEFAULT '0', `mail_date` INT NOT NULL DEFAULT '0', `mail_type` varchar(50) NOT NULL DEFAULT '', PRIMARY KEY (`id`), KEY `mail_user` (`mail_user`), KEY `mail_date` (`mail_date`), KEY `mail_type` (`mail_type`)) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_name` (`id` INT NOT NULL AUTO_INCREMENT, `uid` INT NOT NULL DEFAULT '0', `uname` varchar(100) NOT NULL DEFAULT '', `udate` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `uid` (`uid`)) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_news` (`id` INT NOT NULL AUTO_INCREMENT, `news_comment` INT NOT NULL DEFAULT '1', `news_like` INT NOT NULL DEFAULT '1', `news_poster` INT NOT NULL DEFAULT '0', `news_message` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `news_file` varchar(1000) NOT NULL DEFAULT '', `news_file_type` varchar(20) NOT NULL DEFAULT '', `news_date` INT NOT NULL DEFAULT '1', PRIMARY KEY (`id`), KEY `news_date` (`news_date`)) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_news_like` (`id` INT NOT NULL AUTO_INCREMENT, `uid` INT NOT NULL DEFAULT '0', `liked_uid` INT NOT NULL DEFAULT '0', `like_type` INT NOT NULL DEFAULT '1', `like_post` INT NOT NULL DEFAULT '1', `like_date` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `uid` (`uid`), KEY `liked_uid` (`liked_uid`), KEY `like_date` (`like_date`)) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_news_reply` (`reply_id` INT NOT NULL AUTO_INCREMENT, `parent_id` INT NOT NULL DEFAULT '0', `reply_user` INT NOT NULL DEFAULT '0', `reply_date` INT NOT NULL DEFAULT '0', `reply_content` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `reply_uid` INT NOT NULL DEFAULT '0', PRIMARY KEY (`reply_id`), KEY `parent_id` (`parent_id`), KEY `reply_user` (`reply_user`), KEY `reply_date` (`reply_date`), KEY `reply_uid` (`reply_uid`)) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_notification` (`id` INT NOT NULL AUTO_INCREMENT, `notifier` INT NOT NULL DEFAULT '0', `notified` INT NOT NULL DEFAULT '0', `notify_type` varchar(30) NOT NULL DEFAULT '', `notify_date` INT NOT NULL DEFAULT '0', `notify_source` varchar(30) NOT NULL DEFAULT '', `notify_id` INT NOT NULL DEFAULT '0', `notify_rank` INT NOT NULL DEFAULT '0', `notify_delay` INT NOT NULL DEFAULT '0', `notify_reason` varchar(2000) NOT NULL DEFAULT '', `notify_view` INT NOT NULL DEFAULT '0', `notify_custom` varchar(2000) NOT NULL DEFAULT '', `notify_custom2` varchar(2000) NOT NULL DEFAULT '', `notify_icon` varchar(30) NOT NULL DEFAULT '', `notify_class` varchar(50) NOT NULL DEFAULT '', `notify_data` varchar(300) NOT NULL DEFAULT '', PRIMARY KEY (`id`), KEY `notifier` (`notifier`), KEY `notified` (`notified`), KEY `notify_date` (`notify_date`), KEY `notify_source` (`notify_source`), KEY `notify_id` (`notify_id`), KEY `notify_view` (`notify_view`)) ENGINE=InnoDB AUTO_INCREMENT=1216 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_page` (`page_id` INT NOT NULL AUTO_INCREMENT, `page_name` varchar(100) NOT NULL DEFAULT '', `page_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, PRIMARY KEY (`page_id`)) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_post` (`post_id` INT NOT NULL AUTO_INCREMENT, `post_comment` INT NOT NULL DEFAULT '1', `post_like` INT NOT NULL DEFAULT '1', `post_user` INT NOT NULL DEFAULT '0', `post_date` INT NOT NULL DEFAULT '0', `post_content` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `post_file` varchar(1000) NOT NULL DEFAULT '', `post_file_type` varchar(20) NOT NULL DEFAULT '', `post_actual` INT NOT NULL DEFAULT '0', PRIMARY KEY (`post_id`), KEY `post_user` (`post_user`), KEY `post_date` (`post_date`)) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_post_like` (`id` INT NOT NULL AUTO_INCREMENT, `uid` INT NOT NULL DEFAULT '0', `liked_uid` INT NOT NULL DEFAULT '0', `like_type` INT NOT NULL DEFAULT '1', `like_post` INT NOT NULL DEFAULT '1', `like_date` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `uid` (`uid`), KEY `liked_uid` (`liked_uid`), KEY `like_date` (`like_date`)) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_post_reply` (`reply_id` INT NOT NULL AUTO_INCREMENT, `parent_id` INT NOT NULL DEFAULT '0', `reply_user` INT NOT NULL DEFAULT '0', `reply_date` INT NOT NULL DEFAULT '0', `reply_content` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `reply_uid` INT NOT NULL DEFAULT '0', PRIMARY KEY (`reply_id`), KEY `parent_id` (`parent_id`), KEY `reply_user` (`reply_user`), KEY `reply_date` (`reply_date`), KEY `reply_uid` (`reply_uid`)) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_private` (`id` INT NOT NULL AUTO_INCREMENT, `time` INT NOT NULL DEFAULT '0', `message` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `hunter` INT NOT NULL DEFAULT '0', `target` INT NOT NULL DEFAULT '0', `status` INT NOT NULL DEFAULT '0', `view` INT NOT NULL DEFAULT '0', `file` INT NOT NULL DEFAULT '0', `qpost` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `hunter` (`hunter`), KEY `target` (`target`), KEY `time` (`time`), KEY `status` (`status`), KEY `qpost` (`qpost`)) ENGINE=InnoDB AUTO_INCREMENT=8452 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_pro_like` (`id` INT NOT NULL AUTO_INCREMENT, `hunter` INT NOT NULL DEFAULT '0', `target` INT NOT NULL DEFAULT '0', `like_date` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `hunter` (`hunter`), KEY `target` (`target`), KEY `like_date` (`like_date`)) ENGINE=InnoDB AUTO_INCREMENT=657 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_radio_stream` (`id` INT NOT NULL AUTO_INCREMENT, `stream_url` varchar(300) NOT NULL DEFAULT '', `stream_alias` varchar(50) NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_report` (`report_id` INT NOT NULL AUTO_INCREMENT, `report_type` INT NOT NULL DEFAULT '0', `report_user` INT NOT NULL DEFAULT '0', `report_target` INT NOT NULL DEFAULT '0', `report_post` INT NOT NULL DEFAULT '0', `report_reason` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `report_room` INT NOT NULL DEFAULT '0', `report_date` INT NOT NULL DEFAULT '0', PRIMARY KEY (`report_id`), KEY `report_user` (`report_user`), KEY `report_target` (`report_target`)) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_room_action` (`id` INT NOT NULL AUTO_INCREMENT, `action_room` INT NOT NULL DEFAULT '0', `action_user` INT NOT NULL DEFAULT '0', `action_muted` INT NOT NULL DEFAULT '0', `action_blocked` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `action_user` (`action_user`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_room_staff` (`id` INT NOT NULL AUTO_INCREMENT, `room_id` INT NOT NULL DEFAULT '0', `room_staff` INT NOT NULL DEFAULT '0', `room_rank` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `room_id` (`room_id`), KEY `room_staff` (`room_staff`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_rooms` (`room_id` INT NOT NULL AUTO_INCREMENT, `room_name` varchar(40) NOT NULL DEFAULT '', `topic` varchar(1000) NOT NULL DEFAULT '', `access` INT NOT NULL DEFAULT '0', `description` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `room_icon` varchar(100) NOT NULL DEFAULT 'default_room.png', `max_user` INT NOT NULL DEFAULT '0', `password` varchar(40) NOT NULL DEFAULT '', `room_system` INT NOT NULL DEFAULT '1', `room_action` INT NOT NULL DEFAULT '0', `room_player_id` INT NOT NULL DEFAULT '0', `room_creator` INT NOT NULL DEFAULT '0', `rcaction` INT NOT NULL DEFAULT '0', `rldelete` varchar(300) NOT NULL DEFAULT '', `rltime` INT NOT NULL DEFAULT '0', `pinned` INT NOT NULL DEFAULT '0', PRIMARY KEY (`room_id`), KEY `room_system` (`room_system`), KEY `room_action` (`room_action`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_setting` (`id` INT NOT NULL AUTO_INCREMENT, `title` varchar(200) NOT NULL DEFAULT 'Codychat', `site_description` varchar(600) NOT NULL DEFAULT '', `site_keyword` varchar(600) NOT NULL DEFAULT '', `login_page` varchar(50) NOT NULL DEFAULT 'Default', `dat` varchar(100) NOT NULL DEFAULT '', `system_id` INT NOT NULL DEFAULT '0', `registration` INT NOT NULL DEFAULT '1', `reg_act` INT NOT NULL DEFAULT '0', `reg_delay` INT NOT NULL DEFAULT '5', `maint_mode` INT NOT NULL DEFAULT '0', `use_bridge` INT NOT NULL DEFAULT '0', `use_lobby` INT NOT NULL DEFAULT '0', `use_logs` INT NOT NULL DEFAULT '12345', `allow_guest` INT NOT NULL DEFAULT '0', `guest_form` INT NOT NULL DEFAULT '0', `default_theme` varchar(30) NOT NULL DEFAULT 'Lite', `allow_theme` INT NOT NULL DEFAULT '100', `max_avatar` INT NOT NULL DEFAULT '1', `max_cover` INT NOT NULL DEFAULT '1', `max_ricon` INT NOT NULL DEFAULT '1', `file_weight` INT NOT NULL DEFAULT '2', `domain` varchar(100) NOT NULL DEFAULT '', `allow_avatar` INT NOT NULL DEFAULT '1', `allow_cover` INT NOT NULL DEFAULT '100', `allow_gcover` INT NOT NULL DEFAULT '100', `allow_name_color` INT NOT NULL DEFAULT '100', `allow_name_grad` INT NOT NULL DEFAULT '100', `allow_name_neon` INT NOT NULL DEFAULT '100', `allow_name_font` INT NOT NULL DEFAULT '100', `allow_pstyle` INT NOT NULL DEFAULT '100', `allow_history` INT NOT NULL DEFAULT '100', `allow_main` INT NOT NULL DEFAULT '0', `allow_private` INT NOT NULL DEFAULT '0', `allow_cupload` INT NOT NULL DEFAULT '100', `allow_pupload` INT NOT NULL DEFAULT '100', `allow_wupload` INT NOT NULL DEFAULT '100', `allow_direct` INT NOT NULL DEFAULT '0', `allow_room` INT NOT NULL DEFAULT '100', `allow_vroom` INT NOT NULL DEFAULT '100', `allow_quote` INT NOT NULL DEFAULT '100', `allow_pquote` INT NOT NULL DEFAULT '100', `allow_video` INT NOT NULL DEFAULT '100', `allow_audio` INT NOT NULL DEFAULT '100', `allow_zip` INT NOT NULL DEFAULT '100', `use_like` INT NOT NULL DEFAULT '0', `use_flag` INT NOT NULL DEFAULT '0', `use_gender` INT NOT NULL DEFAULT '0', `use_geo` INT NOT NULL DEFAULT '1', `version` varchar(5) NOT NULL DEFAULT '10', `bbfv` varchar(5) NOT NULL DEFAULT '1.0', `language` varchar(20) NOT NULL DEFAULT 'English', `activation` INT NOT NULL DEFAULT '0', `use_wall` INT NOT NULL DEFAULT '1', `timezone` varchar(60) NOT NULL DEFAULT 'America/Toronto', `boom` varchar(50) NOT NULL DEFAULT '', `min_age` INT NOT NULL DEFAULT '14', `allow_colors` INT NOT NULL DEFAULT '100', `allow_grad` INT NOT NULL DEFAULT '100', `allow_neon` INT NOT NULL DEFAULT '100', `allow_font` INT NOT NULL DEFAULT '100', `allow_mood` INT NOT NULL DEFAULT '100', `allow_scontent` INT NOT NULL DEFAULT '100', `allow_rnews` INT NOT NULL DEFAULT '100', `allow_about` INT NOT NULL DEFAULT '100', `allow_report` INT NOT NULL DEFAULT '100', `emo_plus` INT NOT NULL DEFAULT '100', `speed` INT NOT NULL DEFAULT '3000', `player_id` INT NOT NULL DEFAULT '0', `max_main` INT NOT NULL DEFAULT '300', `max_private` INT NOT NULL DEFAULT '200', `word_action` INT NOT NULL DEFAULT '0', `word_delay` INT NOT NULL DEFAULT '5', `spam_action` INT NOT NULL DEFAULT '0', `spam_delay` INT NOT NULL DEFAULT '60', `flood_action` INT NOT NULL DEFAULT '1', `flood_delay` INT NOT NULL DEFAULT '5', `vpn_delay` INT NOT NULL DEFAULT '5', `email_filter` INT NOT NULL DEFAULT '0', `max_username` INT NOT NULL DEFAULT '18', `chat_delete` INT NOT NULL DEFAULT '0', `private_delete` INT NOT NULL DEFAULT '0', `wall_delete` INT NOT NULL DEFAULT '0', `member_delete` INT NOT NULL DEFAULT '0', `room_delete` INT NOT NULL DEFAULT '0', `ignore_delete` INT NOT NULL DEFAULT '0', `max_offcount` INT NOT NULL DEFAULT '0', `site_email` varchar(200) NOT NULL DEFAULT 'yoursiteemail@email.com', `email_from` varchar(100) NOT NULL DEFAULT 'Codychat', `mail_type` varchar(10) NOT NULL DEFAULT 'mail', `smtp_host` varchar(100) NOT NULL DEFAULT '', `smtp_username` varchar(100) NOT NULL DEFAULT '', `smtp_password` varchar(100) NOT NULL DEFAULT '', `smtp_port` varchar(10) NOT NULL DEFAULT '465', `smtp_type` varchar(10) NOT NULL DEFAULT 'tls', `allow_name` INT NOT NULL DEFAULT '100', `act_delay` INT NOT NULL DEFAULT '0', `cookie_law` INT NOT NULL DEFAULT '0', `use_recapt` INT NOT NULL DEFAULT '0', `recapt_key` varchar(100) NOT NULL DEFAULT '', `recapt_secret` varchar(100) NOT NULL DEFAULT '', `can_raction` INT NOT NULL DEFAULT '100', `can_mute` INT NOT NULL DEFAULT '100', `can_warn` INT NOT NULL DEFAULT '100', `can_kick` INT NOT NULL DEFAULT '100', `can_ghost` INT NOT NULL DEFAULT '100', `can_ban` INT NOT NULL DEFAULT '100', `can_delete` INT NOT NULL DEFAULT '100', `can_modavat` INT NOT NULL DEFAULT '100', `can_modcover` INT NOT NULL DEFAULT '100', `can_modmood` INT NOT NULL DEFAULT '100', `can_modabout` INT NOT NULL DEFAULT '100', `can_modcolor` INT NOT NULL DEFAULT '100', `can_modname` INT NOT NULL DEFAULT '100', `can_modemail` INT NOT NULL DEFAULT '100', `can_modpass` INT NOT NULL DEFAULT '100', `can_modblock` INT NOT NULL DEFAULT '100', `can_modvpn` INT NOT NULL DEFAULT '100', `can_verify` INT NOT NULL DEFAULT '100', `can_vip` INT NOT NULL DEFAULT '100', `can_vemail` INT NOT NULL DEFAULT '100', `can_vghost` INT NOT NULL DEFAULT '999', `can_vother` INT NOT NULL DEFAULT '100', `can_vname` INT NOT NULL DEFAULT '100', `can_vhistory` INT NOT NULL DEFAULT '100', `can_note` INT NOT NULL DEFAULT '100', `can_news` INT NOT NULL DEFAULT '100', `can_rank` INT NOT NULL DEFAULT '100', `can_auth` INT NOT NULL DEFAULT '100', `can_inv` INT NOT NULL DEFAULT '100', `can_clear` INT NOT NULL DEFAULT '100', `can_bpriv` INT NOT NULL DEFAULT '100', `can_rpass` INT NOT NULL DEFAULT '100', `can_topic` INT NOT NULL DEFAULT '100', `can_content` INT NOT NULL DEFAULT '100', `can_maddons` INT NOT NULL DEFAULT '100', `can_mroom` INT NOT NULL DEFAULT '100', `can_mfilter` INT NOT NULL DEFAULT '100', `can_dj` INT NOT NULL DEFAULT '100', `can_cuser` INT NOT NULL DEFAULT '100', `can_mip` INT NOT NULL DEFAULT '100', `can_mlogs` INT NOT NULL DEFAULT '100', `can_mplay` INT NOT NULL DEFAULT '100', `can_mcontact` INT NOT NULL DEFAULT '100', `use_vpn` INT NOT NULL DEFAULT '0', `vpn_key` varchar(80) NOT NULL DEFAULT '', `coppa` INT NOT NULL DEFAULT '0', `redis_status` INT NOT NULL DEFAULT '0', `max_flood` INT NOT NULL DEFAULT '6', `max_emo` INT NOT NULL DEFAULT '10', `max_room` INT NOT NULL DEFAULT '1', `max_reg` INT NOT NULL DEFAULT '5', `max_greg` INT NOT NULL DEFAULT '25', `curset` INT NOT NULL DEFAULT '0', `can_rclear` INT NOT NULL DEFAULT '6', `can_rlogs` INT NOT NULL DEFAULT '6', `use_level` INT NOT NULL DEFAULT '0', `level_mode` INT NOT NULL DEFAULT '10', `exp_chat` INT NOT NULL DEFAULT '1', `exp_priv` INT NOT NULL DEFAULT '1', `exp_gift` INT NOT NULL DEFAULT '1', `exp_post` INT NOT NULL DEFAULT '1', `use_rate` INT NOT NULL DEFAULT '0', `rate_limit` INT NOT NULL DEFAULT '50', `word_proof` INT NOT NULL DEFAULT '100', `use_badge` INT NOT NULL DEFAULT '0', `bachat` INT NOT NULL DEFAULT '10', `bagift` INT NOT NULL DEFAULT '10', `balike` INT NOT NULL DEFAULT '10', `bafriend` INT NOT NULL DEFAULT '10', `baruby` INT NOT NULL DEFAULT '100', `bagold` INT NOT NULL DEFAULT '5000', `babeat` INT NOT NULL DEFAULT '1000', `use_gift` INT NOT NULL DEFAULT '0', `use_wallet` INT NOT NULL DEFAULT '0', `can_vwallet` INT NOT NULL DEFAULT '100', `can_swallet` INT NOT NULL DEFAULT '100', `can_ruby` INT NOT NULL DEFAULT '100', `ruby_delay` INT NOT NULL DEFAULT '60', `ruby_base` INT NOT NULL DEFAULT '0', `can_gold` INT NOT NULL DEFAULT '100', `gold_delay` INT NOT NULL DEFAULT '2', `gold_base` INT NOT NULL DEFAULT '0', `use_call` INT NOT NULL DEFAULT '0', `can_acall` INT NOT NULL DEFAULT '100', `can_vcall` INT NOT NULL DEFAULT '100', `call_appid` varchar(50) NOT NULL DEFAULT '', `call_secret` varchar(50) NOT NULL DEFAULT '', `call_max` INT NOT NULL DEFAULT '60', `call_method` INT NOT NULL DEFAULT '1', `call_cost` INT NOT NULL DEFAULT '0', `live_url` varchar(60) NOT NULL DEFAULT '', `live_appid` varchar(50) NOT NULL DEFAULT '', `live_secret` varchar(100) NOT NULL DEFAULT '', `use_app` INT NOT NULL DEFAULT '0', `app_name` varchar(30) NOT NULL DEFAULT 'Chat', `app_color` varchar(10) NOT NULL DEFAULT '#000000', `openai_key` varchar(200) NOT NULL DEFAULT '', `mod_cat` varchar(200) NOT NULL DEFAULT '', `img_mod` INT NOT NULL DEFAULT '0', `can_gcall` INT NOT NULL DEFAULT '100', `can_mgcall` INT NOT NULL DEFAULT '100', `max_gcall` INT NOT NULL DEFAULT '180', `can_agcall` INT NOT NULL DEFAULT '100', `can_cgcall` INT NOT NULL DEFAULT '100', `can_vgcall` INT NOT NULL DEFAULT '100',`log_mode` INT NOT NULL DEFAULT '1',`can_pmusic` INT NOT NULL DEFAULT '1',`allow_pmusic` INT NOT NULL DEFAULT '100', `left_mode` INT NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_temp` (`id` INT NOT NULL AUTO_INCREMENT, `temp_user` INT NOT NULL DEFAULT '0', `temp_key` varchar(200) NOT NULL DEFAULT '', `temp_date` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `temp_user` (`temp_user`), KEY `temp_key` (`temp_key`), KEY `temp_date` (`temp_date`)) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_upload` (`id` INT NOT NULL AUTO_INCREMENT, `file_name` varchar(300) NOT NULL DEFAULT '', `file_key` varchar(100) NOT NULL DEFAULT '', `date_sent` INT NOT NULL DEFAULT '0', `file_user` INT NOT NULL DEFAULT '0', `file_zone` varchar(30) NOT NULL DEFAULT '1', `file_type` varchar(30) NOT NULL DEFAULT '', `file_complete` INT NOT NULL DEFAULT '1', `relative_post` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `date_sent` (`date_sent`), KEY `file_zone` (`file_zone`), KEY `file_complete` (`file_complete`)) ENGINE=InnoDB AUTO_INCREMENT=694 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_users` (`user_id` INT NOT NULL AUTO_INCREMENT, `user_name` varchar(60) NOT NULL DEFAULT '', `user_password` varchar(60) NOT NULL DEFAULT '', `user_email` varchar(80) NOT NULL DEFAULT '', `user_smail` varchar(80) NOT NULL DEFAULT '', `sub_id` varchar(50) NOT NULL DEFAULT '', `user_ip` varchar(50) NOT NULL DEFAULT '', `user_auth` INT NOT NULL DEFAULT '0', `user_join` INT NOT NULL DEFAULT '0', `user_move` INT NOT NULL DEFAULT '0', `last_action` INT NOT NULL DEFAULT '0', `user_beat` INT NOT NULL DEFAULT '0', `user_language` varchar(30) NOT NULL DEFAULT 'English', `user_timezone` varchar(60) NOT NULL DEFAULT 'America/Toronto', `user_status` INT NOT NULL DEFAULT '1', `user_color` varchar(20) NOT NULL DEFAULT 'user', `user_pstyle` varchar(100) NOT NULL DEFAULT '', `user_font` varchar(10) NOT NULL DEFAULT '', `bccolor` varchar(10) NOT NULL DEFAULT '', `bcbold` varchar(10) NOT NULL DEFAULT '', `bcfont` varchar(10) NOT NULL DEFAULT '', `user_rank` INT NOT NULL DEFAULT '1', `user_level` INT NOT NULL DEFAULT '1', `vip_end` INT NOT NULL DEFAULT '0', `user_dj` INT NOT NULL DEFAULT '0', `user_onair` INT NOT NULL DEFAULT '0', `user_roomid` INT NOT NULL DEFAULT '1', `user_theme` varchar(30) NOT NULL DEFAULT 'system', `user_sex` INT NOT NULL DEFAULT '0', `user_age` INT NOT NULL DEFAULT '0', `user_tumb` varchar(200) NOT NULL DEFAULT 'default_avatar.png', `user_relation` varchar(50) NOT NULL DEFAULT '', `user_pmusic` varchar(200) NOT NULL DEFAULT '', `pmusic` INT NOT NULL DEFAULT '0', `user_birth` DATE NULL, `user_cover` varchar(100) NOT NULL DEFAULT '', `user_sound` INT NOT NULL DEFAULT '12345', `user_verify` INT NOT NULL DEFAULT '0', `valid_key` varchar(64) NOT NULL DEFAULT '', `country` varchar(10) NOT NULL DEFAULT '', `session_id` INT NOT NULL DEFAULT '1', `pcount` INT NOT NULL DEFAULT '0', `user_news` INT NOT NULL DEFAULT '0', `user_ghost` INT NOT NULL DEFAULT '0', `user_mute` INT NOT NULL DEFAULT '0', `user_rmute` INT NOT NULL DEFAULT '0', `user_mmute` INT NOT NULL DEFAULT '0', `user_pmute` INT NOT NULL DEFAULT '0', `user_banned` INT NOT NULL DEFAULT '0', `user_kick` INT NOT NULL DEFAULT '0', `kick_msg` varchar(300) NOT NULL DEFAULT '', `warn_msg` varchar(500) NOT NULL DEFAULT '', `ban_msg` varchar(300) NOT NULL DEFAULT '', `user_role` INT NOT NULL DEFAULT '0', `user_action` INT NOT NULL DEFAULT '0', `room_mute` INT NOT NULL DEFAULT '0', `user_mood` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `user_bot` INT NOT NULL DEFAULT '0', `naction` INT NOT NULL DEFAULT '1', `user_private` INT NOT NULL DEFAULT '1', `user_delete` INT NOT NULL DEFAULT '0', `user_gold` INT NOT NULL DEFAULT '0', `user_sgold` INT NOT NULL DEFAULT '0', `last_gold` INT NOT NULL DEFAULT '0', `user_ruby` INT NOT NULL DEFAULT '0', `user_sruby` INT NOT NULL DEFAULT '0', `last_ruby` INT NOT NULL DEFAULT '0', `pdel` varchar(300) NOT NULL DEFAULT '', `pdeltime` INT NOT NULL DEFAULT '0', `ulogin` INT NOT NULL DEFAULT '0', `uvpn` INT NOT NULL DEFAULT '1', `bupload` INT NOT NULL DEFAULT '0', `bcall` INT NOT NULL DEFAULT '0', `bnews` INT NOT NULL DEFAULT '0', `ashare` INT NOT NULL DEFAULT '1', `sshare` INT NOT NULL DEFAULT '1', `lshare` INT NOT NULL DEFAULT '1', `fshare` INT NOT NULL DEFAULT '1', `gshare` INT NOT NULL DEFAULT '1', `ucall` INT NOT NULL DEFAULT '0', `user_call` INT NOT NULL DEFAULT '1', `ufriend` INT NOT NULL DEFAULT '1', `ugcall` INT NOT NULL DEFAULT '0', `user_wall` INT NOT NULL DEFAULT '0', `user_bubble` INT NOT NULL DEFAULT '0', PRIMARY KEY (`user_id`), KEY `user_ip` (`user_ip`), KEY `user_email` (`user_email`), KEY `user_smail` (`user_smail`), KEY `user_roomid` (`user_roomid`), KEY `last_action` (`last_action`), KEY `user_rank` (`user_rank`), KEY `user_bot` (`user_bot`), KEY `user_status` (`user_status`), KEY `user_delete` (`user_delete`), KEY `vip_end` (`vip_end`)) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_users_data` (`uid` INT NOT NULL AUTO_INCREMENT, `badge_auth` INT NOT NULL DEFAULT '0', `badge_member` INT NOT NULL DEFAULT '0', `badge_chat` INT NOT NULL DEFAULT '0', `badge_top` INT NOT NULL DEFAULT '0', `badge_qtop` INT NOT NULL DEFAULT '0', `badge_ruby` INT NOT NULL DEFAULT '0', `badge_beat` INT NOT NULL DEFAULT '0', `badge_gold` INT NOT NULL DEFAULT '0', `badge_like` INT NOT NULL DEFAULT '0', `badge_friend` INT NOT NULL DEFAULT '0', `badge_gift` INT NOT NULL DEFAULT '0', `user_about` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', `user_note` varchar(4000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '', PRIMARY KEY (`uid`)) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_users_gift` (`id` INT NOT NULL AUTO_INCREMENT, `target` INT NOT NULL DEFAULT '0', `gift` INT NOT NULL DEFAULT '0', `gift_count` INT NOT NULL DEFAULT '1', `gift_date` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `target` (`target`), KEY `gift` (`gift`), KEY `gift_date` (`gift_date`)) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_style` (`id` INT NOT NULL AUTO_INCREMENT, `style_ref` VARCHAR(100) NOT NULL, `style_name` VARCHAR(100) NOT NULL DEFAULT '', `style_active` TINYINT NOT NULL DEFAULT '1', `style_wrap` TEXT, `style_top` TEXT, `style_avatar` TEXT, `style_menu` TEXT, `style_content` TEXT, `style_custom` TEXT, PRIMARY KEY (`id`), UNIQUE KEY `style_ref` (`style_ref`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_vip` (`id` INT NOT NULL AUTO_INCREMENT, `userid` INT NOT NULL DEFAULT '11', `userp` varchar(50) NOT NULL DEFAULT '', `plan` varchar(20) NOT NULL DEFAULT '', `price` varchar(20) NOT NULL DEFAULT '', `vdate` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `userid` (`userid`)) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$mysqli->query("CREATE TABLE `boom_vpn` (`id` INT NOT NULL AUTO_INCREMENT, `vip` varchar(100) NOT NULL DEFAULT '0', `vtype` INT NOT NULL DEFAULT '0', `vdate` INT NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `vip` (`vip`), KEY `vtype` (`vtype`), KEY `vdate` (`vdate`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

$database_write = "<?php\r\n" .
"// base system prefix\r\n" .
"define('BOOM_PREFIX', '$prefix');\r\n\r\n" .
"// optional base domain\r\n" .
"define('BOOM_DOMAIN', '$domain/');\r\n\r\n" .
"// default redis configuration\r\n" .
"define('REDIS_IP', '127.0.0.1');\r\n" .
"define('REDIS_PORT', 6379);\r\n" .
"define('REDIS_TIMEOUT', 0.2);\r\n" .
"define('REDIS_PASS', '');\r\n\r\n" .
"// you can edit these lines to configure new setting for your chat\r\n" .
"define('BOOM_DHOST', '" . $_POST["db_host"] . "');\r\n" .
"define('BOOM_DUSER', '" . $_POST["db_user"] . "');\r\n" .
"define('BOOM_DPASS', '" . $_POST["db_pass"] . "');\r\n" .
"define('BOOM_DNAME', '" . $_POST["db_name"] . "');\r\n\r\n" .
"// base system main path do not modify\r\n" .
"define('BOOM_PATH', dirname(__DIR__));\r\n\r\n" .
"// do not modify those variables\r\n" .
"define('BOOM_CRYPT', '" . $encrypt . "');\r\n" .
"define('BOOM_INSTALL', 1);\r\n" .
"define('BOOM', 1);\r\n" .
"?>";

$database_file = fopen(BOOM_PATH . "/system/database.php", "w+");
fwrite($database_file, $database_write);
fclose($database_file);

$settings_write = "<?php\r\n";
$settings_write .= "\$setting['id'] = '1';\r\n";
$settings_write .= "\$setting['title'] = '$title';\r\n";
$settings_write .= "\$setting['site_description'] = '';\r\n";
$settings_write .= "\$setting['site_keyword'] = '';\r\n";
$settings_write .= "\$setting['login_page'] = 'Default';\r\n";
$settings_write .= "\$setting['dat'] = '$password';\r\n";
$settings_write .= "\$setting['system_id'] = '2';\r\n";
$settings_write .= "\$setting['registration'] = '1';\r\n";
$settings_write .= "\$setting['reg_act'] = '0';\r\n";
$settings_write .= "\$setting['reg_delay'] = '5';\r\n";
$settings_write .= "\$setting['maint_mode'] = '0';\r\n";
$settings_write .= "\$setting['use_bridge'] = '0';\r\n";
$settings_write .= "\$setting['use_lobby'] = '0';\r\n";
$settings_write .= "\$setting['use_logs'] = '123';\r\n";
$settings_write .= "\$setting['allow_guest'] = '0';\r\n";
$settings_write .= "\$setting['guest_form'] = '0';\r\n";
$settings_write .= "\$setting['default_theme'] = 'Dark';\r\n";
$settings_write .= "\$setting['allow_theme'] = '50';\r\n";
$settings_write .= "\$setting['max_avatar'] = '4';\r\n";
$settings_write .= "\$setting['max_cover'] = '9';\r\n";
$settings_write .= "\$setting['max_ricon'] = '6';\r\n";
$settings_write .= "\$setting['file_weight'] = '10';\r\n";
$settings_write .= "\$setting['domain'] = '$domain';\r\n";
$settings_write .= "\$setting['allow_avatar'] = '1';\r\n";
$settings_write .= "\$setting['allow_cover'] = '100';\r\n";
$settings_write .= "\$setting['allow_gcover'] = '100';\r\n";
$settings_write .= "\$setting['allow_name_color'] = '50';\r\n";
$settings_write .= "\$setting['allow_name_grad'] = '50';\r\n";
$settings_write .= "\$setting['allow_name_neon'] = '50';\r\n";
$settings_write .= "\$setting['allow_name_font'] = '50';\r\n";
$settings_write .= "\$setting['allow_pstyle'] = '50';\r\n";
$settings_write .= "\$setting['allow_history'] = '80';\r\n";
$settings_write .= "\$setting['allow_main'] = '0';\r\n";
$settings_write .= "\$setting['allow_private'] = '1';\r\n";
$settings_write .= "\$setting['allow_cupload'] = '100';\r\n";
$settings_write .= "\$setting['allow_pupload'] = '100';\r\n";
$settings_write .= "\$setting['allow_wupload'] = '100';\r\n";
$settings_write .= "\$setting['allow_direct'] = '70';\r\n";
$settings_write .= "\$setting['allow_room'] = '90';\r\n";
$settings_write .= "\$setting['allow_vroom'] = '90';\r\n";
$settings_write .= "\$setting['allow_quote'] = '50';\r\n";
$settings_write .= "\$setting['allow_pquote'] = '50';\r\n";
$settings_write .= "\$setting['allow_video'] = '100';\r\n";
$settings_write .= "\$setting['allow_audio'] = '100';\r\n";
$settings_write .= "\$setting['allow_zip'] = '100';\r\n";
$settings_write .= "\$setting['use_like'] = '1';\r\n";
$settings_write .= "\$setting['use_flag'] = '1';\r\n";
$settings_write .= "\$setting['use_gender'] = '1';\r\n";
$settings_write .= "\$setting['use_geo'] = '1';\r\n";
$settings_write .= "\$setting['version'] = '10';\r\n";
$settings_write .= "\$setting['bbfv'] = '1.03';\r\n";
$settings_write .= "\$setting['language'] = '$language';\r\n";
$settings_write .= "\$setting['activation'] = '0';\r\n";
$settings_write .= "\$setting['use_wall'] = '1';\r\n";
$settings_write .= "\$setting['timezone'] = 'America/Toronto';\r\n";
$settings_write .= "\$setting['boom'] = 'nulledbyblackhunterandfxntxm';\r\n";
$settings_write .= "\$setting['min_age'] = '14';\r\n";
$settings_write .= "\$setting['allow_colors'] = '50';\r\n";
$settings_write .= "\$setting['allow_grad'] = '50';\r\n";
$settings_write .= "\$setting['allow_neon'] = '50';\r\n";
$settings_write .= "\$setting['allow_font'] = '50';\r\n";
$settings_write .= "\$setting['allow_mood'] = '100';\r\n";
$settings_write .= "\$setting['allow_scontent'] = '70';\r\n";
$settings_write .= "\$setting['allow_rnews'] = '1';\r\n";
$settings_write .= "\$setting['allow_about'] = '100';\r\n";
$settings_write .= "\$setting['allow_report'] = '1';\r\n";
$settings_write .= "\$setting['emo_plus'] = '50';\r\n";
$settings_write .= "\$setting['speed'] = '3000';\r\n";
$settings_write .= "\$setting['player_id'] = '1';\r\n";
$settings_write .= "\$setting['max_main'] = '600';\r\n";
$settings_write .= "\$setting['max_private'] = '500';\r\n";
$settings_write .= "\$setting['word_action'] = '2';\r\n";
$settings_write .= "\$setting['word_delay'] = '30';\r\n";
$settings_write .= "\$setting['spam_action'] = '0';\r\n";
$settings_write .= "\$setting['spam_delay'] = '60';\r\n";
$settings_write .= "\$setting['flood_action'] = '1';\r\n";
$settings_write .= "\$setting['flood_delay'] = '5';\r\n";
$settings_write .= "\$setting['vpn_delay'] = '5';\r\n";
$settings_write .= "\$setting['email_filter'] = '0';\r\n";
$settings_write .= "\$setting['max_username'] = '18';\r\n";
$settings_write .= "\$setting['chat_delete'] = '0';\r\n";
$settings_write .= "\$setting['private_delete'] = '0';\r\n";
$settings_write .= "\$setting['wall_delete'] = '0';\r\n";
$settings_write .= "\$setting['member_delete'] = '0';\r\n";
$settings_write .= "\$setting['room_delete'] = '0';\r\n";
$settings_write .= "\$setting['ignore_delete'] = '0';\r\n";
$settings_write .= "\$setting['max_offcount'] = '10';\r\n";
$settings_write .= "\$setting['site_email'] = 'yoursiteemail@email.com';\r\n";
$settings_write .= "\$setting['email_from'] = 'Codychat';\r\n";
$settings_write .= "\$setting['mail_type'] = 'mail';\r\n";
$settings_write .= "\$setting['smtp_host'] = '';\r\n";
$settings_write .= "\$setting['smtp_username'] = '';\r\n";
$settings_write .= "\$setting['smtp_password'] = '';\r\n";
$settings_write .= "\$setting['smtp_port'] = '465';\r\n";
$settings_write .= "\$setting['smtp_type'] = 'tls';\r\n";
$settings_write .= "\$setting['allow_name'] = '100';\r\n";
$settings_write .= "\$setting['act_delay'] = '0';\r\n";
$settings_write .= "\$setting['cookie_law'] = '1';\r\n";
$settings_write .= "\$setting['use_recapt'] = '0';\r\n";
$settings_write .= "\$setting['recapt_key'] = '';\r\n";
$settings_write .= "\$setting['recapt_secret'] = '';\r\n";
$settings_write .= "\$setting['can_raction'] = '90';\r\n";
$settings_write .= "\$setting['can_mute'] = '90';\r\n";
$settings_write .= "\$setting['can_warn'] = '90';\r\n";
$settings_write .= "\$setting['can_kick'] = '90';\r\n";
$settings_write .= "\$setting['can_ghost'] = '90';\r\n";
$settings_write .= "\$setting['can_ban'] = '90';\r\n";
$settings_write .= "\$setting['can_delete'] = '90';\r\n";
$settings_write .= "\$setting['can_modavat'] = '90';\r\n";
$settings_write .= "\$setting['can_modcover'] = '90';\r\n";
$settings_write .= "\$setting['can_modmood'] = '90';\r\n";
$settings_write .= "\$setting['can_modabout'] = '90';\r\n";
$settings_write .= "\$setting['can_modcolor'] = '90';\r\n";
$settings_write .= "\$setting['can_modname'] = '90';\r\n";
$settings_write .= "\$setting['can_modemail'] = '90';\r\n";
$settings_write .= "\$setting['can_modpass'] = '90';\r\n";
$settings_write .= "\$setting['can_modblock'] = '90';\r\n";
$settings_write .= "\$setting['can_modvpn'] = '90';\r\n";
$settings_write .= "\$setting['can_verify'] = '90';\r\n";
$settings_write .= "\$setting['can_vip'] = '90';\r\n";
$settings_write .= "\$setting['can_vemail'] = '90';\r\n";
$settings_write .= "\$setting['can_vghost'] = '90';\r\n";
$settings_write .= "\$setting['can_vother'] = '90';\r\n";
$settings_write .= "\$setting['can_vname'] = '90';\r\n";
$settings_write .= "\$setting['can_vhistory'] = '90';\r\n";
$settings_write .= "\$setting['can_note'] = '90';\r\n";
$settings_write .= "\$setting['can_news'] = '90';\r\n";
$settings_write .= "\$setting['can_rank'] = '90';\r\n";
$settings_write .= "\$setting['can_auth'] = '100';\r\n";
$settings_write .= "\$setting['can_inv'] = '100';\r\n";
$settings_write .= "\$setting['can_clear'] = '80';\r\n";
$settings_write .= "\$setting['can_bpriv'] = '90';\r\n";
$settings_write .= "\$setting['can_rpass'] = '80';\r\n";
$settings_write .= "\$setting['can_topic'] = '80';\r\n";
$settings_write .= "\$setting['can_content'] = '90';\r\n";
$settings_write .= "\$setting['can_maddons'] = '90';\r\n";
$settings_write .= "\$setting['can_mroom'] = '90';\r\n";
$settings_write .= "\$setting['can_mfilter'] = '90';\r\n";
$settings_write .= "\$setting['can_dj'] = '90';\r\n";
$settings_write .= "\$setting['can_cuser'] = '90';\r\n";
$settings_write .= "\$setting['can_mip'] = '90';\r\n";
$settings_write .= "\$setting['can_mlogs'] = '90';\r\n";
$settings_write .= "\$setting['can_mplay'] = '90';\r\n";
$settings_write .= "\$setting['can_mcontact'] = '90';\r\n";
$settings_write .= "\$setting['use_vpn'] = '0';\r\n";
$settings_write .= "\$setting['vpn_key'] = '';\r\n";
$settings_write .= "\$setting['coppa'] = '0';\r\n";
$settings_write .= "\$setting['redis_status'] = '0';\r\n";
$settings_write .= "\$setting['max_flood'] = '6';\r\n";
$settings_write .= "\$setting['max_emo'] = '10';\r\n";
$settings_write .= "\$setting['max_room'] = '1';\r\n";
$settings_write .= "\$setting['max_reg'] = '5';\r\n";
$settings_write .= "\$setting['max_greg'] = '25';\r\n";
$settings_write .= "\$setting['curset'] = '46';\r\n";
$settings_write .= "\$setting['privload'] = '1';\r\n";
$settings_write .= "\$setting['can_rclear'] = '9';\r\n";
$settings_write .= "\$setting['can_rlogs'] = '6';\r\n";
$settings_write .= "\$setting['use_level'] = '1';\r\n";
$settings_write .= "\$setting['level_mode'] = '5';\r\n";
$settings_write .= "\$setting['exp_chat'] = '1';\r\n";
$settings_write .= "\$setting['exp_priv'] = '1';\r\n";
$settings_write .= "\$setting['exp_gift'] = '1';\r\n";
$settings_write .= "\$setting['exp_post'] = '1';\r\n";
$settings_write .= "\$setting['use_rate'] = '0';\r\n";
$settings_write .= "\$setting['rate_limit'] = '50';\r\n";
$settings_write .= "\$setting['word_proof'] = '90';\r\n";
$settings_write .= "\$setting['use_badge'] = '1';\r\n";
$settings_write .= "\$setting['bachat'] = '10';\r\n";
$settings_write .= "\$setting['bagift'] = '10';\r\n";
$settings_write .= "\$setting['balike'] = '10';\r\n";
$settings_write .= "\$setting['bafriend'] = '10';\r\n";
$settings_write .= "\$setting['baruby'] = '100';\r\n";
$settings_write .= "\$setting['bagold'] = '5000';\r\n";
$settings_write .= "\$setting['babeat'] = '1000';\r\n";
$settings_write .= "\$setting['use_gift'] = '1';\r\n";
$settings_write .= "\$setting['use_wallet'] = '1';\r\n";
$settings_write .= "\$setting['can_vwallet'] = '70';\r\n";
$settings_write .= "\$setting['can_swallet'] = '1';\r\n";
$settings_write .= "\$setting['can_ruby'] = '1';\r\n";
$settings_write .= "\$setting['ruby_delay'] = '60';\r\n";
$settings_write .= "\$setting['ruby_base'] = '1';\r\n";
$settings_write .= "\$setting['can_gold'] = '1';\r\n";
$settings_write .= "\$setting['gold_delay'] = '2';\r\n";
$settings_write .= "\$setting['gold_base'] = '2';\r\n";
$settings_write .= "\$setting['use_call'] = '2';\r\n";
$settings_write .= "\$setting['can_acall'] = '100';\r\n";
$settings_write .= "\$setting['can_vcall'] = '100';\r\n";
$settings_write .= "\$setting['call_appid'] = '';\r\n";
$settings_write .= "\$setting['call_secret'] = '';\r\n";
$settings_write .= "\$setting['call_max'] = '60';\r\n";
$settings_write .= "\$setting['call_method'] = '1';\r\n";
$settings_write .= "\$setting['call_cost'] = '1';\r\n";
$settings_write .= "\$setting['live_url'] = '';\r\n";
$settings_write .= "\$setting['live_appid'] = '';\r\n";
$settings_write .= "\$setting['live_secret'] = '';\r\n";
$settings_write .= "\$setting['use_app'] = '1';\r\n";
$settings_write .= "\$setting['app_name'] = 'Chat';\r\n";
$settings_write .= "\$setting['app_color'] = '#000000';\r\n";
$settings_write .= "\$setting['openai_key'] = '';\r\n";
$settings_write .= "\$setting['mod_cat'] = '';\r\n";
$settings_write .= "\$setting['img_mod'] = '0';\r\n";
$settings_write .= "\$setting['can_gcall'] = '100';\r\n";
$settings_write .= "\$setting['can_mgcall'] = '100';\r\n";
$settings_write .= "\$setting['max_gcall'] = '180';\r\n";
$settings_write .= "\$setting['can_agcall'] = '100';\r\n";
$settings_write .= "\$setting['can_vgcall'] = '100';\r\n";
$settings_write .= "\$setting['log_mode'] = '1';\r\n";
$settings_write .= "\$setting['can_pmusic'] = '1';\r\n";
$settings_write .= "\$setting['allow_pmusic'] = '100';\r\n";
$settings_write .= "\$setting['left_mode'] = '1';\r\n";
$settings_write .= "?>";

$settings_file = fopen(BOOM_PATH . "/system/settings.php", "w+");
fwrite($settings_file, $settings_write);
fclose($settings_file);

	$mysqli->query("INSERT INTO `boom_setting` (id, title, domain, language, default_theme, system_id, boom) VALUES (1, '" . $title . "', '" . $domain . "', '" . $language . "', 'Dark', 2, 'nulledbyblackhunterandfxntxm')");
	$mysqli->query("INSERT INTO `boom_users` (user_id, user_name, user_email, user_join, user_password, user_language, user_rank,  user_verify, user_timezone) VALUES (1, '" . $username . "', '" . $email . "', '" . $time . "', '" . $password . "', '" . $language . "', '100', '1', 'America/Toronto')");
	$mysqli->query("INSERT INTO `boom_users_data` (`uid`, `badge_auth`, `badge_member`, `badge_chat`, `badge_top`, `badge_qtop`, `badge_ruby`, `badge_beat`, `badge_gold`, `badge_like`, `badge_friend`, `badge_gift`, `user_about`, `user_note`) VALUES ('1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '')");
	$mysqli->query("INSERT INTO `boom_style` (`style_ref`, `style_name`, `style_active`, `style_wrap`, `style_menu`) VALUES ('amber_orange', 'Amber Orange', '1', 'border:1px solid rgba(255,140,0,0.6); box-shadow:0 0 16px rgba(255,140,0,0.45), 0 0 40px rgba(255,140,0,0.30);', 'background:linear-gradient(to bottom, rgba(200,110,0,1), rgba(150,80,0,1));'), ('neon_blue', 'Neon Blue', '1', 'border:1px solid rgba(0,190,255,0.6); box-shadow:0 0 16px rgba(0,190,255,0.45), 0 0 40px rgba(0,190,255,0.30);', 'background:linear-gradient(to bottom, rgba(0,150,200,1), rgba(0,110,160,1));'), ('cherry_red', 'Cherry Red', '1', 'border:1px solid rgba(255,80,80,0.6); box-shadow:0 0 16px rgba(255,80,80,0.45), 0 0 40px rgba(255,80,80,0.30);', 'background:linear-gradient(to bottom, rgba(200,60,60,1), rgba(150,40,40,1));'), ('emerald_green', 'Emerald Green', '1', 'border:1px solid rgba(70,200,110,0.6); box-shadow:0 0 16px rgba(70,200,110,0.45), 0 0 40px rgba(70,200,110,0.30);', 'background:linear-gradient(to bottom, rgba(60,160,90,1), rgba(40,120,65,1));')");
    $mysqli->query("INSERT INTO `boom_exp` (`uid`, `exp_current`, `exp_week`, `exp_month`, `exp_total`) VALUES ('1', '0', '0', '0', '0')");
	$mysqli->query("INSERT INTO `boom_users` (user_id, user_name, user_ip, user_join, user_password, user_rank, user_tumb, user_bot) VALUES (2, 'System', '0.0.0.0', '" . $time . "', '" . randomPass() . "', '69', 'default_system.png', '1')");
    $mysqli->query("INSERT INTO boom_rooms ( room_id, room_name, room_system, room_action, room_creator ) VALUES (1, 'Main room', 1, '" . $time . "', '1')");
    $mysqli->query("INSERT INTO `boom_page` (`page_id`, `page_name`, `page_content`) VALUES (1, 'terms_of_use', '" . $term_content . "'), (2, 'privacy_policy', '" . $privacy_content . "'), (3, 'rules', '" . $help_content . "')");
    $mysqli->query("INSERT INTO boom_filter (word, word_type) VALUES\r\n\t('aol','email'),('att','email'),('comcast','email'),('facebook','email'),('gmail','email'),('gmx','email'),('googlemail','email'),('google','email'),('hotmail','email'),('mac','email'),('me','email'),('mail','email'),('msn','email'),('live','email'),('sbcglobal','email'),\r\n\t('verizon','email'),('yahoo','email'),('email','email'),('fastmail','email'),('games','email'),('hush','email'),('hushmail','email'),('icloud','email'),('iname','email'),('inbox','email'),('lavabit','email'),('love','email'),('outlook','email'),('pobox','email'),\r\n\t('protonmail','email'),('rocketmail','email'),('safe-mail','email'),('wow','email'),('ygm','email'),('ymail','email'),('zoho','email'),('yandex','email'),('bellsouth','email'),('charter','email'),('cox','email'),('earthlink','email'),('juno','email'),\r\n\t('btinternet','email'),('virginmedia','email'),('blueyonder','email'),('freeserve','email'),('ntlworld','email'),('o2','email'),('orange','email'),('sky','email'),('talktalk','email'),('tiscali','email'),('virgin','email'),('wanadoo','email'),\r\n\t('bt','email'),('sina','email'),('qq','email'),('naver','email'),('hanmail','email'),('daum','email'),('nate','email'),('laposte','email'),('gmx','email'),('sfr','email'),('neuf','email'),('free','email'),('online','email'),('t-online','email'),('web','email'),\r\n\t('libero','email'),('virgilio','email'),('alice','email'),('tin','email'),('poste','email'),('teletu','email'),('mail','email'),('rambler','email'),('ya','email'),('list','email'),('skynet','email'),('voo','email'),('tvcablenet','email'),('telenet','email'),\r\n\t('fibertel','email'),('speedy','email'),('arnet','email'),('prodigy.mx','email'),('uol','email'),('bol','email'),('terra','email'),('ig','email'),('itelefonica','email'),('r7','email'),('zipmail','email'),('globo','email'),('globomail','email'),('oi','email')\r\n\t");
    $mysqli->query("INSERT INTO `boom_gift` (`id`, `gift_image`, `gift_title`, `gift_method`, `gift_cost`, `gift_rank`) VALUES (1, 'clover.svg', 'Lucky clover', 1, 100, 1),\r\n(2, 'clown.svg', 'Clown face', 1, 100, 1),\r\n(3, 'coffee.svg', 'Hot coffee cup', 1, 100, 1),\r\n(4, 'cool.svg', 'Cool guy face', 1, 100, 1),\r\n(5, 'crown.svg', 'Nice crown', 1, 100, 1),\r\n(6, 'cure.svg', 'Magic potion', 1, 100, 1),\r\n(7, 'diamond.svg', 'Glossy diamond', 1, 100, 1),\r\n(8, 'fishbone.svg', 'Fish bones', 1, 100, 1),\r\n(9, 'flowers.svg', 'Flower bouquet', 1, 100, 1),\r\n(10, 'gift.svg', 'Gift box', 1, 100, 1),\r\n(11, 'goldpot.svg', 'Pot of gold', 1, 100, 1),\r\n(12, 'hot.svg', 'Hot fire flame', 1, 100, 1),\r\n(13, 'icecream.svg', 'Ice cream', 1, 100, 1),\r\n(14, 'karma.svg', 'Karma back', 1, 100, 1),\r\n(15, 'kiss.svg', 'Gentle kiss', 1, 100, 1),\r\n(16, 'like.svg', 'Tumbs up', 1, 100, 1),\r\n(17, 'love.svg', 'Love', 1, 100, 1),\r\n(18, 'lovepotion.svg', 'Love potion', 1, 100, 1),\r\n(19, 'loverepair.svg', 'Broken heart', 1, 100, 1),\r\n(20, 'medal.svg', 'Winner medal', 1, 100, 1),\r\n(21, 'money.svg', 'Pile of cash', 1, 100, 1),\r\n(22, 'pizza.svg', 'Pizza slice', 1, 100, 1),\r\n(23, 'poison.svg', 'Poison potion', 1, 100, 1),\r\n(24, 'power.svg', 'Energy potion', 1, 100, 1),\r\n(25, 'ring.svg', 'Expensive ring', 1, 100, 1),\r\n(26, 'rose.svg', 'Fresh rose', 1, 100, 1),\r\n(27, 'smile.svg', 'Smiley face', 1, 100, 1),\r\n(28, 'star.svg', 'Night star', 1, 100, 1),\r\n(29, 'teddy.svg', 'Teddy bear', 1, 100, 1),\r\n(30, 'trophy.svg', 'Gold trophy', 1, 100, 1),\r\n(31, 'voodoo.svg', 'Voodo doll', 1, 100, 1),\r\n(34, 'energy.svg', 'Power energy', 1, 100, 1)");
	return boomCode(1);
}

?>