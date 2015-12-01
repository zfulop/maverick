<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

$type = $_REQUEST['type'];
$descrId = $_REQUEST['booking_description_id'];
$amount = str_replace(',', '.', $_REQUEST['amount']);
$currency = $_REQUEST['currency'];
$comment = $_REQUEST['comment'];
$nowTime = date('Y-m-d H:i:s');
$mode = $_REQUEST['pay_mode'];

$sql = "INSERT INTO payments (booking_description_id, type, amount, currency, time_of_payment, comment, pay_mode) VALUES ($descrId, '$type', '$amount', '$currency', '$nowTime', '$comment', '$mode')";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save payment: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save payment');
} else {
	set_message('Payment saved');
	audit(AUDIT_PAYMENT_ADDED, $_REQUEST, 0, $descrId, $link);
}

mysql_close($link);
header("Location: edit_booking.php?description_id=$descrId");

?>
