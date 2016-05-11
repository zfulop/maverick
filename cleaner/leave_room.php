<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$roomId = $_REQUEST['room_id'];
$cleaner = $_SESSION['login_user'];

$link = db_connect();

// Load room data
$sql = "SELECT r.id, r.room_type_id, r.name, rt.name AS rt_name, rt.type FROM rooms r INNER JOIN room_types rt ON r.room_type_id=rt.id WHERE r.id=$roomId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms. Error: " . mysql_error($link) . " (SQL: $sql)");
}
$roomData = mysql_fetch_assoc($result);
$dayToShow = date('Y-m-d');
$today = date('Y/m/d', strtotime($dayToShow));
$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));

// Get cleaner actions
$sql = "SELECT * FROM cleaner_action WHERE time_of_event>'$dayToShow' AND room_id=$roomId AND cleaner='$cleaner'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get clear actions for date: $dayToShow and room: $roomId. Error: " . mysql_error($link) . " (SQL: $sql)");
}

$cleanerEntered = null;
$cleanerLeft = null;
$notes = array();
while($row = mysql_fetch_assoc($result)) {
	if($row['type'] == 'ENTER_ROOM') {
		$cleanerEntered = $row;
	}
	if($row['type'] == 'FINISH_ROOM') {
		$cleanerEntered = null;
	}
	if($row['type'] == 'NOTE') {
		$notes[] = $row['comment'];
	}
}

$now = date('Y-m-d H:i:s');
if(!is_null($cleanerEntered)) {
	$sql = "INSERT INTO cleaner_action (cleaner, room_id, time_of_event, type) VALUES ('$cleaner',$roomId, '$now', 'FINISH_ROOM')";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot insert clear action. Error: " . mysql_error($link) . " (SQL: $sql)");
	} else {
		set_message('Left room');
	}

	$msg = '';	
	if(count($notes) > 0) {
		$msg = implode("\n", $notes);
	}
	
	sendMail("Cleaner", "cleaner", CONTACT_EMAIL, CONTACT_EMAIL, "Room " . $roomData['name'] . " is clean", $msg);
}


header('Location: index.php');

?>
