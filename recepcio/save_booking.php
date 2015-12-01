<?php

require("includes.php");
require("room_booking.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$descrId = $_REQUEST['description_id'];
header('Location: edit_booking.php?description_id=' . $descrId);
set_debug("save_booking.php START");

$link = db_connect();

$name = mysql_escape_string($_REQUEST['name']);
$nameExt = mysql_escape_string($_REQUEST['name_ext']);
$gender = $_REQUEST['gender'];
$addr = mysql_escape_string($_REQUEST['address']);
$nat = mysql_escape_string($_REQUEST['nationality']);
$email = mysql_escape_string($_REQUEST['email']);
$tel = mysql_escape_string($_REQUEST['telephone']);
$deposit = mysql_escape_string($_REQUEST['deposit']);
$depositCurrency = $_REQUEST['deposit_currency'];
$comment = mysql_escape_string($_REQUEST['comment']);
$source = mysql_escape_string($_REQUEST['source']);
$arrivalTime = mysql_escape_string($_REQUEST['arrival_time']);

$sql = "UPDATE booking_descriptions SET name='$name', name_ext='$nameExt', gender='$gender', address='$addr', nationality='$nat', email='$email', telephone='$tel', comment='$comment', source='$source', arrival_time='$arrivalTime' WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save booking description: " . mysql_error($link) . " (SQL: $sql)");
	set_error('Cannot save booking');
} else {
	set_message('Booking saved');
	audit(AUDIT_EDIT_BOOKING, $_REQUEST, 0, $descrId, $link);
}

$sql = "DELETE FROM payments WHERE booking_description_id=$descrId AND comment='*booking deposit*'";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot delete existing booking deposit: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Could not delete existing booking deposit.');
	mysql_close($link);
	return;
}

$nowTime = date('Y-m-d H:i:s');
if($deposit > 0) {
	$sql = "INSERT INTO payments (booking_description_id, amount, currency, time_of_payment, comment) VALUES($descrId, $deposit, '$depositCurrency', '$nowTime', '*booking deposit*' )";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save booking deposit: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Could not save booking deposit.');
		mysql_close($link);
		return;
	}
}

mysql_close($link);


?>
