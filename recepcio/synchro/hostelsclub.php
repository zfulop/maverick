<?php

ini_set('display_errors', 'On');

require('HTTP/Client.php'); 
require('HTTP/Request.php');
require('HTTP/Client/CookieManager.php');


require("../includes.php");
require('../room_booking.php');
require('booker.php');



class HostelsClubBooker extends Booker {

	var $httpClient = null;

	function init() {
		echo "<b>Hostelsclub.com synchronization init</b><br>";

		$this->httpClient =& new Http_Client();

		echo "Sending login request...<br>\n";
		$postData = array();
		$postData['username'] = 'HCU0010987';
		$postData['password'] = 'PAlinka123';
		$this->httpClient->get("https://www.hostelsclub.com/admin/index.php");
		$this->httpClient->post("https://www.hostelsclub.com/admin/login.php", $postData);
		$response = $this->httpClient->currentResponse();
		$this->addResponse('login', $response['body'], $response['headers']);

		$this->httpClient->get("https://www.hostelsclub.com/admin/rooms.php");
		$response = $this->httpClient->currentResponse();
		$this->addResponse('rooms', $response['body'], $response['headers']);
		echo "done.<br>\n";

		echo "<b>Hostelsclub.com synchronization init finished</b><br><br><br>";
	}


	var $idMapPrivateRooms = array(
			'202' => array('39', '40'), /* Mr and Mss Yellow ; Ms Lemon */
			'232' => array('46', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58'), /* Private double rooms */
			'226' => array('59'), /* NEW Maverick ensuites - 3 beds */
			'307' => array('60'), /* NEW Maverick ensuites - 4 beds */
			'554' => array('61') /* NEW Maverick ensuites - 5 beds */
	);

	var $idMapSharedRooms = array(
			'276' => '42',   /* Mr Green */
			'211' => '35',   /* The Blue Brothers */
			'251' => '36'    /* Mss Peach */
	);



	/*
	 */
	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "Hostelsclub.com synchronization update for private rooms ";
		foreach($this->idMapPrivateRooms as $hostelRoomId => $roomIds) {
			$currDate = "$startYear-$startMonth-$startDay";
			$endDate = "$endYear-$endMonth-$endDay";
			$endTS = strtotime($endDate);
			$currTS = strtotime($currDate);
			$roomData = $rooms[$roomIds[0]];
			while($currTS <= $endTS) {
				$currTS = strtotime($currDate);
				$currYear = date('Y', $currTS);
				$currMonth = date('m', $currTS);
				$currDay = date('d', $currTS);

				$prc = getRoomPrice($currYear, $currMonth, $currDay, $roomData);
				$availRooms = 0;
				foreach($roomIds as $roomId) {
					$roomIdata = $rooms[$roomId];
					if(getNumOfAvailBeds($roomData, $currDate) == $roomData['num_of_beds']) {
						$availRooms += 1;
					}

				}
				$postData = array('action' => 'set', 'day' => $currDate, 'formaction' => 'day', 
					'id_type' => $hostelRoomId, 'num' => $availRooms, 'resnum' => '0', 'valore' => $prc);
				echo ".";
				$this->httpClient->post("https://www.hostelsclub.com/admin/calendar_main.php?ajax=1&cat=1&anno=$currYear&mese=$currMonth", $postData);
				$response = $this->httpClient->currentResponse();
				$respData = explode('|', $response['body']);
				$status = substr($respData[0], strlen('ajaxstatus=>'));
				if($status != 'ok') {
					echo "\n<br><b>ERROR: </b> $status <br>\n";
				}


				$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
			} 
		}


		echo "\n<br>Hostelsclub.com synchronization update for shared rooms ";
		foreach($this->idMapSharedRooms as $hostelRoomId => $roomId) {
			$currDate = "$startYear-$startMonth-$startDay";
			$endDate = "$endYear-$endMonth-$endDay";
			$endTS = strtotime($endDate);
			$currTS = strtotime($currDate);
			$roomData = $rooms[$roomId];
			while($currTS <= $endTS) {
				$currTS = strtotime($currDate);
				$currYear = date('Y', $currTS);
				$currMonth = date('m', $currTS);
				$currDay = date('d', $currTS);

				$prc = getBedPrice($currYear, $currMonth, $currDay, $roomData);
				$availBeds = getNumOfAvailBeds($roomData, $currDate);
				$postData = array('action' => 'set', 'day' => $currDate, 'formaction' => 'day', 
					'id_type' => $hostelRoomId, 'num' => $availBeds, 'resnum' => '0', 'valore' => $prc);
				$this->httpClient->post("https://www.hostelsclub.com/admin/calendar_main.php?ajax=1&cat=2&anno=$currYear&mese=$currMonth", $postData);
				$response = $this->httpClient->currentResponse();
				$respData = explode('|', $response['body']);
				$status = substr($respData[0], strlen('ajaxstatus=>'));
				if($status != 'ok') {
					echo "\n<br><b>ERROR: </b> $status <br>\n";
				}
				echo ".";

				$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
			} 

		}

		echo "\n<br>Hostelsclub.com synchronization update finished<br><br><br>";
	}

