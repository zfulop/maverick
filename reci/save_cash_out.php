<?php

require("includes.php");

$type = $_REQUEST['type'];
$receiver = $_REQUEST['receiver'];
$timeOfPayment = date('Y-m-d H:i:s');
$amount = str_replace(",", ".", $_REQUEST['amount']);
$currency = $_REQUEST['currency'];
$comment = $_REQUEST['comment'];
$payMode = 'CASH3';

$link = db_connect();

$sql = "INSERT INTO cash_out (type, receiver, time_of_payment, amount, currency, comment, pay_mode) VALUES ('$type', '$receiver', '$timeOfPayment', $amount, '$currency',  '$comment', '$payMode')";

if(!mysql_query($sql, $link)) {
	trigger_error("Could not save cashout: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Could not save cashout");
} else {
	set_message("Cashout entry saved.");
	audit(AUDIT_SAVE_CASH_OUT, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: " . $_SERVER['HTTP_REFERER']);


?>
