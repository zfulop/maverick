<?php


define('HASHED_PASSWORD', '$1$rM3.YS0.$.BMdC5Qd31wO6VUArIhb21');
define('SIMULATION', false);

date_default_timezone_set('Europe/Budapest');

$SOURCES = array(
	'ago' => 'Agoda',
	'air' => 'AirBnb',
	'bnw' => 'BookNow - Tripadvisor',
	'boo' => 'booking.com',
	'exp' => array('Hotel Collect Booking' => 'Expedia - Hotel Collect', 'Expedia Collect Booking' => 'Expedia - Expedia Collect'),
	'fam' => 'Famous Hostels',
	'gom' => 'Gomio',
	'hb2' => 'hostelbookers',
	'hc' => 'HC',
	'hcu' => 'Hostel Culture',
	'hi' => 'HiHostels',
	'hbe' => 'Hotel Beds',
	'hrs' => 'HRS',
	'hw2' => 'hostelworld',
	'ost' => 'Ostrovok.ru',
	'rep' => 'Travel Public',
	'ta' => 'Trip Advisor'
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

require('./includes.php');
logDebug ("Start processing incoming booking request");
logDebug ("=========================================");
logDebug("included " . './includes.php');
require('../includes/country_alias.php');
logDebug("included " . '../includes/country_alias.php');
require('./room_booking.php');
logDebug("included " . './room_booking.php');


if(php_sapi_name() === 'cli') {
	$bookingJson = file_get_contents($argv[1]);
	for ($i = 0; $i <= 31; ++$i) { 
		$bookingJson = str_replace(chr($i), "", $bookingJson); 
	}
	$bookingJson = str_replace(chr(127), "", $bookingJson);
	if (0 === strpos(bin2hex($bookingJson), 'efbbbf')) {
		$bookingJson = substr($bookingJson, 3);
	}
	
	logDebug("File content encoding: " . mb_detect_encoding($bookingJson, mb_detect_order(), true));
} else {
	$bookingJson = $_REQUEST['booking'];
	$pwd = $_REQUEST['password'];
	if(crypt($pwd, HASHED_PASSWORD) !=  HASHED_PASSWORD) {
		respond('10', false, "Incorrect password");
		return;
	}
	$bookingJson = stripslashes($bookingJson);
}

logDebug("JSON START>>>" . $bookingJson . "<<<JSON END");
$bookingData = json_decode($bookingJson, true, 512, JSON_UNESCAPED_UNICODE);
$err = json_last_error();
if($err) {
	logDebug("Error parsing json: $err");
	respond('21', false, "Error parsing json: $err");
	return;
}

// sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', "Booking request in " . LOCATION, 'booking data: ' . print_r($bookingData,true) . "\n\nRaw data: \n" . stripslashes($_REQUEST['booking']));

if(!isset($bookingData['PropertyId'])) {
	$matches = array();
	preg_match('/"PropertyId":([^,]*),/', $bookingJson, $matches);
	$propertyId = $matches[1];
} else {
	$propertyId = $bookingData['PropertyId'];
}


require('../includes/config/myallocator.php');
$location = $myallocatorPropertyMap[$propertyId];
 
logDebug("Location: $location ($propertyId)");

require('../includes/config/' . $location . '.php');
logDebug("included " . '../includes/config/' . $location . '.php');

$lang = 'eng';
//require(LANG_DIR . $lang . '.php');
//logDebug("included " . LANG_DIR . $lang . '.php');

$_SESSION['login_user'] = 'myallocator';

set_debug($bookingJson);
set_debug(print_r($bookingData,true));

$locationName = constant('DB_' . strtoupper($location) . '_NAME');

$link = db_connect($location);

mysql_query("START TRANSACTION", $link);

$roomTypesData = loadRoomTypes($link);

$loadedRooms = array();
$rooms = array();

// sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', "myalloc debug", "location: $location, property: $propertyId, db name: " . mysql_db_name($link) . ", db error: " . mysql_error($link));

$result = null;
if((strpos($bookingJson, "\"IsCancellation\":true") > 0) or (isset($bookingData['IsCancellation']) and $bookingData['IsCancellation'])) {
	$matches = array();
	preg_match('/"MyallocatorId":"([^"]*)"/', $bookingJson, $matches);
	$myallocId = $matches[1];
	// sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', "Booking cancellation [$myallocId] for " . LOCATION, stripslashes($_REQUEST['booking']));
	$result = cancelBooking($bookingData['MyallocatorId'], $link);
} else {
	// sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', "Booking creation " . LOCATION, 'booking data: ' . print_r($bookingData,true) . "\n\nRaw data: \n" . stripslashes($_REQUEST['booking']));
	$result = createBooking($bookingData, $link);
}

if(!$result) {
	logDebug("Rolling back db changes");
	mysql_query("ROLLBACK", $link);
} else {
	logDebug("Commiting db changes");
	mysql_query("COMMIT", $link);
}

logDebug("End of processing");
logDebug("=================");


mysql_close($link);
// End of program


function cancelBooking($myAllocatorId, $link) {
	global $lang, $locationName;
	logDebug("Cancelling booking with myalloc id: $myAllocatorId");
	$sql = "SELECT * FROM booking_descriptions WHERE my_allocator_id='$myAllocatorId'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		respond('51', false, "Cannot retieve booking in admin interface when canceling it: " . mysql_error($link) . " (SQL: $sql)");
		return false;
	}
	$row = mysql_fetch_assoc($result);
	$descrId = $row['id'];
	logDebug("Id of the booking to cancel: $descrId");
	if($row['checked_in'] == 1) {
		$subject = "Cancellation request arrived from myallocator for a Checked-in booking ($locationName)";
		$descriptionId = $row['id'];
		$name = $row['name'];
		$email = $row['email'];
		$fn = $row['first_night'];
		$ln = $row['last_night'];
		$message .= "<a href=\"" . EDIT_BOOKING_URL . "?description_id=$descriptionId\">View booking</a><br>\n";
		$message .= "$name - $email<br>\n";
		$message .= "$fn - $ln<br>\n";
		logDebug("Sending notification email about cancelling checked in booking to " . CONTACT_EMAIL);
		$result = sendMail(CONTACT_EMAIL, $locationName, CONTACT_EMAIL, $locationName, $subject, $message);
		respond(null, true);
		return true;
	}


	if(intval($descrId) > 0) {
		$sql = "UPDATE booking_descriptions SET cancelled=1,cancel_type='guest' WHERE id=$descrId";
		if(SIMULATION) {
			logDebug("Not actually cancelling as this is a SIMULATION only. SQL: $sql");
		} else {
			$result = mysql_query($sql, $link);
			if(!$result) {
				respond('51', false, "Cannot cancel booking in admin interface: " . mysql_error($link) . " (SQL: $sql)");
				return false;
			} else {
				logDebug("Booking cancelled.");
			}
		}
	}

	respond(null, true);

	audit(AUDIT_CANCEL_BOOKING, $_REQUEST, 0, $descrId, $link, 'myallocator');
	return true;
}



