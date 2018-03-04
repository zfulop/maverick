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
$checkedin = array();
if($dayToShow == date('Y-m-d')) {
	$sql = "SELECT * FROM booking_descriptions WHERE checked_in=1 AND cancelled=0";
} else {
	$sql = "SELECT * FROM booking_descriptions WHERE cancelled=0 and first_night<='$today' and last_night>='$yesterday'";
}
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get checked in guests: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot get checked in guests");
} else {
	while($row = mysql_fetch_assoc($result)) {
		$checkedin[] = $row;
		if(!in_array($row['id'], $bdids)) {
			$bdids[] = $row['id'];
		}
	}
}

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

$guestData = array();
if(count($bdids) > 0) {
	$sql = "SELECT * FROM booking_guest_data WHERE booking_description_id IN (" . implode(',', $bdids) . ")";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get guest data: " . mysql_error($link) . " (SQL: $sql)");
		set_error("Cannot get guest data");
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$guestData[$row['booking_description_id']][] = $row;
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



if(isset($_REQUEST['current_guest_order'])) {
	$_SESSION['current_guest_order'] = $_REQUEST['current_guest_order'];
} elseif(!isset($_SESSION['current_guest_order'])) {
	$_SESSION['current_guest_order'] = 'name';
}
$currentGuestOrder = $_SESSION['current_guest_order'];

if($currentGuestOrder == 'name') {
	$nameTitle = '<a href="index.php?current_guest_order=name" style="font-size: 130%;">Name</a>';
} else {
	$nameTitle = '<a href="index.php?current_guest_order=name">Name</a>';
}
if($currentGuestOrder == 'fnight') {
	$fnightTitle = '<a href="index.php?current_guest_order=fnight" style="font-size: 130%;">First night</a>';
} else {
	$fnightTitle = '<a href="index.php?current_guest_order=fnight">First night</a>';
}
if($currentGuestOrder == 'lnight') {
	$lnightTitle = '<a href="index.php?current_guest_order=lnight" style="font-size: 130%;">Last night</a>';
} else {
	$lnightTitle = '<a href="index.php?current_guest_order=lnight">Last night</a>';
}
if($currentGuestOrder == 'room') {
	$roomTitle = '<a href="index.php?current_guest_order=room" style="font-size: 130%;">Room</a>';
} else {
	$roomTitle = '<a href="index.php?current_guest_order=room">Room</a>';
}
if($currentGuestOrder == 'guest_name') {
	$guestNameTitle = '<a href="index.php?current_guest_order=guest_name" style="font-size: 130%;">Guest name</a>';
} else {
	$guestNameTitle = '<a href="index.php?current_guest_order=guest_name">Guest name</a>';
}
if($currentGuestOrder == 'guest_deposit') {
	$guestDepositTitle = '<a href="index.php?current_guest_order=guest_deposit" style="font-size: 130%;">Guest deposit</a>';
} else {
	$guestDepositTitle = '<a href="index.php?current_guest_order=guest_deposit">Guest deposit</a>';
}


echo <<<EOT


<h2>Current guests</h2>
<table style="border: 1px solid #000;">
	<tr>
		<th>$nameTitle</th>
		<th>$fnightTitle</th>
		<th>$lnightTitle</th>
		<th>$roomTitle</th>
		<th>$guestNameTitle</th>
		<th>$guestDepositTitle</th>
	</tr>

EOT;

$dataArr = array();
foreach($checkedin as $bookingDescr) {
	$id = $bookingDescr['id'];
	$name = $bookingDescr['name_ext'] . ' ' . $bookingDescr['name'];
	$fnight = $bookingDescr['first_night'];
	$lnight = $bookingDescr['last_night'];
	$style = "";
	if($lnight < $yesterday) {
		$style = "color: red;";
	}
	$gdName = '';
	$gdRoom = '';
	$gdDeposit = '';
	if(isset($guestData[$id])) {
		foreach($guestData[$id] as $oneGD) {
			if($currentGuestOrder == 'room' or $currentGuestOrder == 'guest_name' or $currentGuestOrder == 'guest_deposit') {
				if(isset($rooms[$oneGD['room_id']]['name'])) {
					$gdRoom = $rooms[$oneGD['room_id']]['name'];
				} else {
					$gdRoom = '-';
				}
				$dataArr[] = array($id, $name, $fnight, $lnight, $gdRoom, $oneGD['name'], $oneGD['deposit'], $style);
			} else {
				$gdName .= str_replace(' ', '&nbsp;', $oneGD['name']) . '<br>';
				if(isset($rooms[$oneGD['room_id']]['name'])) {
					$gdRoom .= str_replace(' ', '&nbsp;', $rooms[$oneGD['room_id']]['name']) . '<br>';
				} else {
					$gdRoom .= ' - <br>';
				}
				$gdDeposit .= str_replace(' ', '&nbsp;', $oneGD['deposit']) . '<br>';
			}
		}
		if($currentGuestOrder != 'room' and $currentGuestOrder != 'guest_name' and $currentGuestOrder != 'guest_deposit') {
			$dataArr[] = array($id, $name, $fnight, $lnight, $gdRoom, $gdName, $gdDeposit, $style);
		}
	} elseif(isset($bookings[$id])) {
		$roomCol = '';
		foreach($bookings[$id] as $oneBooking) {
			$roomName = '';
			if(!isset($rooms[$oneBooking['room_id']])) {
				$roomName = 'no room found for id: ' . $oneBooking['room_id'];
			} else {
				$roomName = $rooms[$oneBooking['room_id']]['name'];
			}
			foreach($roomChanges as $bookingId => $changes) {
				foreach($changes as $oneRoomChange) {
					if($oneRoomChange['booking_id'] == $oneBooking['id'] and $oneRoomChange['date_of_room_change'] == $yesterday) {
						$roomName = $rooms[$oneRoomChange['new_room_id']]['name'] . '(RC)';
					}
				}
			}

			if($currentGuestOrder == 'room') {
				$dataArr[] = array($id, $name, $fnight, $lnight, str_replace(' ', '&nbsp;',  $roomName) . '&nbsp;' . $oneBooking['num_of_person'], '', '', $style);
			} else {
				$roomCol .= str_replace(' ', '&nbsp;',  $roomName) . '&nbsp;' . $oneBooking['num_of_person'] . '<br>';
			}
		}
		if($currentGuestOrder != 'room') {
			$dataArr[] = array($id, $name, $fnight, $lnight, $roomCol, '', '', $style);
		}
	}

}

$sortFunction = 'orderBy0';
if($currentGuestOrder == 'name') {
	$sortFunction = 'orderBy1';
} elseif($currentGuestOrder == 'fnight') {
	$sortFunction = 'orderBy2';
} elseif($currentGuestOrder == 'lnight') {
	$sortFunction = 'orderBy3';
} elseif($currentGuestOrder == 'room') {
	$sortFunction = 'orderBy4';
} elseif($currentGuestOrder == 'guest_name') {
	$sortFunction = 'orderBy5';
} elseif($currentGuestOrder == 'guest_deposit') {
	$sortFunction = 'orderBy6';
}
usort($dataArr, $sortFunction);
foreach($dataArr as $row) {
	$id = $row[0];
	$name = $row[1];
	$fnight = $row[2];
	$lnight = $row[3];
	$room = $row[4];
	$gdName = $row[5];
	$gdDeposit = $row[6];
	$style = $row[7];
	echo "	<tr style=\"$style\"><td><a href=\"edit_booking.php?description_id=$id\">$name</a></td><td>$fnight</td><td>$lnight</td><td>$room</td><td>$gdName</td><td>$gdDeposit</td></tr>\n";

}


echo <<<EOT

</table>

EOT;


html_end();

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
