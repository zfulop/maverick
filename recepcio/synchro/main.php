<?php


require('../includes.php');
require('../room_booking.php');


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



if(file_exists('captcha/hrs.png')) {
	unlink('captcha/hrs.png');
}
if(file_exists('captcha/hostelworld.gif')) {
	unlink('captcha/hostelworld.gif');
}

if(file_exists('captcha/hostelworld.txt')) {
	unlink('captcha/hostelworld.txt');
}
if(file_exists('captcha/hrs.txt')) {
	unlink('captcha/hrs.txt');
}

$loadHrs = '';
$loadMyAllocator = '';

$startDate = $_REQUEST['start_date'];
$endDate = $_REQUEST['end_date'];
$hostel = getLoginHotel();
logDebug("Start: $startDate, End: $endDate");

logDebug("Exporting availability for the period into file");
$cmd = "php -c ../../php.ini extract_availability.php $hostel $startDate $endDate";
logDebug("cmd: $cmd");
$output = shell_exec("cd /home/maveric3/reception/synchro; $cmd");
logDebug("availability exported. Output: $output");

$paramsArray = array();
$datesArray = array();
$currDate = $startDate;
$currEndDate = min($endDate, date('Y-m-d', strtotime($startDate . ' +1 month')));
$cntr = 0;
while($currDate <= $endDate) {
	logDebug("currDate: $currDate");
	logDebug("currEndDate: $currEndDate");
	list($currStartYear, $currStartMonth, $currStartDay) = explode('-', $currDate);
	list($currEndYear, $currEndMonth, $currEndDay) = explode('-', $currEndDate);
	if(strlen($currStartDay) == 1)
		$currStartDay = '0' . $currStartDay;
	if(strlen($currStartMonth) == 1)
		$currStartMonth = '0' . $currStartMonth;
	if(strlen($currEndDay) == 1)
		$currEndDay = '0' . $currEndDay;
	if(strlen($currEndMonth) == 1)
		$currEndMonth = '0' . $currEndMonth;
	$params = "start_year=$currStartYear&start_month=$currStartMonth&start_day=$currStartDay&end_year=$currEndYear&end_month=$currEndMonth&end_day=$currEndDay";
	$paramsArray[] = $params;
	$datesArray[] = "$currDate - $currEndDate";
	$loadHrs .= in_array('hrs', $_REQUEST['sites']) ? "loadFrame('hrs$cntr', 'hrs.php?$params');\n" : '';
	$loadMyAllocator .= in_array('myallocator', $_REQUEST['sites']) ? "loadFrame('myallocator$cntr', 'myallocator.php?$params');\n" : '';
	$currDate = date('Y-m-d', strtotime($currEndDate . ' +1 day'));
	$currEndDate = min(max($currDate, $endDate), date('Y-m-d', strtotime($currDate . ' +1 month')));
	$cntr += 1;
}
$diff = strtotime($endDate) - strtotime($startDate);

list($startYear, $startMonth, $startDay) = explode('-', $startDate);
list($endYear, $endMonth, $endDay) = explode('-', $endDate);
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

/*
$loadHW = in_array('hw', $_REQUEST['sites']) ? 'true' : 'false';
$loadMyallocator = in_array('myallocator', $_REQUEST['sites']) ? 'true' : 'false';
$loadHB = in_array('hb', $_REQUEST['sites']) ? 'true' : 'false';
$loadHC = in_array('hc', $_REQUEST['sites']) ? 'true' : 'false';
$loadExpedia = in_array('expedia', $_REQUEST['sites']) ? 'true' : 'false';
$loadBookings = in_array('bookings', $_REQUEST['sites']) ? 'true' : 'false';
$loadLaterooms = in_array('laterooms', $_REQUEST['sites']) ? 'true' : 'false';
$loadAgoda = in_array('agoda', $_REQUEST['sites']) ? 'true' : 'false';
*/

$reloadParent = '';
if((strpos($_SERVER['HTTP_REFERER'], 'view_rooms') > 0) or (strpos($_SERVER['HTTP_REFERER'], 'view_pricing') > 0)) {
	$reloadParent = 'window.opener.location.reload(false);';
}

$extraHeader = <<<EOT

<script type="text/javascript">
	function init() {
 		$loadMyAllocator
		$loadHrs
		$reloadParent
	}

	function loadFrame(frameId, src) {
		document.getElementById(frameId).src=src;
	}
</script>

EOT;

$onloadScript = 'init();';

html_start("Maverick Recepcio - Room availability synchronization", $extraHeader, true, $onloadScript);

