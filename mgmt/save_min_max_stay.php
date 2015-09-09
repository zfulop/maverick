<?php

require("includes.php");

header('Location: view_min_max_stay.php');

$link = db_connect();

$fromDate = $_REQUEST['from_date'];
$toDate = $_REQUEST['to_date'];
$min = $_REQUEST['min_stay'];
$max = $_REQUEST['max_stay'];

if(strlen($fromDate) != 10) {
	$fromDate = 'NULL';
} else {
	$fromDate = "'$fromDate'";
}
if(strlen($toDate) != 10) {
	$toDate = 'NULL';
} else {
	$toDate = "'$toDate'";
}
if(intval($min) < 1) {
	$min = 'NULL';
} else {
	$min = intval($min);
}
if(intval($max) < 1) {
	$max = 'NULL';
} else {
	$max = intval($max);
}

$sql = "INSERT INTO min_max_stay (from_date, to_date, min_stay, max_stay) VALUES ($fromDate, $toDate, $min, $max)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot insert min_max_stay in mgmt interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error("Cannot create min_max_stay entry");
	mysql_close($link);
	return;
}

set_message("min max stay item created");
mysql_close($link);

?>