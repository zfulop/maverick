<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



header('Location: ' . $_SERVER['HTTP_REFERER']);

$link = db_connect();

$descrId = $_REQUEST['description_id'];
$bookingId = $_REQUEST['id'];
$roomName = $_REQUEST['room'];

$sql = "DELETE FROM bookings WHERE id=$bookingId LIMIT 1";

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete booking: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete booking');
} else {
	set_message('Booking deleted');
	audit(AUDIT_DELETE_BOOKING, $_REQUEST, $bookingId, $descrId, $link);
}

mysql_close($link);


?>
