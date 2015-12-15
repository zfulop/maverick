<?php

define('PROPERTY_ID_HOSTEL','1650');
define('PROPERTY_ID_LODGE','1748');
define('PROPERTY_ID_APARTMENT','5637');

define('HASHED_PASSWORD', '$1$rM3.YS0.$.BMdC5Qd31wO6VUArIhb21');

require('includes.php');
require('includes/country_alias.php');
require('room_booking.php');

$SOURCES = array(
	'hw2' => 'hostelworld',
	'hb2' => 'hostelbookers',
	'boo' => 'booking.com',
	'ago' => 'Agoda',
	'hrs' => 'HRS',
	'bnw' => 'BookNow - Tripadvisor',
	'hi' => 'HiHostels',
	'hbe' => 'Hotel Beds',
	'hcu' => 'Hostel Culture',
	'rep' => 'Travel Public',
	'exp' => array('Hotel Collect Booking' => 'Expedia - Hotel Collect', 'Expedia Collect Booking' => 'Expedia - Expedia Collect')
);

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
			),
		array(
			'roomName' => 'HW 4 bedded extra private ensuite',
			'roomTypeId' => 60,
			'remoteRoomId' => '10032'
			),
		array(
			'roomName' => 'single private ensuite',
			'roomTypeId' => 65,
			'remoteRoomId' => '24369'
			),
		array(
			'roomName' => '5 bed private ensuite mixed dorm',
			'roomTypeId' => 64,
			'remoteRoomId' => '9431'
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
			'roomTypeId' => 70,
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
		),
	'apartment' => array(
		array(
			'roomName' => 'Studio Apartment',
			'roomTypeId' => 70,
			'remoteRoomId' => '29812'
			),
		array(
			'roomName' => 'Deluxe Studio Apartment',
			'roomTypeId' => array(69,71),
			'remoteRoomId' => '29813'
			),
		array(
			'roomName' => 'One-bedroom apartment, Ferenciek',
			'roomTypeId' => array(68,72),
			'remoteRoomId' => '29814'
			),
		array(
			'roomName' => 'One-bedroom apartment, Belgrád',
			'roomTypeId' => 67,
			'remoteRoomId' => '29815'
			),
		array(
			'roomName' => 'Two-bedroom apartment, Deák',
			'roomTypeId' => 66,
			'remoteRoomId' => '29816'
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
	'56' => 'Cannot create booking because there is already a booking with the same myallocator id',
	'57' => 'invalid property id'
);

$lang = 'eng';
$location = LOCATION;

require(LANG_DIR . $lang . '.php');

$locationName = constant('LOCATION_NAME_' . strtoupper($location));

$bookingJson = $_REQUEST['booking'];
$pwd = $_REQUEST['password'];

logMessage($bookingJson);

if(crypt($pwd, HASHED_PASSWORD) !=  HASHED_PASSWORD) {
	respond('10', false, "Incorrect password");
	return;
}

$bookingJson = stripslashes($bookingJson);
set_debug($bookingJson);
$bookingData = json_decode($bookingJson, true);
set_debug(print_r($bookingData,true));
// sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', "Booking request in " . LOCATION, 'booking data: ' . print_r($bookingData,true) . "\n\nRaw data: \n" . stripslashes($_REQUEST['booking']));

if(!isset($bookingData['PropertyId'])) {
	$matches = array();
	preg_match('/"PropertyId":([^,]*),/', $bookingJson, $matches);
	$propertyId = $matches[1];
} else {
	$propertyId = $bookingData['PropertyId'];
}


if((($propertyId == PROPERTY_ID_HOSTEL) or ($propertyId == PROPERTY_ID_APARTMENT)) and $_SERVER['HTTP_HOST'] != 'recepcio.maverickhostel.com') {
	// sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', "Forward Booking to " . LOCATION, 'booking data: ' . print_r($bookingData,true) . "\n\nRaw data: \n" . stripslashes($_REQUEST['booking']));
	$url = 'http://recepcio.maverickhostel.com/myallocator_booking.php';

	$fields_string = '';
	//url-ify the data for the POST
	foreach($_REQUEST as $key=>$value) {
		$fields_string .= $key.'='.urlencode(stripslashes($value)).'&';
	}
	rtrim($fields_string,'&');

	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,count($_REQUEST));
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

	//execute post
	$result = curl_exec($ch);
	header("Content-type: application/json; charset=utf-8");
	if(substr($result, -1, 1) == '1') {
		$result = substr($result, 0, -1);
	}
	echo $result;
	return;
}

