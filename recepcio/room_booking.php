<?php

function loadRoomTypes($link, $lang = 'eng') {
	$sql = "SELECT rt.id, rt.price_per_bed, rt.price_per_room, rt.surcharge_per_bed, rt.type, rt.num_of_beds, lt1.value AS name, lt2.value AS description, " .
	"lt3.value AS short_description, lt4.value AS size, lt5.value AS location, lt6.value AS bathroom, rt._order, 0 AS num_of_beds_avail FROM room_types rt " . 
	"INNER JOIN lang_text lt1 ON (lt1.table_name='room_types' AND lt1.column_name='name' AND lt1.row_id=rt.id AND lt1.lang='$lang') " . 
	"INNER JOIN lang_text lt2 ON (lt2.table_name='room_types' AND lt2.column_name='description' AND lt2.row_id=rt.id AND lt2.lang='$lang') " . 
	"LEFT OUTER JOIN lang_text lt3 ON (lt3.table_name='room_types' AND lt3.column_name='short_description' AND lt3.row_id=rt.id AND lt3.lang='$lang') " .
	"LEFT OUTER JOIN lang_text lt4 ON (lt3.table_name='room_types' AND lt3.column_name='size' AND lt3.row_id=rt.id AND lt3.lang='$lang') " .
	"LEFT OUTER JOIN lang_text lt5 ON (lt3.table_name='room_types' AND lt3.column_name='location' AND lt3.row_id=rt.id AND lt3.lang='$lang') " .
	"LEFT OUTER JOIN lang_text lt6 ON (lt3.table_name='room_types' AND lt3.column_name='bathroom' AND lt3.row_id=rt.id AND lt3.lang='$lang') " .
	"ORDER BY rt._order";

	$result = mysql_query($sql, $link);
	$roomTypesData = array();
	while($row = mysql_fetch_assoc($result)) {
		$roomTypesData[$row['id']] = $row;
	}

	return $roomTypesData;
}

function loadRoomTypesWithAvailableBeds($link, $startDate, $endDate, $lang = 'eng') {
	$roomTypesData = loadRoomTypes($link, $lang);
	foreach($roomTypesData as $rtId => $roomType) {
		$roomType['available_beds'] = 0;
		$roomType['num_of_rooms'] = 0;
		$roomTypesData[$rtId] = $roomType;
	}
	$sql = "SELECT rt.id, COUNT(*)*rt.num_of_beds AS available_beds, COUNT(*) AS num_of_rooms FROM room_types rt INNER JOIN rooms r ON rt.id=r.room_type_id WHERE r.valid_from<='" . str_replace('-', '/', $startDate) . "' and r.valid_to>='" . str_replace('-', '/', $endDate) . "' GROUP BY rt.id";
	$result = mysql_query($sql, $link);
	if(!$result) {
		echo "SQL ERROR: " . mysql_error($link) . " (sql: $sql)<br>\n";
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$oneRoomType = $roomTypesData[$row['id']];
			$oneRoomType['available_beds'] = $row['available_beds'];
			$oneRoomType['num_of_rooms'] = $row['num_of_rooms'];
			$roomTypesData[$row['id']] = $oneRoomType;
		}
	}
	$sql = "SELECT room_type_id, COUNT(*) AS cnt FROM rooms_to_room_types GROUP BY room_type_id";
	$result = mysql_query($sql, $link);
	if(!$result) {
		echo "SQL ERROR: " . mysql_error($link) . " (sql: $sql)<br>\n";
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$roomTypesData[$row['room_type_id']]['available_beds'] += $roomTypesData[$row['room_type_id']]['num_of_beds'] * $row['cnt'];
			$roomTypesData[$row['room_type_id']]['num_of_rooms'] += $row['cnt'];
		}
	}	
	return $roomTypesData;
}



function loadSpecialOffers($startDate, $endDate, $link, $lang = 'eng') {
	$whereClause = '';
	if(!is_null($startDate)) {
		$whereClause = "(so.end_date>='$endDate' AND so.start_date<='$startDate') OR (sod.end_date>='$endDate' AND sod.start_date<='$startDate')";
	} else {
		$whereClause = "so.end_date>'$endDate' OR sod.end_date>'$endDate'";
	}
	$sql = "SELECT so.*, n.value AS title, d.value AS text, r.value AS room_name FROM special_offers so " .
		"INNER JOIN lang_text n ON (so.id=n.row_id AND n.table_name='special_offers' AND n.column_name='title' AND n.lang='$lang') " .
		"INNER JOIN lang_text d ON (so.id=d.row_id AND d.table_name='special_offers' AND d.column_name='text' AND d.lang='$lang') " .
		"LEFT OUTER JOIN lang_text r ON (so.id=r.row_id AND r.table_name='special_offers' AND r.column_name='room_name' AND r.lang='$lang') " .
		"LEFT OUTER JOIN special_offer_dates sod ON (so.id=sod.special_offer_id) " .
		"WHERE $whereClause";
	$result = mysql_query($sql, $link);
	$specialOffers = array();
	if(!$result) {
		trigger_error("Cannot get special offers: " . mysql_error($link) . " (SQL: $sql)");
	}
	while($row = mysql_fetch_assoc($result)) {
		$row['dates'] = array();
		$row['dates'][] = array('start_date' => $row['start_date'], 'end_date' => $row['end_date']);
		$specialOffers[$row['id']] = $row;
	}

	if(count($specialOffers) > 0) {
		$sql = "SELECT * FROM special_offer_dates WHERE special_offer_id IN (" . implode(',',array_keys($specialOffers)) . ")";
		$result = mysql_query($sql, $link);
		while($row = mysql_fetch_assoc($result)) {
			if(isset($specialOffers[$row['special_offer_id']])) {
				$specialOffers[$row['special_offer_id']]['dates'][] = $row;
			}
		}
	}

	return $specialOffers;
}


function isRoomOfType(&$roomData, $roomTypeId) {
	return in_array($roomTypeId, array_keys($roomData['room_types']));
}

