<?php

require('includes.php');
require('room_booking.php');

if(!isset($_REQUEST['action'])) {
	echo "'action' parameter missing";
	return;
}
$action = $_REQUEST['action'];

if($action == 'rooms') {
	$retVal = _loadRooms();
} elseif($action == 'availability') {
	$retVal = loadAvailability();
} elseif($action == 'book') {
	$retVal = doBooking();
} elseif($action == 'dictionary') {
	$retVal = loadWebsiteTexts();
} elseif($action == 'room_avalability') {
	$retVal = loadRoomCalendarAvailability();
} else {
	echo "invalid action parameter value";
}

header("Content-Type: application/json");
echo json_encode($retVal);
return;


function _loadRooms() {
	if(!checkMissingParameters(array('location','lang'))) {
		return null;
	}

	logDebug("Loading rooms");
	$location = $_REQUEST['location'];
	$lang = $_REQUEST['lang'];
	$link = db_connect($location);
	
	$roomTypesData = loadRoomTypes($link, $lang);
	loadRoomImages($roomTypesData, $link);

	logDebug("Rooms loaded");
	mysql_close($link);
	return $roomTypesData;
}

function loadAvailability() {
	if(!checkMissingParameters(array('location','lang','from','to'))) {
		return null;
	}

	$location = $_REQUEST['location'];
	$lang = $_REQUEST['lang'];

	$fromDate = $_REQUEST['from'];
	$toDate = $_REQUEST['to'];
	$nights = round((strtotime($toDate) - strtotime($fromDate)) / (60*60*24));

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
	$retVal['special_offers'] = loadSpecialOffers($arriveDate,$lastNight, $link, $lang);

	logDebug("Loading room types");
	$roomTypesData = loadRoomTypes($link, $lang);
	loadRoomImages($roomTypesData, $link);

	logDebug("Loading rooms and their bookings for the selected period");
	$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
	foreach($rooms as $roomId => $roomData) {
		foreach($roomData['room_types']	as $roomTypeId => $roomTypeName) {
			fillInPriceAndAvailability($arriveDateTs, $nights, $roomData, $roomTypesData[$roomTypeId]);
		}
	}

	$retVal['rooms'] = array();
	foreach($roomTypesData as $roomTypeId => $roomType) {
		matchSpecialOffer($roomType, $roomTypeId, $nights, $arriveDate, $specialOffers, $link);
		$retVal['rooms'][] = $roomType;
	}

	mysql_close($link);
	return $retVal;
}

function loadRoomImages(&$roomTypes, $link) {
	logDebug("Loading room images");
	$sql = "SELECT * FROM room_images";
	$result = mysql_query($sql, $link);
	$roomImg = '';
	if(mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_assoc($result)) {
			if(!isset($roomTypes[$row['room_type_id']]['images'])) {
				$roomTypes[$row['room_type_id']]['images'] = array();
			}
			$row['original_img_url'] = ROOMS_IMG_URL . $row['filename'];
			$row['medium_img_url'] = ROOMS_IMG_URL . (is_null($row['medium']) ? $row['filename'] : $row['medium']);
			$row['thumb_img_url'] = ROOMS_IMG_URL . (is_null($row['thumb']) ? $row['filename'] : $row['thumb']);
			$roomTypes[$row['room_type_id']]['images'][] = $row;
		}
	}
}


