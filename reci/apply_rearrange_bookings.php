<?php

require 'includes.php';

if(count($_SESSION['rearrange_room_changes']) < 1) {
	set_error("There are no room changes.");
	header("Location: " . $_SERVER['HTTP_REFERER']);
	return;
}

$link = db_connect();


$rooms = array();
$sql = "SELECT * FROM rooms";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$rooms[$row['id']] = $row;
	}
}


$sql = "SELECT bookings.*, booking_descriptions.name, booking_descriptions.first_night, booking_descriptions.last_night  FROM bookings INNER JOIN booking_descriptions ON bookings.description_id=booking_descriptions.id WHERE bookings.id IN (" . implode(',', array_keys($_SESSION['rearrange_room_changes'])) . ")";
$result = mysql_query($sql, $link);
$bookings = array();
if(!$result) {
	trigger_error("Cannot get bookings: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$bookings[$row['id']] = $row;
	}
}

$sql = array();
$message = array();
foreach($_SESSION['rearrange_room_changes'] as $bookingId => $changes) {
	$oneBooking = $bookings[$bookingId];
	$msg = $oneBooking['name'] . ' ' . $oneBooking['first_night'] . ' - ' . $oneBooking['last_night'] . " in room: " . $rooms[$oneBooking['room_id']]['name'] . "<br><ul>";
	$sql[] = "DELETE FROM booking_room_changes WHERE booking_id=$bookingId AND date_of_room_change IN ('" . implode("','", array_keys($changes)) . "')";
	foreach($changes as $dateOfChange => $newRoomId) {
		if($oneBooking['room_id'] == $newRoomId)
			continue;

		$sql[] = "INSERT INTO booking_room_changes (booking_id, date_of_room_change, new_room_id) VALUES ($bookingId, '$dateOfChange', $newRoomId)";
		$msg .= "<li>$dateOfChange - " . $rooms[$newRoomId]['name'] . "</li>";
	}
	$msg .= "</ul>";
	$message[] = $msg;
}

foreach($sql as $s) {
	if(!mysql_query($s, $link)) {
		trigger_error("Cannot rearrange booking. Error: " . mysql_error($link) . " (SQL: $s)");
	}
}


html_start("Maverick Reception - Rearrange bookings");

echo "Rearranging bookings:<br>\n";
echo "<ul>\n";
foreach($message as $msg) {
	echo "<li>$msg</li>\n";
}
echo "</ul>\n";


mysql_close($link);

html_end();


?>
