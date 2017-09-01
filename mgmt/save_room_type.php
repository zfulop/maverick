<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

header('Location: delete_extracted_rooms_file.php');

$id = intval($_REQUEST['id']);
$name = $_REQUEST['name'];
$pricePerBed = floatval($_REQUEST['price_per_bed']);
$surchargePerBed = intval($_REQUEST['surcharge_per_bed']);
$pricePerRoom = floatval($_REQUEST['price_per_room']);
$numOfBeds = intval($_REQUEST['num_of_beds']);
$numOfExtraBeds = intval($_REQUEST['num_of_extra_beds']);
$type = $_REQUEST['type'];

$order = intval($_REQUEST['order']);

mysql_query("START TRANSACTION", $link);


if($id < 1) {
	$sql = "UPDATE room_types SET _order=_order+1 WHERE _order>=$order";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot change room types orders in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new room type');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO room_types (name, type, num_of_beds, num_of_extra_beds, price_per_room, price_per_bed, surcharge_per_bed, _order) VALUES ('$name', '$type', $numOfBeds, $numOfExtraBeds, $pricePerRoom, $pricePerBed, $surchargePerBed, $order)";
} else {
	$sql = "UPDATE room_types SET name='$name', type='$type', num_of_beds=$numOfBeds, num_of_extra_beds=$numOfExtraBeds, price_per_room=$pricePerRoom, price_per_bed=$pricePerBed, surcharge_per_bed=$surchargePerBed WHERE id=$id";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create room type in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new room type');
	mysql_close($link);
	return;
}

$inserted = false;
if($id < 1) {
	$id = mysql_insert_id($link);
	$inserted = true;
}


$sql = "DELETE FROM lang_text WHERE table_name='room_types' AND row_id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save room type name in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save room type');
	mysql_close($link);
	return;
}

foreach(getLanguages() as $lang => $name) {
	$name = mysql_real_escape_string($_REQUEST["name_$lang"], $link);
	$description = mysql_real_escape_string($_REQUEST["description_$lang"], $link);
	$shortDescription = mysql_real_escape_string($_REQUEST["short_description_$lang"], $link);
	$size = mysql_real_escape_string($_REQUEST["size_$lang"], $link);
	$location = mysql_real_escape_string($_REQUEST["location_$lang"], $link);
	$bathroom = mysql_real_escape_string($_REQUEST["bathroom_$lang"], $link);
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('room_types', 'name', $id, '$lang', '$name')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Can save room type name in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save room type');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('room_types', 'description', $id, '$lang', '$description')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save room type description in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save room type');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('room_types', 'short_description', $id, '$lang', '$shortDescription')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save room type short_description in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save room type');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('room_types', 'location', $id, '$lang', '$location')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save room type location in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save room type');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('room_types', 'size', $id, '$lang', '$size')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save room type size in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save room type');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('room_types', 'bathroom', $id, '$lang', '$bathroom')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save room type bathroom in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save room type');
		mysql_close($link);
		return;
	}
}

set_message('Room type saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
