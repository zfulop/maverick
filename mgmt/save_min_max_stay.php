<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


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

$sql = "SELECT * FROM min_max_stay";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot load min_max_stay in mgmt interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error("Cannot extract min_max_stay into a file");
	mysql_close($link);
	return;
}

$minMaxStay = array();
while($row = mysql_fetch_assoc($result)) {
	$minMaxStay[] = $row;
}

$location = getLoginHotel();
$file = JSON_DIR . $location . '/min_max_stay.json';
$data = json_encode($minMaxStay, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
file_put_contents($file, $data);


mysql_close($link);

?>