function &loadOnlyRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link, $lang = 'eng') {
	$startMonth = __getNormalizedDate($startMonth);
	$startDay = __getNormalizedDate($startDay);
	$endMonth = __getNormalizedDate($endMonth);
	$endDay = __getNormalizedDate($endDay);

	$arriveDate = "$startYear/$startMonth/$startDay";
	$lastNightDate = "$endYear/$endMonth/$endDay";

	$rooms = array();
	$sql = "SELECT r.id, r.room_type_id, r.name AS name, rt.name AS room_type_name, rt.type, rt.num_of_beds, rt.price_per_room, rt.price_per_bed, rt.surcharge_per_bed, rt._order FROM rooms r INNER JOIN room_types rt ON (r.room_type_id=rt.id) WHERE r.valid_to>='$lastNightDate' AND r.valid_from<='$arriveDate'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		return false;
	}
	while($row = mysql_fetch_assoc($result)) {
		$row['bookings'] = array();
		$row['prices'] = array();
		$row['room_changes'] = array();
		$row['room_types'] = array($row['room_type_id'] => $row['room_type_name']);
		$rooms[$row['id']] = $row;
	}


	$sql = "SELECT rtrt.*, rt.name AS room_type_name FROM rooms_to_room_types rtrt INNER JOIN room_types rt ON (rtrt.room_type_id=rt.id) INNER JOIN rooms r ON rtrt.room_id=r.id WHERE r.valid_to>='$lastNightDate' AND r.valid_from<='$arriveDate'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		return false;
	}
	while($row = mysql_fetch_assoc($result)) {
		$theRoom = $rooms[$row['room_id']];
		$theRoom['room_types'][$row['room_type_id']] = $row['room_type_name'];
		$rooms[$row['room_id']] = $theRoom;
	}

	return $rooms;
}

function &loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link, $lang = 'eng') {
	$startMonth = __getNormalizedDate($startMonth);
	$startDay = __getNormalizedDate($startDay);
	$endMonth = __getNormalizedDate($endMonth);
	$endDay = __getNormalizedDate($endDay);

	$arriveDate = "$startYear/$startMonth/$startDay";
	$lastNightDate = "$endYear/$endMonth/$endDay";

	$rooms = loadOnlyRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link, $lang);

	$roomChanges = array();
	$sql = "SELECT brc.*, bd.name, bd.name_ext, b.description_id, b.room_payment, b.booking_type, b.num_of_person, b.creation_time, bd.first_night, bd.last_night, bd.num_of_nights, bd.confirmed, bd.cancelled, bd.checked_in, bd.paid FROM booking_room_changes brc INNER JOIN bookings b ON brc.booking_id=b.id INNER JOIN booking_descriptions bd ON b.description_id=bd.id WHERE brc.date_of_room_change>='$arriveDate' AND brc.date_of_room_change<='$lastNightDate'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get existing bookings: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		return false;
	}
	while($row = mysql_fetch_assoc($result)) {
		if(isset($rooms[$row['new_room_id']])) {
			$row['payments'] = array();
			$row['service_charges'] = array();
			$roomChanges[$row['booking_id']][] = $row;
			$rooms[$row['new_room_id']]['room_changes'][] = $row;
		}
	}

	$sql = "SELECT bd.name, bd.name_ext, bd.confirmed, bd.checked_in, bd.cancelled, bd.cancel_type, bd.paid, bd.first_night, bd.arrival_time, bd.last_night, bd.num_of_nights, b.* FROM bookings b INNER JOIN booking_descriptions bd ON b.description_id=bd.id WHERE bd.first_night<='$lastNightDate' AND bd.last_night>='$arriveDate'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get existing bookings between dates: $arriveDate and $lastNightDate: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		return false;
	}
	$descrIds = array();
	while($row = mysql_fetch_assoc($result)) {
		if(isset($rooms[$row['room_id']])) {
			$row['changes'] = array();
			if(isset($roomChanges[$row['id']])) {
				$row['changes'] = $roomChanges[$row['id']];
			}
			$row['payments'] = array();
			$row['service_charges'] = array();
			$rooms[$row['room_id']]['bookings'][] = $row;
			$descrIds[] = $row['description_id'];
		}
	}

	if(count($descrIds) > 0) {
		$descrIds = implode(',', $descrIds);
		$sql = "SELECT * FROM payments WHERE booking_description_id IN ($descrIds)";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get payments when loading rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		} else {
			while($row = mysql_fetch_assoc($result)) {
				foreach($rooms as $roomId => $roomData) {
					for($i = 0; $i < count($rooms[$roomId]['bookings']); $i++) {
						if($rooms[$roomId]['bookings'][$i]['description_id'] == $row['booking_description_id']) {
							$rooms[$roomId]['bookings'][$i]['payments'][] = $row;
						}
					}
					for($i = 0; $i < count($rooms[$roomId]['room_changes']); $i++) {
						if($rooms[$roomId]['room_changes'][$i]['description_id'] == $row['booking_description_id']) {
							$rooms[$roomId]['room_changes'][$i]['payments'][] = $row;
						}
					}

				}
			}
		}
		$sql = "SELECT * FROM service_charges WHERE booking_description_id IN ($descrIds)";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get service chanrges when loading rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		} else {
			while($row = mysql_fetch_assoc($result)) {
				foreach($rooms as $roomId => $roomData) {
					for($i = 0; $i < count($rooms[$roomId]['bookings']); $i++) {
						if($rooms[$roomId]['bookings'][$i]['description_id'] == $row['booking_description_id']) {
							$rooms[$roomId]['bookings'][$i]['service_charges'][] = $row;
						}
					}
					for($i = 0; $i < count($rooms[$roomId]['room_changes']); $i++) {
						if($rooms[$roomId]['room_changes'][$i]['description_id'] == $row['booking_description_id']) {
							$rooms[$roomId]['room_changes'][$i]['service_charges'][] = $row;
						}
					}
				}
			}
		}

	}

	//logDebug("Rooms: " . print_r($rooms, true));

	$sql = "SELECT * FROM prices_for_date WHERE date<='$lastNightDate'  AND date>='$arriveDate'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get existing room prices when loading rooms data: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}

	$pricesForRoomType = array();
	while($row = mysql_fetch_assoc($result)) {
		if(!isset($pricesForRoomType[$row['room_type_id']])) {
			$pricesForRoomType[$row['room_type_id']] = array();
		}
		$pricesForRoomType[$row['room_type_id']][] = $row;
	}

	foreach($rooms as $roomId => $roomData) {
		if(isset($pricesForRoomType[$roomData['room_type_id']])) {
			foreach($pricesForRoomType[$roomData['room_type_id']] as $price) {
				//logDebug("Setting price for room. Price: " . print_r($price, true));
				$rooms[$roomId]['prices'][$price['date']] = $price;
			}
		}
	}

	//logDebug("Rooms: " . print_r($rooms, true));
	//logDebug("Room changes: " . print_r($roomChanges, true));


	uasort($rooms, 'cmpRoomByOrder');

	return $rooms;
}

