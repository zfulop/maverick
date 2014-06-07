<?php

require("includes.php");
require("../recepcio/room_booking.php");

$link = db_connect();


$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

EOT;

$startDate = date('Y/m') . '/01';
$endDate = date('Y/m/t');

$numOfBedsForRooms = array();
$allBeds = 0;
/*
foreach($ROOM_IDS_FOR_ROOM_CODE as $roomCode => $roomIds) {
	foreach($roomIds as $roomId) {
		$numOfBedsForRooms[$roomId] = $NUM_OF_BEDS_PER_ROOM_CODE[$roomCode];
		$allBeds += $NUM_OF_BEDS_PER_ROOM_CODE[$roomCode];
	}
}
 */

if(isset($_REQUEST['start_date'])) {
	$startDate = $_REQUEST['start_date'];
}

if(isset($_REQUEST['end_date'])) {
	$endDate = $_REQUEST['end_date'];
}


$numOfDays = intval((strtotime(str_replace('/', '-', $endDate)) - strtotime(str_replace('/', '-', $startDate))) / (60*60*24)) + 1;


html_start("Maverick Admin - Statistics", $extraHeader);


echo <<<EOT

<form action="view_statistics.php" method="GET">
<table>
	<tr><td>Start date:</td><td>
		<input id="start_date" name="start_date" size="10" maxlength="10" type="text" value="$startDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2008, 2025, 'Y/m/d', false);">
		<div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>End date:</td><td>
		<input id="end_date" name="end_date" size="10" maxlength="10" type="text" value="$endDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date', 'chooserSpanED', 2008, 2025, 'Y/m/d', false);">
		<div id="chooserSpanED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
</table>
<input type="submit" value="Generate Statistics">
</form>

EOT;

$sql = "SELECT booking_descriptions.*, bookings.num_of_person, bookings.room_id, bookings.room_payment FROM booking_descriptions INNER JOIN bookings ON booking_descriptions.id=bookings.description_id WHERE booking_descriptions.first_night<='$endDate' AND booking_descriptions.last_night>='$startDate' AND booking_descriptions.cancelled=0 AND booking_descriptions.maintenance<>1";
$bookings = array();
$result = mysql_query($sql, $link);
if(!$result) {
	echo "SQL ERROR: " . mysql_error($link) . "<br>\n";
}
$source = array();
$nationality = array();
$rooms = array();
while($row = mysql_fetch_assoc($result)) {
	$bookings[] = $row;
	$numOfNights = $row['num_of_nights'];
	if($row['first_night'] < $startDate) {
		$numOfNights -= intval((strtotime(str_replace('/', '-', $startDate)) - strtotime(str_replace('/', '-', $row['first_night']))) / (60*60*24));
	}
	if($row['last_night'] > $endDate) {
		$numOfNights -= intval((strtotime(str_replace('/', '-', $row['last_night'])) - strtotime(str_replace('/', '-', $endDate))) / (60*60*24));
	}
	//echo $row['first_night'] . ' - ' . $row['last_night'] . "(" . $row['num_of_nights'] . ") [$numOfNights] <br>\n";
	if(!isset($source[$row['source']])) {
		$source[$row['source']] = array();
		$source[$row['source']]['beds'] = 0;
		$source[$row['source']]['bookings'] = 0;
		$source[$row['source']]['payment'] = 0;
	}
	$source[$row['source']]['beds'] += $row['num_of_person'] * $numOfNights;
	$source[$row['source']]['bookings'] += 1;
	$source[$row['source']]['payment'] += $row['room_payment'];
	if(!isset($nationality[$row['nationality']])) {
		$nationality[$row['nationality']] = array();
		$nationality[$row['nationality']]['beds'] = 0;
		$nationality[$row['nationality']]['bookings'] = 0;
		$nationality[$row['nationality']]['payment'] = 0;
	}
	$nationality[$row['nationality']]['beds'] += $row['num_of_person'] * $numOfNights;
	$nationality[$row['nationality']]['bookings'] += 1;
	$nationality[$row['nationality']]['payment'] += $row['room_payment'];
}


list($syear, $smonth, $sday) = explode('/', $startDate);
list($eyear, $emonth, $eday) = explode('/', $endDate);
$rooms = loadRooms($syear, $smonth, $sday, $eyear, $emonth, $eday, $link);

//echo "\n\n<!-- roomdata: \n";
//print_r($rooms);
//echo "-->\n\n";


mysql_close($link);


list($source_bedTitle, $source_bookingTitle, $source_paymentTitle, $source_bedData, $source_bookingData, $source_paymentData) = generatePieChartData($source);

list($nationality_bedTitle, $nationality_bookingTitle, $nationality_paymentTitle, $nationality_bedData, $nationality_bookingData, $nationality_paymentData) = generatePieChartData($nationality);


$roomNameLabels = '';
$roomValueLabels = '';
$roomData = '';
$bedsUsed = 0;

$maxAvailBeds = 0;
foreach($rooms as $roomId => $room) {
	$maxAvailBeds += $room['num_of_beds'];
	$currDate = "$syear-$smonth-$sday";
	$occup = 0;
	echo " <!-- For room: " . $room['name'] . "\n";
	do {
		$currTS = strtotime($currDate);
		$style="";
		$occup += getNumOfOccupBeds($room, $currDate);
		echo "		for day: $currDate num of occup beds: " . getNumOfOccupBeds($room, $currDate) . "\n";
		$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
	} while($currDate <= str_replace('/', '-', $endDate));

	$bedsUsed += $occup;
	$maxBeds = $room['num_of_beds'] * $numOfDays;
	$usage = ($occup / $maxBeds) * 100;
	$roomNameLabels .= '|' . urlencode($room['name']);
	$roomValueLabels .= '|' . intval($usage) . '%';
	$roomData = intval($usage) . ',' . $roomData;

	echo " Max beds: $maxBeds for $numOfDays days. Total number of occupied beds: $occup -->\n";
}

