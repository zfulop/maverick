<?php

ini_set('display_errors', 'On');

require('HTTP/Client.php'); 
require('HTTP/Request.php');
require('HTTP/Client/CookieManager.php');


require("../includes.php");
require('../room_booking.php');
require('booker.php');



class HostelWorldBooker extends Booker {

	var $httpClient = null;

	function init() {

		echo "<b>HostelWorld.com synchronization init</b><br>\n";

		if(file_exists('captcha/hostelworld.gif')) {
			unlink('captcha/hostelworld.gif');
		}

		$this->httpClient = new Http_Client();

		$retCode = $this->httpClient->get("https://secure.hostelworld.com/inbox/");
		$response = $this->httpClient->currentResponse();

		$matches = array();
		echo "Parsing login page...<br>\n";
		preg_match('/\<img\s*class="captchaImage"\s*src="([^"]*)"/', $response['body'], $matches);
		echo "Login page parsed.<br>\n";

		if(count($matches) < 2) {
			echo "ERROR: Cannot find captchaImage!!!<br><br>\n\n";
			print_r($matches);
			return false;
		}

		$imgUrl = $matches[1];

		//echo "Cookie manager: <br>\n<pre>";
		//print_r($this->httpClient->getCookieManager());
		//echo "</pre>\n";


		echo "Downloading captcha img from: $imgUrl . <br>\n";

		$this->httpClient->get($imgUrl);
		$response = $this->httpClient->currentResponse();
		file_put_contents('captcha/hostelworld.gif', $response['body']);

		if(file_exists('captcha/hostelworld.txt')) {
			unlink('captcha/hostelworld.txt');
		}


		while(!file_exists('captcha/hostelworld.txt')) {
			sleep(2);
		}

		$txt = file_get_contents('captcha/hostelworld.txt');

		echo "Received captcha text ($txt).<br>\n";
		echo "Sending login request with captcha text...<br>\n";

		$postData = array();
		$postData['HostelNumber'] = '30688';
		$postData['Username'] = '';
		$postData['Password'] = 'MaV*777';
		$postData['ImageText'] = $txt;
		$this->httpClient->post("https://secure.hostelworld.com/inbox/trylogin.php", $postData);

		$response = $this->httpClient->currentResponse();
		$this->addResponse('login', $response['body'], $response['headers']);
		echo "done.<br>\n";

		echo "cleaning up captcha data (image, text).<br>\n";
		unlink('captcha/hostelworld.gif');
		unlink('captcha/hostelworld.txt');

		echo "<b>HostelWorld.com synchronization init finished</b><br><br><br>";
	}

	
	var $idMap = array(
		'42' => '50927', /* Mr Green */
		'35' => '50928', /* The Blue Brothers */
		'36' => '50929', /* Mss Peach */
		'39' => '50930', /* Mr and Mss Yellow */
		'40' => '50931', /* Ms Lemon */
		'46' => '99298', /* Maverick ensuites - Mia */
		'48' => '99299', /* Maverick ensuites - Jules */
		'49' => '99300', /* Maverick ensuites - Vincent */
		'50' => '99301', /* Maverick ensuites - Butch */
		'51' => '99302',  /* Maverick ensuites - Honey Bunny */
		'52' => '161724',  /* NEW Maverick ensuites - dbl room with bathroom */
		'53' => '161726',  /* NEW Maverick ensuites - dbl room with bathroom */
		'54' => '160962',  /* NEW Maverick ensuites - dbl room with bathroom */
		'55' => '161727',  /* NEW Maverick ensuites - dbl room with bathroom */
		'56' => '161728',  /* NEW Maverick ensuites - dbl room with bathroom */
		'57' => '161729',  /* NEW Maverick ensuites - dbl room with bathroom */
		'58' => '161730',  /* NEW Maverick ensuites - dbl room with bathroom */
		'59' => '161595',  /* NEW Maverick ensuites - 3 beds */
		'60' => '161596',  /* NEW Maverick ensuites - 4 beds */
		'61' => '161597'   /* NEW Maverick ensuites - 5 beds - Taken out as per the agreement with HW */
	);

