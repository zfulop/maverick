<?php

require("includes.php");

$link = db_connect();

$name = $_REQUEST['name'];
$destination = $_REQUEST['destination'];
$fnight = $_REQUEST['arrival_date'];
$numOfNights = intval($_REQUEST['nights']);
$amount = floatval(str_replace(',', '.', $_REQUEST['amount_value']));
$currency = $_REQUEST['amount_currency'];
$today = date('Y-m-d H:i:s');
$comment = $_REQUEST['comment'];
$mode = $_REQUEST['pay_mode'];
if($amount <= 0) {
	$currency = '';
	$amount = 0;
}

$sql = "INSERT INTO guest_transfer (name, destination, first_night, num_of_nights, amount_value, amount_currency, comment, time_of_enter, pay_mode) VALUES ('$name', '$destination', '$fnight', $numOfNights, $amount, '$currency', '$comment', '$today', '$mode')";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save guest transfer: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save gust transfer');
} else {
	set_message('Guest transfer saved');
	audit(AUDIT_GUEST_TRANSFER_ADDED, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: view_guest_transfer.php?destination=" . urlencode($destination)); 

?>
