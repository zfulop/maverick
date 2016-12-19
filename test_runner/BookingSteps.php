<?php

function givenThereAreNoBookings($table) {
	$link = db_connect('teszt_hostel');
	foreach($table['rows'] as $row) {
		$startDate = $row['start date'];
		$endDate = $row['end date'];
		echo "Deleting existing bookings between $startDate and $endDate\n";
		$startDate = str_replace('-','/',$startDate);
		$endDate = str_replace('-','/',$endDate);
		$sql = "DELETE b FROM bookings b INNER JOIN booking_descriptions bd ON b.description_id=bd.id WHERE bd.first_night<='$endDate' AND bd.last_night>='$startDate'";
		if(!mysql_query($sql, $link)) {
			throw new Exception("Error deleting the booking: " . mysql_error($link) . " (SQL: $sql)");
		}
		$sql = "DELETE FROM booking_descriptions WHERE first_night<='$endDate' AND last_night>='$startDate'";
		if(!mysql_query($sql, $link)) {
			throw new Exception("Error deleting the booking: " . mysql_error($link) . " (SQL: $sql)");
		}
		$sql = "DELETE FROM booking_room_changes WHERE date_of_room_change<='$endDate' AND date_of_room_change>='$startDate'";
		if(!mysql_query($sql, $link)) {
			throw new Exception("Error deleting the booking room changes: " . mysql_error($link) . " (SQL: $sql)");
		}
	}
	mysql_close($link);
}

function givenTheFollowingBookingsExist($table) {
	$link = db_connect('teszt_hostel');
	mysql_query("START TRANSACTION", $link);
	$sql = "SELECT r.*,rt.type, rt.num_of_beds FROM rooms r INNER JOIN room_types rt ON r.room_type_id=rt.id";
	$result = mysql_query($sql, $link);
	$rooms = array();
	while($row = mysql_fetch_assoc($result)) {
		$rooms[$row['name']] = $row;
	}
	foreach($table['rows'] as $row) {
		$roomName = $row['room name'];
		foreach($table['titles'] as $title) {
			if($title == 'room name' or $title == 'room type' or $title == '') {
				continue;
			}
			$whatToBook = trim($row[$title]);
			if(strlen($whatToBook) > 0) {
				createBooking($rooms[$roomName], $title, $whatToBook, $link);
			}
		}
	}
	mysql_query("COMMIT", $link);
	mysql_close($link);
}



function createBooking($roomData, $date, $whatToBook, $link) {
	$date = str_replace('-','/',$date);
	$sql = "INSERT INTO booking_descriptions (name,first_night,last_night,num_of_nights,source) VALUES ('test booking', '$date', '$date', 1, '**TESTER**')";
	if(!mysql_query($sql, $link)) {
		throw new Exception("Error creating booking descr: " . mysql_error($link) . " (SQL: $sql)");
	}
	$bdid = mysql_insert_id($link);
	$numOfPerson = $roomData['type'] == 'DORM' ? $whatToBook : $roomData['num_of_beds'];
	$bookingType = $roomData['type'] == 'DORM' ? 'BED' : 'ROOM';
	$roomId = $roomData['id'];
	$now = date('Y-m-d H:i:s');
	$roomTypeId = $roomData['room_type_id'];
	echo "Creating booking for room: " . $roomData['name'] . " for date: $date, type: $bookingType, num: $numOfPerson\n";
	$sql = "INSERT INTO bookings (num_of_person,room_id,booking_type,creation_time,description_id,room_payment,original_room_type_id) VALUES ($numOfPerson,$roomId,'$bookingType','$now',$bdid,10,$roomTypeId)";
	if(!mysql_query($sql, $link)) {
		throw new Exception("Error creating booking descr: " . mysql_error($link) . " (SQL: $sql)");
	}
}


