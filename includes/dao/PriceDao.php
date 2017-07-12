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
	 */
	public static function loadPriceForDate($startTs, $endTs, $link) {
		global $pricedao_priceForDate;
		$startDate = date('Y/m/d', $startTs);
		$endDate = date('Y/m/d', $startTs);
		$sql = "SELECT * FROM prices_for_date WHERE date>='$startDate' and date<='$endDate'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get prices. Error: " . mysql_error($link) . " (SQL: $sql)");
			throw new Exception("Cannot load prices: " . mysql_error($link));
		}
		while($row = mysql_fetch_assoc($result)) {
			$pricedao_priceForDate[$row['date']][$row['room_type_id']] = $row;
		}
	}


	

}
?>