function createBooking($bookingData, $link) {
	global $lang, $bookingJson, $locationName, $SOURCES, $MESSAGES, $COUNTRY_ALIASES, $propertyId, $loadedRooms, $rooms;
	logDebug("Creating booking");
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

	foreach($bookingData['Rooms'] as $room) {
		$arriveDate = $room['StartDate'];
		$lastNight = $room['EndDate'];
		$cutoff = strtotime($arriveDate . " +1 day +3 hours");
//		if($cutoff < time()) {
//			respond('54', false, "Cannot create booking: arrive date ($arriveDate) is in the past");
//			return false;
//		}
		if($arriveDate > $lastNight) {
			respond('55', false, "Cannot create booking: arrive date ($arriveDate) must be before (or the same as) last night ($lastNight)");
			return false;
		}
	}

	$currency = $bookingData['TotalCurrency'];
	$myAllocatorId = $bookingData['MyallocatorId'];
	$bookingRef = $bookingData['OrderId'];

	$customer = $bookingData['Customers'][0];
	$firstname = mysql_real_escape_string(decode($customer['CustomerFName']), $link);
	$lastname = mysql_real_escape_string(decode($customer['CustomerLName']), $link);
	$email = $customer['CustomerEmail'];

	verifyBlacklist("$firstname $lastname", $email, CONTACT_EMAIL, $link);

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

	set_debug("before combine: <pre>" . print_r($bookingData['Rooms'], true) . "</pre>\n");
	if(canCombineRooms($bookingData['Rooms'])) {
		logDebug("combining rooms");
		$bookingData['Rooms'] = combineRooms($bookingData['Rooms']);
	}
	set_debug("after combine: <pre>" . print_r($bookingData['Rooms'], true) . "</pre>\n");

	$roomTypesData = loadRoomTypes($link, $lang);
	$bookingDescriptions = array();
	$extraServices = array();
	$serviceChargeAmt = 0;

	// Save the parking as a service change
	if(isset($bookingData['ExtraServices'])) {
		foreach($bookingData['ExtraServices'] as $oneService) {
			if((substr($oneService['Description'],0,6) == 'Parkol' or substr($oneService['Description'],0,7) == 'Reggeli') and isset($oneService['Price'])) {
				$amount = doubleval($oneService['Price']);
				$curr = $oneService['Currency'];
				$descr = mysql_real_escape_string(decode($oneService['Description']), $link);
				$svcText = 'Parkolás';
				if(substr($oneService['Description'],0,7) == 'Reggeli') {
					$svcText = 'Reggeli - FatMama';
				}
				logDebug("Saving extra service as service_charge: $svcText, $amount $curr");
				$extraServices[] = array('amount' => $amount, 'comment' => $descr, 'type' => $svcText, 'currency' => $curr);
				$serviceChargeAmt += $amount;
			}
		}
	}

	foreach($bookingData['Rooms'] as $roomData) {
		$arriveDate = $roomData['StartDate'];
		$arriveDateTs = strtotime($arriveDate);
		$lastNight = $roomData['EndDate'];
		$lastNightTs = strtotime($lastNight);
		$nights = round(($lastNightTs-$arriveDateTs) / (60*60*24)) + 1;
		$bdKey = $arriveDate . '|' . $lastNight;
      
		if(!isset($bookingDescriptions[$bdKey])) {
			$bookingDescriptions[$bdKey] = array('arriveDate' => $arriveDate, 'lastNight' => $lastNight, 'name' => "$firstname $lastname", 'email' => $email, 'bookings' => array(), 'sql' => "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time, language, currency, my_allocator_id, create_time, booking_ref) VALUES ('$firstname $lastname', NULL, '$address', '$nationality', '$email', '$phone', '" . str_replace("-", "/", $arriveDate) . "', '" . str_replace("-", "/", $lastNight) . "', $nights, 0, 0, 0, 0, '$comment', '$source', '', '$lang', '$currency', '$myAllocatorId', '$nowTime', '$bookingRef')");
		}

		$rooms = null;
		if(isset($loadedRooms[$arriveDate . '|' . $lastNight])) {
			$rooms = $loadedRooms[$arriveDate . '|' . $lastNight];
		} else {
			$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
			$loadedRooms[$arriveDate . '|' . $lastNight] = $rooms;
		}

		foreach($roomData['RoomTypeIds'] as $myAllocRoomTypeId) {
			$roomTypeIds = findRoomTypeId($myAllocRoomTypeId, $propertyId);
			if(is_null($roomTypeIds)) {
				set_debug("Cannot find roomTypeId for myallocator room id: $myAllocRoomTypeId");
				respond('52', false, "Cannot find roomTypeId for myallocator room id: $myAllocRoomTypeId, property id: $propertyId");
				return false;
			}

			$price = $roomData['Price'];
//                      if(isset($bookingData['TotalTaxes'])) {
//                            $price = $price + $bookingData['TotalTaxes'] / count($bookingData['Rooms']);
//                      }
			if(isPrivate($roomTypesData[$roomTypeIds[0]]) or isApartment($roomTypesData[$roomTypeIds[0]])) {
				for($i = 0; $i < $roomData['Units']; $i++) {
					$bookingDescriptions[$bdKey]['bookings'][] = array('type' => 'ROOM', 'myAllocRoomTypeId' => $myAllocRoomTypeId, 'numOfPerson' => $roomTypesData[$roomTypeIds[0]]['num_of_beds'], 'price' => ($price / floatval($roomData['Units'])), 'propertyId'=> $propertyId);
				}
			} else {
				$bookingDescriptions[$bdKey]['bookings'][] = array('type' => 'BED', 'myAllocRoomTypeId' => $myAllocRoomTypeId, 'numOfPerson' => $roomData['Units'], 'price' => $price, 'propertyId'=> $propertyId);
			}
		}

	} // end of loop bookingData['Rooms']
	logDebug("Incoming booking(s): " . print_r($bookingDescriptions, true));
	foreach($bookingDescriptions as $bdKey => $bd) {
		list($fn,$ln) = explode('|', $bdKey);
		logDebug("   " . $fn . '-' . $ln);
		foreach($bd['bookings'] as $b) {
			logDebug("      type:" . $b['type'] . ',myAllocRoomTypeId:' . $b['myAllocRoomTypeId'] . ',numOfPerson:' . $b['numOfPerson'] . ',price:' . $b['price']);
		}
	}
	

	// Adjust room price if it includes service charge
	$roomPrice = calcRoomPrice($bookingDescriptions);
	logDebug("Total price: " . $bookingData['TotalPrice'] . ", total room price: $roomPrice, service charges: $serviceChargeAmt");
	if($roomPrice == $bookingData['TotalPrice'] and $serviceChargeAmt > 0) {
		logDebug("Room price include service charge, lets deduct it");
		$scAmtPerBooking = $serviceChargeAmt / getNumOfBookings($bookingDescriptions);
		foreach($bookingDescriptions as $bdKey => $bd) {
			foreach($bd['bookings'] as $b) {
				$b['price'] -= $scAmtPerBooking;
			}
		}
	}
	$roomPrice = calcRoomPrice($bookingDescriptions);
	logDebug("After adjustment total room price: $roomPrice");

	$ifa = $roomPrice * 0.034;
	$extraServices[] = array('amount' => $ifa, 'comment' => 'IFA', 'type' => 'IFA / City Tax', 'currency' => $bookingData['TotalCurrency']);

	// Now compare with DB data
	$dbBookingDescriptions = loadDBBookings($myAllocatorId, $link);
	logDebug("Already saved booking(s):");
	foreach($dbBookingDescriptions as $id => $bd) {
		logDebug("   " . $bd['first_night'] . '-' . $bd['last_night'] . ($bd['checked_in'] == 1 ? 'checked in' : ''));
		foreach($bd['bookings'] as $b) {
			logDebug("      roomtypeid:" . $b['original_room_type_id'] . ',num of person:' . $b['num_of_person'] . ',room payment:' . $b['room_payment']);
		}
	}
	$diffs = reportDiff($dbBookingDescriptions, $bookingDescriptions);
	if(count($dbBookingDescriptions) > 0) {
		$diffMsg = '';
		foreach($diffs as $oneDiff) {
			$diffMsg .= '   ' . $oneDiff['type'] . ' - ' . $oneDiff['descr'] . "\n";
		}
		if(count($diffs) > 0) {
			logDebug("The differences between DB and the incoming message: \n$diffMsg");
		} else {
			logDebug("No difference found between DB and the incoming message.");
		}
	} else {
		logDebug("This is a new booking (not yet in the DB.");
	}
	
	$response = true;
	if(count($diffs) < 1) {
		logDebug("There are no differences. Finish here.");
	} elseif(isCheckedIn($dbBookingDescriptions)) {
		logDebug("Guest checked in, just mailing the differences.");
		sendDiffEmail($dbBookingDescriptions, $diffs);
	} elseif(isOnlyPriceDiff($diffs)) {
		logDebug("same rooms in booking, update prices only");
		$response = updatePrices($dbBookingDescriptions, $bookingDescriptions, $diffs);
		if($response) {
			$response = saveExtraServices($dbBookingDescriptions, $extraServices, array_keys($dbBookingDescriptions));
		}
	} elseif(count($dbBookingDescriptions) < 1) {
		logDebug("no existing booking yet");
		$descrIds = insertBookingIntoDb($bookingDescriptions);
		if($descrIds) {
			$response = saveExtraServices(array(), $extraServices, $descrIds);
		}
		if($response) {
			$response = saveDeposit($bookingData, $descrIds);
		}
		sendEmailNotification($bookingDescriptions);
	} else {
		logDebug("there is existing booking and the structure is different. Remove the existing bookings and create new ones.");
		$response = deleteExistingBooking($dbBookingDescriptions, $bookingDescriptions, $diffs);
		$descrIds = null;
		if($response) {
			$descrIds = insertBookingIntoDb($bookingDescriptions);
		}
		if($descrIds) {
			$response = updateExistingPayments($dbBookingDescriptions, $bookingDescriptions, $descrIds);
		}
		if($response) {
			$response = saveExtraServices($dbBookingDescriptions, $extraServices, $descrIds);
		}
		if($response) {
			$response = saveDeposit($bookingData, $descrIds);
		}

		sendEmailNotification($bookingDescriptions);
	}


	if(!$response) {
		respond('51', false, "Cannot create booking: DB Error");
	} else {
		respond(null, true);
	}

	return $response;
}

