<?php

ini_set('display_errors', 'On');

require('HTTP/Client.php'); 
require('HTTP/Request.php');
require('HTTP/Client/CookieManager.php');


require("../includes.php");
require("../includes/simple_html_dom.php");
require('../room_booking.php');
require('booker.php');



class LateRoomsBooker extends Booker {

	var $httpClient = null;
	var $guid = null;
	var $hotelId = 197014;
	var $hotelUrl = null;

	function init() {

		echo "<b>Laterooms.com synchronization init</b><br>\n";

		$this->httpClient = new Http_Client();

		$retCode = $this->httpClient->get("http://hoteladmin.laterooms.com/en/SignIn.aspx");
		$response = $this->httpClient->currentResponse();
		$matches = array();
		preg_match('/id="__VIEWSTATE" value="([^"]*)"/', $response['body'], $matches);

		$postData = array();
		$postData['__VIEWSTATE'] = $matches[1];
		$postData['_ctl1:btnLogin'] = 'Login';
		$postData['_ctl1:userName'] = '67147';
		$postData['_ctl1:passWord'] = 'argons whacks';
		$retCode = $this->httpClient->post("http://hoteladmin.laterooms.com/en/SignIn.aspx", $postData);

		$response = $this->httpClient->currentResponse();
		$this->addResponse('login', $response['body'], $response['headers']);

		$body = $response['body'];
		$matches = array();
		preg_match('/\?guid=([^&]*)&/', $body, $matches);
		$this->guid = $matches[1];
		echo "guid=" . $this->guid . "<br>\n";

		$matches = array();
		preg_match('/"([^"]*)".Maverick Hostel/', $body, $matches);
		$this->hotelUrl = $matches[1];
		echo "Hotel home url: " . $this->hotelUrl . "<br>\n";
		echo "done.<br>\n";

		echo "<b>laterooms.com synchronization init finished</b><br><br><br>\n\n";
	}

	var $idMap = array(
		'4499029' => array('46','48','49','50','51','52','53','54','55','56','57','58'), /* Dbl room private bathroom */
		'4499555' => array('39','40'), /* Dbl room shared bathroom */
		'4499557' => array('35'), /* The Blue Brothers - 6 beds */
		'4500046' => array('36'), /* Mss Peach - 5 beds */
		'4591698' => array('59'), /* NEW Maverick ensuites - 3 beds */
		'4591700' => array('60'), /* NEW Maverick ensuites - 4 beds */
		'4591701' => array('61') /* NEW Maverick ensuites - 5 beds */
	);


	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "<b>laterooms.com synchronization update</b><br>\n";
		$this->httpClient->get($this->hotelUrl);
		$response = $this->httpClient->currentResponse();

		$matches = array();
		preg_match('/"([^"]*)".Rooms . Rates/', $response['body'], $matches);
		$randrUrl = $matches[1];
		$randrUrl = str_replace('&amp;', '&', $randrUrl);
		echo "Rooms & Rates url: " . $randrUrl . "<br>\n";
		$guid = substr($randrUrl, 15, strpos($randrUrl, '.', 16) - 15);
		echo "guid: $guid";

		$this->httpClient->get($randrUrl);

		echo "<br>\n";

		$componentUrl = 'http://hoteladmin.laterooms.com/en/Component.aspx';

		$postData = array();
		$postData['actiontype'] = 'validatedates';
		$postData['control'] = 'RatesService';
		$postData['enddate'] = $endDay . '/' . $endMonth . '/' . substr($endYear, 2);
		$postData['guid'] = $guid;
		$postData['hotelId'] = $this->hotelId;
		$postData['initialdate'] =  $startDay . '/' . $startMonth . '/' . substr($startYear, 2, 2);
		$postData['timestamp'] = time() * 1000;
		$this->httpClient->get($componentUrl, $postData);
		$response = $this->httpClient->currentResponse();
		//echo "Validating date with data: " . print_r($postData, true) . ", response: " . $response['body'] . "<br>\n";
		$jsonObj = json_decode($response['body'], true);
		echo "Validate date success: " . $jsonObj['Success'] . " (end date: " . $jsonObj['EndDate'] . ")<br>\n";

		$idx = 0;
		foreach($this->idMap as $roomId => $myRoomIds) {
			$postData = $this->_getComponentData('getroomdetailsheader', $guid, $roomId, time() * 1000);
			if($idx > 0) {
				$postData['roomindex'] = $idx;
			}
			$this->httpClient->get($componentUrl, $postData);

			$postData = $this->_getComponentData('getroomratesdetails', $guid, $roomId, time() * 1000);
			$postData['enddate'] = $endDay . '/' . $endMonth . '/' . $endYear;
			$postData['initialdate'] =  $startDay . '/' . $startMonth . '/' . substr($startYear, 2, 2);
			if($idx > 0) {
				$postData['roomindex'] = $idx;
			}
			$this->httpClient->get($componentUrl, $postData);
			$idx +=1;
		}

		echo "Sending update request...<br>\n";

