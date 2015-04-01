<?php

require("includes.php");
require("room_booking.php");

set_error_handler('ajaxErrorHandler');

header("Content-type: application/json");

$link = db_connect();

$roomPayment = 0;


$arr = split("/", $_REQUEST['first_night']);
if(count($arr) != 3) {
	echo "{\"roomPayment\":\"0\",\"message\": \"first night date not set.\"}";
	mysql_close($link);
	return;
}
list($startYear, $startMonth, $startDay) = $arr;
$arr = split("/", $_REQUEST['last_night']);
if(count($arr) != 3) {
	echo "{\"roomPayment\":\"0\",\"message\": \"last night date not set.\"}";
	mysql_close($link);
	return;
}
list($endYear, $endMonth, $endDay) = $arr;
$startDate = $startYear . '-' . $startMonth . '-' . $startDay;
$endDate = $endYear . '-' . $endMonth . '-' . $endDay;
$nights = round((strtotime($endDate) - strtotime($startDate)) / (60*60*24)) + 1;


$roomTypes = array();
$sql = "SELECT rt.*, count(*) as num_of_rooms FROM room_types rt inner join rooms r on (rt.id=r.room_type_id) group by rt.id";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	$roomTypes[$row['id']] = $row;
}

$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

if(!$rooms) {
	$message = json_encode($ajaxErrors);
	echo "{\"roomPayment\":\"0\",\"message\": $message}";
}

$info = "";
foreach($roomTypes as $roomTypeId => $roomType) {
	$numOfPerson = $_REQUEST['num_of_person_' . $roomTypeId];
	$oneRoomId = null;
	foreach($rooms as $roomId => $roomData) {
		if($roomData['room_type_id'] == $roomTypeId) {
			$oneRoomId = $roomId;
			break;
		}
	}
	if(isset($_REQUEST['num_of_person_' . $roomTypeId]) and $_REQUEST['num_of_person_' . $roomTypeId] > 0) {
		$info .= "for room: " . $roomType['name'] . " there are $numOfPerson people. ";
		$price = getPrice(strtotime($startDate), $nights, $rooms[$oneRoomId], $numOfPerson);
		$roomPayment += $price;
	}
}


mysql_close($link);
echo "{\"roomPayment\":\"$roomPayment\",\"message\": \"$info\"}";
return;

?>
