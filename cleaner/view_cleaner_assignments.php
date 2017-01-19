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
$roomChanges = BookingDao::getRoomChangeBookings($dayToShow, $link);

// Get cleaner assignments
$assignments = CleanerDao::getCleanerAssignments($dayToShow, $link);

// Get cleaner actions
$actions = CleanerDao::getCleanerActions($dayToShow, $link);

// Get the available cleaners
$cleaners = UserDao::getUsersForRole('CLEANER', $link);


html_start("Assign rooms to cleaners");

logDebug("leaves for $dayToShow: " . count($leaves));
logDebug("room changes for $dayToShow: " . count($roomChanges));

echo <<<EOT

<form action="assign_cleaners.php" method="POST" accept-charset="utf-8">
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
		$changedRoomId = (is_null($oneRc['yesterday_new_room_id']) ? $oneRc['room_id'] : $oneRc['yesterday_new_room_id']);
		if($changedRoomId == $roomId) {
			$bookingIds[] = $oneRc['bid'];
			$numOfBeds += $oneRc['num_of_person'] + $oneRc['extra_beds'];
		}
	}
	
	
	if($numOfBeds > 0) {
		$roomAssignment = getAssignment($assignments, 'ROOM', $roomId);
		$bathroomAssignment = getAssignment($assignments, 'BATHROOM', $roomId);
		$roomNotes = is_null($roomAssignment) ? '' : $roomAssignment['comment'];
		$bathroomNotes = is_null($bathroomAssignment) ? '' : $bathroomAssignment['comment'];
		
		$rcleanerOptions = "<option value=\"\"></option>";
		$brcleanerOptions = "<option value=\"\"></option>";
		foreach($cleaners as $oneCleaner) {
			$rselected = (!is_null($roomAssignment) and ($roomAssignment['cleaner'] == $oneCleaner['username']));
			$brselected = (!is_null($bathroomAssignment) and ($bathroomAssignment['cleaner'] == $oneCleaner['username']));
			$rcleanerOptions .= "<option value=\"" . $oneCleaner['username'] . "\"" . ($rselected ? ' selected' : '') . ">" . $oneCleaner['name'] . "</option>";
			$brcleanerOptions .= "<option value=\"" . $oneCleaner['username'] . "\"" . ($brselected ? ' selected' : '') . ">" . $oneCleaner['name'] . "</option>";
		}
		$roomStatus = getRoomStatus($actions, $roomId);
		$bathroomStatus = getBathRoomStatus($actions, $roomId);
		$bookingIds = implode(',', $bookingIds);
		echo <<<EOT
	<input type="hidden" name="booking_ids_$roomId" value="$bookingIds">
	<tr>
		<td>$roomName</td><td>$numOfBeds</td>
		<td><select name="room_cleaner_$roomId">$rcleanerOptions</select></td>
		<td><input name="room_note_$roomId" value="$roomNotes"></td>
		<td>$roomStatus</td>
		<td><select name="bathroom_cleaner_$roomId">$brcleanerOptions</select></td>
		<td><input name="bathroom_note_$roomId" value="$bathroomNotes"></td>
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

function getAssignment($assignments, $roomPart, $roomId) {
	foreach($assignments as $oneAssignment) {
		if($oneAssignment['room_part'] == $roomPart and $oneAssignment['room_id'] == $roomId) {
			logDebug("For room: $roomId and part: $roomPart the assigned cleaner is " . $oneAssignment['cleaner']);
			return $oneAssignment;
		}
	}
	return null;
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
