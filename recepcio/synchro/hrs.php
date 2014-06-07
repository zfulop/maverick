<?php

ini_set('display_errors', 'On');

require('HTTP/Client.php'); 
require('HTTP/Request.php');
require('HTTP/Client/CookieManager.php');


require("../includes.php");
require("../includes/simple_html_dom.php");
require('../room_booking.php');
require('booker.php');



class HRSBooker extends Booker {

	var $httpClient = null;
	var $jSessionId = null;
	var $portalToken = null;

	function init() {

		echo "<b>hotelservice.hrs.com synchronization init</b><br>\n";

		/*
		if(file_exists('captcha/hrs.png')) {
			unlink('captcha/hrs.png');
		}
		if(file_exists('captcha/hrs.txt')) {
			unlink('captcha/hrs.txt');
		}
		 */


		$this->httpClient = new Http_Client();

		$this->httpClient->setDefaultHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
		$this->httpClient->setDefaultHeader('Accept-Encoding', 'gzip, deflate');
		$this->httpClient->setDefaultHeader('Accept-Language', 'en-US,en;q=0.5');
		$this->httpClient->setDefaultHeader('Connection', 'keep-alive');
		$this->httpClient->setDefaultHeader('DNT', '1');
		$this->httpClient->setDefaultHeader('Host', 'hotelservice.hrs.com');
		$this->httpClient->setDefaultHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; rv:18.0) Gecko/20100101 Firefox/18.0');


		$retCode = $this->httpClient->get("https://hotelservice.hrs.com/portal/");
		$response = $this->httpClient->currentResponse();
		$matches = array();
		preg_match('/login.xhtml;jsessionid=([^"]*)"/', $response['body'], $matches);
		$jsessionid = $matches[1];
		$this->jSessionId = $jsessionid;
		$matches = array();
		preg_match('/id="javax.faces.ViewState" value="([^"]*)"/', $response['body'], $matches);
		$viewState = $matches[1];

		$matches = array();
		preg_match('/name="([^"]*)" value="Log on"/', $response['body'], $matches);
		$loginForm_j_idt = $matches[1];

		/*
		$matches = array();
		preg_match('/img id="loginForm:captchaOutput" src="([^"]*)"/', $response['body'], $matches);
		$imgUrl = $matches[1];
		$imgUrl = 'https://hotelservice.hrs.com' . str_replace('&amp;', '&', $imgUrl);
		echo "Downloading captcha img...<br>\n";

		$this->httpClient->get($imgUrl);
		$response = $this->httpClient->currentResponse();
		file_put_contents('captcha/hrs.png', $response['body']);

		echo "Waiting for captcha text";
		while(!file_exists('captcha/hrs.txt')) {
			sleep(1);
			echo ".";
		}
		echo "<br>\n";
		$txt = file_get_contents('captcha/hrs.txt');
		echo "Received captcha text ($txt).<br>\n";

		if(file_exists('captcha/hrs.png')) {
			unlink('captcha/hrs.png');
		}
		if(file_exists('captcha/hrs.txt')) {
			unlink('captcha/hrs.txt');
		}
		 */

		$postData = array();
		$postData['AJAX:EVENTS_COUNT'] = '1';
		$postData['javax.faces.ViewState'] = $viewState;
		$postData['javax.faces.behavior.event'] = 'blur';
		$postData['javax.faces.partial.ajax'] = 'true';
		$postData['javax.faces.partial.event'] = 'blur';
		$postData['javax.faces.partial.execute'] = 'loginForm:hotelKey @component';
		$postData['javax.faces.partial.render'] = '@component';
		$postData['javax.faces.source'] = 'loginForm:hotelKey';
		$postData['lang'] = 'en_US';
		$postData['loginForm'] = 'loginForm';
		$postData['loginForm:captcha'] = '';
		$postData['loginForm:hotelKey'] = '526942';
		$postData['loginForm:password'] = '';
		$postData['loginForm:username'] = 'sync';
		$postData['org.richfaces.ajax.component'] = 'loginForm:hotelKey';

		$this->httpClient->post('https://hotelservice.hrs.com/portal/faces/views/login.xhtml;jsessionid=' . $jsessionid, $postData, false, array(), array('Faces-Request' => 'partial/ajax'));
		$response = $this->httpClient->currentResponse();
		$this->addResponse('xml_ajax_response_before_login', $response['body'], $response['headers']);

