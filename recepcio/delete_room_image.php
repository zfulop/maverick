<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}

header('Location: view_room_images.php');

$link = db_connect();

$id = intval($_REQUEST['room_image_id']);

logDebug("Deleting room image assiciations with room image id: $id");
$sql = "DELETE FROM room_images_room_types WHERE room_image_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete existing room image room type linkages: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	set_error("Cannot delete room image");
	return;
}

logDebug("Deleting room image record");
$sql = "DELETE FROM room_images WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete existing room image row: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	set_error("Cannot delete room image");
	return;
}


mysql_close($link);
set_message('room image deleted');

?>
