<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}



header('Location: view_rooms.php');

$link = db_connect();

mysql_query("START TRANSACTION", $link);

$id = intval($_REQUEST['id']);

$sql = "SELECT * FROM bookings WHERE room_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get existing bookings for a room in mgmt interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete room');
	mysql_close($link);
	return;
}
if(mysql_num_rows($result) > 0) {
	set_error('Cannot delete room because there are bookings on it. You can expire the room only (edit the room and set expire date).');
	mysql_close($link);
	return;
}


$sql = "DELETE FROM rooms WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete room');
	mysql_close($link);
	return;
}

$sql = "DELETE FROM lang_text WHERE table_name='rooms' and row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete room');
	mysql_close($link);
	return;
}

$sql = "DELETE FROM rooms_to_room_types WHERE room_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete room');
	mysql_close($link);
	return;
}


set_message('Room deleted');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
