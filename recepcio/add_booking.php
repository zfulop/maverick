<?php

require("includes.php");
require("room_booking.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}


set_debug("add_booking.php START");

$link = db_connect();

$fnight = $_REQUEST['first_night'];
$lnight = $_REQUEST['last_night'];
$descriptionId = $_REQUEST['booking_description_id'];

header('Location: edit_booking.php?description_id=' . $descriptionId);

list($startYear, $startMonth, $startDay) = explode('/', $fnight);
list($endYear, $endMonth, $endDay) = explode('/', $lnight);
$startDate = "$startYear-$startMonth-$startDay";
$endDate = "$endYear-$endMonth-$endDay";
$rooms  = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);
$roomTypes  = loadRoomTypes($link);
$specialOffers = loadSpecialOffers($startDate, $endDate, $link);


//
// Check to see if the booking does not conflict with the existing bookings...
//
$numOfPersonForRoomType = array();
foreach($roomTypes as $roomTypeId => $roomType) {
	if(isset($_REQUEST['num_of_person_' . $roomTypeId]) and intval($_REQUEST['num_of_person_' . $roomTypeId]) > 0) {
		$numOfPersonForRoomType[] = array('roomTypeIds'=> array($roomTypeId), 'numOfPerson' => array($_REQUEST['num_of_person_' . $roomTypeId]));
	}
}

$overbookings = getOverbookings($numOfPersonForRoomType, $startDate, $endDate, $rooms);
$error = false;
$warning = false;
if(count($overbookings) > 0) {
	foreach($overbookings as $roomTypeId => $datesUnavailable) {
		$roomName = $roomTypes[$roomTypeId]['name'];
		$numOfPerson = $numOfPersonForRoomType[$roomTypeId];
		$datesUnavailableStr = '';
		foreach($datesUnavailable as $currDate => $availableBeds) {
			$datesUnavailableStr .= ", $currDate - there are only $availableBeds beds available";
		}
		$datesUnavailableStr = substr($datesUnavailableStr, 2);
		if(isDorm($roomTypes[$roomTypeId])) {
			set_warning("Overbooking: For the dormitory room: $roomName and dates: $datesUnavailableStr. ");		
			$warning = true;
		} elseif(isPrivate($roomTypes[$roomTypeId])) {
			set_error("Overbooking: For the private room: $roomName and dates: $datesUnavailableStr. The booking cannot be saved.");
			$error = true;
		} elseif(isApartment($roomTypes[$roomTypeId])) {
			set_error("Overbooking: For the apartment: $roomName and dates: $datesUnavailableStr. The booking cannot be saved.");
			$error = true;
		}
	}
}

if($error) {
	mysql_close($link);
	return;
}

if($warning) {
	set_warning('The booking is saved, please make sure that the extra beds are available at arrival.');
}


// We can save the booking now!
logDebug("We can add the booking now!");


// Now create an array: $toBook that contains the roomId as a key and the value contains the number
// of people and the type (ROOM or BED) of the booking.
list($toBook, $roomChanges) = getBookingData($numOfPersonForRoomType, $startDate, $endDate, $rooms, $roomTypes);

$bookingIds = saveBookings($toBook, $roomChanges, $startDate, $endDate, $rooms, $roomTypes, $specialOffers, $descriptionId, $link);
audit(AUDIT_ADD_BOOKING, array('booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $descriptionId, $link);

set_message('Booking added');
mysql_close($link);



?>
