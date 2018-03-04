<?php


class SpecialOfferDao {


	/**
	 * Loads the special offers from the DB containing the special offer dates
	 */
	public static function getAllSpecialOffers($link, $lang = 'eng') {
		$sql = "SELECT so.*, n.value AS title, d.value AS text, r.value AS room_name FROM special_offers so " .
			"INNER JOIN lang_text n ON (so.id=n.row_id AND n.table_name='special_offers' AND n.column_name='title' AND n.lang='$lang') " .
			"INNER JOIN lang_text d ON (so.id=d.row_id AND d.table_name='special_offers' AND d.column_name='text' AND d.lang='$lang') " .
			"LEFT OUTER JOIN lang_text r ON (so.id=r.row_id AND r.table_name='special_offers' AND r.column_name='room_name' AND r.lang='$lang')";
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

		$sql = "SELECT * FROM special_offer_dates";
		$result = mysql_query($sql, $link);
		while($row = mysql_fetch_assoc($result)) {
			if(isset($specialOffers[$row['special_offer_id']])) {
				$specialOffers[$row['special_offer_id']]['dates'][] = $row;
			}
		}

		return $specialOffers;
	}


	public static function getSpecialOffers($startDate, $endDate, $link, $lang) {
		return SpecialOfferDao::getSpecialOffersFromArray($startDate, $endDate, SpecialOfferDao::getAllSpecialOffers($link, $lang));
	}

	public static function getSpecialOffersFromArray($startDate, $endDate, $specialOffers) {
		if(!is_null($startDate)) {
			$startDate = str_replace('/','-',$startDate);
		}
		$endDate = str_replace('/','-',$endDate);
		
		$retVal = array();
		logDebug("Special offer matching, startDate: $startDate, endDate: $endDate");
		foreach($specialOffers as $soId => $so) {
			if(SpecialOfferDao::isSpecialOfferWithinDates($startDate, $endDate, $so)) {
				logDebug("\tDates before: " . print_r($so['dates'], true));
				$retVal[$soId] = SpecialOfferDao::modifyDates($so, $startDate, $endDate);
				logDebug("\tDates after: " . print_r($retVal[$soId]['dates'], true));
			}
		}

		return $retVal;
	}

	public static function modifyDates($specialOffer, $startDate, $endDate) {
		$dates = array();
		foreach($specialOffer['dates'] as $oneDate) {
			if(($oneDate['start_date'] <= $startDate) and ($oneDate['end_date'] >= $endDate)) {
				$dates[] = $oneDate;
			}
		}
		$specialOffer['dates'] = $dates;
		return $specialOffer;
	}

	public static function isSpecialOfferWithinDates($startDate, $endDate, $specialOffer) {
		foreach($specialOffer['dates'] as $soDate) {
			if($soDate['end_date'] > $endDate and (is_null($startDate) or $soDate['start_date'] <= $startDate)) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Find the best (biggest discount) special offer within the list of special offers passed in that matched the parameters
	 */
	public static function findSpecialOffer(&$specialOffers, &$roomType, $nights, $arriveDate, $numOfBeds) {
		$discount = 0;
		$selectedSo = null;
		foreach($specialOffers as $so) {
			if(SpecialOfferDao::doesSpecialOfferApply($so, $roomType, $nights, $arriveDate, $numOfBeds) and $so['discount_pct'] > $discount ) {
				$discount = $so['discount_pct'];
				$selectedSo = $so;
			}
		}
		return array($discount, $selectedSo);
	}

	public static function doesSpecialOfferApply(&$specialOffer, &$roomType, $nights, $arriveDate, $numOfBedsInRoom = null) {
		if(is_null($specialOffer) or is_null($roomType)) {
			return false;
		}

		//logDebug("Checking if special offer applies to arrive date: $arriveDate, nights: $nights, roomType: " . $roomType['name'] . '[' . $roomType['id'] . '], special offer: ' . $specialOffer['name']);
		if(!is_null($specialOffer['room_type_ids']) and (strpos($specialOffer['room_type_ids'],"" . $roomType['id']) === false)) {
			//logDebug("this special offer is not applicable for this room type (" . $roomType['id'] . "). Valid room types: " . $specialOffer['room_type_ids']);
			return false;
		}

		if(!is_null($specialOffer['nights']) and $nights < $specialOffer['nights']) {
			//logDebug("this special ofer is applicable for at least " . $specialOffer['nights'] . " nights only");
			return false;
		}

		if(!is_null($specialOffer['valid_num_of_days_before_arrival'])) {
			$cutoffDate = date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $specialOffer['valid_num_of_days_before_arrival'] . ' day'));
			if($arriveDate > $cutoffDate) {
				//logDebug("this special ofer is applicable for atmost  " . $specialOffer['valid_num_of_days_before_arrival'] . " days before arrival");
				return false;
			}
		}

		if(!is_null($specialOffer['early_bird_day_count'])) {
			$cutoffDate = date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $specialOffer['early_bird_day_count'] . ' day'));
			if($arriveDate < $cutoffDate) {
				//logDebug("this special ofer is applicable for at least  " . $specialOffer['valid_num_of_days_before_arrival'] . " days before arrival");
				return false;
			}
		}

		//logDebug("this special offer applies for arrive date: $arriveDate, nights: $nights, roomType: " . $roomType['name'] . '[' . $roomType['id'] . '], special offer: ' . $specialOffer['name']);
		return true;
	}

}
?>