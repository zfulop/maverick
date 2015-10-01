<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . 'room_booking.php');


$location = getLocation();
$link = db_connect($location);
$lang = getCurrentLanguage();


$startDate = $_SESSION['from_date'];
if(isset($_REQUEST['start'])) {
	$startDate = $_REQUEST['start'];
}
$prevStart = date('Y-m-d', strtotime($startDate . ' -' . $_REQUEST['items'] . ' days'));
$nextStart = date('Y-m-d', strtotime($startDate . ' +' . $_REQUEST['items'] . ' days'));

$roomTypeId = $_REQUEST['room_type_id'];
$items = $_REQUEST['items'];

$startTs = strtotime($startDate);
$endTs = strtotime("$startDate +$items day");
$roomTypesData = loadRoomTypes($link, $lang);
$roomType = $roomTypesData[$roomTypeId];
$rooms = loadRooms(date('Y', $startTs), date('m', $startTs), date('d', $startTs), date('Y', $endTs), date('m', $endTs), date('d', $endTs), $link, $lang);

mysql_close($link);


echo "<div class='roomBedNav roomBedNavLeft noselect' onClick=\"$(this).parent().load('roomCalendar.php?room_type_id=$roomTypeId&items=$items&start=$prevStart');\">&nbsp;</div>";

for($i = 1; $i <= $items; $i++) {
	$currTs = strtotime("$startDate +$i day");
	$dayOfMonth = date('d', $currTs);
	$month = strftime("%b", $currTs);
	$currDate = date('Y-m-d', $currTs);
	$availability = 0;
	foreach($rooms as $roomId => $roomData) {
		if($roomData['room_type_id'] != $roomTypeId) {
			continue;
		}
		$availability += getNumOfAvailBeds($roomData, $currDate);
	}

	$actBed = $availability > 0 ? 'roomCalBedAct' : 'roomCalBedInact';

	$roomCalNumClass = 'roomCalNum';
	if($availability >= 10) {
		$roomCalNumClass = 'roomCalNum2';
	}
    
	echo "<div class=\"roomBed $actBed\">";
	echo "	<div class=\"roomCalDate\">";
	echo "		<div class=\"roomCalDateTop\">$dayOfMonth</div>";
	echo "		<div class=\"roomCalDateBottom\">$month</div>";
	echo "	</div>";
	echo "	<div class=\"$roomCalNumClass\">$availability</div>";
	echo "	<div class=\"clearfix\"></div>";
	echo "</div>";
    
}

echo "<div class=\"roomBedNav roomBedNavRight noselect\" onClick=\"$(this).parent().load('roomCalendar.php?room_type_id=$roomTypeId&items=$items&start=$nextStart');\">&nbsp;</div>";


?>
