<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$roomId = $_REQUEST['room_id'];
$cleaner = $_SESSION['login_user'];

$link = db_connect();

$note = mysql_real_escape_string($_REQUEST['note'], $link);

$dayToShow = date('Y-m-d');
$today = date('Y/m/d', strtotime($dayToShow));
$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));

$now = date('Y-m-d H:i:s');
$sql = "INSERT INTO cleaner_action (cleaner, room_id, time_of_event, type, comment) VALUES ('$cleaner',$roomId, '$now', 'NOTE', '$note')";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot insert note. Error: " . mysql_error($link) . " (SQL: $sql)");
} else {
	set_message('Note saved');
}

header("Location: view_room.php?room_id=$roomId");

?>
