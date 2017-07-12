<?php

require('HTTP/Client.php'); 
//require('HTTP/Request.php');
//require('HTTP/Client/CookieManager.php');

define('MYALLOC_XML_FILE', '/home/maveric3/dev/test_runner/myalloc_request.xml');

function whenIUpdateMyallocatorWithTheCurrentAvailability($table) {
	foreach($table['rows'] as $row) {
		$startDate = $row['from'];
		$endDate = $row['to'];
		list($startYear,$startMonth,$startDay) = explode('-', $startDate);
		list($endYear,$endMonth,$endDay) = explode('-', $endDate);

		$httpClient = new Http_Client();

		$postData = array('hostel' => 'teszt_hostel', 'username' => 'test', 'password' => '', 'test_runner_response' => 'true');
		$retCode = $httpClient->post("http://reception.dev.roomcaptain.com/do_login.php", $postData);
		$resp = $httpClient->currentResponse();
		echo "Login response: " . $resp['body'] . "\n";

		$postData = array();
		$postData['start_year'] = $startYear;
		$postData['start_month'] = $startMonth;
		$postData['start_day'] = $startDay;
		$postData['end_year'] = $endYear;
		$postData['end_month'] = $endMonth;
		$postData['end_day'] = $endDay;
		$postData['save_file'] = MYALLOC_XML_FILE;
		$postData['test_runner_response'] = 'true';

		echo 'Invoking myallocator sync and saving the output to ' . MYALLOC_XML_FILE . "\n";
		$retCode = $httpClient->post("http://reception.dev.roomcaptain.com/synchro/myallocator.php", $postData);
	}
}

function thenTheFollowingAvailabilityIsSentToMyallocator($table) {
	echo "Checking the MyAlloc message\n";
	$fileContent = file_get_contents(MYALLOC_XML_FILE);
	$xmlData = simplexml_load_file(MYALLOC_XML_FILE);
	//  print_r($xmlData);
	$roomTypeNames = getRoomTypeNamesForRemoteRoomId();
	$actualData = array();
	echo "The MyAlloc message has " . count($xmlData->Allocations->Allocation) . " elements\n";
	foreach($xmlData->Allocations->Allocation as $oneAlloc) {
		$actualData[] = array('date' => $oneAlloc->StartDate->__toString(), 'price' => $oneAlloc->Prices->Price->__toString(), 'availability' => $oneAlloc->Units->__toString(), 'room type' => $roomTypeNames['ID_' . $oneAlloc->RoomTypeId]);
	}
	unlink (MYALLOC_XML_FILE);
	$expectedData = array();
	foreach($table['rows'] as $row) {
		$expectedData[] = array('date' => $row['date'], 'price' => $row['price'], 'availability' => $row['availability'], 'room type' => $row['room type name']);
	}

	compareList($expectedData, $actualData, 'myallocDataCompare');
	
}

function myallocDataCompare($data1, $data2) {
	if($data1['room type'] < $data2['room type']) return 1;
	if($data1['room type'] > $data2['room type']) return -1;
	if($data1['date'] < $data2['date']) return 2;
	if($data1['date'] > $data2['date']) return -2;
	if(intval($data1['price']) < intval($data2['price'])) return 3;
	if(intval($data1['price']) > intval($data2['price'])) return -3;
	if($data1['availability'] < $data2['availability']) return 4;
	if($data1['availability'] > $data2['availability']) return -4;
	return 0;
}

function getRoomTypeNamesForRemoteRoomId() {
	global $myallocatorRoomMap;
	$link = db_connect('teszt_hostel');
	$sql = "SELECT * FROM room_types";
	$result = mysql_query($sql, $link);
	$rooms = array();
	while($row = mysql_fetch_assoc($result)) {
		$rooms[$row['id']] = $row;
	}
	mysql_close($link);
	
	$retVal = array();
	foreach($myallocatorRoomMap[-1000] as $myallocRecord) {
		$rtId = $myallocRecord['roomTypeId'];
		if(is_array($rtId)) {
			$rtId = $rtId[0];
		}
		$retVal['ID_' . $myallocRecord['remoteRoomId']] = $rooms[$rtId]['name'];
	}
	
	return $retVal;
}


