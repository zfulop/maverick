<?php

require("includes.php");
require("room_booking.php");

set_debug("create_booking.php START");

foreach($_REQUEST as $code => $val) {
	$_SESSION['ENB_' . $code] = $val;
}

$link = db_connect();

$name = $_REQUEST['name'];
$nameExt = $_REQUEST['name_ext'];
$gender = $_REQUEST['gender'];
$addr = $_REQUEST['address'];
$nat = $_REQUEST['nationality'];
$email = $_REQUEST['email'];
$tel = $_REQUEST['telephone'];
$fnight = $_REQUEST['first_night'];
$lnight = $_REQUEST['last_night'];
$deposit = $_REQUEST['deposit'];
$depositCurrency = $_REQUEST['deposit_currency'];
$comment = $_REQUEST['comment'];
$source = $_REQUEST['source'];
$arrivalTime = $_REQUEST['arrival_time'];
$numOfNights = round((strtotime(str_replace('/', '-', $lnight)) - strtotime(str_replace('/', '-', $fnight))) / (60*60*24)) + 1;

list($startYear, $startMonth, $startDay) = explode('/', $fnight);
list($endYear, $endMonth, $endDay) = explode('/', $lnight);
$startDate = "$startYear-$startMonth-$startDay";
$endDate = "$endYear-$endMonth-$endDay";
$rooms  = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);
$roomTypes  = loadRoomTypes($link);
$specialOffers = loadSpecialOffers("start_date<='$startDate' AND end_date>='$endDate'", $link);


$sql = "SELECT rt.*, count(*) as num_of_rooms FROM room_types rt inner join rooms r on (rt.id=r.room_type_id) group by rt.id";
$result = mysql_query($sql, $link);
$roomTypes = array();
while($row = mysql_fetch_assoc($result)) {
	$roomTypes[$row['id']] = $row;
}



//
// Check to see if the booking does not conflict with the existing bookings...
//
$numOfPersonForRoomType = array();
foreach($roomTypes as $roomTypeId => $roomType) {
	$numOfPersonForRoomType[$roomTypeId] = $_REQUEST['num_of_person_' . $roomTypeId];
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
	header('Location: edit_new_booking.php');
	mysql_close($link);
	return;
}

if($warning) {
	set_warning('The booking is saved, please make sure that the extra beds are available at arrival.');
}



// We can save the booking now!
set_debug("We can save the booking now!");

// Now create an array: $toBook that contains the roomId as a key and the value contains the number
// of people and the type (ROOM or BED) of the booking.
list($toBook, $roomChanges) = getBookingData($numOfPersonForRoomType, $startDate, $endDate, $rooms, $roomTypes);

$sql = "INSERT INTO booking_descriptions (name, name_ext, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time) VALUES ('$name', '$nameExt', '$gender', '$addr', '$nat', '$email', '$tel', '$fnight', '$lnight', $numOfNights, 0, 0, 0, 0, '$comment', '$source', '$arrivalTime')";
set_debug($sql);

if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save booking: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Could not save booking description.');
	header('Location: edit_new_booking.php');
	mysql_close($link);
	return;
}
$descriptionId = mysql_insert_id($link);

$nowTime = date('Y-m-d H:i:s');
if($deposit > 0) {
	$sql = "INSERT INTO payments (booking_description_id, amount, currency, time_of_payment, comment) VALUES('$descriptionId', '$deposit', '$depositCurrency', '$nowTime', '*booking deposit*' )";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save booking deposit: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Could not save booking deposit.');
		header('Location: edit_new_booking.php');
		mysql_close($link);
		return;
	}
}

$bookingIds = saveBookings($toBook, $roomChanges, $startDate, $endDate, $rooms, $roomTypes, $specialOffers, $descriptionId, $link);
audit(AUDIT_CREATE_BOOKING, array('booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $descriptionId, $link);


set_message('Booking saved');
foreach($_SESSION as $code => $val) {
	if(substr($code, 0, 4) == 'ENB_')
		unset($_SESSION[$code]);
}
header('Location: view_availability.php');
mysql_close($link);




?>