function cmpRoomByOrder($room1, $room2) {
	$retVal = ($room1['_order'] < $room2['_order'] ? -1 : ($room1['_order'] > $room2['_order'] ? 1 : 0));
	if($retVal === 0) {
		$retVal = ($room1['name'] < $room2['name'] ? -1 : ($room1['name'] > $room2['name'] ? 1 : 0));
	}
	return $retVal;
}


function isPrivate(&$roomData) {
	return $roomData['type'] == 'PRIVATE';
}

function isDorm(&$roomData) {
	return $roomData['type'] == 'DORM';
}

function isApartment(&$roomData) {
	return $roomData['type'] == 'APARTMENT';
}



function getPrice($arriveTS, $nights, &$roomData, $numOfPerson) {
	$oneDayTS = $arriveTS;
	$totalPrice = 0;
	logDebug("getting price for " . $roomData['name'] . " arriving " . date('Y-m-d', $arriveTS) . " staying for $nights nights for $numOfPerson guests");
	for($i = 0; $i < $nights; $i++) {
		$currYear = date('Y', $oneDayTS);
		$currMonth = date('m', $oneDayTS);
		$currDay = date('d', $oneDayTS);
		$oneDay =  date('Y/m/d', $oneDayTS);
		$oneDayTS += 24 * 60 * 60;
		if(isDorm($roomData)) {
			$bedPrice = getBedPrice($currYear, $currMonth, $currDay, $roomData) * $numOfPerson;
			logDebug("      for $oneDay the bedPrice for all the people: $bedPrice");
			$totalPrice += $bedPrice;
		} elseif(isPrivate($roomData)) {
			$roomPrice = getRoomPrice($currYear, $currMonth, $currDay, $roomData);
			logDebug("      for $oneDay the room price for all the people: $roomPrice");
			$totalPrice += $roomPrice;
		} elseif(isApartment($roomData)) {
			//logDebug('get apartment price');
			$price = getRoomPrice($currYear, $currMonth, $currDay, $roomData);
			//logDebug('room price: ' . $price);
			//logDebug('data: ' . print_r(array('num of person'=>$numOfPerson,'room beds'=>$roomData['num_of_beds'],'surcharge per bed'=>getSurchargePerBed($currYear, $currMonth, $currDay, $roomData)),true));
			$price = $price + $price * ($numOfPerson - 2) * getSurchargePerBed($currYear, $currMonth, $currDay, $roomData) / 100.0;
			logDebug("      for $oneDay the apt room price for $numOfPerson people: $price");
			$totalPrice += $price;
		}
	}

	logDebug("      returning the total of $totalPrice");
	return $totalPrice;
}




function getBedPrice($year, $month, $day, &$room) {
	$retVal = null;
	$month = __getNormalizedDate($month);
	$day = __getNormalizedDate($day);
	$dt = $year . '/' . $month . '/' . $day;
	if(isset($room['prices'][$dt])) {
		if(is_null($room['prices'][$dt]['price_per_bed']))
			$retVal = $room['prices'][$dt]['price_per_room'] / $room['num_of_beds'];
		else
			$retVal = $room['prices'][$dt]['price_per_bed'];
	} else {
		if(is_null($room['price_per_bed']))
			$retVal = $room['price_per_room'] / $room['num_of_beds'];
		else
			$retVal = $room['price_per_bed'];
	}
	return $retVal;
}



function getRoomPrice($year, $month, $day, &$room) {
	$retVal = null;
	$month = __getNormalizedDate($month);
	$day = __getNormalizedDate($day);
	$dt = $year . '/' . $month . '/' . $day;
	if(isset($room['prices'][$dt])) {
		if(is_null($room['prices'][$dt]['price_per_room']))
			$retVal = $room['prices'][$dt]['price_per_bed'] * $room['num_of_beds'];
		else
			$retVal = $room['prices'][$dt]['price_per_room'];
	} else {
		if(is_null($room['price_per_room']))
			$retVal = $room['price_per_bed'] * $room['num_of_beds'];
		else
			$retVal = $room['price_per_room'];
	}
	return $retVal;
}


function getSurchargePerBed($year, $month, $day, &$room) {
	$retVal = null;
	$month = __getNormalizedDate($month);
	$day = __getNormalizedDate($day);
	$dt = $year . '/' . $month . '/' . $day;
	if(isset($room['prices'][$dt])) {
		if(!is_null($room['prices'][$dt]['surcharge_per_bed']))
			$retVal = $room['prices'][$dt]['surcharge_per_bed'];
		else
			$retVal = $room['surcharge_per_bed'];
	} else {
		$retVal = $room['surcharge_per_bed'];
	}
	return $retVal;
}





function getNumOfAvailBeds(&$oneRoom, $oneDay, $excludeBookingWithId = null, $excludeBookingWithDescrId = null) {
	$occupBeds = getNumOfOccupBeds($oneRoom, $oneDay, $excludeBookingWithId, $excludeBookingWithDescrId);
	$avail = max(0, $oneRoom['num_of_beds'] - $occupBeds);
	return $avail;
}





