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
$leavingToday = array();
$leavingTodayBDIds = array();
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
		if($row['last_night'] == $yesterday) {
			$leavingToday[] = $row;
			$leavingTodayBDIds[] = $row['id'];
		}
		if(!in_array($row['id'], $bdids)) {
			$bdids[] = $row['id'];
		}
	}
}

$payments = array();
$serviceCharges = array();
if(count($leavingTodayBDIds) > 0) {
	$ids = implode(',', $leavingTodayBDIds);
	$sql = "SELECT * FROM payments WHERE booking_description_id IN ($ids)";
	$result = mysql_query($sql, $link);
	if(!$result) {
		$err = "Cannot get payment(s) of booking (with description_id(s): $ids).";
		set_error($err);
		trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
	while($row = mysql_fetch_assoc($result)) {
		if(!isset($payments[$row['booking_description_id']])) {
			$payments[$row['booking_description_id']] = array();
		}
		$payments[$row['booking_description_id']][] = $row;
	}

	$sql = "SELECT * FROM service_charges WHERE booking_description_id IN ($ids)";
	$result = mysql_query($sql, $link);
	if(!$result) {
		$err = "Cannot get service charge(s) of booking (with description_id(s): $ids).";
		set_error($err);
		trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
	while($row = mysql_fetch_assoc($result)) {
		if(!isset($serviceCharges[$row['booking_description_id']])) {
			$serviceCharges[$row['booking_description_id']] = array();
		}
		$serviceCharges[$row['booking_description_id']][] = $row;
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



if(count($leavingToday) > 0) {
	echo <<<EOT
<h2>Guests leaving today</h2>
<table>
	<tr><th>Name</th><th>Rooms</th><th>Balance</th><th></th></tr>

EOT;

	foreach($leavingToday as $bookingDescr) {
		$descrId = $bookingDescr['id'];
		$name = $bookingDescr['name_ext'] . ' ' . $bookingDescr['name'];
		$roomNames = '';
		$roomTotal = 0;
		if(isset($bookings[$descrId])) {
			foreach($bookings[$descrId] as $oneBooking) {
				$roomTotal += $oneBooking['room_payment'];
				if(!isset($rooms[$oneBooking['room_id']])) {
					$roomName = 'no room found for id: ' . $oneBooking['room_id'];
				} else {
					$roomName = $rooms[$oneBooking['room_id']]['name'];
				}
				foreach($roomChanges as $bookingId => $changes) {
					foreach($changes as $oneRoomChange) {
						if($oneRoomChange['booking_id'] == $oneBooking['id'] and $oneRoomChange['date_of_room_change'] == $yesterday) {
							$roomName = $rooms[$oneRoomChange['new_room_id']]['name'];
						}
					}
				}
				$roomNames .= $roomName . ' (' . $oneBooking['num_of_person'] . ')<br>';
			}
		}
		$serviceChargeTotal = 0;
		if(isset($serviceCharges[$descrId])) {
			foreach($serviceCharges[$descrId] as $sc) {
				$serviceChargeTotal += convertAmount($sc['amount'], $sc['currency'], 'EUR', date('Y-m-d'));
			}
		}
		$paymentTotal = 0;
		if(isset($payments[$descrId])) {
			foreach($payments[$descrId] as $payment) {
				$paymentTotal += convertAmount($payment['amount'], $payment['currency'], 'EUR', date('Y-m-d'));
			}
		}

		$balance = sprintf('%.2f', $roomTotal + $serviceChargeTotal - $paymentTotal);

		echo "	<tr><td><a href=\"edit_booking.php?description_id=$descrId\">$name</a></td><td>$roomNames</td><td>$balance EUR</td></tr>\n";
	}

	echo <<<EOT
</table>

EOT;
} else {
	echo "<p>No guests leaving today.</p>\n";
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
