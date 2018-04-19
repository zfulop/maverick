<?php

require_once "HTML/Template/IT.php";

function getBcrMessage($bookingDescr, $bcrMessage, $link, &$dict, $location) {
	$descrId = $bookingDescr['id'];
	$lang = $bookingDescr['language'];
	if(is_null($lang) or strlen(trim($lang)) < 3) {
		$lang = 'eng';
	}
	$currency = $bookingDescr['currency'];
	if(is_null($currency) or strlen(trim($currency)) < 3) {
		$currency = 'EUR';
	}
	$fnight = str_replace('/', '-', $bookingDescr['first_night']);
	$lnight = str_replace('/', '-', $bookingDescr['last_night']);
	$departureDate = date('Y-m-d', strtotime($lnight . " +1 day"));
	$confirmCode = $descrId . 'A' . password_hash($bookingDescr['email'], PASSWORD_DEFAULT);

	$confirmBookingUrl = CONFIRM_BOOKING_URL;
	$confirmBookingUrl = str_replace('LANG_2', substr($lang,0,2), $confirmBookingUrl);
	$confirmBookingUrl = str_replace('LANG', $lang, $confirmBookingUrl);
	$confirmBookingUrl = str_replace('LOCATION', $location, $confirmBookingUrl);
	echo "confirm code: $confirmCode\n";
	$confirmBookingUrl = str_replace('CONFIRM_CODE', urlencode($confirmCode), $confirmBookingUrl);
	$locationName = $dict[$bookingDescr['language']]['LOCATION_NAME_' . strtoupper($location)];
	$bcrMessage = str_replace('RECIPIENT', $bookingDescr['name'], $bcrMessage);
	$bcrMessage = str_replace('LOCATION', $locationName, $bcrMessage);
	$bcrMessage = str_replace('CONFIRM_URL', $confirmBookingUrl, $bcrMessage);

	$tpl = new HTML_Template_IT(".");
	$tpl->loadTemplatefile("bcr.tpl", true, true);

	$tpl->setCurrentBlock("BCR") ;

	foreach($dict[$lang] as $key => $value) {
		$tpl->setVariable($key, $value) ;
	}
	$tpl->setVariable('booker_name', $bookingDescr['name']);
	$tpl->setVariable('booker_email', $bookingDescr['email']);
	$tpl->setVariable('booker_phone', $bookingDescr['telephone']);
	$tpl->setVariable('booker_address', $bookingDescr['address']);
	$tpl->setVariable('booker_nationality', $bookingDescr['nationality']);
	$tpl->setVariable('booker_arrival_date', $fnight);
	$tpl->setVariable('booker_departure_date', $departureDate);
	$tpl->setVariable('booker_number_of_nights', $bookingDescr['num_of_nights']);
	$tpl->setVariable('booker_comment', $bookingDescr['comment']);
	$tpl->setVariable('bcr_message', $bcrMessage);

	$total = 0;
	$sql = "SELECT b.*, l.value AS room_name, rt.type AS room_type FROM bookings b INNER JOIN rooms r ON b.room_id=r.id INNER JOIN room_types rt ON r.room_type_id=rt.id INNER JOIN lang_text l on (l.table_name='room_types' and l.column_name='name' and l.row_id=r.room_type_id AND l.lang='$lang') WHERE b.description_id=$descrId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo 'Cannot get booking data';
		mysql_close($link);
		return;
	}
	while($row = mysql_fetch_assoc($result)) {
		$payment = intval(convertAmount($row['room_payment'], 'EUR', $currency, substr($row['creation_time'], 0, 10)));
		$numOfPerson = '(' . $row['num_of_person'] . ')';
		if($row['room_type'] == 'APARTMENT') {
			$numOfPerson = '';
		}
		$total += $payment;

		$tpl->setCurrentBlock("room") ;
		$tpl->setVariable('room_name', $row['room_name'] . ' - ' . $dict[$bookingDescr['language']][strtoupper($row['booking_type'])] . $numOfPerson);
		$tpl->setVariable('room_price', $payment . $currency);
		$tpl->parseCurrentBlock();
	}

	$sql = "SELECT sc.*, s.price, s.currency AS svcCurr, l.value AS title FROM service_charges sc INNER JOIN services s ON (sc.type=s.service_charge_type AND s.free_service=0) INNER JOIN lang_text l on (l.table_name='services' and l.column_name='title' and l.row_id=s.id AND l.lang='$lang') WHERE sc.booking_description_id=$descrId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo 'Cannot get booking data';
		mysql_close($link);
		return;
	}
	$hasServices = (mysql_num_rows($result) > 0);
	while($row = mysql_fetch_assoc($result)) {
		$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_service'], 0, 10)));
		$prc = intval(convertAmount($row['price'], $row['svcCurr'], 'EUR', substr($row['time_of_service'], 0, 10)));
		$occasion = intval($amount / $prc);
		$amount = intval(convertAmount($amount, 'EUR', $currency, substr($row['time_of_service'], 0, 10)));
		$total += $amount;

		$tpl->setCurrentBlock("service") ;
		$tpl->setVariable('service_name', $row['title'] . ' X ' . $occasion);
		$tpl->setVariable('service_price', $amount . $currency);
		$tpl->parseCurrentBlock();
	}


	$sql = "SELECT p.* FROM payments p WHERE p.booking_description_id=$descrId AND storno<>1";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get payments for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo 'Cannot get booking data';
		mysql_close($link);
		return;
	}
	while($row = mysql_fetch_assoc($result)) {
		$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_payment'], 0, 10)));
		$amount = intval(convertAmount($amount, 'EUR', $currency, substr($row['time_of_payment'], 0, 10)));
		$title = $row['type'];
		$total -= $amount;

		$tpl->setCurrentBlock("payment") ;
		$tpl->setVariable('payment_name', $title);
		$tpl->setVariable('payment_price', $amount . $currency);
		$tpl->parseCurrentBlock();
	}

	$tpl->setVariable('booker_total_price', $total . $currency);
	$tpl->setVariable('fromTrainStationInstructions', $dict[$bookingDescr['language']]['RAILWAY_STATIONS_TO_' . strtoupper($location)]);
	$tpl->setVariable('fromAirportInstructions', $dict[$bookingDescr['language']]['AIRPORT_TO_' . strtoupper($location)]);
	$tpl->setVariable('fromAirportInstructions2', $dict[$bookingDescr['language']]['AIRPORT_TO_' . strtoupper($location) . '_2']);

	$idx = 1;
	while(isset($dict[$bookingDescr['language']]['POLICY_' . strtoupper($location) . '_' . $idx])) {
		$tpl->setCurrentBlock("policy") ;
		$tpl->setVariable('policy_text', $dict[$bookingDescr['language']]['POLICY_' . strtoupper($location) . '_' . $idx]);
		$tpl->parseCurrentBlock();
		$idx += 1;
	}

	$tpl->parseCurrentBlock(); /* parse the main BRC block */


	$html = $tpl->get();
	
	return $html;

}





?>
