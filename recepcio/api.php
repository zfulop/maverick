<?php

if(!hasParameter('location')) {
	echo "location parameter missing";
	return;
}

$configFile = '../includes/config/' . getParameter('location') . '.php';
if(!file_exists($configFile)) {
	echo "invalid location parameter";
	return;
}
require($configFile);
require('../includes/country_alias.php');
require('includes.php');
require('room_booking.php');
require('bcr.php');

session_start();
$_SESSION['login_hotel'] = getParameter('location');

if(!hasParameter('action')) {
	echo "'action' parameter missing";
	return;
}
$action = getParameter('action');
logDebug("BEGIN*****************************************************");
logDebug("API action: $action");

$retVal = null;
if($action == 'rooms') {
	$retVal = _loadRooms(null, null);
} elseif($action == 'availability') {
	$retVal = loadAvailability();
} elseif($action == 'services') {
	$retVal = loadServices();
} elseif($action == 'book') {
	$retVal = doBooking();
} elseif($action == 'dictionary') {
	$retVal = loadWebsiteTexts();
} elseif($action == 'room_avalability') {
	$retVal = loadRoomCalendarAvailability();
} elseif($action == 'room_highlights') {
	$retVal = loadRoomHighlights();
} elseif($action == 'room_highlights_with_special_offers') {
	$retVal = loadRoomHighlightsWithSpecialOffers();
} elseif($action == 'confirm_booking') {
	$retVal = getBookingToConfirm();
} elseif($action == 'confirm_booking_submit') {
	$retVal = doConfirmBooking();
} else {
	echo "invalid action parameter value";
}

