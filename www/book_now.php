<?php

require('includes.php');
require('includes/common_booking.php');
require('../recepcio/room_booking.php');

$location = getLocation();
$lang = getCurrentLanguage();
$currency = getCurrency();

$firstname = $_REQUEST['data_firstname'];
$lastname = $_REQUEST['data_last_name'];
$email = $_REQUEST['data_email'];
$email2 = $_REQUEST['data_email2'];
$countryCode = $_REQUEST['data_countrycode'];
$phone = $_REQUEST['data_phone'];
$nationality = $_REQUEST['data_nationality'];
$street = $_REQUEST['data_street'];
$city = $_REQUEST['data_city'];
$zip = $_REQUEST['data_zip'];
$country = $_REQUEST['data_country'];
$comment = $_REQUEST['data_comment'];

$_SESSION['firstname'] = $_REQUEST['data_firstname'];
$_SESSION['lastname'] = $_REQUEST['data_last_name'];
$_SESSION['email'] = $_REQUEST['data_email'];
$_SESSION['email2'] = $_REQUEST['data_email2'];
$_SESSION['countryCode'] = $_REQUEST['data_countrycode'];
$_SESSION['phone'] = $_REQUEST['data_phone'];
$_SESSION['nationality'] = $_REQUEST['data_nationality'];
$_SESSION['street'] = $_REQUEST['data_street'];
$_SESSION['city'] = $_REQUEST['data_city'];
$_SESSION['zip'] = $_REQUEST['data_zip'];
$_SESSION['country'] = $_REQUEST['data_country'];
$_SESSION['comment'] = $_REQUEST['data_comment'];


