<?php

function verifyThatTheFollowingRoomTypesExist($table) {
	$link = db_connect('teszt_hostel');
	$roomTypes = RoomDao::getRoomTypes('eng',$link);
	$expectedRoomTypes = array();
	foreach($table['rows'] as $row) {
		$expectedRoomTypes[] = array('id' => $row['id'], 'rt_name' => $row['name'], 'type' => $row['type'], 'num_of_beds' => $row['number of beds']);
	}
	compareList($expectedRoomTypes, array_values($roomTypes, 'roomTypeCompare'));
	mysql_close($link);
}

function roomTypeCompare($roomType1, $roomType2) {
	if($roomType1['id'] < $roomType2['id']) return -1;
	if($roomType1['id'] > $roomType2['id']) return 1;
	if($roomType1['rt_name'] < $roomType2['rt_name']) return -2;
	if($roomType1['rt_name'] > $roomType2['rt_name']) return 2;
	if($roomType1['type'] < $roomType2['type']) return -3;
	if($roomType1['type'] > $roomType2['type']) return 3;
	if($roomType1['num_of_beds'] < $roomType2['num_of_beds']) return -3;
	if($roomType1['num_of_beds'] > $roomType2['num_of_beds']) return 3;
	return 0;
}

function verifyThatTheFollowingRoomsExist($table) {
	$link = db_connect('teszt_hostel');
	$rooms = RoomDao::getRooms($link);
	$expectedRooms = array();
	foreach($table['rows'] as $row) {
		$expectedRooms[] = array('name' => $row['name'], 'room_type_id' => $row['room_type_id']);
	}
	compareList($expectedRooms, array_values($rooms, 'roomCompare'));
	mysql_close($link);
}

function roomCompare($roomType1, $roomType2) {
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


?>