function getNumOfOccupBeds(&$oneRoom, $oneDay, $excludeBookingWithId = null, $excludeBookingWithDescrId = null) {
	$occupBeds = 0;
	$oneDay = str_replace('-', '/', $oneDay);
	list($year, $month, $day) = explode('/', $oneDay);
	$month = __getNormalizedDate($month);
	$day = __getNormalizedDate($day);
	$oneDate = "$year/$month/$day";

	foreach($oneRoom['bookings'] as $oneBooking) {
		if($oneBooking['cancelled'] == 1) {
			continue;
		}
		if($oneBooking['id'] == $excludeBookingWithId) {
			continue;
		}
		if($oneBooking['description_id'] == $excludeBookingWithDescrId) {
			continue;
		}

		if(isset($oneBooking['changes'])) {
			$isThereRoomChangeForDay = false;
			foreach($oneBooking['changes'] as $oneChange) {	
				if($oneChange['date_of_room_change'] == $oneDate) {
					$isThereRoomChangeForDay = true;
				}
			}
			if($isThereRoomChangeForDay)
				continue;
		}

		// The real number of occupied beds will return regardless if the whole room is booked or not
		// If this is a private room or an appartment, the overbooking calculation will see that not all
		// beds are available and will make the whole room unavailable
		if(($oneBooking['first_night'] <= $oneDay) and ($oneBooking['last_night'] >= $oneDay)) {
//			if($oneBooking['booking_type'] == 'BED')
				$occupBeds += $oneBooking['num_of_person'];
//			else {
//				$occupBeds = $oneRoom['num_of_beds'];
//				break;
//			}
		}
	}

	foreach($oneRoom['room_changes'] as $oneRoomChange) {
		if($oneRoomChange['cancelled'] == 1) {
			continue;
		}
		if($oneRoomChange['booking_id'] == $excludeBookingWithId) {
			continue;
		}
		if($oneRoomChange['description_id'] == $excludeBookingWithDescrId) {
			continue;
		}


		// The real number of occupied beds will return regardless if the whole room is booked or not
		// If this is a private room or an appartment, the overbooking calculation will see that not all
		// beds are available and will make the whole room unavailable
		if($oneRoomChange['date_of_room_change'] == $oneDay) {
//			if($oneRoomChange['booking_type'] == 'BED')
				$occupBeds += $oneRoomChange['num_of_person'];
//			else {
//				$occupBeds = $oneRoom['num_of_beds'];
//				break;
//			}
		}
	}

	return $occupBeds;
}



/**
 * Returns an array where the key is the roomTypeId and the values is an array 
 * of dates for which there is no available space.
 */
function getOverbookings($numOfPersonForRoomType, $startDate, $endDate, &$rooms) {
	$startDate = str_replace('/', '-', $startDate);
	$endDate = str_replace('/', '-', $endDate);
	list($startYear, $startMonth, $startDay) = explode('-', $startDate);
	list($endYear, $endMonth, $endDay) = explode('-', $endDate);
	$startMonth = __getNormalizedDate($startMonth);
	$startDay = __getNormalizedDate($startDay);
	$endMonth = __getNormalizedDate($endMonth);
	$endDay = __getNormalizedDate($endDay);
	$startDate = "$startYear-$startMonth-$startDay";
	$endDate = "$endYear-$endMonth-$endDay";
	logDebug("getOverbookings() - From: $startDate, To: $endDate");
	$overbookings = array();
	foreach($numOfPersonForRoomType as $roomTypeId => $numOfPerson) {
		$datesUnavailable = array();
		logDebug("getOverbookings() - checking room type ($roomTypeId)");
		for($currDate = $startDate; $currDate <= $endDate; $currDate = date('Y-m-d', strtotime("$currDate +1 day"))) {
			$availableBeds = 0;
			foreach($rooms as $roomId => $roomData) {
	//			logDebug("for room id: $oneRoomId, the data is: " . print_r($rooms[$oneRoomId], true));
				if($roomData['room_type_id'] != $roomTypeId) {
					continue;
				}
				$beds = getNumOfAvailBeds($rooms[$roomId], $currDate);
				if((isPrivate($rooms[$roomId]) or isApartment($rooms[$roomId])) and $beds < $rooms[$roomId]['num_of_beds']) {
					$beds = 0;
				}
				$availableBeds += $beds;
			}
			logDebug("getOverbookings() - Available beds: $availableBeds for date: $currDate");

			if($availableBeds < $numOfPerson) {
				$datesUnavailable[$currDate] = $availableBeds;
			}
		}
		if(count($datesUnavailable) > 0) {
			$overbookings[$roomTypeId] = $datesUnavailable;
		}
	}

	logDebug("getOverbookings() - returning: " . print_r($overbookings, true));
	return $overbookings;
}


//
// Returns a assoc array with key: roomTypeId, value the booking data for
// the room type
// Input params:
//   location: hostel or lodge (used to get the selected room from SESSION
//   arriveDateTs: timestamp of arrive date
//   nights: number of nights
//   roomTypesData: result of the loadRoomTypes() function
//   rooms: result of the loadRooms() function
//   specialOffers: 
function getBookingsWithDiscount($location, $arriveDateTs, $nights, &$roomTypesData, &$rooms, $specialOffers) {
	$arriveDate = date('Y-m-d', $arriveDateTs);
	$retVal = array();
	foreach($roomTypesData as $roomTypeId => $roomType) {
		$numOfGuests = 0;
		//$numOfGuestsArr = array();
		$key = 'room_type_' . $location . '_' . $roomTypeId;
		if(isset($_REQUEST[$key])) {
			$_SESSION[$key] = $_REQUEST[$key];
		}
		if(isset($_SESSION[$key])) {
			$numOfGuests = $_SESSION[$key];
		}
		/*if(isApartment($roomType)) {
			for($i = 2; $i <= $roomType['num_of_beds']; $i++) {
				$key = 'room_type_' . $location . '_' . $roomTypeId . '_' . $i;
				if(isset($_REQUEST[$key])) {
					$_SESSION[$key] = $_REQUEST[$key];
				}
				if(isset($_SESSION[$key])) {
					$numOfGuests += $_SESSION[$key];
					$numOfGuestsArr[$i] = $_SESSION[$key];
				}
			}
		}*/
		if($numOfGuests > 0) {
			$name = $roomType['name'];
			$rtId = $roomType['id'];
			$roomData = getRoomData($rooms, $roomTypeId);
			if(!isApartment($roomType)) {
				$oneRoom = getBookingRoomData($arriveDateTs, $nights, $roomData, $roomType['num_of_beds'], $roomType, $arriveDate, $numOfGuests, $specialOffers);
				$retVal[$rtId] = $oneRoom;
			} else {
				$oneRoom = getBookingRoomData($arriveDateTs, $nights, $roomData, $numOfGuests, $roomType, $arriveDate, $numOfGuests/*Ap*/, $specialOffers);
				$retVal[$rtId] = $oneRoom;
			}
		}
	}

	return $retVal;
}