function loadDBBookings($myAllocatorId, $link) {
	$sql = "SELECT * FROM booking_descriptions WHERE my_allocator_id='$myAllocatorId'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		respond('51', false, "Cannot retrieve booking when handling incoming myallocator booking request: " . mysql_error($link) . " (SQL: $sql)");
		return false;
	}
	$existingBookingDescription = array();
	while($row = mysql_fetch_assoc($result)) {
		$row['bookings'] = array();
		$existingBookingDescription[$row['id']] = $row;
	}

	if(count($existingBookingDescription) > 0) {
		$bookingCounter = 1;
		$sql = "SELECT b.description_id, b.booking_type, b.num_of_person, b.room_payment, b.original_room_type_id, rt.name as room_type_name FROM bookings b INNER JOIN room_types rt ON b.original_room_type_id=rt.id WHERE b.booking_type='ROOM' AND b.description_id IN (" . implode(",", array_keys($existingBookingDescription)) . ")";
		$result = mysql_query($sql, $link);
		if(!$result) {
			logDebug("Cannot retrieve ROOM bookings when handling incoming myallocator booking request: " . mysql_error($link) . " (SQL: $sql)");
			return false;
		}
		while($row = mysql_fetch_assoc($result)) {
			$row['id'] = $bookingCounter;
			$bookingCounter += 1;
			$existingBookingDescription[$row['description_id']]['bookings'][] = $row;
		}
		$sql = "SELECT b.description_id, b.booking_type, sum(b.num_of_person) AS num_of_person, sum(b.room_payment) AS room_payment, b.original_room_type_id, rt.name as room_type_name FROM bookings b INNER JOIN room_types rt ON b.original_room_type_id=rt.id WHERE b.booking_type='BED' AND b.description_id IN (" . implode(",", array_keys($existingBookingDescription)) . ") GROUP BY b.description_id, b.booking_type, b.original_room_type_id, rt.name";
		$result = mysql_query($sql, $link);
		if(!$result) {
			logDebug("Cannot retrieve BED bookings when handling incoming myallocator booking request: " . mysql_error($link) . " (SQL: $sql)");
			return false;
		}
		while($row = mysql_fetch_assoc($result)) {
			$row['id'] = $bookingCounter;
			$bookingCounter += 1;
			$existingBookingDescription[$row['description_id']]['bookings'][] = $row;
		}
	}
	return $existingBookingDescription;
}

