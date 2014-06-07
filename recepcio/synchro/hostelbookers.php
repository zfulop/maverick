<?php

ini_set('display_errors', 'On');

require('HTTP/Client.php'); 
require('HTTP/Request.php');
require('HTTP/Client/CookieManager.php');


require("../includes.php");
require('../room_booking.php');
require('../includes/simple_html_dom.php');
require('booker.php');



class HostelBookersBooker extends Booker {

	var $httpClient = null;

	function init() {

		echo "<b>HostelBookers.com synchronization init</b><br>";

		$this->httpClient =& new Http_Client();
		$this->httpClient->get('http://admin.hostelbookers.com/backoffice/');
		$response = $this->httpClient->currentResponse();
		$this->addResponse('prelogin', $response['body'], $response['headers']);
			
		// Received cookies: 
		// ARPT=NKYYJYS192.168.6.123CKOIK; domain=.hostelbookers.com; path=/ 
		// CFID=5085934;domain=.hostelbookers.com;expires=Thu, 08-Dec-2039 14:55:30 GMT;path=/ 
		// CFTOKEN=8238e29e15bb3294-92D63858-24E8-4E17-BEB8AB386D0E4F3D;domain=.hostelbookers.com;expires=Thu, 08-Dec-2039 14:55:30 GMT;path=/

		//echo "Cookie manager: (should have cookies: ARPT, CFID, CFTOKEN)<br>\n<pre>";
		//print_r($this->httpClient->_cookieManager);
		//echo "</pre>\n";


		$postData = array();
		$postData['fuseaction'] = 'auth';
		$postData['area'] = 'backoffice';
		$postData['lastpage'] = '/backoffice/booking/';
		$postData['strLogin'] = 'mavrichos';
		$postData['strPassword'] = 'Palinka1';
		echo "sending login request...<br>\n";
		$this->httpClient->post("https://admin.hostelbookers.com/login/index.cfm", $postData);
		$response = $this->httpClient->currentResponse();
		$this->addResponse('login', $response['body'], $response['headers']);
		echo "done.<br>\n";

		// Received cookie:
		// LOGGED=92D14FCA%2D24E8%2D4E17%2DBEB734AA07619EA4;path=/

		echo "Cookie manager: (should have cookies: ARPT, CFID, CFTOKEN, LOGGED)<br>\n<pre>";
		print_r($this->httpClient->_cookieManager);
		echo "</pre>\n";

		$fwdUrl = null;
		$html = str_get_html($response['body']);
		$aElements = $html->find('a');
		echo "There are " . count($aElements) . " anchor elements in the response (there should be at least one).<br>\n";
		$urlStart = '/backoffice/booking/?login';
		foreach($aElements as $anchor)  {
			echo "Checking href: " . $anchor->getAttribute('href') . "...<br>\n";
			if(substr($anchor->getAttribute('href'), 0, strlen($urlStart)) == $urlStart) {
				$fwdUrl = $anchor->getAttribute('href');
				break;
			}
		}

		if(is_null($fwdUrl)) {
			echo "<b>ERROR:</b> Cannot find fwd url in Hostel bookers login!!!<br>\n";
			//echo " response: <pre>" . $response['body'] . "</pre>\n";
			return;
		}

		echo "After login forwarding to url: $fwdUrl<br>\n";
		$this->httpClient->get($fwdUrl);
		$response = $this->httpClient->currentResponse();
		$this->addResponse('login_fwd', $response['body'], $response['headers']);

		//echo "Cookie manager: \n";
		//print_r($this->httpClient->_cookieManager);
		echo "<b>HostelBookers.com synchronization init finished</b><br><br><br>";

	}


