<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();


$type = $_REQUEST['type'];
$receiver = $_REQUEST['receiver'];
$timeOfPayment = date('Y-m-d H:i:s');
$amount = str_replace(",", ".", $_REQUEST['amount']);
$currency = $_REQUEST['currency'];
$comment = mysql_real_escape_string($_REQUEST['comment'], $link);
$payMode = $_REQUEST['pay_mode'];

logDebug("Saving cash out for type=$tye, receiver=$receiver, amount=$amount, comment=$comment, payMode=$payMode");


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
