<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



header('Location: ' . $_SERVER['HTTP_REFERER']);

$link = db_connect();
$descrId = intval($_REQUEST['description_id']);

$sql = "UPDATE booking_descriptions SET checked_in=1 WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot checkin booking in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot checkin booking');
} else {
	set_message('Booking checked in.');
	audit(AUDIT_CHECKIN_BOOKING, $_REQUEST, 0, $descrId, $link);
}

mysql_close($link);

?>