if(!is_null($retVal)) {
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($retVal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
}
logDebug("end of API call");
logDebug("*****************************************************END");
return;





function _loadRooms($from, $to) {
	if(!checkMissingParameters(array('location','lang','currency'))) {
		return null;
	}

	logDebug("Loading rooms");
	$location = getParameter('location');
	$lang = getParameter('lang');
	$currency = getParameter('currency');
	
	$filePath = JSON_DIR . $location . '/rooms_' . $lang . '_' . $currency . '.json';
	if(file_exists($filePath) and (is_null($from) or $from == date('Y-m-d'))) {
		logDebug("File exists: $filePath. Loading the file instead of loading all data from the db");
		$json = file_get_contents($filePath);
		return json_decode($json, true);
	}
	
	logDebug("Loading the rooms data from the db");
	$link = db_connect($location, true);

	$now = time();

	if(hasParameter('from') and hasParameter('to')) {
		$from = getParameter('from');
		$to = getParameter('to');
	}
	if(is_null($from)) {
		$from = date('Y-m-d');
	}
	if(is_null($to)) {
		$to = $from;
	}

	PriceDao::loadPriceForDate(strtotime($from), strtotime($to), $location);

	$roomTypesData = RoomDao::getRoomTypesWithRooms($lang, $from, $to, $link);
	enrichWithImageAndPrice($roomTypesData, $lang, $currency, $link);

	logDebug("Rooms loaded. There are " . count($roomTypesData) . " room types");
	mysql_close($link);

	$json = json_encode($roomTypesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	logDebug("Saving rooms data to $filePath");
	file_put_contents($filePath, $json);
	return $roomTypesData;
}

function enrichWithImageAndPrice(&$roomTypesData, $lang, $currency, $link) {
	foreach(loadRoomImages($lang, $link) as $rtId => $imgs) {
		if(!isset($roomTypesData[$rtId])) {
			continue;
		}
		$roomTypesData[$rtId]['images'] = $imgs;
	}

	$today = date('Y-m-d');
	$todayDash = date('Y/m/d');
	logDebug("getting prices for date: " . $todayDash);
	$prices = RoomDao::getRoomPricesForDate($todayDash, $link);
	foreach($roomTypesData as $rtId => &$roomType) {
		$pricePerBed = $roomType['price_per_bed'];
		$pricePerRoom = $roomType['price_per_room'];
		$surchargePerBed = $roomType['surcharge_per_bed'];

		if(isset($prices[$rtId])) {
			logDebug("\tfor room type: " . $roomType['rt_name'] . " the special price for bed: " . $prices[$rtId]['price_per_bed'] . " and for room: " . $prices[$rtId]['price_per_room']);
			$pricePerBed = $prices[$rtId]['price_per_bed'];
			$pricePerRoom = $prices[$rtId]['price_per_room'];
			$surchargePerBed = $prices[$rtId]['surcharge_per_bed'];
		}
		
		$roomType['price_per_bed'] = convertAmount($pricePerBed, 'EUR', $currency, $today);
		$roomType['price_per_room'] = convertAmount($pricePerRoom, 'EUR', $currency, $today);
		$roomType['surcharge_per_bed'] = $surchargePerBed;
	}
}

function loadAvailability() {
	if(!checkMissingParameters(array('location','lang','from','to','currency'))) {
		return null;
	}

	$location = getParameter('location');
	$lang = getParameter('lang');
	$currency = getParameter('currency');

	$fromDate = getParameter('from');
	$toDate = getParameter('to');
	$nights = round((strtotime($toDate) - strtotime($fromDate)) / (60*60*24));
	$arriveDateTs = strtotime($fromDate);
	$arriveDate = $fromDate;
	$lastNight = date('Y-m-d', strtotime($fromDate . '+' . ($nights-1) . ' days'));
	$lastNightTs = strtotime($lastNight);

	$filterRoomIds = null;
	if(hasParameter('filter_room_types')) {
		$filterRoomIds = explode(',', getParameter('filter_room_types'));
		logDebug("Filtering for room type ids: " . print_r($filterRoomIds, true));
	}
	
	logDebug("Loading availability from $fromDate to $toDate ($nights nights)");

	if(!hasParameter('ignore_date_check') or (hasParameter('ignore_date_check') and getParameter('ignore_date_check') !== 'true')) {
		if($fromDate < date('Y-m-d')) {
			return array('error' => 'BOOKING_DATE_MUST_BE_IN_THE_FUTURE');
		}
		if($toDate <= date('Y-m-d')) {
			return array('error' => 'BOOKING_DATE_MUST_BE_IN_THE_FUTURE');
		}
		if($toDate <= $fromDate) {
			return array('error' => 'CHECKOUT_DATE_MUST_BE_AFTER_CHECKIN_DATE');
		}
	}
		
	$link = db_connect($location);

	$minMax = getMinMaxStay($fromDate, $toDate, $location);
	if(!is_null($minMax) and $minMax['min_stay'] > $nights) {
		mysql_close($link);
		return array('error' => 'FOR_SELECTED_DATE_MIN_STAY ' . $minMax['min_stay']);
	}
	if(!is_null($minMax) and !is_null($minMax['max_stay']) and  $minMax['max_stay'] < $nights) {
		mysql_close($link);
		return array('error' => 'FOR_SELECTED_DATE_MAX_STAY ' . $minMax['max_stay']);
	}

	PriceDao::loadPriceForDate($arriveDateTs, $lastNightTs, $location);

	logDebug("Loading special offers for period");
	$retVal = array();
	$specialOffers = array();
	foreach(loadSpecialOffersFromFile($arriveDate, $lastNight, $link, $lang, $location) as $soId => $so) {
		if($so['visible'] == 1) {
			$specialOffers[$soId] = $so;
		}
	}
	usort($specialOffers, 'sortOffersByPercent');
	$retVal['special_offers'] = $specialOffers;

	$roomTypesData = _loadRooms($fromDate, $toDate);
	foreach($roomTypesData as $roomTypeId => $rt) {
		// logDebug("\tFor room type: " . $rt['rt_name'] . "($roomTypeId) there are " . $rt['num_of_rooms'] . " rooms of this type [" . implode(",", $rt['rooms_providing_availability']) . "]");
		if(RoomDao::isDorm($rt)) {
			// logDebug("\t\tmax num of available beds: " . $rt['num_of_rooms'] * $rt['num_of_beds']);
			$roomTypesData[$roomTypeId]['num_of_beds_avail'] = $rt['num_of_rooms'] * $rt['num_of_beds'];
		} else {
			$roomTypesData[$roomTypeId]['num_of_rooms_avail'] = $rt['num_of_rooms'];
			// logDebug("\t\tmax num of available rooms: " . $rt['num_of_rooms']);
		}
	}
	
	// logDebug("Checking occupancy based on saved bookings for the selected period");
	for($currDate = $arriveDate; $currDate <= $lastNight; $currDate = date('Y-m-d', strtotime($currDate . ' +1 day'))) {
		// logDebug("\tChecking date $currDate");
		$roomsProvidingAvailability = array();
		$numOfRooms = array();
		$numOfBeds = array();
		$originalRoomTypes = array();
		$roomAlreadyHasBooking = array();
		foreach($roomTypesData as $rtId => $roomType) {
			// logDebug("\t\tFor room type: " . $roomType['rt_name'] . "($rtId) the rooms providing availability: " . implode(",", $roomType['rooms_providing_availability']));
			$roomsProvidingAvailability[$rtId] = $roomType['rooms_providing_availability'];
			$numOfRooms[$rtId] = $roomType['num_of_rooms'];
			$numOfBeds[$rtId] = $roomType['num_of_rooms'] * $roomType['num_of_beds'];
			$originalRoomTypes[$rtId] = $roomType['original_rooms_types'];
		}
		foreach(loadBookingsPerRoomFromExtractedFile($currDate, $location) as $roomId => $bookings) {
			foreach($bookings as $oneBooking) {
				// logDebug("\t\tChecking booking for room: $roomId, num_of_person: " . $oneBooking['num_of_person'] . ' booking type: ' . $oneBooking['booking_type']);
				if($oneBooking['booking_type'] == 'BED') {
					$numOfBeds[$oneBooking['room_type_id']] -= $oneBooking['num_of_person'];
				} else {
					if(in_array($roomId, $roomAlreadyHasBooking)) {
						logDebug("\t\tRoom is already used for today");
						continue;
					} else {
						$roomAlreadyHasBooking[] = $roomId;
					}
					$roomTypesToDecrease = array();
					foreach($roomsProvidingAvailability as $rtId => $roomIds) {
						// logDebug("\t\t\tFor room type: $rtId the rooms providing availability are: [" . implode(",", $roomIds) . "]");
						if(in_array($roomId, $roomIds)) {
							// logDebug("\t\t\tDecreasing availability for room type: $rtId"); 
							$roomTypesToDecrease[] = $rtId;
						}
					}
					foreach($roomTypesToDecrease as $rtId) {
						$roomsProvidingAvailability[$rtId] = remove_element_from_array($roomsProvidingAvailability[$rtId], $roomId);
						$numOfRooms[$rtId] -= 1;
						// logDebug("\t\t\tRemoving availability from room type: $rtId. After removal it has " . $numOfRooms[$rtId] . " rooms");
					}
				}
			}
		}
		foreach($roomTypesData as $roomTypeId => $roomType) {
			// Check if for a room type there is only 1 room available and that rooms original room type is that type, then that room cannot be
			// counted as available for any other addtional room type
			if(count($roomsProvidingAvailability[$roomTypeId]) != $numOfRooms[$roomTypeId]) {
				logError("For room type: " . roomType['rt_name'] . "($roomTypeId) the rooms providing availability array has " . count($roomsProvidingAvailability[$roomTypeId]) . " elements but the numOfRooms has a value of " . $numOfRooms[$roomTypeId]);
				continue;
			}
			if(count($roomsProvidingAvailability[$roomTypeId]) != 1) {
				continue;
			}
			if(!isset($roomsProvidingAvailability[$roomTypeId][0])) {
				logError("There should be 1 element in roomsProvidingAvailability for room type: $roomTypeId. However it contains: " . print_r($roomsProvidingAvailability[$roomTypeId], true));
			}
			$roomId = $roomsProvidingAvailability[$roomTypeId][0];
			if(!RoomDao::isDorm($roomType) and in_array($roomId, $originalRoomTypes[$roomTypeId])) {
				// logDebug("\t\tFor room type: " . $roomType['rt_name'] . "($roomTypeId) and date: $currDate there is only one room available ($roomId) and that room's original room type is this room type");
				// logDebug("\t\tRemoving availability from the additional room types that this room has"); 
				$roomTypesToDecrease = array();
				foreach($roomsProvidingAvailability as $rtId => $roomIds) {
					if($roomTypeId != $rtId and in_array($roomId, $roomIds)) {
						logDebug("\t\t\tDecreasing availability for room type: $rtId"); 
						$roomTypesToDecrease[] = $rtId;
					}
				}
				foreach($roomTypesToDecrease as $rtId) {
					$roomsProvidingAvailability[$rtId] = remove_element_from_array($roomsProvidingAvailability[$rtId], $roomId);
					$numOfRooms[$rtId] -= 1;
					// logDebug("\t\t\tRemoving availability from room type: $rtId. After removal it has " . $numOfRooms[$rtId] . " rooms");
				}
			}
		}

		foreach(array_keys($roomTypesData) as $roomTypeId) {
			$roomType = $roomTypesData[$roomTypeId];
			if(RoomDao::isDorm($roomType)) {
				// logDebug("\t\tFor today for DORM room type: " . $roomType['name'] . "($roomTypeId) the number of available beds: " . $numOfBeds[$roomTypeId] . ". The number of available beds so far: " . $roomType['num_of_beds_avail']); 
				$roomTypesData[$roomTypeId]['num_of_beds_avail'] = min($roomType['num_of_beds_avail'], $numOfBeds[$roomTypeId]);
			} else {
				// logDebug("\t\tFor today for PRIVATE/APARTMENT room type: " . $roomType['rt_name'] . "($roomTypeId) the number of available rooms: " . $numOfRooms[$roomTypeId] . ". The number available rooms so far: " . $roomType['num_of_rooms_avail']); 
				$roomTypesData[$roomTypeId]['num_of_rooms_avail'] = min($roomType['num_of_rooms_avail'], $numOfRooms[$roomTypeId]);
			}
		}

	}

	$roomTypes = array();
	foreach($roomTypesData as $roomTypeId => $roomType) {
		$roomTypes[$roomTypeId] = fillPriceForAvailability($arriveDateTs, $nights, $roomType, $specialOffers, $currency, $link);
		if(RoomDao::isDorm($roomType)) {
			unset($roomTypesData[$roomTypeId]['num_of_rooms_avail']);
		} else {
			unset($roomTypesData[$roomTypeId]['num_of_beds_avail']);
		}
	}

	$retVal['rooms'] = array();
	foreach($roomTypes as $roomTypeId => $roomType) {
		if(is_null($filterRoomIds) or in_array($roomTypeId, $filterRoomIds)) {
			matchSpecialOffer($roomType, $roomTypeId, $nights, $arriveDate, $specialOffers);
			$retVal['rooms'][] = $roomType;
		}
	}

	usort($retVal['rooms'], 'sortAvailabilityRooms');
	
	mysql_close($link);
	return $retVal;
}

function sortOffersByPercent($so1, $so2) {
	if($so1['discount_pct'] < $so2['discount_pct']) {
		return -1;
	}
	if($so2['discount_pct'] < $so1['discount_pct']) {
		return 1;
	}
	return 0;
}

function loadSpecialOffersFromFile($firstNight, $lastNight, $link, $lang, $location) {
	$filePath = JSON_DIR . $location . '/special_offers_' . $lang . '.json';
	logDebug("\t\tLoad  special offers from file: $filePath");
	$spcialOffers = array();
	if(file_exists($filePath)) {
		$json = file_get_contents($filePath);
		$specialOffers = json_decode($json, true);
	} else {
		logDebug("\t\tFile does not exist, extracting from DB and saving it into the file");
		$specialOffers  = SpecialOfferDao::getAllSpecialOffers($link, $lang);
		$json = json_encode($specialOffers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
		logDebug("Saving special offers data to $filePath");
		file_put_contents($filePath, $json);
	}
	return SpecialOfferDao::getSpecialOffersFromArray($firstNight, $lastNight, $specialOffers);
}


function sortAvailabilityRooms($room1, $room2) {
	// Available rooms first, then not available rooms
	if(isAvailable($room1) and !isAvailable($room2)) {
		return -1;
	}
	if(isAvailable($room2) and !isAvailable($room1)) {
		return 1;
	}
	// dorm first, private second, apartment after
	if(RoomDao::isDorm($room1) and !RoomDao::isDorm($room2)) {
		return -1;
	}
	if(RoomDao::isDorm($room2) and !RoomDao::isDorm($room1)) {
		return 1;
	}
	if(isPrivate($room1) and isApartment($room2)) {
		return -1;
	}
	if(isPrivate($room2) and isApartment($room1)) {
		return 1;
	}
	// finaly sort by price where both are available or bot are not available and both are the same types.
	if($room1['price'] < $room2['price']) {
		return -1;
	}
	if($room2['price'] < $room1['price']) {
		return 1;
	}
	
	return 0;
}

function loadBookingsPerRoomFromExtractedFile($date, $location) {
	$date = str_replace('/','-',$date);
	$filePath = JSON_DIR . $location . '/avail_' . $date . '.json';
	logDebug("\t\tLoad  bookings from file: $filePath");
	if(file_exists($filePath)) {
		$json = file_get_contents($filePath);
		return json_decode($json, true);
	} else {
		logDebug("\t\tFile does not exist, returning empty array");
		return array();
	}
}


function isAvailable($roomAvailability) {
	if(RoomDao::isDorm($roomAvailability) and $roomAvailability['num_of_beds_avail'] > 0) {
		return true;
	}
	if(!RoomDao::isDorm($roomAvailability) and $roomAvailability['num_of_rooms_avail'] > 0) {
		return true;
	}
}

function removeAvailabilityForAdditionalRoomTypes($roomId, $roomTypeId, &$roomTypesData) {
	logDebug("For room type: $roomTypeId there is only one room available ($roomId) and that room's original room type is this room type"); 
	logDebug("Removing availability from the additional room types that this room is has."); 
	// There is only 1 room available for this room type and that one room's main room type is this room type.
	// In this case we need to remove this room's availability from all the additional room types for this room
	foreach($roomData['room_types']	as $additionalRoomTypeId => $additionalRoomTypeName) {
		if($additionalRoomTypeId == $roomTypeId) { continue; }
		logDebug("	remove availability from the additional room type: $additionalRoomTypeName ($additionalRoomTypeId). Current availability: " . $roomTypesData[$additionalRoomTypeId]['num_of_rooms_avail']);
		$roomTypesData[$additionalRoomTypeId]['rooms_providing_availability'] = removeRoomIdFromList($roomId, $roomTypesData[$additionalRoomTypeId]['rooms_providing_availability']);
		$roomTypesData[$additionalRoomTypeId]['num_of_rooms_avail'] -= 1;
		logDebug("  new availability: " . $roomTypesData[$additionalRoomTypeId]['num_of_rooms_avail']);
		for($i = 0; $i < count($retVal['rooms']); $i++) {
			
		}
	}
}

function removeRoomIdFromList($roomId, $roomsProvidingAvailability) {
	$roomIdArray = array();
	foreach(explode(',',$roomsProvidingAvailability) as $id) {
		if($id != $roomId) { $roomIdArray[] = $id; }
	}
	return implode(",", $roomIdArray);
}

// Returns array wit key: room type id, value: list of image objects
function loadRoomImages($lang, $link) {
	logDebug("Loading room images");
	$location = getParameter('location');
	$roomImages = array();
	$imgCnt = 0;
	foreach(RoomDao::getRoomImages($lang, $link) as $imgId => $row) {
		foreach($row['room_types'] as $rtId) {
			if(!isset($roomImages[$rtId])) {
				$roomImages[$rtId] = array();
			}
			$img = array();
			$img['original_img_url'] = ROOMS_IMG_URL . $location . '/' . $row['filename'];
			$img['medium_img_url'] = ROOMS_IMG_URL . $location . '/' . (is_null($row['medium']) ? $row['filename'] : $row['medium']);
			$img['thumb_img_url'] = ROOMS_IMG_URL . $location . '/' . (is_null($row['thumb']) ? $row['filename'] : $row['thumb']);
			$img['width'] = $row['width'];
			$img['height'] = $row['height'];
			$img['default'] = 0;
			if(isset($row['description'][$lang])) {
				$img['description'] = $row['description'][$lang];
			} else {
				$img['description'] = '';
			}
			if(in_array($rtId, $row['default_for_room_types'])) {
				$img['default'] = 1;
			}
			$roomImages[$rtId][] = $img;
		}
		$imgCnt += 1;
	}
	foreach($roomImages as $rtId => &$imgs) {
		usort($imgs, 'sortByDefaultDesc');
	}
	logDebug("There are $imgCnt room images loaded for " . count($roomImages) . " room types");
	return $roomImages;
}

function sortByDefaultDesc($img1, $img2) {
	if($img1['default'] == 1) return -1;
	if($img2['default'] == 1) return 1;
	return 0;
}


function fillPriceForAvailability($arriveTs, $nights, $roomType, &$specialOffers, $currency, $link) {
	$roomTypeId = $roomType['id'];
	$roomType['price'] = convertAmount(PriceDao::getPrice($arriveTs, $nights, $roomTypeId, 1, $link)/$nights,'EUR',$currency, date('Y-m-d')) ;
	if(isApartment($roomType)) {
		$roomType['price'] = convertAmount(PriceDao::getPrice($arriveTs, $nights, $roomTypeId, 2, $link)/$nights,'EUR',$currency, date('Y-m-d')) ;
		for($i=2; $i<= $roomType['num_of_beds']; $i++) {
			$roomType['price_' . $i] = convertAmount(PriceDao::getPrice($arriveTs, $nights, $roomTypeId, $i, $link),'EUR',$currency, date('Y-m-d')) / $nights;
		}
	}

	if(!is_null($specialOffers)) {
		list($discount, $selectedSo) = SpecialOfferDao::findSpecialOffer($specialOffers, $roomType, $nights, date('Y-m-d', $arriveTs), 1);
		// apply special offer
		if($discount > 0) {
			$discountedPayment = $roomType['price'] * (100 - $discount) / 100;
			$roomType['price_without_discount'] = $roomType['price'];
			$roomType['price'] = $discountedPayment;
			if(isApartment($roomType)) {
				for($i=2; $i<= $roomType['num_of_beds']; $i++) {
					$discountedPayment = $roomType['price_' . $i] * (100 - $discount) / 100;
					$roomType['price_' . $i . '_without_discount'] = $roomType['price_' . $i];
					$roomType['price_' . $i] = $discountedPayment;
				}
			}
		}
	}
	
	return $roomType;
}



function sortRoomsByAvailOrder($rt1, $rt2) {
	if($rt1['num_of_beds_avail'] > 0 and $rt2['num_of_beds_avail'] < 1) {
		return -1;
	}
	if($rt1['num_of_beds_avail'] < 1 and $rt2['num_of_beds_avail'] > 0) {
		return 1;
	}
	if($rt1['_order'] < $rt2['_order']) {
		return -1;
	}
	if($rt1['_order'] > $rt2['_order']) {
		return 1;
	}
}

function matchSpecialOffer(&$roomType, $roomTypeId, $nights, $arriveDate, $specialOffers) {
	$so = null;
	$specialOfferForOneMoreDay = null;
	list($discount, $so) = SpecialOfferDao::findSpecialOffer($specialOffers, $roomType, $nights, $arriveDate, $roomType['num_of_beds']);
	list($discountPlus1, $specialOfferForOneMoreDay) = SpecialOfferDao::findSpecialOffer($specialOffers, $roomType, $nights+1, $arriveDate, $roomType['num_of_beds']);
	$roomType['special_offer'] = $so;
	$roomType['special_offer_for_one_more_day'] = $specialOfferForOneMoreDay;
}

function getMinMaxStay($fromDate, $toDate, $location) {
	$filePath = JSON_DIR . $location . '/min_max_stay.json';
	if(!file_exists($filePath)) {
		logError("min max stay file does not exist: $filePath");
		return array();
	}
	
	logDebug("File exists: $filePath. Loading the min max stay file");
	$json = file_get_contents($filePath);
	foreach(json_decode($json, true) as $minMaxStay) {
		if((is_null($minMaxStay['from_date']) or $minMaxStay['from_date'] <= $fromDate) and (is_null($minMaxStay['to_date']) or $minMaxStay['to_date'] >= $fromDate)) {
			return $minMaxStay;
		}
	}
	return null;
}


function loadServices() {
	if(!checkMissingParameters(array('location','lang','currency'))) {
		return null;
	}

	logDebug("Loading services");
	$location = getParameter('location');
	$lang = getParameter('lang');
	$currency = getParameter('currency');

	$filePath = JSON_DIR . $location . '/services_' . $lang . '_' . $currency . '.json';
	if(file_exists($filePath)) {
		logDebug("File exists: $filePath. Loading the file instead of loading all data from the db");
		$json = file_get_contents($filePath);
		return json_decode($json, true);
	}

	$link = db_connect($location);
	
	$services = loadServicesFromDB($lang, $link);
	$today = date('Y-m-d');
	
	foreach($services as &$oneService) {
		$oneService['price'] = convertAmount($oneService['price'], $oneService['currency'], $currency, $today);
		$oneService['currency'] = $currency;
	}

	logDebug("Services loaded");
	mysql_close($link);

	$json = json_encode($services, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	logDebug("Saving services data to $filePath");
	file_put_contents($filePath, $json);

	return $services;
}


function loadRoomHighlightsWithSpecialOffers() {
	$location = getParameter('location');
	$lang = getParameter('lang');

	$today = date('Y-m-d');

	$link = db_connect($location);

	$roomHighlights = loadRoomHighlights();
	$specialOffers = loadSpecialOffersFromFile(null, $today, $link, $lang, $location);
	
	$retVal = array('room_highlights' => $roomHighlights, 'special_offers' => $specialOffers);
	
	mysql_close($link);
	return $retVal;
}


function loadRoomHighlights() {
	if(!checkMissingParameters(array('location','lang','currency'))) {
		return null;
	}

	logDebug("Loading room highlights");
	$location = getParameter('location');
	$lang = getParameter('lang');
	$currency = getParameter('currency');

	$filePath = JSON_DIR . $location . '/rooms_hightlights_' . $lang . '_' . $currency . '.json';
	if(file_exists($filePath)) {
		logDebug("File exists: $filePath. Loading the file instead of loading all data from the db");
		$json = file_get_contents($filePath);
		return json_decode($json, true);
	}

	$today = date('Y-m-d');
	$link = db_connect($location);	

	$roomTypesData = RoomDao::getRoomTypes($lang, $link);
	enrichWithImageAndPrice($roomTypesData, $lang, $currency, $link);

	$roomHighlights = RoomDao::getRoomHighlights($link);
	$retVal = array();
	foreach($roomHighlights as $rtId) {
		$retVal[] = $roomTypesData[$rtId];
	}

	logDebug("Room highlights loaded. There are " . count($retVal) . " room highlights");
	mysql_close($link);
	$json = json_encode($retVal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	logDebug("Saving room highlights data to $filePath");
	file_put_contents($filePath, $json);
	return $retVal;
}


function getBookingToConfirm() {
	if(!checkMissingParameters(array('location','lang','confirm_code'))) {
		return null;
	}

	logDebug("Loading booking to confirm");
	$location = getParameter('location');
	$lang = getParameter('lang');
	$confirmCode = getParameter('confirm_code');
	$link = db_connect($location);

	$row = getDBBooking($confirmCode, $link);
	$retVal = array();
	if(!is_null($row)) {
		$retVal = array('name' => $row['name'],
			'name' => $row['name'],
			'email' => $row['email'],
			'arrive_date' => str_replace('/','-',$row['first_night']),
			'departure_date' => date('Y-m-d', strtotime(str_replace('/','-',$row['last_night']) . ' +1 day')),
		);
	}

	mysql_close($link);
	return $retVal;
}

function getDBBooking($confirmCode, $link) {
	$idx = strpos($confirmCode, 'A');
	$descrId = substr($confirmCode, 0, $idx);
	$code = substr($confirmCode, $idx + 1);
	logDebug("booking descr id: $descrId");
	$sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
	$result = mysql_query($sql, $link);
	if($result and (mysql_num_rows($result) > 0)) {
		$row = mysql_fetch_assoc($result);
		$emailValue = $row['email'];
		if(password_verify($emailValue, $code)) {
			logDebug("Found and validated booking with email: $emailValue");
			return $row;
		} else {
			logDebug("Cannot validate the email");
		}
	} else {
		logDebug("No booking found for description id");
	}
	return null;
}


function doConfirmBooking() {
	if(!checkMissingParameters(array('location','lang','confirm_code', 'arrive_time', 'comment'))) {
		return null;
	}

	logDebug("Submit booking confirmation");
	$location = getParameter('location');
	$lang = getParameter('lang');
	$confirmCode = getParameter('confirm_code');
	$link = db_connect($location);

	$row = getDBBooking($confirmCode, $link);
	$retVal = array();
	if(!is_null($row)) {
		$arrivalTime = mysql_real_escape_string(getParameter('arrive_time'), $link);
		$comment = mysql_real_escape_string(getParameter('comment'), $link);
		$descrId = $row['id'];

		$sql = "UPDATE bcr SET checkin_time='$arrivalTime', comment='$comment' WHERE booking_description_id=$descrId";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("cannot confirm booking: Cannot update booking data (sql: $sql) error: " . mysql_error($link));
			$hasError = true;
		}

		$sql = "UPDATE booking_descriptions SET arrival_time='$arrivalTime', confirmed=1 WHERE id=$descrId";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("cannot confirm booking: Cannot update booking data (sql: $sql) error: " . mysql_error($link));
			$hasError = true;
		}
		$retVal = array('name' => $row['name'],
			'name' => $row['name'],
			'email' => $row['email'],
			'arrive_date' => str_replace('/','-',$row['first_night']),
			'departure_date' => date('Y-m-d', strtotime(str_replace('/','-',$row['last_night']) . ' +1 day')),
		);

		logDebug("Sending email about guest confirmation to reception: " . CONTACT_EMAIL);
		$name = $row['name'];
		$email = $row['email'];
		$id = $row['id'];
		$arriveDate = $row['first_night'];
		$editBookingUrl = "http://reception.roomcaptain.com/edit_booking.php?description_id=$id";
		$mailMessage = <<<EOT
		
Guest confirmed a <a href="$editBookingUrl">booking</a><br>
<table>
<tr><td>Name</td><td>$name</td></tr>
<tr><td>Email</td><td>$email</td></tr>
<tr><td>Arrive date</td><td>$arriveDate</td></tr>
<tr><td>Arrive time</td><td>$arrivalTime</td></tr>
<tr><td>Comment</td><td>$comment</td></tr>
</table>

This email was generated from the API call when a user confirmed a booking via the website in response to a BCR email.

EOT;
		$result = MaverickMailer::send(CONTACT_EMAIL, $location . " API", CONTACT_EMAIL, $location . " API", "Guest confirmed booking", $mailMessage);
		if(!is_null($result)) {
			logError("Cannot send email: $result");
		}
		
	}

	mysql_close($link);
	return $retVal;
}



function doBooking() {
	global $COUNTRY_ALIASES;

	if(!checkMissingParameters(array('firstname','lastname','email','phone','nationality','street','city','zip','country','currency','comment','booking_data','from_date','to_date'))) {
		return null;
	}

	$now = date('Y-m-d H:i:s');
	$today = date('Y-m-d');
	$location = getParameter('location');
	$lang = getParameter('lang');
	$currency = getParameter('currency');

	$link = db_connect($location);
	
	
	$firstname = getParameter('firstname');
	$lastname = getParameter('lastname');

	if(trim($firstname)=='1' or trim($lastname)=='1') {
		return null;
	}
	
	$address = mysql_real_escape_string(getParameter('street') . ', ' . getParameter('city') . ', ' . getParameter('zip') . ', ' . getParameter('country'), $link);
	$name = mysql_real_escape_string("$firstname $lastname", $link);
	$nationality = getParameter('nationality');
	if(isset($COUNTRY_ALIASES[strtolower($nationality)])) {
		$nationality = mysql_real_escape_string($COUNTRY_ALIASES[strtolower($nationality)], $link);
	} else {
		$nationality = mysql_real_escape_string($nationality, $link);
	}

	$email = mysql_real_escape_string(getParameter('email'), $link);
	$phone = mysql_real_escape_string(getParameter('phone'), $link);
	$comment = mysql_real_escape_string(getParameter('comment'), $link);
	$bookingRef = mysql_real_escape_string(gen_booking_ref(), $link);

	verifyBlacklist("$firstname $lastname", $email, CONTACT_EMAIL, $link);
	
	$arriveDate = getParameter('from_date');
	$arriveDateTs = strtotime(getParameter('from_date'));
	$departureDate = getParameter('to_date');
	$departureDateTs = strtotime(getParameter('to_date'));
	$nights = round(($departureDateTs - $arriveDateTs) / (60*60*24));
	$lastNightTs = $arriveDateTs;
	if($nights > 1) {
		$lastNightTs = strtotime($arriveDate . " +" . ($nights-1) . " days");
	}
	$lastNight = date('Y-m-d', $lastNightTs);

	logDebug("arrive date: $arriveDate, departure date: $departureDate, last night: $lastNight, num of nights: $nights");
	$link = db_connect($location);
	mysql_query('START TRANSACTION', $link);
	
	$specialOffers = SpecialOfferDao::getSpecialOffers(null, $lastNight, $link, $lang);
	logDebug("There are " . count($specialOffers) . " special offers valid between $arriveDate and $lastNight");
	$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
	$roomTypesData = loadRoomTypes($link, $lang);
	$services = loadServicesFromDB($lang, $link);

	$bookingRequest = json_decode(getParameter('booking_data'), true);
	$source = mysql_real_escape_string('saját', $link);
	$sql = "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time, language, currency,booking_ref) VALUES ('$name', NULL, '$address', '$nationality', '$email', '$phone', '" . str_replace("-", "/", $arriveDate) . "', '" . str_replace("-", "/", $lastNight) . "', $nights, 0, 0, 0, 0, '$comment', '$source', '', '$lang', '$currency', '$bookingRef')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save booking: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		mysql_query('ROLLBACK', $link);
		mysql_close($link);
		return array('error' => 'Could not save booking description.');
	}
	$descriptionId = mysql_insert_id($link);

	list($toBook, $roomChanges) = getBookingData($bookingRequest, $arriveDate, $lastNight, $rooms, $roomTypesData, $specialOffers);
	$bookingIds = saveBookings($toBook, $roomChanges, $arriveDate, $lastNight, $rooms, $roomTypesData, $specialOffers, $descriptionId, $link);
	$bookedServices = json_decode(getParameter('services'), true);
	logDebug("Services to book: " . print_r($bookedServices, true));
	foreach($bookedServices as $service) {
		$id = $service['serviceId'];
		if(!isset($services[$id])) {
			logError("The booking contains a service with id: $id that is not in the DB. Ignoring.");
			continue;
		}
		$title = $services[$id]['name'];
		$serviceComment = $service['comment'];
		$occasion =  $service['occasion'];
		if($occasion < 1) {
			logError("The service: $title [$id] has occasion: $occasion. It has to be greater than 0. Ignoring.");
		}
		$price =  $services[$id]['price'] * $occasion;
		$serviceCurrency = $services[$id]['currency'];
		$type = $services[$id]['service_charge_type'];
		$serviceComment = mysql_real_escape_string("$title for $occasion occasions. $serviceComment", $link);
		$sql = "INSERT INTO service_charges (booking_description_id, amount, currency, time_of_service, comment, type) VALUES ($descriptionId, $price, '$serviceCurrency', '$now', '$serviceComment', '$type')";
		if(!mysql_query($sql, $link)) {
			trigger_error("Cannot save service charge: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			mysql_query('ROLLBACK', $link);
			mysql_close($link);
			return array('error' => 'Could not save the selected services.');
		}
	}

	// Save IFA
	$ifaMultiplier = 0.034;
	logDebug("Using IFA multiplier of $ifaMultiplier");
	$roomPrice = 0;
	foreach($toBook as $roomId => $oneRoomBooked) {
		$dprice = convertAmount($oneRoomBooked['discounted_price'], 'EUR', $currency, $today);
		$roomPrice += $dprice;
	}
	$ifa = $roomPrice * $ifaMultiplier;
	$ifaComment = 'IFA multiplier: ' . $ifaMultiplier;
	$sql = "INSERT INTO service_charges (booking_description_id, amount, currency, time_of_service, comment, type) VALUES ($descriptionId, $ifa, '$currency', '$now', '$ifaComment', 'IFA / City Tax')";
	mysql_query($sql, $link);

	
	$_SESSION['login_user'] = 'website';
	audit(AUDIT_CREATE_BOOKING, array('booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $descriptionId, $link);
	mysql_query('COMMIT', $link);

	$sql = "SELECT * FROM booking_descriptions WHERE id=$descriptionId";
	$result = mysql_query($sql, $link);
	$savedBookingDescr = mysql_fetch_assoc($result);

	mysql_close($link);
	$link = db_connect($location);
	
	// function sendBcrMessage($bookingDescr, $subject, $bcrMessage, $link, &$dict, $location) {
	$texts = loadWebsiteTexts();
	$texts[$lang] = $texts;
	$locationName = $texts['LOCATION_NAME_' . strtoupper($location)];
	$subject = str_replace('LOCATION', $locationName, $texts['BOOKING_CONFIRMATION_EMAIL_SUBJECT']);
	$bcr = new BCR($savedBookingDescr, $location, $texts, $link);
	$bcr->sendBcrMessage($subject, '');
	if($result != 'SUCCESS') {
		logError("Cannot send email to guest for confirming booking: $result");
	}
	$bcr->sendBookingMessageToReception();
	
	// sendEmailForBooking($name, $email, $phone, $address, $nationality, $arriveDate, $departureDate, $nights, $comment, $toBook, $bookedServices, $roomTypesData, $services, $descriptionId);

	mysql_close($link);

	return array('success'=> true);
}


// Loads the dictionary for the website
function loadWebsiteTexts() {
	if(!checkMissingParameters(array('location','lang'))) {
		return null;
	}
	$location = getParameter('location');
	$lang = getParameter('lang');
	$link = db_connect($location, true);

	$sql = "SELECT * FROM lang_text WHERE lang='$lang' AND table_name='website'";
	$result = mysql_query($sql, $link);
	$dict = array();
	while($row = mysql_fetch_assoc($result)) {
		$dict[$row['column_name']] = $row['value'];
	}
	mysql_close($link);
	return $dict;
}

function loadRoomCalendarAvailability() {
	if(!checkMissingParameters(array('location','lang','from','to','room_type_id'))) {
		return null;
	}
	$location = getParameter('location');
	$lang = getParameter('lang');
	$startDate = getParameter('from');
	$endDate = getParameter('to');
	$startTs = strtotime($startDate);
	$endTs = strtotime($endDate);

	if($endTs < $startTs) {
		return array('error' => 'CHECKOUT_DATE_MUST_BE_AFTER_CHECKIN_DATE');
	}

	$link = db_connect($location);

	$roomTypeId = getParameter('room_type_id');

	$roomTypesData = loadRoomTypes($link, $lang);
	$roomType = $roomTypesData[$roomTypeId];
	$rooms = loadRooms(date('Y', $startTs), date('m', $startTs), date('d', $startTs), date('Y', $endTs), date('m', $endTs), date('d', $endTs), $link, $lang);

	$currTs = $startTs;
	$avail = array();
	for($i = 0; $currTs <= $endTs; $i++) {
		$currTs = strtotime("$startDate +$i day");
		$dayOfMonth = date('d', $currTs);
		$month = strftime("%b", $currTs);
		$currDate = date('Y-m-d', $currTs);
		$availability = 0;
		$availabilityRoom = 0;
		foreach($rooms as $roomId => $roomData) {
			if($roomData['room_type_id'] != $roomTypeId) {
				continue;
			}
			$beds = getNumOfAvailBeds($roomData, $currDate);
			if((isPrivate($roomData) or isApartment($roomData)) and $beds < $roomData['num_of_beds']) {
				$beds = 0;
			}
			$availability += $beds;
			if($beds > 0 and (isPrivate($roomData) or isApartment($roomData))) {
				$availabilityRoom += 1;
			}
		}

		$avail[] = array('date' => $currDate, 'numberOfAvailableBeds' => $availability, 'numberOfAvailableRooms' => $availabilityRoom);
	}

	mysql_close($link);
	return $avail;
}

function loadServicesFromDB($lang, $link) {
	$services = array();
	$sql = "SELECT s.id,s.price,s.currency,s.img, s.name,s.service_charge_type,s.free_service, t.value AS title, d.value AS description , u.value AS unit_name FROM services s INNER JOIN lang_text t ON (s.id=t.row_id AND t.table_name='services' AND t.column_name='title' AND t.lang='$lang') INNER JOIN lang_text d ON (s.id=d.row_id AND d.table_name='services' AND d.column_name='description' AND d.lang='$lang') LEFT OUTER JOIN lang_text u ON (s.id=u.row_id AND u.table_name='services' AND u.column_name='unit_name' AND u.lang='$lang') ORDER BY s._order";
	$result = mysql_query($sql, $link);
	while($row = mysql_fetch_assoc($result)) {
		if(strlen($row['img']) > 0) {
			$row['img'] = SERVICES_IMG_URL . $row['img'];
		}
		$services[$row['id']] = $row;
	}

	return $services;
}



function checkMissingParameters($paramNames) {
	foreach($paramNames as $oneParamName) {
		if(!hasParameter($oneParamName)) {
			echo "'$oneParamName' parameter missing";
			return false;
		}
		if($oneParamName == 'currency' and !checkParameterValue($oneParamName, getCurrencies())) {
			return false;
		}
		if($oneParamName == 'lang' and !checkParameterValue($oneParamName, array_keys(getLanguages()))) {
			return false;
		}
	}
	return true;
}

function checkParameterValue($parameterName, $possibleValues) {
	if(!in_array(getParameter($parameterName), $possibleValues)) {
		echo "$parameterName parameter is invalid. Valid values: " . implode(',', $possibleValues);
		return false;
	}
	return true;
}

function getParameter($parameterName) {
	if(isset($argv)) {
		for($i = 1; $i < (count($argv)-1); $i++) {
			if($argv[$i] == ('-' . $parameterName)) {
				return $argv[$i+1];
			}
		}
	}
	if(isset($_REQUEST) and isset($_REQUEST[$parameterName])) {
		return $_REQUEST[$parameterName];
	}
	return null;
}

function hasParameter($parameterName) {
	if(isset($argv)) {
		$parameterName = '-' . $parameterName;
		for($i = 1; $i < (count($argv)-1); $i++) {
			if($argv[$i] == $parameterName) {
				return true;
			}
		}
	}
	if(isset($_REQUEST)) {
		return isset($_REQUEST[$parameterName]);
	}
	return false;
}

function remove_element_from_array($arr, $element) {
	$retVal = array();
	foreach($arr as $item) {
		if($item != $element) {
			$retVal[] = $item;
		}
	}
	return $retVal;
}

?>