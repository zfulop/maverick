<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}


$link = db_connect();

// Load room data
$sql = "SELECT r.id, r.room_type_id, r.name, rt.name AS rt_name FROM rooms r INNER JOIN room_types rt ON r.room_type_id=rt.id";
$rooms = array();
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms. Error: " . mysql_error($link) . " (SQL: $sql)");
}
while($row = mysql_fetch_assoc($result)) {
	$rooms[$row['id']] = $row;
}

$roomsToClean = array();
$dayToShow = date('Y-m-d');
$today = date('Y/m/d', strtotime($dayToShow));
$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));

// Get rooms from where guests are leaving
$sql = "SELECT 'departure' AS type, b.room_id, brc.new_room_id, bd.checked_in FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id LEFT OUTER JOIN booking_room_changes brc ON (b.id=brc.booking_id AND brc.date_of_room_change='$yesterday') WHERE bd.cancelled=0 AND bd.maintenance=0 AND bd.last_night='$yesterday'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get departures for date: $today. Error: " . mysql_error($link) . " (SQL: $sql)");
}
echo "There are " . mysql_num_rows($result) . " departures on $today<br>\n";
while($row = mysql_fetch_assoc($result)) {
	$roomId = (is_null($row['new_room_id']) ? $row['room_id'] : $row['new_room_id']);
	if(!isset($roomsToClean[$roomId])) {
		$roomsToClean[$roomId] = array();
	}
	$roomsToClean[$roomId][] = $row;
}

// Get rooms where there was a room_change yesterday and today there is either no room change or a room change to a different room.
$sql = "SELECT 'room_change' AS type, b.room_id, brcy.new_room_id AS yesterday_new_room_id, brct.new_room_id AS today_new_room_id, brcy.leave_new_room_time AS left_room_time, brct.enter_new_room_time AS enter_room_time FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id LEFT OUTER JOIN booking_room_changes brcy ON (b.id=brcy.booking_id AND brcy.date_of_room_change='$yesterday') LEFT OUTER JOIN booking_room_changes brct ON (b.id=brct.booking_id AND brct.date_of_room_change='$today') WHERE bd.first_night<='$yesterday' AND bd.last_night>='$today' AND brcy.new_room_id<>brct.new_room_id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get departures for date: $today. Error: " . mysql_error($link) . " (SQL: $sql)");
}
echo "There are " . mysql_num_rows($result) . " room changes on $today<br>\n";
while($row = mysql_fetch_assoc($result)) {
	$roomId = (is_null($oneRoomToClean['yesterday_new_room_id']) ? $oneRoomToClean['room_id'] : $oneRoomToClean['yesterday_new_room_id']);
	if($row['room_id'] != $row['yesterday_new_room_id'] or $row['room_id'] != $row['today_new_room_id']) {
		if(!isset($roomsToClean[$roomId])) {
			$roomsToClean[$roomId] = array();
		}
		$roomsToClean[$roomId][] = $row;
	}
}

// Get cleaner actions
$sql = "SELECT * FROM cleaner_action WHERE time_of_event>'$dayToShow'";
$result = mysql_query($sql, $link);
$actions = array();
if(!$result) {
	trigger_error("Cannot get clear actions for date: $dayToShow. Error: " . mysql_error($link) . " (SQL: $sql)");
}
// echo "There are " . mysql_num_rows($result) . " room changes on $today<br>\n";
while($row = mysql_fetch_assoc($result)) {
	if(!isset($actions[$row['room_id']])) {
		$actions[$row['room_id']] = array();
	}
	$actions[$row['room_id']][] = $row;
}

//echo "<pre>\n";
//print_r($roomsToClean);
//echo "</pre>\n";

html_start("Rooms to clean");

echo "<div class=\"row\"><div class=\"col-md-offset-4 col-md-4\">\n";
foreach($roomsToClean as $roomId => $cleanList) {
	$canCleanRoom = true;
	$cleanerEntered = null;
	$roomCleaned = false;
	$room = $rooms[$roomId];
	foreach($cleanList as $oneRoomToClean) {
		if($oneRoomToClean['type'] == 'departure') {
			$canCleanRoom = $canCleanRoom and ($oneRoomToClean['checked_in'] === 0);
		} else {
			if(!is_null($oneRoomToClean['today_new_room_id']) and is_null($oneRoomToClean['enter_room_time'])) {
				$canCleanRoom = false;
			}
			if(!is_null($oneRoomToClean['yesterday_new_room_id']) and is_null($oneRoomToClean['left_room_time'])) {
				$canCleanRoom = false;
			}
		}
	}
	if(isset($actions[$roomId])) {
		foreach($actions[$roomId] as $oneAction) {
			if($oneAction['type'] == 'ENTER_ROOM') {
				$cleanerEntered = $oneAction['cleaner'];
			}
			if($oneAction['type'] == 'FINISH_ROOM') {
				$roomCleaned = true;
			}
		}
	}

	$roomName = $room['name'];
	if($roomCleaned) {
		echo "<a href=\"enter_room.php?room_id=$roomId\" role=\"button\" class=\"btn btn-default btn-lg btn-block\">$roomName is clean</a>\n";
	} elseif(!is_null($cleanerEntered)) {
		echo "<a href=\"enter_room.php?room_id=$roomId\" role=\"button\" class=\"btn btn-default btn-lg btn-block\">$roomName<br>In progress by $cleanerEntered</a>\n";
	} elseif(!$canCleanRoom) {
		echo "<a href=\"#\" role=\"button\" class=\"btn btn-default btn-lg btn-block disabled\">$roomName<br>Guest still in room</a>\n";
	} else {
		echo "<a href=\"enter_room.php?room_id=$roomId\" role=\"button\" class=\"btn btn-default btn-lg btn-block\">$roomName</a>\n";
	}
}

echo "</div></div>\n";


echo <<<EOT
</table>

EOT;


html_end();



?>