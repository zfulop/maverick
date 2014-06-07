<?php

ini_set('display_errors', 'On');

require('HTTP/Client.php'); 
require('HTTP/Request.php');
require('HTTP/Client/CookieManager.php');

require("../includes.php");
require('../room_booking.php');
require('../includes/simple_html_dom.php');
require('booker.php');




class BookingsBooker extends Booker {

	var $httpClient = null;
	var $sessionId = null;
	var $pid = null;

	function init() {
		echo "<b>bookings.org synchronization init</b><br>";

		$this->httpClient = new Http_Client();

		$this->httpClient->setDefaultHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
		$this->httpClient->setDefaultHeader('Accept-Encoding', 'gzip, deflate');
		$this->httpClient->setDefaultHeader('Accept-Language', 'en-US,en;q=0.5');
		$this->httpClient->setDefaultHeader('Connection', 'keep-alive');
		$this->httpClient->setDefaultHeader('Host', 'hotelservice.hrs.com');
		$this->httpClient->setDefaultHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; rv:18.0) Gecko/20100101 Firefox/18.0');



		$this->httpClient->get("https://admin.bookings.org/hotel/hoteladmin/login.html");
		$response = $this->httpClient->currentResponse();
		$body = $response['body'];
		$sesMatch = array();
		preg_match('/ses=([^;]*);/', $body, $sesMatch);
		if(count($sesMatch) < 2) {
			preg_match('/id="ses" name="ses" value="([^"]*)"/', $body, $sesMatch);
		}
		$this->sessionId = $sesMatch[1];

		// wait 1000 milliseconds
		sleep(1);

		$this->httpClient->get("https://admin.bookings.org/hotel/hoteladmin/login.html");


		// $this->_fetchLoadAndNavTimes();