function isCheckedIn($existingBookingDescription) {
	logDebug('Checking if booking is checked in');
	foreach($existingBookingDescription as $bdId => $bd) {
		if($bd['checked_in'] == 1) {
			return true;
		}
	}
	return false;
}

function calcRoomPrice($bookingDescriptions) {
	$roomPrice = 0;
	foreach($bookingDescriptions as $bdKey => $bd) {
		foreach($bd['bookings'] as $b) {
			$roomPrice += $b['price'];
		}
	}
	return $roomPrice;
}

function getNumOfBookings($bookingDescriptions) {
	$numOfBookings = 0;
	foreach($bookingDescriptions as $bdKey => $bd) {
		$numOfBookings += count($bd['bookings']);
	}
	return $numOfBookings;
}

// $bookingDescriptions: bdKey => array(arriveDate, lastNight, bookings => array(myAllocRoomTypeId, numOfPerson, price)) | bdKey=arriveDate|lastNight
// $dbBookingDescription: $bdId => array(first_night, last_night, bookings => array(room_id, num_of_person, room_payment)
function reportDiff(&$dbBookingDescriptions, &$bookingDescriptions) {
	global $roomTypesData;
	$diffs = array();
	$bdKeysAccounted = array();
	foreach($dbBookingDescriptions as $bdId => $dbBd) {
		$bdKey = str_replace('/','-',$dbBd['first_night']) . '|' . str_replace('/','-',$dbBd['last_night']);
		if(isset($bookingDescriptions[$bdKey])) {
			$bdKeysAccounted[] = $bdKey;
			$diffs = array_merge($diffs, reportDiffBd($dbBd, $bookingDescriptions[$bdKey]));
		} else {
			$bookings = '';
			foreach($dbBookingDescriptions[$bdId]['bookings'] as $b) {
				$bookings .= "<div style=\"margin-left: 15px;\">room type: " . $roomTypesData[$b['original_room_type_id']]['name'] . ',num of person:' . $b['num_of_person'] . ',room payment:' . $b['room_payment'] . "</div>";
			}
			$diffs[] = array('type' => 'EXTRA_IN_DB', 'descr' => 'There are extra bookings in db between dates: ' . $dbBd['first_night'] . " and " . $dbBd['last_night'] . " that are missing from incoming message. Existing bookings: $bookings");
		}
	}
	foreach(array_diff(array_keys($bookingDescriptions), $bdKeysAccounted) as $oneDiff) {
		list($firstNight, $lastNight) = explode("|", $oneDiff);
		$bookings = '';
		foreach($bookingDescriptions[$oneDiff]['bookings'] as $b) {
			$roomTypeIds = findRoomTypeId($b['myAllocRoomTypeId'], $b['propertyId']);
			$bookings .= "<div style=\"margin-left: 15px;\">room type:" . $roomTypesData[$roomTypeIds[0]]['name'] . ',num of person:' . $b['numOfPerson'] . ',room payment:' . $b['price'] . "</div>";
		}
		$diffs[] = array('type' => 'MISSING_FROM_DB', 'descr' => 'No bookings in db between dates: ' . $firstNight . " and " . $lastNight . " but there are booking(s) in the incoming message: $bookings");
	}
	return $diffs;
}


