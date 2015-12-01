<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");
require(ADMIN_BASE_DIR . "common_booking.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


header("Location: " . $_SERVER['HTTP_REFERER']);

$link = db_connect();

$startDate = $_SESSION['pricing_start_date'];
$endDate = $_SESSION['pricing_end_date'];
$startDateDash = str_replace('-', '/', $startDate);
$endDateDash = str_replace('-', '/', $endDate);

list($startYear, $startMonth, $startDay) = explode('-', $startDate);
list($endYear, $endMonth, $endDay) = explode('-', $endDate);

if(isset($_REQUEST['sync'])) {
	header('Location: ' . RECEPCIO_BASE_URL . "synchro/main.php?start_date=$startDate&end_date=$endDate&sites[]=myallocator");
} else {
	header("Location: " . $_SERVER['HTTP_REFERER']);
}

$roomTypes = loadRoomTypesWithAvailableBeds($link, $startDate, $endDate);
$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

$sql = "SELECT * FROM prices_for_date WHERE date>='$startDateDash' AND date<='$endDateDash'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get old room prices in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

$priceRows = array();
while($row = mysql_fetch_assoc($result)) {
	$roomTypeId = $row['room_type_id'];
	$currDate = str_replace('/','-',$row['date']);
	if(!isset($_REQUEST[$roomTypeId . '|' . $currDate])) {
		continue;
	}
	if(!isset($priceRows[$roomTypeId])) {
		$priceRows[$roomTypeId] = array();
	}
	$priceRows[$roomTypeId][$currDate] = $row;
}



$newPriceMessage = '';
$historyMessage = '';
$historyValues = array();
$newPriceValues = array();

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
		$spb = null;
		if(isset($_REQUEST['spb' . $roomTypeId . '|' . $currDate])) {
			$spb = $_REQUEST['spb' . $roomTypeId . '|' . $currDate];
		}
		$newPricePerRoom = ((isPrivate($roomTypeData) or isApartment($roomTypeData)) ? $val : null);
		$newPricePerBed = (isDorm($roomTypeData) ? $val : null);


		if(isset($priceRows[$roomTypeId][$currDate])) {
			$priceRow = $priceRows[$roomTypeId][$currDate];
			if(isSamePrice($newPricePerRoom, $newPricePerBed, $spb, $priceRow)) {
				continue;
			}

			list($y,$m,$d) = explode('-', $currDate);
			$bookings = getBookings($roomTypeId, $rooms, $currDate, $currDate);
			$avgNumOfBeds = getAvgNumOfBedsOccupied($bookings, $currDate, $currDate);
			$occupancy = round($avgNumOfBeds / $roomTypeData['available_beds'] * 100);

			$historyValues[] = "('" . $priceRow['date'] . "', " . 
				(is_null($priceRow['price_per_room']) ? 'NULL' : $priceRow['price_per_room']) . ", " . 
				(is_null($priceRow['price_per_bed']) ? 'NULL' : $priceRow['price_per_bed']) . ", " . 
				$priceRow['room_type_id'] . ", " . 
				(is_null($priceRow['price_set_date']) ? 'NULL' : '\''.$priceRow['price_set_date'].'\'') . ", " .
				"'$todaySlash', $occupancy, " .
				(is_null($priceRow['surcharge_per_bed']) ? 'NULL' : '\''.$priceRow['surcharge_per_bed'].'\'') . ")";
			$historyMessage .= "New history item: " . $roomTypeData['name'] . " " . $priceRow['date'] . "<br>\n";

		}


		$sql = "DELETE FROM prices_for_date WHERE room_type_id=$roomTypeId AND date='$currDateSlash'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot delete old room prices in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}

		$newPriceValues[] = "($roomTypeId, '$currDateSlash', " . (is_null($newPricePerRoom) ? 'NULL' : $newPricePerRoom) . ", " . (is_null($newPricePerBed) ? 'NULL' : $newPricePerBed) . ", '$todaySlash', " . (is_null($spb) ? 'NULL' : $spb) . ")";
		$newPriceMessage .= "New price saved: " . $roomTypeData['name'] . " $currDate <br>\n";

	}
}

if(count($historyValues) > 0) {
	$sql = "INSERT INTO prices_for_date_history (date, price_per_room, price_per_bed, room_type_id, price_set_date, price_unset_date, occupancy, surcharge_per_bed) VALUES " . implode(', ', $historyValues);
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot create room prices history in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		set_message('price history saved (' . count($historyValues) . ' rows inserted)');
	}
} else {
	set_message('No historical data saved');
}

if(count($newPriceValues) > 0) {
	$sql = "INSERT INTO prices_for_date (room_type_id, date, price_per_room, price_per_bed, price_set_date, surcharge_per_bed) VALUES " . implode(', ', $newPriceValues);
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot create new room prices in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		set_message('new prices saved (' . count($newPriceValues) . ' rows inserted)');
	}
} else {
	set_message('No price data saved');
}

set_message($newPriceMessage);
set_message($historyMessage);


mysql_close($link);


function isSamePrice($newPricePerRoom, $newPricePerBed, $spb, $priceRow) {
	if(!is_null($newPricePerRoom) and ($newPricePerRoom != $priceRow['price_per_room'])) {
		return false;
	}
	if(!is_null($newPricePerBed) and ($newPricePerBed != $priceRow['price_per_bed'])) {
		return false;
	}
	if(!is_null($spb) and ($spb != $priceRow['surcharge_per_bed'])) {
		return false;
	}
	return true;
}

?>
