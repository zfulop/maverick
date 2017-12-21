<?php


$location = $argv[1];
$startDate = $argv[2];
$endDate = $argv[3];

$configFile = '../../includes/config/' . $location . '.php';
if(!file_exists($configFile)) {
	echo "invalid location parameter";
	exit;
}
require($configFile);
require('../includes.php');

echo "Start: $startDate, End: $endDate\n";

$dates = array();
echo "Dates: \n";
for($currDate = $startDate; $currDate <= $endDate; $currDate = date('Y-m-d', strtotime("$currDate +1 day"))) {
	echo "\t$currDate\n";
	$dates[] = $currDate;
}

$link = db_connect($location);

$startDateDash = str_replace('-', '/', $startDate);
$endDateDash = str_replace('-', '/', $endDate);

$sql = "SELECT bd.first_night, bd.last_night, bd.cancelled, r.id AS room_id, r.name AS room_name, rt.id AS room_type_id, rt.name AS room_type_name, bd.name AS booking_name, bd.id AS booking_description_id, b.booking_type, b.num_of_person, bd.confirmed, bd.checked_in, bd.paid, b.id AS booking_id, 1 as is_room_change, brc.date_of_room_change FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id INNER JOIN booking_room_changes brc ON b.id=brc.booking_id INNER JOIN rooms r ON brc.new_room_id=r.id INNER JOIN room_types rt ON r.room_type_id=rt.id WHERE bd.last_night>='$startDateDash' AND bd.first_night<='$endDateDash' AND brc.date_of_room_change>='$startDateDash' AND brc.date_of_room_change<='$endDateDash'";
$result = mysql_query($sql, $link);
if(!$result) {
	echo "Cannot get room changes. Error: " . mysql_error($link) . " (SQL: $sql)\n";
	mysql_close($link);
	exit;
}
$roomAvail = array();
$bookingForDate = array();
echo "There are " . mysql_num_rows($result) . " room changes\n";
while($row = mysql_fetch_assoc($result)) {
	if($row['cancelled'] == 1) {
		continue;
	}
	$date = str_replace('/','-',$row['date_of_room_change']);
	$rId = $row['room_id'];
	$bId = $row['booking_id'];
	if(!isset($avail[$date])) {
		$avail[$date] = array();
	}
	if(!isset($avail[$date][$rId])) {
		$avail[$date][$rId] = array();
	}
	$avail[$date][$rId][] = $row;
	$bookingForDate[$date][$bId] = 'X';
}

$sql = "SELECT bd.first_night, bd.last_night, bd.cancelled, r.id AS room_id, r.name AS room_name, rt.id AS room_type_id, rt.name AS room_type_name, bd.name AS booking_name, bd.id AS booking_description_id, b.booking_type, b.num_of_person, bd.confirmed, bd.checked_in, bd.paid, b.id AS booking_id, 0 as is_room_change FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id INNER JOIN rooms r ON b.room_id=r.id INNER JOIN room_types rt ON r.room_type_id=rt.id WHERE bd.last_night>='$startDateDash' AND bd.first_night<='$endDateDash'";
$result = mysql_query($sql, $link);
if(!$result) {
	echo "Cannot get room changes. Error: " . mysql_error($link) . " (SQL: $sql)\n";
	mysql_close($link);
	exit;
}
echo "There are " . mysql_num_rows($result) . " bookings\n";
while($row = mysql_fetch_assoc($result)) {
	if($row['cancelled'] == 1) {
		continue;
	}
	$rId = $row['room_id'];
	$bId = $row['booking_id'];
	foreach($dates as $date) {
		$dateDash = str_replace('-','/', $date);
		if(!isset($avail[$date])) {
			$avail[$date] = array();
		}
		if(!isset($avail[$date][$rId])) {
			$avail[$date][$rId] = array();
		}
		if($row['first_night'] > $dateDash or $row['last_night'] < $dateDash) {
			continue;
		}
		if(!isset($bookingForDate[$date][$bId])) { // check if there is a room change for the date for this booking
			$avail[$date][$rId][] = $row;
		}
	}
}

mysql_close($link);

echo "Saving availability\n";
foreach($avail as $date => $availForDate) {
	echo "\t$date\n";
	$file = JSON_DIR . $location . '/avail_' . $date . '.json';
	$data = json_encode($availForDate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	file_put_contents($file, $data);
}


?>
