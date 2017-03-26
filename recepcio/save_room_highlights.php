<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$roomTypes = RoomDao::getRoomTypes('eng', $link);

$roomHighlights = array();

foreach($roomTypes as $rtId => $roomType) {
	if(isset($_REQUEST[$rtId])) {
		$roomHighlights[] = $rtId;
	}
}

if(RoomDao::saveRoomHighlights($roomHighlights, $link)) {
	set_message("Room highlights saved");
} else {
	set_error("Cannot save room highlights");
}


mysql_close($link);

header('Location: view_room_highlights.php');


?>
