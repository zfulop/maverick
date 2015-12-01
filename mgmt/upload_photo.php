<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$imgFile = saveUploadedImage('photo', PHOTOS_DIR, 600, 600);
$thumb = createThumbnail($imgFile, 115, 115);
if($imgFile === false)
	set_error("Cannot upload image");
else
	set_message("Image uploaded (" . basename($imgFile) . ")");

header('Location: view_photos.php');

?>
