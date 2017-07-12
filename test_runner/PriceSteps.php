<?php

function givenThereAreNoPricesSet($table) {
	$link = db_connect('teszt_hostel');
	foreach($table['rows'] as $row) {
		$startDate = str_replace("-","/",$row['start date']);
		$endDate = str_replace("-","/",$row['end date']);
		echo "Deleting room prices from $startDate to $endDate\n";
		$sql = "DELETE FROM prices_for_date WHERE date<='$endDate' AND date>='$startDate'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			$err = mysql_error($link);
			mysql_close($link);
			throw new Exception("Cannot delete price for a date interval. Error: $err. SQL: $sql");
		}
	}
	mysql_close($link);
}

function givenTheFollowingPricesAreSet($table) {
	echo "Setting room prices for dates\n";
	$link = db_connect('teszt_hostel');
	$sql = "SELECT * FROM room_types";
	$result = mysql_query($sql, $link);
	$rooms = array();
	while($row = mysql_fetch_assoc($result)) {
		$rooms[$row['name']] = $row;
	}
	foreach($table['rows'] as $row) {
		$roomTypeId = $row['id'];
		$roomTypeName = $row['room type'];
		if(!isset($rooms[$roomTypeName])) {
			mysql_close($link);
			throw new Exception("Cannot find room type: $roomTypeName when setting price.");
		}
		if($rooms[$roomTypeName]['id'] != $roomTypeId) {
			mysql_close($link);
			throw new Exception("Room type id and name do not match for: $roomType and id: $roomTypeId.");
		}
		$roomType = $rooms[$roomTypeName];
		
		foreach($table['titles'] as $title) {
			if($title == 'room type' or $title == 'id' or $title == '') {
				continue;
			}
			$priceForDate = trim($row[$title]);
			$priceDate = str_replace('-','/',$title);
			if(intval($priceForDate) > 0) {
				$sql = "INSERT INTO prices_for_date (room_type_id, date, price_set_date, " . ($roomType['type'] == 'DORM' ? 'price_per_bed' : 'price_per_room') .
					") VALUES ($roomTypeId, '$priceDate', '2001-01-01 00:00:00', $priceForDate)";
				$result = mysql_query($sql, $link);
				if(!$result) {
					$err = mysql_error($link);
					mysql_close($link);
					throw new Exception("Cannot save price for a date: $priceDate, room type: $roomTypeName($roomTypeId), price: $priceForDate. Error: $err. SQL: $sql");
				}
			}
		}
	}
	mysql_close($link);
}



 ?>