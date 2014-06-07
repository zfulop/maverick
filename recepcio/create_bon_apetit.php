<?php

require("includes.php");

$link = db_connect();

header('Location: view_bon_apetit.php');

$name = $_REQUEST['name'];
$location = $_REQUEST['location'];
$distance = $_REQUEST['distance'];
$hours = $_REQUEST['hours'];
$url = $_REQUEST['url'];
$telephone = $_REQUEST['telephone'];
$order = intval($_REQUEST['order']);
$img = saveUploadedImage('img', BON_APETIT_IMG_DIR, 270, 180);
if($img)
	$img = "'" . basename($img) . "'";
else
	$img = 'NULL';

mysql_query("START TRANSACTION", $link);

$sql = "UPDATE bon_apetit SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change restaurant orders in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new restaurant');
	mysql_close($link);
	return;
}

$sql = "INSERT INTO bon_apetit (name, location, distance_from_hostel, hours, img, url, telephone, _order) VALUES ('$name', '$location', '$distance', '$hours', $img, '$url', '$telephone', $order)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create restaurant in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new restaurant');
	mysql_close($link);
	return;
}

$rowId = mysql_insert_id($link);


foreach(getLanguages() as $lang => $name) {
	$description = $_REQUEST["description_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('bon_apetit', 'description', $rowId, '$lang', '$description')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Can create restaurant text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new service');
		mysql_close($link);
		return;
	}
}

set_message('New restaurant saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
