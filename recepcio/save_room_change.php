<?php

require 'includes.php';


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$currDate = str_replace('-', '/', $_REQUEST['date']);
$roomId = $_REQUEST['new_room_id'];

$_SESSION['rearrange_room_changes'][$_REQUEST['booking_id']][$currDate] = $roomId;

echo "OK";

?>