	var $idMap = array(
		'42' => '83742', /* Mr Green */
		'35' => '83743', /* The Blue Brothers */
		'36' => '83744', /* Mss Peach */
		'39' => '83745', /* Mr and Mss Yellow */
		'40' => '83746', /* Ms Lemon */
		'46' => '108681', /* Maverick ensuites - Mia */
		'48' => '108682', /* Maverick ensuites - Jules */
		'49' => '108683', /* Maverick ensuites - Vincent */
		'50' => '108684', /* Maverick ensuites - Butch */
		'51' => '108685',  /* Maverick ensuites - Honey Bunny */
		'52' => '145622',  /* NEW Maverick ensuites - dbl room with bathroom */
		'53' => '146113',  /* NEW Maverick ensuites - dbl room with bathroom */
		'54' => '146114',  /* NEW Maverick ensuites - dbl room with bathroom */
		'55' => '146115',  /* NEW Maverick ensuites - dbl room with bathroom */
		'56' => '146116',  /* NEW Maverick ensuites - dbl room with bathroom */
		'57' => '146117',  /* NEW Maverick ensuites - dbl room with bathroom */
		'58' => '146118',  /* NEW Maverick ensuites - dbl room with bathroom */
		'59' => '146060',  /* NEW Maverick ensuites - 3 beds */
		'60' => '146061',  /* NEW Maverick ensuites - 4 beds */
		'61' => '146062'  /* NEW Maverick ensuites - 5 beds */
	);




