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
	return executeApiViaUrl($currency, $action, $params);
}

function executeApiViaLocalCmd($currency, $action, $params) {
	$cmd = 'php -c /home/maveric3/php.ini /home/maveric3/dev/reception/api.php -location teszt_hostel -lang eng -currency $currency -action $action -ignore_date_check true';
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
	return $apiOutput;
}

function executeApiViaUrl($currency, $action, $params) {
	$httpClient = new Http_Client();
	$postData = array('location' => 'teszt_hostel', 'lang' => 'eng', 'currency' => $currency, 'action' => $action, 'ignore_date_check' => 'true');
	foreach($params as $name => $value) {
		$postData[$name] = $value;
	}

	$retCode = $httpClient->post("http://reception.dev.roomcaptain.com/api.php", $postData);
	$resp = $httpClient->currentResponse();
	return $resp['body'];
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
	if(trim($avail1['num_of_beds_avail']) !== '' and $avail1['num_of_beds_avail'] > $avail2['num_of_beds_avail']) { return 3; }
	if(trim($avail1['num_of_beds_avail']) !== '' and $avail1['num_of_beds_avail'] < $avail2['num_of_beds_avail']) { return -3; }
	if(trim($avail1['num_of_rooms_avail']) !== '' and $avail1['num_of_rooms_avail']> $avail2['num_of_rooms_avail']) { return 4; }
	if(trim($avail1['num_of_rooms_avail']) !== '' and $avail1['num_of_rooms_avail'] < $avail2['num_of_rooms_avail']) { return -4; }
	return 0;
}


 ?>