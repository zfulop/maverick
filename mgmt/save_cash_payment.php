<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}



$link = db_connect();

header('Location: view_cash_bookings.php');

$id = $_REQUEST['booking_description_id'];
$amount = $_REQUEST['amount'];
$currency = $_REQUEST['currency'];
$nowTime = date('Y-m-d H:i:s');

$sql = "INSERT INTO payments (booking_description_id, type, amount, currency, time_of_payment, comment, pay_mode) VALUES ($id, 'Szobabevétel', '$amount', '$currency', '$nowTime', '', 'CASH3')";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save payment: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save payment');
} else {
	set_message('Payment saved');
	audit(AUDIT_PAYMENT_ADDED, $_REQUEST, 0, $descrId, $link);
}

mysql_close($link);

?>
