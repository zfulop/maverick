<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");
require("common_booking.php");

if(!checkLogin(SITE_ADMIN)) {
	return;
}


$link = db_connect();


$roomTypeId = $_REQUEST['room_type_id'];
$startDate = $_REQUEST['start_date'];
$endDate = $_REQUEST['end_date'];
if(isset($_REQUEST['start_date_booking_rec'])) {
	$startDateBookingRec = $_REQUEST['start_date_booking_rec'];
} else {
	$startDateBookingRec = '';
}
if(isset($_REQUEST['end_date_booking_rec'])) {
	$endDateBookingRec = $_REQUEST['end_date_booking_rec'];
} else {
	$endDateBookingRec = '';
}

$startDateSlash = str_replace('-', '/', $startDate);
$endDateSlash = str_replace('-', '/', $endDate);

list($startYear, $startMonth, $startDay) = explode('-', $startDate);
list($endYear, $endMonth, $endDay) = explode('-', $endDate);

$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

$roomTypes = loadRoomTypesWithAvailableBeds($link, $startDate, $endDate);
$isDorm = ($roomTypes[$roomTypeId]['type'] == 'DORM');
mysql_close($link);



$bookings = getBookings($roomTypeId, $rooms, $startDate, $endDate, $startDateBookingRec, $endDateBookingRec);

$roomTypeCnt = array();
foreach($bookings as $booking) {
	if(!isset($roomTypeCnt[$booking['original_room_type_id']])) {
		$roomTypeCnt[$booking['original_room_type_id']] = 0;
	}
	if($isDorm) {
		$roomTypeCnt[$booking['original_room_type_id']] += $booking['num_of_person'];
	} else {
		$roomTypeCnt[$booking['original_room_type_id']] += 1;
	}
}

if(count($roomTypeCnt) > 1) {
	echo <<<EOT
Summary of bookings per room type: <br>
<table class="bookings" style="width: 400px; margin-bottom: 5px;">
	<tr><th>Room type</th><th>Num of booking</th></tr>

EOT;
	foreach($roomTypeCnt as $rtId => $cnt) {
		echo "<tr><td>" . $roomTypes[$rtId]['name'] . "</td><td>$cnt</td></tr>\n";
	}
	echo "</table>\n";
} else {
	echo "All bookings are for the same original room type.<br>\n";
}

echo <<<EOT
<table class="bookings" style="width: 400px;">
	<tr><th>Name</th><th>First Night</th><th>Last Night</th><th>Number of person</th><th>Room change</th><th>Creation Date</th></tr>

EOT;
	
foreach($bookings as $booking) {
	$cd = substr($booking['creation_time'], 0, 10);
	$roomChange = isset($booking['room_change']) ? 'YES' : '';
	echo "				<tr><td>" . $booking['name'] . "</td><td>" . $booking['first_night'] . "</td><td>" . $booking['last_night'] . "</td><td align=\"center\">" . $booking['num_of_person'] . "</td><td align=\"center\">$roomChange</td><td>$cd</td></tr>\n";
}
echo "</table>\n";


?>