		$postData = array();
		$postData['ajaxSubmittedForm'] = 'true';
		$postData['javax.faces.ViewState'] = $viewState;
		$postData['lang'] = 'en_US';
		$postData['loginForm'] = 'loginForm';
		//$postData['loginForm:captcha'] = $txt;
		$postData['loginForm:hotelKey'] = '526942';
		$postData[$loginForm_j_idt] = $loginForm_j_idt;
		$postData['loginForm:password'] = 'Palinka1';
		$postData['loginForm:username'] = 'sync';
		
		//echo "Sending login request with postData: <pre>" . print_r($postData, true) . "</pre><br>\n";
		$this->httpClient->post('https://hotelservice.hrs.com/portal/faces/views/login.xhtml;jsessionid=' . $jsessionid, $postData);
		$response = $this->httpClient->currentResponse();
		$this->addResponse('loggedin', $response['body'], $response['headers']);

		$matches = array();
		preg_match('/name="authToken" value="([^"]*)"/', $response['body'], $matches);
		$authToken = $matches[1];
		$matches = array();
		preg_match('/name="returnToken" value="([^"]*)"/', $response['body'], $matches);
		$returnToken = $matches[1];
		$matches = array();
		preg_match('/id="javax.faces.ViewState" value="([^"]*)"/', $response['body'], $matches);
		$viewState = $matches[1];

		$matches = array();
		preg_match('/Prices .amp. availability[^o]*orm id="([^"]*)"/', $response['body'], $matches);		
		$j_idtCode = $matches[1];

		echo "j_idt...: $j_idtCode<br>\n";

		$postData = array();
		$postData['authToken'] = $authToken;
		$postData['returnToken'] = $returnToken;
		$postData['javax.faces.ViewState'] = $viewState;
		$postData[$j_idtCode] = $j_idtCode;
		$this->httpClient->post('https://hotelservice.hrs.com/hsv3_hotelView/?lang=en_US', $postData);
		$response = $this->httpClient->currentResponse();
		$this->addResponse('get_portalToken', $response['body'], $response['headers']);
		$matches = array();
		preg_match('/id="loginForm" method="post" action="([^"]*)"/', $response['body'], $matches);
		$hotelLoginUrl = $matches[1];
		//echo "hotelLoginUrl (form action): " . $hotelLoginUrl . "<br>\n";
		$this->portalToken = substr($hotelLoginUrl, strpos($hotelLoginUrl, 'portalToken') + strlen('portalToken='));
		$startPos = strpos($hotelLoginUrl, 'jsessionid') + strlen('jsessionid=');
		$endPos = strpos($hotelLoginUrl, '?');
		$this->jSessionId = substr($hotelLoginUrl, $startPos, $endPos - $startPos);
		//echo "portalToken: " . $this->portalToken . "<br>\n";

		$hotelLoginUrl2 = $this->getUrl('hotelLogin.do');
		//echo "HSA site url: $hotelLoginUrl2<br>\n";

