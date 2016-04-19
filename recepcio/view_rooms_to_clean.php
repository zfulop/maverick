<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

// Load room data
$sql = "SELECT r.id, r.room_type_id, r.name, rt.name AS rt_name FROM rooms r INNER JOIN room_types rt ON r.room_type_id=rt.id";
$rooms = array();
$roomTypes = array();
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms. Error: " . mysql_error($link) . " (SQL: $sql)");
}
while($row = mysql_fetch_assoc($result)) {
	$rooms[$row['id']] = $row;
	$roomTypes[$row['room_type_id']] = $row['rt_name'];
}

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
	$today = date('Y/m/d', strtotime($dayToShow));
	$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));


	// Get rooms from where gurests are leaving
	$sql = "SELECT 'departure' AS type, b.room_id, brc.new_room_id, bd.checked_in FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id LEFT OUTER JOIN booking_room_changes brc ON (b.id=brc.booking_id AND brc.date_of_room_change='$yesterday') WHERE bd.cancelled=0 AND bd.last_night='$yesterday'";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get departures for date: $today. Error: " . mysql_error($link) . " (SQL: $sql)");
	}
	// echo "There are " . mysql_num_rows($result) . " departures on $today<br>\n";
	while($row = mysql_fetch_assoc($result)) {
		if(!isset($roomsToClean[$today])) {
			$roomsToClean[$today] = array();
		}
		$roomsToClean[$today][] = $row;
	}


	// Get rooms where there was a room_change yesterday and today there is either no room change or a room change to a different room.
	$sql = "SELECT 'room_change' AS type, b.room_id, brcy.new_room_id AS yesterday_new_room_id, brct.new_room_id AS today_new_room_id FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id LEFT OUTER JOIN booking_room_changes brcy ON (b.id=brcy.booking_id AND brcy.date_of_room_change='$yesterday') LEFT OUTER JOIN booking_room_changes brct ON (b.id=brct.booking_id AND brct.date_of_room_change='$today') WHERE bd.first_night<='$yesterday' AND bd.last_night>='$today' AND brcy.new_room_id<>brct.new_room_id";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get departures for date: $today. Error: " . mysql_error($link) . " (SQL: $sql)");
	}
	// echo "There are " . mysql_num_rows($result) . " room changes on $today<br>\n";
	while($row = mysql_fetch_assoc($result)) {
		if(!isset($roomsToClean[$today])) {
			$roomsToClean[$today] = array();
		}
		if($row['room_id'] != $row['yesterday_new_room_id'] or $row['room_id'] != $row['today_new_room_id']) {
			$roomsToClean[$today][] = $row;
		}
	}

	$dayToShow = date('Y-m-d', strtotime($dayToShow . " +1 day"));
}

//echo "<pre>\n";
//print_r($roomsToClean);
//echo "</pre>\n";

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

foreach($roomTypes as $rtid => $rtname) {
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
				$beds[$room['name']] = 1;
			} else {
				$beds[$room['name']] += 1;
			}
		}
		echo "		<td>\n";
		foreach($roomNames as $rName) {
			echo $rName;
			if($beds[$rName] > 1) {
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



?>