<?php

require("includes.php");

$link = db_connect();

if(isset($_REQUEST['day_to_show'])) {
	$dayToShow = $_REQUEST['day_to_show'];
} else {
	$dayToShow = date('Y-m-d');
}

if(date('G') < 5 and !isset($_REQUEST['day_to_show'])) {
	$today = date('Y/m/d', strtotime(date('Y-m-d') . ' -1 day'));
	$yesterday = date('Y/m/d', strtotime(date('Y-m-d') . ' -2 day'));
} else {
	$today = date('Y/m/d', strtotime($dayToShow));
	$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));
}

$bdids = array();
$arrivingToday = array();
$sql = "SELECT bd.*, bd2.first_night AS prev_first_night FROM booking_descriptions bd LEFT OUTER JOIN booking_descriptions bd2 ON (bd.email=bd2.email AND bd.first_night>bd2.first_night) WHERE bd.first_night='$today' and bd.checked_in=0 AND bd.cancelled=0";
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
	$sql = "SELECT * FROM booking_descriptions WHERE cancelled=0 and first_night<='$today' and last_night>='$today'";
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
$sql = "SELECT booking_room_changes.booking_id, booking_room_changes.new_room_id, booking_room_changes.date_of_room_change, bookings.room_id, booking_descriptions.name as bd_name, booking_descriptions.name_ext as bd_name_ext, booking_descriptions.first_night as bd_first_night, booking_descriptions.last_night as bd_last_night, booking_guest_data.name as bgd_name FROM booking_room_changes INNER JOIN bookings ON booking_room_changes.booking_id=bookings.id INNER JOIN booking_descriptions ON (bookings.description_id=booking_descriptions.id AND booking_descriptions.cancelled=0) LEFT OUTER JOIN booking_guest_data ON (bookings.room_id=booking_guest_data.room_id AND booking_guest_data.booking_description_id=booking_descriptions.id) WHERE booking_room_changes.date_of_room_change IN ('$today', '$yesterday')";
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

$roomNames = array();
$sql = "SELECT * FROM rooms";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot room names: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot get room names");
} else {
	while($row = mysql_fetch_assoc($result)) {
		$roomNames[$row['id']] = $row['name'];
	}
}

$twoWeeksFromToday = date('Y/m/d', strtotime(date('Y-m-d') . ' + 2 weeks'));
$bcrTo = array();
$sendBcr = array();
$sql = "SELECT * FROM booking_descriptions WHERE first_night<='$twoWeeksFromToday' AND cancelled=0 AND confirmed=0 AND checked_in=0 AND bcr_sent IS NULL ORDER BY first_night";
$result = mysql_query($sql, $link);
$initSendBcr = '';
if(!$result) {
	trigger_error("Cannot bookings without BCR: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot get bookings without BCR");
} else {
	$counter = 1;
	while($row = mysql_fetch_assoc($result)) {
		$bcrTo[] = $row;
		$bdid = $row['id'];
		if($counter <= 3 and strlen(trim($row['email'])) > 1) {
			$initSendBcr .= "\t\t\tsetTimeout(function() { sendBcr($bdid); }, " . (2000*$counter) . ");\n";
			$counter+=1;
		}
	}
}

$extraHeader =<<<EOT

	<script type="text/javascript">

		function cancelBooking(id) {
			if(confirm('Are you sure to cancel the booking?')) { 
				new Ajax.Request('cancel_booking.php', {
					method: 'post',
					parameters: {description_id: id, type: 'reception'},
					onSuccess: function(transport) {
						alert('The booking is cancelled.');
						$('bcr_' + id).hide();
					}
				});
			}
		}

		function bcrSent(id) {
			new Ajax.Request('set_bcr_sent.php', {
				method: 'post',
				parameters: {description_id: id},
				onSuccess: function(transport) {
					alert(transport.responseText);
					$('bcr_' + id).hide();
				}
			});
		}

		function sendBcr(id) {
			new Ajax.Request('send_bcr.php', {
				method: 'post',
				parameters: {description_id: id},
				onSuccess: function(transport) {
					if(transport.responseText.substring(0,2) == 'OK') {
						alert('The BCR sent is set for the booking: ' + transport.responseText.substring(3));
						$('bcr_' + id).hide();
					} else {
						alert('BCR could not be sent for the booking: ' + transport.responseText);
					}
				}
			});
		}

		function initSendBcr() {
$initSendBcr
		}

	</script>

EOT;


html_start("Maverick Reception - Activities for today ($today)", $extraHeader, true, 'initSendBcr();');

	echo <<<EOT

