<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}


$link = db_connect();

// Load room data
$rooms = RoomDao::getRooms($link);

$dayToShow = date('Y-m-d');
$today = date('Y/m/d', strtotime($dayToShow));
$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));

// Get rooms from where guests are leaving
$leaves = BookingDao::getLeavingBookings($dayToShow, $link);

// Get rooms where there was a room_change yesterday and today there is either no room change or a room change to a different room.
$roomChanges = BookingDao::getLeavingBookings($dayToShow, $link);

// Get cleaner assignments
$assignments = CleanerDao::getCleanerAssignments($dayToShow, $link);

// Get cleaner actions
$actions = CleanerDao::getCleanerActions($dayToShow, $link);

// Get the available cleaners
$cleaners = UserDao::getUsersForRole('CLEANER', $link);


html_start("Assign rooms to cleaners");

logDebug("leaves for $dayToShow: " . print_r($leaves, true));
logDebug("room changes for $dayToShow: " . print_r($roomChanges, true));

echo <<<EOT

<form action"assign_cleaners.php" method="POST" accept-charset="utf-8">
<table class="table form-inline">
	<tr>
		<th>Room name</th>
		<th># of beds to clean</th>
		<th>Room cleaner</th>
		<th>Room notes</th>
		<th>Room Status</th>
		<th>Bathroom cleaner</th>
		<th>Bathroom notes</th>
		<th>Bathroom Status</th>
	</tr>

EOT;

$lastActionForRoom = array();
$lastActionForBathroom = array();
foreach($actions as $oneAction) {
	if(in_array($oneAction['type'], array('REJECT_FINISH_ROOM','REJECT_FINISH_BATHROOM'))) {
		if($oneAction['type'] == 'REJECT_FINISH_ROOM') {
			$lastActionForRoom[$oneAction['room_id']] = $oneAction['type'] . ' ' . $oneAction['comment'];
		} else {
			$lastActionForBathroom[$oneAction['room_id']] = $oneAction['type'] . ' ' . $oneAction['comment'];
		}
	} else {
		if(strpos($oneAction['room_id'], 'BATH') > 0) {
			$lastActionForBathroom[$oneAction['room_id']] = $oneAction['type'];
		} else {
			$lastActionForRoom[$oneAction['room_id']] = $oneAction['type'];
		}
	}
}



foreach($rooms as $roomId => $roomData) {
	$roomName = $roomData['name'];
	$numOfBeds = 0;
	$bookingIds = array();
	foreach($leaves as $oneLeave) {
		$leftRoomId = (is_null($oneLeave['new_room_id']) ? $oneLeave['room_id'] : $oneLeave['new_room_id']);
		if($leftRoomId == $roomId) {
			$bookingIds[] = $oneLeave['bid'];
			$numOfBeds += $oneLeave['num_of_person'] + $oneLeave['extra_beds'];
		}
	}
	foreach($roomChanges as $oneRc) {
		$changedRoomId = (is_null($row['yesterday_new_room_id']) ? $row['room_id'] : $row['yesterday_new_room_id']);
		if($changedRoomId == $roomId) {
			$bookingIds[] = $oneRc['bid'];
			$numOfBeds += $oneRc['num_of_person'] + $oneRc['extra_beds'];
		}
	}
	
	
	if($numOfBeds > 0) {
		$rcleanerOptions = "<option value=\"\"></option>";
		$brcleanerOptions = "<option value=\"\"></option>";
		foreach($cleaners as $oneCleaner) {
			$rcleanerOptions .= "<option value=\"" . $oneCleaner['username'] . "\"" . (isCleanerAssigned($oneCleaner['name'], 'ROOM', $assignments) ? ' selected' : '') . ">" . $oneCleaner['name'] . "</option>";
			$brcleanerOptions .= "<option value=\"" . $oneCleaner['username'] . "\"" . (isCleanerAssigned($oneCleaner['name'], 'BATHROOM', $assignments) ? ' selected' : '') . ">" . $oneCleaner['name'] . "</option>";
		}
		$roomStatus = getRoomStatus($actions, $roomId);
		$bathroomStatus = getBathRoomStatus($actions, $roomId);
		$bookingIds = implode(',', $bookingIds);
		echo <<<EOT
	<input type="hidden" name="booking_ids_$roomId" value="$bookingIds">
	<tr>
		<td>$roomName</td><td>$numOfBeds</td>
		<td><select name="room_cleaner_$roomId">$rcleanerOptions</select></td>
		<td><input name="room_note_$roomId"></td>
		<td>$roomStatus</td>
		<td><select name="bathroom_cleaner_$roomId">$brcleanerOptions</select></td>
		<td><input name="bathroom_note_$roomId"></td>
		<td>$bathroomStatus</td>
		<td>
			<a href="#" onclick="room_id=$roomId" class="btn btn-success">Confirm bathroom</a>
			<a href="#" onclick="room_id=$roomId" class="btn btn-danger">Reject bathroom</a>
			<a href="#" onclick="room_id=$roomId" class="btn btn-success">Confirm room</a>
			<a href="#" onclick="room_id=$roomId" class="btn btn-danger">Reject room</a>
		</td>
	</tr>

EOT;

	}
}


echo <<<EOT
</table>
<button type="submit" class="btn btn-default">Save cleaner assignments</button>
</form>

EOT;


html_end();


function isCleanerAssigned($cleanerName, $cleanType, &$assignments) {
	foreach($assignments as $oneAssignment) {
		if($oneAssignment['type'] == $cleanType and $oneAssignment['cleaner'] == $cleanerName) {
			return true;
		}
	}
	return false;
}

function getRoomStatus($actions, $roomId) {
	$roomActionTypes = array('ENTER_ROOM','LEAVE_ROOM','FINISH_ROOM','CONFIRM_FINISH_ROOM', 'REJECT_FINISH_ROOM');
	$status = getStatus($actions, $roomId, $roomActionTypes);
	return $status;
}

function getBathRoomStatus($actions, $roomId) {
	$athroomActionTypes = array('ENTER_BATHROOM','LEAVE_BATHROOM','FINISH_BATHROOM','CONFIRM_FINISH_BATHROOM', 'REJECT_FINISH_BATHROOM');
	$status = getStatus($actions, $roomId, $athroomActionTypes);
	return $status;	
}

function getStatus($actions, $roomId, $possibleActionTypes) {
	$roomActionTypes = array('ENTER_ROOM','LEAVE_ROOM','FINISH_ROOM','CONFIRM_FINISH_ROOM', 'REJECT_FINISH_ROOM');
	$status = '';
	foreach($actions as $oneAction) {
		if($oneAction['room_id'] == $roomId and in_array($oneAction['type'], $possibleActionTypes)) {
			$status = $oneAction['type'] . '(' . $oneAction['cleaner'] . ')';
		}
	}
	return $status;
}


?>
