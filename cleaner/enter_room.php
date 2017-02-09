<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$roomId = $_REQUEST['room_id'];
$cleaner = $_SESSION['login_user'];
$roomPart = $_REQUEST['room_part'];

$link = db_connect();

$dayToShow = date('Y-m-d');
$today = date('Y/m/d', strtotime($dayToShow));
$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));

// Get cleaner actions
$sql = "SELECT * FROM cleaner_action WHERE time_of_event>'$dayToShow' AND room_id=$roomId AND cleaner='$cleaner' ORDER BY time_of_event";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get clear actions for date: $dayToShow and room: $roomId. Error: " . mysql_error($link) . " (SQL: $sql)");
}

$cleanerEntered = null;
$cleanerLeft = null;
while($row = mysql_fetch_assoc($result)) {
	if($row['type'] == 'ENTER_ROOM') {
		$cleanerEntered = $row;
	}
	if($row['type'] == 'FINISH_ROOM') {
		$cleanerEntered = null;
	}
}

$now = date('Y-m-d H:i:s');
if(is_null($cleanerEntered)) {
	$sql = "INSERT INTO cleaner_action (cleaner, room_id, time_of_event, type) VALUES ('$cleaner',$roomId, '$now', 'ENTER_ROOM')";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot insert clear action. Error: " . mysql_error($link) . " (SQL: $sql)");
	} else {
		set_message('Entered room');
	}
}


header("Location: view_room.php?room_id=$roomId&room_part=$roomPart");

?>