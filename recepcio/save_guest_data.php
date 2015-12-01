<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$id = intval($_REQUEST['guest_data_id']);
$bookingDescrId = $_REQUEST['booking_description_id'];
$name = $_REQUEST['name'];
$gender = $_REQUEST['gender'];
$address = $_REQUEST['address'];
$nationality = $_REQUEST['nationality'];
$email = $_REQUEST['email'];
$telephone = $_REQUEST['telephone'];
$deposit = $_REQUEST['deposit'];
$roomId = $_REQUEST['room_id'];
$comment = $_REQUEST['comment'];
$bed = $_REQUEST['bed'];

$link = db_connect();
if($id > 0) {
	$sql = "UPDATE booking_guest_data SET name='$name', address='$address', email='$email', telephone='$telephone', nationality='$nationality', gender='$gender', deposit='$deposit', room_id=$roomId, comment='$comment', bed='$bed' WHERE id=$id";
} else {
	$sql = "INSERT INTO booking_guest_data (booking_description_id, name, address, email, telephone, nationality, gender, deposit, room_id, comment, bed) VALUES ('$bookingDescrId', '$name', '$address', '$email', '$telephone', '$nationality', '$gender',  '$deposit', $roomId, '$comment', '$bed')";
}
if(!mysql_query($sql, $link)) {
	trigger_error("Could not save guest data: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Could not save guest data");
} else {
	set_message("Guest data saved.");
	audit(AUDIT_SAVE_GUEST_DATA, $_REQUEST, 0, $bookingDescrId, $link);
}

mysql_close($link);
header("Location: edit_booking.php?description_id=$bookingDescrId");


?>