		$json = array();
		$roomIdx = 0;
		foreach($this->idMap as $roomId => $myRoomIds) {
			$dayIdx = 0;
			$endTS = strtotime("$endYear-$endMonth-$endDay");
			$currDate = "$startYear-$startMonth-$startDay";
			do {
				$currTS = strtotime($currDate);
				$currYear = date('Y', $currTS);
				$currMonth = date('m', $currTS);
				$currDay = date('d', $currTS);

				$item = array();
				$item["RoomId"] = '' . $roomId;
				$item["ControlName"] = $roomIdx . ":" . $dayIdx . ":rate";
				$item["Rate"] = '' . getRoomPrice($currYear, $currMonth, $currDay, $rooms[$myRoomIds[0]]);
				$numOfAvailRooms = 0;
				foreach($myRoomIds as $myRoomId) {
					if(!isset($rooms[$myRoomId])) {
						echo "<b>ERROR:</b> room id: $myRoomId is not found in the loaded rooms!<br>\n";
						continue;
					}
					$numOfAvailBeds = getNumOfAvailBeds($rooms[$myRoomId], $currDate);
					//echo "For roomId: $roomId, for date: $currDate, the avail beds: $numOfAvailBeds<br>\n";
					if($numOfAvailBeds == $rooms[$myRoomId]['num_of_beds']) {
						$numOfAvailRooms += 1;
					}
				}

				$item["Allocation"] = '' . $numOfAvailRooms;
				$item["Status"]  = "a";
				$item["MinStay"]  = "1";
				$item["ErrorControlId"] = $roomIdx . ":" . $dayIdx . ":rate";
				$item["AllocationDate"] = $currDay . "/" . $currMonth . "/" . $currYear . " 00:00:00";
				$json[] = $item;
				$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
				$dayIdx += 1;
			} while($currTS <= $endTS);
			$roomIdx += 1;

		}

		$jsonData = json_encode($json);
		$jsonData = str_replace("\\/", "/", $jsonData);
		$jsonData = str_replace("\"", "\\\"", $jsonData);
		$data = array();
		$data["Data"] = "{\"Data\":\"$jsonData\",\"IsWizard\":false,\"InitialBindDate\":\"" . $startDay . '/' . $startMonth . '/' . substr($startYear, 2, 2) . "\",\"EndBindDate\":\"" . $endDay . '/' . $endMonth . '/' . $endYear . "\"}";
		$saveUrl = $componentUrl . "?guid=" . $guid . "&hotelId=" . $this->hotelId . "&control=RatesService&actiontype=saveroomrates";
		//echo "Saving rates and availability to url: $saveUrl<br>\n";
		$this->httpClient->post($saveUrl, $data);
		$response = $this->httpClient->currentResponse();
		//echo "Saving rate data: " . print_r($data, true) . ", response: " . $response['body'] . "<br>\n";
		$jsonObj = json_decode($response['body'], true);
		echo "Rate and availability update result: " . $jsonObj['Success'] . "<br>\n";
		if(isset($jsonObj['ErrorMessage'])) {
			echo '<b>ERROR:</b> ' . $jsonObj['ErrorMessage'] . "<br>\n";
		}
		if(isset($jsonObj['ErrorMessages'])) {
			echo '<b>ERROR:</b> ' . print_r($jsonObj['ErrorMessages'], true) . "<br>\n";
		}
		if(isset($jsonObj['ValidationMessages']) and count($jsonObj['ValidationMessages']) > 0) {
			echo '<b>Validation message:</b> ' . print_r($jsonObj['ValidationMessages'], true) . "<br>\n";
		}


		echo "<b>Laterooms.com synchronization update finished</b><br><br><br>\n\n";
	}

	function _getComponentData($actionType, $guid, $roomId, $timestamp) { 
		$postData = array();
		$postData['actiontype'] = $actionType;
		$postData['control'] = 'RatesService';
		$postData['guid'] = $guid;
		$postData['hotelId'] = $this->hotelId;
		$postData['roomid'] = $roomId;
		$postData['timestamp'] = $timestamp;
		return $postData;
	}


	function shutdown() {
		echo "<b>Laterooms.com synchronization shutdown</b><br>";
		echo "Sending logout request...<br>\n";
//		$this->httpClient->get("https://secure.hostelworld.com/inbox/logout.php");
//		$response = $this->httpClient->currentResponse();
//		$this->addResponse('logout', $response['body'], $response['headers']);

		$this->httpClient->reset();
		echo "done.<br>\n";
		echo "<b>Laterooms.com synchronization shutdown finished</b><br><br><br>";
	}

} // end of class


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



$link = db_connect();
$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

echo "Period begining: $startYear - $startMonth - $startDay<br>\n";
echo "Period ending: $endYear - $endMonth - $endDay<br>\n";


$booker = new LateRoomsBooker();
$booker->init();
$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();
$responseFiles = $booker->saveResponsesInfoFile('laterooms');
echo "Saved responses: <br>\n";
echo "<ul>\n";
foreach($responseFiles as $key => $filename) {
	echo "	<li><a target=\"_blank\" href=\"$filename\">$key</a></li>\n";
}
echo "</ul>\n";

mysql_close($link);

return;




?>
