<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

header('Location: view_rooms.php');

$id = intval($_REQUEST['id']);
$name = $_REQUEST['name'];
$type = intval($_REQUEST['type']);
$validFrom = $_REQUEST['valid_from'];
$validTo = $_REQUEST['valid_to'];
if(strlen($validTo) != 10)
	$validTo = '2099/12/31';

mysql_query("START TRANSACTION", $link);


if($id < 1) {
	$sql = "INSERT INTO rooms (name, valid_from, valid_to, room_type_id) VALUES ('$name', '$validFrom', '$validTo', $type)";
} else {
	$sql = "UPDATE rooms SET name='$name', valid_from='$validFrom', valid_to='$validTo', room_type_id=$type WHERE id=$id";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new room');
	mysql_close($link);
	return;
}
if($id < 1) {
	$id = mysql_insert_id($link);
}

$additionalRoomTypes = array();
if(isset($_REQUEST['additional_types'])) {
	$additionalRoomTypes = $_REQUEST['additional_types'];
}
$sql = "DELETE FROM rooms_to_room_types WHERE room_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new room');
	mysql_close($link);
	return;
}

$sql = '';
foreach($additionalRoomTypes as $rt) {
	if($rt != $type) {
		$sql .= "($id, $rt),";
	}
}

if(strlen($sql) > 0) {
	$sql = "INSERT INTO rooms_to_room_types (room_id, room_type_id) VALUES " . substr($sql, 0, -1);
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot create room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new room');
		mysql_close($link);
		return;
	}
}


set_message('Room saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
