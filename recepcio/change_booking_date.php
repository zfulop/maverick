<?php

require("includes.php");
require("room_booking.php");

$descrId = intval($_REQUEST['booking_description_id']);
header('Location: edit_booking.php?description_id=' . $descrId);

$link = db_connect();


$fnight = $_REQUEST['first_night'];
$lnight = $_REQUEST['last_night'];
$oldFnight = $_REQUEST['old_first_night'];
$oldLnight = $_REQUEST['old_last_night'];
$numOfNights = round((strtotime(str_replace('/', '-', $lnight)) - strtotime(str_replace('/', '-', $fnight))) / (60*60*24)) + 1;

set_debug("Changing booking dates from: $oldFnight - $oldLnight to: $fnight - $lnight");


// load booking:
$sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
$result = mysql_query($sql, $link);
$bookingDescription = mysql_fetch_assoc($result);
$bookings = array();
$sql = "SELECT * FROM bookings WHERE description_id=$descrId";
$result = mysql_query($sql, $link);
$bookingIds = array();
while($row = mysql_fetch_assoc($result)) {
	$bookings[$row['room_id']] = $row;
	$bookingIds[] = $row['id'];
}


list($startYear, $startMonth, $startDay) = explode('/', $fnight);
list($endYear, $endMonth, $endDay) = explode('/', $lnight);

$rooms  = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

$numOfPersonForRoomType = array();
if($fnight < $oldFnight or $lnight > $oldLnight) {
	$numOfPersonForRoomCode = array();
	foreach($rooms as $roomId => $roomData) {
		$numOfPerson = 0;
		if(isset($bookings[$roomId])) {
			$numOfPerson += $bookings[$roomId]['num_of_person'];
		}
		if(!isset($numOfPersonForRoomType[$roomData['room_type_id']]) and $numOfPerson > 0) {
			$numOfPersonForRoomType[$roomData['room_type_id']] = $numOfPerson;
		} elseif($numOfPerson > 0) {
			$numOfPersonForRoomType[$roomData['room_type_id']] += $numOfPerson;
		}
	}

	// Now check the overbooking for the dates that the change involves:
	$overbookingsBefore = array();
	$overbookingsAfter = array();
	if($fnight < $oldFnight) {
		$overbookingsBefore = getOverbookings($numOfPersonForRoomType, str_replace('/', '-', $fnight), date('Y-m-d', strtotime(str_replace('/', '-', $oldFnight) . " -1 day")), $rooms);
	}
	if($lnight > $oldLnight) {
		$overbookingsAfter = getOverbookings($numOfPersonForRoomType, date('Y-m-d', strtotime(str_replace('/', '-', $oldLnight) . " +1 day")), str_replace('/', '-', $lnight),  $rooms);
	}

	$overbookings = $overbookingsBefore;
	foreach($overbookingsAfter as $roomTypeId => $datesUnavailable) {
		if(isset($overbookings[$roomType])) {
			$overbookings[$roomTypeId] = array_merge($overbookings[$roomTypeId], $datesUnavailable);
		} else {
			$overbookings[$roomTypeId] = $datesUnavailable;
		}
	}

	if(count($overbookings) > 0) {
		printErrorOverbookings($numOfPersonForRoomType, $overbookings, $roomTypes);
	}
}


// We can save the bookings now.
$dates = array();
$fnight = str_replace('/', '-', $fnight);
$oldFnight = str_replace('/', '-', $oldFnight);
$lnight = str_replace('/', '-', $lnight);
$oldLnight = str_replace('/', '-', $oldLnight);
for($currDate = $fnight; $currDate < $oldFnight; $currDate = date('Y-m-d', strtotime($currDate . " +1 day"))) {
	$dates[] = $currDate;
}
for($currDate = date('Y-m-d', strtotime($oldLnight . " +1 day")); $currDate <= $lnight; $currDate = date('Y-m-d', strtotime($currDate . " +1 day"))) {
	$dates[] = $currDate;
}
$roomChanges = array();
foreach($bookings as $oneBooking) {
	foreach($dates as $oneDate) {
		$avail = getNumOfAvailBeds($rooms[$oneBooking['room_id']], $oneDate);
		if($avail < $oneBooking['num_of_person']) {
			$roomTypeId = $rooms[$oneBooking['room_id']]['room_type_id'];
			$newRoomId = null;
			foreach($rooms as $oneRoomId => $roomData) {
				if($roomData['room_type_id'] != $roomTypeId) {
					continue;
				}
				$avail = getNumOfAvailBeds($roomData, $oneDate);
				if($avail >= $oneBooking['num_of_person']) {
					$newRoomId = $oneRoomId;
					break;
				}
			}
			if(!is_null($newRoomId)) {
				$roomChanges[$oneBooking['id']][$oneDate] = $newRoomId;
			}
		}
	}
}


$error = false;
$fnight = str_replace('-', '/', $fnight);
$lnight = str_replace('-', '/', $lnight);
$sql = "UPDATE booking_descriptions SET first_night='$fnight', last_night='$lnight', num_of_nights=$numOfNights WHERE id=$descrId";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot change date of booking: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Error saving booking date change");
	$error = true;
}

$sql = "DELETE FROM booking_room_changes WHERE (date_of_room_change<'$fnight' OR  date_of_room_change>'$lnight') AND booking_id IN (" . implode(",", $bookingIds) . ")";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot remove room changes for dates that are no more valid: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot remove room changes for dates that are no more valid");
	$error = true;
}

foreach($roomChanges as $bookingId => $changes) {
	foreach($changes as $oneDate => $newRoomId) {
		$oneDate = str_replace('-', '/', $oneDate);
		$sql = "INSERT INTO booking_room_changes(booking_id, date_of_room_change, new_room_id) VALUES ($bookingId, '$oneDate', $newRoomId)";
		if(!mysql_query($sql, $link)) {
			trigger_error("Cannot add new room change: " . mysql_error($link) . " (SQL: $sql)");
			set_error("Cannot add new room change");
			$error = true;
		}
	}
}

foreach($bookings as $roomId => $oneBooking) {
	$numOfPerson = $oneBooking['num_of_person'];
	$prc = getPrice(strtotime(str_replace('/','-',$fnight)), $numOfNights, $rooms[$roomId], $numOfPerson);
	$sql = "UPDATE bookings SET room_payment='$prc' WHERE id=" . $oneBooking['id'];
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot update booking's room payment: " . mysql_error($link) . " (SQL: $sql)");
		set_error("Cannot update booking's room payment");
		$error = true;
	}
}

if(!$error) {
	audit(AUDIT_CHANGE_BOOKING_DATE, array('first_night' => $fnight, 'last_ngith' => $lnight), 0, $descrId, $link);
	set_message("Booking's date changed to $fnight - $lnight");
}

mysql_close($link);
return;


function printErrorOverbookings(&$numOfPersonForRoomType, &$overbookings, &$roomTypes) {
	foreach($overbookings as $roomTypeId => $datesUnavailable) {
		$roomName = $roomTypes[$roomTypeId]['name'];
		$numOfPerson = $numOfPersonForRoomCode[$roomTypeId];
		$datesUnavailableStr = '';
		foreach($datesUnavailable as $currDate => $availableBeds) {
			$datesUnavailableStr .= ", $currDate - there are only $availableBeds beds available";
		}
		$datesUnavailableStr = substr($datesUnavailableStr, 2);
		set_warning("Overbooking: For the room: $roomName and dates: $datesUnavailableStr. The booking is saved, please make sure that the extra beds are available at arrival.");

	}
}

?>
