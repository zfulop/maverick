<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$id = intval($_REQUEST['guest_data_id']);
$bookingDescrId = $_REQUEST['booking_description_id'];
$name = mysql_real_escape_string($_REQUEST['name'], $link);
$gender = mysql_real_escape_string($_REQUEST['gender'], $link);
$address = mysql_real_escape_string($_REQUEST['address'], $link);
$nationality = mysql_real_escape_string($_REQUEST['nationality'], $link);
$email = mysql_real_escape_string($_REQUEST['email'], $link);
$telephone = mysql_real_escape_string($_REQUEST['telephone'], $link);
$deposit = mysql_real_escape_string($_REQUEST['deposit'], $link);
$roomId = $_REQUEST['room_id'];
$comment = mysql_real_escape_string($_REQUEST['comment'], $link);
$bed = $_REQUEST['bed'];

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
