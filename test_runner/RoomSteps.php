<?php

function verifyThatTheFollowingRoomTypesExist($table) {
	$link = db_connect('teszt_hostel');
	$roomTypes = RoomDao::getRoomTypes('eng',$link);
	$expectedRoomTypes = array();
	foreach($table['rows'] as $row) {
		$expectedRoomTypes[] = array('id' => $row['id'], 'rt_name' => $row['name'], 'type' => $row['type'], 'num_of_beds' => $row['number of beds']);
	}
	compareList($expectedRoomTypes, array_values($roomTypes), 'roomTypeCompare');
	mysql_close($link);
}

function roomTypeCompare($roomType1, $roomType2) {
	if($roomType1['id'] < $roomType2['id']) return -1;
	if($roomType1['id'] > $roomType2['id']) return 1;
	if($roomType1['rt_name'] < $roomType2['rt_name']) return -2;
	if($roomType1['rt_name'] > $roomType2['rt_name']) return 2;
	if($roomType1['type'] < $roomType2['type']) return -3;
	if($roomType1['type'] > $roomType2['type']) return 3;
	if($roomType1['num_of_beds'] < $roomType2['num_of_beds']) return -4;
	if($roomType1['num_of_beds'] > $roomType2['num_of_beds']) return 4;
	return 0;
}

function verifyThatTheFollowingRoomsExist($table) {
	$link = db_connect('teszt_hostel');
	$rooms = RoomDao::getRooms($link);
	$expectedRooms = array();
	foreach($table['rows'] as $row) {
		$expectedRooms[] = array('id' => $row['id'], 'name' => $row['name'], 'room_type_id' => $row['room type id']);
	}
	compareList($expectedRooms, array_values($rooms), 'roomCompare');
	mysql_close($link);
}

function roomCompare($room1, $room2) {
	if($room1['id'] < $room2['id']) return -1;
	if($room1['id'] > $room2['id']) return 1;
	if($room1['name'] < $room2['name']) return -2;
	if($room1['name'] > $room2['name']) return 2;
	if($room1['room_type_id'] < $room2['room_type_id']) return -3;
	if($room1['room_type_id'] > $room2['room_type_id']) return 3;
	return 0;
}


function givenThereAreNoVirtualRooms($table) {
	$link = db_connect('teszt_hostel');
	echo "Deleting virtual rooms\n";
	$sql = "DELETE FROM rooms_to_room_types";
	$result = mysql_query($sql, $link);
	if(!$result) {
		mysql_close($link);
		throw new Exception("Error deleting all existing virtual rooms: " . mysql_error($link) . " (SQL: $sql)");		
	}
	mysql_close($link);
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
	
	$sql = "INSERT INTO rooms_to_room_types (room_id, room_type_id) VALUES " . implode(",", $rows);
	$result = mysql_query($sql, $link);
	if(!$result) {
		mysql_close($link);
		throw new Exception("Error setting up virtual rooms: " . mysql_error($link) . " (SQL: $sql)");		
	}
	

}

?>