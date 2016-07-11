<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}

$link = db_connect();

for($i = 0; $i < 10; $i++) {
	if(!isset($_REQUEST['room_type_id_' . $i])) {
		continue;
	}
	$roomTypeIds = $_REQUEST['room_type_id_' . $i];
	if(count($roomTypeIds) < 1) {
		continue;
	}

	$fullpath = saveUploadedImage('photo_' . $i, ROOMS_IMG_DIR, 1000, 1000);
	$imgFile = basename($fullpath);
	if(!$fullpath) {
		set_error("Cannot upload image");
		continue;
	} else {
		set_message("Image uploaded ($imgFile)");
	}

	$defaultImg = 0;
	if(isset($_REQUEST['default_img_' . $i])) {
		$defaultImg = 1;
		$sql = "UPDATE room_images SET `default`=0 WHERE room_type_id IN (" . implode(",",$roomTypeIds) . ")";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot unset existing default image in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
	}
	$order = 0;
	if(isset($_REQUEST['order_' . $i]) and intval($_REQUEST['order_' . $i]) > 0) {
		$order = intval($_REQUEST['order_' . $i]);
	}
	$sql = "UPDATE room_images SET _order=_order+1 WHERE room_type_id IN (" . implode(",", $roomTypeIds) . ") AND _order>=$order";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot change ordering of images in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}

	list($width, $height, $type, $attr) = getimagesize($fullpath);
	foreach($roomTypeIds as $roomTypeId) {
		$sql = "INSERT INTO room_images (filename, room_type_id, width, height, _order, `default`) VALUES ('$imgFile', '$roomTypeId', $width, $height, $order, $defaultImg)";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot save image in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
		$id = mysql_insert_id($link);

		foreach(getLanguages() as $langCode => $langName) {
			$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('room_images', 'description', $id, '$langCode', '" . $_REQUEST['description_' . $langCode . '_' . $i] . "')";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot save description ($langCode) of the image: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			}
		}
	}
}

mysql_close($link);
header('Location: view_room_images.php');

?>
