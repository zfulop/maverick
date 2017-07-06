<?php

$apiOutput = '';

function whenIAskForTheRoomsFromTheAPI($table) {
	global $apiOutput;
	$apiOutput = executeApi('EUR', 'rooms', array());
}

function whenIAskForTheAvailabilityFromTheAPI($table) {
	global $apiOutput;
	$params = array();
	$params['from'] = $table['rows'][0]['from'];
	$params['to'] = $table['rows'][0]['to'];
	$currency = $table['rows'][0]['currency'];
	$apiOutput = executeApi($currency, 'availability', $params);
}

function executeApi($currency, $action, $params) {
	$cmd = '/home/maveric3/reception-dev/api.php -lang eng -currency $currency -action $action -ignore_date_check true';
	foreach($params as $name => $value) {
		$cmd .= " -$name $value";
	}

	echo "Calling api with cmd: $cmd\n";
	
	$output = array();
	$returnVar = 0;
	exec($cmd, $output, $returnVar);
	$apiOutput = implode("\n", $output);
	if($returnVar > 0) {
		throw new Exception("Cannot execute command: $cmd. Error: $apiOutput");
	}
}

function thenTheFollowingRoomsReturnFromTheAPI($table) {
	global $apiOutput;
	
	echo "Checking rooms from API response\n";
	$json = json_decode($apiOutput, true);
	if(is_null($json)) {
		throw new Exception("Invalid JSON returned from API: $apiOutput");
	}
	$apiRooms = array_values($json);
	$expectedRooms = array();
	foreach($table['rows'] as $row) {
		$expectedRooms[] = array(
			'rt_name' => $row['room type name'],
			'name' => $row['name'],
			'type' => $row['type'],
			'num_of_beds' => $row['number of beds'],
			'id' => $row['id']);
	}
	
	compareList($expectedRooms, $apiRooms, 'apiRoomCompare');

}

function apiRoomCompare($room1, $room2) {
	if($room1['rt_name'] > $room2['rt_name']) { return 1; }
	if($room1['rt_name'] < $room2['rt_name']) { return -1; }
	if($room1['name'] > $room2['name']) { return 2; }
	if($room1['name'] < $room2['name']) { return -2; }
	if($room1['type'] > $room2['type']) { return 3; }
	if($room1['type'] < $room2['type']) { return -3; }
	if($room1['num_of_beds'] > $room2['num_of_beds']) { return 4; }
	if($room1['num_of_beds'] < $room2['num_of_beds']) { return -4; }
	if($room1['id'] > $room2['id']) { return 5; }
	if($room1['id'] < $room2['id']) { return -5; }
	return 0;
}

function thenTheFollowingAvailabilityReturnsFromTheAPI($table) {
	global $apiOutput;
	
	echo "Checking availability from API response\n";
	$json = json_decode($apiOutput, true);
	if(is_null($json)) {
		throw new Exception("Invalid JSON returned from API: $apiOutput");
	}
	$apiAvail = $json['rooms'];
	$expectedAvail = array();
	foreach($table['rows'] as $row) {
		$expectedAvail[] = array(
			'rt_name' => $row['room type name'],
			'id' => $row['id'],
			'num_of_beds_avail' => $row['number of beds available'],
			'num_of_rooms_avail' => $row['number of rooms available']);
	}

	compareList($expectedAvail, $apiAvail, 'apiAvailCompare');
	
}

function apiAvailCompare($avail1, $avail2) {
	if($avail1['rt_name'] > $avail2['rt_name']) { return 1; }
	if($avail1['rt_name'] < $avail2['rt_name']) { return -1; }
	if($avail1['id'] > $avail2['id']) { return 2; }
	if($avail1['id'] < $avail2['id']) { return -2; }
	if($avail1['num_of_beds_avail'] > $avail2['num_of_beds_avail']) { return 3; }
	if($avail1['num_of_beds_avail'] < $avail2['num_of_beds_avail']) { return -3; }
	if($avail1['num_of_rooms_avail'] > $avail2['num_of_rooms_avail']) { return 4; }
	if($avail1['num_of_rooms_avail'] < $avail2['num_of_rooms_avail']) { return -4; }
	return 0;
}

function givenThereAreNoVirtualRoomsConfigured() {
	$link = db_connect('teszt_hostel');
	deleteVirtualRooms($link);
	mysql_close($link);
}

function deleteVirtualRooms($link) {
	echo "Deleting virtual rooms\n";
	$sql = "DELETE FROM rooms_to_room_types";
	$result = mysql_query($sql, $link);
	if(!$result) {
		mysql_close($link);
		throw new Exception("Error deleting all existing virtual rooms: " . mysql_error($link) . " (SQL: $sql)");		
	}
}

function givenTheFollowingVirtualRoomsAreConfigured($table) {

	echo "Setting up virtual rooms\n";

	$link = db_connect('teszt_hostel');
	$sql = "SELECT * FROM rooms";
	$result = mysql_query($sql, $link);
	$rooms = array();
	while($row = mysql_fetch_assoc($result)) {
		$rooms[$row['name']] = $row;
	}
	$sql = "SELECT * FROM room_types";
	$result = mysql_query($sql, $link);
	$roomTypes = array();
	while($row = mysql_fetch_assoc($result)) {
		$roomTypes[$row['name']] = $row;
	}

	$rows = array();
	foreach($table['rows'] as $row) {
		$roomName = $row['room name'];
		$roomTypeName = $row['additional room type'];
		if(!isset($rooms[$roomName])) {
			mysql_close($link);
			throw new Exception("Cannot find room by name: $roomName");
		}
		if(!isset($roomTypes[$roomTypeName])) {
			mysql_close($link);
			throw new Exception("Cannot find room type by name: $roomTypeName");
		}
		$roomId = $rooms[$roomName]['id'];
		$roomTypeId = $roomTypes[$roomTypeName]['id'];
		echo "    $roomName ($roomId) to be associated with type: $roomTypeName ($roomTypeId)\n";
		$rows[] = "($roomId, $roomTypeId)";
	}
	
	deleteVirtualRooms($link);

	$sql = "INSERT INTO rooms_to_room_types (room_id, room_type_id) VALUES " . implode(",", $rows);
	$result = mysql_query($sql, $link);
	if(!$result) {
		mysql_close($link);
		throw new Exception("Error setting up virtual rooms: " . mysql_error($link) . " (SQL: $sql)");		
	}
	

}



 ?>