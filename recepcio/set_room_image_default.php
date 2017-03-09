<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$imgId = intvaL($_REQUEST['room_image_id']);
$rtId = intvaL($_REQUEST['room_type_id']);

logDebug("Unsetting default image for room type: $rtId");
$sql = "UPDATE room_images_room_types SET default_img=0 WHERE room_type_id=$rtId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot unset default image for room type: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

logDebug("Set default image for room type $rtId and image: $imgId");
$sql = "UPDATE room_images_room_types SET default_img=1 WHERE room_type_id=$rtId AND room_image_id=$imgId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot set default image for room type: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

mysql_close($link);
set_message('default image set');
header('Location: view_room_images.php');

?>
