<?php

require("includes.php");
require("../recepcio/room_booking.php");
require("../admin/common_booking.php");

header("Location: " . $_SERVER['HTTP_REFERER']);

$link = db_connect();

$roomTypeId = intval($_REQUEST['room_type_id']);
$sql = "select * from room_types where id=$roomTypeId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get room type in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error("Cannot save price: cannot get room type");
	mysql_close($link);
	return;
}
$roomType = mysql_fetch_assoc($result);


$startYear = $_REQUEST['start_year'];
$startMonth = $_REQUEST['start_month'];
$startDay = $_REQUEST['start_day'];
$endYear = $_REQUEST['end_year'];
$endMonth = $_REQUEST['end_month'];
$endDay = $_REQUEST['end_day'];
$_SESSION['room_price_start_year'] = $startYear;
$_SESSION['room_price_start_month'] = $startMonth;
$_SESSION['room_price_start_day'] = $startDay;
$_SESSION['room_price_end_year'] = $endYear;
$_SESSION['room_price_end_month'] = $endMonth;
$_SESSION['room_price_end_day'] = $endDay;
if(isset($_REQUEST['days'])) {
	$days = $_REQUEST['days'];
} else {
	$days = array(1,2,3,4,5,6,7);
}
$_SESSION['room_price_days'] = $days;
if(strlen($startMonth) < 2)
	$startMonth = '0' . $startMonth;	
if(strlen($startDay) < 2)
	$startDay = '0' . $startDay;	
if(strlen($endMonth) < 2)
	$endMonth = '0' . $endMonth;	
if(strlen($endDay) < 2)
	$endDay = '0' . $endDay;	


$startDate = "$startYear-$startMonth-$startDay";
$endDate = "$endYear-$endMonth-$endDay";
$todaySlash = date('Y/m/d');
for($currDate = $startDate; $currDate <= $endDate; $currDate = date('Y-m-d', strtotime($currDate . ' +1 day'))) {
	$currDayOfWeek = date('N', strtotime($currDate));
	if(!in_array($currDayOfWeek, $days)) {
		set_message("Price setting skipped for date: $currDate");
		continue;
	}
	$dateStr = str_replace('-', '/', $currDate);
	if(isset($_REQUEST['price'])) {
		$val = intval($_REQUEST['price']);
		$dpb = intval($_REQUEST['discount_per_bed']);
	} else {
		$val = intval($_REQUEST[$dateStr]);
		if(isset($_REQUEST['dpb_' . $dateStr])) {
			$dpb = intval($_REQUEST['dpb_' . $dateStr]);
		} else {
			$dpb = 0;
		}
	}
	if($val > 0) {
		$sql = "SELECT * FROM prices_for_date WHERE room_type_id=$roomTypeId AND date='$dateStr'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get old room prices in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
		if(mysql_num_rows($result) > 0) {
			list($y,$m,$d) = explode('-', $currDate);
			$roomTypes = loadRoomTypesWithAvailableBeds($link, $currDate, $currDate);
			$rooms = loadRooms($y, $m, $d, $y, $m, $d, $link);
			$bookings = getBookings($roomTypeId, $rooms, $currDate, $currDate);
			$avgNumOfBeds = getAvgNumOfBedsOccupied($bookings, $currDate, $currDate);
			$occupancy = round($avgNumOfBeds / $roomTypes[$roomTypeId]['available_beds'] * 100);

			$priceRow = mysql_fetch_assoc($result);
			$sql = "INSERT INTO prices_for_date_history (date, price_per_room, price_per_bed, room_type_id, price_set_date, price_unset_date, occupancy, discount_per_bed) VALUES ('" . $priceRow['date'] . "', " . 
				(is_null($priceRow['price_per_room']) ? 'NULL' : $priceRow['price_per_room']) . ", " . 
				(is_null($priceRow['price_per_bed']) ? 'NULL' : $priceRow['price_per_bed']) . ", " . 
				$priceRow['room_type_id'] . ", " . 
				(is_null($priceRow['price_set_date']) ? 'NULL' : '\''.$priceRow['price_set_date'].'\'') . ", " .
				"'$todaySlash', $occupancy, $dpb)";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot create room prices history in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			}
		}


		$sql = "DELETE FROM prices_for_date WHERE room_type_id=$roomTypeId AND date='$dateStr'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot delete old room prices in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}

		// For private and apartment set the room price, for dorm set the bed price.
		$sql = "INSERT INTO prices_for_date (room_type_id, date, price_per_room, price_per_bed,	price_set_date, discount_per_bed) VALUES ($roomTypeId, '$dateStr', " . ($roomType['type'] != 'DORM' ? $val : 'NULL') . ", " . ($roomType['type'] == 'DORM' ? $val : 'NULL') . ", '$todaySlash', $dpb)";
		if(!mysql_query($sql, $link)) {
			trigger_error("Cannot save room price: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			set_error("Cannot save price for day: $dateStr and room(s): " . $roomType['name']);
		} else {
			set_message("Price saved for day: $dateStr and room(s): " . $roomType['name']);
		}
	}
}


mysql_close($link);



?>
