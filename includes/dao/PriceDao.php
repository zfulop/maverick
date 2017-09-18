<?php

$pricedao_priceForDate = array();

class PriceDao {


	public static function getPrice($arriveTS, $nights, $roomTypeId, $numOfPerson, $link) {
		$oneDayTS = $arriveTS;
		$totalPrice = 0;
		$roomType = RoomDao::getRoomType($roomTypeId, 'eng', $link);
		logDebug("getting price for " . $roomType['name'] . " arriving " . date('Y-m-d', $arriveTS) . " staying for $nights nights for $numOfPerson guests");
		for($i = 0; $i < $nights; $i++) {
			$currYear = date('Y', $oneDayTS);
			$currMonth = date('m', $oneDayTS);
			$currDay = date('d', $oneDayTS);
			$oneDay =  date('Y/m/d', $oneDayTS);
			$oneDayTS += 24 * 60 * 60;
			if(RoomDao::isDorm($roomType)) {
				$bedPrice = PriceDao::getBedPrice($currYear, $currMonth, $currDay, $roomTypeId, $link) * $numOfPerson;
				logDebug("      for $oneDay the bedPrice for all the people: $bedPrice");
				$totalPrice += $bedPrice;
			} elseif(RoomDao::isPrivate($roomType)) {
				$roomPrice = PriceDao::getRoomPrice($currYear, $currMonth, $currDay, $roomTypeId, $link);
				logDebug("      for $oneDay the room price for all the people: $roomPrice");
				$totalPrice += $roomPrice;
			} elseif(RoomDao::isApartment($roomType)) {
				//logDebug('get apartment price');
				$price = PriceDao::getRoomPrice($currYear, $currMonth, $currDay, $roomTypeId, $link);
				//logDebug('room price: ' . $price);
				//logDebug('data: ' . print_r(array('num of person'=>$numOfPerson,'room beds'=>$roomData['num_of_beds'],'surcharge per bed'=>getSurchargePerBed($currYear, $currMonth, $currDay, $roomData)),true));
				$price = $price + $price * ($numOfPerson - 2) * PriceDao::getSurchargePerBed($currYear, $currMonth, $currDay, $roomTypeId, $link) / 100.0;
				logDebug("      for $oneDay the apt room price for $numOfPerson people: $price");
				$totalPrice += $price;
			}
		}

		logDebug("      returning the total of $totalPrice");
		return $totalPrice;
	}


	public static function getBedPrice($year, $month, $day, $roomTypeId, $link) {
		$retVal = null;
		$roomTypeData = RoomDao::getRoomType($roomTypeId, 'eng', $link);
		$priceData = PriceDao::__getPriceForDate($year, $month, $day, $roomTypeId, $link);
		if(!is_null($priceData)) {
			if(is_null($priceData['price_per_bed']))
				$retVal = $priceData['price_per_room'] / $roomTypeData['num_of_beds'];
			else
				$retVal = $priceData['price_per_bed'];
		} else {
			if(is_null($roomTypeData['price_per_bed']))
				$retVal = $roomTypeData['price_per_room'] / $roomTypeData['num_of_beds'];
			else
				$retVal = $roomTypeData['price_per_bed'];
		}
		return $retVal;
	}



	public static function getRoomPrice($year, $month, $day, $roomTypeId, $link) {
		$retVal = null;
		$roomTypeData = RoomDao::getRoomType($roomTypeId, 'eng', $link);
		$priceData = PriceDao::__getPriceForDate($year, $month, $day, $roomTypeId, $link);
		if(!is_null($priceData)) {
			if(is_null($priceData['price_per_room']))
				$retVal = $priceData['price_per_bed'] * $roomTypeData['num_of_beds'];
			else
				$retVal = $priceData['price_per_room'];
		} else {
			if(is_null($roomTypeData['price_per_room']))
				$retVal = $roomTypeData['price_per_bed'] * $roomTypeData['num_of_beds'];
			else
				$retVal = $roomTypeData['price_per_room'];
		}
		return $retVal;
	}


