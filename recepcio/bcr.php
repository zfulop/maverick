<?php

class BCR {
	
	private $bookingDescr;
	private $descrId;
	private $location;
	private $lang;
	private $currency;
	private $dict;
	private $roomArray;
	private $serviceArray;
	private $paymentArray;
	private $total;

	function __construct($bookingDescr, $location, $dict, $link) {
		$this->bookingDescr = $bookingDescr;
		$this->descrId = $this->bookingDescr['id'];
		$this->currency = $this->bookingDescr['currency'];
		if(is_null($this->currency) or strlen(trim($this->currency)) < 3) {
			$this->currency = 'EUR';
		}
		$this->lang = $this->bookingDescr['language'];
		if(is_null($this->lang) or strlen(trim($this->lang)) < 3) {
			$this->lang = 'eng';
		}
		$this->location = $location;
		$this->dict = $dict;
		$this->total = 0;
		$this->roomArray = $this->loadRooms($link);
		$this->serviceArray = $this->loadServices($link);
		$this->paymentArray = $this->loadPayments($link);
		logDebug("BCR::ctor() - there are " . count($this->roomArray) . " rooms, " . count($this->serviceArray) . " services and " . count($this->paymentArray) . " payments");
	}

	private function loadRooms($link) {
		// Load rooms
		$roomArray = array();
		$sql = "SELECT b.*, l.value AS room_name, rt.type AS room_type FROM bookings b " .
			" INNER JOIN rooms r ON b.room_id=r.id INNER JOIN room_types rt ON r.room_type_id=rt.id " .
			" INNER JOIN lang_text l on (l.table_name='room_types' and l.column_name='name' and l.row_id=r.room_type_id AND l.lang='" . $this->lang . "')" .
			" WHERE b.description_id=" . $this->descrId;
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			echo "Cannot get booking data: " . mysql_error($link) . "\nSQL: $sql";
			throw new Exception("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)");
		}
		while($row = mysql_fetch_assoc($result)) {
			$payment = intval(convertAmount($row['room_payment'], 'EUR', $this->currency, substr($row['creation_time'], 0, 10)));
			$numOfPerson = '(' . $row['num_of_person'] . ')';
			if($row['room_type'] == 'APARTMENT') {
				$numOfPerson = '';
			}
			$this->total += $payment;

			$roomArray[] = array(
				'item_name' => $row['room_name'] . ' - ' . $this->dict[$this->lang][strtoupper($row['booking_type'])] . $numOfPerson,
				'item_price' => $payment . $this->currency);
		}
		return $roomArray;
	}

	private function loadServices($link) {
		// Load services
		$serviceArray = array();
		$sql = "SELECT sc.*, s.price, s.currency AS svcCurr, l.value AS title FROM service_charges sc " .
			" INNER JOIN services s ON (sc.type=s.service_charge_type AND s.free_service=0) " .
			" INNER JOIN lang_text l on (l.table_name='services' and l.column_name='title' and l.row_id=s.id AND l.lang='" . $this->lang . "') " .
			" WHERE sc.booking_description_id=" . $this->descrId;
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get services for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			echo "Cannot get services data: " . mysql_error($link) . "\n";
			throw new Exception("Cannot get services for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)");
		}
		while($row = mysql_fetch_assoc($result)) {
			$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_service'], 0, 10)));
			$prc = intval(convertAmount($row['price'], $row['svcCurr'], 'EUR', substr($row['time_of_service'], 0, 10)));
			$occasion = intval($amount / $prc);
			$amount = intval(convertAmount($amount, 'EUR', $this->currency, substr($row['time_of_service'], 0, 10)));
			$this->total += $amount;

			$serviceArray[] = array(
				'item_name' => $row['title'] . ' X ' . $occasion,
				'item_price' => $amount . $this->currency);
		}

		return $serviceArray;
	}

	private function loadPayments($link) {
		// Load payments
		$paymentArray = array();
		$sql = "SELECT p.* FROM payments p WHERE p.booking_description_id=" . $this->descrId . " AND storno<>1";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get payments for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			echo "Cannot get payments data: " . mysql_error($link) . "\n";
			throw new Exception("Cannot get payments for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)");
		}
		while($row = mysql_fetch_assoc($result)) {
			$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_payment'], 0, 10)));
			$amount = intval(convertAmount($amount, 'EUR', $this->currency, substr($row['time_of_payment'], 0, 10)));
			$title = $row['type'];
			$this->total -= $amount;

			$paymentArray[] = array('item_name' => $title,
									'item_price' => $amount . $this->currency);
		}

		return $paymentArray;
	}
	
	function sendBookingMessageToReception() {
		$fnight = str_replace('/', '-', $this->bookingDescr['first_night']);
		$lnight = str_replace('/', '-', $this->bookingDescr['last_night']);
		
		$departureDate = date('Y-m-d', strtotime($lnight . " +1 day"));
		$messageData['booker_name'] = $this->bookingDescr['name'];
		$messageData['booker_email'] = $this->bookingDescr['email'];
		$messageData['booker_phone'] = $this->bookingDescr['telephone'];
		$messageData['booker_address'] = $this->bookingDescr['address'];
		$messageData['booker_nationality'] = $this->bookingDescr['nationality'];
		$messageData['booker_arrival_date'] = $fnight;
		$messageData['booker_departure_date'] = $departureDate;
		$messageData['booker_number_of_nights'] = $this->bookingDescr['num_of_nights'];
		$messageData['booker_comment'] = $this->bookingDescr['comment'];
		$messageData['editBookingUrl'] = "http://reception.roomcaptain.com/edit_booking.php?description_id=" . $this->descrId . "&login_hotel=" . $this->location;

		$itemBlock = array();
		$itemBlock[] = array('item_title' => 'Rooms', 'item' => $this->roomArray);
		if(count($this->serviceArray) > 0) {
			$itemBlock[] = array('item_title' => 'Services', 'item' => $this->serviceArray);
		}
		if(count($this->paymentArray) > 0) {
			$itemBlock[] = array('item_title' => 'Payments', 'item' => $this->paymentArray);
		}
		$messageData['item_block'] = $itemBlock;
		$messageData['total_payment'] = array(array('booker_totalprice' => $this->total . $this->currency));

		$recEmail = CONTACT_EMAIL;
		// If booker is zfulop, then it is a test and then send it to zfulop
		if($this->bookingDescr['email'] == 'zfulop@zolilla.com') {
			$recEmail = 'zfulop@zolilla.com';
		}
		$mailer = new MaverickMailer(CONTACT_EMAIL, $this->locationName, $recEmail, $this->locationName);
		$result = $mailer->sendTemplatedMail("Booking arrived from website", 'booking_notification_to_reception.tpl', $messageData);

		if(is_null($result)) {
			$result = 'SUCCESS';
		}
		return $result;
	}

	function sendBcrMessage($subject, $bcrMessage, $templateFile) {
		$inlineAttachments = array(	
			'logo' => EMAIL_IMG_DIR . 'logo-white-' . $this->location . '.png',
			'airport' => EMAIL_IMG_DIR . 'airport.jpg',
			'bullet' => EMAIL_IMG_DIR . 'bullet.jpg',
			'map' => EMAIL_IMG_DIR . 'map-' . $this->location . '.jpg',
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
		
		$this->locationName = $this->dict[$this->lang]['LOCATION_NAME_' . strtoupper($this->location)];
		$subject = str_replace('LOCATION', $this->locationName, $subject);

		$fnight = str_replace('/', '-', $this->bookingDescr['first_night']);
		$lnight = str_replace('/', '-', $this->bookingDescr['last_night']);
		$departureDate = date('Y-m-d', strtotime($lnight . " +1 day"));
		$confirmCode = $this->descrId . 'A' . password_hash($this->bookingDescr['email'], PASSWORD_DEFAULT);

		$confirmBookingUrl = CONFIRM_BOOKING_URL;
		$confirmBookingUrl = str_replace('LANG_2', substr($this->lang,0,2), $confirmBookingUrl);
		$confirmBookingUrl = str_replace('LANG', $this->lang, $confirmBookingUrl);
		$confirmBookingUrl = str_replace('LOCATION', $this->location, $confirmBookingUrl);
		logDebug("confirm code: $confirmCode");
		$confirmBookingUrl = str_replace('CONFIRM_CODE', urlencode($confirmCode), $confirmBookingUrl);
		$bcrMessage = str_replace('RECIPIENT', $this->bookingDescr['name'], $bcrMessage);
		$bcrMessage = str_replace('LOCATION', $this->locationName, $bcrMessage);
		$bcrMessage = str_replace('CONFIRM_URL', $confirmBookingUrl, $bcrMessage);

		$messageData = array();		
		$messageData['BELOW_FIND_BOOKING_INFO'] = $this->dict[$this->lang]['BELOW_FIND_BOOKING_INFO'];
		$messageData['NAME'] = $this->dict[$this->lang]['NAME'];
		$messageData['EMAIL'] = $this->dict[$this->lang]['EMAIL'];
		$messageData['PHONE'] = $this->dict[$this->lang]['PHONE'];
		$messageData['ADDRESS_TITLE'] = $this->dict[$this->lang]['ADDRESS_TITLE'];
		$messageData['NATIONALITY'] = $this->dict[$this->lang]['NATIONALITY'];
		$messageData['DATE_OF_ARRIVAL'] = $this->dict[$this->lang]['DATE_OF_ARRIVAL'];
		$messageData['DATE_OF_DEPARTURE'] = $this->dict[$this->lang]['DATE_OF_DEPARTURE'];
		$messageData['NUMBER_OF_NIGHTS'] = $this->dict[$this->lang]['NUMBER_OF_NIGHTS'];
		$messageData['comment'] = $this->dict[$this->lang]['comment'];
		$messageData['rooms'] = $this->dict[$this->lang]['rooms'];
		$messageData['services'] = $this->dict[$this->lang]['services'];
		$messageData['PAYMENT'] = $this->dict[$this->lang]['PAYMENT'];
		$messageData['TOTAL_PRICE'] = $this->dict[$this->lang]['TOTAL_PRICE'];
		$messageData['ADVISE_TO_TRAVEL'] = $this->dict[$this->lang]['ADVISE_TO_TRAVEL'];
		$messageData['RAILWAY_STATIONS'] = $this->dict[$this->lang]['RAILWAY_STATIONS'];
		$messageData['FROM_AIRPORT'] = $this->dict[$this->lang]['FROM_AIRPORT'];
		$messageData['PAYMENT_DESCRIPTION'] = $this->dict[$this->lang]['PAYMENT_DESCRIPTION'];
		$messageData['ACTUAL_EXCHANGE_RATE'] = $this->dict[$this->lang]['ACTUAL_EXCHANGE_RATE'];
		$messageData['POLICY'] = $this->dict[$this->lang]['POLICY'];
		$messageData['trainStationInstr'] = $this->dict[$this->lang]['RAILWAY_STATIONS_TO_' . strtoupper($this->location)];
		$messageData['airportInstr'] = $this->dict[$this->lang]['AIRPORT_TO_' . strtoupper($this->location)];
		$messageData['airportInstr2'] = $this->dict[$this->lang]['AIRPORT_TO_' . strtoupper($this->location) . '_2'];
		$messageData['googlePlusLink'] = $this->dict[$this->lang]['GOOGLE_PLUS'];

		$messageData['booker_name'] = $this->bookingDescr['name'];
		$messageData['booker_email'] = $this->bookingDescr['email'];
		$messageData['booker_phone'] = $this->bookingDescr['telephone'];
		$messageData['booker_address'] = $this->bookingDescr['address'];
		$messageData['booker_nationality'] = $this->bookingDescr['nationality'];
		$messageData['booker_arrival_date'] = $fnight;
		$messageData['booker_departure_date'] = $departureDate;
		$messageData['booker_number_of_nights'] = $this->bookingDescr['num_of_nights'];
		$messageData['booker_comment'] = $this->bookingDescr['comment'];
		$messageData['bcr_message'] = $bcrMessage;

		$itemBlock = array();
		$itemBlock[] = array('item_title' => $this->dict[$this->lang]['rooms'], 'item' => $this->roomArray);
		if(count($this->serviceArray) > 0) {
			$itemBlock[] = array('item_title' => $this->dict[$this->lang]['services'], 'item' => $this->serviceArray);
		}
		if(count($this->paymentArray) > 0) {
			$itemBlock[] = array('item_title' => $this->dict[$this->lang]['PAYMENT'], 'item' => $this->paymentArray);
		}
		$messageData['item_block'] = $itemBlock;

		$messageData['total_payment'] = array(array('booker_totalprice' => $this->total . $this->currency));
		logDebug('Total price set to: ' . $messageData['total_payment'][0]['booker_totalprice']);

		$policyArray = array();
		$idx = 1;
		while(isset($this->dict[$this->lang]['POLICY_' . strtoupper($this->location) . '_' . $idx])) {
			$policyArray[] = array('policy_text' => $this->dict[$this->lang]['POLICY_' . strtoupper($this->location) . '_' . $idx]);
			$idx += 1;
		}
		$messageData['policy'] = $policyArray;

		$messageData = array('BCR' => array($messageData));
		
		$mailer = new MaverickMailer(CONTACT_EMAIL, $this->locationName, $this->bookingDescr['email'], $this->bookingDescr['name']);
		$result = $mailer->sendTemplatedMail($subject, $templateFile, $messageData, $inlineAttachments);

		if(is_null($result)) {
			$result = 'SUCCESS';
		}
		return $result;
	}

}




?>
