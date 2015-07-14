<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");
require("common_booking.php");

header("Location: " . $_SERVER['HTTP_REFERER']);

$link = db_connect();

$startDate = $_SESSION['pricing_start_date'];
$endDate = $_SESSION['pricing_end_date'];

list($startYear, $startMonth, $startDay) = explode('-', $startDate);
list($endYear, $endMonth, $endDay) = explode('-', $endDate);

$roomTypes = loadRoomTypesWithAvailableBeds($link, $startDate, $endDate);

$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);


$todaySlash = date('Y/m/d H:i:s');
for($currDate = $startDate; $currDate <= $endDate; $currDate = date('Y-m-d', strtotime($currDate . ' +1 day'))) {
	$currDateSlash = str_replace('-','/',$currDate);
	foreach($roomTypes as $roomTypeId => $roomTypeData) {
		if(!isset($_REQUEST[$roomTypeId . '|' . $currDate])) {
			continue;
		}

		$val = $_REQUEST[$roomTypeId . '|' . $currDate];
		if($val <= 0) {
			continue;
		}
		$dpb = null;
		if(isset($_REQUEST['dpb' . $roomTypeId . '|' . $currDate])) {
			$dpb = $_REQUEST['dpb' . $roomTypeId . '|' . $currDate];
		}

		$sql = "SELECT * FROM prices_for_date WHERE room_type_id=$roomTypeId AND date='$currDateSlash'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get old room prices in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
		if(mysql_num_rows($result) > 0) {
			$priceRow = mysql_fetch_assoc($result);
			$newPricePerRoom = ((isPrivate($roomTypeData) or isApartment($roomTypeData)) ? $val : null);
			$newPricePerBed = (isDorm($roomTypeData) ? $val : null);
			if(isSamePrice($newPricePerRoom, $newPricePerBed, $dpb, $priceRow)) {
				continue;
			}

			list($y,$m,$d) = explode('-', $currDate);
			$bookings = getBookings($roomTypeId, $rooms, $currDate, $currDate);
			$avgNumOfBeds = getAvgNumOfBedsOccupied($bookings, $currDate, $currDate);
			$occupancy = round($avgNumOfBeds / $roomTypeData['available_beds'] * 100);

			$sql = "INSERT INTO prices_for_date_history (date, price_per_room, price_per_bed, room_type_id, price_set_date, price_unset_date, occupancy, discount_per_bed) VALUES ('" . $priceRow['date'] . "', " . 
				(is_null($priceRow['price_per_room']) ? 'NULL' : $priceRow['price_per_room']) . ", " . 
				(is_null($priceRow['price_per_bed']) ? 'NULL' : $priceRow['price_per_bed']) . ", " . 
				$priceRow['room_type_id'] . ", " . 
				(is_null($priceRow['price_set_date']) ? 'NULL' : '\''.$priceRow['price_set_date'].'\'') . ", " .
				"'$todaySlash', $occupancy, " .
				(is_null($priceRow['discount_per_bed']) ? 'NULL' : '\''.$priceRow['discount_per_bed'].'\'') . ")";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot create room prices history in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			}
		}


		$sql = "DELETE FROM prices_for_date WHERE room_type_id=$roomTypeId AND date='$currDateSlash'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot delete old room prices in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}

		$sql = "INSERT INTO prices_for_date (room_type_id, date, price_per_room, price_per_bed,	price_set_date, discount_per_bed) VALUES ($roomTypeId, '$currDateSlash', " . (is_null($newPricePerRoom) ? 'NULL' : $newPricePerRoom) . ", " . (is_null($newPricePerBed) ? 'NULL' : $newPricePerBed) . ", '$todaySlash', " . (is_null($dpb) ? 'NULL' : $dpb) . ")";
		if(!mysql_query($sql, $link)) {
			trigger_error("Cannot save room price: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			set_error("Cannot save price for day: $currDateSlash and room(s): " . $roomTypeData['name']);
		} else {
			set_message("Price saved for day: $currDateSlash and room(s): " . $roomTypeData['name'] . " ppr:$newPricePerRoom, pbd:$newPricePerBed, dpb:$dpb, old ppr: " . $priceRow['price_per_room'] . ",old ppb:" . $priceRow['price_per_bed'] . ", old dpb:" . $priceRow['discount_per_bed']);
		}
	}
}


mysql_close($link);


function isSamePrice($newPricePerRoom, $newPricePerBed, $dpb, $priceRow) {
	if(!is_null($newPricePerRoom) and ($newPricePerRoom != $priceRow['price_per_room'])) {
		return false;
	}
	if(!is_null($newPricePerBed) and ($newPricePerBed != $priceRow['price_per_bed'])) {
		return false;
	}
	if(!is_null($dpb) and ($dpb != $priceRow['discount_per_bed'])) {
		return false;
	}
	return true;
}

?>
