<?php

require 'includes.php';


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$currDate = str_replace('-', '/', $_REQUEST['date']);
$roomIds = array();
for($i = 1; $i < 10; $i++) {
	if(isset($_REQUEST['room_id_' . $i])) {
		$roomIds[] = $_REQUEST['room_id_' . $i];
	}
}

$link = db_connect();

$roomData = array();
$sql = "SELECT r.*, rt.num_of_beds FROM rooms r INNER JOIN room_types rt ON (r.room_type_id=rt.id) WHERE r.id IN (" . implode(",", $roomIds) . ")";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$roomData[$row['id']] = $row;
	}
}

/*
echo "roomIds: ";
print_r($roomIds);
echo "roomData: ";
print_r($roomData);
 */

$bookings = array();
$sql = "SELECT b.*, bd.first_night, bd.name, bd.last_night FROM bookings b INNER JOIN booking_descriptions bd ON b.description_id=bd.id WHERE bd.last_night>='$currDate' AND bd.first_night<='$currDate' AND bd.cancelled<>1";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$bookings[$row['id']] = $row;
	}
}

$sql = "SELECT * FROM booking_room_changes WHERE date_of_room_change='$currDate' AND booking_id IN (" . implode(',', array_keys($bookings)) . ")";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$bookings[$row['booking_id']]['room_change'] = $row['new_room_id'];
	}
}

$responses = array();
foreach($roomIds as $roomId) {
	$numPerson = 0;
//	echo "Checking room: $roomId\n";
	foreach($bookings as $oneBooking) {
		$roomChange = null;
		if(isset($oneBooking['room_change'])) {
			$roomChange = $oneBooking['room_change'];
		}
		if(isset($_SESSION['rearrange_room_changes'][$oneBooking['id']][$currDate])) {
			$roomChange = $_SESSION['rearrange_room_changes'][$oneBooking['id']][$currDate];
		}
		if($oneBooking['room_id'] == $roomId) {
			if(is_null($roomChange) or $roomChange == $roomId) {
				$numPerson += $oneBooking['num_of_person'];
//				echo "Counting booking 1: ";
//				print_r($oneBooking);
			}
		} else {
			if($roomChange == $roomId) {
				$numPerson += $oneBooking['num_of_person'];
//				echo "Counting booking 2: ";
//				print_r($oneBooking);
			}
		}
	}

	$numBeds = $roomData[$roomId]['num_of_beds'];

	if($numBeds < $numPerson) {
		$responses[] = "For room: " . $roomData[$roomId]['name'] . "($roomId) There are $numPerson people, but there are only $numBeds beds.";
	} else {
		$responses[] = "OK";
	}
}

mysql_close($link);

echo implode('|', $responses);

?>
