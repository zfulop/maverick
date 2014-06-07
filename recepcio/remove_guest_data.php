<?php

require("includes.php");

$id = intval($_REQUEST['id']);
$name = $_REQUEST['name'];
$bdid = $_REQUEST['booking_description_id'];
$link = db_connect();
$sql = "DELETE FROM booking_guest_data WHERE id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Could not remove guest data: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Could not remove guest data.");
} else {
	set_error("Guest data removed.");
	audit(AUDIT_REMOVE_GUEST_DATA, $name, 0, $bdid, $link);
}

mysql_close($link);
header("Location: edit_booking.php?description_id=$bookingDescrId");


?>
