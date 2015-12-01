<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

header('Location: view_have_fun.php');

$name = $_REQUEST['name'];
$location = $_REQUEST['location'];
$distance = $_REQUEST['distance'];
$time = $_REQUEST['time'];
$url = $_REQUEST['url'];
$telephone = $_REQUEST['telephone'];
$order = intval($_REQUEST['order']);
if($order < 1) $order = 1;
$hfId = intval($_REQUEST['id']);

$img = false;
if(isset($_FILES['img'])) {
	$img = saveUploadedImage('img', HAVE_FUN_IMG_DIR, 270, 180);
}

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

if($hfId < 1) {
	$sql = "INSERT INTO have_fun (name, location, distance_from_hostel, time_of_show, img, url, telephone, _order) VALUES ('$name', '$location', '$distance', '$time', $img, '$url', '$telephone', $order)";
} else {
	$sql = "UPDATE have_fun SET name='$name', location='$location', distance_from_hostel='$distance', time_of_show='$time', url='$url', telephone='$telephone', _order=$order";
	if($img != 'NULL') {
		$sql .= ", img=$img";
	}
	$sql .= " WHERE id=$hfId";
}
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save event in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save event');
	mysql_close($link);
	return;
}

$inserted = false;
if($hfId < 1) {
	$hfId = mysql_insert_id($link);
	$inserted = true;
}

$sql = "DELETE FROM lang_text WHERE table_name='have_fun' AND row_id=$hfId";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save event description in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save event');
	mysql_close($link);
	return;
}
foreach(getLanguages() as $lang => $name) {
	$description = $_REQUEST["description_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('have_fun', 'description', $hfId, '$lang', '$description')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save event description in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save event');
		mysql_close($link);
		return;
	}
}

set_message('Event saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
