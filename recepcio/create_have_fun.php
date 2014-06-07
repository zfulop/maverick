<?php

require("includes.php");

$link = db_connect();

header('Location: view_have_fun.php');

$name = $_REQUEST['name'];
$location = $_REQUEST['location'];
$distance = $_REQUEST['distance'];
$time = $_REQUEST['time'];
$url = $_REQUEST['url'];
$telephone = $_REQUEST['telephone'];
$order = $_REQUEST['order'];

$img = saveUploadedImage('img', HAVE_FUN_IMG_DIR, 270, 180);
if($img)
	$img = "'" . basename($img) . "'";
else
	$img = 'NULL';

mysql_query("START TRANSACTION", $link);

$sql = "UPDATE have_fun SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change event orders in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new event');
	mysql_close($link);
	return;
}


$sql = "INSERT INTO have_fun (name, location, distance_from_hostel, time_of_show, img, url, telephone, _order) VALUES ('$name', '$location', '$distance', '$time', $img, '$url', '$telephone', $order)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create event in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new event');
	mysql_close($link);
	return;
}

$rowId = mysql_insert_id($link);


foreach(getLanguages() as $lang => $name) {
	$description = $_REQUEST["description_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('have_fun', 'description', $rowId, '$lang', '$description')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Can create event text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new event');
		mysql_close($link);
		return;
	}
}

set_message('New event saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
