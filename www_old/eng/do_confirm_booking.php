<?php

require('../includes.php');

if(!isset($_SESSION['confirm_booking_validated']) or !$_SESSION['confirm_booking_validated']) {
	header('Location: index.php');
	return;
}

$descrId = $_SESSION['confirm_booking_descr_id'];

$bcname = $_REQUEST['name'];
$bcfnight = $_REQUEST['first_night'];
$arrivalTime = $_REQUEST['arrival_time'];
$comment = $_REQUEST['comment'];

if(strlen($arrivalTime) < 1) {
	set_error("Please enter the arrival time.");
	header("Location: " . $_SERVER['HTTP_REFERER']);
	return;
}

$link = db_connect();
$hasError = false;
$sql = "UPDATE bcr SET name='$bcname', first_night='$bcfnight', checkin_time='$arrivalTime', comment='$comment' WHERE booking_description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error('Cannot confirm booking: Cannot update booking data');
	//set_error("sql: $sql. error: " . mysql_error($link));
	$hasError = true;
}

$sql = "UPDATE booking_descriptions SET arrival_time='$arrivalTime', confirmed=1 WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error('Cannot confirm booking: Cannot update booking data');
	//set_error("sql: $sql. error: " . mysql_error($link));
	$hasError = true;
}

$sql = $sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error('Cannot confirm booking: Cannot load booking data');
	//set_error("sql: $sql. error: " . mysql_error($link));
	$hasError = true;
} else {
	$row = $row = mysql_fetch_assoc($result);
	$name = $row['name'];
	$email = $row['email'];
	$phone = $row['telephone'];
	$fnight = $row['first_night'];
	$lnight = $row['last_night'];
}

$sql = "SELECT b.*, l.value AS room_name FROM bookings b INNER JOIN lang_text l on (l.table_name='rooms' and l.column_name='name' and l.row_id=b.room_id AND l.lang='eng') WHERE b.description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error('Cannot confirm booking: Cannot get booking data');
	//set_error("sql: $sql. error: " . mysql_error($link));
	$hasError = true;
}

$rooms = '<table><tr><th>Room name</th><th>Type</th><th>Number of person</th></tr>';
while($row = mysql_fetch_assoc($result)) {
	$rooms .= '<tr><td>' . $row['room_name'] . '</td><td>' . strtolower($row['booking_type']) . '</td><td>' . $row['num_of_person'] . '</td></tr>';
}
$rooms .= '</table>';


$msg = <<<EOT

<a href="http://recepcio.maverickhostel.com/edit_booking.php?description_id=$descrId">Edit booking</a><br>

Booking confirmation info:
<table>
	<tr><td>Name</td><td>$bcname></td></tr>
	<tr><td>First night</td><td>$bcfnight</td></tr>
	<tr><td>Arrival time</td><td>$arrivalTime</td></tr>
	<tr><td>Comment</td><td>$comment</td></tr>
</table>

<h2>Booking information</h2>
<table>
	<tr><td>Name</td><td>$name</td></tr>
	<tr><td>Email</td><td>$email</td></tr>
	<tr><td>Phone</td><td>$phone</td></tr>
	<tr><td>First night</td><td>$fnight</td></tr>
	<tr><td>Last night</td><td>$lnight</td></tr>
	<tr><td>Rooms</td><td>$rooms</td></tr>
</table>

EOT;

$headers =	"From: $email" . "\r\n" .
   			'Content-type: text/html' . "\r\n";

if(!$hasError) {
	set_message('Thank you. Your booking is confirmed.');
}

mail('reservation@maverickhostel.com', 'Booking confirmation', $msg, $headers);
//set_message('Mail to: reservation@maverickhostel.com<br><br' . $msg);
html_start('BookingAndPrices', null, 'Booking Confirmation');

html_end('BookingAndPrices', null);



?>
