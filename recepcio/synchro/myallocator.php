/loa<?php

// Enable user error handling
libxml_use_internal_errors(true);


ini_set('display_errors', 'On');

require("../includes.php");
require('../room_booking.php');
require('booker.php');


class MyAllocatorBooker extends Booker {

	private $saveFile = null;

	function init() {
	}


	function setSaveFile($file) {
		$this->saveFile = $file;
	}

	/////////////////////////////////////////////////////////////
	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		global $myallocatorRoomMap;
		echo "<b>myalocator.com synchronization update</b><br>";
		$location = strtoupper(LOCATION);
		foreach(array_keys($myallocatorRoomMap) as $propertyId) {
			$this->updateLocation($propertyId, $startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
		}
		echo "<b>myallocator.com synchronization update finished</b><br><br><br>";
	}

	function updateLocation($propertyId, $startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		global $myallocatorRoomMap;
		$link = db_connect();
		echo "Location is: $propertyId <br>\n";
		//echo "<!-- " . print_r($rooms, true) . "-->\n";
		$endTS = strtotime("$endYear-$endMonth-$endDay");
		$allocations = '';
		$availabilities = array();
		$prices = array();
		$roomDataToSend = array();
		foreach($myallocatorRoomMap[$propertyId] as $oneRoomMap) {
			$availabilities[$oneRoomMap['roomName']] = array();
			$prices[$oneRoomMap['roomName']] = array();
			//echo "Updating price and availability for room: " . $oneRoomMap['roomName'] . "...<br>\n";

			$currDate = "$startYear-$startMonth-$startDay";
			do {
				$currTS = strtotime($currDate);
				$currYear = date('Y', $currTS);
				$currMonth = date('m', $currTS);
				$currDay = date('d', $currTS);

				$numOfAvailBeds = 0;
				$numOfAvailRooms = 0;
				$roomData = null;
				$roomIds = getRoomIds($rooms, $oneRoomMap['roomTypeId']);
				$roomsProvidingAvaialability = array();
				foreach($roomIds as $roomId) {
					$arr = array();
					if(is_array($oneRoomMap['roomTypeId'])) {
						$arr = $oneRoomMap['roomTypeId'];
					} else {
						$arr[] = $oneRoomMap['roomTypeId'];
					}
					if(is_null($roomData) and in_array($rooms[$roomId]['room_type_id'], $arr)) {
						$roomData = $rooms[$roomId];
					}
					if(!isset($rooms[$roomId])) {
						echo "<b>ERROR:</b> for room[" . $oneRoomMap['roomName'] . "] the room id: $roomId is not found in the loaded rooms!<br>\n";
						continue;
					}
					$availBeds = getNumOfAvailBeds($rooms[$roomId], $currDate);
					//echo "For roomId: $roomId, for date: $currDate, the avail beds: $availBeds<br>\n";
					$numOfAvailBeds += $availBeds;
					if($availBeds == $rooms[$roomId]['num_of_beds']) {
						$numOfAvailRooms += 1;
						$roomsProvidingAvaialability[] = $roomId;
					}
				}
				//logDebug("The room map: " . print_r($oneRoomMap, true));
				//logDebug("For the room type " . $oneRoomMap['roomTypeId'] . " and date $currDate the number of available rooms: $numOfAvailRooms");
				//logDebug("The list of room ids: " . print_r($roomIds, true));
				//logDebug("Type of room: " . $roomData['type']);
				$rtId = $oneRoomMap['roomTypeId'];
				$numOfPerson = (RoomDao::isDorm($roomData) ? 1 : (RoomDao::isApartment($roomData) ? 2 : $roomData['num_of_beds']));
				if(is_array($oneRoomMap['roomTypeId'])) {
					$price = 0;
					foreach($oneRoomMap['roomTypeId'] as $rtId) {
						$price = max($price, PriceDao::getPrice($currTS, 1, $rtId, $numOfPerson, $link));
					}
				} else {
					$price = PriceDao::getPrice($currTS, 1, $rtId, $numOfPerson, $link);
				}
				$units = $roomData['type'] == 'DORM' ? $numOfAvailBeds : $numOfAvailRooms;
				$availabilities[$oneRoomMap['roomName']][$currYear . '-' . $currMonth . '-' . $currDay] = $units;
				$prices[$oneRoomMap['roomName']][$currYear . '-' . $currMonth . '-' . $currDay] = $price;
				$remoteRoomId = $oneRoomMap['remoteRoomId'];
				if($price == intval($price)) {
					$price = $price . '.00';
				}
				
				$roomDataToSend[] = array(
					'roomTypeId' => $oneRoomMap['roomTypeId'], 
					'type' => $roomData['type'],
					'remoteRoomId' => $remoteRoomId, 
					'date' => "$currYear-$currMonth-$currDay", 
					'units' => $units,
					'price' => $price,
					'roomsProvidingAvaialability' => $roomsProvidingAvaialability);
				
				$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
			} while($currTS < $endTS); // End of iteration of dates 

			//echo "done.<br>\n";
		} // End of iteration of rooms

		logDebug("There are " . count($roomDataToSend) . " items in the roomDataToSend array");
		// If for a 'non-DORM' room type there is 1 available and the room providing that 1 availability is of type the room tpye 
		// then remove availability from the other room types that is associated with that room
		foreach($roomDataToSend as $item) {
			if($item['units'] != 1 or $item['type'] == 'DORM') {
				continue;
			}
			// There is only 1 room in the roomsProvidingAvaialability list because Units=1
			$room1 = $rooms[$item['roomsProvidingAvaialability'][0]];
			if($room1['room_type_id'] != $item['roomTypeId']) {
				continue;
			}
			removeAdditionalAvailability($room1, $item['date'], $roomDataToSend);
		}			
			
		foreach($roomDataToSend as $item) {
			$remoteRoomId = $item['remoteRoomId'];
			$startDate = $item['date'];
			$endDate = $item['date'];
			$units = $item['units'];
			$price = $item['price'];
			$allocations .= <<<EOT
		<Allocation>
			<RoomTypeId>$remoteRoomId</RoomTypeId>
			<StartDate>$startDate</StartDate>
			<EndDate>$endDate</EndDate>
			<Units>$units</Units>
			<Prices>
				<Price>$price</Price>
			</Prices>
		</Allocation>

EOT;
		}
		
		$auth = $this->getAuth($propertyId);
		$request = <<<EOT
<?xml version="1.0" encoding="UTF-8" ?>
<SetAllocation>
$auth
	<Channels>
		<Channel>all</Channel>
	</Channels>
	<Allocations>
$allocations
	</Allocations>
</SetAllocation>

EOT;

		$resp = $this->processRequest($request);

		$availTable = "";
		$availTable .=  "<table>\n";
		$availTable .=  "	<tr><th></th>";
		$currDate = "$startYear-$startMonth-$startDay";
		$debugLine = str_repeat(" ", 10);
		do {
			$debugLine .= (sprintf("%12s", $currDate));
			$availTable .=  "<th>$currDate</th>";
			$currTS = strtotime($currDate);
			$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
		} while($currTS < $endTS);
		$availTable .=  "</tr>\n";
		logDebug($debugLine);
		foreach($availabilities as $roomName => $availForDates) {
			$debugLine = sprintf("%10.10s", $roomName);
			$availTable .=  "	<tr><th>$roomName</th>";
			foreach($availForDates as $date => $avail) {
				$prc = $prices[$roomName][$date];
				$availTable .=  "<td>$avail($prc)</td>";
				$debugLine .= sprintf("%12.12s", $avail . '(' . $prc . ')');
			}
			$availTable .=  "</tr>\n";
			logDebug($debugLine);
		}
		$availTable .=  "</table>\n";
		if(!isset(getParameter('test_runner_response'))) {
			echo $availTable;
		}
	}