echo "<h1>Synchronization from $startYear-$startMonth-$startDay to $endYear-$endMonth-$endDay</h1>\n";
;

/*
echo "<h2>HostelWorld.com</h2>\n";
echo "<a href=\"#\" onclick=\"window.open('view_captcha.php?hostelworld=on', '_blank', 'location=0, toolbar=0, status=0, width=300, height=200');document.getElementById('hostelworld').src='hostelworld.php?$params';return false; \">Reload</a><br>\n";
echo "<iframe id=\"hostelworld\" width=\"90%\" height=\"200\" style=\"margin: 10px; background: rgb(200, 200, 200); border: 1px dotted black;\">\n";
echo "</iframe>\n";
 */

if(in_array('hrs', $_REQUEST['sites'])) {
	for($i = 0; $i < count($paramsArray); $i++) {
		$params = $paramsArray[$i];
		$dates = $datesArray[$i];
		echo "<h2>hotelservice.hrs.com $dates</h2>\n";
		echo "<a href=\"#\" onclick=\"document.getElementById('hrs$i').src='hrs.php?$params';return false; \">Reload</a><br>\n";
		echo "<iframe id=\"hrs$i\" width=\"90%\" height=\"200\" style=\"margin: 10px; background: rgb(200, 200, 200); border: 1px dotted black;\">\n";
		echo "</iframe>\n";
	}
}

if(in_array('myallocator', $_REQUEST['sites'])) {
	for($i = 0; $i < count($paramsArray); $i++) {
		$params = $paramsArray[$i];
		$dates = $datesArray[$i];
		echo "<h2>MyAllocator.com $dates</h2>\n";
		echo "<a href=\"#\" onclick=\"document.getElementById('myallocator$i').src='myallocator.php?$params'; return false; \">Reload</a><br>\n";
		echo "<iframe id=\"myallocator$i\" width=\"90%\" height=\"200\" style=\"margin: 10px; background: rgb(200, 200, 200); border: 1px dotted black;\">\n";
		echo "</iframe>\n";
	}
}

/*
echo "<h2>HostelBookers.com</h2>\n";
echo "<a href=\"#\" onclick=\"document.getElementById('hostelbookers').src='hostelbookers.php?$params'; return false; \">Reload</a><br>\n";
echo "<iframe id=\"hostelbookers\" width=\"90%\" height=\"200\" style=\"margin: 10px; background: rgb(200, 200, 200); border: 1px dotted black;\">\n";
echo "</iframe>\n";

echo "<h2>HostelsClub.com</h2>\n";
echo "<a href=\"#\" onclick=\"document.getElementById('hostelsclub').src='hostelsclub.php?$params'; return false;\">Reload</a><br>\n";
echo "<iframe id=\"hostelsclub\" width=\"90%\" height=\"200\" style=\"margin: 10px; background: rgb(200, 200, 200); border: 1px dotted black;\">\n";
echo "</iframe>\n";

echo "<h2>Expedia.com</h2>\n";
echo "<a href=\"#\" onclick=\"document.getElementById('expedia').src='expedia.php?$params'; return false;\">Reload</a><br>\n";
echo "<iframe id=\"expedia\" width=\"90%\" height=\"200\" style=\"margin: 10px; background: rgb(200, 200, 200); border: 1px dotted black;\">\n";
echo "</iframe>\n";

echo "<h2>Bookings.org</h2>\n";
echo "<a href=\"#\" onclick=\"document.getElementById('bookings').src = 'bookings.php?$params'; return false;\">Reload</a><br>\n";
echo "<iframe id=\"bookings\" width=\"90%\" height=\"200\" style=\"margin: 10px; background: rgb(200, 200, 200); border: 1px dotted black;\">\n";
echo "</iframe>\n";

echo "<h2>Laterooms.com</h2>\n";
echo "<a href=\"#\" onclick=\"document.getElementById('laterooms').src='laterooms.php?$params'; return false;\">Reload</a><br>\n";
echo "<iframe id=\"laterooms\" width=\"90%\" height=\"200\" style=\"margin: 10px; background: rgb(200, 200, 200); border: 1px dotted black;\">\n";
echo "</iframe>\n";

echo "<h2>Agoda.com</h2>\n";
echo "<a href=\"#\" onclick=\"document.getElementById('agoda').src='agoda.php?$params'; return false;\">Reload</a><br>\n";
echo "<iframe id=\"agoda\" width=\"90%\" height=\"200\" style=\"margin: 10px; background: rgb(200, 200, 200); border: 1px dotted black;\">\n";
echo "</iframe>\n";
 */






