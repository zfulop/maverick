<?php

require("includes.php");

$link = db_connect();

header('Location: view_special_offers.php');

mysql_query("START TRANSACTION", $link);

$id = intval($_REQUEST['id']);
$name = $_REQUEST['name'];
$startDate = $_REQUEST['start_date'];
$endDate = $_REQUEST['end_date'];
$discount = intval($_REQUEST['discount']);
$nights = intval($_REQUEST['num_of_nights']);
$visible = isset($_REQUEST['visible']) ? 1 : 0;
if($nights < 1) {
	$nights = 'NULL';
}
$numOfDaysBeforeArrival = intval($_REQUEST['num_of_days_before_arrival']);
if($numOfDaysBeforeArrival < 1) {
	$numOfDaysBeforeArrival = 'NULL';
}
$roomTypeIds = implode(",",$_REQUEST['room_type_ids']);
if(strlen($roomTypeIds) < 1) {
	$roomTypeIds = 'NULL';
} else {
	$roomTypeIds = "'$roomTypeIds'";
}


if($id < 1) {
	$sql = "INSERT INTO special_offers (name, start_date, end_date, discount_pct, nights, room_type_ids, valid_num_of_days_before_arrival, visible) VALUES ('$name', '$startDate', '$endDate', $discount, $nights, $roomTypeIds, $numOfDaysBeforeArrival, $visible)";
} else {
	$sql = "UPDATE special_offers SET name='$name', start_date='$startDate', end_date='$endDate', discount_pct=$discount, nights=$nights, room_type_ids=$roomTypeIds, valid_num_of_days_before_arrival=$numOfDaysBeforeArrival, visible=$visible WHERE id=$id";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create special offers in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new special offer');
	mysql_close($link);
	return;
}

$inserted = false;
if($id < 1) {
	$inserted = true;
	$id = mysql_insert_id($link);
}

$sql = "DELETE FROM lang_text WHERE table_name='special_offers' AND row_id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save special offers title in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save special offer');
	mysql_close($link);
	return;
}

foreach(getLanguages() as $lang => $name) {
	$title = $_REQUEST["title_$lang"];
	$text = $_REQUEST["text_$lang"];
	$roomName = $_REQUEST["room_name_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('special_offers', 'title', $id, '$lang', '$title')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save special offers title in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save special offer');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('special_offers', 'text', $id, '$lang', '$text')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save special offers text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save special offer');
		mysql_close($link);
		return;
	}

	if(strlen($roomName) > 0) {
		$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('special_offers', 'room_name', $id, '$lang', '$roomName')";
		if(!mysql_query($sql, $link)) {
			trigger_error("Cannot save special offers roomName in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			set_error('Cannot save special offer');
			mysql_close($link);
			return;
		}
	}
}

set_message('Special offer saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
