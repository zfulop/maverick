<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$location = $_SESSION['login_hotel'];

$roomTypes = RoomDao::getRoomTypes('eng', $link);
$roomImages = RoomDao::getRoomImages(array_keys(getLanguages()), $link);

logDebug("There are " . count($roomImages) . " room images to show");
if(count($roomImages) < 1) {
	trigger_error("No room images");
}
foreach($roomImages as $riId => $roomImage) {
	$id = $roomImage['id'];
	$src = ROOMS_IMG_URL . $location . '/' . $roomImage['thumb'];
	$roomTypesTxt = '';
	foreach($roomImage['room_types']  as $rtId) {
		$roomTypesTxt .= '<li>' . $roomTypes[$rtId]['name'] . '</li>';
	}
	echo <<<EOT
<div class="room_image" title="edit image" style="border:black solid 1px; float:left; padding:5px; margin: 5px; height:150px;" id="room_image_$id">
	<a href="#" onclick="editImage($id);return false;"><img src="$src" style="height: 100px;"></a><br>
	<ul>$roomTypesTxt</ul>
</div>

EOT;
}

mysql_close($link);


?>