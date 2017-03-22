<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

if($_SESSION['login_role'] != 'CLEANER') {
	header('Location: view_cleaner_assignments.php');
	return;
}

$link = db_connect();

$dayToShow = date('Y-m-d');
$today = date('Y/m/d', strtotime($dayToShow));
$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));
$cleaner = $_SESSION['login_user'];

// Load room data
$rooms = RoomDao::getRooms($link);

// Get cleaner assignments
$assignments = CleanerDao::getCleanerAssignmentsForCleaner($cleaner, $dayToShow, $link);

// Get cleaner actions
$actions = CleanerDao::getCleanerActionsForCleaner($cleaner, $dayToShow, $link);

// Get rooms from where guests are leaving
$leaves = BookingDao::getLeavingBookings($dayToShow, $link);

// Get rooms where there was a room_change yesterday and today there is either no room change or a room change to a different room.
$roomChanges = BookingDao::getRoomChangeBookings($dayToShow, $link);

$lastActionForRoom = array();
$lastActionForBathroom = array();
foreach($actions as $oneAction) {
	if(in_array($oneAction['type'], array('REJECT_FINISH_ROOM','REJECT_FINISH_BATHROOM'))) {
		if($oneAction['type'] == 'REJECT_FINISH_ROOM') {
			$lastActionForRoom[$oneAction['room_id']] = $oneAction['type'] . ' ' . $oneAction['comment'];
		} else {
			$lastActionForBathroom[$oneAction['room_id']] = $oneAction['type'] . ' ' . $oneAction['comment'];
		}
	} elseif($oneAction['type'] != 'NOTE') {
		if(strpos($oneAction['room_id'], 'BATH') > 0) {
			$lastActionForBathroom[$oneAction['room_id']] = $oneAction['type'];
		} else {
			$lastActionForRoom[$oneAction['room_id']] = $oneAction['type'];
		}
	}
}

// If cleaner is already entered into one of the rooms (to clean room or bathroom), redirect to the room page
foreach($lastActionForRoom as $roomId => $actionType) {
	if($actionType == 'ENTER_ROOM') {
		header("Location: view_room.php?room_id=$roomId&room_part=ROOM");
		mysql_close($link);
		return;
	}
}
foreach($lastActionForBathroom as $roomId => $actionType) {
	if($actionType == 'ENTER_BATHROOM') {
		header("Location: view_room.php?room_id=$roomId&room_part=BATHROOM");
		mysql_close($link);
		return;
	}
}

$extraHeader = <<<EOT

<script type="text/javascript">
	function refresh() {
		window.location.reload(true);
	}
</script>

EOT;

html_start("Rooms to clean", $extraHeader, 'setTimeout(refresh, 60000);');

echo "<div class=\"row\"><div class=\"col-md-offset-4 col-md-4\">\n";
foreach($assignments as $oneAssignment) {
	$roomId = $oneAssignment['room_id'];
	$room = $rooms[$roomId];
	$roomName = $room['name'];
	$roomStatus = '';
	$roomCleaned = false;
	$roomPart = $oneAssignment['room_part'];
	if(isset($lastActionForRoom[$roomId]) and $roomPart == 'ROOM') {
		$roomStatus = $lastActionForRoom[$roomId];
		$roomCleaned = ($lastActionForRoom[$roomId] == 'CONFIRM_FINISH_ROOM');
	} elseif(isset($lastActionForBathroom[$roomId]) and $roomPart == 'BATHROOM') {
		$roomStatus = $lastActionForBathroom[$roomId];
		$roomCleaned = ($lastActionForBathoom[$roomId] == 'CONFIRM_FINISH_BATHROOM');
	}
	$canCleanRoom = CleanerUtils::canCleanRoom($roomId, $leaves, $roomChanges);
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

	if($roomCleaned) {
		echo "<!-- a href=\"enter_room.php?room_id=$roomId&room_part=$roomPart\" role=\"button\" class=\"btn btn-default btn-lg btn-block\">$roomName $roomPart is clean</a -->\n";
	} elseif(!$canCleanRoom) {
		echo "<a href=\"#\" role=\"button\" class=\"btn btn-default btn-lg btn-block disabled\">$roomName<br>A vendég még nem hagyta el a szobát</a>\n";
	} else {
		$roomPartTran = CleanerUtils::translate($roomPart);
		$roomStatus = CleanerUtils::translate($roomStatus);
		echo "<a href=\"enter_room.php?room_id=$roomId&room_part=$roomPart\" role=\"button\" class=\"btn btn-default btn-lg btn-block\">$roomName $roomPartTran<br>$roomStatus</a>\n";
	}
}

echo "</div></div>\n";


echo <<<EOT
</table>

EOT;


html_end();

?>