<form action="index.php">
Current date is set to: $dayToShow.<br>
<input name="day_to_show" value="$dayToShow"><input type="submit" value="Set ative day">
</form><br><br>

<table style="border-collapse: collapse;">
<tr><td style="vertical-align: top; border: 1px solid black; padding: 10px;">

EOT;

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
	<tr><th>$nameTitle</th><th>$roomTitle</th><th>$arrivalTimeTitle</th></tr>

EOT;
	$dataArr = array();
	foreach($arrivingToday as $bookingDescr) {
		$descrId = $bookingDescr['id'];
		$name = $bookingDescr['name_ext'] . ' ' . $bookingDescr['name'];
		if(!is_null($bookingDescr['prev_first_night'])) {
			$name = '<b>'. $name . '</b> ' . $bookingDescr['prev_first_night'];
		}
		$rooms = '';
		$aTime = $bookingDescr['arrival_time'];
		if(isset($bookings[$descrId])) {
			foreach($bookings[$descrId] as $oneBooking) {
				$roomName = $roomNames[$oneBooking['room_id']];
				foreach($roomChanges as $bookingId => $changes) {
					foreach($changes as $oneRoomChange) {
						if($oneRoomChange['booking_id'] == $oneBooking['id'] and $oneRoomChange['date_of_room_change'] == $today) {
							$roomName = $roomNames[$oneRoomChange['new_room_id']];
						}
					}
				}
				$rooms .= $roomName . ' (' . $oneBooking['num_of_person'] . ')<br>';
				if($_SESSION['arriving_order'] == 'room') {
					$dataArr[] = array($name, $roomName . ' (' . $oneBooking['num_of_person'] . ')', $aTime, $descrId);
				}
			}
			if($_SESSION['arriving_order'] != 'room') {
				$dataArr[] = array($name, $roomName . ' (' . $oneBooking['num_of_person'] . ')', $aTime, $descrId);
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
		echo "	<tr><td><a href=\"edit_booking.php?description_id=$descrId\">$name</a></td><td>$room</td><td>$aTime</td></tr>\n";
	}


	echo <<<EOT
</table>



EOT;
} else {
	echo "<p>No guests arriving today.</p>\n";
}

echo <<<EOT

</td><td style="vertical-align: top; border: 1px solid black; padding: 10px;">


EOT;

if(count($leavingToday) > 0) {
	echo <<<EOT
<h2>Guests leaving today</h2>
<table>
	<tr><th>Name</th><th>Rooms</th><th>Balance</th><th></th></tr>

EOT;

	foreach($leavingToday as $bookingDescr) {
		$descrId = $bookingDescr['id'];
		$name = $bookingDescr['name_ext'] . ' ' . $bookingDescr['name'];
		$rooms = '';
		$roomTotal = 0;
		if(isset($bookings[$descrId])) {
			foreach($bookings[$descrId] as $oneBooking) {
				$roomTotal += $oneBooking['room_payment'];
				$roomName = $roomNames[$oneBooking['room_id']];
				foreach($roomChanges as $bookingId => $changes) {
					foreach($changes as $oneRoomChange) {
						if($oneRoomChange['booking_id'] == $oneBooking['id'] and $oneRoomChange['date_of_room_change'] == $yesterday) {
							$roomName = $roomNames[$oneRoomChange['new_room_id']];
						}
					}
				}
				$rooms .= $roomName . ' (' . $oneBooking['num_of_person'] . ')<br>';
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

		echo "	<tr><td><a href=\"edit_booking.php?description_id=$descrId\">$name</a></td><td>$rooms</td><td>$balance EUR</td></tr>\n";
	}

	echo <<<EOT
</table>

EOT;
} else {
	echo "<p>No guests leaving today.</p>\n";
}

echo <<<EOT

</td><td style="vertical-align: top; border: 1px solid black; padding: 10px;">


EOT;

if(count($roomChanges) > 0) {
	echo <<<EOT

<h2>Room changes today</h2>
<table>
	<tr><th>From room</th><th>To room</th><th>Guest</th></tr>

EOT;

	foreach($roomChanges as $bookingId => $changes) {
		$changeYesterday = null;
		$changeToday = null;
		foreach($changes as $oneChange) {
			if(($oneChange['date_of_room_change'] == $today) && ($oneChange['bd_first_night'] != $today)) {
				$changeToday = $oneChange;
			} else if(($oneChange['date_of_room_change'] == $yesterday) && ($oneChange['bd_last_night'] != $yesterday)) {
				$changeYesterday = $oneChange;
			}
		}
		$fromRoom = null;
		$toRoom = null;
		$guest = null;
		if(!is_null($changeToday)) {
			if(!is_null($changeYesterday) && isDiffRoomChange($changeYesterday, $changeToday)) {
				$toRoom = $roomNames[$changeToday['new_room_id']];
				$fromRoom = $roomNames[$changeYesterday['new_room_id']];
			} else if(is_null($changeYesterday)) {
				$toRoom = $roomNames[$changeToday['new_room_id']];
				$fromRoom = $roomNames[$changeToday['room_id']];
			}
			$guest = $changeToday['bgd_name'];
			if(is_null($guest)) {
				$guest = $changeToday['bd_name'];
			}
		} else if(!is_null($changeYesterday)) {
			$toRoom = $roomNames[$changeYesterday['room_id']];
			$fromRoom = $roomNames[$changeYesterday['new_room_id']];
			$guest = $changeYesterday['bgd_name'];
			if(is_null($guest)) {
				$guest = $changeYesterday['bd_name'];
			}
		}

		if(!is_null($fromRoom)) {
			echo "	<tr><td>$fromRoom</td><td>$toRoom</td><td>$guest</td></tr>\n";
		}
	}

	echo "</table>\n";
} else {
	echo "<p>No room changes for today</p>\n";
}


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

</td></tr></table>
<br>

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
				if(isset($roomNames[$oneGD['room_id']])) {
					$gdRoom = $roomNames[$oneGD['room_id']];
				} else {
					$gdRoom = '-';
				}
				$dataArr[] = array($id, $name, $fnight, $lnight, $gdRoom, $oneGD['name'], $oneGD['deposit'], $style);
			} else {
				$gdName .= str_replace(' ', '&nbsp;', $oneGD['name']) . '<br>';
				if(isset($roomNames[$oneGD['room_id']])) {
					$gdRoom .= str_replace(' ', '&nbsp;', $roomNames[$oneGD['room_id']]) . '<br>';
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
			$roomName = $roomNames[$oneBooking['room_id']];
			foreach($roomChanges as $bookingId => $changes) {
				foreach($changes as $oneRoomChange) {
					if($oneRoomChange['booking_id'] == $oneBooking['id'] and $oneRoomChange['date_of_room_change'] == $yesterday) {
						$roomName = $roomNames[$oneRoomChange['new_room_id']] . '(RC)';
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


<h2>Send BCR to</h2>
<table style="border: 1px solid #000;">
	<tr>
		<th>Name</th>
		<th>First Night</th>
		<th>Last Night</th>
		<th>Email</th>
		<th>Telephone</th>
		<th>Souce</th>
		<th>Comment</th>
		<th>Action</th>
	</tr>

EOT;


$bgColor='#ffffff';
foreach($bcrTo as $bookingDescr) {
	$bgColor = ($bgColor=='#ffffff' ? '#dddddd' : '#ffffff');
	$id = $bookingDescr['id'];
	$name = $bookingDescr['name_ext'] . ' ' . $bookingDescr['name'];
	$fnight = str_replace('/','-',$bookingDescr['first_night']);
	$lnight = str_replace('/','-',$bookingDescr['last_night']);
	$email = $bookingDescr['email'];
	$telephone = $bookingDescr['telephone'];
	$source = $bookingDescr['source'];
	$comment = $bookingDescr['comment'];
		echo <<<EOT
	<tr id="bcr_$id" style="background-color: $bgColor;">
		<td><a href="edit_booking.php?description_id=$id">$name</a></td>
		<td>$fnight</td>
		<td>$lnight</td>
		<td>$email</td>
		<td>$telephone</td>
		<td>$source</td>
		<td style="position: relative;">
			<div style="text-align: center;">
				<a href="#" id="comment_show_btn_$id" onclick="$('comment_$id').show();$('comment_hide_btn_$id').show();$('comment_show_btn_$id').hide();return false;">Show</a>
				<a href="#" id="comment_hide_btn_$id" onclick="$('comment_$id').hide();$('comment_hide_btn_$id').hide();$('comment_show_btn_$id').show();return false;" style="display: none;">Hide</a>
			</div>
			<div id="comment_$id" style="display: none; boder: 1px dotted black; background-color: #eeeeee; width: 250px; height: 300px; overflow: none; position: absolute; font-size: 80%; z-index: 10; overflow:scroll"><pre>$comment</pre></div>
		</td>
		<td>
			<a href="#" onclick="bcrSent($id); return false;">Set BCR-Sent</a>|
			<a href="#" onclick="sendBcr($id); return false;">Send BCR</a>
		</td>
	</tr>
EOT;
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