	var $roomIdsToLeaveAsIs = array(
		'279201',		/* 4 bed private ensuite */
		'281907'		/* 8 Bed Apartment Ensuite */
		'344255'		/* 5 Bed dorm */
	);




	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "<b>HostelWorld.com synchronization update</b><br>";

		echo "Updating availability.<br>\n";
		$this->httpClient->get("https://secure.hostelworld.com/inbox/availability/shortterm.php");

		$postData = array();
		$startDate = $startYear . '-' . $startMonth . '-' . $startDay;
		$endDate = $endYear . '-' . $endMonth . '-' . $endDay;

		$postData['startDate'] = $startDate;
		$postData['selMonthStart'] = $startMonth;
		$postData['selDayStart'] = $startDay;
		$postData['selYearStart'] = $startYear;

		$postData['endDate'] = $endDate;
		$postData['selMonthEnd'] = $endMonth;
		$postData['selDayEnd'] = $endDay;
		$postData['selYearEnd'] = $endYear;

		$postData['SaveAllocationShortTerm'] = '0';
		$postData['UpdateDates'] = '1';
		$postData['selAllocatedTo'] = 'WRI';

		$this->httpClient->get("https://secure.hostelworld.com/inbox/availability/shortterm.php", $postData);
		$response = $this->httpClient->currentResponse();
		$body = $response['body'];
		$this->addResponse('before_update_availability', $body, $response['headers']);

		$postData = array();
		foreach($rooms as $roomId => $roomData) {
			if(!isset($this->idMap[$roomId])) {
				continue;
			}
			$myRoomId = $this->idMap[$roomId];
			for($currDate = $startDate; $currDate <= $endDate; $currDate = date("Y-m-d", strtotime($currDate . " +1 day"))) {
				$postDataKey = 'lbl_' . $myRoomId . '_' . str_replace('-', '_', $currDate);
				$numOfAvailBeds = getNumOfAvailBeds(&$roomData, $currDate);
				if($roomData['type'] != 'DORM' and $numOfAvailBeds < $roomData['num_of_beds']) {
					$numOfAvailBeds = 0;
				}
				$postData[$postDataKey] = $numOfAvailBeds;
			}
			
		}

		// For these rooms leave the number as it is already.
		// That is getting the value from the input field loaded from HW and setting it in the postData
		foreach($this->roomIdsToLeaveAsIs as $myRoomId) {
			for($currDate = $startDate; $currDate <= $endDate; $currDate = date("Y-m-d", strtotime($currDate . " +1 day"))) {
				$postDataKey = 'lbl_' . $myRoomId . '_' . str_replace('-', '_', $currDate);
				$matches = array();
				preg_match('/id="' . $postDataKey . '" value="([^"]*)"/', $body, $matches);
				$numOfAvailBeds = $matches[1];
				$postData[$postDataKey] = $numOfAvailBeds;
			}
		}

		$postData['startDate'] = $startDate;
		$postData['selMonthStart'] = $startMonth;
		$postData['selDayStart'] = $startDay;
		$postData['selYearStart'] = $startYear;

		$postData['endDate'] = $endDate;
		$postData['selMonthEnd'] = $endMonth;
		$postData['selDayEnd'] = $endDay;
		$postData['selYearEnd'] = $endYear;

		$postData['SaveAllocationShortTerm'] = '1';
		$postData['UpdateDates'] = '0';
		$postData['startDate'] = $startDate;
		$postData['endDate'] = $endDate;
		$postData['selAllocatedTo'] = 'WRI';

		echo "Sending update request...<br>\n";
		//echo "Post data: <pre>" . print_r($postData, true) . "</pre><br>\n";
		$this->httpClient->post("https://secure.hostelworld.com/inbox/availability/shortterm.php", $postData);
		$response = $this->httpClient->currentResponse();
		$this->addResponse('after_update_availability', $response['body'], $response['headers']);
		echo "done.<br>\n";

		echo "Updating prices.<br>\n";
		$this->httpClient->get("https://secure.hostelworld.com/inbox/availability/dailyrate_availability.php");

		$postData = array();
		$postData['startDate'] = $startDate;
		$postData['endDate'] = $endDate;
		$postData['UpdateDates'] = '1';
		$postData['SaveDailyRatesAvailability'] = '0'; 
		$postData['selAllocatedTo'] = 'WRI'; 
		$this->httpClient->get("https://secure.hostelworld.com/inbox/availability/dailyrate_availability.php", $postData);
		$response = $this->httpClient->currentResponse();
		$body = $response['body'];
		$this->addResponse('before_update_price', $body, $response['headers']);

