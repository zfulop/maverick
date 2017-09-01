<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

header('Location: delete_extracted_rooms_file.php');

$pricePerBed = floatval($_REQUEST['price_per_bed']);
$pricePerRoom = floatval($_REQUEST['price_per_room']);
$numOfBeds = intval($_REQUEST['num_of_beds']);
$numOfExtraBeds = intval($_REQUEST['num_of_extra_beds']);
$type = $_REQUEST['type'];
$validFrom = $_REQUEST['valid_from'];
$validTo = $_REQUEST['valid_to'];
if(strlen($validTo) != 10)
	$validTo = '2099/12/31';

$order = intval($_REQUEST['order']);

mysql_query("START TRANSACTION", $link);

$sql = "UPDATE rooms SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change rooms orders in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new room');
	mysql_close($link);
	return;
}


$sql = "INSERT INTO rooms (type, num_of_beds, num_of_extra_beds, valid_from, valid_to, price_per_room, price_per_bed, _order) VALUES ('$type', $numOfBeds, $numOfExtraBeds, '$validFrom', '$validTo', $pricePerRoom, $pricePerBed, $order)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new room');
	mysql_close($link);
	return;
}

$rowId = mysql_insert_id($link);


foreach(getLanguages() as $lang => $name) {
	$name = $_REQUEST["name_$lang"];
	$description = $_REQUEST["description_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('rooms', 'name', $rowId, '$lang', '$name')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Can create room text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new room');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('rooms', 'description', $rowId, '$lang', '$description')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot create room text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new room');
		mysql_close($link);
		return;
	}
}

set_message('New room saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
