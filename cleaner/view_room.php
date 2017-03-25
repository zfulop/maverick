<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$roomId = $_REQUEST['room_id'];
$roomPart = $_REQUEST['room_part'];

$cleaner = $_SESSION['login_user'];

$link = db_connect();

// Load room data
$roomData = RoomDao::getRoom($roomId, $link);
$roomTypeData = RoomDao::getRoomType($roomData['room_type_id'], 'eng', $link);
$dayToShow = date('Y-m-d');

$theAssignment = null;
$assignments = CleanerDao::getCleanerAssignmentsForCleaner($cleaner, $dayToShow, $link);
foreach($assignments as $oneAssignment) {
	if($oneAssignment['room_part'] == $roomPart) {
		$theAssignment = $oneAssignment;
		break;
	}
}

$beds = '';
$assignmentNotesHtml = '';
if(is_null($theAssignment)) {
	set_error("Cannot find cleaner assignment. Please leave this room.");
	logError("Cleaner: $cleaner viewed room: $roomId today to clean: $roomPart. However there was no assignment for this cleaner.");
} else {
	$bookingIds = explode(",", $theAssignment['booking_ids']);
	$bookings = BookingDao::getBookings($bookingIds, $link);
	$bdIds = array();
	foreach($bookings as $b) { $bdIds[] = $b['description_id']; }
	$bookingGuestData = BookingDao::getBookingGuestData($bdIds, $link);
	$beds = array();
	foreach($bookingGuestData as $gd) {
		if($gd['room_id'] == $roomId and strlen(trim($gd['bed'])) > 0) {
			$beds[] = trim($gd['bed']);
		}
	}
	$beds = implode(",", $beds);
	if(strlen($theAssignment['comment']) > 0) {
		$assignmentNotesHtml = "<div class=\"panel panel-default\"><div class=\"panel-body\">" . $theAssignment['comment'] . "</div></div>\n";
	}
}

$roomPartTran = CleanerUtils::translate($roomPart);


$toCleanHtml = '';
if($roomTypeData['type'] != 'DORM') {
	$toCleanHtml = $roomPartTran . ' takarítása';
} else {
	$toCleanHtml = "Ezeket az ágyakat kell takarítani: $beds";
}


// Get cleaner actions
$actions = CleanerDao::getCleanerActions($dayToShow, $link);
$notes = array();
foreach($actions as $oneAction) {
	if($oneAction['room_id'] == $roomId and $oneAction['type'] == 'NOTE') {
		$notes[] = $oneAction;
	}
}

$notesHtml = '';
if(count($notes) > 0) {
	$notesHtml = "<div class=\"panel panel-default\"><div class=\"panel-heading\">Feljegyzések</div><div class=\"panel-body\"><ul>\n";
	foreach($notes as $n) {
		$notesHtml .= "<li>" . $n['comment'] . " (" . $n['cleaner'] . ")</li>\n";
	}
	$notesHtml .= "</ul></div></div>\n";
}

$noteOptions = '';
foreach(ListsDao::getCleanerItemTypes($link) as $item) {
	$noteOptions .= "<option value=\"$item\">$item</option>";
}

html_start($roomData['name'] . ' - ' . $roomTypeData['name']);

echo "<h2>" . $roomData['name'] . ' - ' . $roomTypeData['name'] . ' - ' . $roomPartTran . "</h2>\n";
echo <<<EOT
<a href="finish_room.php?room_id=$roomId&room_part=$roomPart" role="button" class="btn btn-default btn-lg btn-block">$roomPartTran kész</a>
<a href="leave_room.php?room_id=$roomId&room_part=$roomPart" role="button" class="btn btn-default btn-lg btn-block">$roomPartTran elhagyása (nem fejeztem be a takarítást itt)</a>
<div class="panel panel-default"><div class="panel-body">$toCleanHtml</div></div>
$assignmentNotesHtml
<div class="panel panel-default"><div class="panel-body">
<form class="form-inline" action="add_note.php" accept-charset="utf-8">
	<input type="hidden" name="room_id" value="$roomId">
	<input type="hidden" name="room_part" value="$roomPart">
	<div class="form-group">
		<label for="note">feljegyzés: </label>
		<select class="form-control" id="note" name="note">
$noteOptions
		</select>
	</div>
	<button type="submit" class="btn btn-default">Feljegyzés hozzáadása</button>
</form>
</div></div>
$notesHtml


EOT;


html_end();

mysql_close($link);


?>