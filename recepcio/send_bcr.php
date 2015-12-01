<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();
$descrId = intval($_REQUEST['description_id']);

$sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
$today=date('Y/m/d');
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get booking description when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo 'Cannot get booking data';
	mysql_close($link);
	return;
}

$row = mysql_fetch_assoc($result);
$bookingDescr = $row;
$name = $row['name'];
$email = $row['email'];
$lang = $row['language'];
if(is_null($lang) or strlen(trim($lang)) < 3) {
	$lang = 'eng';
}
$currency = $row['currency'];
if(is_null($currency) or strlen(trim($currency)) < 3) {
	$currency = 'EUR';
}
$fnight = str_replace('/', '-', $row['first_night']);
$lnight = str_replace('/', '-', $row['last_night']);
$confirmCode = $descrId . 'A' . crypt($email);

require(LANG_DIR . $lang . '.php');

mysql_query('START TRANSACTION', $link);

$today=date('Y/m/d');
$sql = "UPDATE booking_descriptions SET bcr_sent='$today' WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot set BCR sent in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot set BCR sent for booking when sending BCR');
}

$sql = "DELETE FROM bcr WHERE booking_description_id=$descrId";
mysql_query($sql, $link);

$today = date('Y-m-d');
$sql = "INSERT INTO bcr (booking_description_id, mail_sent, email) VALUES ($descrId, '$today', '$email')";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot set BCR sent in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot create BCR record when sending BCR');
}


$confirmBookingUrl = CONFIRM_BOOKING_URL;
$confirmBookingUrl = str_replace('LANG', $lang, $confirmBookingUrl);
$confirmBookingUrl = str_replace('CONFIRM_CODE', urlencode($confirmCode), $confirmBookingUrl);

$total = 0;
$sql = "SELECT b.*, l.value AS room_name FROM bookings b INNER JOIN rooms r ON b.room_id=r.id INNER JOIN lang_text l on (l.table_name='room_types' and l.column_name='name' and l.row_id=r.room_type_id AND l.lang='$lang') WHERE b.description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo 'Cannot get booking data';
	mysql_close($link);
	return;
}

$rooms = '<table cellpadding="5"><tr><th>' . NAME . '</th><th>' . TYPE . '</th><th>' . NUMBER_OF_PERSON . '</th>' . /*'<th>' . PRICE . '</th>' . */'</tr>';
while($row = mysql_fetch_assoc($result)) {
	$payment = intval(convertAmount($row['room_payment'], 'EUR', $currency, substr($row['creation_time'], 0, 10)));
	$rooms .= '<tr><td>' . $row['room_name'] . '</td><td>' . constant(strtoupper($row['booking_type'])) . '</td><td align="center">' . $row['num_of_person'] . '</td>' . /*'<td align="right">' . $payment . $currency . '</td>' . */ '</tr>';
	$total += $payment;
}
$rooms .= '</table>';

$sql = "SELECT sc.*, s.price, s.currency AS svcCurr, l.value AS title FROM service_charges sc INNER JOIN services s ON (sc.type=s.service_charge_type AND s.free_service=0) INNER JOIN lang_text l on (l.table_name='services' and l.column_name='title' and l.row_id=s.id AND l.lang='$lang') WHERE sc.booking_description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo 'Cannot get booking data';
	mysql_close($link);
	return;
}
$hasServices = (mysql_num_rows($result) > 0);
$services = '<table cellpadding="5"><tr><th>' . NAME . '</th><th>' . OCCASION . '</th><th>' . PRICE . '</th></tr>';
while($row = mysql_fetch_assoc($result)) {
	$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_service'], 0, 10)));
	$prc = intval(convertAmount($row['price'], $row['svcCurr'], 'EUR', substr($row['time_of_service'], 0, 10)));
	$occasion = intval($amount / $prc);
	$amount = intval(convertAmount($amount, 'EUR', $currency, substr($row['time_of_service'], 0, 10)));
	$services .= '<tr><td>' . $row['title'] . '</td><td>' . $occasion . '</td><td align="right">' . $amount . $currency . '</td></tr>';
	$total += $amount;
}
$services .= '</table>';


$sql = "SELECT p.* FROM payments p WHERE p.booking_description_id=$descrId AND storno<>1";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get payments for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo 'Cannot get booking data';
	mysql_close($link);
	return;
}
$hasPayments = (mysql_num_rows($result) > 0);
$payments = '<table cellpadding="5"><tr><th>' . NAME . '</th><th>' . PRICE . '</th></tr>';
while($row = mysql_fetch_assoc($result)) {
	$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_payment'], 0, 10)));
	$amount = intval(convertAmount($amount, 'EUR', $currency, substr($row['time_of_payment'], 0, 10)));
	$title = $row['type'];
	if($row['comment'] == '*booking deposit*') {
		$title = DEPOSIT;
	}
	$payments .= '<tr><td>' . $title . '</td><td align="right">' . $amount . $currency . '</td></tr>';
	$total -= $amount;
}
$payments .= '</table>';




$location = LOCATION;
$confirmBookingMsg = str_replace('CONFIRM_URL', $confirmBookingUrl, CONFIRM_BOOKING_MESSAGE);
$checkinTimeInfo = CHECKIN_TIME_INFO;
$belowFindBookingInfo = BELOW_FIND_BOOKING_INFO;
$nameTitle = NAME;
$nameValue = $name;
$emailTitle = EMAIL;
$emailValue = $bookingDescr['email'];
$phoneTitle = PHONE;
$phoneValue = $bookingDescr['telephone'];
$dateOfArriveTitle = DATE_OF_ARRIVAL;
$dateOfArriveValue = $fnight;
$dateOfDepartureTitle = DATE_OF_DEPARTURE;
$nights = $bookingDescr['num_of_nights'];
$dateOfDepartureValue = date('Y-m-d', strtotime($lnight . " +1 day"));
$numberOfNightsTitle = NUMBER_OF_NIGHTS;
$numberOfNightsValue = $nights;
$roomsTitle = ROOMS;
$servicesTitle = EXTRA_SERVICES;
$paymentsTitle = PAYMENT;
$balance = BALANCE;
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
                      $confirmBookingMsg<br>
					  $checkinTimeInfo<br>
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
$mailMessage .= getEmailRow("$phoneTitle:", $phoneValue);
$mailMessage .= getEmailRow("$dateOfArriveTitle:", $dateOfArriveValue);
$mailMessage .= getEmailRow("$dateOfDepartureTitle:", $dateOfDepartureValue);
$mailMessage .= getEmailRow("$numberOfNightsTitle:", $numberOfNightsValue);
$mailMessage .= getEmailRow("$roomsTitle:", $rooms);
if($hasServices) {
	$mailMessage .= getEmailRow("$servicesTitle:", $services);
}
if($hasPayments) {
	$mailMessage .= getEmailRow("$paymentsTitle:", $payments);
}
//$mailMessage .= getEmailRow("$balance:", "$total $currency");
$mailMessage .= <<<EOT

                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                          </table>
                        </td>
                        <td width="15"></td>
                      </tr>
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
$result = sendMail(CONTACT_EMAIL, $locationName, 
	$email, "$name", sprintf(ACTION_CONFIRM_BOOKING_EMAIL_SUBJECT, $locationName), $mailMessage, $inlineAttachments);
//set_debug("Send mail response: $result");


mysql_query('COMMIT', $link);
echo "OK $name $fnight";
audit(AUDIT_BCR_SENT, $_REQUEST, 0, $descrId, $link);

mysql_close($link);


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
