<?php

if(!isset($_REQUEST['location'])) {
	echo "location parameter missing";
	return;
}

$configFile = '../includes/config/' . $_REQUEST['location'] . '.php';
if(!file_exists($configFile)) {
	echo "invalid location parameter";
	return;
}
require($configFile);

require('includes.php');
require('room_booking.php');

if(!isset($_REQUEST['action'])) {
	echo "'action' parameter missing";
	return;
}
$action = $_REQUEST['action'];
logDebug("BEGIN*****************************************************");
logDebug("API action: $action");

$retVal = null;
if($action == 'rooms') {
	$retVal = _loadRooms();
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


function _loadRooms() {
	if(!checkMissingParameters(array('location','lang','currency'))) {
		return null;
	}

	logDebug("Loading rooms");
	$location = $_REQUEST['location'];
	$lang = $_REQUEST['lang'];
	$currency = $_REQUEST['currency'];
	$link = db_connect($location);
	
	$roomTypesData = RoomDao::getRoomTypes($lang, $link);
	foreach(loadRoomImages($lang, $link) as $rtId => $imgs) {
		if(!isset($roomTypesData[$rtId])) {
			continue;
		}
		$roomTypesData[$rtId]['images'] = $imgs;
	}

	$today = date('Y-m-d');
	foreach($roomTypesData as &$roomType) {
		$roomType['price_per_bed'] = convertAmount($roomType['price_per_bed'], 'EUR', $currency, $today);
		$roomType['price_per_room'] = convertAmount($roomType['price_per_room'], 'EUR', $currency, $today);
	}

	logDebug("Rooms loaded. There are " . count($roomTypesData) . " room types");
	mysql_close($link);
	return $roomTypesData;
}

function loadAvailability() {
	if(!checkMissingParameters(array('location','lang','from','to','currency'))) {
		return null;
	}

	$location = $_REQUEST['location'];
	$lang = $_REQUEST['lang'];
	$currency = $_REQUEST['currency'];	

	$fromDate = $_REQUEST['from'];
	$toDate = $_REQUEST['to'];
	$nights = round((strtotime($toDate) - strtotime($fromDate)) / (60*60*24));

	$filterRoomIds = null;
	if(isset($_REQUEST['filter_room_types'])) {
		$filterRoomIds = explode(',', $_REQUEST['filter_room_types']);
		logDebug("Filtering for room type ids: " . print_r($filterRoomIds, true));
	}
	
	logDebug("Loading availability from $fromDate to $toDate ($nights nights)");

	if($fromDate < date('Y-m-d')) {
		return array('error' => 'BOOKING_DATE_MUST_BE_IN_THE_FUTURE');
	}
	if($toDate <= date('Y-m-d')) {
		return array('error' => 'BOOKING_DATE_MUST_BE_IN_THE_FUTURE');
	}
	if($toDate <= $fromDate) {
		return array('error' => 'CHECKOUT_DATE_MUST_BE_AFTER_CHECKIN_DATE');
	}

	$link = db_connect($location);

	$minMax = getMinMaxStay($fromDate, $toDate, $link);
	if(!is_null($minMax) and $minMax['min_stay'] > $_SESSION['nights']) {
		mysql_close($link);
		return array('error' => 'FOR_SELECTED_DATE_MIN_STAY ' . $minMax['min_stay']);
	}
	if(!is_null($minMax) and !is_null($minMax['max_stay']) and  $minMax['max_stay'] < $_SESSION['nights']) {
		mysql_close($link);
		return array('error' => 'FOR_SELECTED_DATE_MAX_STAY ' . $minMax['max_stay']);
	}

	$arriveDateTs = strtotime($fromDate);
	$arriveDate = $fromDate;

	$lastNight = date('Y-m-d', strtotime($fromDate . '+' . ($nights-1) . ' days'));
	$lastNightTs = strtotime($lastNight);

	logDebug("Loading special offers for period");
	$retVal = array();
	$specialOffers = array();
	foreach(loadSpecialOffers($arriveDate,$lastNight, $link, $lang) as $soId => $so) {
		if($so['visible'] == 1) {
			$specialOffers[$soId] = $so;
		}
	}
	$retVal['special_offers'] = $specialOffers;
	 
	logDebug("Loading room types");
	$roomTypesData = RoomDao::getRoomTypes($lang, $link);
	foreach(loadRoomImages($lang, $link) as $rtId => $imgs) {
		if(!isset($roomTypesData[$rtId])) {
			continue;
		}
		$roomTypesData[$rtId]['images'] = $imgs;
	}
	$today = date('Y-m-d');
	foreach($roomTypesData as &$roomType) {
		$roomType['price_per_bed'] = convertAmount($roomType['price_per_bed'], 'EUR', $currency, $today);
		$roomType['price_per_room'] = convertAmount($roomType['price_per_room'], 'EUR', $currency, $today);
	}	

	logDebug("Loading rooms and their bookings for the selected period");
	$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
	foreach($rooms as $roomId => $roomData) {
		foreach($roomData['room_types']	as $roomTypeId => $roomTypeName) {
			if(is_null($filterRoomIds) or in_array($roomTypeId, $filterRoomIds)) {
				fillInPriceAndAvailability($arriveDateTs, $nights, $roomData, $roomTypesData[$roomTypeId], $currency);
			}
		}
	}

	$retVal['rooms'] = array();
	foreach($roomTypesData as $roomTypeId => $roomType) {
		if(is_null($filterRoomIds) or in_array($roomTypeId, $filterRoomIds)) {
			matchSpecialOffer($roomType, $roomTypeId, $nights, $arriveDate, $specialOffers, $link);
			$retVal['rooms'][] = $roomType;
		}
	}

	usort($retVal['rooms'], 'sortAvailabilityRooms');
	
	mysql_close($link);
	return $retVal;
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
	if(isDorm($room1) and !isDorm($room2)) {
		return -1;
	}
	if(isDorm($room2) and !isDorm($room1)) {
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

function isAvailable($roomAvailability) {
	if(isDorm($roomAvailability) and $roomAvailability['num_of_beds_avail'] > 0) {
		return true;
	}
	if(!isDorm($roomAvailability) and $roomAvailability['num_of_rooms_avail'] > 0) {
		return true;
	}
}

function loadRoomImages($lang, $link) {
	logDebug("Loading room images");
	$roomImages = array();
	$imgCnt = 0;
	foreach(RoomDao::getRoomImages($lang, $link) as $imgId => $row) {
		if(!isset($roomImages[$row['room_type_id']])) {
			$roomImages[$row['room_type_id']] = array();
		}
		$row['original_img_url'] = ROOMS_IMG_URL . $row['filename'];
		$row['medium_img_url'] = ROOMS_IMG_URL . (is_null($row['medium']) ? $row['filename'] : $row['medium']);
		$row['thumb_img_url'] = ROOMS_IMG_URL . (is_null($row['thumb']) ? $row['filename'] : $row['thumb']);
		if(isset($row['description'][$lang])) {
			$row['description'] = $row['description'][$lang];
		} else {
			$row['description'] = '';
		}
		$roomImages[$row['room_type_id']][] = $row;
		$imgCnt += 1;
	}
	logDebug("There are $imgCnt room images loaded for " . count($roomImages) . " room types");
	return $roomImages;
}


function fillInPriceAndAvailability($arriveTS, $nights, &$roomData, &$roomType, $currency) {
	$oneDayTS = $arriveTS;
	$type = $roomData['type'];
	$minAvailBeds = $roomType['num_of_beds'];
	$totalPrice = 0;
	logDebug("Filling in price and availability for room type: " . $roomType['name']);
	for($i = 0; $i < $nights; $i++) {
		$currYear = date('Y', $oneDayTS);
		$currMonth = date('m', $oneDayTS);
		$currDay = date('d', $oneDayTS);
		$oneDay =  date('Y/m/d', $oneDayTS);
		$availBeds = getNumOfAvailBeds($roomData, $oneDay);
		// echo "For room:  " . $roomData['name'] . " (room type: " . $roomData['room_type_id'] . ") for day: $oneDay, there are $availBeds available beds<br>\n";
		$minAvailBeds = min($minAvailBeds, $availBeds);
		$oneDayTS = strtotime(date('Y-m-d',$oneDayTS) . ' +1 day');

	}
	if((isPrivate($roomData) or isApartment($roomData)) and $minAvailBeds < $roomData['num_of_beds']) {
		$minAvailBeds = 0;
	}

	if(!isset($roomType['num_of_beds_avail'])) {
		$roomType['num_of_beds_avail'] = 0;
	}
	if(!isset($roomType['num_of_rooms_avail'])) {
		$roomType['num_of_rooms_avail'] = 0;
	}
	if(!isset($roomType['rooms_providing_availability'])) {
		$roomType['rooms_providing_availability'] = '';
	}
	if($minAvailBeds == $roomData['num_of_beds']) {
		$roomType['num_of_rooms_avail'] += 1;
		$roomType['rooms_providing_availability'] .= $roomData['id'] . ',';
	} elseif($minAvailBeds > 0 and isDorm($roomType)) {
		$roomType['rooms_providing_availability'] .= $roomData['id'] . ',';
	}

	$roomType['num_of_beds_avail'] += $minAvailBeds;
	$roomType['price'] = (convertAmount(getPrice($arriveTS, $nights, $roomData, 1),'EUR',$currency, date('Y-m-d')) / $nights);
	if(isApartment($roomType)) {
		for($i=2; $i<= $roomType['num_of_beds']; $i++) {
			$roomType['price_' . $i] = (convertAmount(getPrice($arriveTS, $nights, $roomData, $i),'EUR',$currency, date('Y-m-d')) / $nights);
		}
	}
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

function matchSpecialOffer(&$roomType, $roomTypeId, $nights, $arriveDate, $specialOffers, $link) {
	$so = null;
	$specialOfferForOneMoreDay = null;
	list($discount, $so) = findSpecialOffer($specialOffers, $roomType, $nights, $arriveDate, $roomType['num_of_beds']);
	list($discountPlus1, $specialOfferForOneMoreDay) = findSpecialOffer($specialOffers, $roomType, $nights+1, $arriveDate, $roomType['num_of_beds']);
	$roomType['special_offer'] = $so;
	$roomType['special_offer_for_one_more_day'] = $specialOfferForOneMoreDay;
}

function getMinMaxStay($fromDate, $toDate, $link) {
	$sql = "SELECT * FROM min_max_stay WHERE (from_date IS NULL OR from_date<='$fromDate') AND (to_date IS NULL OR to_date>='$fromDate')";
	$result = mysql_query($sql, $link);
	if(mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		return $row;
	}
	return null;
}

function checkMissingParameters($paramNames) {
	foreach($paramNames as $oneParamName) {
		if(!isset($_REQUEST[$oneParamName])) {
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
	if(!in_array($_REQUEST[$parameterName], $possibleValues)) {
		echo "$parameterName parameter is invalid. Valid values: " . implode(',', $possibleValues);
		return false;
	}
	return true;
}


function loadServices() {
	if(!checkMissingParameters(array('location','lang','currency'))) {
		return null;
	}

	logDebug("Loading services");
	$location = $_REQUEST['location'];
	$lang = $_REQUEST['lang'];
	$currency = $_REQUEST['currency'];	

	$link = db_connect($location);
	
	$services = loadServicesFromDB($lang, $link);
	$today = date('Y-m-d');
	
	foreach($services as &$oneService) {
		$oneService['price'] = convertAmount($oneService['price'], $oneService['currency'], $currency, $today);
		$oneService['currency'] = $currency;
	}

	logDebug("Services loaded");
	mysql_close($link);
	return $services;
}



function doBooking() {
	if(!checkMissingParameters(array('firstname','lastname','email','phone','nationality','street','city','zip','country','currency','comment','booking_data','from_date','to_date'))) {
		return null;
	}

	$location = $_REQUEST['location'];
	$lang = $_REQUEST['lang'];
	$currency = $_REQUEST['currency'];

	$link = db_connect($location);
	
	
	$firstname = $_REQUEST['firstname'];
	$lastname = $_REQUEST['lastname'];

	if(trim($firstname)=='1' or trim($lastname)=='1') {
		return null;
	}
	
	$address = mysql_real_escape_string($_REQUEST['street'] . ', ' . $_REQUEST['city'] . ', ' . $_REQUEST['zip'] . ', ' . $_REQUEST['country'], $link);
	$name = mysql_real_escape_string("$firstname $lastname", $link);
	$nationality = mysql_real_escape_string($_REQUEST['nationality'], $link);
	$email = mysql_real_escape_string($_REQUEST['email'], $link);
	$phone = mysql_real_escape_string($_REQUEST['phone'], $link);
	$comment = mysql_real_escape_string($_REQUEST['comment'], $link);
	$bookingRef = mysql_real_escape_string(gen_booking_ref(), $link);

	verifyBlacklist("$firstname $lastname", $email, CONTACT_EMAIL, $link);
	
	$arriveDate = $_REQUEST['from_date'];
	$arriveDateTs = strtotime($_REQUEST['from_date']);
	$departureDate = $_REQUEST['to_date'];
	$departureDateTs = strtotime($_REQUEST['to_date']);
	$nights = round(($departureDateTs - $arriveDateTs) / (60*60*24));
	$lastNightTs = $arriveDateTs + ($nights-1) * (60*60*24) - ($nights>1?60*60:0); // minus 1 hour because when the daylight saving is turned off then one day is only 23 hours so if we add 24 that would be an extra day.
	$lastNight = date('Y-m-d', $lastNightTs);

	logDebug("arrive date: $arriveDate, departure date: $departureDate, last night: $lastNight, num of nights: $nights");
	$link = db_connect($location);
	mysql_query('START TRANSACTION', $link);
	
	$specialOffers = loadSpecialOffers($arriveDate,$lastNight, $link);
	logDebug("There are " . count($specialOffers) . " special offers valid between $arriveDate and $lastNight");
	$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
	$roomTypesData = loadRoomTypes($link, $lang);
	$services = loadServicesFromDB($lang, $link);

	$bookingRequest = json_decode($_REQUEST['booking_data'], true);

	$sql = "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time, language, currency,booking_ref) VALUES ('$name', NULL, '$address', '$nationality', '$email', '$phone', '" . str_replace("-", "/", $arriveDate) . "', '" . str_replace("-", "/", $lastNight) . "', $nights, 0, 0, 0, 0, '$comment', 'saját', '', '$lang', '$currency', '$bookingRef')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save booking: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		mysql_query('ROLLBACK', $link);
		mysql_close($link);
		return array('error' => 'Could not save booking description.');
	}
	$descriptionId = mysql_insert_id($link);

	list($toBook, $roomChanges) = getBookingData($bookingRequest, $arriveDate, $lastNight, $rooms, $roomTypesData, $specialOffers);
	$bookingIds = saveBookings($toBook, $roomChanges, $arriveDate, $lastNight, $rooms, $roomTypesData, $specialOffers, $descriptionId, $link);
	$bookedServices = json_decode($_REQUEST['services'], true);
	logDebug("Services to book: " . print_r($bookedServices, true));
	foreach($bookedServices as $service) {
		$id = $service['serviceId'];
		if(!isset($services[$id])) {
			logError("The booking contains a service with id: $id that is not in the DB. Ignoring.");
			continue;
		}
		$title = $services[$id]['name'];
		$comment = $service['comment'];
		$occasion =  $service['occasion'];
		if($occasion < 1) {
			logError("The service: $title [$id] has occasion: $occasion. It has to be greater than 0. Ignoring.");
		}
		$price =  $services[$id]['price'] * $occasion;
		$serviceCurrency = $services[$id]['currency'];
		$now = date('Y-m-d H:i:s');
		$type = $services[$id]['service_charge_type'];
		$comment = mysql_real_escape_string("$title for $occasion occasions. $comment", $link);
		$sql = "INSERT INTO service_charges (booking_description_id, amount, currency, time_of_service, comment, type) VALUES ($descriptionId, $price, '$serviceCurrency', '$now', '$comment', '$type')";
		if(!mysql_query($sql, $link)) {
			trigger_error("Cannot save service charge: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			mysql_query('ROLLBACK', $link);
			mysql_close($link);
			return array('error' => 'Could not save the selected services.');
		}
	}

	$_SESSION['login_user'] = 'website';
	audit(AUDIT_CREATE_BOOKING, array('booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $descriptionId, $link);
	mysql_query('COMMIT', $link);
	mysql_close($link);

	sendEmailForBooking($name, $email, $phone, $address, $nationality, $arriveDate, $departureDate, $nights, $toBook, $bookedServices, $roomTypesData, $services);
	return array('success'=> true);
}

function sendEmailForBooking($nameValue, $emailValue, $phoneValue, $addressValue, $nationalityValue, $dateOfArriveValue, $dateOfDepartureValue, $numberOfNightsValue, $bookings, $bookedServices, &$roomTypesData, &$services) {
	$texts = loadWebsiteTexts();
	$location = $_REQUEST['location'];
	$lang = $_REQUEST['lang'];
	$currency = $_REQUEST['currency'];
	$today = date('Y-m-d');
	
	$nameTitle = $texts['NAME'];
	$emailTitle = $texts['EMAIL'];
	$addressTitle = $texts['ADDRESS_TITLE'];
	$nationalityTitle = $texts['NATIONALITY'];
	$dateOfArriveTitle = $texts['DATE_OF_ARRIVAL'];
	$dateOfDepartureTitle = $texts['DATE_OF_DEPARTURE'];
	$numberOfNightsTitle = $texts['NUMBER_OF_NIGHTS'];
	$roomsTitle = $texts['ROOMS'];
	$extraServicesTitle = $texts['EXTRA_SERVICES'];
	$totalPrice = $texts['TOTAL_PRICE'];
	$adviseToTravel = $texts['ADVISE_TO_TRAVEL'];
	$fromTrainStation = $texts['RAILWAY_STATIONS'];
	$fromTrainStationInstructions = $texts['RAILWAY_STATIONS_TO_' . strtoupper($location)];
	$fromAirport = $texts['FROM_AIRPORT'];
	$fromAirportInstructions = $texts['AIRPORT_TO_' . strtoupper($location)];
	$fromAirportInstructions2 = $texts['AIRPORT_TO_' . strtoupper($location) . '_2'];
	$important = $texts['IMPORTANT'];
	$importantNotice = $texts['IMPORTANT_NOTICE_WHEN_ARRIVE_' . strtoupper($location)];
	$importantHtml = '';
	if(strlen($importantNotice) > 0) {
		$importantHtml = <<<EOT
                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 25px; line-height: 1.2;">
                      $important
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="20"></td></tr>
                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 14px;">
                      $importantNotice
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="35"></td></tr>

EOT;
	}
	$payment = $texts['PAYMENT'];
	$paymentDescription = $texts['PAYMENT_DESCRIPTION'];
	$actualExchangeRate = $texts['ACTUAL_EXCHANGE_RATE'];
	$policy = $texts['POLICY'];
	$belowFindBookingInfo = $texts['BELOW_FIND_BOOKING_INFO'];
	$mailMessage = <<<EOT
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
  
</head>
<body>
  <table width="100%" cellspacing="0" border="0" cellpadding="0" bgcolor="#ffffff">
    <tr>
      <td align="center">
        <table width="600" cellspacing="0" border="0" cellpadding="0">
          <tr>
            <td width="40" bgcolor="#1d0328"></td>
            <td width="520" height="120" bgcolor="#1d0328" valign="middle">
              <img width="130" height="64" src="cid:logo" style="display: block;">
            </td>
            <td width="40" bgcolor="#1d0328"></td>
          </tr>
          <tr>
            <td colspan="3" height="10" bgcolor="#f7fac1"></td>
          </tr>
          <tr>
            <td></td>
            <td>
              <table width="100%" cellspacing="0" border="0" cellpadding="0">
                <!-- space --><tr><td height="35"></td></tr>
                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 25px; line-height: 1.2;">
                      $belowFindBookingInfo
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="25"></td></tr>
                <tr>
                  <td>
                    <table width="100%" cellspacing="0" border="0" cellpadding="0">
                      <tr>
                        <td width="15"></td>
                        <td width="590">
                          <table width="100%" cellspacing="0" border="0" cellpadding="0">

EOT;
	$mailMessage .= getEmailRow("$nameTitle:", $nameValue);
	$mailMessage .= getEmailRow("$emailTitle:", $emailValue);
	$mailMessage .= getEmailRow("$addressTitle:", $addressValue);
	$mailMessage .= getEmailRow("$nationalityTitle:", $nationalityValue);
	$mailMessage .= getEmailRow("$dateOfArriveTitle:", $dateOfArriveValue);
	$mailMessage .= getEmailRow("$dateOfDepartureTitle:", $dateOfDepartureValue);
	$mailMessage .= getEmailRow("$numberOfNightsTitle:", $numberOfNightsValue);

	$mailMessage .= <<<EOT
                            <tr>
                              <td valign="top"><font face="arial" color="#252525" style="font-size: 14px;">$roomsTitle:</font></td>
							  <td colspan="2">&nbsp;</td>
                            </tr>

EOT;

	$total = 0;
	$dtotal = 0;

	foreach($bookings as $roomId => $oneRoomBooked) {
		$roomTypeId = $oneRoomBooked['room_type_id'];
		$roomType = $roomTypesData[$roomTypeId];
		$type = $roomType['type'] == 'DORM' ? $texts['BED'] : $texts['ROOM'];
		$name = $roomType['name'];
//		if(isClientFromHU() and $roomType['num_of_beds'] > 4) {
//			$name = str_replace('5', '4', $name);
//		}
		$numOfGuests = $oneRoomBooked['num_of_person'];
		$numNightsForNumPerson = sprintf($texts['NUM_NIGHTS_FOR_NUM_PERSON'], $numberOfNightsValue, $numOfGuests);
		$roomData = getRoomData($rooms, $roomTypeId);
		$price = convertAmount($oneRoomBooked['price'], 'EUR', $currency, $today);
		$dprice = convertAmount($oneRoomBooked['discounted_price'], 'EUR', $currency, $today);
		$dtotal += $dprice;
		$total += $price;
		if($price != $dprice) {
			$pctOff = sprintf($texts['PERCENT_OFF'], (100 - $dprice/($price/100)));
			$price = "<span style=\"text-decoration:line-through\">" . formatMoney($price, $currency) . "</span> " . $pctOff . " " . formatMoney($dprice, $currency);
		} else {
			$price = formatMoney($dprice, $currency);
		}
		$mailMessage .= <<<EOT

                            <tr>
                              <td valign="top"><font face="arial" color="#252525" style="font-size: 14px;">&nbsp;</font></td>
							  <td>
                                <font face="arial" color="#252525" style="font-size: 14px;">
                                  <b>
                                    $name [$type]<br>
                                    $numNightsForNumPerson
                                  </b>
                                </font>
							  </td>
							  <td style="text-align:right">
                                $price
                              </td>
                            </tr>

EOT;
	}
	if(count($bookedServices) > 0) {
		$mailMessage .= <<<EOT
							<!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td valign="top"><font face="arial" color="#252525" style="font-size: 14px;">$extraServicesTitle:</font></td>
							  <td colspan="2">&nbsp;</td>
                            </tr>

EOT;
	}

	$totalServicePrice = 0;
	foreach($bookedServices as $service) {
		$id = $service['serviceId'];
		if(!isset($services[$id])) {
			logError("The booking contains a service with id: $id tha tis not in the DB. Ignoring.");
		}		
		$title = $services[$service['id']]['title'];
		$forNumOfOccasion = sprintf($texts['FOR_NUM_OF_OCCASIONS'], $service['occasion']);
		$serviceCurrency = $services[$service['id']]['currency'];
		$price = convertAmount($services[$service['id']]['price'], $serviceCurrency, 'EUR', $today);
		$totalServicePrice += $price;
		$price = formatMoney(convertAmount($price, 'EUR', $currency, $today), $currency);
		$mailMessage .= <<<EOT

                            <tr>
                              <td valign="top"><font face="arial" color="#252525" style="font-size: 14px;">&nbsp;</font></td>
							  <td>
                                <font face="arial" color="#252525" style="font-size: 14px;">
                                  <b>
                                    $title<br>
                                    $forNumOfOccasion
                                  </b>
                                </font>
							  </td>
							  <td style="text-align:right">
                                $price
                              </td>
                            </tr>

EOT;
	}

	$totalServicePrice = convertAmount($totalServicePrice, 'EUR', $currency, $today);
	$total += $totalServicePrice;
	$dtotal += $totalServicePrice;
	if($total != $dtotal) {
		//$pctOff = sprintf(PERCENT_OFF, ($dtotal/($total/100)));
		//$total = formatMoney($dtotal, $currency) . " <span style=\"text-decoration:line-through\">" . formatMoney($total, $currency) . "</span> " . $pctOff;
		$total = formatMoney($dtotal, $currency);
	} else {
		$total = formatMoney($total, $currency);
	}
	$mailMessage .= <<<EOT


                          </table>
                        </td>
                        <td width="15"></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td bgcolor="#f7fac1">
                    <table width="100%" cellspacing="0" border="0" cellpadding="0">
                      <tr><td colspan="3" height="1" bgcolor="#959595"></td></tr>
                      <tr>
                        <td width="15" height="50"></td>
                        <td width="160">
                          <font face="arial" color="#252525" style="font-size: 14px;">
                            $totalPrice:
                          </font>
                        </td>
                        <td>
                          <font face="arial" color="#252525" style="font-size: 23px;">
                            $total
                          </font>
                        </td>
                      </tr>
                      <tr><td colspan="3" height="4" bgcolor="#959595"></td></tr>
                    </table>
                  </td>
                </tr>
                <!-- space --><tr><td height="70"></td></tr>

                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 25px; line-height: 1.2;">
                      $adviseToTravel
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="20"></td></tr>
              </table>
            </td>
            <td></td>
          </tr>
          <tr>
            <td colspan="3">
              <img width="600" height="317" src="cid:map" style="display: block;">
            </td>
          </tr>
          <tr>
            <td></td>
            <td>
              <table width="100%" cellspacing="0" border="0" cellpadding="0">
                <!-- space --><tr><td height="20"></td></tr>
                <tr>
                  <td>
                    <img width="49" height="49" src="cid:railwaystation">
                  </td>
                </tr>
                <!-- space --><tr><td height="10"></td></tr>
                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 14px;">
                      <b>$fromTrainStation</b>
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="10"></td></tr>
                <tr>
                  <td>
                    <table width="100%" cellspacing="0" border="0" cellpadding="0">
                      <tr>
                        <td width="15"></td>
                        <td width="6" bgcolor="#959595"></td>
                        <td width="10"></td>
                        <td>
                          <font face="arial" color="#252525" style="font-size: 14px;">
                            $fromTrainStationInstructions
                          </font>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <!-- space --><tr><td height="20"></td></tr>
                <tr>
                  <td>
                    <img width="54" height="55" src="cid:airport">
                  </td>
                </tr>
                <!-- space --><tr><td height="10"></td></tr>
                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 14px;">
                      <b>$fromAirport</b>
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="10"></td></tr>
                <tr>
                  <td>
                    <table width="100%" cellspacing="0" border="0" cellpadding="0">
                      <tr>
                        <td width="15"></td>
                        <td width="6" bgcolor="#959595"></td>
                        <td width="10"></td>
                        <td>
						  <font face="arial" color="#252525" style="font-size: 14px;">
                            $fromAirportInstructions
                          </font>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <!-- space --><tr><td height="20"></td></tr>
                <tr>
                  <td>
                    <table width="100%" cellspacing="0" border="0" cellpadding="0">
                      <tr>
                        <td width="15"></td>
                        <td width="6" bgcolor="#959595"></td>
                        <td width="10"></td>
                        <td>
                          <font face="arial" color="#252525" style="font-size: 14px;">
                            $fromAirportInstructions2
                          </font>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <!-- space --><tr><td height="35"></td></tr>
$importantHtml
                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 25px; line-height: 1.2;">
                      $payment
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="20"></td></tr>
                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 14px;">
                      $paymentDescription <br>
                      $actualExchangeRate:
                      <a href="http://www.cib.hu/maganszemelyek/arfolyamok/arfolyamok">
                        <font color="#101010">
                          http://www.cib.hu/maganszemelyek/arfolyamok/arfolyamok
                        </font>
                      </a>
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="35"></td></tr>
                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 25px; line-height: 1.2;">
                      $policy
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="20"></td></tr>
                <tr>
                  <td>
                    <table width="100%" cellspacing="0" border="0" cellpadding="0">

EOT;
	$idx = 1;
	while(isset($texts['POLICY_' . strtoupper($location) . '_' . $idx])) {
		$policyIdx = $texts['POLICY_' . strtoupper($location) . '_' . $idx];
		$mailMessage .= <<<EOT
                      <tr>
                        <td width="15" valign="top"><img width="5" height="17" src="cid:bullet"></td>
                        <td>
                          <font face="arial" color="#252525" style="font-size: 14px; line-height: 1.2;">
                            $policyIdx
                          </font>
                        </td>
                      </tr>
                      <!-- space --><tr><td height="10"></td></tr>

EOT;
		$idx += 1;
	}
	$mailMessage .= <<<EOT
                    </table>
                  </td>
                </tr>
                <!-- space --><tr><td height="35"></td></tr>
              </table>
            </td>
            <td></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>

EOT;

	$inlineAttachments = array(	
		'logo' => EMAIL_IMG_DIR . 'logo-' . $location . '.jpg',
		'airport' => EMAIL_IMG_DIR . 'airport.jpg',
		'bullet' => EMAIL_IMG_DIR . 'bullet.jpg',
		'map' => EMAIL_IMG_DIR . 'map-' . $location . '.jpg',
		'railwaystation' => EMAIL_IMG_DIR . 'railwaystation.jpg'
	);

	$locationName = $texts['LOCATION_NAME'];
	$subject = str_replace('LOCATION', $locationName, $texts['BOOKING_CONFIRMATION_EMAIL_SUBJECT']);
	$result = sendMail('reservation@mavericklodges.com', $locationName, $emailValue, "$nameValue", $subject, $mailMessage, $inlineAttachments);
	if(!is_null($result)) {
		logError("Cannot send email: $result");
	}

	$editBookingUrl = "http://recepcio.roomcaptain.com/edit_booking.php?description_id=$descriptionId";
	$recepcioMessage = <<<EOT
Booking arrived (<a href="$editBookingUrl">edit</a>)<br>

<table>	
<tr><td>Name: </td><td>$nameValue</td></tr>
<tr><td>Emai: </td><td>$emailValue</td></tr>
<tr><td>Phone: </td><td>$phoneValue</td></tr>
<tr><td>Nationality: </td><td>$nationalityValue</td></tr>
<tr><td>Address: </td><td>$addressValue</td></tr>
<tr><td>Arrival: </td><td>$dateOfArriveValue</td></tr>
<tr><td>Departure: </td><td>$dateOfDepartureValue</td></tr>
<tr><td>Num of nights: </td><td>$numberOfNightsValue</td></tr>
</table>

Rooms:
<table cellpadding="10" cellspacing="5">
<tr><th>Name</th><th>Type</th><th>Number of guests</th><th>Price</th></tr>

EOT;
	$total = 0;
	foreach($bookings as $roomId => $oneRoomBooked) {
		$roomTypeId = $oneRoomBooked['roomTypeId'];
		$roomType = $roomTypesData[$roomTypeId];
		$type = $roomType['type'] == 'DORM' ? "Bed" : "Room";
		$name = $roomType['name'];
		$numOfGuests = $oneRoomBooked['num_of_person'];
		$price = $oneRoomBooked['price'];
		$dprice = $oneRoomBooked['discounted_price'];
		$total += $dprice;
		if($price != $dprice) {
			$price = "<span style=\"text-decoration:line-through\">" . formatMoney($price, 'EUR') . "</span> " . formatMoney($dprice, 'EUR');
		} else {
			$price = formatMoney($price, 'EUR');
		}
		$recepcioMessage .= "<tr><td>$name</td><td>$type</td><td>$numOfGuests</td><td>$price</td></tr>\n";
	}
	$recepcioMessage .= "</table><br>\n";
	if(count($bookedServices) > 0) {
		$recepcioMessage .= "Services:<br><table>\n";
		$recepcioMessage .= "<tr><th>Name</th><th>Occasions</th><th>Price(total)</th></tr>\n";
	}
	foreach($bookedServices as $service) {
		$id = $service['serviceId'];
		if(!isset($services[$id])) {
			logError("The booking contains a service with id: $id tha tis not in the DB. Ignoring.");
		}		
		$title = $services[$service['id']]['title'];
		$occasion = $service['occasion'];
		$serviceCurrency = $services[$service['id']]['currency'];
		$price = convertAmount($services[$service['id']]['price'], $serviceCurrency, 'EUR', date('Y-m-d'));
		$total += $price;
		$price = formatMoney($price, 'EUR');
		$recepcioMessage .= "<tr><td>$title</td><td>$occasion</td><td>$price</td></tr>\n";
	}
	$recepcioMessage .= "</table><br>\n";
	$recepcioMessage .= "Total: $total euro<br>\n";

	$result = sendMail(CONTACT_EMAIL, $locationName, CONTACT_EMAIL, $locationName, "Booking arrived from website", $recepcioMessage);
	if(!is_null($result)) {
		logError("Cannot send email: $result");
	}

}

function getEmailRow($title, $value) {
	$retVal = <<<EOT
                            <tr>
                              <td width="160"><font face="arial" color="#252525" style="font-size: 14px;">$title</font></td>
                              <td colspan="2" width="430"><font face="arial" color="#252525" style="font-size: 14px;">$value</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>

EOT;
	return $retVal;
}

// Loads the dictionary for the website
function loadWebsiteTexts() {
	if(!checkMissingParameters(array('location','lang'))) {
		return null;
	}
	$location = $_REQUEST['location'];
	$lang = $_REQUEST['lang'];
	$link = db_connect($location);

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
	$location = $_REQUEST['location'];
	$lang = $_REQUEST['lang'];
	$startDate = $_REQUEST['from'];
	$endDate = $_REQUEST['to'];
	$startTs = strtotime($startDate);
	$endTs = strtotime($endDate);

	if($endTs < $startTs) {
		return array('error' => 'CHECKOUT_DATE_MUST_BE_AFTER_CHECKIN_DATE');
	}

	$link = db_connect($location);

	$roomTypeId = $_REQUEST['room_type_id'];

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
	$sql = "SELECT s.id,s.price,s.currency,s.img, s.free_service, t.value AS title, d.value AS description , u.value AS unit_name FROM services s INNER JOIN lang_text t ON (s.id=t.row_id AND t.table_name='services' AND t.column_name='title' AND t.lang='$lang') INNER JOIN lang_text d ON (s.id=d.row_id AND d.table_name='services' AND d.column_name='description' AND d.lang='$lang') LEFT OUTER JOIN lang_text u ON (s.id=u.row_id AND u.table_name='services' AND u.column_name='unit_name' AND u.lang='$lang') ORDER BY s._order";
	$result = mysql_query($sql, $link);
	while($row = mysql_fetch_assoc($result)) {
		if(strlen($row['img']) > 0) {
			$row['img'] = SERVICES_IMG_URL . $row['img'];
		}
		$services[$row['id']] = $row;
	}

	return $services;
}



?>