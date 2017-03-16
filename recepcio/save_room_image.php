<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}

$location = $_SESSION['login_hotel'];
$link = db_connect();
logDebug("Saving image into directory: " . ROOMS_IMG_DIR . $location . '/');
$fullpath = saveUploadedImage('file', ROOMS_IMG_DIR . $location . '/', 1000, 1000);
logDebug("Full path of the file: $fullPath ");
$imgFile = basename($fullpath);
if(!$fullpath) {
	logError("Cannot upoioad image");
	set_error("Cannot upload image");
} else {
	set_message("Image uploaded ($imgFile)");
}

$thumb = createThumbnail('thumb_', $fullpath, 375, 375);
$mid = createThumbnail('mid_', $fullpath, 700, 700);
$thumb = basename($thumb);
$mid = basename($mid);
list($width, $height, $type, $attr) = getimagesize($fullpath);
$sql = "INSERT INTO room_images (filename, room_type_id, width, height, thumb, medium) VALUES ('$imgFile', '$roomTypeId', $width, $height, '$thumb', '$mid')";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save image in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo "Cannot save image";
} else {
	logDebug("Image saved in the db");
	echo "Image saved";
}
mysql_close($link);
?>
