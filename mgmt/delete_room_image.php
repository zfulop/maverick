<?php

require("includes.php");

$link = db_connect();

$imgFile = $_REQUEST['file'];
$id = $_REQUEST['id'];
if(file_exists(ROOMS_IMG_DIR . $imgFile)) {
	unlink(ROOMS_IMG_DIR . $imgFile);
}
mysql_query("DELETE FROM lang_text WHERE table_name='room_images' AND row_id=$id", $link);
mysql_query("DELETE FROM room_images WHERE id=$id", $link);
set_message("Image deleted ($imgFile)");


header('Location: view_room_images.php');
mysql_close($link);

?>
