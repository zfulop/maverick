<?php

require("includes.php");

header('Location: ' . $_SERVER['HTTP_REFERER']);

$link = db_connect();
$descrId = intval($_REQUEST['description_id']);
$type = $_REQUEST['type'];

$sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot retieve booking in admin interface when canceling it: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot cancel booking - DB error');
	mysql_close($link);
	return;
}
$row = mysql_fetch_assoc($result);
list($year, $month, $day) = explode('/', $row['first_night']);
if(time() > strtotime("$year-$month-$day + 2 day")) {
	set_error('Cannot cancel booking because the 1st night is less than 2 days away (first night: ' . $row['first_night'] . ')');
	mysql_close($link);
	return;
}



$sql = "UPDATE booking_descriptions SET cancelled=1,cancel_type='$type' WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot cancel booking in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot cancel booking');
} else {
	set_message('Booking cancelled');
	audit(AUDIT_CANCEL_BOOKING, $_REQUEST, 0, $descrId, $link);
}

mysql_close($link);

?>