//
// caled from getBookingsWithDiscount()
// returns the assoc array that contains a booking based on the REQUEST data coming from the www site.
function getBookingRoomData($arriveDateTs, $nights, $roomData, $numOfBeds, $roomType, $arriveDate, $numOfGuests, $specialOffers) {
	$price = getPrice($arriveDateTs, $nights, $roomData, $numOfBeds);
	$price = $numOfGuests/$numOfBeds*getPrice($arriveDateTs, $nights, $roomData, $numOfBeds);
	list($discount, $selectedSo) = findSpecialOffer($specialOffers, $roomType, $nights, $arriveDate, $numOfBeds);
	// apply special offer
	$discountedPrice = $price;
	if($discount > 0) {
		$discountedPrice = $price * (100 - $discount) / 100;
	}
	$oneRoom = array('roomName' => $roomType['name'], 'roomTypeId' => $roomType['id'], 'numOfGuests' => $numOfGuests, 'price' => $price, 'discountedPrice' => $discountedPrice);
	if(!is_null($selectedSo)) {
		$oneRoom['specialOfferId'] = $selectedSo['id'];
	}
	return $oneRoom;
}

function findSpecialOffer(&$specialOffers, &$roomType, $nights, $arriveDate, $numOfBeds) {
	$discount = 0;
	$selectedSo = null;
	foreach($specialOffers as $so) {
		if(specialOfferApplies($so, $roomType, $nights, $arriveDate, $numOfBeds) and $so['discount_pct'] > $discount ) {
			$discount = $so['discount_pct'];
			$selectedSo = $so;
		}
	}
	return array($discount, $selectedSo);
}

function specialOfferApplies(&$specialOffer, &$roomType, $nights, $arriveDate, $numOfBedsInRoom = null) {
	if(is_null($specialOffer) or is_null($roomType)) {
		return false;
	}

	// logDebug("Checking if special offer applies to arrive date: $arriveDate, nights: $nights, roomType: " . $roomType['name'] . '[' . $roomType['id'] . '], special offer: ' . $specialOffer['name']);
	if(!is_null($specialOffer['room_type_ids']) and strpos($specialOffer['room_type_ids'],$roomType['id']) === false) {
		// logDebug("this special ofer is not applicable for this room type");
		return false;
	}

	if(!is_null($specialOffer['nights']) and $nights < $specialOffer['nights']) {
		// logDebug("this special ofer is applicable for at least " . $specialOffer['nights'] . " nights only");
		return false;
	}

	if(!is_null($specialOffer['valid_num_of_days_before_arrival'])) {
		$cutoffDate = date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $specialOffer['valid_num_of_days_before_arrival'] . ' day'));
		if($arriveDate > $cutoffDate) {
			// logDebug("this special ofer is applicable for atmost  " . $specialOffer['valid_num_of_days_before_arrival'] . " days before arrival");
			return false;
		}
	}

	if(!is_null($specialOffer['early_bird_day_count'])) {
		$cutoffDate = date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $specialOffer['early_bird_day_count'] . ' day'));
		if($arriveDate < $cutoffDate) {
			// logDebug("this special ofer is applicable for atleast  " . $specialOffer['valid_num_of_days_before_arrival'] . " days before arrival");
			return false;
		}
	}

	logDebug("this special offer applies for arrive date: $arriveDate, nights: $nights, roomType: " . $roomType['name'] . '[' . $roomType['id'] . '], special offer: ' . $specialOffer['name']);
	return true;
}


function getRoomData(&$rooms, $roomTypeId) {
	foreach($rooms as $rid => $roomData) {
		if($roomData['room_type_id'] == $roomTypeId) {
			return $roomData;
		}
	}
	return null;
}


/**
 * Returns the roomIds where the room_type_id of the room (or the array_keys of the room_types of the room) is the same as the roomTypeId in the parameter
 * The roomTypeId parameter may be an array of ids
 */
function getRoomIds(&$rooms, $roomTypeId) {
	$rids = array();
	$roomTypeIds = $roomTypeId;
	if(!is_array($roomTypeId)) {
		$roomTypeIds = array($roomTypeId);
	}
	foreach($roomTypeIds as $roomTypeId) {
		foreach($rooms as $rid => $roomData) {
			if(isRoomOfType($roomData, $roomTypeId)) {
				$rids[] = $rid;
			}
		}
	}
	return $rids;
}


