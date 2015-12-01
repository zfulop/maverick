<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

$descrId = $_REQUEST['booking_description_id'];
$amount = str_replace(",", ".", $_REQUEST['amount']);
$currency = $_REQUEST['currency'];
$comment = $_REQUEST['comment'];
$nowTime = date('Y-m-d H:i:s');
$type = $_REQUEST['type'];

$sql = "INSERT INTO service_charges (booking_description_id, amount, currency, time_of_service, comment, type) VALUES ($descrId, '$amount', '$currency', '$nowTime', '$comment', '$type')";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save service charge: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save service charge');
} else {
	set_message('Service charge saved');
	audit(AUDIT_SERVICE_CHARGE_ADDED, $_REQUEST, 0, $descrId, $link);
}

mysql_close($link);
header("Location: edit_booking.php?description_id=$descrId");

?>
