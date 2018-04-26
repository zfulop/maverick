<?php


function sendBcrMessage($bookingDescr, $subject, $bcrMessage, $link, &$dict, $location) {
	$inlineAttachments = array(	
		'logo' => EMAIL_IMG_DIR . 'logo-white-' . $location . '.png',
		'airport' => EMAIL_IMG_DIR . 'airport.jpg',
		'bullet' => EMAIL_IMG_DIR . 'bullet.jpg',
		'map' => EMAIL_IMG_DIR . 'map-' . $location . '.jpg',
		'railwaystation' => EMAIL_IMG_DIR . 'railwaystation.jpg',
		'5star_award_footer_2015' => EMAIL_IMG_DIR . '5star_award_footer_2015.png',
		'5star_award_footer_2016' => EMAIL_IMG_DIR . '5star_award_footer_2016.png',
		'booking_award_footer_2016' => EMAIL_IMG_DIR . 'booking_award_footer_2016.png',
		'hostelworld_award_footer_2015' => EMAIL_IMG_DIR . 'hostelworld_award_footer_2015.png',
		'bullet' =>  EMAIL_IMG_DIR . 'bullet.jpg',
		'facebook' =>  EMAIL_IMG_DIR . 'facebook.png',
		'famous_hostels' =>  EMAIL_IMG_DIR . 'famous_hostels.png',
		'gplus' =>  EMAIL_IMG_DIR . 'gplus.png',
		'hostelbookers_award_footer_2012' =>  EMAIL_IMG_DIR . 'hostelbookers_award_footer_2012.png',
		'hostelbookers_award_footer_2013' =>  EMAIL_IMG_DIR . 'hostelbookers_award_footer_2013.png',
		'hostelbookers_award_footer_2015' =>  EMAIL_IMG_DIR . 'hostelbookers_award_footer_2015.png',
		'insta' =>  EMAIL_IMG_DIR . 'insta.png',
		'reservation' =>  EMAIL_IMG_DIR . 'reservation.jpg',
		'tripadvisor_award_footer_2012' =>  EMAIL_IMG_DIR . 'tripadvisor_award_footer_2012.png',
		'tripadvisor_award_footer_2013' =>  EMAIL_IMG_DIR . 'tripadvisor_award_footer_2013.png',
		'tripadvisor_award_footer_2014' =>  EMAIL_IMG_DIR . 'tripadvisor_award_footer_2014.png',
		'tripadvisor_award_footer_2015' =>  EMAIL_IMG_DIR . 'tripadvisor_award_footer_2015.png',
		'tripadvisor_award_footer_2016' =>  EMAIL_IMG_DIR . 'tripadvisor_award_footer_2016.png'
	);

	$messageData = getBcrData($bookingDescr, $bcrMessage, $link, $dict, $location);
	
	$locationName = $dict[$bookingDescr['language']]['LOCATION_NAME_' . strtoupper($location)];
	$subject = str_replace('LOCATION', $locationName, $subject);
	
	$mailer = new MaverickMailer(CONTACT_EMAIL, $locationName, $bookingDescr['email'], $bookingDescr['name']);
	$result = $mailer->sendTemplatedMail($subject, 'bcr.tpl', $messageData, $inlineAttachments);

	if(is_null($result)) {
		$result = 'SUCCESS';
	}
	return $result;
}