// This method returns the booking data base fo the date and the selected rooms (and the people in each room).
// Parameters:
//   $numOfPersonForRoomType - array with each element has a structure: {roomTypeIds: array, numOfPerson: array}. 
//                             roomTypeIds: the list of room types 	where the one booking can be booked in.
//                             numOfPerson: list of number of people to be booke in each room. If there are 2 room bookings, this array will have 2 items
// creates two arrays: 
// $toBook that contains the roomId as a key and the value contains the number of people and the type (ROOM or BED) of the booking.
// $roomChanges where the key is a roomId and the value is an array of date => new_room_id
function getBookingData($numOfPersonForRoomType, $startDate, $endDate, &$rooms, &$roomTypes, &$specialOffers = null) {
	$startDate = str_replace('/', '-', $startDate);
	$endDate = str_replace('/', '-', $endDate);
	list($startYear, $startMonth, $startDay) = explode('-', $startDate);
	list($endYear, $endMonth, $endDay) = explode('-', $endDate);
	$startMonth = __getNormalizedDate($startMonth);
	$startDay = __getNormalizedDate($startDay);
	$endMonth = __getNormalizedDate($endMonth);
	$endDay = __getNormalizedDate($endDay);
	$startDate = "$startYear-$startMonth-$startDay";
	$endDate = "$endYear-$endMonth-$endDay";
	$numOfNights = round((strtotime($endDate) - strtotime($startDate)) / (60*60*24)) + 1;
	$toBook = array();
	$roomChanges = array(); // contains the rooms where some days has to be spent in another room
	logDebug("Inside getBookingData()");
	logDebug("booking interval: $startDate, $endDate, this is $numOfNights nights");
	logDebug("numOfPersonForRoomType = " .print_r($numOfPersonForRoomType, true));
	foreach($numOfPersonForRoomType as $oneBooking) {
		$roomTypeIds = $oneBooking['roomTypeIds'];
		$numOfPersonArray = $oneBooking['numOfPerson'];
		$roomType = $roomTypes[$roomTypeIds[0]];
		logDebug("getBookingData() - Checking room types: " . print_r($roomTypeIds, true) . ". There are " . print_r($numOfPersonArray, true) . " person for that room(s)");
		if(isApartment($roomType)) {
			logDebug("getBookingData() - in this apartment there are " . $roomType['num_of_beds'] . " beds in the first room type");
		}
		foreach($numOfPersonArray as $numOfPerson) {
			if($numOfPerson < 1) {
				continue;
			}
			// Collect the room that should not be used for booking because there are at least one day where the room (or beds) is not available
			$roomsNotToBook = array();
			for($currDate = $startDate; $currDate <= $endDate; $currDate = date('Y-m-d', strtotime("$currDate +1 day"))) {
				foreach($rooms as $roomId => $roomData) { 
					$roomTypeId = roomTypeMatch($roomTypeIds, $roomData);
					if(is_null($roomTypeId)) {
						continue;
					}
					$roomType = $roomTypes[$roomTypeId];
					$availableBeds = getNumOfAvailBeds($roomData, $currDate);
					if((isPrivate($roomType) or isApartment($roomType)) and $availableBeds != $roomType['num_of_beds']) {
						$roomsNotToBook[$roomId][] = $currDate;
						logDebug("getBookingData() - Not using room $roomId fpr $currDate");
					}
					if(isDorm($roomType) and $availableBeds < $numOfPerson) {
						$roomsNotToBook[$roomId][] = $currDate;
						logDebug("getBookingData() - Not using room $roomId for $currDate");
					}
				}
			}

			$numOfBedsBooked = 0;
			foreach($rooms as $roomId => $roomData) { 
				$roomTypeId = roomTypeMatch($roomTypeIds, $roomData);
				if(is_null($roomTypeId)) {
					continue;
				}
				$roomType = $roomTypes[$roomTypeId];
				logDebug("getBookingData() - Checking room:  " . $roomData['name'] . " (" . $roomId . ")");
				if($numOfPerson <= $numOfBedsBooked) {
					break;
				}
				if(!isset($roomsNotToBook[$roomId]) and !isset($toBook[$roomId])) {
					if(isPrivate($roomType)) {
						logDebug("getBookingData() - Booking private room to room $roomId for " . $roomData['num_of_beds'] . " (room type id: $roomTypeId)");
						$toBook[$roomId] = array('num_of_person' => $roomData['num_of_beds'], 'type' => 'ROOM', 'room_type_id' => $roomTypeId);
						$numOfBedsBooked += $roomData['num_of_beds'];
					} elseif(isApartment($roomType)) {
						logDebug("getBookingData() - Booking apartment to room $roomId for $numOfPerson (room type id: $roomTypeId)");
						$toBook[$roomId] = array('num_of_person' => $numOfPerson/*PerApt*/, 'type' => 'ROOM', 'room_type_id' => $roomTypeId);
						$numOfBedsBooked += $numOfPerson/*PerApt*/;
					} elseif(isDorm($roomType)) {
						$numOfPersonInDorm = $numOfPerson-$numOfBedsBooked;
						$toBook[$roomId] = array('num_of_person' => $numOfPersonInDorm, 'type' => 'BED', 'room_type_id' => $roomTypeId);
						logDebug("getBookingData() - Booking dorm room to room $roomId for $numOfPersonInDorm (room type id: $roomTypeId)");
						$numOfBedsBooked += $numOfPersonInDorm;
					}
				}
			}

			$numOfPersonLeftToBeBooked = $numOfPerson - $numOfBedsBooked;
			// For dorms put the rest of the bookings into the next room. The overbookings will be handled below.
			if(isDorm($roomType)) {
				logDebug("getBookingData(DORM) - For dorms put the rest of the bookings (num of beds left to be booked: $numOfPersonLeftToBeBooked into the next room. The overbookings will be handled");
				foreach($rooms as $roomId => $roomData) { 
					$roomTypeId = roomTypeMatch($roomTypeIds, $roomData);
					if(is_null($roomTypeId)) {
						continue;
					}
					if($numOfPerson <= $numOfBedsBooked) {
						break;
					}
					if(!isset($toBook[$roomId])) {
						$numOfPersonInDorm = $numOfPerson-$numOfBedsBooked;
						logDebug("getBookingData(DORM) - Booking the dorm room (" . $roomData['name'] . ", $roomId) with $numOfPersonInDorm guests");
						$toBook[$roomId] = array('num_of_person' => $numOfPersonInDorm, 'type' => 'BED', 'room_type_id' => $roomTypeId);
						$numOfBedsBooked += $numOfPersonInDorm;
					}
				}
			}

			
			logDebug("getBookingData() - So far there are $numOfBedsBooked beds booked, $numOfPersonLeftToBeBooked left to go.");
			logDebug("getBookingData() - So far the booked rooms " . print_r($toBook, true));
	
			// If there is no room that would be free for all the days, find the room that is the least booked, 
			// and add 'roomChanges' for the days that is booked, that is: it will find another room for the days where 
			// the mostly free room is booked.
			if((isPrivate($roomType) or isApartment($roomType)) and $numOfBedsBooked < $numOfPerson) {
				$roomsNotToBookInReverse = $roomsNotToBook;
				// sort the $roomsNotToBook in the order of the number of dates unavailable (ascending)
				uasort($roomsNotToBook, "sortByArraySize");
				// sort the $roomsNotToBookInReverse in the order of the number of dates unavailable (descending)
				uasort($roomsNotToBookInReverse, "sortByArraySizeDesc");
				logDebug('getBookingData(PRIVATE) - there is not enough room that would be free for all days, so select the room that is the least booked and create room changes for the occubed days');	
				logDebug('getBookingData(PRIVATE) - $roomsNotToBook = ' . print_r($roomsNotToBook, true));
				logDebug('getBookingData(PRIVATE) - $roomsNotToBookInReverse = ' . print_r($roomsNotToBookInReverse, true));
				foreach($roomsNotToBook as $roomId => $datesUnavailable) {
					if($numOfBedsBooked >= $numOfPerson) {
						break;
					}
					if(isset($toBook[$roomId])) {
						break;
					}
					$roomTypeId = $rooms[$roomId]['room_type_id'];
					$roomType = $roomTypes[$roomTypeId];
					$personToBook = (isPrivate($roomType) ? $roomType['num_of_beds'] : $numOfPerson/*PerApt*/);
					$toBook[$roomId] = array('num_of_person' => $personToBook, 'type' => 'ROOM', 'room_type_id' => $roomTypeId);
					logDebug("getBookingData(PRIVATE) - booking  $personToBook person into room " . $rooms[$roomId]['name'] . "($roomId) and there will be room changes for certain dates.");
	
					$numOfBedsBooked += $personToBook;
					// add roomChanges for the dates unavailable
					foreach($datesUnavailable as $oneDate) {
						$changeRoomId = findRoomForDate($roomsNotToBookInReverse, $oneDate);
						if(is_null($changeRoomId)) {
							continue;
						}
						$roomChanges[$roomId][] = array('room_id' => $changeRoomId, 'date' => $oneDate);
						logDebug("getBookingData(PRIVATE) - 	room change for $oneDate into room $changeRoomId");
						$roomsNotToBookInReverse[$changeRoomId][] = $oneDate;
						$roomsNotToBook[$changeRoomId][] = $oneDate;
						uasort($roomsNotToBook, "sortByArraySize");
						uasort($roomsNotToBookInReverse, "sortByArraySizeDesc");
					}
				}
			} elseif(isDorm($roomType)) {
				// Handle the overbookings for dorm: 
				// for each booking iterate through the dates:
				//   get the overbooking for that dorm room for that date
				//   iterate through again the rooms
				//     for the room with same roomtypeid, book the available beds until the overbooking is down to 0
				foreach($toBook as $bookedRoomId => $bookData) {
					for($currDate = $startDate; $currDate <= $endDate; $currDate = date('Y-m-d', strtotime("$currDate +1 day"))) {
						$availableBeds = getNumOfAvailBeds($rooms[$bookedRoomId], $currDate);
						$overbooking = $bookData['num_of_person'] - $availableBeds;
						if($overbooking > 0) {
							logDebug("getBookingData(DORM) - for room: $bookedRoomId, date: $currDate, there are $overbooking overbookings.");
							foreach($rooms as $roomId => $roomData) { 
								$roomTypeId = roomTypeMatch($roomTypeIds, $roomData);
								if(is_null($roomTypeId)) {
									continue;
								}
								if($roomId == $bookedRoomId) {
									continue;
								}
								$availableBeds = getNumOfAvailBeds($roomData, $currDate);
								if($availableBeds > 0) {
									$numPersonInThisRoom = min($availableBeds, $overbooking);
									logDebug("getBookingData(DORM) -     saving room change into room: $roomId, numOfPerson: $numPersonInThisRoom");	
									$roomChanges[$bookedRoomId][] = array('room_id' => $roomId, 'date' => $currDate, 'num_of_person' => $numPersonInThisRoom);
									$overbooking = $overbooking - $numPersonInThisRoom;
								}
								if($overbooking < 1) {
									break;
								}
							}
						}
					}
				}
			} // end of if(private) elseif(dorm)
		} // end of foreach(numOf_PersonArray)
	} // end of numOfPersonPerRoomType
	
	foreach($toBook as $roomId => &$bookingData) {
		$payment = getPrice(strtotime($startDate), $numOfNights, $rooms[$roomId], $bookingData['num_of_person']);
		if(!is_null($specialOffers)) {
			list($discount, $selectedSo) = findSpecialOffer($specialOffers, $roomTypes[$bookingData['room_type_id']], $numOfNights, $startDate, $bookingData['num_of_person']);
			// apply special offer
			$discountedPayment = $payment;
			if($discount > 0) {
				$discountedPayment = $payment * (100 - $discount) / 100;
			}
			$bookingData['price'] = $payment;
			$bookingData['discounted_price'] = $discountedPayment;
		}
	}
	
	logDebug("getBookingData() - The toBook array's content: " . print_r($toBook, true));
	logDebug("getBookingData() - The roomChanges array's content: " . print_r($roomChanges, true));

	return array($toBook, $roomChanges);
}

