<?php

require("includes.php");

$link = db_connect();

header('Location: view_bon_apetit.php');

$id = intval($_REQUEST['id']);
$name = $_REQUEST['name'];
$location = $_REQUEST['location'];
$distance = $_REQUEST['distance'];
$hours = $_REQUEST['hours'];
$url = $_REQUEST['url'];
$telephone = $_REQUEST['telephone'];
$order = intval($_REQUEST['order']);
if($order < 1) $order = 1;

$img = false;
if(isset($_FILES['img'])) {
	$img = saveUploadedImage('img', BON_APETIT_IMG_DIR, 270, 180);
}

if($img)
	$img = "'" . basename($img) . "'";
else
	$img = 'NULL';


mysql_query("START TRANSACTION", $link);

$sql = "UPDATE bon_apetit SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change restaurant orders: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new restaurant');
	mysql_close($link);
	return;
}

if($id < 1) {
	$sql = "INSERT INTO bon_apetit (name, location, distance_from_hostel, hours, img, url, telephone, _order) VALUES ('$name', '$location', '$distance', '$hours', $img, '$url', '$telephone', $order)";
} else {
	$sql = "UPDATE bon_apetit SET name='$name', location='$location', distance_from_hostel='$distance', hours='$hours', url='$url', telephone='$telephone', _order=$order";
	if($img != 'NULL') {
		$sql .= ", img=$img";
	}
	$sql .= " WHERE id=$id";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save restaurant: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save restaurant');
	mysql_close($link);
	return;
}

$inserted = false;
if($id < 1) {
	$id = mysql_insert_id($link);
	$inserted = true;
}

$sql = "DELETE FROM lang_text WHERE table_name='bon_apetit' AND row_id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save restaurant description in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save restaurant');
	mysql_close($link);
	return;
}
foreach(getLanguages() as $lang => $name) {
	$description = $_REQUEST["description_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('bon_apetit', 'description', $id, '$lang', '$description')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save restaurant description in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save restaurant');
		mysql_close($link);
		return;
	}
}

set_message('Restaurant saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
