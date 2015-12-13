<?php

// Enable user error handling
libxml_use_internal_errors(true);


ini_set('display_errors', 'On');

require("../includes.php");
require('../room_booking.php');
require('booker.php');


class MyAllocatorBooker extends Booker {

	function init() {
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
		echo "Location is: $propertyId <br>\n";
		//echo "<!-- " . print_r($rooms, true) . "-->\n";
		$endTS = strtotime("$endYear-$endMonth-$endDay");
		$allocations = '';
		$availabilities = array();
		foreach($myallocatorRoomMap[$propertyId] as $oneRoomMap) {
			$availabilities[$oneRoomMap['roomName']] = array();
			//echo "Updating price and availability for room: " . $oneRoomMap['roomName'] . "...<br>\n";

			$currDate = "$startYear-$startMonth-$startDay";
			$idx = 0;
			do {
				$currTS = strtotime($currDate);
				$currYear = date('Y', $currTS);
				$currMonth = date('m', $currTS);
				$currDay = date('d', $currTS);

				$numOfAvailBeds = 0;
				$numOfAvailRooms = 0;
				foreach($oneRoomMap['roomIds'] as $roomId) {
					if(!isset($rooms[$roomId])) {
						echo "<b>ERROR:</b> for room[" . $oneRoomMap['roomName'] . "] the room id: $roomId is not found in the loaded rooms!<br>\n";
						continue;
					}
					$availBeds = getNumOfAvailBeds($rooms[$roomId], $currDate);
					//echo "For roomId: $roomId, for date: $currDate, the avail beds: $availBeds<br>\n";
					$numOfAvailBeds += $availBeds;
					if($availBeds == $rooms[$roomId]['num_of_beds']) {
						$numOfAvailRooms += 1;
					}
				}
				//echo "For the room type the number of available rooms: $numOfAvailRooms<br>\n";
				$roomData = $rooms[$oneRoomMap['roomIds'][0]];
				//echo "Type of room: " . $roomData['type'] . "<br>\n";
				$price = $roomData['type'] == 'DORM' ? getBedPrice($currYear, $currMonth, $currDay, $roomData) : getRoomPrice($currYear, $currMonth, $currDay, $roomData);
				$units = $roomData['type'] == 'DORM' ? $numOfAvailBeds : $numOfAvailRooms;
				$availabilities[$oneRoomMap['roomName']][$currYear . '-' . $currMonth . '-' . $currDay] = $units;
				$remoteRoomId = $oneRoomMap['remoteRoomId'];
				$allocations .= <<<EOT
		<Allocation>
			<RoomTypeId>$remoteRoomId</RoomTypeId>
			<StartDate>$currYear-$currMonth-$currDay</StartDate>
			<EndDate>$currYear-$currMonth-$currDay</EndDate>
			<Units>$units</Units>
			<Prices>
				<Price>$price.00</Price>
			</Prices>
		</Allocation>

EOT;

				$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
				$idx += 1;
			} while($currTS < $endTS);

			//echo "done.<br>\n";
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
		echo "\n<!-- REQUEST: " . $request . "\n-->\n\n";
		echo "\n<!-- RESPONSE: " . $resp . "\n-->\n\n";

		echo "<table>\n";
		echo "	<tr><th></th>";
		$currDate = "$startYear-$startMonth-$startDay";
		do {
			echo "<th>$currDate</th>";
			$currTS = strtotime($currDate);
			$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
		} while($currTS < $endTS);
		echo "</tr>\n";
		foreach($availabilities as $roomName => $availForDates) {
			echo "	<tr><th>$roomName</th>";
			foreach($availForDates as $date => $avail) {
				echo "<td>$avail</td>";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";

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

	function getRoomTypes($location) {
		$pid = constant('PROPERTY_ID_' . $location);
		$auth = $this->getAuth($pid);
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
		logMessage($request);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://api.myallocator.com/pms/v201408/xml/SetAllocation');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'xmlRequestString=' . urlencode($request));
		$body = trim(curl_exec($curl));
		// echo "Response: " . $body . "\n";
		$matches = array();
		logMessage($body);
		preg_match('/<Success>([^<]*)<\/Success>/', $body, $matches);
		$success = count($matches) > 0 ? $matches[1] : '';
		if($success == 'true') {
			echo "<b>Update Successful!</b><br>\n";
		} else {
			echo "Update not successful: " . $success . "<br>\n";
			echo "Warnings: <br><ul>\n";
			$matches = array();
			preg_match('/<WarningMsg>([^<]*)<\/WarningMsg>/', $body, $matches);
			foreach ($matches as $msg) {
				echo '<li>' . $msg . "</li>\n";
			}
			echo "</ul>\n";
			echo "Errors: <br><ul>\n";
			preg_match('/<ErrorMsg>([^<]*)<\/ErrorMsg>/', $body, $matches);
			foreach ($matches as $msg) {
				echo '<li>' . $msg . "</li>\n";
			}
			echo "</ul>\n";

		}

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





$startYear = $_REQUEST['start_year'];
$startMonth = $_REQUEST['start_month'];
$startDay = $_REQUEST['start_day'];
$endYear = $_REQUEST['end_year'];
$endMonth = $_REQUEST['end_month'];
$endDay = $_REQUEST['end_day'];



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

echo "Period begining: $startYear - $startMonth - $startDay<br>\n";
echo "Period ending: $endYear - $endMonth - $endDay<br>\n";


$booker = new MyAllocatorBooker();
$booker->init();

//$booker->getProperties();
//$booker->getRoomTypes(strtoupper(LOCATION));
$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();

mysql_close($link);

return;



function logMessage($message) {
	$fh = fopen("sync." . date('Ymd') . ".log", "a");
	if($fh) {
		fwrite($fh, date('Y-m-d H:i:s') . "\n");
		fwrite($fh, $message . "\n");
		fclose($fh);
	}
}




?>