function roomTypeMatch($roomTypeIds, &$roomData) {
	foreach($roomTypeIds as $roomTypeId) {
		if(in_array($roomTypeId, array_keys($roomData['room_types']))) {
			return $roomTypeId;
		}
	}
	return null;
}

function findRoomForDate($roomsNotToBookInReverse, $oneDate) {
	foreach($roomsNotToBookInReverse as $roomId => $datesUnavail) {
		if(!in_array($oneDate, $datesUnavail))
			return $roomId;
	}
	return null;
}


function sortByArraySize($arr1, $arr2) {
	if(count($arr1) < count($arr2)) return -1;
	elseif(count($arr1) > count($arr2)) return 1;
	else return 0;
}

function sortByArraySizeDesc($arr1, $arr2) {
	return -1 * sortByArraySize($arr1, $arr2);
}

function getNumOfNights($startDate, $endDate) {
	$sts = strtotime($startDate);
	$ets = strtotime($endDate);
	return round(($ets - $sts) / (60*60*24)) + 1;
}

function saveBookings($toBook, $roomChanges, $startDate, $endDate, &$rooms, &$roomTypes, &$specialOffers, $descriptionId, $link, $priceForRoomType = null) {
	$startDate = str_replace('/', '-', $startDate);
	$endDate = str_replace('/', '-', $endDate);
	list($startYear, $startMonth, $startDay) = explode('-', $startDate);
	list($endYear, $endMonth, $endDay) = explode('-', $endDate);
	$startMonth = __getNormalizedDate($startMonth);
	$startDay = __getNormalizedDate($startDay);
	$endMonth = __getNormalizedDate($endMonth);
	$endDay = __getNormalizedDate($endDay);
	$startDate = "$startYear-$startMonth-$startDay";
	$endDate = "$endYear-$endMonth-$endDay";
	$numOfNights = round((strtotime($endDate) - strtotime($startDate)) / (60*60*24)) + 1;


	logDebug("saveBooking() - start: $startDate, end: $endDate");

	$bookingIds = array();
	$roomIdToBookingId = array();
	foreach($toBook as $roomId => $roomData) {
		$roomTypeId = $roomData['room_type_id'];
		$roomType = $roomTypes[$roomTypeId];
		$type = $roomData['type'];
		$numOfPerson = $roomData['num_of_person'];
		$specialOfferId = 'NULL';
		if(is_null($priceForRoomType) or !isset($priceForRoomType[$roomTypeId])) {
			$payment = getPrice(strtotime($startDate), $numOfNights, $rooms[$roomId], $numOfPerson);
			list($discount, $selectedSo) = findSpecialOffer($specialOffers, $roomType, $numOfNights, $startDate, $numOfPerson);
			// apply special offer
			$discountedPayment = $payment;
			if($discount > 0) {
				$discountedPayment = $payment * (100 - $discount) / 100;
				$specialOfferId = $selectedSo['id'];
			}
		} else {
			$discountedPayment = $priceForRoomType[$roomTypeId];
		}

		$time = date('Y-m-d H:i:s');
		if($type == 'ROOM') {
			logDebug('creating room booking');
			$id = createDbBooking($type, $numOfPerson, $discountedPayment, $roomId, $descriptionId, $time, $specialOfferId, $link, $rooms, $roomTypeId);
			if($id) {
				$roomIdToBookingId[$roomId] = $id;
				$bookingIds[] = $id;
			}
		} else {
			// The dorm booking must be saved as one booking for each bed this way the room changes 
			// can happen for individuals as well
			logDebug('creating bed booking');
			$roomIdToBookingId[$roomId] = array();
			for($i = 0; $i < $numOfPerson; $i++) {
				$id = createDbBooking($type, 1, $discountedPayment/$numOfPerson, $roomId, $descriptionId, $time, $specialOfferId, $link, $rooms, $roomTypeId);
				if($id) {
					$roomIdToBookingId[$roomId][] = $id;
					$bookingIds[] = $id;
				}
			}
		}
	}

	logDebug('roomIdToBookingId: ' . print_r($roomIdToBookingId, true));
	$bedRoomChange = array();
	foreach($roomChanges as $roomId => $arr) {
		$bookingId = $roomIdToBookingId[$roomId];
		foreach($arr as $oneChange) {
			$dateOfRoomChange = str_replace('-', '/', $oneChange['date']);
			$rid = $oneChange['room_id'];
			$type = $toBook[$roomId]['type'];
			if($type == 'ROOM') {
				createDbBookingRoomChange($bookingId, $dateOfRoomChange, $rid, $link, $rooms);
			} else {
				// If the booking is per bed (dormitory), then create one room change per person up until num_of_person
				// in this case $bookingId will be an array of bookings ids (one booking per one person)
				// There can be for one day 2 room changes (when the original room is full and the team must be
				// divided into two separate rooms $bedRoomChange holds for a given day how many booking have 
				// changed rooms, so if there is a new room change for the same day it wouldn't reassign the 
				// booking to a new room
				$numOfPerson = $oneChange['num_of_person'];
				if(!isset($bedRoomChange[$dateOfRoomChange])) {
					$bedRoomChange[$dateOfRoomChange] = 0;
				}
				for($i = 0; $i < $numOfPerson; $i++) {
					createDbBookingRoomChange($bookingId[$bedRoomChange[$dateOfRoomChange] + $i], $dateOfRoomChange, $rid, $link, $rooms);
				}
				$bedRoomChange[$dateOfRoomChange] += $numOfPerson;
			}
		}
	}

	return $bookingIds;
}