$error = false;
if(strlen(trim($firstname)) < 1) {
	$_SESSION['firstnameError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($lastname)) < 1) {
	$_SESSION['lastnameError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($email)) < 1) {
	$_SESSION['emailError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($email2)) < 1) {
	$_SESSION['confirmEmailError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if($email != $email2) {
	$_SESSION['confirmEmailError'] = EMAIL_MISMATCH;
	$error = true;
}

if((strlen(trim($countryCode)) < 1) or ($countryCode == PLEASE_SELECT)) {
	$_SESSION['countryCodeError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($phone)) < 1) {
	$_SESSION['dataPhoneError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if((strlen(trim($nationality)) < 1) or ($nationality == PLEASE_SELECT)) {
	$_SESSION['nationalityError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($city)) < 1) {
	$_SESSION['cityError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($street)) < 1) {
	$_SESSION['streetError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($zip)) < 1) {
	$_SESSION['zipcodeError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if((strlen(trim($country)) < 1) or ($country == PLEASE_SELECT)) {
	$_SESSION['countryError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}

if($error) {
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}


$arriveDate = $_SESSION['from_date'];
$arriveDateTs = strtotime($_SESSION['from_date']);
$nights = $_SESSION['nights'];
$lastNightTs = strtotime($_SESSION['from_date'] . " +" . ($nights-1) . " day");
$lastNight = date('Y-m-d', $lastNightTs);
$departureDateTs = strtotime($_SESSION['from_date'] . " +" . $nights . " day");
$departureDate = date('Y-m-d', $departureDateTs);


$link = db_connect($location);

$specialOffers = loadSpecialOffers("start_date<='$arriveDate' AND end_date>='$lastNight'", $link);
$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
$roomTypesData = loadRoomTypes($link, $lang);


$numOfPersonForRoomType = array();
foreach($roomTypesData as $roomTypeId => $roomType) {
	$key = 'room_type_' . $location . '_' . $roomTypeId;
	if(isset($_SESSION[$key])) {
		$numOfPersonForRoomType[$roomTypeId] = $_SESSION[$key];
	}
	/*if(isApartment($roomType)) {
		for($i = 2; $i <= $roomType['num_of_beds']; $i++) {
			$key = 'room_type_' . $location . '_' . $roomTypeId . '_' . $i;
			if(isset($_SESSION[$key])) {
				$numOfPersonForRoomType[$roomTypeId . '_' . $i] = $_SESSION[$key];
			}
		}
	}*/
}


$address = $street . ', ' . $city . ', ' . $zip . ', ' . $country;
$sql = "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time, language, currency) VALUES ('$firstname $lastname', NULL, '$address', '$nationality', '$email', '$phone', '" . str_replace("-", "/", $arriveDate) . "', '" . str_replace("-", "/", $lastNight) . "', $nights, 0, 0, 0, 0, '$comment', 'saját', '', '$lang', '$currency')";
set_debug($sql);

if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save booking: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Could not save booking description.');
	header('Location: contact_details.php');
	mysql_close($link);
	return;
}
$descriptionId = mysql_insert_id($link);

$bookings = getBookingsWithDiscount($location, $arriveDateTs, $nights, $roomTypesData, $rooms, $specialOffers);

list($toBook, $roomChanges) = getBookingData($numOfPersonForRoomType, $arriveDate, $lastNight, $rooms, $roomTypesData);

$bookingIds = saveBookings($toBook, $roomChanges, $arriveDate, $lastNight, $rooms, $roomTypesData, $specialOffers, $descriptionId, $link);

$services = loadServices($link);
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
		set_error('Could not save service.');
		header('Location: contact_details.php');
		mysql_close($link);
		return;
	}
}



audit(AUDIT_CREATE_BOOKING, array('booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $descriptionId, $link);

foreach($_SESSION as $key => $value) {
	if('room_type_' == substr($key, 0, 10)) {
		unset($_SESSION[$key]);
	}
}



mysql_close($link);

$thankYou = THANK_YOU;
$thankYouForYourBooking = THANK_YOU_FOR_YOUR_BOOKING;
$dontForgetToLikeUs = DONT_FORGET_TO_LIKE_US;
$belowFindBookingInfo = BELOW_FIND_BOOKING_INFO;
$nameTitle = NAME;
$nameValue = $lastname . ', ' . $firstname;
$emailTitle = EMAIL;
$emailValue = $email;
$addressTitle = ADDRESS_TITLE;
$addressValue = $street . ', ' . $city . ', ' . $zip . ', ' . $country;
$nationalityTitle = NATIONALITY;
$nationalityValue = $nationality;
$dateOfArriveTitle = DATE_OF_ARRIVAL;
$dateOfArriveValue = $arriveDate;
$dateOfDepartureTitle = DATE_OF_DEPARTURE;
$dateOfDepartureValue = $departureDate;
$numberOfNightsTitle = NUMBER_OF_NIGHTS;
$numberOfNightsValue = $nights;
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

html_start(THANK_YOU);


$extraHtml = '';
if(isset($_SESSION['booking_source']) and ($_SESSION['booking_source'] == 'WHIP')) {
	$dtotal = 0;
	foreach($bookings as $oneRoomBooked) {
		$dprice = convertCurrency($oneRoomBooked['discountedPrice'], 'EUR', $currency);
		$dtotal += $dprice;
	}
	foreach($bookedServices as $service) {
		$serviceCurrency = $service['currency'];
		$price = convertCurrency($service['price'], $serviceCurrency, 'EUR');
		$price = convertCurrency($price, 'EUR', $currency);
		$dtotal += $price;
	}

	if($location == 'lodge') {
		$whipId = '171414';
	} elseif($location == 'hostel') {
		$whipId = '171415';
	}

	$extraHtml = <<<EOT
<script type="text/javascript" src="https://secure-hotel-tracker.com/tics/log.php?act=conversion&ref=bdid_$descriptionId&amount=$dtotal&currency=$currency&idbe=3&idwihp=$whipId"></script>

EOT;
}

echo <<<EOT

      <h1 class="page-title page-title-booknow">$thankYou</h1>
      
      <div class="fluid-wrapper booking">
        <section id="thank-you" class="clearfix">
          <h1>$thankYouForYourBooking</h1>
          
          <iframe class="likebox" src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FMaverick-Hostel%2F115569091837790&amp;width&amp;height=258&amp;colorscheme=light&amp;show_faces=true&amp;header=false&amp;stream=false&amp;show_border=false"></iframe>
        </section>
      </div>


$extraHtml

<!-- Google Code for &uacute;j honlap - booking - LODGE Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 999565014;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "9j9rCPqC9AcQ1s3Q3AM";
var google_conversion_value = 0;
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/999565014/?value=0&amp;label=9j9rCPqC9AcQ1s3Q3AM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>


EOT;


html_end();

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
$result = sendMail('reservation@mavericklodges.com', $locationName, 
	$email, "$lastname, $firstname", sprintf(BOOKING_CONFIRMATION_EMAIL_SUBJECT, $locationName), $mailMessage, $inlineAttachments);
//set_debug("Send mail response: $result");
//

if($location == 'hostel') {
	$editBookingUrl = "http://recepcio.maverickhostel.com/edit_booking.php?description_id=$descriptionId";
} else {
	$editBookingUrl = "http://recepcio.mavericklodges.com/edit_booking.php?description_id=$descriptionId";
}

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
		$price = "<span style=\"text-decoration:line-through\">$price euro</span> $dprice euro";
	} else {
		$price = "$price euro";
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
	$recepcioMessage .= "<td><td>$title</td><td>$occasion</td><td>$price euro</td></tr>\n";
}
$recepcioMessage .= "</table><br>\n";
$recepcioMessage .= "Total: $total euro<br>\n";


$result = sendMail('reservation@mavericklodges.com', $locationName, 
	constant('CONTACT_EMAIL_' . strtoupper($location)), $locationName, "Booking arrived from website", $recepcioMessage);




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



?>

