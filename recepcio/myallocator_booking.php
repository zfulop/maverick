<?php

require('includes.php');
require('room_booking.php');

define('HASHED_PASSWORD', '$1$rM3.YS0.$.BMdC5Qd31wO6VUArIhb21');

$ROOM_MAP = array(
	'hostel' => array(
		array(
			'roomName' => 'The_Blue_Brothers_6_Bed',
			'roomTypeId' => 35,
			'remoteRoomId' => '9131'
			),
		array(
			'roomName' => 'Mss_Peach_5_Bed',
			'roomTypeId' => 36,
			'remoteRoomId' => '9130'
			),
		array(
			'roomName' => 'Double_room_shared_bathroom',
			'roomTypeId' => 39,
			'remoteRoomId' => '9133'
			),
		array(
			'roomName' => 'Double_room_private_bathroom_ensuites_with_NEW_rooms',
			'roomTypeId' => 46,
			'remoteRoomId' => '9134'
			),
		array(
			'roomName' => 'NEW_Maverick_ensuite_3_Bed',
			'roomTypeId' => 59,
			'remoteRoomId' => '9135'
			),
		array(
			'roomName' => 'NEW_Maverick_ensuite_4_Bed',
			'roomTypeId' => 60,
			'remoteRoomId' => '9136'
			),
		array(
			'roomName' => 'NEW_Maverick_ensuite_5_Bed',
			'roomTypeId' => 61,
			'remoteRoomId' => '9137'
		),
		array(
			'roomName' => 'Mr Green',
			'roomTypeId' => 42,
			'remoteRoomId' => '9132'
			)
		),
	'lodge' => array(
		array(
			'roomName' => 'Double Private Room with shared bathroom',
			'roomTypeId' => 66,
			'remoteRoomId' => '10035'
			),
		array(
			'roomName' => 'Double Private Ensuite Room',
			'roomTypeId' => 65,
			'remoteRoomId' => '10036'
			),
		array(
			'roomName' => 'Triple Private Ensuite Room',
			'roomTypeId' => 67,
			'remoteRoomId' => '10037'
			),
		array(
			'roomName' => 'Quadruple Private Ensuite Room',
			'roomTypeId' => 68,
			'remoteRoomId' => '10038'
			),
		array(
			'roomName' => '4 bed mixed dorm',
			'roomTypeId' => 71,
			'remoteRoomId' => '10066'
			),
		array(
			'roomName' => '6 bed mixed dorm',
			'roomTypeId' => 42,
			'remoteRoomId' => '10067'
			),
		array(
			'roomName' => '8 bed mixed dorm',
			'roomTypeId' => 69,
			'remoteRoomId' => '10068'
			),
		array(
			'roomName' => '107 Female',
			'roomTypeId' => 72,
			'remoteRoomId' => '16475'
			)
		)
	);

$MESSAGES = array(
	'10' => 'Password wrong or not set.',
	'20' => 'Error while parsing JSON structure (including the exception message, for example position of not parsable part)',
	'21' => 'Error while parsing JSON structure (exact reason unknown)',
	'22' => 'Error while parsing JSON content',
	'30' => 'PropertyId is NULL or 0',
	'31' => 'Property not used on your PMS anymore',
	'35' => 'MyallocatorId already existing',
	'51' => 'Could not save booking - DB error',
	'52' => 'Could not find room for the myallocator room type id specified',
	'53' => 'Cannot cancel booking because the 1st night is less than 2 days away',
	'54' => 'Cannot create booking because start date is in the past',
	'55' => 'Cannot create booking because start date is the same of after the end date',
	'56' => 'Cannot create booking because there is already a booking with the same myallocator id'
);

$lang = 'eng';

require(LANG_DIR . $lang . '.php');

$bookingJson = $_REQUEST['booking'];
$pwd = $_REQUEST['password'];

if(crypt($pwd, HASHED_PASSWORD) !=  HASHED_PASSWORD) {
	respond('10', false, "Incorrect password");
	return;
}

$bookingJson = stripslashes($bookingJson);

//echo "<br>\nJSON:<br>\n<pre>";
//var_dump($bookingJson);
//echo "</pre>\n";

$bookingData = json_decode($bookingJson, true);

//echo "<br>\nparsed:<br><pre>\n";
//var_dump($bookingData);
//echo "</pre>\n";

$link = db_connect();

mysql_query("START TRANSACTION", $link);

