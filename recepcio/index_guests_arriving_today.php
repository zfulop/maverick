<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

$dayToShow = $_SESSION['day_to_show'];

$today = date('Y/m/d', strtotime($dayToShow));
$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));
$todayDash = $dayToShow;
$tomorrowDash = date('Y-m-d', strtotime($dayToShow . ' +1 day'));


$bdids = array();
$arrivingToday = array();
$sql = "SELECT bd.*, bd2.first_night AS prev_first_night FROM booking_descriptions bd LEFT OUTER JOIN booking_descriptions bd2 ON (((bd.email<>'' AND bd.email=bd2.email) OR bd.name=bd2.name) AND bd.first_night>bd2.first_night AND bd2.cancelled<>1) WHERE bd.first_night='$today' and bd.checked_in=0 AND bd.cancelled=0";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get arriving guests for today: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot get arriving guests for today");
} else {
	while($row = mysql_fetch_assoc($result)) {
		if(in_array($row['id'], $bdids)) {
			continue;
		}
		$arrivingToday[] = $row;
		$bdids[] = $row['id'];
	}
}

$checkedin = array();


$bookings = array();
if(count($bdids) > 0) {
	$sql = "SELECT * FROM bookings WHERE description_id IN (" . implode(',', $bdids) . ")";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get bookings: " . mysql_error($link) . " (SQL: $sql)");
		set_error("Cannot get bookings");
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$bookings[$row['description_id']][] = $row;
		}
	}
}


$roomChanges = array();
$sql = "SELECT brc.id, brc.booking_id, brc.new_room_id, brc.date_of_room_change, brc.enter_new_room_time, brc.leave_new_room_time, b.room_id, bd.name as bd_name, bd.name_ext as bd_name_ext, bd.first_night as bd_first_night, bd.last_night as bd_last_night, bgd.name as bgd_name FROM booking_room_changes brc INNER JOIN bookings b ON brc.booking_id=b.id INNER JOIN booking_descriptions bd ON (b.description_id=bd.id AND bd.cancelled=0) LEFT OUTER JOIN booking_guest_data bgd ON (b.room_id=bgd.room_id AND bgd.booking_description_id=bd.id) WHERE brc.date_of_room_change IN ('$today', '$yesterday')";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot room changes for today: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot get room changes for today");
} else {
	while($row = mysql_fetch_assoc($result)) {
		if(!isset($roomChanges[$row['booking_id']])) {
			$roomChanges[$row['booking_id']] = array();
		}
		$roomChanges[$row['booking_id']][] = $row;
	}
}

$rooms = RoomDao::getRooms($link);

$roomTypes = RoomDao::getRoomTypes('eng', $link);

$roomsStatus = array();
$bathroomsStatus = array();
$sql = "SELECT * FROM cleaner_action WHERE time_of_event>'$todayDash' AND time_of_event<'$tomorrowDash' ORDER BY time_of_event";
$result = mysql_query($sql, $link);
$initSendBcr = '';
if(!$result) {
	trigger_error("Cannot get cleaned rooms: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot get cleaned rooms");
} else {
	while($row = mysql_fetch_assoc($result)) {
		if($row['type'] == 'CONFIRM_FINISH_ROOM' or $row['type'] == 'REJECT_FINISH_ROOM') {
			$roomsStatus[$row['room_id']] = $row['type'];
		} elseif($row['type'] == 'CONFIRM_FINISH_BATHROOM' or $row['type'] == 'REJECT_FINISH_BATHROOM') {
			$bathroomsStatus[$row['room_id']] = $row['type'];
		}
	}
}

$cleanedRooms = array();
foreach($rooms as $room) {
	$roomType = $roomTypes[$room['room_type_id']];
	if(isset($roomsStatus[$room['id']]) and $roomsStatus[$room['id']] == 'CONFIRM_FINISH_ROOM' and 
			((isset($bathroomsStatus[$room['id']]) and $bathroomsStatus[$room['id']] == 'CONFIRM_FINISH_BATHROOM') or $roomType['type'] == 'DORM')) {
		$cleanedRooms[$room['id']] = true;
	}
}


if(isset($_REQUEST['arriving_order'])) {
	$_SESSION['arriving_order'] = $_REQUEST['arriving_order'];
} elseif(!isset($_SESSION['arriving_order'])) {
	$_SESSION['arriving_order'] = 'name';
}
$arriveOrder = $_SESSION['arriving_order'];

