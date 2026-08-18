<?php
require __DIR__ . "/../config_session.php";
require BOOM_PATH . "/system/language/" . $data["user_language"] . "/console.php";

if (isset($_POST["reload_console"])) {
    $last = escape($_POST["reload_console"]);
    $result = reloadSystemConsole($last);
    if ($result === 0) {
        echo boomCode(1);
    } else {
        echo boomCode(1, ['data' => $result]);
    }
    exit;
}

if (isset($_POST["more_console"])) {
    $last = escape($_POST["more_console"]);
    $result = loadMoreSystemConsole($last);
    if ($result === 0) {
        echo boomCode(0);
    } else {
        echo boomCode(1, ['html' => $result]);
    }
    exit;
}

if (isset($_POST["search_console"])) {
    $find = escape($_POST["search_console"]);
    if ($find == "") {
        $result = reloadSystemConsole(0);
        if ($result === 0) {
            echo boomCode(0);
        } else {
            echo boomCode(1, ['html' => $result]);
        }
        exit;
    }
    $id = 0;
    $user = nameDetails($find);
    if (!empty($user)) {
        $id = $user["user_id"];
    }
    $result = searchSystemConsole($id, $find);
    echo boomCode(1, ['html' => $result]);
    exit;
}

if (isset($_POST["clear_console"]) && boomAllow(11)) {
    if (clearSystemConsole()) {
        echo boomCode(1);
    } else {
        echo boomCode(0);
    }
    exit;
}

function consoleUser($console)
{
    return "<span onclick=\"getProfile(" . $console["hunter"] . ");\" class=\"bold console_user\">" . $console["chunter"] . "</span>";
}

function consoleTarget($console)
{
    return "<span onclick=\"getProfile(" . $console["target"] . ");\" class=\"bold console_user\">" . $console["ctarget"] . "</span>";
}

function consoleText($t)
{
    return "<span class=\"bold console_text\">" . $t . "</span>";
}

function renderConsoleText($console)
{
    global $data;
    global $clang;
    global $lang;
    $ctext = $clang[$console["ctype"]];
    $ctext = str_replace(
        ["%hunter%", "%target%", "%oldname%", "%room%", "%data%", "%data2%", "%rank%", "%roomrank%", "%delay%"],
        [
            consoleUser($console),
            consoleTarget($console),
            consoleText($console["custom"]),
            consoleText($console["croom"]),
            consoleText($console["custom"]),
            consoleText($console["custom2"]),
            consoleText(rankTitle($console["crank"])),
            consoleText(roomRankTitle($console["crank"])),
            consoleText(boomRenderMinutes($console["delay"]))
        ],
        $ctext
    );
    return $ctext;
}

function reloadSystemConsole($id)
{
    global $mysqli;
    global $data;
    global $lang;
    global $clang;
    $get_console = $mysqli->query("
        SELECT *,
        (SELECT user_name FROM boom_users WHERE user_id = hunter) AS chunter,
        (SELECT user_name FROM boom_users WHERE user_id = target) AS ctarget,
        (SELECT room_name FROM boom_rooms WHERE room_id = room) AS croom
        FROM boom_console WHERE id > '" . $id . "' ORDER BY cdate DESC LIMIT 500
    ");
    if ($get_console->num_rows > 0) {
        $list = "";
        while ($console = $get_console->fetch_assoc()) {
            $list .= boomTemplate("element/console_log", $console);
        }
        return $list;
    }
    return 0;
}

function loadMoreSystemConsole($id)
{
    global $mysqli;
    global $data;
    global $lang;
    global $clang;
    $get_console = $mysqli->query("
        SELECT *,
        (SELECT user_name FROM boom_users WHERE user_id = hunter) AS chunter,
        (SELECT user_name FROM boom_users WHERE user_id = target) AS ctarget,
        (SELECT room_name FROM boom_rooms WHERE room_id = room) AS croom
        FROM boom_console WHERE id < '" . $id . "' ORDER BY cdate DESC LIMIT 500
    ");
    if ($get_console->num_rows > 0) {
        $list = "";
        while ($console = $get_console->fetch_assoc()) {
            $list .= boomTemplate("element/console_log", $console);
        }
        return $list;
    }
    return 0;
}

function searchSystemConsole($id, $find)
{
    global $mysqli;
    global $data;
    global $lang;
    global $clang;
    $find_list = [];
    foreach ($clang as $key => $value) {
        if (stripos($value, $find) !== false) {
            array_push($find_list, $key);
        }
    }
    $find_list = listWordArray($find_list);
    $get_console = $mysqli->query("
        SELECT *,
        (SELECT user_name FROM boom_users WHERE user_id = hunter) AS chunter,
        (SELECT user_name FROM boom_users WHERE user_id = target) AS ctarget,
        (SELECT room_name FROM boom_rooms WHERE room_id = room) AS croom
        FROM boom_console WHERE hunter = '" . $id . "' OR target = '" . $id . "' OR ctype IN (" . $find_list . ") ORDER BY cdate DESC LIMIT 500
    ");
    if ($get_console->num_rows > 0) {
        $list = "";
        while ($console = $get_console->fetch_assoc()) {
            $list .= boomTemplate("element/console_log", $console);
        }
        return $list;
    }
    return emptyZone($lang["no_data"]);
}

function clearSystemConsole()
{
    global $mysqli;
    global $data;
    global $cody;
    if (!boomAllow($cody["can_clear_console"])) {
        return 0;
    }
    $mysqli->query("TRUNCATE TABLE boom_console");
    boomConsole("clear_console");
    return 1;
}
?>