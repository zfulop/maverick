<?php

require("includes.php");

$link = db_connect();

$type = $_REQUEST['type'];
$id = null;
if(intval($_REQUEST['photo_id']) > 0) {
	$id = intval($_REQUEST['photo_id']);
	$sql = "UPDATE images SET type='$type' WHERE id=$id";
} else {
	$imgFile = basename(saveUploadedImage('photo', PHOTOS_DIR, 600, 600));
	$thumb = createThumbnail(PHOTOS_DIR . '/' . $imgFile, 115, 115);
	if($imgFile === false)
		set_error("Cannot upload image");
	else
		set_message("Image uploaded ($imgFile)");

	$sql = "INSERT INTO images (filename, type) VALUES ('$imgFile', '$type')";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save image in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
if(is_null($id)) {
	$id = mysql_insert_id($link);
}

$sql = "DELETE FROM lang_text WHERE table_name='images' AND row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete existing descriptions of the image: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

foreach(getLanguages() as $langCode => $langName) {
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('images', 'description', $id, '$langCode', '" . $_REQUEST["description_$langCode"] . "')";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot save description ($langCode) of the image: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
}


mysql_close($link);
header('Location: view_photos.php');

?>