$startDate = "$startYear-$startMonth-$startDay";
$endDate = "$endYear-$endMonth-$endDay";
$dates = array();
for($currDate = $startDate; $currDate <= $endDate; $currDate = date('Y-m-d', strtotime("$currDate +1 day"))) {
	$dates[] = $currDate;
}


echo <<<EOT
	<table class="tableWithFloatingHeader" border="1" style="font-size: 11px;">
		<tr>
			<th></th>

EOT;
foreach($dates as $currDate) {
	echo "		<th>$currDate</th>\n";
}
echo "	</tr>\n";
foreach($rooms as $room) {
	$roomName = $room['name'];
	echo <<<EOT
	<tr>
		<td style="width: 100px; background: rgb(49, 236, 243);">
			<div class="divFloatingHeader" style="position:relative">
				<div class="tableFloatingHeader" style="width: 150px; padding: 10px; background: rgb(49, 236, 243); border: 1px solid rgb(0,0,0); position: absolute; top: 0px; left: 0px; visibility: hidden;">$roomName</div>
			</div>
			$roomName
		</td>

EOT;
	foreach($dates as $currDate) {
		$style="";
		$avail = getNumOfAvailBeds($room, $currDate);
		if($avail < 1) {
			$style = 'background: red;';
		} elseif($avail < $room['num_of_beds']) {
			$style = 'background: yellow;';
		}
		echo "		<td align=\"center\" style=\"$style\">\n";
		echo "			$avail" . " / " . $room['num_of_beds'] . "<br>\n";
		foreach(getBookerNamesForDay($room, $currDate) as $oneBookerName) {
			echo "			$oneBookerName\n";
		}
		echo "		</td>\n";
	}
	echo "	</tr>\n";
}
echo "	<tr>\n";
echo "		<th></th>\n";
foreach($dates as $currDate) {
	echo "		<th>" . substr($currDate, 8, 2) . "</th>\n";
}
echo "	</tr>\n";

echo "</table>\n";


mysql_close($link);
html_end();



function getBookerNamesForDay(&$oneRoom, $oneDay) {
	$names = array();
	$oneDay = str_replace('-', '/', $oneDay);
	foreach($oneRoom['bookings'] as $oneBooking) {
		if($oneBooking['cancelled'] == 1) {
			continue;
		}

		if(isset($oneBooking['changes'])) {
			$isThereRoomChangeForDay = false;
			foreach($oneBooking['changes'] as $oneChange) {	
				if($oneChange['date_of_room_change'] == $oneDay) {
					$isThereRoomChangeForDay = true;
				}
			}
			if($isThereRoomChangeForDay)
				continue;
		}

		if(($oneBooking['first_night'] <= $oneDay) and ($oneBooking['last_night'] >= $oneDay)) {
			$count = 0;
			if($oneBooking['booking_type'] == 'BED') {
				$count = $oneBooking['num_of_person'];
			} else {
				$count = $oneRoom['num_of_beds'];
			}
			for($i = 0; $i < $count; $i++) {
				$style = '';
				if($oneBooking['confirmed'] == 1) {
					$style .= 'font-weight: bold;';
				}
				if($oneBooking['checked_in'] == 1) {
					$style .= 'background: rgb(0, 255, 0);';
				}
				if($oneBooking['paid'] == 1) {
					$style .= 'border: 2px solid rgb(0, 0, 255);';
				}
				$names[] = "<span style=\"margin: 3px;$style\">" . str_replace(" ", "&nbsp;", $oneBooking['name']) . "</span><br>";
			}
		}
	}

	foreach($oneRoom['room_changes'] as $oneRoomChange) {
		if($oneRoomChange['cancelled'] == 1) {
			continue;
		}

		if($oneRoomChange['date_of_room_change'] == $oneDay) {
			if($oneRoomChange['booking_type'] == 'BED')
				$count = $oneRoomChange['num_of_person'];
			else {
				$count = $oneRoom['num_of_beds'];
			}
			for($i = 0; $i < $count; $i++) {
				$style = '';
				if($oneRoomChange['confirmed'] == 1) {
					$style .= 'font-weight: bold;';
				}
				if($oneRoomChange['checked_in'] == 1) {
					$style .= 'background: rgb(0, 255, 0);';
				}
				if($oneRoomChange['paid'] == 1) {
					$style .= 'border: 2px solid rgb(0, 0, 255);';
				}
				$names[] = "<span style=\"margin: 3px;$style\">" . str_replace(" ", "&nbsp;", $oneRoomChange['name']) . "&nbsp;(RC)</span><br>";
			}

		}
	}

	return $names;
}



?>