$link = db_connect();

mysql_query("START TRANSACTION", $link);

$result = null;
if((strpos($bookingJson, "\"IsCancellation\":true") > 0) or (isset($bookingData['IsCancellation']) and $bookingData['IsCancellation'])) {
	$matches = array();
	preg_match('/"MyallocatorId":"([^"]*)"/', $bookingJson, $matches);
	$myallocId = $matches[1];
	// sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', "Booking cancellation [$myallocId] for " . LOCATION, stripslashes($_REQUEST['booking']));
	$result = cancelBooking($myallocId, $link);
} else {
	// sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', "Booking creation " . LOCATION, 'booking data: ' . print_r($bookingData,true) . "\n\nRaw data: \n" . stripslashes($_REQUEST['booking']));
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
	global $lang, $locationName;
	$sql = "SELECT * FROM booking_descriptions WHERE my_allocator_id='$myAllocatorId'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		respond('51', false, "Cannot retieve booking in admin interface when canceling it: " . mysql_error($link) . " (SQL: $sql)");
		return false;
	}
	$row = mysql_fetch_assoc($result);
	$descrId = $row['id'];

	if(intval($descrId) > 0) {
		$sql = "UPDATE booking_descriptions SET cancelled=1,cancel_type='guest' WHERE id=$descrId";
		$result = mysql_query($sql, $link);
		if(!$result) {
			respond('51', false, "Cannot cancel booking in admin interface: " . mysql_error($link) . " (SQL: $sql)");
			return false;
		}
	}

	respond(null, true);

	audit(AUDIT_CANCEL_BOOKING, $_REQUEST, 0, $descrId, $link, 'myallocator');
	return true;
}