	/*
	 */
	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "<b>HostelBookers.com synchronization update</b><br>";
		$endTS = strtotime("$endYear-$endMonth-$endDay");
		$currTS = strtotime("$startYear-$startMonth-$startDay");
		do {
			$this->internal_update(date('Y', $currTS), date('m', $currTS), date('d', $currTS), $rooms, $endYear, $endMonth, $endDay);
			$currTS = strtotime(date('Y-m-d', $currTS) . " +14 day");
		} while($currTS < $endTS);
		echo "<b>HostelBookers.com synchronization update finished</b><br><br><br>";
	}
	
	function internal_update($startYear, $startMonth, $startDay, &$rooms, $totalEndYear, $totalEndMonth, $totalEndDay) {
		echo "Update period start: $startYear-$startMonth-$startDay<br>\n";

		$this->httpClient->get("https://admin.hostelbookers.com/fusebox/index.cfm", array('fuseaction' => 'c_availabilityGrid.displayAvailabilityGrid'));
		$response = $this->httpClient->currentResponse();
		$this->addResponse("availability", $response['body'], $response['headers']);

		$html = str_get_html($response['body']);
		$inputElements = $html->find('form[name=f1] input');
		$postData = array();
		foreach($inputElements as $inputElement)  {
			$postData[$inputElement->getAttribute('name')] = $inputElement->getAttribute('value');
		}

		$postData['day'] = substr($startDay, 0, 1) === '0' ? substr($startDay, 1, 1) : $startDay;
		$postData['month'] = substr($startMonth, 0, 1) === '0' ? substr($startMonth, 1, 1) : $startMonth;
		$postData['year'] = $startYear;
		$postData['showBeds'] = '1';
		$postData['showPrice'] = '1';
		$postData['timeSpan'] = 'ww,2';
		$this->httpClient->post("https://admin.hostelbookers.com/fusebox/index.cfm", $postData);
		$response = $this->httpClient->currentResponse();
		$this->addResponse("availability_for_$startYear-$startMonth-$startDay", $response['body'], $response['headers']);

		echo "Getting input element values ";
		$html = str_get_html($response['body']);
		$inputElements = $html->find('form[name=f2] input');
		$postData = array();
		if(count($inputElements) < 1) {
			echo "<b>ERROR:</b>Cannot get data from hostelbooker site. Reload this frame to rerun synch with hostelbookers!!!<br>\n";
		}
		foreach($inputElements as $inputElement)  {
			$postData[$inputElement->getAttribute('name')] = $inputElement->getAttribute('value');
		}
		echo "<br>\n";


		$endTS = strtotime("$startYear-$startMonth-$startDay +14 day");
		$endYear = date('Y', $endTS);
		$endMonth = date('m', $endTS);
		$endDay = date('d', $endTS);

		echo "Updating period: $startYear-$startMonth-$startDay to $endYear-$endMonth-$endDay<br>\n";

		// The day of availablility is mapped like this:
		// 40882 - Dec 5, 2011
		// 40883 - Dec 6, 2011
		// ...

		$changed = '0';
		$startDaysNum = gregoriantojd((substr($startMonth, 0, 1) === '0' ? substr($startMonth, 1, 1) : $startMonth), (substr($startDay, 0, 1) === '0' ? substr($startDay, 1, 1) : $startDay), $startYear) - gregoriantojd(12, 5, 2011) + 40882;
		foreach($rooms as $roomId => $roomData) {
			if(!in_array($roomId, array_keys($this->idMap))) {
				echo "<b>WARNING</b> Room: " . $roomData['name'] . " is not being updated here.<br>\n";
				continue;
			}
			$myRoomId = $this->idMap[$roomId];
			for($day = 0; $day < 14; $day++) {
				$t = strtotime("$startYear-$startMonth-$startDay +$day day");
				$currYear = date("Y", $t);
				$currMonth = date("m", $t);
				$currDay = date("d", $t);
				$currDate = $currYear . '-' . $currMonth . '-' . $currDay;
				$days = $startDaysNum + $day;
				$postDataKey = $days . '_' . $myRoomId;
				if($currDate <= "$totalEndYear-$totalEndMonth-$totalEndDay") {
					$numOfAvailBeds = getNumOfAvailBeds(&$roomData, $currDate);
/*					// For Mr. Green the number of available beds has to be at least 1.
					if($roomId == 42 and $numOfAvailBeds == 0) { 
						echo "For Mr. Green for the day of $currDate there are no available beds, but saying that there is one for the hostel to show up.<br>\n";
						$numOfAvailBeds = 1;
					}*/
					if(!isset($postData['beds_' . $postDataKey])) {
						echo "<b>ERROR:</b> for date: $currDate and room: " . $roomData['name'] . " the key: 'beds_$postDataKey' not found... (not updating that day and room)<br>\n";
					} elseif(intval($postData['beds_' . $postDataKey]) != $numOfAvailBeds) {
						$postData['beds_' . $postDataKey] = $numOfAvailBeds;
						$changed .= ',beds_' . $postDataKey;
					}
					$prc = getBedPrice($currYear, $currMonth, $currDay, $roomData) . '.00';
					if(!isset($postData['price_' . $postDataKey])) {
						echo "<b>ERROR:</b> for date: $currDate and room: " . $roomData['name'] . " the key: 'price_$postDataKey' not found... (not updating that day and room)<br>\n";
					} elseif($postData['price_' . $postDataKey] != $prc) {
						$postData['price_' . $postDataKey] = $prc;
						$changed .= ',price_' . $postDataKey;
					}
				}
			}
		}
		$postData['changed'] = $changed;

		echo "Sending update request...<br>\n";
		$this->httpClient->post("https://admin.hostelbookers.com/fusebox/index.cfm", $postData);
		$response = $this->httpClient->currentResponse();
		$this->addResponse("update_$startYear-$startMonth-$startDay", $response['body'], $response['headers']);
		echo "done.<br>\n";
	}

	function shutdown() {
		echo "<b>HostelBookers.com synchronization shutdown</b><br>";
		$postData = array();
		$postData['fuseaction'] = 'logout';
		echo "Sending logout request...<br>\n";
		$this->httpClient->get("https://admin.hostelbookers.com/backoffice/index.cfm", $postData);
		$response = $this->httpClient->currentResponse();
		$this->addResponse('logout', $response['body'], $response['headers']);

		$this->httpClient->reset();
		echo "done.<br>\n";
		echo "<b>HostelBookers.com synchronization shutdown finished</b><br><br><br>";
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


$booker = new HostelBookersBooker();
$booker->init();
$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();
$responseFiles = $booker->saveResponsesInfoFile('hostel_bookers');
echo "Saved responses: <br>\n";
echo "<ul>\n";
foreach($responseFiles as $key => $filename) {
	echo "	<li><a target=\"_blank\" href=\"$filename\">$key</a></li>\n";
}
echo "</ul>\n";

mysql_close($link);

return;




?>