		$this->httpClient->post($hotelLoginUrl2, array());
		$response = $this->httpClient->currentResponse();
		$this->addResponse('hsa_main', $response['body'], $response['headers']);
		if(strpos($response['body'], 'Rates + Availability') < 1) {
			echo "<b>ERROR: </b> Cannot get HSA main page<br>\n";
		} else {
			echo "HSA Main page <b>OK</b><br>\n";
		}
		echo "<b>hotelservice.hrs.com synchronization init finished</b><br><br><br>\n\n";
	}

	var $roomIds = array(
		'SINGLE_ROOM' => array('46','48','49','50','51','52','53','54','55','56','57','58'), /* Dbl room private bathroom */
		'DOUBLE_ROOM' => array('46','48','49','50','51','52','53','54','55','56','57','58'), /* Dbl room private bathroom */
		'BUDGET_ROOM' => array('39','40'), /* Dbl room shared bathroom */
		'TRIPLE_ROOM' => array('59','62'), /* Ensuites 3 beds */
		'4BED_ROOM' => array('60','63'), /* Ensuites 4 beds */
	);


	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "<b>hotelservice.hrs.com synchronization update</b><br>\n";

		$ratesAndAvailability = $this->getUrl('showRateCalendar.do');
		$this->httpClient->get($ratesAndAvailability);
		$response = $this->httpClient->currentResponse();
		$this->addResponse('rates_and_availability', $response['body'], $response['headers']);

		$getRateCalendarUrl = $this->getUrl('getRateCalendar.do');
		$postData = array();
		$postData['date.date'] = (substr($startMonth,0,1) == '0' ? substr($startMonth,1,1) : $startMonth) . '/' . (substr($startDay,0,1) == '0' ? substr($startDay,1,1) : $startDay) . '/' . substr($startYear, 2, 2);
		$postData['duration'] = '14';
		$this->httpClient->post($getRateCalendarUrl, $postData);
		$response = $this->httpClient->currentResponse();
		$jsonResponse = json_decode($response['body']);
		if(isset($jsonResponse->error)) {
			echo "<b>ERROR (cannot get rate calendar for specified date):</b> " . $jsonResponse->error->msgTitle . " - " . $jsonResponse->error->msgTitle->msgs[0];
			echo "<b>ABORTING synchronization!</b>\n";
			return;
		}
		$this->addResponse('getRateCalendar', $response['body'], $response['headers']);

		$resultArray = array();
		$resultArray['SINGLE_ROOM'] = array();
		$resultArray['DOUBLE_ROOM'] = array();
		$resultArray['TRIPLE_ROOM'] = array();
		$resultArray['4BED_ROOM'] = array();
		$resultArray['BUDGET_ROOM_SGL'] = array();
		$resultArray['BUDGET_ROOM_DBL'] = array();

		$currDate = "$startYear-$startMonth-$startDay";
		$endDate = "$endYear-$endMonth-$endDay";
		$endTS = strtotime($endDate);
		$currTS = strtotime($currDate);
		while($currTS <= $endTS) {
			echo $currDate . ' ' . time() . "<br>\n";
			$currTS = strtotime($currDate);
			$currDate = date('Y-m-d', $currTS);
			$currYear = date('Y', $currTS);
			$currMonth = date('m', $currTS);
			$currDay = date('d', $currTS);

			// Set the single room (single and double room rates and availability are the same)
			if($this->setRoomRate('SINGLE_ROOM', $rooms, $currTS, false)) {
				$resultArray['SINGLE_ROOM'][$currDate] = 'OK';
			} else {
				$resultArray['SINGLE_ROOM'][$currDate] = 'X';
			}
			sleep(1);
			flush();
			if($this->setRoomRate('DOUBLE_ROOM', $rooms, $currTS, true)) {
				$resultArray['DOUBLE_ROOM'][$currDate] = 'OK';
			} else {
				$resultArray['DOUBLE_ROOM'][$currDate] = 'X';
			}
			sleep(1);
			flush();
			if($this->setSupplementData('TRIPLE_ROOM', $rooms, $currTS)) {
				$resultArray['TRIPLE_ROOM'][$currDate] = 'OK';
			} else {
				$resultArray['TRIPLE_ROOM'][$currDate] = 'X';
			}
			sleep(1);
			flush();
			if($this->setSupplementData('4BED_ROOM', $rooms, $currTS)) {
				$resultArray['4BED_ROOM'][$currDate] = 'OK';
			} else {
				$resultArray['4BED_ROOM'][$currDate] = 'X';
			}
			sleep(1);
			flush();
			if($this->setSupplementData('BUDGET_ROOM', $rooms, $currTS, true)) {
				$resultArray['BUDGET_ROOM_SGL'][$currDate] = 'OK';
			} else {
				$resultArray['BUDGET_ROOM_SGL'][$currDate] = 'X';
			}
			sleep(1);
			flush();
			if($this->setSupplementData('BUDGET_ROOM', $rooms, $currTS, false)) {
				$resultArray['BUDGET_ROOM_DBL'][$currDate] = 'OK';
			} else {
				$resultArray['BUDGET_ROOM_DBL'][$currDate] = 'X';
			}
			sleep(1);
			flush();

			$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
		} 

		$this->displayResult($resultArray);

		echo "<b>hotelservice.hrs.com synchronization update finished</b><br><br><br>\n\n";
	}


	function setRoomRate($roomType, &$rooms, $currTS, $isDoubleRate) {
		$currDate = date('Y-m-d', $currTS);
		$currYear = date('Y', $currTS);
		$currMonth = date('m', $currTS);
		$currDay = date('d', $currTS);
		$currMonthNoZero = date('n', $currTS);
		$currDayNoZero = date('j', $currTS);
		list($availRooms,$prc) = $this->getRoomData($roomType, $rooms, $currYear, $currMonth, $currDay);

		$postData = array('c' => '1', 't' => 'HRS_RATE', 'r' => $roomType, 'd' => $currDay . $currMonth . $currYear);
		$this->httpClient->get($this->getUrl('showEditRate.do'), $postData);
		$postData = array('isEdit' => 'true', 'rateType' => 'HRS_RATE');
		$this->httpClient->get($this->getUrl('showEditRateDetails.do'), $postData);
		$postData = array('forSR' => !$isDoubleRate, 'forDR' => $isDoubleRate, 't' => 'HRS_RATE', 'e' => $currDay . $currMonth . $currYear, 's' => $currDay . $currMonth . $currYear);
		$this->httpClient->post($this->getUrl('checkUpdateDate.do'), $postData);
		$postData = array('forSR' => !$isDoubleRate, 'forDR' => $isDoubleRate, 'e' => $currDay . $currMonth . $currYear, 's' => $currDay . $currMonth . $currYear);
		$postData = array('fieldName' => 'sr.amount', 'fieldValue' => $availRooms, 'formName' => 'editRateForm');
		$this->httpClient->post($this->getUrl('validator'), $postData);
		$postData = array('fieldName' => 'sr.price', 'fieldValue' => $prc, 'formName' => 'editRateForm');
		$this->httpClient->post($this->getUrl('validator'), $postData);

		$saveEditRateUrl = $this->getUrl('saveEditRate.do');
		$postData = array();
		$postData['breakfast'] = 'NOT_AVAILABLE';
		$postData['breakfastType'] = 'INCLUDED';
		$postData['column'] = '1';
		$postData['confirmationCode'] = '';	
		$postData['date.end.date'] = $currMonthNoZero . '/' . $currDayNoZero . '/' . substr($currYear, 2, 2);
		$postData['date.start.date'] = $currMonthNoZero . '/' . $currDayNoZero . '/' . substr($currYear, 2, 2);
		$postData['dr.freesale'] = 'false';
		$postData['sr.freesale'] = 'false';
		$postData['rateType'] = 'HRS_RATE';
		if($isDoubleRate) {
			$postData['forDR'] = 'on';
			$postData['dr.amount'] = $availRooms;
			$postData['dr.price'] = $prc;
			$postData['sr.amount'] = '';
			$postData['sr.price'] = '';
		} else {
			$postData['forSR'] = 'on';
			$postData['dr.amount'] = '';
			$postData['dr.price'] = '';
			$postData['sr.amount'] = $availRooms;
			$postData['sr.price'] = $prc;
		}

		//echo "For $roomType, $currDate, the availability is: $availRooms, prc: $prc, postData: " . print_r($postData, true) . "<br>\n";
		$this->httpClient->post($saveEditRateUrl, $postData);
		$response = $this->httpClient->currentResponse();
		$jsonResponse = json_decode($response['body']);
		if(isset($jsonResponse->businessHints)) {
			// If there is a businessHint, it means that the save has to be confirmed, that is send in again with the confirmation code
			$postData['confirmationCode'] = $jsonResponse->confirmationCode;
			//echo "   postData again: " . print_r($postData, true) . "<br>\n";
			$this->httpClient->post($saveEditRateUrl, $postData);
			$response = $this->httpClient->currentResponse();
			$jsonResponse = json_decode($response['body']);
		}

		if(isset($jsonResponse->success)) {
			return true;
		}
		if(isset($jsonResponse->businessHints)) {
			echo "<b>WARNING (cannot set $roomType for $currDate:</b> " . $jsonResponse->businessHints[0] . "<br>\n";
		}
		if(isset($jsonResponse->businessErrors) and count($jsonResponse->businessErrors) > 0) {
			echo "<b>ERROR (cannot set $roomType for $currDate:</b> " . $jsonResponse->businessErrors[0] . "<br>\n";
		}
		return false;
	}

	function setSupplementData($roomType, &$rooms, $currTS, $doubleRoom = true) {
		$currDate = date('Y-m-d', $currTS);
		$currYear = date('Y', $currTS);
		$currMonth = date('m', $currTS);
		$currDay = date('d', $currTS);
		$currMonthNoZero = date('n', $currTS);
		$currDayNoZero = date('j', $currTS);
		list($availRooms,$prc) = $this->getRoomData($roomType, $rooms, $currYear, $currMonth, $currDay);

		$roomData = $rooms[$this->roomIds[$roomType][0]];
		$s = $roomData['num_of_beds'] == 2 ? 'ECONOMY_ROOM' : ($roomData['num_of_beds'] == 3 ? 'EXTRA_BED_3RD_PERSON' : 'EXTRA_BED_4TH_PERSON');
		$r = $doubleRoom ? 'DOUBLE_ROOM' : 'SINGLE_ROOM';
		$postData = array('t' => 'HRS_RATE', 's' => $s, 'r' => $r, 'd' => $currDay . $currMonth . $currYear);
		//echo "For $roomType, $currDate, the postData is: " . print_r($postData, true) . "<br>\n";
		$this->httpClient->get($this->getUrl('showEditSupplementDetails.do'), $postData);

		$postData = array('e' => $currYear, 's' => $currYear);
		//echo "For $roomType, $currDate, the postData is: " . print_r($postData, true) . "<br>\n";
		$this->httpClient->get($this->getUrl('checkUpdateDateSupplementDetails.do'), $postData);


		$postData = array();
		$postData['confirmationCode'] = '';	
		$postData['date.end.date'] = $currMonthNoZero . '/' . $currDayNoZero . '/' . substr($currYear, 2, 2);
		$postData['date.start.date'] = $currMonthNoZero . '/' . $currDayNoZero . '/' . substr($currYear, 2, 2);
		$postData['dr.amount'] = $availRooms;
		$postData['dr.freesale'] = 'false';
		$postData['dr.price'] = $prc;
		$postData['forDR'] = 'true';
		$postData['forSR'] = 'false';
		$saveEditSupplementDetailsUrl = $this->getUrl('saveEditSupplementDetails.do');
		//echo "For $roomType, $currDate, the availability is: $availRooms, prc: $prc<br>\n";
		//echo "For $roomType, $currDate, the postData is: " . print_r($postData, true) . "<br>\n";
		$this->httpClient->post($saveEditSupplementDetailsUrl, $postData);
		$response = $this->httpClient->currentResponse();
		$jsonResponse = json_decode($response['body']);
		if(isset($jsonResponse->businessHints)) {
			// If there is a businessHint, it means that the save has to be confirmed, that is send in again with the confirmation code
			$postData['confirmationCode'] = $jsonResponse->confirmationCode;
			//echo "For $roomType, $currDate, the postData is: " . print_r($postData, true) . "<br>\n";
			$this->httpClient->post($saveEditSupplementDetailsUrl, $postData);
			$response = $this->httpClient->currentResponse();
			$jsonResponse = json_decode($response['body']);
		}

		if(isset($jsonResponse->success)) {
			return true;
		}
		echo "<b>WARNING - cannot set $roomType for $currDate ";
		if(isset($jsonResponse->businessHints)) {
			echo $jsonResponse->businessHints[0];
		}
		if(isset($jsonResponse->businessErrors) and count($jsonResponse->businessErrors) > 0) {
			echo $jsonResponse->businessErrors[0];
		}
		echo "<br>\n";
		return false;
	}


	function getRoomData($roomType, &$rooms, $currYear, $currMonth, $currDay) {
		$availRooms = 0;
		foreach($this->roomIds[$roomType] as $roomId) {
			$roomData = $rooms[$roomId];
			if(getNumOfAvailBeds($roomData, $currYear . '-' . $currMonth . '-' . $currDay) == $roomData['num_of_beds']) {
				$availRooms += 1;
			}
		}
		$roomData = $rooms[$this->roomIds[$roomType][0]];
		$prc = getRoomPrice($currYear, $currMonth, $currDay, $roomData);
		return array($availRooms, $prc);
	}

	function displayResult($resultArray) {
		echo "<table>\n";
		echo "	<tr><th></th>";
		foreach($resultArray['SINGLE_ROOM'] as $date => $result) {
			echo "<th>$date</th>";
		}
		echo "</tr>\n";
		foreach($resultArray as $roomType => $resultsForRoomType) {
			echo "	<tr><td><b>$roomType</b></td>";
			foreach($resultsForRoomType as $date => $result) {
				echo "<td>$result</td>";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	}

	function shutdown() {
		echo "<b>hotelservice.hrs.com synchronization shutdown</b><br>";
		echo "Sending logout request...<br>\n";
		$this->httpClient->get($this->getUrl('logout.do'));
		$response = $this->httpClient->currentResponse();
		$this->addResponse('logout', $response['body'], $response['headers']);
		$this->httpClient->reset();
		echo "done.<br>\n";
		echo "<b>hotelservice.hrs.com synchronization shutdown finished</b><br><br><br>";
	}

	function getUrl($action) {
		$retVal = 'https://hotelservice.hrs.com/hsv3_hotelView/' . $action . ';jsessionid=' . $this->jSessionId . '?lang=en_US&portalToken=' . $this->portalToken;
		return $retVal;
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


$booker = new HRSBooker();
$booker->init();
$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();
$responseFiles = $booker->saveResponsesInfoFile('hrs');
echo "Saved responses: <br>\n";
echo "<ul>\n";
foreach($responseFiles as $key => $filename) {
	echo "	<li><a target=\"_blank\" href=\"$filename\">$key</a></li>\n";
}
echo "</ul>\n";

mysql_close($link);

return;




?>
