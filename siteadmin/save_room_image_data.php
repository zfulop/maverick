<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$id = $_REQUEST['id']; // image id
$roomTypeId = $_REQUEST['rtid'];
$defaultImg = 0;
if(isset($_REQUEST['default'])) {
	$defaultImg = 1;
	$sql = "UPDATE room_images SET `default`=0 WHERE room_type_id=$roomTypeId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot unset existing default image in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
}

$order = intval($_REQUEST['order']);
$sql = "UPDATE room_images SET _order=_order+1 WHERE room_type_id=$roomTypeId AND _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change ordering of images in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

$sql = "UPDATE room_images SET _order=$order, `default`=$defaultImg WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save image in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

$sql = "DELETE FROM lang_text WHERE table_name='room_images' AND row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete existing descrptions in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

foreach(getLanguages() as $langCode => $langName) {
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('room_images', 'description', $id, '$langCode', '" . $_REQUEST[$langCode] . "')";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot save description ($langCode) of the image: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
}

mysql_close($link);
set_message('image data saved');
header('Location: view_room_images.php');

?>