		$postData = array();
		foreach($rooms as $roomId => $roomData) {
			if(!isset($this->idMap[$roomId])) {
				continue;
			}
			$myRoomId = $this->idMap[$roomId];
			for($currDate = $startDate; $currDate <= $endDate; $currDate = date("Y-m-d", strtotime($currDate . " +1 day"))) {
				$postDataKey = 'lbl_' . $myRoomId . '_' . str_replace('-', '_', $currDate);
				list($year, $month, $day) = explode('-', $currDate);
				$numOfAvailBeds = getNumOfAvailBeds(&$roomData, $currDate);
				if($roomData['type'] != 'DORM' and $numOfAvailBeds < $roomData['num_of_beds']) {
					$numOfAvailBeds = 0;
				}
				$postData[$postDataKey] = $numOfAvailBeds;
				$postDataKey = 'rates_' . $myRoomId . '_' . str_replace('-', '_', $currDate);
				$priceOfBed = getBedPrice($year, $month, $day, &$roomData);
				$postData[$postDataKey] = $priceOfBed;
			}
		}

		// For these rooms leave the number as it is already.
		// That is getting the value from the input field loaded from HW and setting it in the postData
		foreach($this->roomIdsToLeaveAsIs as $myRoomId) {
			for($currDate = $startDate; $currDate <= $endDate; $currDate = date("Y-m-d", strtotime($currDate . " +1 day"))) {
				$postDataKey = 'lbl_' . $myRoomId . '_' . str_replace('-', '_', $currDate);
				$matches = array();
				preg_match('/id="' . $postDataKey . '" value="([^"]*)"/', $body, $matches);
				if(count($matches) < 2) {
					unset($postData[$postDataKey]);
				} else {
					$numOfAvailBeds = $matches[1];
					$postData[$postDataKey] = $numOfAvailBeds;
				}
				$postDataKey = 'rates_' . $myRoomId . '_' . str_replace('-', '_', $currDate);
				$matches = array();
				preg_match('/id="' . $postDataKey . '" title="[^"]*" value="([^"]*)"/', $body, $matches);
				if(count($matches) < 2) {
					unset($postData[$postDataKey]);
				} else {
					$prc = $matches[1];
					$postData[$postDataKey] = $prc;
				}
			}
		}


		$postData['startDate'] = $startDate;
		$postData['endDate'] = $endDate;
		$postData['UpdateDates'] = '0';
		$postData['SaveDailyRatesAvailability'] = '1'; 
		$postData['selAllocatedTo'] = 'WRI'; 
		echo "Sending update request...<br>\n";
		//echo "<pre>" . print_r($postData, true) . "</pre>\n";
		$this->httpClient->post("https://secure.hostelworld.com/inbox/availability/dailyrate_availability.php", $postData);
		$response = $this->httpClient->currentResponse();
		$body = $response['body'];
		$this->addResponse('after_update_price', $body, $response['headers']);
		echo "done.<br>\n";


		echo "<b>HostelWorld.com synchronization update finished</b><br><br><br>";
	}


	function shutdown() {
		echo "<b>HostelWorld.com synchronization shutdown</b><br>";
		echo "Sending logout request...<br>\n";
		$this->httpClient->get("https://secure.hostelworld.com/inbox/logout.php");
		$response = $this->httpClient->currentResponse();
		$this->addResponse('logout', $response['body'], $response['headers']);

		$this->httpClient->reset();
		echo "done.<br>\n";
		echo "<b>HostelWorld.com synchronization shutdown finished</b><br><br><br>";
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


$booker = new HostelWorldBooker();
$booker->init();
$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();
$responseFiles = $booker->saveResponsesInfoFile('hostel_world');
echo "Saved responses: <br>\n";
echo "<ul>\n";
foreach($responseFiles as $key => $filename) {
	echo "	<li><a target=\"_blank\" href=\"$filename\">$key</a></li>\n";
}
echo "</ul>\n";

mysql_close($link);

return;




?>
