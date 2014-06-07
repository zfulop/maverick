<?php

require("includes.php");

$link = db_connect();

header('Location: view_awards.php');

$id = intval($_REQUEST['id']);
$name = $_REQUEST['name'];
$url = $_REQUEST['url'];
$order = intval($_REQUEST['order']);
if($order < 1) $order = 1;

$img = false;
if(isset($_FILES['img'])) {
	$img = saveUploadedImage('img', AWARDS_IMG_DIR, 270, 180);
}

if($img)
	$img = "'" . basename($img) . "'";
else
	$img = 'NULL';


mysql_query("START TRANSACTION", $link);

$sql = "UPDATE awards SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change awards orders: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save award');
	mysql_close($link);
	return;
}

if($id < 1) {
	$sql = "INSERT INTO awards (name, img, url, _order) VALUES ('$name', $img, '$url', $order)";
} else {
	$sql = "UPDATE awards SET name='$name', url='$url', _order=$order";
	if($img != 'NULL') {
		$sql .= ", img=$img";
	}
	$sql .= " WHERE id=$id";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save award: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save award');
	mysql_close($link);
	return;
}

$inserted = false;
if($id < 1) {
	$id = mysql_insert_id($link);
	$inserted = true;
}

$sql = "DELETE FROM lang_text WHERE table_name='awards' AND row_id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save award description in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save awards');
	mysql_close($link);
	return;
}
foreach(getLanguages() as $lang => $name) {
	$description = $_REQUEST["description_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('awards', 'description', $id, '$lang', '$description')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save award description in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save award');
		mysql_close($link);
		return;
	}
}

set_message('award saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