// $bookingDescriptions: bookings => array(myAllocRoomTypeId, numOfPerson, price)
// $dbBookingDescription: bookings => array(room_id, num_of_person, room_payment)
function reportDiffBd(&$dbBookingDescription, &$bookingDescription) {
	global $roomTypesData;
	$diffs = array();
	$bookingIdsMatched = array();
	foreach($bookingDescription['bookings'] as $oneBooking) {
		$roomTypeIds = findRoomTypeId($oneBooking['myAllocRoomTypeId'], $oneBooking['propertyId']);
		$bookingMatched = false;
		foreach($dbBookingDescription['bookings'] as $oneDbBooking) {
			if(in_array($oneDbBooking['original_room_type_id'], $roomTypeIds) && !in_array($oneDbBooking['id'], $bookingIdsMatched)) {
				$bookingMatched = true;
				$bookingIdsMatched[] = $oneDbBooking['id'];
				$diffs = array_merge($diffs, reportDiffOneBooking($oneDbBooking, $oneBooking, $dbBookingDescription));
				break;
			}
		}
		if(!$bookingMatched) {
			$diffs[] = array(	'type' => 'MISSING_BOOKING_FROM_DB', 
								'descr' => 'No bookings in db for room type: ' . $roomTypesData[$roomTypeIds[0]]['name'] . ', num of person: ' . $oneBooking['numOfPerson'] . ' between dates: ' . $dbBookingDescription['first_night'] . ' and ' . $dbBookingDescription['last_night'] . ', room payment: ' . $oneBooking['price'] . ' but it is in the incoming message', 
								'booking' => $oneBooking,
								'bookingDescription' => $bookingDescription);
		}
	}
	// Iterate through the DB bookings and see which bookings are there in extra
	foreach($dbBookingDescription['bookings'] as $oneDbBooking) {
		if(!in_array($oneDbBooking['id'], $bookingIdsMatched)) {
			$diffs[] = array(	'type' => 'MISSING_BOOKING_FROM_DB', 
								'descr' => 'Extra booking in db for room type: ' . $roomTypesData[$oneDbBooking['original_room_type_id']] . ', num of person: ' .  $oneDbBooking['num_of_person'] . ' between dates: ' . $dbBookingDescription['first_night'] . ' and ' . $dbBookingDescription['last_night'] . ', room payment: ' . $oneDbBooking['room_payment'] . ', this is not in the incoming message', 
								'booking' => $oneBooking,
								'bookingDescription' => $bookingDescription);
		}
	}
	return $diffs;
}

