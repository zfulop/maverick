<?php

require("includes.php");

$type = $_REQUEST['type'];

$link = db_connect();

$sql = "SELECT * FROM cash_out WHERE type='$type' ORDER BY time_of_payment DESC LIMIT 1";
$result = mysql_query($sql, $link);
if(!$result) {
	$err = "Cannot get service cash out";
	set_error($err);
	trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	echo "";
	return;
}
if(mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$startDate = substr($row['time_of_payment'], 0, 10);
} else {
	$startDate = '2008-01-01';
}

echo $startDate;


mysql_close($link);

?>