	function shutdown() {
	}

	function getAuth($propertyId = null) {
		$customerID = MYALLOCATOR_CUSTOMER_ID;
		$customerPassword = MYALLOCATOR_CUSTOMER_PASSWORD;
		$propertyLine = '';
		if(!is_null($propertyId)) {
			$propertyLine = "		<PropertyId>$propertyId</PropertyId>";
		}
		$vendorID = MYALLOCATOR_VENDOR_ID;
		$vendorPassword = MYALLOCATOR_VENDOR_PASSWORD;

		$auth = <<<EOT
	<Auth>
		<UserId>$customerID</UserId>
		<UserPassword>$customerPassword</UserPassword>
$propertyLine
		<VendorId>$vendorID</VendorId>
		<VendorPassword>$vendorPassword</VendorPassword>
	</Auth>

EOT;
		return $auth;
	}

	function getProperties() {
		$auth = $this->getAuth();
		$request = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<GetProperties>
$auth
</GetProperties>
EOT;

		$this->processRequest($request);

	}

	function getRoomTypes($propertyId) {
		$auth = $this->getAuth($propertyId);
		$request = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<GetRoomTypes>
$auth
</GetRoomTypes>
EOT;

		$this->processRequest($request);

	}



	function getElementValue($element, $name) {
		$list = $xml->getElementsByTagName('name');
		if(is_null($list) or count($list) < 1) {
			return null;
		}
		return $list[0]->nodeValue;
	}