//echo "Beds used: $bedsUsed. Max beds: " . $maxAvailBeds . " for $numOfDays days: " . ($maxAvailBeds*$numOfDays) . "<br>\n";
$hostelUsedPercentage = intval($bedsUsed / ($maxAvailBeds * $numOfDays) * 100);
$hostelAvailPercentage = 100 - $hostelUsedPercentage;
$hostelUsageData = chr(intval($hostelUsedPercentage / 4) + 65) . chr(intval($hostelAvailPercentage / 4) + 65);
$hostelUsedPercentage = $hostelUsedPercentage . '%';
$hostelAvailPercentage = $hostelAvailPercentage . '%';

$roomData = substr($roomData, 0, -1);


echo <<<EOT

<br><br>


<table>
	<tr><th colspan="3">Sources</th></tr>
	<tr>
		<td>
			<img src="http://chart.apis.google.com/chart?chtt=Sources+by+beds&chs=300x225&cht=p3&chco=FF9900|0ED60E|DE0707|3366CC|AA0033|7777CC|FFFF88|C2BDDD&chd=s:$source_bedData&chdl=$source_bedTitle">
		</td>
		<td>
			<img src="http://chart.apis.google.com/chart?chtt=Sources+by+number+of+bookings&chs=300x225&cht=p3&chco=FF9900|0ED60E|DE0707|3366CC|AA0033|7777CC|FFFF88|C2BDDD&chd=s:$source_bookingData&chdl=$source_bookingTitle">
		</td>
		<td>
			<img src="http://chart.apis.google.com/chart?chtt=Sources+by+room+payment&chs=300x225&cht=p3&chco=FF9900|0ED60E|DE0707|3366CC|AA0033|7777CC|FFFF88|C2BDDD&chd=s:$source_paymentData&chdl=$source_paymentTitle">
		</td>
	</tr>
</table>

<br>
<br>

<table>
	<tr><th colspan="3">Nationalities</th></tr>
	<tr>
		<td>
			<img src="http://chart.apis.google.com/chart?chtt=Nationalities+by+beds&chs=300x225&cht=p3&chco=FF9900|0ED60E|DE0707|3366CC|AA0033|7777CC|FFFF88|C2BDDD&chd=s:$nationality_bedData&chdl=$nationality_bedTitle">
		</td>
		<td>
			<img src="http://chart.apis.google.com/chart?chtt=Nationalities+by+number+of+bookings&chs=300x225&cht=p3&chco=FF9900|0ED60E|DE0707|3366CC|AA0033|7777CC|FFFF88|C2BDDD&chd=s:$nationality_bookingData&chdl=$nationality_bookingTitle">
		</td>
		<td>
			<img src="http://chart.apis.google.com/chart?chtt=Nationalities+by+room+payment&chs=300x225&cht=p3&chco=FF9900|0ED60E|DE0707|3366CC|AA0033|7777CC|FFFF88|C2BDDD&chd=s:$nationality_paymentData&chdl=$nationality_paymentTitle">
		</td>
	</tr>
</table>

<br>
<br>

<table>
	<tr><th colspan="2">Rooms</th></tr>
	<tr>
		<td>
			<img src="http://chart.apis.google.com/chart?chtt=Room+usage&cht=bhs&chxt=x,y,r&chxr=0,0,100&chs=300x640&chxl=1:$roomNameLabels|2:$roomValueLabels&chd=t:$roomData">
		</td>
		<td valign="top">
			<img src="http://chart.apis.google.com/chart?chtt=Hostel+usage&chs=450x205&cht=p3&chco=CA0F0F|3072F3&chd=s:$hostelUsageData&chdl=Booked|Availabled&chp=2.5&chl=$hostelUsedPercentage|$hostelAvailPercentage">
		</td>
	</tr>
</table>


EOT;


html_end();



function generatePieChartData($data) {
	$bedData = '';
	$bookingData = '';
	$paymentData = '';
	$bedTitle = '';
	$bookingTitle = '';
	$paymentTitle = '';
	$maxBed = 0;
	$maxBooking = 0;
	$maxPayment = 0;
	foreach($data as $title => $sourceData) {
		$maxBed = max($maxBed, $sourceData['beds']);
		$maxBooking = max($maxBooking, $sourceData['bookings']);
		$maxPayment = max($maxPayment, $sourceData['payment']);
	}
	$adjustBed = $maxBed / 50;
	$adjustBookings = $maxBooking / 50;
	$adjustPayment = $maxPayment / 50;
	foreach($data as $title => $sourceData) {
		$bedTitle .= $title . ' (' . $sourceData['beds'] . ')|';
		$bookingTitle .= $title . ' (' . $sourceData['bookings'] . ')|';
		$paymentTitle .= $title . ' (' . $sourceData['payment'] . ' euro)|';
		$bedData .= chr(intval($sourceData['beds'] / $adjustBed)  + 65);
		$bookingData .= chr(intval($sourceData['bookings'] / $adjustBookings) + 65);
		$paymentData .= chr(intval($sourceData['payment'] / $adjustPayment)+ 65);
	}
	$bedTitle = urlencode(substr($bedTitle, 0, -1));
	$bookingTitle = urlencode(substr($bookingTitle, 0, -1));
	$paymentTitle = urlencode(substr($paymentTitle, 0, -1));
	return array($bedTitle, $bookingTitle, $paymentTitle, $bedData, $bookingData, $paymentData);
}


?>
