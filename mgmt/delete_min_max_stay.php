<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}



header('Location: view_min_max_stay.php');

$link = db_connect();

$id = $_REQUEST['id'];

$sql = "DELETE FROM min_max_stay WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete min_max_stay in mgmt interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error("Cannot delete min_max_stay");
	mysql_close($link);
	return;
}

set_message("min_max_stay item deleted");

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
