<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$bookingDescrId = intval($_REQUEST['booking_description_id']);
$bookingIds = explode(",",$_REQUEST['booking_ids']);

foreach($bookingIds as $bid) {
	$extraBeds = intval($_REQUEST["extra_beds_$bid"]);
	if(intval($extraBeds) < 1) {
		$extraBeds = 'NULL';
	}
	$sql = "UPDATE bookings SET extra_beds=$extraBeds where id=$bid";
	if(!mysql_query($sql, $link)) {
		trigger_error("Could not save extra beds for booking: " . mysql_error($link) . " (SQL: $sql)");
		set_error("Could not save extra beds for booking");
	} else {
		set_message("Extra beds saved.");
		audit(AUDIT_SAVE_EXTRA_BEDS, $_REQUEST, 0, $bookingDescrId, $link);
	}
}

mysql_close($link);
header("Location: edit_booking.php?description_id=$bookingDescrId");


?>