function whenTheFollowingBookingArrivesFromMyallocator($table) {
	$link = db_connect('teszt_hostel');
	echo "Sending myallocator booking to roomcaptain via HTTP json\n";
	$sql = "SELECT id, name, type FROM room_types";
	$result = mysql_query($sql, $link);
	$roomTypes = array();
	while($row = mysql_fetch_assoc($result)) {
		$roomTypes[$row['name']] = $row;
	}
	mysql_close($link);
	$custArr = array("CustomerLName" => "Tester", "CustomerEmail" => "testing@test.com", "CustomerFName" => "James", "CustomerCity" => "Budapest", "CustomerPostCode" => "1095", "CustomerAddress" => "Teszt Utca 5", "CustomerPhone" => "1234567", "CustomerCountry" => "hu");
	$today = date('Y-m-d');
	$orderId = 'qwedsc1213';
	$myAllocatorId = 'qwedsc1213';
	$jsonMessage = array(
		"Customers" => array($custArr),
		"OrderDate" => $today,
		"OrderId" => $orderId,
		"IsCancellation" => false,
		"MyallocatorId" => $myAllocatorId,
		"PropertyId" => -1000,
		"Channel" => "boo",
		"Rooms" => array()
	);

	$totalPrice = 0;
	$currency = 'EUR';
	$first = true;
	foreach($table['rows'] as $row) {
		if($first) {
			$first = false;
			$currency = $row['currency'];
			$jsonMessage['Comission'] = $row['comission'];
			$currency = $row['currency'];
			$jsonMessage['Currency'] = $currency;
			$jsonMessage['StartDate'] = $row['start date'];
			$jsonMessage['EndDate'] = $row['end date'];
			if(isset($row['myallocatorid'])) {
				$jsonMessage['MyallocatorId'] = $row['myallocatorid'];
			}
		} else {
			$roomType = $roomTypes[$row['room type']];
			if(is_null($roomType)) {
				throw new Exception("Cannot find room type: " . $row['room type']);
			}
			$remoteRoomId = getMyAllocationRoomId($roomType['id']);
			if(is_null($roomType)) {
				throw new Exception("Cannot find myallocator room id for room type: " . $row['room type']);
			}
			// echo "    for room type: " . $row['room type'] . "(id: " . $roomType['id'] . ") the remote room id: $remoteRoomId\n";
			$startDate = $row['start date'];
			$endDate = $row['end date'];
			$units = $row['units'];
			$roomPrice = $row['price'];
			$totalPrice += $roomPrice;
			// echo "    " . $roomType['name'] . " from $startDate to $endDate, $units units, price: $roomPrice\n";
			$jsonMessage['Rooms'][] = array("Units" => $units, "StartDate" => $startDate, "EndDate" => $endDate, "RoomTypeIds" => array($remoteRoomId), "Price" => $roomPrice, "Currency" => $currency);
		}
	}
	$jsonMessage['TotalCurrency'] = $currency;
	$jsonMessage['TotalPrice'] = $totalPrice;
	
	// echo "Sending in json booking request: " . json_encode($jsonMessage, JSON_PRETTY_PRINT) . "\n";
	
	$postString = "password=dfvsdq23sd&booking=" . urlencode(json_encode($jsonMessage));
	
	$curl_connection = curl_init('http://dev.recepcio.roomcaptain.com/myallocator_booking.php');	
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $postString);

	$result = curl_exec($curl_connection);
	
	if($result === false) {
		$msg = "Cannot execute http request to send booking: " . curl_error($curl_connection) . " " . curl_errorno($curl_connection);
		curl_close($curl_connection);
		throw new Exception($msg);
	}
	
	curl_close($curl_connection);
}

function getMyAllocationRoomId($roomTypeId) {
	global $myallocatorRoomMap;
	foreach($myallocatorRoomMap[-1000] as $roomMap) {
		if($roomMap['roomTypeId'] == $roomTypeId or (is_array($roomMap['roomTypeId']) and in_array($roomTypeId, $roomMap['roomTypeId']))) {
			return $roomMap['remoteRoomId'];
		}
	}
}

