<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}



$link = db_connect();

$imgFile = $_REQUEST['file'];
$id = $_REQUEST['id'];
if(file_exists(PHOTOS_DIR . "/" . $imgFile)) {
	unlink(PHOTOS_DIR . "/" . $imgFile);
	unlink(PHOTOS_DIR . "/_thumb_" . $imgFile);
	mysql_query("DELETE FROM lang_text WHERE table_name='images' AND row_id=$id", $link);
	mysql_query("DELETE FROM images WHERE id=$id", $link);
	set_message("Image deleted ($imgFile)");
} else {
	set_error("Image not found ($imgFile)");
}


header('Location: view_photos.php');
mysql_close($link);

?>
