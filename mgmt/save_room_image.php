<?php

require("includes.php");

$link = db_connect();

$roomTypeId = $_REQUEST['room_type_id'];
$defaultImg = 0;
$id = null;
if(isset($_REQUEST['default_img'])) {
	$defaultImg = 1;
	$sql = "UPDATE room_images SET `default`=0 WHERE room_type_id=$roomTypeId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot unset existing default image in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
}

$order = 0;
if(isset($_REQUEST['order']) and intval($_REQUEST['order']) > 0) {
	$order = intval($_REQUEST['order']);
}

$sql = "UPDATE room_images SET _order=_order+1 WHERE room_type_id=$roomTypeId and _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change ordering of images in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

if(intval($_REQUEST['photo_id']) > 0) {
	$id = intval($_REQUEST['photo_id']);
	$sql = "UPDATE room_images SET room_type_id=$roomTypeId, `default`=$defaultImg, _order=$order WHERE id=$id";
} else {
	$fullpath = saveUploadedImage('photo', ROOMS_IMG_DIR, 1000, 1000);
	$imgFile = basename($fullpath);
	if($imgFile === false)
		set_error("Cannot upload image");
	else
		set_message("Image uploaded ($imgFile)");

	list($width, $height, $type, $attr) = getimagesize($fullpath);
	$sql = "INSERT INTO room_images (filename, room_type_id, width, height, _order, `default`) VALUES ('$imgFile', '$roomTypeId', $width, $height, $order, $defaultImg)";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save image in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
if(is_null($id)) {
	$id = mysql_insert_id($link);
}

$sql = "DELETE FROM lang_text WHERE table_name='room_images' AND row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete existing descriptions of the image: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

foreach(getLanguages() as $langCode => $langName) {
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('room_images', 'description', $id, '$langCode', '" . $_REQUEST["description_$langCode"] . "')";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot save description ($langCode) of the image: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
}


mysql_close($link);
header('Location: view_room_images.php');

?>
