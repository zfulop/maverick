<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

header('Location: view_special_offers.php');

mysql_query("START TRANSACTION", $link);

$id = intval($_REQUEST['id']);
$name = $_REQUEST['name'];
$startDate = $_REQUEST['start_date_0'];
$endDate = $_REQUEST['end_date_0'];
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
$earlyBirdDayCount = intval($_REQUEST['early_bird_day_count']);
if($earlyBirdDayCount < 1) {
	$earlyBirdDayCount = 'NULL';
}
$roomTypeIds = implode(",",$_REQUEST['room_type_ids']);
if(strlen($roomTypeIds) < 1) {
	$roomTypeIds = 'NULL';
} else {
	$roomTypeIds = "'$roomTypeIds'";
}


if($id < 1) {
	$sql = "INSERT INTO special_offers (name, start_date, end_date, discount_pct, nights, room_type_ids, valid_num_of_days_before_arrival, early_bird_day_count, visible) VALUES ('$name', '$startDate', '$endDate', $discount, $nights, $roomTypeIds, $numOfDaysBeforeArrival, $earlyBirdDayCount, $visible)";
} else {
	$sql = "UPDATE special_offers SET name='$name', start_date='$startDate', end_date='$endDate', discount_pct=$discount, nights=$nights, room_type_ids=$roomTypeIds, valid_num_of_days_before_arrival=$numOfDaysBeforeArrival, early_bird_day_count=$earlyBirdDayCount, visible=$visible WHERE id=$id";
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

$sql = "DELETE FROM special_offer_dates WHERE special_offer_id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot delete old special offer dates in recepcio interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save special offer');
	mysql_close($link);
	return;
}

for($i = 1; $i < 9; $i++) {
	$startDate = $_REQUEST['start_date_' . $i];
	$endDate = $_REQUEST['end_date_' . $i];
	if(isValidDate($startDate) and isValidDate($endDate)) {
		$sql = "INSERT INTO special_offer_dates (special_offer_id, start_date, end_date) VALUES ($id, '$startDate', '$endDate')";
		if(!mysql_query($sql, $link)) {
			trigger_error("Cannot save special offer date in recepcio interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			set_error("Cannot save special offer date: $startDate - $endDate");
		} else {
			set_message("Special offer date ($startDate - $endDate) saved");
		}
	}
}

$sql = "DELETE FROM lang_text WHERE table_name='special_offers' AND row_id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot delete special offer title in recepcio interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save special offer');
	mysql_close($link);
	return;
}

foreach(getLanguages() as $lang => $name) {
	$title = mysql_real_escape_string($_REQUEST["title_$lang"], $link);
	$text = mysql_real_escape_string($_REQUEST["text_$lang"], $link);
	$roomName = mysql_real_escape_string($_REQUEST["room_name_$lang"], $link);
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

function isValidDate($date) {
	if(strlen(trim($date)) == 10) {
		return true;
	} else { 
		return false;
	}
}
?>