$result = null;
if($bookingData['IsCancellation']) {
	$result = cancelBooking($bookingData['MyAllocatorId'], $link);
} else {
	$result = createBooking($bookingData, $link);
}

if(!$result) {
	mysql_query("ROLLBACK", $link);
} else {
	mysql_query("COMMIT", $link);
}


mysql_close($link);
// End of program


function cancelBooking($myAllocatorId, $link) {
	global $lang;
	$sql = "SELECT * FROM booking_descriptions WHERE my_allocator_id='$myAllocatorId'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		respond('51', false, "Cannot retieve booking in admin interface when canceling it: " . mysql_error($link) . " (SQL: $sql)");
		return false;
	}
	$row = mysql_fetch_assoc($result);
	$descrId = $row['id'];
	list($year, $month, $day) = explode('/', $row['first_night']);
	if(time() > strtotime("$year-$month-$day + 2 day")) {
		respond('53', false, 'Cannot cancel booking (Name: ' . $row['name'] . ') because the 1st night is less than 2 days away (first night: ' . $row['first_night'] . ')');
		return false;
	}

	$sql = "UPDATE booking_descriptions SET cancelled=1,cancel_type='guest' WHERE id=$descrId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		respond('51', false, "Cannot cancel booking in admin interface: " . mysql_error($link) . " (SQL: $sql)");
		return false;
	}

	audit(AUDIT_CANCEL_BOOKING, $_REQUEST, 0, $descrId, $link, 'myallocator');
	return true;
}


