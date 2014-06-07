<?php

require("includes.php");

$type = $_REQUEST['type'];
$receiver = $_REQUEST['payee'];
$timeOfPayment = date('Y-m-d H:i:s');
$amount = -1 * str_replace(",", ".", $_REQUEST['amount']);
$currency = $_REQUEST['currency'];
$comment = $_REQUEST['comment'];
$payMode = $_REQUEST['pay_mode'];
if($serviceId < 1) {
	$serviceId = 'NULL';
}

$link = db_connect();

$sql = "INSERT INTO cash_out (type, receiver, time_of_payment, amount, currency, comment, pay_mode) VALUES ('$type', '$receiver', '$timeOfPayment', $amount, '$currency',  '$comment', '$payMode')";

if(!mysql_query($sql, $link)) {
	trigger_error("Could not save cash-in: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Could not save cash-in");
} else {
	set_message("Cash-in entry saved.");
	audit(AUDIT_SAVE_CASH_IN, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: " . $_SERVER['HTTP_REFERER']);


?>
