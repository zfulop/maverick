<?php

require("includes.php");
require("room_booking.php");

$link = db_connect();

$currDate = $_REQUEST['date'];
list($selectedYear, $selectedMonth, $selectedDay) = explode('-', $currDate);
$rooms = loadRooms($selectedYear, $selectedMonth, $selectedDay, $selectedYear, $selectedMonth, $selectedDay, $link);
$roomId = $_REQUEST['roomId'];
$room = $rooms[$roomId];


mysql_close($link);

$currDate = str_replace('-', '/', $currDate);
echo "			<div>\n";
echo "<div style=\"font-size: 130%;font-weight:bold;\">View bookings for room: " . $room['name'] . " and date: $currDate</div>";
$hasBooking = hasBookingForDay($room, $currDate);
if(!$hasBooking) {
	return;
}


echo "				<table border=\"1\" style=\"font-weight: normal; background: rgb(120, 240, 120)\">\n";
echo "					<tr><th>Name</th><th>1st night</th><th>Last night</th><th>Num. of person</th><th>Room</th><th>Balance</th><th>Status</th><th>Action</th></tr>\n";
foreach($room['bookings'] as $oneBooking) {
	if($oneBooking['cancelled'] or ($oneBooking['first_night'] > $currDate) or ($oneBooking['last_night'] < $currDate)) {
		continue;
	}
	if(isset($oneBooking['changes'])) {
		$isThereRoomChangeForDay = false;
		foreach($oneBooking['changes'] as $oneChange) {	
			if($oneChange['date_of_room_change'] == $currDate) {
				$isThereRoomChangeForDay = true;
			}
		}
		if($isThereRoomChangeForDay)
			continue;
	}

	echo getTableRow($oneBooking, $room);
	//echo "<tr><td>RC<br><pre>" . print_r($oneBooking, true) . "</pre></td></tr>\n";
}

foreach($room['room_changes'] as $oneRoomChange) {
	if($oneRoomChange['cancelled'] or ($oneRoomChange['date_of_room_change'] != $currDate)) {
		continue;
	}
	echo getTableRow($oneRoomChange, $room);
	//echo "<tr><td>RC<br><pre>" . print_r($oneRoomChange, true) . "</pre></td></tr>\n";
}

echo "				</table>\n";
echo "			</div>\n";


function getTableRow(&$booking, &$room) {
	$status = getStatusCell($booking);
	$name = $booking['name'] . ' ' . $booking['name_ext'];
	$fnight = $booking['first_night'];
	if(isset($booking['arrival_time'])) {
		$fnight .= '<br>' . $booking['arrival_time'];
	}
	$lnight = $booking['last_night'];
	$numOfPerson = $booking['num_of_person'];
	$roomName = $room['name'];
	$balance = $booking['room_payment'];
	$lastPaymentDate = 0;
	foreach($booking['payments'] as $payment) {
		$lastPaymentDate = max($lastPaymentDate, substr($payment['time_of_payment'], 0, 10));
	}
	$dtOfConversion = ($booking['paid'] ? $lastPaymentDate : date('Y-m-d'));
	foreach($booking['service_charges'] as $sc) {
		$balance += convertAmount($sc['amount'], $sc['currency'], 'EUR', $dtOfConversion);
	}
	foreach($booking['payments'] as $payment) {
		if($payment['storno'] == 1) {
			continue;
		}
		$balance -= convertAmount($payment['amount'], $payment['currency'], 'EUR', $dtOfConversion);
	}
	$balance = sprintf('%.2f', $balance) . ' EUR';

	$actionsHtml = 	getActionCell($booking);

	$retVal = <<<EOT
		<tr>
			<td>$name</td>
			<td>$fnight</td><td>$lnight</td>
			<td align="center">$numOfPerson</td><td>$roomName</td>
			<td align="right">$balance</td>
			<td><ul>
$status
			</ul></td>
			<td><ul>
$actionsHtml
			</ul></td>
		</tr>

EOT;
	return $retVal;

}

function getStatusCell(&$booking) {
	$status = '';
	if($booking['confirmed'] == 1)
		$status .= '<li>confirmed</li>';
	if($booking['cancelled'] == 1) {
		$status .= '<li>cancelled';
		if(!is_null($booking['cancel_type'])) {
			$status .= ' (' . $booking['cancel_type'] . ')';
		}
		$status .= '</li>';
	}
	if($booking['paid'] == 1)
		$status .= '<li>paid</li>';
	if($booking['checked_in'] == 1)
		$status .= '<li>checked in</li>';

	return $status;
}

function getActionCell(&$booking) {
	$actionsHtml = 	"\t\t\t\t\t\t\t<li><a href=\"edit_booking.php?description_id=" . $booking['description_id'] . "\">Edit...</a></li>\n";
	$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"print_booking_summary.php?description_id=" . $booking['description_id'] . "\">Print booking summary</a></li>\n";
	if(!$booking['cancelled']) {
		$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"#\" onclick=\"if(confirm('Are you sure to cancel the booking?')) { window.location='cancel_booking.php?description_id=" . $booking['description_id'] . "&type=reception';} return false;\">Reception cancel</a></li>\n";
		$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"#\" onclick=\"if(confirm('Are you sure to cancel the booking?')) { window.location='cancel_booking.php?description_id=" . $booking['description_id'] . "&type=guest';} return false;\">Guest cancel</a></li>\n";
		$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"#\" onclick=\"if(confirm('Are you sure to \'no show\' the booking?')) { window.location='cancel_booking.php?description_id=" . $booking['description_id'] . "&type=no_show';} return false;\">No show</a></li>\n";
		if($booking['confirmed']) {
			$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"unconfirm_booking.php?description_id=" . $booking['description_id'] . "\">Unconfirm</a></li>\n";
		} else {
			$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"confirm_booking.php?description_id=" . $booking['description_id'] . "\">Confirm</a></li>\n";
		}
		if($booking['checked_in']) {
			$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"checkout_booking.php?description_id=" . $booking['description_id'] . "\">Checkout</a></li>\n";
		} else {
			$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"checkin_booking.php?description_id=" . $booking['description_id'] . "\">Checkin</a></li>\n";
		}
		if($booking['paid']) {
			$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"not_paid_booking.php?description_id=" . $booking['description_id'] . "\">Not paid</a></li>\n";
		} else {
			$actionsHtml .= "\t\t\t\t\t\t\t<li><a href=\"paid_booking.php?description_id=" . $booking['description_id'] . "\">Paid</a></li>\n";
		}

	}
	return $actionsHtml;
}



function hasBookingForDay(&$room, $currDate) {
	$currDate = str_replace('-', '/', $currDate);
	$hasBooking = false;
	foreach($room['bookings'] as $oneBooking) {
		if($oneBooking['cancelled']) {
			continue;
		}
		if(($oneBooking['first_night'] <= $currDate) and ($oneBooking['last_night'] >= $currDate)) {
			$hasBooking = true;
			break;
		}
	}
	foreach($room['room_changes'] as $oneRoomChange) {
		if($oneRoomChange['cancelled']) {
			continue;
		}
		if($oneRoomChange['date_of_room_change'] == $currDate) {
			$hasBooking = true;
			break;
		}
	}
	return $hasBooking;
}




?>
