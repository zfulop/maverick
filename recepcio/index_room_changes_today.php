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


$roomChanges = array();
$sql = "SELECT brc.id, brc.booking_id, brc.new_room_id, brc.date_of_room_change, brc.enter_new_room_time, brc.leave_new_room_time, b.room_id, 
		bd.name as bd_name, bd.name_ext as bd_name_ext, bd.first_night as bd_first_night, bd.last_night as bd_last_night, bgd.name as bgd_name 
	FROM booking_room_changes brc 
		INNER JOIN bookings b ON brc.booking_id=b.id 
		INNER JOIN booking_descriptions bd ON (b.description_id=bd.id AND bd.cancelled=0) 
		LEFT OUTER JOIN booking_guest_data bgd ON (b.room_id=bgd.room_id AND bgd.booking_description_id=bd.id) 
	WHERE brc.date_of_room_change IN ('$today', '$yesterday')";
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


//logDebug("There are " . count($roomChanges) . " room changes to check for today: $today and yesterday: $yesterday");
if(count($roomChanges) > 0) {
	$roomChangesToShow = array();
	foreach($roomChanges as $bookingId => $changes) {
		//logDebug("	for booking id: $bookingId there are " . count($changes) . " room change");
		$changeYesterday = null;
		$changeToday = null;
		foreach($changes as $oneChange) {
			//logdebug("		Checking change: " . print_r($oneChange, true));
			if(($oneChange['date_of_room_change'] == $today) && ($oneChange['bd_first_night'] != $today)) {
				$changeToday = $oneChange;
			} else if(($oneChange['date_of_room_change'] == $yesterday) && ($oneChange['bd_last_night'] != $yesterday)) {
				$changeYesterday = $oneChange;
			}
		}
		//logDebug("	is rc today null: " . is_null($changeToday));
		//logDebug("	is rc yesterday null: " . is_null($changeYesterday));
		//logDebug("	is rc today and yesterday different: " . isDiffRoomChange($changeYesterday, $changeToday));
		$fromRoomId = null;
		$toRoomId = null;
		$guest = null;
		$bdName = null;
		$leaveAction = '';
		$enterAction = '';
		if(!is_null($changeToday)) {
			if(!is_null($changeYesterday) && isDiffRoomChange($changeYesterday, $changeToday)) {
				//logDebug("	rc today not null and rc yesterday is not null either");
				$toRoomId = $changeToday['new_room_id'];
				$fromRoomId = $changeYesterday['new_room_id'];
				if(is_null($changeYesterday['leave_new_room_time'])) {
					$leaveAction = 'save_booking_room_change.php?today=' . urlencode($today) . '&leave_room_brc=' . $changeYesterday['id'];
				}
				if(is_null($changeToday['enter_new_room_time'])) {
					$enterAction = 'save_booking_room_change.php?today=' . urlencode($today) . '&enter_room_brc=' . $changeToday['id'];
				}
			} else if(is_null($changeYesterday)) {
				//logDebug("	rc today not null and rc yesterday is null");
				$toRoomId = $changeToday['new_room_id'];
				$fromRoomId = $changeToday['room_id'];
				if(is_null($changeToday['enter_new_room_time'])) {
					$enterAction = 'save_booking_room_change.php?today=' . urlencode($today) . '&enter_room_brc=' . $changeToday['id'];
				}
			}
			$bdName = $changeToday['bd_name'];
			$guest = $changeToday['bgd_name'];
			if(is_null($guest)) {
				$guest = $changeToday['bd_name'];
			}
		} else if(!is_null($changeYesterday)) {
			//logDebug("	rc today is null and rc yesterday is not null");
			$toRoomId = $changeYesterday['room_id'];
			$fromRoomId = $changeYesterday['new_room_id'];
			if(is_null($changeYesterday['leave_new_room_time'])) {
				$leaveAction = 'save_booking_room_change.php?today=' . urlencode($today) . '&leave_room_brc=' . $changeYesterday['id'];
			}
			$action = 'save_booking_room_change.php?today=' . urlencode($today) . '&brc_id[]=' . $changeYesterday['id'];
			$bdName = $changeYesterday['bd_name'];
			$guest = $changeYesterday['bgd_name'];
			if(is_null($guest)) {
				$guest = $changeYesterday['bd_name'];
			}
		}
		$action = '';
		if(strlen($leaveAction) > 0) {
			$action = "<a href=\"$leaveAction\">Leave room</a>";
		} elseif(strlen($enterAction) > 0) {
			$action = "<a href=\"$enterAction\">Enter new room</a>";
		}
		//logDebug("	from room: $fromRoomId, to room: $toRoomId");

		if(!is_null($fromRoomId)) {
			$key = min($toRoomId, $fromRoomId) . max($toRoomId, $fromRoomId) . $bdName;
			//logDebug("key: $key");
			if(array_key_exists($key, $roomChangesToShow)) {
				//logDebug("key exists, removing it...");
				unset($roomChangesToShow[$key]);
			} else {
				logDebug("adding key...");
				$toRoom = $rooms[$toRoomId]['name'];
				$fromRoom = $rooms[$fromRoomId]['name'];
				$roomChangesToShow[$key] = "	<tr><td>$fromRoom</td><td>$toRoom</td><td>$guest</td><td>$action</td></tr>\n";
			}
		} else {
			//logDebug("	fromRoomId is null, ignoring it");
		}
	}

	
	echo <<<EOT
<h2>Room changes today</h2>
<table>
	<tr><th>From room</th><th>To room</th><th>Guest</th><th>Action</th></tr>

EOT;
	foreach($roomChangesToShow as $key => $line) {
		echo $line;
	}
	
	echo "</table>\n";
} else {
	echo "<p>No room changes for today</p>\n";
}



function isDiffRoomChange($change1, $change2) {
	if(is_null($change1) and is_null($change2)) return false;
	if(is_null($change1) or is_null($change2)) return true;
	return $change1['new_room_id'] != $change2['new_room_id'];
}


?>