		echo "Sending login request with session id: " . $this->sessionId . "...<br>\n";
		$postData = array();
		$postData['login'] = 'Login';
		$postData['ses'] = $this->sessionId;
		$postData['loginname'] = '185246';
		$postData['password'] = '4412';
		$postData['lang'] = 'xu';
		$this->httpClient->post("https://admin.bookings.org/hotel/hoteladmin/login.html", $postData, false, array(), array('Referer'=> 'https://admin.booking.com/hotel/hoteladmin/login.html'));
		$this->httpClient->get("https://admin.bookings.org/hotel/hoteladmin/general/home.html?hotel_id=185246;t=" . time() . ';ses=' . $this->sessionId);
		$response = $this->httpClient->currentResponse();
		$matches = array();
		preg_match('/Maverick Hostel/', $response['body'], $matches);
		if(count($matches) > 1 || (count($matches) == 1 && $matches[0] == 'Maverick Hostel') ) {
			echo "Login success.<br>\n";
		} else {
			echo "<b>ERROR:</b> bookings.org login not successful<br> (" . count($matches) . ")\n";
		}
		$this->addResponse('login', $response['body'], $response['headers']);
		echo "done.<br>\n";
		echo "<b>bookings.org synchronization init finished</b><br><br><br>";
	}



	var $roomMap = array(
		array(
			'roomName' => 'The_Blue_Brothers_6_Bed',
			'roomIds' => array(35),
			'ro' => '18524604'
			),
		array(
			'roomName' => 'Mss_Peach_5_Bed',
			'roomIds' => array(36),
			'ro' => '18524603'
			),
		array(
			'roomName' => 'Double_room_shared_bathroom',
			'roomIds' => array(39, 40),
			'ro' => '18524602'
			),
		array(
			'roomName' => 'Double_room_private_bathroom_ensuites_with_NEW_rooms',
			'roomIds' => array(46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58),
			'ro' => '18524601'
			),
		array(
			'roomName' => 'NEW_Maverick_ensuite_3_Bed',
			'roomIds' => array(59),
			'ro' => '18524605'
			),
		array(
			'roomName' => 'NEW_Maverick_ensuite_4_Bed',
			'roomIds' => array(60),
			'ro' => '18524607'
			),
		array(
			'roomName' => 'NEW_Maverick_ensuite_5_Bed',
			'roomIds' => array(61),
			'ro' => '18524606'
			)
	);



	/////////////////////////////////////////////////////////////
	// IMPORTANT!!!!!!!!!
	// Here the availability is by the rooms (not by beds)!!!! 
	/////////////////////////////////////////////////////////////
	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "<b>bookings.org synchronization update</b><br>";

		sleep(4);

		$this->httpClient->get("https://admin.bookings.org/hotel/availadmin/index.html?ses=" . $this->sessionId) . "&hotel_id=185246";
		$response = $this->httpClient->currentResponse();
		$this->addResponse('rates_and_availability', $response['body'], $response['headers']);
		$this->_fetchLoadAndNavTimes();


		sleep(1);
		$this->httpClient->get("https://admin.bookings.org/hotel/availadmin/roomrate.html?ses=" . $this->sessionId . ';hotel_id=185246');
		$response = $this->httpClient->currentResponse();
		$this->addResponse('room_rate_category_settings', $response['body'], $response['headers']);
		$this->_fetchLoadAndNavTimes();

		$endTS = strtotime("$endYear-$endMonth-$endDay");
		foreach($this->roomMap as $oneRoomMap) {
			sleep(1);
			echo "Updating price and availability for room: " . $oneRoomMap['roomName'] . "...<br>\n";
			$this->httpClient->get("https://admin.bookings.org/hotel/availadmin/matrix.html?hotel_id=185246;ses=" . $this->sessionId . ";ro=" . $oneRoomMap['ro'] . ';ba=0;ra=');
			$this->_fetchLoadAndNavTimes();

			$postData = array();
			$postData['dfd'] = $startDay;
			$postData['dfym'] = $startYear . '-' . $startMonth;
			$postData['dld'] = $endDay;
			$postData['dlym'] = $endYear . '-' . $endMonth;
			$postData['first_date_old'] = $startYear . '-' . $startMonth . '-' . $startDay;
			$postData['last_date_old'] = $endYear . '-' . $endMonth . '-' . $endDay;
			$postData['hotel_id'] = '185246';
			$postData['go'] = 'Select Period';
			$this->httpClient->post("https://admin.bookings.org/hotel/availadmin/matrix.html?ses=" . $this->sessionId, $postData);
			$response = $this->httpClient->currentResponse();
			$this->addResponse('before_price_update_' . $oneRoomMap['roomName'], $response['body'], $response['headers']);

			$postData = array();
			$body = $response['body'];
			$match = array();
			//$useF1 = strpos($body, 'f1pr0') > 0;
			//if(count($match) > 1) {
				$useF1 = true;
			//}
			$match = array();
			preg_match('/form id="matrix" method="post" action="([^"]*)"/', $body, $match);
			if(count($match) < 2) {
				echo "<b>ERROR:</b> Cannot get form action for room. Skipping its update...<br>\n";
			}
			$formElementActionAttr = $match[1];
			$postData['hotel_id'] = "185246";
			$postData['ses'] = $this->sessionId;
			$match = array();
			preg_match('/input type="hidden" name="op" value="([^"]*)"/', $body, $match);
			if(count($match) < 2) {
				echo "<b>ERROR:</b> Cannot get form parameter(op) for room. Skipping its update...<br>\n";
			}
			$postData['op'] = $match[1];
			$match = array();
			preg_match('/input type="hidden" name="dt" value="([^"]*)"/', $body, $match);
			$postData['dt'] = $match[1];
			if(count($match) < 2) {
				echo "<b>ERROR:</b> Cannot get form parameter(dt) for room. Skipping its update...<br>\n";
			}
			$match = array();
			preg_match('/input type="hidden" name="post_id" value="([^"]*)"/', $body, $match);
			if(count($match) < 2) {
				echo "<b>ERROR:</b> Cannot get form parameter(post_id) for room. Skipping its update...<br>\n";
			}
			$postData['post_id'] = $match[1];
			$postData['update_1'] = "Update";

			$currDate = "$startYear-$startMonth-$startDay";
			$idx = 0;
			do {
				$currTS = strtotime($currDate);
				$currYear = date('Y', $currTS);
				$currMonth = date('m', $currTS);
				$currDay = date('d', $currTS);

				$key = 'f0pr' . $idx;
				if($useF1) {
					$key = 'f1pr' . $idx;
				}
				$postData[$key] = getRoomPrice($currYear, $currMonth, $currDay, $rooms[$oneRoomMap['roomIds'][0]]);		// Price for the night of $currentDate
				$numOfAvailRooms = 0;
				foreach($oneRoomMap['roomIds'] as $roomId) {
					$numOfAvailBeds = getNumOfAvailBeds($rooms[$roomId], $currDate);
					//echo "For roomId: $roomId, for date: $currDate, the avail beds: $numOfAvailBeds<br>\n";
					if(!isset($rooms[$roomId])) {
						echo "<b>ERROR:</b> for room[" . $oneRoomMap['roomName'] . "] the room id: $roomId is not found in the loaded rooms!<br>\n";
					} elseif($numOfAvailBeds == $rooms[$roomId]['num_of_beds']) {
						$numOfAvailRooms += 1;
					}
				}
				$key = 'f0ri' . $idx;
				if($useF1) {
					$key = 'f1ri' . $idx;
				}
				$postData[$key] = $numOfAvailRooms;
	
				$key = 's0ri' . $idx;
				if($useF1) {
					$key = 's1ri' . $idx;
				}
				$match = array();
				preg_match('/input type="hidden" name="' . $key . '" value="([^"]*)"/', $body, $match);
				if(count($match) > 1) {
					$postData[$key] = $match[1];
				}

				$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
				$idx += 1;
			} while($currTS < $endTS);

			$url = 'https://admin.bookings.org/hotel/availadmin/matrix.html' . $formElementActionAttr;
			//echo "Sending post data for update bookings.org (url: $url), formdata: <br><pre>\n";
			//print_r($postData);
			//echo "</pre>\n";
			$this->httpClient->post($url, $postData);
			$response = $this->httpClient->currentResponse();
			$this->addResponse('after_price_update_' . $oneRoomMap['roomName'], $response['body'], $response['headers']);
			echo "done.<br>\n";

			$this->_fetchLoadAndNavTimes();
		}

		/*
		echo "Updating room availability...<br>\n";
		$this->httpClient->get("https://admin.bookings.org/hotel/availadmin/set_room_inventory.html?ses=" . $this->sessionId . ";hotel_id=185246");
		$postData = array();
		$postData['change_period']  = 'Update Date Range';
		$postData["dfym"] = $startYear . '-' . $startMonth;
		$postData["dfd"] = $startDay;
		$postData["dlym"] = $endYear . '-' . $endMonth;
		$postData["dld"] = $endDay;
		$postData["hotel_id"] = '185246';
		$postData['shortcut'] = '';
		$this->httpClient->post("https://admin.bookings.org/hotel/availadmin/set_room_inventory.html?ses=" . $this->sessionId, $postData);
		$response = $this->httpClient->currentResponse();
		$body = $response['body'];
		$this->addResponse('before_availability_update', $body, $response['headers']);
		$postData = array();
		$postData["dfym"] = $startYear . '-' . $startMonth;
		$postData["dfd"] = $startDay;
		$postData["dlym"] = $endYear . '-' . $endMonth;
		$postData["dld"] =$endDay;
		$postData["dt"] = '';
		$postData["grid_update"] = "Update Grid Overview";
		foreach($this->roomMap as $oneRoomMap) {
			$currDate = "$startYear-$startMonth-$startDay";
			do {
				$currTS = strtotime($currDate);
				$currYear = date('Y', $currTS);
				$currMonth = date('m', $currTS);
				$currDay = date('d', $currTS);
				$numOfAvailRooms = 0;
				foreach($oneRoomMap['roomIds'] as $roomId) {
					$numOfAvailBeds = getNumOfAvailBeds($rooms[$roomId], $currDate);
					//echo "For roomId: $roomId, for date: $currDate, the avail beds: $numOfAvailBeds<br>\n";
					if(!isset($rooms[$roomId])) {
						echo "<b>ERROR:</b> for room[" . $oneRoomMap['roomName'] . "] the room id: $roomId is not found in the loaded rooms!<br>\n";
					} elseif($numOfAvailBeds == $rooms[$roomId]['num_of_beds']) {
						$numOfAvailRooms += 1;
					}
				}
				//echo "For room: " . $oneRoomMap['roomName'] . " ($roomId) for date: $currDate the avail rooms are: $numOfAvailRooms<br>\n";
				if($numOfAvailRooms < 1)
					$numOfAvailRooms = '';

				$paramName = 'rir_' . $oneRoomMap['ro'] . '_' . $currYear . $currMonth . $currDay;
				$oparamName = 'o_rir_' . $oneRoomMap['ro'] . '_' . $currYear . $currMonth . $currDay;
				$postData[$paramName] = $numOfAvailRooms;
				$match = array();
				preg_match("/'$oparamName' value = '([^']*)'/", $body, $match);
				$postData[$oparamName] = $match[1];
				$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
			} while($currTS < $endTS);
		}

		echo "Setting the available rooms now...<br><pre>\n";
		print_r($postData);
		echo "</pre><br>\n";
		$this->httpClient->post("https://admin.bookings.org/hotel/availadmin/set_room_inventory.html?ses=" . $this->sessionId . ";hotel_id=", $postData);
		$response = $this->httpClient->currentResponse();
		$this->addResponse('update_availability', $response['body'], $response['headers']);
		 */

		echo "<b>bookings.org synchronization update finished</b><br><br><br>";
	}


	function shutdown() {
		echo "<b>bookings.org synchronization shutdown</b><br>";
		echo "Sending logout request...<br>\n";
		$this->httpClient->get("https://admin.bookings.org/hotel/login.html?ses=" . $this->sessionId . ";logout=1");
		$response = $this->httpClient->currentResponse();
		$this->addResponse('logout', $response['body'], $response['headers']);
		
		$this->httpClient->reset();
		echo "done.<br>\n";
		echo "<b>bookings.org synchronization shutdown finished</b><br><br><br>";
	}

	function _fetchLoadAndNavTimes() {
		$postData = array();
		$postData['pid'] = $this->pid;
		$postData['jquery_ready'] = '774';
		$postData['window_onload'] = '774';
		$this->httpClient->get("https://admin.bookings.org/load_times", $postData);

		$time = time();
		$postData = array();
		$postData['pid'] = $this->pid;
		$postData['nts'] = '0,0,' . $time . ',0,0,0,0,' . ($time+1) . ',' . ($time+13) . ',' . ($time+90) . ',' . ($time+1) . ',' . ($time+1) . ',undefined,' . ($time+607) . ',' . ($time+704) . ',' . ($time+711) . ',' . ($time+704) . ',' . ($time+3625) . ',' . ($time+3626) . ',' . ($time+3665) . ',' . ($time+5003) . ',' . ($time+5007) . ',' . ($time+5009) . ',' . ($time+1110);
		$this->httpClient->get("https://admin.bookings.org/navigation_times", $postData);


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



$link = db_connect();
$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

echo "Period begining: $startYear - $startMonth - $startDay<br>\n";
echo "Period ending: $endYear - $endMonth - $endDay<br>\n";


$booker = new BookingsBooker();
$booker->init();
$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();
$responseFiles = $booker->saveResponsesInfoFile('bookings_org');
echo "Saved responses: <br>\n";
echo "<ul>\n";
foreach($responseFiles as $key => $filename) {
	echo "	<li><a target=\"_blank\" href=\"$filename\">$key</a></li>\n";
}
echo "</ul>\n";

mysql_close($link);

return;




?>