function thenTheFollowingBookingsWillExistInTheDb($table) {
	$link = db_connect('teszt_hostel');
	echo "Comparing saved bookings with given list\n";
	$sql = "SELECT r.name AS room_name, rt.name AS room_type_name, b.room_payment, b.booking_type, bd.first_night, bd.last_night FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id INNER JOIN rooms r ON b.room_id=r.id INNER JOIN room_types rt on r.room_type_id=rt.id WHERE bd.source<>'**TESTER**' AND bd.first_night like '2010%'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		mysql_close($link);
		throw new Exception("Cannot get bookings from db. Error: " . mysql_error($link) . " (SQL: $sql)");
	}
	$dbBookings = array();
	while($row = mysql_fetch_assoc($result)) {
		$dbBookings[] = $row;
	}
	mysql_close($link);

	$expectedBookings = array();
	foreach($table['rows'] as $row) {
		$expectedBookings[] = array(
			'room_type_name' => $row['room type'],
			'room_name' => $row['room'],
			'first_night' => str_replace("-", "/" , $row['first night']),
			'last_night' => str_replace("-", "/" , $row['last night']),
			'booking_type' => $row['booking type'],
			'room_payment' => $row['room payment']);
	}
	
	compareList($expectedBookings, $dbBookings, 'bookingCompare');
}


function bookingCompare($booking1, $booking2) {
	if($booking1['room_type_name'] > $booking2['room_type_name']) { return 1; }
	if($booking1['room_type_name'] < $booking2['room_type_name']) { return -1; }
	if(!nameMatch($booking1['room_name'], $booking2['room_name'])) {
		if($booking1['room_name'] > $booking2['room_name']) { return 2; }
		if($booking1['room_name'] < $booking2['room_name']) { return -2; }
	}
	if($booking1['first_night'] > $booking2['first_night']) { return 3; }
	if($booking1['first_night'] < $booking2['first_night']) { return -3; }
	if($booking1['last_night'] > $booking2['last_night']) { return 4; }
	if($booking1['last_night'] < $booking2['last_night']) { return -4; }
	if($booking1['booking_type'] > $booking2['booking_type']) { return 5; }
	if($booking1['booking_type'] < $booking2['booking_type']) { return -5; }
	if(intval($booking1['room_payment']) > intval($booking2['room_payment'])) { return 6; }
	if(intval($booking1['room_payment']) < intval($booking2['room_payment'])) { return -6; }
	
	return 0;
}

function nameMatch($roomName1, $roomName2) {
	if(strpos($roomName1, ";") > 0) {
		$names = explode(";", $roomName1);
		return in_array($roomName2, $names);
	} elseif(strpos($roomName2, ";") > 0) {
		$names = explode(";", $roomName2);
		return in_array($roomName1, $names);
	} else {
		return ($roomName1 == $roomName2);
	}
}


function thenTheFollowingBookingRoomChangesWillExistInTheDb($table) {
	$link = db_connect('teszt_hostel');
	echo "Comparing saved room changes with given list\n";
	$sql = "SELECT br.name as original_room, nr.name as new_room, brc.date_of_room_change FROM bookings b INNER JOIN booking_room_changes brc ON b.id=brc.booking_id INNER JOIN rooms br ON b.room_id=br.id INNER JOIN rooms nr ON brc.new_room_id=nr.id WHERE brc.date_of_room_change LIKE '2010%'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		mysql_close($link);
		throw new Exception("Cannot get bookings from db. Error: " . mysql_error($link) . " (SQL: $sql)");
	}
	$dbRoomChanges = array();
	while($row = mysql_fetch_assoc($result)) {
		$dbRoomChanges[] = $row;
	}
	mysql_close($link);

	$expectedRoomChanges = array();
	foreach($table['rows'] as $row) {
		$expectedRoomChanges[] = array(
			'original_room' => $row['original room'],
			'new_room' => $row['new room'],
			'date_of_room_change' => str_replace("-", "/" , $row['date of room change']));
	}
	
	compareList($expectedRoomChanges, $dbRoomChanges, 'roomChangeCompare');
	
}

function roomChangeCompare($booking1, $booking2) {
	if($booking1['original_room'] > $booking2['original_room']) { return 1; }
	if($booking1['original_room'] < $booking2['original_room']) { return -1; }
	if($booking1['new_room'] > $booking2['new_room']) { return 2; }
	if($booking1['new_room'] < $booking2['new_room']) { return -2; }
	if($booking1['date_of_room_change'] > $booking2['date_of_room_change']) { return 3; }
	if($booking1['date_of_room_change'] < $booking2['date_of_room_change']) { return -3; }
	
	return 0;
}



 ?>