function createBooking($bookingData, $link) {
	global $lang, $bookingJson, $locationName, $SOURCES, $MESSAGES, $COUNTRY_ALIASES, $propertyId;
	$nowTime = date('Y-m-d H:i:s');
	if(!isset($bookingData['Rooms']) or !is_array($bookingData['Rooms']) or count($bookingData['Rooms']) < 1) {
		set_debug("booking data:");
		set_debug(print_r($bookingData, true));
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
		respond('51', false, "Cannot retrieve booking in admin interface when creating it: " . mysql_error($link) . " (SQL: $sql)");
		return false;
	}
	$descrId = null;
	if(mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$descrId = $row['id'];
		$sql = "DELETE FROM booking_guest_data WHERE booking_description_id=$descrId";
		if(!mysql_query($sql, $link)) {
			logMessage("Cannot delete booking guest data when modifying booking: " . mysql_error($link) . " (SQL: $sql)");
		}
		$sql = "DELETE FROM booking_room_changes WHERE booking_id in (select id from bookings where description_id=$descrId)";
		if(!mysql_query($sql, $link)) {
			logMessage("Cannot delete booking room changes when modifying booking: " . mysql_error($link) . " (SQL: $sql)");
		}
		$sql = "DELETE FROM bookings WHERE description_id=$descrId";
		if(!mysql_query($sql, $link)) {
			logMessage("Cannot delete bookings when modifying booking: " . mysql_error($link) . " (SQL: $sql)");
		}
		$sql = "DELETE FROM booking_descriptions WHERE id=$descrId";
		if(!mysql_query($sql, $link)) {
			logMessage("Cannot delete booking description when modifying booking: " . mysql_error($link) . " (SQL: $sql)");
		}

		$sql = "DELETE FROM payments WHERE booking_description_id=$descrId AND comment='*booking deposit*'";
		if(!mysql_query($sql, $link)) {
			logMessage("Cannot delete booking description when modifying booking: " . mysql_error($link) . " (SQL: $sql)");
		}

	}


	$currency = $bookingData['TotalCurrency'];
	$customer = $bookingData['Customers'][0];
	$firstname = mysql_escape_string($customer['CustomerFName']);
	$lastname = mysql_escape_string($customer['CustomerLName']);
	$email = $customer['CustomerEmail'];
	$phone = '';
	$nationality = isset($customer['CustomerCountry']) ? $customer['CustomerCountry'] : '';
	if($nationality == '') {
		$nationality = isset($customer['CustomerNationality']) ? $customer['CustomerNationality'] : '';
	}
	if(isset($COUNTRY_ALIASES[$nationality])) {
		$nationality = $COUNTRY_ALIASES[$nationality];
	}
	$city = isset($customer['CustomerCity']) ? mysql_escape_string($customer['CustomerCity']) : '';
	$country = isset($customer['CustomerCountry']) ? mysql_escape_string($customer['CustomerCountry']) : '';
	$address = $city . ', ' . $country;
	$comment = mysql_escape_string(print_r($bookingData, true));

	$source = (isset($bookingData['Channel']) and isset($SOURCES[$bookingData['Channel']])) ? $SOURCES[$bookingData['Channel']] : '';
	if(is_array($source) and ($bookingData['Channel'] == 'exp')) {
		$source = $source[$bookingData['ChannelSpecific']['PaymentType']];
	}
	$roomTypesData = loadRoomTypes($link, $lang);
	set_debug("room types: <pre>" . print_r($roomTypesData, true) . "</pre>\n");

	$bookingDescriptionIds = array();

	set_debug("before combine: <pre>" . print_r($bookingData['Rooms'], true) . "</pre>\n");

	
	if(canCombineRooms($bookingData['Rooms'])) {
		set_debug("combining rooms");
		$bookingData['Rooms'] = combineRooms($bookingData['Rooms']);
	}

	set_debug("after combine: <pre>" . print_r($bookingData['Rooms'], true) . "</pre>\n");

	$allSameDate = isAllSameDate($bookingData['Rooms']);
	set_debug("allSaveDate=" . $allSameDate);
	if($allSameDate) {
		set_debug("all same date");
		$arriveDate = $bookingData['Rooms'][0]['StartDate'];
		$lastNight = $bookingData['Rooms'][0]['EndDate'];
		$arriveDateTs = strtotime($arriveDate);
		$lastNightTs = strtotime($lastNight);
		$cutoff = strtotime(date('Y-m-d') . " +1 day +3 hours");
		if($cutoff < time()) {
			respond('54', false, "Cannot create booking: arrive date ($arriveDate) is in the past");
			return false;
		}
		if($arriveDate > $lastNight) {
			respond('55', false, "Cannot create booking: arrive date ($arriveDate) must be before (or the same as) last night ($lastNight)");
			return false;
		}

		$nights = round(($lastNightTs-$arriveDateTs) / (60*60*24)) + 1;
		$lastNightTs = strtotime($lastNight);

		verifyBlacklist("$firstname $lastname", $email, CONTACT_EMAIL, $link);

		$sql = "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time, language, currency, my_allocator_id, create_time) VALUES ('$firstname $lastname', NULL, '$address', '$nationality', '$email', '$phone', '" . str_replace("-", "/", $arriveDate) . "', '" . str_replace("-", "/", $lastNight) . "', $nights, 0, 0, 0, 0, '$comment', '$source', '', '$lang', '$currency', '$myAllocatorId', '$nowTime')";
		set_debug($sql);
		if(!mysql_query($sql, $link)) {
			respond('51', false, 'Cannot create booking description: ' . mysql_error($link) . " (SQL: $sql)");
			return;
		}
		$descriptionId = mysql_insert_id($link);
		$bookingDescriptionIds[] = $descriptionId;
	}

	// Save the parking as a service change
	$serviceChargeAmt = 0;
	if(isset($bookingData['ExtraServices'])) {
		foreach($bookingData['ExtraServices'] as $oneService) {
			if(substr($oneService['Description'],0,6) == 'Parkol' and isset($oneService['Price'])) {
				$amount = doubleval($oneService['Price']);
				if(isset($bookingData['TotalCurrency'])) {
					$curr = $bookingData['TotalCurrency'];
				} else {
					$curr = 'EUR';
				}
				$bdid = $bookingDescriptionIds[0];
				$nowTime = date('Y-m-d H:i:s');
				set_debug("Saving extra service as service_charge");
				$sql = "INSERT INTO service_charges (booking_description_id, amount, currency, time_of_service, comment, type) VALUES ($bdid, $amount, '$curr', '$nowTime', '" . $oneService['Description'] . "', 'Parkolás')";
				$result = mysql_query($sql, $link);
				if(!$result) {
					set_debug("Cannot save service charge: " . mysql_error($link) . " (SQL: $sql)");
				} else {
					$serviceChargeAmt += $amount;
					set_debug("Save successful. SQL: $sql");
				}
			}
		}
	}

	foreach($bookingData['Rooms'] as $roomData) {
		$arriveDate = $roomData['StartDate'];
		$arriveDateTs = strtotime($arriveDate);
		$lastNight = $roomData['EndDate'];
		$lastNightTs = strtotime($lastNight);
		$nights = round(($lastNightTs-$arriveDateTs) / (60*60*24)) + 1;

		$specialOffers = loadSpecialOffers("start_date<='$arriveDate' AND end_date>='$lastNight'", $link);
		$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);

		$numOfPersonForRoomType = array();
		$priceForRoomType = array();
		foreach($roomData['RoomTypeIds'] as $myAllocRoomTypeId) {
			$roomTypeId = findRoomTypeId($myAllocRoomTypeId, $propertyId);
			if(is_null($roomTypeId)) {
				set_debug("Cannot find roomTypeId for myallocator room id: $myAllocRoomTypeId");
				respond('52', false, "Cannot find roomTypeId for myallocator room id: $myAllocRoomTypeId, property id: $propertyId");
				return false;
			}
			$numOfPerson = $roomData['Units'];
			if(isPrivate($roomTypesData[$roomTypeId])) {
				$numOfPerson = $numOfPerson * $roomTypesData[$roomTypeId]['num_of_beds'];
			}
			if(is_array($roomTypeId)) {
				$goodId = null;
				foreach($roomTypeId as $oneId) {
					if(count(getOverbookings(array($oneId => $numOfPerson), $arriveDate, $lastNight, $rooms)) < 1) {
						$goodId = $oneId;
						break;
					}
				}
				if(is_null($goodId)) {
					$roomTypeId = $roomTypeId[0];
				} else {
					$roomTypeId = $goodId;
				}
			}
			$numOfPersonForRoomType[$roomTypeId] = $numOfPerson;
			$price = $roomData['Price'];
//			if(isset($bookingData['TotalTaxes'])) {
//				$price = $price + $bookingData['TotalTaxes'] / count($bookingData['Rooms']);
//			}
			if(isPrivate($roomTypesData[$roomTypeId])) {
				$priceForRoomType[$roomTypeId] = $price / floatval($roomData['Units']);
			} else {
				$priceForRoomType[$roomTypeId] = $price;
			}
		}

		if(!$allSameDate) {
			$sql = "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time, language, currency, my_allocator_id, create_time) VALUES ('$firstname $lastname', NULL, '$address', '$nationality', '$email', '$phone', '" . str_replace("-", "/", $arriveDate) . "', '" . str_replace("-", "/", $lastNight) . "', $nights, 0, 0, 0, 0, '$comment', '$source', '', '$lang', '$currency', '$myAllocatorId', '$nowTime')";
			set_debug($sql);
			if(!mysql_query($sql, $link)) {
				respond('51', false, 'Cannot create booking description: ' . mysql_error($link) . " (SQL: $sql)");
				return false;
			}
			$descriptionId = mysql_insert_id($link);
			$bookingDescriptionIds[] = $descriptionId;
		}

		if(!is_null($descrId)) {
			$newDescrId = $bookingDescriptionIds[0];
			$sql = "UPDATE payments SET booking_description_id=$newDescrId WHERE booking_description_id=$descrId";
			if(!mysql_query($sql, $link)) {
				respond('51', false, 'Cannot move existing payments from old to new booking: ' . mysql_error($link) . " (SQL: $sql)");
				return;
			}
		}


		// echo "Num of person for room type: <pre>" . print_r($numOfPersonForRoomType, true) . "</pre>\n";

		list($toBook, $roomChanges) = getBookingData($numOfPersonForRoomType, $arriveDate, $lastNight, $rooms, $roomTypesData);

		// echo "toBook: <pre>" . print_r($toBook, true) . "</pre>\n";
		// echo "roomChanges: <pre>" . print_r($roomChanges, true) . "</pre>\n";

		$bookingIds = saveBookings($toBook, $roomChanges, $arriveDate, $lastNight, $rooms, $roomTypesData, $specialOffers, $descriptionId, $link, $priceForRoomType);
	
		$_SERVER['PHP_AUTH_USER'] = 'myallocator';
		audit(AUDIT_CREATE_BOOKING, array('source' => 'myallocator', 'booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $descriptionId, $link);

	}

	if(isset($bookingData['Deposit'])) {
		$amount = doubleval($bookingData['Deposit']);
		if(isset($bookingData['DepositCurrency'])) {
			$curr = $bookingData['DepositCurrency'];
		} else {
			$curr = 'EUR';
		}
		$bdid = $bookingDescriptionIds[0];
		$nowTime = date('Y-m-d H:i:s');
		set_debug("Setting deposit to $amount $curr");
		$sql = "INSERT INTO payments (booking_description_id, amount, currency, time_of_payment, comment, cash, storno, type, pay_mode) VALUES ($bdid, $amount, '$curr', '$nowTime', '*booking deposit*', 1, 0, NULL, 'CASH')";
		$result = mysql_query($sql, $link);
		if(!$result) {
			set_debug("Cannot save deposit payment: " . mysql_error($link) . " (SQL: $sql)");
		} else {
			set_debug("Save successful. SQL: $sql");
		}
	}


	respond(null, true);
	$message = '';
	foreach($bookingDescriptionIds as $descriptionId) {
		$firstName = $bookingData['Customers'][0]['CustomerFName'];
		$lastName = $bookingData['Customers'][0]['CustomerLName'];

		//$message .= "<a href=\"http://" . $_SERVER['HTTP_HOST'] . "/edit_booking.php?description_id=$descriptionId\">View booking</a><br>\n";
		$message .= "<a href=\"http://recepcio.roomcaptain.com/edit_booking.php?description_id=$descriptionId\">View booking</a><br>\n";
		$message .= "$firstName $lastName - $email<br>\n";
		$message .= "<pre>$comment</pre><br><br>\n";
	}

	sendMail(CONTACT_EMAIL, $locationName, CONTACT_EMAIL, $locationName, 'Booking arrived from myallocator', $message);

	return true;
}






function respond($code, $success, $errorMessage = null) {
	global $MESSAGES, $locationName;
	$retVal = array();
	$retVal['success'] = $success;

	$successStr = $success ? 'true' : 'false';
	if(!is_null($code)) {
		$msg = $MESSAGES[$code];
		$retVal['error'] = array('code' => $code, 'msg' => $msg, 'comment' => $errorMessage);
	}
	
	logMessage("Response: code=$code, success=$success, errorMessage=$errorMessage");
	$retVal = array('success' => true);
	logMessage("Always send back success to myallocator to stop resending the message.");
	logMessage("Response (as it is sent back): " . json_encode($retVal) . "\n\n");
	
	
	header("Content-type: application/json; charset=utf-8");
	echo json_encode($retVal);

	if(!is_null($errorMessage)) {
		sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', 'Error with booking from myallocator to ' . LOCATION, $errorMessage . "\n\nRequest:\n" . stripslashes($_REQUEST['booking']));
	}
}


function findRoomTypeId($remoteRoomId, $propertyId) {
	global $ROOM_MAP;

	$location = LOCATION;
	if($propertyId == PROPERTY_ID_APARTMENT) {
		$location = 'apartment';
	}
	foreach($ROOM_MAP[$location] as $roomTypeInfo) {
		if($roomTypeInfo['remoteRoomId'] == $remoteRoomId) {
			return $roomTypeInfo['roomTypeId'];
		}
	}

	return null;
}

function isAllSameDate(&$rooms) {
	$allSameDate = true;
	$arriveDate = null;
	$lastNight = null;
	foreach($rooms as $roomData) {
		if(is_null($arriveDate)) {
			$arriveDate = $roomData['StartDate'];
			$lastNight = $roomData['EndDate'];
		} elseif(($arriveDate != $roomData['StartDate']) or ($lastNight != $roomData['EndDate'])) {
			set_debug("Not all same: $arriveDate!=" . $roomData['StartDate'] . ", result: " . ($arriveDate != $roomData['StartDate']) . " and $lastNight!=" . $roomData['EndDate'] . ", result: " . ($lastNight != $roomData['EndDate']));
			$allSameDate = false;
			break;
		}
	}
	return $allSameDate;
}


function canCombineRooms(&$rooms) {
	if(count($rooms) == 1) {
		return false;
	}
	$roomDates = array();
	foreach($rooms as $roomData) {
		$arriveDate = $roomData['StartDate'];
		$lastNight = $roomData['EndDate'];
		$rtId = $roomData['RoomTypeIds'][0];
		if(!isset($roomDates[$rtId])) {
			$roomDates[$rtId] = array();
		}
		if(!containsDates($roomDates[$rtId], $arriveDate, $lastNight)) {
			$roomDates[$rtId][] = array($arriveDate, $lastNight);
		}
	}
	set_debug("room dates: " . print_r($roomDates, true));
	foreach($roomDates as $rtId => $dates) {
		usort($dates, 'sortDates');
		$currDate = null;
		foreach($dates as $startEndDate) {
			if(!is_null($currDate)) {
				if(date('Y-m-d', strtotime($currDate . " +1 day")) != $startEndDate[0]) {
					return false;
				}
			}
			$currDate = $startEndDate[1];
		}
	}
	return true;
}

function containsDates($roomDatesForOneRoom, $arriveDate, $lastNight) {
	foreach($roomDatesForOneRoom as $startEndDate) {
		if(($arriveDate == $startEndDate[0]) and ($lastNight == $startEndDate[1])) {		
			return true;
		}
	}
	return false;
}


function sortDates($d1, $d2) {
	if($d1[0] < $d2[0]) {
		return -1;
	} elseif($d1[0] > $d2[0]) {
		return 1;
	}
	return 0;
}

function combineRooms($rooms) {
	$roomDates = array();
	foreach($rooms as $roomData) {
		$arriveDate = $roomData['StartDate'];
		$lastNight = $roomData['EndDate'];
		$prc = $roomData['Price'];
		$unit = $roomData['Units'];
		$rtId = $roomData['RoomTypeIds'][0];
		if(!isset($roomDates[$rtId])) {
			$roomDates[$rtId] = array($arriveDate, $lastNight, $prc, $unit);
		} else {
			if($roomDates[$rtId][1] == $lastNight) {
				$roomDates[$rtId][3] += $unit;
			}
			$roomDates[$rtId][1] = $lastNight;
			$roomDates[$rtId][2] += $prc;
		}
	}
	$newRooms = array();
	$roomTypeIdsIncluded = array();
	foreach($rooms as $roomData) {
		$rtId = $roomData['RoomTypeIds'][0];
		if(in_array($rtId, $roomTypeIdsIncluded)) {
			continue;
		}
		$roomData['StartDate'] = $roomDates[$rtId][0];
		$roomData['EndDate'] = $roomDates[$rtId][1];
		$roomData['Price'] = $roomDates[$rtId][2];
		$roomData['Units'] = $roomDates[$rtId][3];
		$roomTypeIdsIncluded[] = $rtId;
		$newRooms[] = $roomData;
	}
	return $newRooms;
}


function logMessage($message) {
	$fh = fopen("myallocator." . date('Ymd') . ".log", "a");
	if($fh) {
		fwrite($fh, date('Y-m-d H:i:s') . "\n");
		fwrite($fh, $message . "\n");
		fclose($fh);
	}
}


?>