	function shutdown() {
		echo "<b>Hostelsclub.com synchronization shutdown</b><br>";
		echo "Sending logout request...<br>\n";
		$this->httpClient->get("https://www.hostelsclub.com/admin/logout.php");
		$response = $this->httpClient->currentResponse();
		$this->addResponse('logout', $response['body'], $response['headers']);
		$this->httpClient->reset();
		echo "done.<br>\n";
		echo "<b>Hostelsclub.com synchronization shutdown finished</b><br><br><br>";
	}


	function isPrivateRoom($roomId) {
		$dormRoomIds = array('42', /* Mr Green */
							 '35', /* The Blue Brothers */
							 '36'); /* Mss Peach */

		return !in_array($roomId, $dormRoomIds);
	}


	/*
	 * key is our price, value is the price id of the hostels club.
	 */
	var $priceIds = array(
		'DORM' => array(
			'3' => 1350068,
			'5' => 1359379,
			'9' => 1336933,
			'10' => 1359378,
			'11' => 806102,
			'12' => 1344764,
			'13' => 806103,
			'14' => 806101,
			'15' => 1336934,
			'16' => 806105,
			'17' => 806100,
			'18' => 1336936,
			'19' => 806104,
			'20' => 1336935
		),
		'PRIVATE' => array(
			'10' => 1193113,
			'11' => 1193114,
			'12' => 1167329,
			'13' => 1193116,
			'14' => 1193117,
			'16' => 1193118,
			'18' => 1193119,
			'20' => 1193120,
			'21' => 1067840,
			'22' => 1193121,
			'24' => 1193122,
			'26' => 1193123,
			'27' => 1067846,
			'28' => 1193124,
			'30' => 1067847,
			'32' => 1193126,
			'34' => 1193127,
			'36' => 1193128,
			'38' => 1120823,
			'40' => 1052284,
			'42' => 1067841,
			'44' => 1067842,
			'46' => 1187053,
			'48' => 593544,
			'50' => 593543,
			'51' => 1193186,
			'52' => 1193136,
			'53' => 1193187,
			'54' => 1067848,
			'55' => 1193189,
			'56' => 593545,
			'57' => 1193191,
			'58' => 1193139,
			'59' => 1193192,
			'60' => 1052285,
			'61' => 1193193,
			'62' => 1193141,
			'63' => 1193195,
			'64' => 1193142,
			'65' => 1193196,
			'66' => 1181163,
			'67' => 1193197,
			'68' => 1193145,
			'69' => 1193198,
			'70' => 1193146,
			'71' => 1193199,
			'72' => 1170392,
			'73' => 1193200,
			'74' => 1193148,
			'75' => 1193201,
			'76' => 1193149,
			'77' => 1193202,
			'78' => 1193150,
			'79' => 1193204,
			'80' => 1181168,
			'81' => 1170389,
			'82' => 1193153,
			'83' => 1193209,
			'84' => 1193154,
			'85' => 1193211,
			'86' => 1193155,
			'87' => 1193212,
			'88' => 1170393,
			'89' => 1193213,
			'90' => 1170397,
			'91' => 1193215,
			'92' => 1172902,
			'93' => 1193216,
			'94' => 1193161,
			'95' => 1193218,
			'96' => 1193160,
			'97' => 1193220,
			'98' => 1193162,
			'99' => 1193221,
			'100' => 1170390,
			'101' => 1193224,
			'102' => 1193164,
			'103' => 1193226,
			'104' => 1193165,
			'105' => 1193227,
			'106' => 1193166,
			'107' => 1193228,
			'108' => 1193167,
			'109' => 1193229,
			'110' => 1170394,
			'111' => 1193230,
			'112' => 1193169,
			'113' => 1193231,
			'114' => 1193170,
			'115' => 1172904,
			'116' => 1193171,
			'117' => 1193233,
			'118' => 1193172,
			'119' => 1193234,
			'120' => 1193173,
			'121' => 1193235,
			'122' => 1193174,
			'123' => 1193236,
			'124' => 1193175,
			'125' => 1170391,
			'126' => 1193176,
			'127' => 1193238,
			'128' => 1193177,
			'129' => 1193237
		)
	);


	function getPriceId($year, $month, $day, &$room) {
		$priceId = null;
		if($this->isPrivateRoom($room['id'])) {
			$roomPrice = getRoomPrice($year, $month, $day, &$room);
			if(!isset($this->priceIds['PRIVATE'][$roomPrice])) {
				echo "<b>ERROR:</b> Cannot find room price: $roomPrice (for room: " . $room['id'] . ")<br>\n";
			} else {
				$priceId = $this->priceIds['PRIVATE'][$roomPrice];
			}
		} else {
			$bedPrice = getBedPrice($year, $month, $day, &$room);
			if(!isset($this->priceIds['DORM'][$bedPrice])) {
				echo "ERROR - HostelsClub.com synchronization: Cannot find bed price: $bedPrice (for room: " . $room['id'] . ")<br>\n";
			} else {
				$priceId = $this->priceIds['DORM'][$bedPrice];
			}
		}

		return $priceId;
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


$booker = new HostelsClubBooker();
$booker->init();
$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();
$responseFiles = $booker->saveResponsesInfoFile('hostels_club');
echo "Saved responses: <br>\n";
echo "<ul>\n";
foreach($responseFiles as $key => $filename) {
	echo "	<li><a target=\"_blank\" href=\"$filename\">$key</a></li>\n";
}
echo "</ul>\n";

mysql_close($link);

return;



?>
