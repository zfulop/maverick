<?php

// Enable user error handling
libxml_use_internal_errors(true);


ini_set('display_errors', 'On');

require("../includes.php");
require('../room_booking.php');
require('../includes/simple_html_dom.php');
require('booker.php');


define('CUSTOMER_ID','maverick');
define('CUSTOMER_PASSWORD','Palesz22');
define('PROPERTY_ID_HOSTEL','1650');
define('PROPERTY_ID_LODGE','1748');
define('PROPERTY_ID_APARTMENT','5637');
define('VENDOR_ID','maverickhostel');
define('VENDOR_PASSWORD','kPnFxw85RS');


class MyAllocatorBooker extends Booker {

	function init() {
	}



	var $roomMap = array(
		'HOSTEL' => array(
			array(
				'roomName' => 'The_Blue_Brothers_6_Bed',
				'roomIds' => array(35),
				'remoteRoomId' => '9131'
				),
			array(
				'roomName' => 'Mss_Peach_5_Bed',
				'roomIds' => array(36),
				'remoteRoomId' => '9130'
				),
			array(
				'roomName' => 'Double_room_shared_bathroom',
				'roomIds' => array(39, 40),
				'remoteRoomId' => '9133'
				),
			array(
				'roomName' => 'Double_room_private_bathroom_ensuites_with_NEW_rooms',
				'roomIds' => array(46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 66, 67, 68, 69, 75),
				'remoteRoomId' => '9134'
				),
			array(
				'roomName' => 'NEW_Maverick_ensuite_3_Bed',
				'roomIds' => array(59, 62, 73),
				'remoteRoomId' => '9135'
				),
			array(
				'roomName' => 'NEW_Maverick_ensuite_4_Bed',
				'roomIds' => array(60, 71, 72),
				'remoteRoomId' => '9136'
				),
			array(
				'roomName' => 'NEW_Maverick_ensuite_5_Bed',
				'roomIds' => array(61, 74),
				'remoteRoomId' => '9137'
			),
			array(
				'roomName' => 'Mr Green',
				'roomIds' => array(42),
				'remoteRoomId' => '9132'
			),
			array(
				'roomName' => 'HW 4 bedded extra private ensuite',
				'roomIds' => array(63),
				'remoteRoomId' => '10032'
			),
			array(
				'roomName' => '5 bed Dorm with private bathroom',
				'roomIds' => array(64),
				'remoteRoomId' => '9431'
			),
			array(
				'roomName' => 'Single room ensuite',
				'roomIds' => array(65),
				'remoteRoomId' => '24369'
			)
		),
		'APARTMENT' => array(
			array(
				'roomName' => 'Studio Apartment',
				'roomIds' => array(82),
				'remoteRoomId' => '29812'
				),
			array(
				'roomName' => 'Deluxe Studio Apartment',
				'roomIds' => array(80,81),
				'remoteRoomId' => '29813'
				),
			array(
				'roomName' => 'One-bedroom apartment, Ferenciek',
				'roomIds' => array(78,79),
				'remoteRoomId' => '29814'
				),
			array(
				'roomName' => 'One-bedroom apartment, Belgrád',
				'roomIds' => array(77),
				'remoteRoomId' => '29815'
				),
			array(
				'roomName' => 'Two-bedroom apartment, Deák',
				'roomIds' => array(76),
				'remoteRoomId' => '29816'
				)
		),
		'LODGE' => array(
			array(
				'roomName' => 'Double Private Room',
				'roomIds' => array(89,90,106,107),
				'remoteRoomId' => '10035'
				),
			array(
				'roomName' => 'Double Private Ensuite Room',
				'roomIds' => array(65,78,79,80,81,82,83,84,85,86,87,88,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,108),
				'remoteRoomId' => '10036'
				),
			array(
				'roomName' => 'Triple Private Ensuite Room',
				'roomIds' => array(109,110,111,115),
				'remoteRoomId' => '10037'
				),
			array(
				'roomName' => 'Quadruple Private Ensuite Room',
				'roomIds' => array(112,113,114,116),
				'remoteRoomId' => '10038'
				),
			array(
				'roomName' => '4 bed mixed dorm',
				'roomIds' => array(74,75,76),
				'remoteRoomId' => '10066'
				),
			array(
				'roomName' => '6 bed mixed dorm',
				'roomIds' => array(67,68,69,70,71,72),
				'remoteRoomId' => '10067'
				),
			array(
				'roomName' => '8 bed mixed dorm',
				'roomIds' => array(77),
				'remoteRoomId' => '10068'
				),
			array(
				'roomName' => 'Female 6 bed dorm',
				'roomIds' => array(117,118),
				'remoteRoomId' => '16475'
				)
		)
	);



	/////////////////////////////////////////////////////////////
	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "<b>myalocator.com synchronization update</b><br>";
		$location = strtoupper(LOCATION);
		$this->updateLocation($location, $startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
		if($location == 'HOSTEL') {
			$this->updateLocation('APARTMENT', $startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
		}
		echo "<b>myallocator.com synchronization update finished</b><br><br><br>";
	}

	function updateLocation($location, $startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "Location is: $location <br>\n";
		//echo "<!-- " . print_r($rooms, true) . "-->\n";
		$endTS = strtotime("$endYear-$endMonth-$endDay");
		$allocations = array();
		$availabilities = array();
		foreach($this->roomMap[$location] as $oneRoomMap) {
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
				$allocations[] = array(
					'RoomId' => $remoteRoomId, 
					'StartDate' => "$currYear-$currMonth-$currDay", 
					'EndDate' => "$currYear-$currMonth-$currDay",
					'Units' => $units,
					'MinStay' => 1,
					'MaxStay' => 30,
					'Price' => $price,
					'PriceWeekend' => $price);
				$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
				$idx += 1;
			} while($currTS < $endTS);

			//echo "done.<br>\n";
		}

		$pid = constant('PROPERTY_ID_' . $location);
		$auth = $this->getAuth($pid);
		$request = array(
			'Auth/UserId' => CUSTOMER_ID,
			'Auth/UserPassword' => CUSTOMER_PASSWORD:
			'Channels' => 'all',
			'Allocations' => $allocations);

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
		$customerID = CUSTOMER_ID;
		$customerPassword = CUSTOMER_PASSWORD;
		$propertyLine = '';
		if(!is_null($propertyId)) {
			$propertyLine = "		<PropertyId>$propertyId</PropertyId>";
		}
		$vendorID = VENDOR_ID;
		$vendorPassword = VENDOR_PASSWORD;

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
		//echo "Request: " . $request . "\n";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://api.myallocator.com/');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'xmlRequestString=' . urlencode($request));
		$body = trim(curl_exec($curl));
		$matches = array();
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




?>
