<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$roomId = $_REQUEST['room_id'];
$cleaner = $_SESSION['login_user'];

$link = db_connect();

// Load room data
$sql = "SELECT r.id, r.room_type_id, r.name, rt.name AS rt_name, rt.type FROM rooms r INNER JOIN room_types rt ON r.room_type_id=rt.id WHERE r.id=$roomId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms. Error: " . mysql_error($link) . " (SQL: $sql)");
}
$roomData = mysql_fetch_assoc($result);
$dayToShow = date('Y-m-d');
$today = date('Y/m/d', strtotime($dayToShow));
$yesterday = date('Y/m/d', strtotime($dayToShow . ' -1 day'));

$toClean = array();
// Get bookings (and guest data) from where guests are leaving
$sql = "SELECT b.id, b.booking_type, bgd.bed FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id LEFT OUTER JOIN booking_room_changes brc ON (b.id=brc.booking_id AND brc.date_of_room_change='$yesterday') LEFT OUTER JOIN booking_guest_data bgd ON bd.id=bgd.booking_description_id WHERE bd.cancelled=0 AND bd.maintenance=0 AND bd.last_night='$yesterday' AND ((brc.new_room_id IS NULL AND b.room_id=$roomId) OR brc.new_room_id=$roomId)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get departure booking data for date: $today and room: $roomId. Error: " . mysql_error($link) . " (SQL: $sql)");
}
while($row = mysql_fetch_assoc($result)) {
	$toClean[] = $row;
}

// Get rooms where there was a room_change yesterday and today there is either no room change or a room change to a different room.
$sql = "SELECT b.id, b.booking_type, bgd.bed FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id LEFT OUTER JOIN booking_room_changes brcy ON (b.id=brcy.booking_id AND brcy.date_of_room_change='$yesterday') LEFT OUTER JOIN booking_room_changes brct ON (b.id=brct.booking_id AND brct.date_of_room_change='$today') LEFT OUTER JOIN booking_guest_data bgd ON bd.id=bgd.booking_description_id WHERE bd.first_night<='$yesterday' AND bd.last_night>='$today' AND brcy.new_room_id<>brct.new_room_id AND ((brcy.new_room_id IS NULL AND b.room_id=$roomId) OR brcy.new_room_id=$roomId)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get room changes for date: $today and room: $roomId. Error: " . mysql_error($link) . " (SQL: $sql)");
}
while($row = mysql_fetch_assoc($result)) {
	$toClean[] = $row;
}

$toCleanHtml = '';
if($roomData['type'] != 'DORM') {
	$toCleanHtml = 'Clean the whole room';
} else {
	$toCleanHtml = 'Clean beds:';
	foreach($toClean as $item) {
		if($item['bed'] <> '') {
			$toCleanHtml .= $item['bed'] . ",";
		}
	}
	//$toCleanHtml = substr($toCleanHtml, 0, -1);
}


// Get cleaner actions
$sql = "SELECT * FROM cleaner_action WHERE time_of_event>'$dayToShow' AND room_id=$roomId ORDER BY time_of_event";
$result = mysql_query($sql, $link);
$enter = null;
$notes = array();
if(!$result) {
	trigger_error("Cannot get clear actions for date: $dayToShow. Error: " . mysql_error($link) . " (SQL: $sql)");
}
// echo "There are " . mysql_num_rows($result) . " room changes on $today<br>\n";
while($row = mysql_fetch_assoc($result)) {
	if($row['type'] == 'ENTER_ROOM') {
		$enter = $row;
	}
	if($row['type'] == 'NOTE') {
		$notes[] = $row;
		
	}
}

$notesHtml = '';
if(count($notes) > 0) {
	$notesHtml = "<b>Notes: <b><ul>\n";
	foreach($notes as $n) {
		$notesHtml .= "<li>" . $n['comment'] . "</li>\n";
	}
	$notesHtml .= "</ul>\n";
}

html_start($roomData['name'] . ' - ' . $roomData['rt_name']);

echo <<<EOT
<a href="leave_room.php?room_id=$roomId" role="button" class="btn btn-default btn-lg btn-block">Finish room</a>
$toCleanHtml <br>
<form class="form-inline" action="add_note.php" accept-charset="utf-8">
	<input type="hidden" name="room_id" value="$roomId">
	<div class="form-group">
		<label for="note">Note: </label>
		<input type="text" class="form-control" id="note" name="note">
	</div>
	<button type="submit" class="btn btn-default">Add Note</button>
</form>

$notesHtml <br>

EOT;


html_end();



?>