function reportDiffOneBooking($oneDbBooking, $oneBooking, $dbBookingDescription) {
	$diffs = array();
	if($oneDbBooking['num_of_person'] < $oneBooking['numOfPerson']) {
			$diffs[] = array('type' => 'LESS_GUEST_IN_DB', 'descr' => 'There are ' . $oneBooking['numOfPerson'] . ' guests from myalloc but only ' . $oneDbBooking['num_of_person'] . ' guests in the db for room type: ' . $oneDbBooking['room_type_name']);
	} elseif($oneDbBooking['num_of_person'] > $oneBooking['numOfPerson']) {
			$diffs[] = array('type' => 'MORE_GUEST_IN_DB', 'descr' => 'There are ' . $oneBooking['numOfPerson'] . ' guests from myalloc but there are ' . $oneDbBooking['num_of_person'] . ' guests in the db for room type: ' . $oneDbBooking['room_type_name']);
	} elseif($oneDbBooking['room_payment'] != $oneBooking['price']) {
			$diffs[] = array('type' => 'PRICE_MISMATCH', 'descr' => 'The price is ' . $oneBooking['price'] . ' for ' . $oneBooking['numOfPerson'] . ' guests from myalloc but in the db we have the price of ' . $oneDbBooking['room_payment'] . ' for room type: ' . $oneDbBooking['room_type_name'], 'newPrice' => $oneBooking['price'], 'booking_description_id' => $dbBookingDescription['id'], 'original_room_type_id' => $oneDbBooking['original_room_type_id'], 'booking_type' => $oneDbBooking['booking_type'], 'num_of_person' => $oneBooking['numOfPerson']);
	}
	return $diffs;
}

function sendDiffEmail(&$dbBookingDescriptions, $diffs) {
	global $locationName;
	$subject = "Checked in booking change arrived from myalloc to $locationName";
	$bd = $dbBookingDescriptions[array_keys($dbBookingDescriptions)[0]];
	$descriptionId = $bd['id'];
	$name = $bd['name'];
	$email = $bd['email'];
	$fn = $bd['first_night'];
	$ln = $bd['last_night'];
	$message .= "<a href=\"" . EDIT_BOOKING_URL . "?description_id=$descriptionId\">View booking</a><br>\n";
	$message .= "$name - $email<br>\n";
	$message .= "$fn - $ln<br>\n";
	$message .= "Changes<br>\n";
	$message .= "<ul>";
	foreach($diffs as $oneDiff) {
		$message .= "	<li>" . $oneDiff['descr'] . "</li>\n";
	}
	$message .= "</ul>";
	logDebug("Sending diff email to " . CONTACT_EMAIL);
	$result = sendMail(CONTACT_EMAIL, $locationName, CONTACT_EMAIL, $locationName, $subject, $message);
	if(!is_null($result)) {
		logDebug("Cannot send diff email: " . $result);
	}

}

function sendEmailNotification(&$bookingDescriptions) {
	global $locationName;
	// logDebug("inside sendEmailNotification() - bookingDescriptions: " . print_r($bookingDescriptions, true));
	$subject = "New booking arrived from myalloc to $locationName";
	$message = '';
	foreach($bookingDescriptions as $bdId => $bd) {
		$descriptionId = $bd['description_id'];
		$name = $bd['name'];
		$email = $bd['email'];
		$fn = $bd['arriveDate'];
		$ln = $bd['lastNight'];
		$message .= "$name - $email <a href=\"" . EDIT_BOOKING_URL . "?description_id=$descriptionId\">$fn - $ln</a><br>\n";
		logDebug("Sending email to reception about booking for $name $email between dates: $fn - $ln and ID: $descriptionId");
	}
	logDebug("Sending new booking email to " . CONTACT_EMAIL);
	$result = sendMail(CONTACT_EMAIL, $locationName, CONTACT_EMAIL, $locationName, $subject, $message);
	if(!is_null($result)) {
		logDebug("Cannot send notification email: " . $result);
	}

}


function isOnlyPriceDiff($diffs) {
	logDebug('Checking if diff is only price');
	$onyPriceDiff = true;
	foreach($diffs as $oneDiff) {
		if($oneDiff['type'] != 'PRICE_MISMATCH') {
			$onyPriceDiff = false;
			break;
		}
	}
	return $onyPriceDiff;
}