function getBcrData($bookingDescr, $bcrMessage, $link, &$dict, $location) {
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
	$locationName = $dict[$lang]['LOCATION_NAME_' . strtoupper($location)];
	$bcrMessage = str_replace('RECIPIENT', $bookingDescr['name'], $bcrMessage);
	$bcrMessage = str_replace('LOCATION', $locationName, $bcrMessage);
	$bcrMessage = str_replace('CONFIRM_URL', $confirmBookingUrl, $bcrMessage);

	$messageData = array();
	
	$messageData['BELOW_FIND_BOOKING_INFO'] = $dict[$lang]['BELOW_FIND_BOOKING_INFO'];
	$messageData['NAME'] = $dict[$lang]['NAME'];
	$messageData['EMAIL'] = $dict[$lang]['EMAIL'];
	$messageData['PHONE'] = $dict[$lang]['PHONE'];
	$messageData['ADDRESS_TITLE'] = $dict[$lang]['ADDRESS_TITLE'];
	$messageData['NATIONALITY'] = $dict[$lang]['NATIONALITY'];
	$messageData['DATE_OF_ARRIVAL'] = $dict[$lang]['DATE_OF_ARRIVAL'];
	$messageData['DATE_OF_DEPARTURE'] = $dict[$lang]['DATE_OF_DEPARTURE'];
	$messageData['NUMBER_OF_NIGHTS'] = $dict[$lang]['NUMBER_OF_NIGHTS'];
	$messageData['comment'] = $dict[$lang]['comment'];
	$messageData['rooms'] = $dict[$lang]['rooms'];
	$messageData['services'] = $dict[$lang]['services'];
	$messageData['PAYMENT'] = $dict[$lang]['PAYMENT'];
	$messageData['TOTAL_PRICE'] = $dict[$lang]['TOTAL_PRICE'];
	$messageData['ADVISE_TO_TRAVEL'] = $dict[$lang]['ADVISE_TO_TRAVEL'];
	$messageData['RAILWAY_STATIONS'] = $dict[$lang]['RAILWAY_STATIONS'];
	$messageData['FROM_AIRPORT'] = $dict[$lang]['FROM_AIRPORT'];
	$messageData['PAYMENT_DESCRIPTION'] = $dict[$lang]['PAYMENT_DESCRIPTION'];
	$messageData['ACTUAL_EXCHANGE_RATE'] = $dict[$lang]['ACTUAL_EXCHANGE_RATE'];
	$messageData['POLICY'] = $dict[$lang]['POLICY'];

	$messageData['booker_name'] = $bookingDescr['name'];
	$messageData['booker_email'] = $bookingDescr['email'];
	$messageData['booker_phone'] = $bookingDescr['telephone'];
	$messageData['booker_address'] = $bookingDescr['address'];
	$messageData['booker_nationality'] = $bookingDescr['nationality'];
	$messageData['booker_arrival_date'] = $fnight;
	$messageData['booker_departure_date'] = $departureDate;
	$messageData['booker_number_of_nights'] = $bookingDescr['num_of_nights'];
	$messageData['booker_comment'] = $bookingDescr['comment'];
	$messageData['bcr_message'] = $bcrMessage;

	$total = 0;

	// Load rooms
	$roomArray = array();
	$sql = "SELECT b.*, l.value AS room_name, rt.type AS room_type FROM bookings b INNER JOIN rooms r ON b.room_id=r.id INNER JOIN room_types rt ON r.room_type_id=rt.id INNER JOIN lang_text l on (l.table_name='room_types' and l.column_name='name' and l.row_id=r.room_type_id AND l.lang='$lang') WHERE b.description_id=$descrId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo "Cannot get booking data: " . mysql_error($link) . "\n";
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

		$roomArray[] = array(
			'item_name' => $row['room_name'] . ' - ' . $dict[$lang][strtoupper($row['booking_type'])] . $numOfPerson,
			'item_price' => $payment . $currency);
	}

	// Load services
	$serviceArray = array();
	$sql = "SELECT sc.*, s.price, s.currency AS svcCurr, l.value AS title FROM service_charges sc INNER JOIN services s ON (sc.type=s.service_charge_type AND s.free_service=0) INNER JOIN lang_text l on (l.table_name='services' and l.column_name='title' and l.row_id=s.id AND l.lang='$lang') WHERE sc.booking_description_id=$descrId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo "Cannot get services data: " . mysql_error($link) . "\n";
		mysql_close($link);
		return;
	}
	while($row = mysql_fetch_assoc($result)) {
		$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_service'], 0, 10)));
		$prc = intval(convertAmount($row['price'], $row['svcCurr'], 'EUR', substr($row['time_of_service'], 0, 10)));
		$occasion = intval($amount / $prc);
		$amount = intval(convertAmount($amount, 'EUR', $currency, substr($row['time_of_service'], 0, 10)));
		$total += $amount;

		$serviceArray[] = array(
			'item_name' => $row['title'] . ' X ' . $occasion,
			'item_price' => $amount . $currency);
	}

	// Load payments
	$paymentArray = array();
	$sql = "SELECT p.* FROM payments p WHERE p.booking_description_id=$descrId AND storno<>1";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get payments for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo "Cannot get payments data: " . mysql_error($link) . "\n";
		mysql_close($link);
		return;
	}
	while($row = mysql_fetch_assoc($result)) {
		$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_payment'], 0, 10)));
		$amount = intval(convertAmount($amount, 'EUR', $currency, substr($row['time_of_payment'], 0, 10)));
		$title = $row['type'];
		$total -= $amount;

		$paymentArray[] = array('item_name' => $title,
								'item_price' => $amount . $currency);
	}	

	$itemBlock = array();
	$itemBlock[] = array('item_title' => $dict[$lang]['rooms'], 'item' => $roomArray);
	if(count($serviceArray) > 0) {
		$itemBlock[] = array('item_title' => $dict[$lang]['services'], 'item' => $serviceArray);
	}
	if(count($paymentArray) > 0) {
		$itemBlock[] = array('item_title' => $dict[$lang]['PAYMENT'], 'item' => $paymentArray);
	}
	$messageData['item_block'] = $itemBlock;

	$messageData['total_payment'] = array(array('booker_totalprice' => $total . $currency));
	logDebug('Total price set to: ' . $messageData['total_payment'][0]['booker_totalprice']);
	$messageData['fromTrainStationInstr'] = $dict[$lang]['RAILWAY_STATIONS_TO_' . strtoupper($location)];
	$messageData['fromAirportInstr'] = $dict[$lang]['AIRPORT_TO_' . strtoupper($location)];
	$messageData['fromAirportInstr2'] = $dict[$lang]['AIRPORT_TO_' . strtoupper($location) . '_2'];

	/*
	echo "from train station: " . $messageData['fromTrainStationInstr'] . "\n\n";
	echo "from airport: " . $messageData['fromAirportInstr'] . "\n\n";
	echo "from airport2: " . $messageData['fromAirportInstr2'] . "\n\n";
	*/
	
	$policyArray = array();
	$idx = 1;
	while(isset($dict[$lang]['POLICY_' . strtoupper($location) . '_' . $idx])) {
		$policyArray[] = array('policy_text' => $dict[$lang]['POLICY_' . strtoupper($location) . '_' . $idx]);
		$idx += 1;
	}
	$messageData['policy'] = $policyArray;

	$messageData = array('BCR' => array($messageData));

	return $messageData;
}





?>