if($_SESSION['arriving_order'] == 'name') {
	$nameTitle = '<a href="index.php?arriving_order=name" style="font-size: 130%;">Name</a>';
} else {
	$nameTitle = '<a href="index.php?arriving_order=name">Name</a>';
}
if($_SESSION['arriving_order'] == 'room') {
	$roomTitle = '<a href="index.php?arriving_order=room" style="font-size: 130%;">Rooms</a>';
} else {
	$roomTitle = '<a href="index.php?arriving_order=room">Rooms</a>';
}
if($_SESSION['arriving_order'] == 'arrival_time') {
	$arrivalTimeTitle = '<a href="index.php?arriving_order=arrival_time" style="font-size: 130%;">Arrival Time</a>';
} else {
	$arrivalTimeTitle = '<a href="index.php?arriving_order=arrival_time">Arrival Time</a>';
}

if(count($arrivingToday) > 0) {
	echo <<<EOT

<h2>Guests arriving today</h2>
<table>
	<tr><th>$nameTitle</th><th>$roomTitle</th><th>$arrivalTimeTitle</th><th>Cln</th></tr>

EOT;
	$dataArr = array();
	foreach($arrivingToday as $bookingDescr) {
		$descrId = $bookingDescr['id'];
		$name = $bookingDescr['name_ext'] . ' ' . $bookingDescr['name'];
		if(!is_null($bookingDescr['prev_first_night'])) {
			$name = '<b>'. $name . '</b> ' . $bookingDescr['prev_first_night'];
		}
		$aTime = $bookingDescr['arrival_time'];
		if(isset($bookings[$descrId])) {
			$roomAccounted = array();
			$roomNames = '';
			$roomsCleaned = '';
			foreach($bookings[$descrId] as $oneBooking) {
				$roomName = $rooms[$oneBooking['room_id']]['name'];
				$roomId = $oneBooking['room_id'];
				foreach($roomChanges as $bookingId => $changes) {
					foreach($changes as $oneRoomChange) {
						if($oneRoomChange['booking_id'] == $oneBooking['id'] and $oneRoomChange['date_of_room_change'] == $today) {
							$roomName = $rooms[$oneRoomChange['new_room_id']]['name'];
							$roomId = $oneRoomChange['new_room_id'];
						}
					}
				}
				$roomNames .= $roomName . ' (' . $oneBooking['num_of_person'] . ')<br>';
				$roomsCleaned .= isset($cleanedRooms[$roomId]) ? '<span style="font-size:80%;">Cleaned</span><br>' : '<span style="font-size:80%;">Not Ready</span><br>';
				if($_SESSION['arriving_order'] == 'room') {
					$dataArr[] = array($name, $roomName . ' (' . $oneBooking['num_of_person'] . ')', $aTime, $descrId, $roomsCleaned);
				}
			}
			if($_SESSION['arriving_order'] != 'room') {
				$dataArr[] = array($name, $roomNames, $aTime, $descrId, $roomsCleaned);
			}
		}
	}

	$sortFunction = 'orderBy0';
	if($_SESSION['arriving_order'] == 'name') {
		$sortFunction = 'orderBy0';
	} elseif($_SESSION['arriving_order'] == 'room') {
		$sortFunction = 'orderBy1';
	} elseif($_SESSION['arriving_order'] == 'arrival_time') {
		$sortFunction = 'orderBy2';
	}
	usort($dataArr, $sortFunction);
	foreach($dataArr as $row) {
		$name = $row[0];
		$room = $row[1];
		$aTime = $row[2];
		$descrId = $row[3];
		$roomCleaned = $row[4];
		echo "	<tr><td><a href=\"edit_booking.php?description_id=$descrId\">$name</a></td><td>$room</td><td>$aTime</td><td>$roomCleaned</td></tr>\n";
	}


	echo <<<EOT
</table>



EOT;
} else {
	echo "<p>No guests arriving today.</p>\n";
}


function isDiffRoomChange($change1, $change2) {
	if(is_null($change1) and is_null($change2)) return false;
	if(is_null($change1) or is_null($change2)) return true;
	return $change1['new_room_id'] != $change2['new_room_id'];
}


function orderBy0($a1, $a2) {
	return orderBy($a1, $a2, 0);
}

function orderBy1($a1, $a2) {
	return orderBy($a1, $a2, 1);
}

function orderBy2($a1, $a2) {
	return orderBy($a1, $a2, 2);
}

function orderBy3($a1, $a2) {
	return orderBy($a1, $a2, 3);
}

function orderBy4($a1, $a2) {
	return orderBy($a1, $a2, 4);
}

function orderBy5($a1, $a2) {
	return orderBy($a1, $a2, 5);
}

function orderBy6($a1, $a2) {
	return orderBy($a1, $a2, 6);
}

function orderBy($a1, $a2, $idx) {
	if($a1[$idx] < $a2[$idx]) {
		return -1;
	} elseif($a1[$idx] > $a2[$idx]) {
		return 1;
	} else {
		return 0;
	}
}

?>
