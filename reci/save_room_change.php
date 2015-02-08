<?php

require 'includes.php';

$currDate = str_replace('-', '/', $_REQUEST['date']);
$roomId = $_REQUEST['new_room_id'];

$_SESSION['rearrange_room_changes'][$_REQUEST['booking_id']][$currDate] = $roomId;

echo "OK";

?>