function fillInPriceAndAvailability($arriveTS, $nights, &$roomData, &$roomType) {
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
	if($minAvailBeds == $roomData['num_of_beds']) {
		$roomType['num_of_rooms_avail'] += 1;
	}

	$roomType['num_of_beds_avail'] += $minAvailBeds;
	$roomType['price'] = (getPrice($arriveTS, $nights, $roomData, 1) / $nights);
	if(isApartment($roomType)) {
		for($i=2; $i<= $roomType['num_of_beds']; $i++) {
			$roomType['price_' . $i] = (getPrice($arriveTS, $nights, $roomData, $i) / $nights);
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
	}
	return true;
}


function doBooking() {
	if(!checkMissingParameters(array('firstname','lastname','email','phone','nationality','street','city','zip','comment','booking_data','from_date','to_date'))) {
		return null;
	}

	$location = getLocation();
	$lang = getCurrentLanguage();
	$currency = getCurrency();

	$firstname = $_REQUEST['firstname'];
	$lastname = $_REQUEST['lastname'];

	if(trim($firstname)=='1' or trim($lastname)=='1') {
		return null;
	}
	
	$address = mysql_real_escape_string($$_REQUEST['street'] . ', ' . $_REQUEST['city'] . ', ' . $_REQUEST['zip'] . ', ' . $_REQUEST['country'], $link);
	$name = mysql_real_escape_string("$firstname $lastname", $link);
	$nationality = mysql_real_escape_string($_REQUEST['nationality'], $link);
	$email = mysql_real_escape_string($_REQUEST['email'], $link);
	$phone = mysql_real_escape_string($_REQUEST['phone'], $link);
	$comment = mysql_real_escape_string($_REQUEST['comment'], $link);
	$bookingRef = mysql_real_escape_string(gen_booking_ref(), $link);

	verifyBlacklist("$firstname $lastname", $email, constant('CONTACT_EMAIL_' . strtoupper($location)), $link);
	
	$arriveDate = $_REQUEST['from_date'];
	$arriveDateTs = strtotime($_REQUEST['from_date']);
	$departureDate = $_REQUEST['to_date'];
	$departureDateTs = strtotime($_REQUEST['to_date']);
	$nights = round(($departureDateTs - $arriveDateTs) / (60*24));
	$lastNightTs = strtotime($arriveDateTs + ($nights -1) * (60*24));
	$lastNight = date('Y-m-d', $lastNightTs);
	
	$link = db_connect($location);
	mysql_query('START TRANSACTION', $link);
	
	$specialOffers = loadSpecialOffers($arriveDate,$lastNight, $link);
	$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
	$roomTypesData = loadRoomTypes($link, $lang);
	$services = loadServices($link);

	$bookingRequest = json_decode($_REQUEST['booking_data']);

	$sql = "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time, language, currency,booking_ref) VALUES ('$name', NULL, '$address', '$nationality', '$email', '$phone', '" . str_replace("-", "/", $arriveDate) . "', '" . str_replace("-", "/", $lastNight) . "', $nights, 0, 0, 0, 0, '$comment', 'saját', '', '$lang', '$currency', '$bookingRef')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save booking: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		mysql_query('ROLLBACK', $link);
		mysql_close($link);
		return array('error' => 'DB_ERROR');
	}
	$descriptionId = mysql_insert_id($link);

	list($toBook, $roomChanges) = getBookingData($bookingRequest, $arriveDate, $lastNight, $rooms, $roomTypesData);
	$bookingIds = saveBookings($toBook, $roomChanges, $arriveDate, $lastNight, $rooms, $roomTypesData, $specialOffers, $descriptionId, $link);
	$bookedServices = getBookedServices($services, $location, 'EUR');
	foreach($bookedServices as $service) {
		$title = $service['title'];
		$occasion =  $service['occasion'];
		$price =  $service['price'];
		$serviceCurrency = $service['currency'];
		$now = date('Y-m-d H:i:s');
		$type = $services[$service['serviceId']]['service_charge_type'];
		$sql = "INSERT INTO service_charges (booking_description_id, amount, currency, time_of_service, comment, type) VALUES ($descriptionId, $price, '$serviceCurrency', '$now', '$title for $occasion occasions', '$type')";
		if(!mysql_query($sql, $link)) {
			trigger_error("Cannot save service charge: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			mysql_query('ROLLBACK', $link);
			mysql_close($link);
			return array('error' => 'DB_ERROR');
		}
	}

	audit(AUDIT_CREATE_BOOKING, array('booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $descriptionId, $link);
	mysql_query('COMMIT', $link);
	mysql_close($link);

	sendEmailForBooking($name, $email, $address, $nationality, $arriveDate, $departureDate, $nights, $bookings, $bookedServices);
}

function sendEmailForBooking($nameValue, $emailValue, $addressValue, $nationalityValue, $dateOfArriveValue, $dateOfDepartureValue, $numberOfNightsValue, $bookings, $bookedServices) {
	$nameTitle = NAME;
	$emailTitle = EMAIL;
	$addressTitle = ADDRESS_TITLE;
	$nationalityTitle = NATIONALITY;
	$dateOfArriveTitle = DATE_OF_ARRIVAL;
	$dateOfDepartureTitle = DATE_OF_DEPARTURE;
	$numberOfNightsTitle = NUMBER_OF_NIGHTS;
	$roomsTitle = ROOMS;
	$extraServicesTitle = EXTRA_SERVICES;
	$totalPrice = TOTAL_PRICE;
	$adviseToTravel = ADVISE_TO_TRAVEL;
	$fromTrainStation = RAILWAY_STATIONS;
	$fromTrainStationInstructions = constant('RAILWAY_STATIONS_TO_' . strtoupper($location));
	$fromAirport = FROM_AIRPORT;
	$fromAirportInstructions = constant('AIRPORT_TO_' . strtoupper($location));
	$fromAirportInstructions2 = constant('AIRPORT_TO_' . strtoupper($location) . '_2');
	$important = IMPORTANT;
	$importantNotice = constant('IMPORTANT_NOTICE_WHEN_ARRIVE_' . strtoupper($location));
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
	$payment = PAYMENT;
	$paymentDescription = PAYMENT_DESCRIPTION;
	$actualExchangeRate = ACTUAL_EXCHANGE_RATE;
	$policy = POLICY;
	$mailMessage = <<<EOT
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
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
	foreach($bookings as $oneRoomBooked) {
		$roomTypeId = $oneRoomBooked['roomTypeId'];
		$roomType = $roomTypesData[$roomTypeId];
		$type = $roomType['type'] == 'DORM' ? BED : ROOM;
		$name = $roomType['name'];
		if(isClientFromHU() and $roomType['num_of_beds'] > 4) {
			$name = str_replace('5', '4', $name);
		}
		$numOfGuests = $oneRoomBooked['numOfGuests'];
		$numNightsForNumPerson = sprintf(NUM_NIGHTS_FOR_NUM_PERSON, $nights, $numOfGuests);
		$roomData = getRoomData($rooms, $roomTypeId);
		$price = convertCurrency($oneRoomBooked['price'], 'EUR', $currency);
		$dprice = convertCurrency($oneRoomBooked['discountedPrice'], 'EUR', $currency);
		$dtotal += $dprice;
		$total += $price;
		if($price != $dprice) {
			$pctOff = sprintf(PERCENT_OFF, (100 - $dprice/($price/100)));
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
		$title = $service['title'];
		$forNumOfOccasion = sprintf(FOR_NUM_OF_OCCASIONS, $service['occasion']);
		$serviceCurrency = $service['currency'];
		$price = convertCurrency($service['price'], $serviceCurrency, 'EUR');
		$totalServicePrice += $price;
		$price = formatMoney(convertCurrency($price, 'EUR', $currency), $currency);
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

	$totalServicePrice = convertCurrency($totalServicePrice, 'EUR', $currency);
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
	while(defined('POLICY_' . strtoupper($location) . '_' . $idx)) {
		$policyIdx = constant('POLICY_' . strtoupper($location) . '_' . $idx);
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

	$locationName = constant('LOCATION_NAME_' . strtoupper($location));
	$subject = str_replace('LOCATION', $locationName, BOOKING_CONFIRMATION_EMAIL_SUBJECT);
	$result = sendMail('reservation@mavericklodges.com', $locationName, $email, "$lastname, $firstname", $subject, $mailMessage, $inlineAttachments);

	$editBookingUrl = "http://recepcio.roomcaptain.com/edit_booking.php?description_id=$descriptionId";
	$recepcioMessage = <<<EOT
Booking arrived (<a href="$editBookingUrl">edit</a>)<br>

<table>	
<tr><td>Name: </td><td>$firstname $lastname</td></tr>
<tr><td>Emai: </td><td>$email</td></tr>
<tr><td>Phone: </td><td>$countryCode $phone</td></tr>
<tr><td>Nationality: </td><td>$nationality</td></tr>
<tr><td>Address: </td><td>$street, $city, $zip, $country</td></tr>
<tr><td>Arrival: </td><td>$dateOfArriveValue</td></tr>
<tr><td>Departure: </td><td>$dateOfDepartureValue</td></tr>
<tr><td>Num of nights: </td><td>$numberOfNightsValue</td></tr>
<tr><td>Comment: </td><td>$comment</td></tr>
</table>

Rooms:
<table cellpadding="10" cellspacing="5">
<tr><th>Name</th><th>Type</th><th>Number of guests</th><th>Price</th></tr>

EOT;
	$total = 0;
	foreach($bookings as $oneRoomBooked) {
		$roomTypeId = $oneRoomBooked['roomTypeId'];
		$roomType = $roomTypesData[$roomTypeId];
		$type = $roomType['type'] == 'DORM' ? "Bed" : "Room";
		$name = $roomType['name'];
		$numOfGuests = $oneRoomBooked['numOfGuests'];
		$price = $oneRoomBooked['price'];
		$dprice = $oneRoomBooked['discountedPrice'];
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
		$title = $service['title'];
		$occasion = $service['occasion'];
		$serviceCurrency = $service['currency'];
		$price = convertCurrency($service['price'], $serviceCurrency, 'EUR');
		$total += $price;
		$price = formatMoney($price, 'EUR');
		$recepcioMessage .= "<tr><td>$title</td><td>$occasion</td><td>$price</td></tr>\n";
	}
	$recepcioMessage .= "</table><br>\n";
	$recepcioMessage .= "Total: $total euro<br>\n";

	$result = sendMail('reservation@mavericklodges.com', $locationName, constant('CONTACT_EMAIL_' . strtoupper($location)), $locationName, "Booking arrived from website", $recepcioMessage);

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
	$link = db_connect($location);

	$startDate = $_REQUEST['from'];
	$endDate = $_REQUEST['to'];
	$startTs = strtotime($startDate);
	$endTs = strtotime($endDate);

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
		foreach($rooms as $roomId => $roomData) {
			if($roomData['room_type_id'] != $roomTypeId) {
				continue;
			}
			$availability += getNumOfAvailBeds($roomData, $currDate);
		}

		$avail[] = array('date' => $currDate, 'numberOfAvailableBeds' => $availability);
	}

	mysql_close($link);
	return $avail;
}


?>