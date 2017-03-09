<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$id = $_REQUEST['room_image_id']; // image id
$roomTypeIds = $_REQUEST['room_types'];

logDebug("Getting which room type the image (with id: $id) is default");
$sql = "SELECT room_type_id FROM room_images_room_types WHERE room_image_id=$id AND default_img=1";
$result = mysql_query($sql, $link);
$defaultRoomTypesIds = array();
while($row = mysql_fetch_assoc($result)) {
	$defaultRoomTypesIds[] = $row['room_type_id'];
	logDebug("	default for " . $row['room_type_id']);
}

logDebug("Deleting existing room type associations");
$sql = "DELETE FROM room_images_room_types WHERE room_image_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete existing room image room type linkages: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

logDebug("Associating image with the selected room types");
foreach($roomTypeIds as $rtId) {
	$def = (in_array($rtId, $defaultRoomTypesIds) ? 1 : 0);
	logDebug("   making connection for room type: $rtId. Is default: $def");
	$sql = "INSERT INTO room_images_room_types (room_image_id, room_type_id, default_img) VALUES ($id, $rtId, $def)";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot save room image room type association: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
}

logDebug("Deleting existing image descriptions");
$sql = "DELETE FROM lang_text WHERE table_name='room_images' AND column_name='description' AND row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete existing room image descriptions: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

foreach(getLanguages() as $code => $langName) {
	$value = mysql_real_escape_string($_REQUEST[$code], $link);
	logDebug("Saving image description for $code to be $value");
	$sql = "INSERT INTO lang_text (table_name,column_name,row_id,lang,value) VALUES ('room_images','description',$id,'$code','$value')";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot insert new room image descriptions: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
}

mysql_close($link);
set_message('image data saved');
header('Location: view_room_images.php');

?>