	function processRequest($request) {
		// echo "Request: " . $request . "\n";
		logDebug($request);
		if(!is_null($this->saveFile)) {
			echo "Request saved into file: " . $this->saveFile;
			file_put_contents($this->saveFile,$request);
			return;
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://api.myallocator.com/pms/v201408/xml/SetAllocation');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'xmlRequestString=' . urlencode($request));
		$body = trim(curl_exec($curl));
		// echo "Response: " . $body . "\n";
		$matches = array();
		logDebug($body);
		preg_match('/<Success>([^<]*)<\/Success>/', $body, $matches);
		$success = count($matches) > 0 ? $matches[1] : '';
		if($success == 'true') {
			echo "<b>Update Successful!</b><br>\n";
		} else {
			echo "Update not successful: " . $success . "<br>\n";
		}

		echo "Warnings: <br><ul>\n";
		$matches = array();
		preg_match('/<WarningMsg>([^<]*)<\/WarningMsg>/', $body, $matches);
		foreach ($matches as $msg) {
			if(strlen($msg) > 0) {
				echo '<li>' . $msg . "</li>\n";
				logDebug("Warning: $msg");
			}
		}
		echo "</ul>\n";
		echo "Errors: <br><ul>\n";
		preg_match('/<ErrorMsg>([^<]*)<\/ErrorMsg>/', $body, $matches);
		foreach ($matches as $msg) {
			if(strlen($msg) > 0) {
				echo '<li>' . $msg . "</li>\n";
				logDebug("Error: $msg");
			}
		}
		echo "</ul>\n";

		//echo "Response: " . $body;
		return $body;
	}

	function _getLibxmlDisplayError($error) {
		$return = "<br/>\n";
		switch ($error->level) {
			case LIBXML_ERR_WARNING:
			$return .= "<b>Warning $error->code</b>: ";
			break;
		case LIBXML_ERR_ERROR:
			$return .= "<b>Error $error->code</b>: ";
			break;
		case LIBXML_ERR_FATAL:
			$return .= "<b>Fatal Error $error->code</b>: ";
			break;
		}
		$return .= trim($error->message);
		if ($error->file) {
			$return .=    " in <b>$error->file</b>";
		}
		$return .= " on line <b>$error->line</b>\n";
		return $return;
	}


}





$startYear = getParameter('start_year');
$startMonth = getParameter('start_month');
$startDay = getParameter('start_day');
$endYear = getParameter('end_year');
$endMonth = getParameter('end_month');
$endDay = getParameter('end_day');


if(strlen($startDay) == 1)
	$startDay = '0' . $startDay;
if(strlen($startMonth) == 1)
	$startMonth = '0' . $startMonth;
if(strlen($endDay) == 1)
	$endDay = '0' . $endDay;
if(strlen($endMonth) == 1)
	$endMonth = '0' . $endMonth;

header('Content-Type: text/html');

$link = db_connect();
$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);
PriceDao::loadPriceForDate(strtotime("$startYear-$startMonth-$startDay"), strtotime("$endYear-$endMonth-$endDay"), getLoginHotel());

echo "Period begining: $startYear-$startMonth-$startDay<br>\n";
echo "Period ending: $endYear-$endMonth-$endDay<br>\n";


$booker = new MyAllocatorBooker();
$booker->init();

if(isset(getParameter('save_file'))) {
	$booker->setSaveFile(getParameter('save_file'));
}



$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();

mysql_close($link);

return;




/**
 * This is called if there is only one room is available from the room type that the $room belongs to. In that case if
 * $room has additional room types setup, we need to remove availability from those room types
 * Parameters
 *   $room                - The room where for the room type (identified by $room['room_type_id']) there is only one available (this room)
 *   $roomDataToSend      - The data to be converted to XML and sent to myallocator
 */
function removeAdditionalAvailability($room, $date, &$roomDataToSend) {
	logDebug("For room type: " . $room['room_type_id'] . " there is only one room available whose original type is this. Removing availability for the additional room types from array with size: " . count($roomDataToSend)); 
	foreach($room['room_types'] as $roomTypeId => $roomTypeName) {
		if($room['room_type_id'] == $roomTypeId) {
			continue;
		}
		logDebug("\tdecrementing availability for date: $date and room type: $roomTypeName($roomTypeId)"); 
		for($i = 0; $i < count($roomDataToSend); $i++) {
			//logDebug("\t\tchecking room data item with room type id: " . is_array($roomDataToSend[$i]['roomTypeId']) ? print_r($roomDataToSend[$i]['roomTypeId'], true) : $roomDataToSend[$i]['roomTypeId']);
			if($roomDataToSend[$i]['date'] != $date) {
				continue;
			}
			if(is_array($roomDataToSend[$i]['roomTypeId']) ? in_array($roomTypeId, $roomDataToSend[$i]['roomTypeId']) : ($roomDataToSend[$i]['roomTypeId'] == $roomTypeId)) {
				$roomDataToSend[$i]['units'] -= 1;
				logDebug("\trevised availability: " . $roomDataToSend[$i]['units']); 
			}
		}
	}
}


function getParameter($parameterName) {
	if(isset($argv)) {
		for($i = 1; $i < (count($argv)-1); $i++) {
			if($argv[$i] == ('-' . $parameterName)) {
				return $argv[$i+1];
			}
		}
	}
	if(isset($_REQUEST) and isset($_REQUEST[$parameterName])) {
		return $_REQUEST[$parameterName];
	}
	return null;
}


?>
