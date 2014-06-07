<?php

require("includes.php");

header('Location: ' . $_SERVER['HTTP_REFERER']);

$link = db_connect();
$descrId = intval($_REQUEST['description_id']);

$sql = "UPDATE booking_descriptions SET checked_in=0 WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot checkout booking in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot checkout booking');
} else {
	set_message('Booking checked out.');
	audit(AUDIT_CHECKOUT_BOOKING, $_REQUEST, 0, $descrId, $link);
}

mysql_close($link);

?>
