<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$link = db_connect();

$dayToShow = date('Y-m-d');
$today = date('Y/m/d', strtotime($dayToShow));
$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));

$rooms = RoomDao::getRooms($link);

logDebug("Assigning cleaners to rooms...");

foreach($rooms as $roomId => $oneRoom) {
	if(isset($_REQUEST["booking_ids_$roomId"])) {
		$roomName = $oneRoom['name'];
		logDebug("Saving cleaner assignments for room: $roomName($roomId)");
		$bids = $_REQUEST["booking_ids_$roomId"];
		$roomCleaner = $_REQUEST["room_cleaner__$roomId"];
		$roomNotes = $_REQUEST["room_note_$roomId"];
		$bathroomCleaner = $_REQUEST["bathroom_cleaner__$roomId"];
		$bathroomNotes = $_REQUEST["bathroom_note_$roomId"];
		if(strlen($roomCleaner) > 0) {
			logDebug("room is for $roomCleaner, comment: $roomNotes, booking ids: $bids");
			if(CleanerDao::replaceCleanerAssignment($roomCleaner, $roomId, 'ROOM', $bids, $roomNotes, $link)) {
				set_message("room: $roomName is assigned to $roomCleaner");
			} else {
				set_error("cannot assign $roomCleaner to room: $roomName");
			}
		}
		if(strlen($bathroomCleaner) > 0) {
			logDebug("bathroom is for $roomCleaner, comment: $roomNotes, booking ids: $bids");
			if(CleanerDao::replaceCleanerAssignment($bathroomCleaner, $roomId, 'BATHROOM', $bids, $bathroomNotes, $link)) {
				set_message("bathroom: $roomName is assigned to $roomCleaner");
			} else {
				set_error("cannot assign $roomCleaner to bathroom: $roomName");
			}
		}
	}
}

logDebug("Assignments are processed");

header("Location: view_cleaner_assignments.php");

?>