<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

// Load room data
$rooms = RoomDao::getRooms($link);
$roomTypes = RoomDao::getRoomTypes('eng', $link);

$roomsToClean = array();
if(isset($_REQUEST['start_date'])) {
	$startDate = $_REQUEST['start_date'];
} else {
	$startDate = date('Y-m-d');
}
$numOfDays = 5;
if(isset($_REQUEST['num_of_days'])) {
	$numOfDays = intval($_REQUEST['num_of_days']);
}

$dayToShow = $startDate;
for($i = 0; $i < $numOfDays; $i++) {
	// Get rooms from where guests are leaving
	$leaves = BookingDao::getLeavingBookings($dayToShow, $link);
	array_walk($leaves, 'applyType', 'departure');

	// Get rooms where there was a room_change yesterday and today there is either no room change or a room change to a different room.
	$roomChanges = BookingDao::getRoomChangeBookings($dayToShow, $link);
	array_walk($roomChanges, 'applyType', 'room_change');

	$roomsToClean[$dayToShow] = array_merge($leaves, $roomChanges);

	$dayToShow = date('Y-m-d', strtotime($dayToShow . " +1 day"));
}


$numOfDaysOptions = '';
for($i = 1; $i < 7; $i++) {
	$numOfDaysOptions .= "		<option value=\"$i\"" . ($numOfDays == $i ? ' selected' : '') . ">$i days</option>\n";
}

$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->


EOT;


html_start("Rooms to clean", $extraHeader);

echo <<<EOT

<form action="view_rooms_to_clean.php">
<table style="border: 1px solid black;">
	<tr><th colspan="2">Select date</th></tr>
	<tr><td>Start date</td><td>
		<input id="start_date" value="$startDate" name="start_date" size="10" maxlength="10" type="text"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>	
	</td></tr>
	<tr><td>Number of days</td><td><select name="num_of_days">
$numOfDaysOptions
	</select></td></tr>
	<tr><th colspan="2"><input type="submit" value="Set date"></td></tr>
</table>
</form>


<table>
	<tr><th></th>
EOT;

foreach(array_keys($roomsToClean) as $date) {
	echo "<th>$date</th>";
}
echo "</tr>\n";

//echo "rooms to clean: " . print_r($roomsToClean, true);

foreach($roomTypes as $rtid => $roomType) {
	$rtname = $roomType['name'];
	echo "	<tr><th>$rtname</th>\n";
	foreach($roomsToClean as $date => $roomsToCleanForDate) {
		$roomNames = array();
		$beds = array();
		foreach($roomsToCleanForDate as $oneRoomToClean) {
			$roomId = null;
			if($oneRoomToClean['type'] == 'departure') {
				$roomId = (is_null($oneRoomToClean['new_room_id']) ? $oneRoomToClean['room_id'] : $oneRoomToClean['new_room_id']);
			} else {
				$roomId = (is_null($oneRoomToClean['yesterday_new_room_id']) ? $oneRoomToClean['room_id'] : $oneRoomToClean['yesterday_new_room_id']);
			}
			$room = $rooms[$roomId];
			if($room['room_type_id'] <> $rtid) {
				continue;
			}
			if(!in_array($room['name'], $roomNames)) {
				$roomNames[] = $room['name'];
				$beds[$room['name']] = $oneRoomToClean['num_of_person'] + $oneRoomToClean['extra_beds'];
			} else {
				$beds[$room['name']] += $oneRoomToClean['num_of_person'] + $oneRoomToClean['extra_beds'];
			}
		}
		echo "		<td>\n";
		foreach($roomNames as $rName) {
			echo $rName;
			if(isset($beds[$rName])) {
				echo "(" . $beds[$rName] . ")";
			}
			echo " ";
		}
		echo "		</td>\n";
	}
	echo "	</tr>\n";
}


echo <<<EOT
</table>

EOT;


html_end();


function applyType(&$element, $key, $type) {
	$element['type'] = $type;
}

?>