	public static function getSurchargePerBed($year, $month, $day, $roomTypeId, $link) {
		$retVal = null;
		$roomTypeData = RoomDao::getRoomType($roomTypeId, 'eng', $link);
		$priceData = PriceDao::__getPriceForDate($year, $month, $day, $roomTypeId, $link);
		if(!is_null($priceData)) {
			$retVal = $priceData['surcharge_per_bed'];
		} else {
			$retVal = $roomTypeData['surcharge_per_bed'];
		}
		return $retVal;
	}


	/**
	 */
	static function __getPriceForDate($year, $month, $day, $roomTypeId, $link) {
		global $pricedao_priceForDate;
		if(intval($month) < 10) { $month = '0' . intval($month); }
		if(intval($day) < 10) { $day = '0' . intval($day); }
		$dateStr = "$year/$month/$day";
		if(!isset($pricedao_priceForDate[$dateStr])) {
			$sql = "SELECT * FROM prices_for_date WHERE date like '$year%'";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot get prices. Error: " . mysql_error($link) . " (SQL: $sql)");
				return null;
			}
			$pricedao_priceForDate[$year] = array();
			while($row = mysql_fetch_assoc($result)) {
				$pricedao_priceForDate[$row['date']][$row['room_type_id']] = $row;
			}
		}

		if(!isset($pricedao_priceForDate[$dateStr])) {
			logError("No price set for date: $dateStr");
			return null;
		}
		if(!isset($pricedao_priceForDate[$dateStr][$roomTypeId])) {
			logError("No price set for date: $dateStr and room type id: $roomTypeId");
			return null;
		}

		return $pricedao_priceForDate[$dateStr][$roomTypeId];
	}

	
	/**
	 * Preloads the price data from the price file into memory so subsequent uses would be faster. If the 
	 */
	public static function loadPriceForDate($startTs, $endTs, $location) {
		global $pricedao_priceForDate;
		$startMonth = date('Y-m', $startTs) . '-1';
		$endMonth = date('Y-m', $endTs) . '-1';
		for($currDate = $startDateMonth; $currDate <= $endDateMonth; $currDate = date('Y-m', strtotime($currDate . ' +1 month')) . '-1') {
			$currMonth = substr($currDate, 0, 7);
			$file = JSON_DIR . $location . '/prices_' . $currMonth . '.json';
			if(!file_exists($file)) {
				logError("Error when loading price data from file, file does not exists: $file");
				continue;
			}
			$data = json_decode(file_get_contents($file), true);
			foreach($data as $price) {
				$pricedao_priceForDate[$price['date']][$price['room_type_id']] = $price;
			}
		}
	}

	/**
	 * Extract the price data from the DB into a file for the API to use rather than using the DB.
	 */
	public static function extractPriceToFile($startDate, $endDate, $link, $location) {
		logDebug("Extracting price data from DB into file for the period: $startDate, $endDate");
		$startDateMonth = date('Y-m', strtotime($startDate)) . '-1';
		$endDateMonth = date('Y-m', strtotime($endDate)) . '-1';
		for($currDate = $startDateMonth; $currDate <= $endDateMonth; $currDate = date('Y-m', strtotime($currDate . ' +1 month')) . '-1') {
			$currMonth = substr($currDate, 0, 7);
			$currMonthDash = str_replace('-', '/', $currMonth);
			$file = JSON_DIR . $location . '/prices_' . $currMonth . '.json';
			$sql = "SELECT * FROM prices_for_date WHERE date LIKE '$currMonthDash%'";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot get prices for month: $currMonth in mgmt interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			} else {
				$prices = array();
				while($row=mysql_fetch_assoc($result)) {
					$prices[] = $row;
				}
				logDebug("Saving prices for the month of $currMonth to file: $file. There are " . count($prices) . " price record for the month");
				$data = json_encode($prices, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
				file_put_contents($file, $data);
			}
		}
	}
}

	

?>