function updatePrices($dbBookingDescription, $bookingDescriptions, $diffs) {
	global $link;
	$sqls = array();
	logDebug("Updating prices");
	foreach($diffs as $oneDiff) {
		if($oneDiff['type'] == 'PRICE_MISMATCH') {
			$bookingType = $oneDiff['booking_type'];
			if($bookingType == 'BED') {
				$newPrice = $oneDiff['newPrice'] / $oneDiff['num_of_person'];
			} else {
				$newPrice = $oneDiff['newPrice'];
			}
			$bdId = $oneDiff['booking_description_id'];
			$origRoomTypeId = $oneDiff['original_room_type_id'];
			logDebug("Updating price for booking_description_id: $bdId, new price: $newPrice and original_room_type_id: $origRoomTypeId");
			$sqls[] = "UPDATE bookings SET room_payment=$newPrice WHERE description_id=$bdId AND booking_type='$bookingType' AND original_room_type_id=$origRoomTypeId";
		}
	}
	
	if(SIMULATION) {
		logDebug("SIMULATION mode, so not actualy udating prices. SQL(s): \n   " . implode("\n   ", $sqls));
	} else {
		foreach($sqls as $sql) {
			$result = mysql_query($sql, $link);
			if(!$result) {
				logDebug("Cannot update price: " . mysql_error($link) . " (SQL: $sql");
				return false;
			} else {
				logDebug("Price updated successfully. SQL: $sql");
			}
		}
	}
	return true;
}

function deleteExistingBooking($dbBookingDescriptions, $bookingDescriptions, $diffs) {
	global $link;
	logDebug("Deleting existing booking");
	$ids = array_keys($dbBookingDescriptions);
	$sqls = array();
	if(count($ids) > 0) {
		$sqls[] = 'DELETE FROM booking_room_changes WHERE booking_id in (select id from bookings where description_id in (' . implode(',',$ids) . '))';
		$sqls[] = 'DELETE FROM bookings WHERE description_id in (' . implode(',',$ids) . ')';
		$sqls[] = 'DELETE FROM booking_descriptions WHERE id in (' . implode(',',$ids) . ')';
		$sqls[] = 'DELETE FROM payments WHERE booking_description_id in (' . implode(',',$ids) . ') AND comment=\'*booking deposit*\'';
	}

	if(SIMULATION) {
		logDebug("SIMULATION mode, so not actualy deleting existing data. SQL(s): \n   " . implode("\n   ", $sqls));
	} else {
		foreach($sqls as $sql) {
			$result = mysql_query($sql, $link);
			if(!$result) {
				logDebug("Cannot delete booking data: " . mysql_error($link) . " (SQL: $sql");
				return false;
			} else {
				logDebug("Deleted data successfully. SQL: $sql");
			}
		}
	}
	
	return true;
}