function createDbBooking($type, $numOfPerson, $discountedPayment, $roomId, $descriptionId, $time, $specialOfferId, $link, &$rooms, $rtid) {
	$sql = "INSERT INTO bookings (booking_type, num_of_person, room_payment, room_id, description_id, creation_time, special_offer_id, original_room_type_id) VALUES ('$type', '$numOfPerson', '$discountedPayment', $roomId, $descriptionId, '$time', $specialOfferId, $rtid)";
	logDebug($sql);
	if(!mysql_query($sql, $link)) {
		logError("Cannot save booking: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Could not save booking for room: ' . $rooms[$roomId]['name']);
		return false;
	}
	$id = mysql_insert_id($link);
	logDebug("Returning $id");
	return $id;
}

function createDbBookingRoomChange($bookingId, $dateOfRoomChange, $rid, $link, &$rooms) {
	$sql = "INSERT INTO booking_room_changes (booking_id, date_of_room_change, new_room_id) VALUES ($bookingId, '$dateOfRoomChange', $rid)";
	logDebug($sql);
	if(!mysql_query($sql, $link)) {
		logError("Cannot save booking's room change: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Could not save booking\'s room change for room: ' . $rooms[$roomId]['name']);
	}
}

function __getNormalizedDate($dt) {
	if(strlen($dt) < 2) {
		$dt = '0' . $dt;
	}
	return $dt;
}



function cmpOrdNm($room1, $room2) {
	if($room1['_order'] < $room2['_order']) {
		return -1;
	} elseif($room1['_order'] > $room2['_order']) {
		return 1;
	} elseif($room1['name'] < $room2['name']) {
		return -1;
	} elseif($room1['name'] > $room2['name']) {
		return 1;
	}
	return 0;
}

function verifyBlacklist($name, $email, $maverickEmail, $link) {
	$sql = "SELECT * FROM blacklist";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot verify blacklist items: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		logError("Cannot verify blacklist items: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		return;
	}
	while($row = mysql_fetch_assoc($result)) {
		if((strlen($row['email']) > 0 and $row['email'] == $email) or isNameMatch($row['name'], $name)) {
			logDebug("$name $email is on the blacklist");
			$msg = "Blacklist items matching $name or $email: ";
			$msg .= "Blacklisted name: " . $row['name'] . ", email: " . $row['email'] . ", source: " . $row['source'] . ", reason: " . $row['reason'] . "\n";
			sendMail($email, $name, $maverickEmail, "Maverick Reception", "Blacklisted booking received for name: $name or email $email", $msg);
			return;
		}
	}
}

function isNameMatch($dbName, $nameToCheck) {
	if($dbName == '' or is_null($dbName)) {
		return false;
	}
	$nameToCheck = strtolower($nameToCheck);
	$nameToCheck = stripAccents($nameToCheck);
	foreach(explode(' ', $dbName) as $namePart) {
		$namePart = stripAccents($namePart);
		$namePart = strtolower($namePart);
		if(trim($namePart) == '') {
			continue;
		}
		if(strstr($nameToCheck, $namePart) === false) {
			return false;
		}
	}
	return true;
}

?>
