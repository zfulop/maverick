<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}


header('Location: ' . $_SERVER['HTTP_REFERER']);

$roomIds = $_REQUEST['rooms'];
$firstNight = str_replace('-','/',$_REQUEST['first_night']);
$lastNight = str_replace('-','/',$_REQUEST['last_night']);
$numOfNights = round((strtotime($_REQUEST['last_night']) - strtotime($_REQUEST['first_night']))/(60*24));

$link = db_connect();

$sql = "SELECT r.*, rt.num_of_beds, rt.type as room_type FROM rooms r INNER JOIN room_types rt ON r.room_type_id=rt.id";
$result = mysql_query($sql, $link);
$rooms = array();
while($row = mysql_fetch_assoc($result)) {
	$rooms[$row['id']] = $row;
}

$sql = "INSERT INTO booking_descriptions (name,first_night,last_night,num_of_nights,maintenance) VALUES ('lockout','$firstNight','$lastNight',$numOfNights,1)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot lock rooms: " . mysql_error($link) . " (SQL: $sql)");
	set_error('Cannot lock rooms');
} else {
	set_message('Rooms locked');
}
$descrId = mysql_insert_id($link);
$now = date('Y-m-d H:i:s');

foreach($roomIds as $rid) {
	$room = $rooms[$rid];
	$bookType = ($room['room_type'] == 'PRIVATE' ? 'ROOM' : 'BED');
	$rtId = $room['room_type_id'];
	$numOfBed = $room['num_of_beds'];
	$sql = "INSERT INTO bookings (num_of_person,room_id,booking_type,creation_time,description_id,room_payment,original_room_type_id) VALUES ($numOfBed,$rid,'$bookType','$now',$descrId,0,$rtId)";
	set_message("executing: $sql");
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot lock room (" . $room['name'] . "): " . mysql_error($link) . " (SQL: $sql)");
	} else {
		set_message('Room "' . $room['name'] . '" locked');
	}
}


audit(AUDIT_LOCK_ROOMS, $_REQUEST, 0, null, $link);
mysql_close($link);


?>