function insertBookingIntoDb(&$bookingDescriptions) {
	global $loadedRooms, $roomTypesData, $link, $rooms;

	logDebug("Creating booking in DB");
	$descrIds = array();
	foreach($bookingDescriptions as $bdKey => &$oneBd) {
		list($arriveDate, $lastNight) = explode('|', $bdKey);
		if(SIMULATION) {
			logDebug("SIMULATION mode, not actualy creating booking description.");
		} else {
			if(!mysql_query($oneBd['sql'], $link)) {
				respond('51', false, 'Cannot create booking description: ' . mysql_error($link) . " (SQL: $sql)");
				return false;
			}
			$oneBd['description_id'] = mysql_insert_id($link);
			logDebug("Created booking description with id: " . $oneBd['description_id']);
			$descrIds[] = $oneBd['description_id'];
			logDebug("Booking description for $bdKey is created with id: " . $oneBd['description_id']);
		}
		$numOfPersonForRoomType = array();
		$priceForRoomType = array();
		foreach($oneBd['bookings'] as $oneBooking) {
			// array('type' => 'ROOM', 'myAllocRoomTypeId' => $myAllocRoomTypeId, 'numOfPerson' => $roomTypesData[$roomTypeId]['num_of_beds'], 'price' => ($price / floatval($roomData['Units'])), 'propertyId'=> $propertyId);
			// array('type' => 'BED', 'myAllocRoomTypeId' => $myAllocRoomTypeId, 'numOfPerson' => $roomData['Units'], 'price' => $price, 'propertyId'=> $propertyId);
			$roomTypeIds = findRoomTypeId($oneBooking['myAllocRoomTypeId'], $oneBooking['propertyId']);
			$numOfPerson = $oneBooking['numOfPerson'];
			foreach($roomTypeIds as $rtId) {
				$priceForRoomType[$rtId] = $oneBooking['price'];
			}
			$addedToExistingBooking = false;
			foreach($numOfPersonForRoomType as &$item) {
				if($item['roomTypeIds'] == $roomTypeIds) {
					$item['numOfPerson'][] = $numOfPerson;
					$addedToExistingBooking = true;
				}
			}
			if(!$addedToExistingBooking) {
				$numOfPersonArray = array();
				$numOfPersonArray[] = $numOfPerson;
				$numOfPersonForRoomType[] = array('roomTypeIds' => $roomTypeIds, 'numOfPerson' => $numOfPersonArray);
			}
		}

		$rooms = $loadedRooms[$arriveDate . '|' . $lastNight];
		list($toBook, $roomChanges) = getBookingData($numOfPersonForRoomType, $arriveDate, $lastNight, $rooms, $roomTypesData);
		$specialOffers = array();
		logDebug("num of person for room type: " . print_r($numOfPersonForRoomType, true));
		logDebug("toBook: " . print_r($toBook, true));
		logDebug("roomChanges: " . print_r($roomChanges, true));
		logDebug("price for room type: " . print_r($priceForRoomType, true));
		if(SIMULATION) {
			logDebug("SIMULATION mode, not actually saving booking.");
		} else {
			$bookingIds = saveBookings($toBook, $roomChanges, $arriveDate, $lastNight, $rooms, $roomTypesData, $specialOffers, $oneBd['description_id'], $link, $priceForRoomType);
			audit(AUDIT_CREATE_BOOKING, array('source' => 'myallocator', 'booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $oneBd['description_id'], $link);
		}
	}
	// logDebug("inside insertBookingIntoDb() - bookingDescriptions: " . print_r($bookingDescriptions, true));


	return $descrIds;
}



function updateExistingPayments($dbBookingDescriptions, $bookingDescriptions, $descrIds) {
	global $link;
	$descrId = $descrIds[0];
	$ids = array_keys($dbBookingDescriptions);
	$sql = "UPDATE payments SET booking_description_id=$descrId WHERE booking_description_id IN (" . implode(',',$ids) . ")";
	if(SIMULATION) {
		logDebug("SIMULATION mode, not actually updating payments. SQL: $sql");
	} else {
		logDebug("Updating existing payments. SQL: $sql");
		$result = mysql_query($sql, $link);
		if(!$result) {
			logDebug("Cannot update payment: " . mysql_error($link) . " (SQL: $sql)");
			return false;
		}
	}
	return true;
}




function saveDeposit(&$bookingData, $descrIds) {
	global $link;
	logDebug("Saving deposit");
	if(!isset($bookingData['Deposit']) or ($bookingData['Deposit'] < 1)) {
		logDebug("No deposit to save");
		return true;
	}

	$descrId = $descrIds[0];
	$amt = $bookingData['Deposit'];
	$curr = $bookingData['DepositCurrency'];
	logDebug("Saving deposit: $amt $curr for booking: $descrId");
	$sql = "INSERT INTO payments (booking_description_id, amount, currency, time_of_payment, comment) VALUE ($descrId, $amt, '$curr', '$nowTime', '*booking deposit*')";
	logDebug("Saving deposit. SQL: $sql");
	if(SIMULATION) {
		logDebug("SIMULATION mode, not actually saving in the db.");
	} else {
		$result = mysql_query($sql, $link);
		if(!$result) {
			logDebug("Cannot insert deposit payment: " . mysql_error($link) . " (SQL: $sql)");
			return false;
		}
	}
	return true;
}



function saveExtraServices($dbBookingDescriptions, $extraServices, $descrIds) {
	global $link;
	logDebug("Saving extra services");
	$nowTime = date('Y-m-d H:i:s');
	$descrId = $descrIds[0];
	$ids = array_keys($dbBookingDescriptions);
	if(count($ids) > 0) {
		$sql = "DELETE FROM service_charges WHERE booking_description_id IN (" . implode(',',$ids) . ")";
		if(SIMULATION) {
			logDebug("SIMULATION mode, not actually deleting existing service charges. SQL: $sql");
		} else {
			logDebug("Deleting existing service charges. SQL: $sql");
			$result = mysql_query($sql, $link);
			if(!$result) {
				logDebug("Cannot update payment: " . mysql_error($link) . " (SQL: $sql)");
				return false;
			}
		}
	}
	foreach($extraServices as $oneSc) {
		$sql = "INSERT INTO service_charges (booking_description_id, amount, currency, time_of_service, comment, type) VALUES ($descrId, " . $oneSc['amount'] . ',\'' . $oneSc['currency'] . '\',\'' . $nowTime . '\',\'' . $oneSc['comment'] . '\',\'' . $oneSc['type'] . '\')';
		if(SIMULATION) {
			logDebug("SIMULATION mode, not actually creating new service charges. SQL: $sql");
		} else {
			logDebug("Creating new service charge. SQL: $sql");
			$result = mysql_query($sql, $link);
			if(!$result) {
				logDebug("Cannot create new service charge: " . mysql_error($link) . " (SQL: $sql)");
				return false;
			}
		}
	}

	logDebug("saving extra services returning tryue");
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
	
	logDebug("Response: code=$code, success=$success, errorMessage=$errorMessage");
	$retVal = array('success' => true);
	logDebug("Response (as it is sent back): " . json_encode($retVal));
	
	
	header("Content-type: application/json; charset=utf-8");
	echo json_encode($retVal);

	if(!is_null($errorMessage)) {
		$result = sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'FZ', 'Error with booking from myallocator to ' . LOCATION, $errorMessage . "\n\nRequest:\n" . stripslashes($_REQUEST['booking']));
		if(!is_null($result)) {
			logDebug("Cannot send error email: " . $result);
		}
	}
}


function findRoomTypeId($remoteRoomId, $propertyId) {
	global $myallocatorRoomMap;
	foreach($myallocatorRoomMap[$propertyId] as $roomTypeInfo) {
		if($roomTypeInfo['remoteRoomId'] == $remoteRoomId) {
			if(!is_array($roomTypeInfo['roomTypeId'])) {
				return array($roomTypeInfo['roomTypeId']);
			} else {
				return $roomTypeInfo['roomTypeId'];
			}
		}
	}

	return null;
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
	logDebug("room dates: " . print_r($roomDates, true));
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