function createBooking($bookingData, $link) {
	global $lang, $bookingJson;
	if(!isset($bookingData['Rooms']) or !is_array($bookingData['Rooms']) or count($bookingData['Rooms']) < 1) {
		respond('22', false, 'Rooms element not in the request or not an array or an empty array');
		return false;
	}
	if(!isset($bookingData['Customers']) or !is_array($bookingData['Customers']) or count($bookingData['Customers']) < 1) {
		respond('22', false, 'Customers element not in the request or not an array or an empty array');
		return false;
	}


	$myAllocatorId = $bookingData['MyallocatorId'];
	$sql = "SELECT * FROM booking_descriptions WHERE my_allocator_id='$myAllocatorId'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		respond('51', false, "Cannot retieve booking in admin interface when canceling it: " . mysql_error($link) . " (SQL: $sql)");
		return false;
	}
	if(mysql_num_rows($result) > 0) {
		respond('56', false, "Cannot create booking a booking with this myallocatorid ($myAllocatorId) already exists.");
		return false;
	}


	$currency = $bookingData['TotalCurrency'];
	$customer = $bookingData['Customers'][0];
	$firstname = $customer['CustomerFName'];
	$lastname = $customer['CustomerLName'];
	$email = $customer['CustomerEmail'];
	$phone = '';
	$nationality = $customer['CustomerCountry'];
	$city = $customer['CustomerCity'];
	$country = $customer['CustomerCountry'];
	$address = $city . ', ' . $country;
	$comment = $bookingJson;

	$source = $bookingData['OrderSource'];
	$roomTypesData = loadRoomTypes($link, $lang);

	$bookingDescriptionIds = array();
	$allSameDate = true;
	$arriveDate = null;
	$departureDate = null;
	foreach($bookingData['Rooms'] as $roomData) {
		if(is_null($arriveDate)) {
			$arriveDate = $roomData['StartDate'];
			$departureDate = $roomData['EndDate'];
		} elseif($arriveDate != $roomData['StartDate'] or $departureDate != $roomData['EndDate']) {
			$allSameDate = false;
			break;
		}
	}

	if($allSameDate) {
		$arriveDateTs = strtotime($arriveDate);
		$departureDateTs = strtotime($departureDate);
		if($arriveDate < date('Y-m-d')) {
			respond('54', false, "Cannot create booking: arrive date ($arriveDate) is in the past");
			return false;
		}
		if($arriveDate >= $departureDate) {
			respond('55', false, "Cannot create booking: arrive date ($arriveDate) must be before departureDate ($departureDate)");
			return false;
		}

		$nights = round(($departureDateTs-$arriveDateTs) / (60*60*24));
		$lastNightTs = strtotime($departureDate . " -1 day");
		$lastNight = date('Y-m-d', $lastNightTs);

		$sql = "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time, language, currency, my_allocator_id) VALUES ('$firstname $lastname', NULL, '$address', '$nationality', '$email', '$phone', '" . str_replace("-", "/", $arriveDate) . "', '" . str_replace("-", "/", $lastNight) . "', $nights, 0, 0, 0, 0, '$comment', '$source', '', '$lang', '$currency', '$myAllocatorId')";
		set_debug($sql);
		if(!mysql_query($sql, $link)) {
			respond('51', false, 'Cannot create booking description: ' . mysql_error($link) . " (SQL: $sql)");
			return;
		}
		$descriptionId = mysql_insert_id($link);
		$bookingDescriptionIds[] = $descriptionId;
	}
	
	foreach($bookingData['Rooms'] as $roomData) {
		$arriveDate = $roomData['StartDate'];
		$arriveDateTs = strtotime($arriveDate);
		$departureDate = $roomData['EndDate'];
		$departureDateTs = strtotime($departureDate);
		$nights = round(($departureDateTs-$arriveDateTs) / (60*60*24));
		$lastNightTs = strtotime($departureDate . " -1 day");
		$lastNight = date('Y-m-d', $lastNightTs);

		$specialOffers = loadSpecialOffers("start_date<='$arriveDate' AND end_date>='$lastNight'", $link);
		$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);

		$numOfPersonForRoomType = array();
		foreach($roomData['RoomTypeIds'] as $myAllocRoomTypeId) {
			$roomTypeId = findRoomTypeId($myAllocRoomTypeId);
			if(is_null($roomTypeId)) {
				respond('52', false, "Cannot find roomTypeId for myallocator rom id: $myAllocRoomTypeId");
				return false;
			}
			$numOfPerson = $roomData['Units'];
			if($roomTypesData[$roomTypeId]['type'] == 'PRIVATE') {
				$numOfPerson = $numOfPerson * $roomTypesData[$roomTypeId]['num_of_beds'];
			}
			$numOfPersonForRoomType[$roomTypeId] = $numOfPerson;
		}

		if(!$allSameDate) {
			$sql = "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time, language, currency, my_allocator_id) VALUES ('$firstname $lastname', NULL, '$address', '$nationality', '$email', '$phone', '" . str_replace("-", "/", $arriveDate) . "', '" . str_replace("-", "/", $lastNight) . "', $nights, 0, 0, 0, 0, '$comment', '$source', '', '$lang', '$currency', '$myAlocatorId')";
			set_debug($sql);
			if(!mysql_query($sql, $link)) {
				respond('51', false, 'Cannot create booking description: ' . mysql_error($link) . " (SQL: $sql)");
				return false;
			}
			$descriptionId = mysql_insert_id($link);
			$bookingDescriptionIds[] = $descriptionId;
		}

		list($toBook, $roomChanges) = getBookingData($numOfPersonForRoomType, $arriveDate, $lastNight, $rooms, $roomTypesData);

		$bookingIds = saveBookings($toBook, $roomChanges, $arriveDate, $lastNight, $rooms, $roomTypesData, $specialOffers, $descriptionId, $link);
	
		$_SERVER['PHP_AUTH_USER'] = 'myallocator';
		audit(AUDIT_CREATE_BOOKING, array('source' => 'myallocator', 'booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $descriptionId, $link);

	}
	respond(null, true);
	$locationName = constant('LOCATION_NAME_' . strtoupper($location));
	$message = '';
	foreach($bookingDescriptionIds as $descriptionId) {
		$message .= "<a href=\"http://" . $_SERVER['HTTP_HOST'] . "/edit_booking.php?description_id=$descriptionId\">View booking</a><br>\n";
	}

	sendMail(CONTACT_EMAIL, $locationName, CONTACT_EMAIL, $locationName, 'Booking arrived from myallocator', $message);

	return true;
}







function respond($code, $success, $errorMessage = null) {
	global $MESSAGES;
	$successStr = $success ? 'true' : 'false';
	echo "{\n";
	if(!is_null($code)) {
		$msg = $MESSAGES[$code];
		echo <<<EOT
	"error": {
		"code": $code,
		"msg" : "$msg"
	},

EOT;
	}
	echo "\t\"success\" : $successStr\n}\n";

	if(!is_null($errorMessage)) {
		sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', 'Error with booking from myallocator', $errorMessage . "\n\nRequest:\n" . print_r($_REQUEST, true));
	}
}


function findRoomTypeId($remoteRoomId) {
	global $ROOM_MAP;

	foreach($ROOM_MAP[LOCATION] as $roomTypeInfo) {
		if($roomTypeInfo['remoteRoomId'] == $remoteRoomId) {
			return $roomTypeInfo['roomTypeId'];
		}
	}

	return null;
}


?>
