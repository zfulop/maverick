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
	'ta' => 'Trip Advisor',
	'fam' => 'Famous Hostels',
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
	$firstname = mysql_real_escape_string(decode($customer['CustomerFName']), $link);
	$lastname = mysql_real_escape_string(decode($customer['CustomerLName']), $link);
	$email = $customer['CustomerEmail'];
	$phone = '';
	$nationality = isset($customer['CustomerCountry']) ? $customer['CustomerCountry'] : '';
	if($nationality == '') {
		$nationality = isset($customer['CustomerNationality']) ? $customer['CustomerNationality'] : '';
	}
	if(isset($COUNTRY_ALIASES[$nationality])) {
		$nationality = $COUNTRY_ALIASES[$nationality];
	}
	$city = isset($customer['CustomerCity']) ? mysql_real_escape_string($customer['CustomerCity'], $link) : '';
	$country = isset($customer['CustomerCountry']) ? mysql_real_escape_string($customer['CustomerCountry'], $link) : '';
	$address = $city . ', ' . $country;
	$comment = mysql_real_escape_string(print_r($bookingData, true), $link);

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
	$bdid = $bookingDescriptionIds[0];
	$nowTime = date('Y-m-d H:i:s');
	if(isset($bookingData['ExtraServices'])) {
		foreach($bookingData['ExtraServices'] as $oneService) {
			if((substr($oneService['Description'],0,6) == 'Parkol' or substr($oneService['Description'],0,7) == 'Reggeli') and isset($oneService['Price'])) {
				$amount = doubleval($oneService['Price']);
				if(isset($bookingData['TotalCurrency'])) {
					$curr = $bookingData['TotalCurrency'];
				} else {
					$curr = 'EUR';
				}
				$descr = mysql_real_escape_string(decode($oneService['Description']), $link);
				$svcText = 'Parkolás';
				if(substr($oneService['Description'],0,7) == 'Reggeli') {
					$svcText = 'Reggeli - FatMama';
				}
				set_debug("Saving extra service as service_charge");
				$sql = "INSERT INTO service_charges (booking_description_id, amount, currency, time_of_service, comment, type) VALUES ($bdid, $amount, '$curr', '$nowTime', '$descr', '$svcText')";
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
	
	// Save IFA as a service charge
	$roomCharge = $bookingData['TotalPrice'] - $serviceChargeAmt;
	$ifa = $roomCharge * 0.034;
	set_debug("Saving IFA as service_charge");
	$sql = "INSERT INTO service_charges (booking_description_id, amount, currency, time_of_service, comment, type) VALUES ($bdid, $amount, 'EUR', '$nowTime', 'IFA', 'IFA')";
	$result = mysql_query($sql, $link);
	if(!$result) {
		set_debug("Cannot save service charge: " . mysql_error($link) . " (SQL: $sql)");
	} else {
		set_debug("Save successful. SQL: $sql");
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
		$numOfBookings = 0;
		foreach($roomData['RoomTypeIds'] as $myAllocRoomTypeId) {
			$roomTypeId = findRoomTypeId($myAllocRoomTypeId, $propertyId);
			if(is_null($roomTypeId)) {
				set_debug("Cannot find roomTypeId for myallocator room id: $myAllocRoomTypeId");
				respond('52', false, "Cannot find roomTypeId for myallocator room id: $myAllocRoomTypeId, property id: $propertyId");
				return false;
			}
			$numOfPerson = $roomData['Units'];
			$numOfBookings += $roomData['Units'];
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

		foreach($priceForRoomType as $roomTypeId => $price) {
			if(isPrivate($roomTypesData[$roomTypeId])) {
				$numOfBookings += $numOfPersonForRoomType[$roomTypeId] / $roomTypesData[$roomTypeId]['num_of_beds'];
			} elseif(isDorm($roomTypesData[$roomTypeId])) {
				$numOfBookings += $roomTypesData[$roomTypeId]['num_of_beds'];
			}
		
		}

		$scPerBooking = $serviceChargeAmt / count($bookingData['Rooms']) / $numOfBookings;
		foreach($priceForRoomType as $roomTypeId => $price) {
			$priceForRoomType[$roomTypeId] = $price - $scPerBooking;
		}


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
		$firstName = decode($bookingData['Customers'][0]['CustomerFName']);
		$lastName = decode($bookingData['Customers'][0]['CustomerLName']);

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

function decode($str) {
$str = str_replace('u00c0', 'À', $str);
$str = str_replace('u00c1', 'Á', $str);
$str = str_replace('u00c2', 'Â', $str);
$str = str_replace('u00c3', 'Ã', $str);
$str = str_replace('u00c4', 'Ä', $str);
$str = str_replace('u00c5', 'Å', $str);
$str = str_replace('u00c6', 'Æ', $str);
$str = str_replace('u00c7', 'Ç', $str);
$str = str_replace('u00c8', 'È', $str);
$str = str_replace('u00c9', 'É', $str);
$str = str_replace('u00ca', 'Ê', $str);
$str = str_replace('u00cb', 'Ë', $str);
$str = str_replace('u00cc', 'Ì', $str);
$str = str_replace('u00cd', 'Í', $str);
$str = str_replace('u00ce', 'Î', $str);
$str = str_replace('u00cf', 'Ï', $str);
$str = str_replace('u00d0', 'Ð', $str);
$str = str_replace('u00d1', 'Ñ', $str);
$str = str_replace('u00d2', 'Ò', $str);
$str = str_replace('u00d3', 'Ó', $str);
$str = str_replace('u00d4', 'Ô', $str);
$str = str_replace('u00d5', 'Õ', $str);
$str = str_replace('u00d6', 'Ö', $str);
$str = str_replace('u00d7', '×', $str);
$str = str_replace('u00d8', 'Ø', $str);
$str = str_replace('u00d9', 'Ù', $str);
$str = str_replace('u00da', 'Ú', $str);
$str = str_replace('u00db', 'Û', $str);
$str = str_replace('u00dc', 'Ü', $str);
$str = str_replace('u00dd', 'Ý', $str);
$str = str_replace('u00de', 'Þ', $str);
$str = str_replace('u00df', 'ß', $str);
$str = str_replace('u00e0', 'à', $str);
$str = str_replace('u00e1', 'á', $str);
$str = str_replace('u00e2', 'â', $str);
$str = str_replace('u00e3', 'ã', $str);
$str = str_replace('u00e4', 'ä', $str);
$str = str_replace('u00e5', 'å', $str);
$str = str_replace('u00e6', 'æ', $str);
$str = str_replace('u00e7', 'ç', $str);
$str = str_replace('u00e8', 'è', $str);
$str = str_replace('u00e9', 'é', $str);
$str = str_replace('u00ea', 'ê', $str);
$str = str_replace('u00eb', 'ë', $str);
$str = str_replace('u00ec', 'ì', $str);
$str = str_replace('u00ed', 'í', $str);
$str = str_replace('u00ee', 'î', $str);
$str = str_replace('u00ef', 'ï', $str);
$str = str_replace('u00f0', 'ð', $str);
$str = str_replace('u00f1', 'ñ', $str);
$str = str_replace('u00f2', 'ò', $str);
$str = str_replace('u00f3', 'ó', $str);
$str = str_replace('u00f4', 'ô', $str);
$str = str_replace('u00f5', 'õ', $str);
$str = str_replace('u00f6', 'ö', $str);
$str = str_replace('u00f7', '÷', $str);
$str = str_replace('u00f8', 'ø', $str);
$str = str_replace('u00f9', 'ù', $str);
$str = str_replace('u00fa', 'ú', $str);
$str = str_replace('u00fb', 'û', $str);
$str = str_replace('u00fc', 'ü', $str);
$str = str_replace('u00fd', 'ý', $str);
$str = str_replace('u00fe', 'þ', $str);
$str = str_replace('u00ff', 'ÿ', $str);
$str = str_replace("u0100", 'Ā', $str);
$str = str_replace("u0101", 'ā', $str);
$str = str_replace("u0102", 'Ă', $str);
$str = str_replace("u0103", 'ă', $str);
$str = str_replace("u0104", 'Ą', $str);
$str = str_replace("u0105", 'ą', $str);
$str = str_replace("u0106", 'Ć', $str);
$str = str_replace("u0107", 'ć', $str);
$str = str_replace("u0108", 'Ĉ', $str);
$str = str_replace("u0109", 'ĉ', $str);
$str = str_replace("u010a", 'Ċ', $str);
$str = str_replace("u010b", 'ċ', $str);
$str = str_replace("u010c", 'Č', $str);
$str = str_replace("u010d", 'č', $str);
$str = str_replace("u010e", 'Ď', $str);
$str = str_replace("u010f", 'ď', $str);
$str = str_replace("u0110", 'Đ', $str);
$str = str_replace("u0111", 'đ', $str);
$str = str_replace("u0112", 'Ē', $str);
$str = str_replace("u0113", 'ē', $str);
$str = str_replace("u0114", 'Ĕ', $str);
$str = str_replace("u0115", 'ĕ', $str);
$str = str_replace("u0116", 'Ė', $str);
$str = str_replace("u0117", 'ė', $str);
$str = str_replace("u0118", 'Ę', $str);
$str = str_replace("u0119", 'ę', $str);
$str = str_replace("u011a", 'Ě', $str);
$str = str_replace("u011b", 'ě', $str);
$str = str_replace("u011c", 'Ĝ', $str);
$str = str_replace("u011d", 'ĝ', $str);
$str = str_replace("u011e", 'Ğ', $str);
$str = str_replace("u011f", 'ğ', $str);
$str = str_replace("u0120", 'Ġ', $str);
$str = str_replace("u0121", 'ġ', $str);
$str = str_replace("u0122", 'Ģ', $str);
$str = str_replace("u0123", 'ģ', $str);
$str = str_replace("u0124", 'Ĥ', $str);
$str = str_replace("u0125", 'ĥ', $str);
$str = str_replace("u0126", 'Ħ', $str);
$str = str_replace("u0127", 'ħ', $str);
$str = str_replace("u0128", 'Ĩ', $str);
$str = str_replace("u0129", 'ĩ', $str);
$str = str_replace("u012a", 'Ī', $str);
$str = str_replace("u012b", 'ī', $str);
$str = str_replace("u012c", 'Ĭ', $str);
$str = str_replace("u012d", 'ĭ', $str);
$str = str_replace("u012e", 'Į', $str);
$str = str_replace("u012f", 'į', $str);
$str = str_replace("u0130", 'İ', $str);
$str = str_replace("u0131", 'ı', $str);
$str = str_replace("u0132", 'Ĳ', $str);
$str = str_replace("u0133", 'ĳ', $str);
$str = str_replace("u0134", 'Ĵ', $str);
$str = str_replace("u0135", 'ĵ', $str);
$str = str_replace("u0136", 'Ķ', $str);
$str = str_replace("u0137", 'ķ', $str);
$str = str_replace("u0138", 'ĸ', $str);
$str = str_replace("u0139", 'Ĺ', $str);
$str = str_replace("u013a", 'ĺ', $str);
$str = str_replace("u013b", 'Ļ', $str);
$str = str_replace("u013c", 'ļ', $str);
$str = str_replace("u013d", 'Ľ', $str);
$str = str_replace("u013e", 'ľ', $str);
$str = str_replace("u013f", 'Ŀ', $str);
$str = str_replace("u0140", 'ŀ', $str);
$str = str_replace("u0141", 'Ł', $str);
$str = str_replace("u0142", 'ł', $str);
$str = str_replace("u0143", 'Ń', $str);
$str = str_replace("u0144", 'ń', $str);
$str = str_replace("u0145", 'Ņ', $str);
$str = str_replace("u0146", 'ņ', $str);
$str = str_replace("u0147", 'Ň', $str);
$str = str_replace("u0148", 'ň', $str);
$str = str_replace("u0149", 'ŉ', $str);
$str = str_replace("u014a", 'Ŋ', $str);
$str = str_replace("u014b", 'ŋ', $str);
$str = str_replace("u014c", 'Ō', $str);
$str = str_replace("u014d", 'ō', $str);
$str = str_replace("u014e", 'Ŏ', $str);
$str = str_replace("u014f", 'ŏ', $str);
$str = str_replace("u0150", 'Ő', $str);
$str = str_replace("u0151", 'ő', $str);
$str = str_replace("u0152", 'Œ', $str);
$str = str_replace("u0153", 'œ', $str);
$str = str_replace("u0154", 'Ŕ', $str);
$str = str_replace("u0155", 'ŕ', $str);
$str = str_replace("u0156", 'Ŗ', $str);
$str = str_replace("u0157", 'ŗ', $str);
$str = str_replace("u0158", 'Ř', $str);
$str = str_replace("u0159", 'ř', $str);
$str = str_replace("u015a", 'Ś', $str);
$str = str_replace("u015b", 'ś', $str);
$str = str_replace("u015c", 'Ŝ', $str);
$str = str_replace("u015d", 'ŝ', $str);
$str = str_replace("u015e", 'Ş', $str);
$str = str_replace("u015f", 'ş', $str);
$str = str_replace("u0160", 'Š', $str);
$str = str_replace("u0161", 'š', $str);
$str = str_replace("u0162", 'Ţ', $str);
$str = str_replace("u0163", 'ţ', $str);
$str = str_replace("u0164", 'Ť', $str);
$str = str_replace("u0165", 'ť', $str);
$str = str_replace("u0166", 'Ŧ', $str);
$str = str_replace("u0167", 'ŧ', $str);
$str = str_replace("u0168", 'Ũ', $str);
$str = str_replace("u0169", 'ũ', $str);
$str = str_replace("u016a", 'Ū', $str);
$str = str_replace("u016b", 'ū', $str);
$str = str_replace("u016c", 'Ŭ', $str);
$str = str_replace("u016d", 'ŭ', $str);
$str = str_replace("u016e", 'Ů', $str);
$str = str_replace("u016f", 'ů', $str);
$str = str_replace("u0170", 'Ű', $str);
$str = str_replace("u0171", 'ű', $str);
$str = str_replace("u0172", 'Ų', $str);
$str = str_replace("u0173", 'ų', $str);
$str = str_replace("u0174", 'Ŵ', $str);
$str = str_replace("u0175", 'ŵ', $str);
$str = str_replace("u0176", 'Ŷ', $str);
$str = str_replace("u0177", 'ŷ', $str);
$str = str_replace("u0178", 'Ÿ', $str);
$str = str_replace("u0179", 'Ź', $str);
$str = str_replace("u017a", 'ź', $str);
$str = str_replace("u017b", 'Ż', $str);
$str = str_replace("u017c", 'ż', $str);
$str = str_replace("u017d", 'Ž', $str);
$str = str_replace("u017e", 'ž', $str);
$str = str_replace("u017f", 'ſ', $str);
$str = str_replace("u0180", 'ƀ', $str);
$str = str_replace("u0181", 'Ɓ', $str);
$str = str_replace("u0182", 'Ƃ', $str);
$str = str_replace("u0183", 'ƃ', $str);
$str = str_replace("u0184", 'Ƅ', $str);
$str = str_replace("u0185", 'ƅ', $str);
$str = str_replace("u0186", 'Ɔ', $str);
$str = str_replace("u0187", 'Ƈ', $str);
$str = str_replace("u0188", 'ƈ', $str);
$str = str_replace("u0189", 'Ɖ', $str);
$str = str_replace("u018a", 'Ɗ', $str);
$str = str_replace("u018b", 'Ƌ', $str);
$str = str_replace("u018c", 'ƌ', $str);
$str = str_replace("u018d", 'ƍ', $str);
$str = str_replace("u018e", 'Ǝ', $str);
$str = str_replace("u018f", 'Ə', $str);
$str = str_replace("u0190", 'Ɛ', $str);
$str = str_replace("u0191", 'Ƒ', $str);
$str = str_replace("u0192", 'ƒ', $str);
$str = str_replace("u0193", 'Ɠ', $str);
$str = str_replace("u0194", 'Ɣ', $str);
$str = str_replace("u0195", 'ƕ', $str);
$str = str_replace("u0196", 'Ɩ', $str);
$str = str_replace("u0197", 'Ɨ', $str);
$str = str_replace("u0198", 'Ƙ', $str);
$str = str_replace("u0199", 'ƙ', $str);
$str = str_replace("u019a", 'ƚ', $str);
$str = str_replace("u019b", 'ƛ', $str);
$str = str_replace("u019c", 'Ɯ', $str);
$str = str_replace("u019d", 'Ɲ', $str);
$str = str_replace("u019e", 'ƞ', $str);
$str = str_replace("u019f", 'Ɵ', $str);
$str = str_replace("u01a0", 'Ơ', $str);
$str = str_replace("u01a1", 'ơ', $str);
$str = str_replace("u01a2", 'Ƣ', $str);
$str = str_replace("u01a3", 'ƣ', $str);
$str = str_replace("u01a4", 'Ƥ', $str);
$str = str_replace("u01a5", 'ƥ', $str);
$str = str_replace("u01a6", 'Ʀ', $str);
$str = str_replace("u01a7", 'Ƨ', $str);
$str = str_replace("u01a8", 'ƨ', $str);
$str = str_replace("u01a9", 'Ʃ', $str);
$str = str_replace("u01aa", 'ƪ', $str);
$str = str_replace("u01ab", 'ƫ', $str);
$str = str_replace("u01ac", 'Ƭ', $str);
$str = str_replace("u01ad", 'ƭ', $str);
$str = str_replace("u01ae", 'Ʈ', $str);
$str = str_replace("u01af", 'Ư', $str);
$str = str_replace("u01b0", 'ư', $str);
$str = str_replace("u01b1", 'Ʊ', $str);
$str = str_replace("u01b2", 'Ʋ', $str);
$str = str_replace("u01b3", 'Ƴ', $str);
$str = str_replace("u01b4", 'ƴ', $str);
$str = str_replace("u01b5", 'Ƶ', $str);
$str = str_replace("u01b6", 'ƶ', $str);
$str = str_replace("u01b7", 'Ʒ', $str);
$str = str_replace("u01b8", 'Ƹ', $str);
$str = str_replace("u01b9", 'ƹ', $str);
$str = str_replace("u01ba", 'ƺ', $str);
$str = str_replace("u01bb", 'ƻ', $str);
$str = str_replace("u01bc", 'Ƽ', $str);
$str = str_replace("u01bd", 'ƽ', $str);
$str = str_replace("u01be", 'ƾ', $str);
$str = str_replace("u01bf", 'ƿ', $str);
$str = str_replace("u01c0", 'ǀ', $str);
$str = str_replace("u01c1", 'ǁ', $str);
$str = str_replace("u01c2", 'ǂ', $str);
$str = str_replace("u01c3", 'ǃ', $str);
$str = str_replace("u01c4", 'Ǆ', $str);
$str = str_replace("u01c5", 'ǅ', $str);
$str = str_replace("u01c6", 'ǆ', $str);
$str = str_replace("u01c7", 'Ǉ', $str);
$str = str_replace("u01c8", 'ǈ', $str);
$str = str_replace("u01c9", 'ǉ', $str);
$str = str_replace("u01ca", 'Ǌ', $str);
$str = str_replace("u01cb", 'ǋ', $str);
$str = str_replace("u01cc", 'ǌ', $str);
$str = str_replace("u01cd", 'Ǎ', $str);
$str = str_replace("u01ce", 'ǎ', $str);
$str = str_replace("u01cf", 'Ǐ', $str);
$str = str_replace("u01d0", 'ǐ', $str);
$str = str_replace("u01d1", 'Ǒ', $str);
$str = str_replace("u01d2", 'ǒ', $str);
$str = str_replace("u01d3", 'Ǔ', $str);
$str = str_replace("u01d4", 'ǔ', $str);
$str = str_replace("u01d5", 'Ǖ', $str);
$str = str_replace("u01d6", 'ǖ', $str);
$str = str_replace("u01d7", 'Ǘ', $str);
$str = str_replace("u01d8", 'ǘ', $str);
$str = str_replace("u01d9", 'Ǚ', $str);
$str = str_replace("u01da", 'ǚ', $str);
$str = str_replace("u01db", 'Ǜ', $str);
$str = str_replace("u01dc", 'ǜ', $str);
$str = str_replace("u01dd", 'ǝ', $str);
$str = str_replace("u01de", 'Ǟ', $str);
$str = str_replace("u01df", 'ǟ', $str);
$str = str_replace("u01e0", 'Ǡ', $str);
$str = str_replace("u01e1", 'ǡ', $str);
$str = str_replace("u01e2", 'Ǣ', $str);
$str = str_replace("u01e3", 'ǣ', $str);
$str = str_replace("u01e4", 'Ǥ', $str);
$str = str_replace("u01e5", 'ǥ', $str);
$str = str_replace("u01e6", 'Ǧ', $str);
$str = str_replace("u01e7", 'ǧ', $str);
$str = str_replace("u01e8", 'Ǩ', $str);
$str = str_replace("u01e9", 'ǩ', $str);
$str = str_replace("u01ea", 'Ǫ', $str);
$str = str_replace("u01eb", 'ǫ', $str);
$str = str_replace("u01ec", 'Ǭ', $str);
$str = str_replace("u01ed", 'ǭ', $str);
$str = str_replace("u01ee", 'Ǯ', $str);
$str = str_replace("u01ef", 'ǯ', $str);
$str = str_replace("u01f0", 'ǰ', $str);
$str = str_replace("u01f1", 'Ǳ', $str);
$str = str_replace("u01f2", 'ǲ', $str);
$str = str_replace("u01f3", 'ǳ', $str);
$str = str_replace("u01f4", 'Ǵ', $str);
$str = str_replace("u01f5", 'ǵ', $str);
$str = str_replace("u01f6", 'Ƕ', $str);
$str = str_replace("u01f7", 'Ƿ', $str);
$str = str_replace("u01f8", 'Ǹ', $str);
$str = str_replace("u01f9", 'ǹ', $str);
$str = str_replace("u01fa", 'Ǻ', $str);
$str = str_replace("u01fb", 'ǻ', $str);
$str = str_replace("u01fc", 'Ǽ', $str);
$str = str_replace("u01fd", 'ǽ', $str);
$str = str_replace("u01fe", 'Ǿ', $str);
$str = str_replace("u01ff", 'ǿ', $str);

	return $str;
}

?>