function whenTheFollowingBookingArrivesFromMyallocator($table) {
	$link = db_connect('teszt_hostel');
	echo "Sending myallocator booking to roomcaptain via HTTP json\n";
	$sql = "SELECT id, name, type FROM room_types";
	$result = mysql_query($sql, $link);
	$roomTypes = array();
	while($row = mysql_fetch_assoc($result)) {
		$roomTypes[$row['name']] = $row;
	}
	mysql_close($link);
	$custArr = array("CustomerLName" => "Tester", "CustomerEmail" => "testing@test.com", "CustomerFName" => "James", "CustomerCity" => "Budapest", "CustomerPostCode" => "1095", "CustomerAddress" => "Teszt Utca 5", "CustomerPhone" => "1234567", "CustomerCountry" => "hu");
	$today = date('Y-m-d');
	$orderId = 'qwedsc1213';
	$myAllocatorId = 'qwedsc1213';
	$jsonMessage = array(
		"Customers" => array($custArr),
		"OrderDate" => $today,
		"OrderId" => $orderId,
		"IsCancellation" => false,
		"MyallocatorId" => $myAllocatorId,
		"PropertyId" => -1000,
		"Channel" => "boo",
		"Rooms" => array()
	);

	$totalPrice = 0;
	$currency = 'EUR';
	$first = true;
	foreach($table['rows'] as $row) {
		if($first) {
			$first = false;
			$currency = $row['currency'];
			$jsonMessage['Comission'] = $row['comission'];
			$currency = $row['currency'];
			$jsonMessage['Currency'] = $currency;
			$jsonMessage['StartDate'] = $row['start date'];
			$jsonMessage['EndDate'] = $row['end date'];
			if(isset($row['myallocatorid'])) {
				$jsonMessage['MyallocatorId'] = $row['myallocatorid'];
			}
		} else {
			$roomType = $roomTypes[$row['room type']];
			if(is_null($roomType)) {
				throw new Exception("Cannot find room type: " . $row['room type']);
			}
			$remoteRoomId = getMyAllocationRoomId($roomType['id']);
			if(is_null($roomType)) {
				throw new Exception("Cannot find myallocator room id for room type: " . $row['room type']);
			}
			// echo "    for room type: " . $row['room type'] . "(id: " . $roomType['id'] . ") the remote room id: $remoteRoomId\n";
			$startDate = $row['start date'];
			$endDate = $row['end date'];
			$units = $row['units'];
			$roomPrice = $row['price'];
			$totalPrice += $roomPrice;
			// echo "    " . $roomType['name'] . " from $startDate to $endDate, $units units, price: $roomPrice\n";
			$jsonMessage['Rooms'][] = array("Units" => $units, "StartDate" => $startDate, "EndDate" => $endDate, "RoomTypeIds" => array($remoteRoomId), "Price" => $roomPrice, "Currency" => $currency);
		}
	}
	$jsonMessage['TotalCurrency'] = $currency;
	$jsonMessage['TotalPrice'] = $totalPrice;
	

	$httpClient = new Http_Client();
	$postData = array('password' => 'dfvsdq23sd', 'booking' => json_encode($jsonMessage));
	$retCode = $httpClient->post("http://reception.dev.roomcaptain.com/myallocator_booking.php", $postData);
	$resp = $httpClient->currentResponse();
	echo "Response from processing the myallocator request: " . $resp['body'] . "\n";

	if($result === false) {
		$msg = "Cannot execute http request to send booking: " . curl_error($curl_connection) . " " . curl_errorno($curl_connection);
		curl_close($curl_connection);
		throw new Exception($msg);
	}
	
}

function getMyAllocationRoomId($roomTypeId) {
	global $myallocatorRoomMap;
	foreach($myallocatorRoomMap[-1000] as $roomMap) {
		if($roomMap['roomTypeId'] == $roomTypeId or (is_array($roomMap['roomTypeId']) and in_array($roomTypeId, $roomMap['roomTypeId']))) {
			return $roomMap['remoteRoomId'];
		}
	}
}



?>