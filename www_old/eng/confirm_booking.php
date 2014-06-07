<?php

require('../includes.php');
$link = db_connect();

$_SESSION['confirm_booking_validated'] = false;
if(!isset($_REQUEST['confirmCode'])) {
	set_error("Cannot confirm booking because no confirmation code is provided");
} else {
	$confirmCode = $_REQUEST['confirmCode'];
	$idx = strpos($confirmCode, 'A');
	$descrId = substr($confirmCode, 0, $idx);
	$code = substr($confirmCode, $idx + 1);
	$sql = $sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		set_error('Cannot confirm booking: Cannot load booking data');
		mysql_close($link);
		return;
	} else {
		$row = $row = mysql_fetch_assoc($result);
		$name = $row['name'];
		$email = $row['email'];
		$phone = $row['telephone'];
		$fnight = $row['first_night'];
		$lnight = $row['last_night'];
		if(crypt($email, $code) == $code) {
			$row = mysql_fetch_assoc($result);
			$_SESSION['confirm_booking_validated'] = true;
		}
	}
}
if(!$_SESSION['confirm_booking_validated']) {
	set_error('Cannot validate booking confirmation');
}
html_start('BookingAndPrices', null, 'Booking Confirmation');
if(!$_SESSION['confirm_booking_validated']) {
	html_end('BookingAndPrices', null);
	return;
}

$_SESSION['confirm_booking_descr_id'] = $descrId;

$sql = "SELECT b.*, l.value AS room_name FROM bookings b INNER JOIN lang_text l on (l.table_name='rooms' and l.column_name='name' and l.row_id=b.room_id AND l.lang='eng') WHERE b.description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo '<h2>Cannot get booking data</h2>';
	html_end('BookingAndPrices', null);
	mysql_close($link);
	return;
}

$rooms = '<table><tr><th>Type</th><th>Number of person</th></tr>';
while($row = mysql_fetch_assoc($result)) {
	$rooms .= '<tr><td>' . strtolower($row['booking_type']) . '</td><td>' . $row['num_of_person'] . '</td></tr>';
}
$rooms .= '</table>';

$fnight = str_replace('/', '-', $fnight);
$lnight = str_replace('/', '-', $lnight);

echo <<<EOT

<form action="do_confirm_booking.php" method="POST" accept-charset="UTF-8" >
<input type="hidden" name="confirmCode" value="$confirmCode">
<table>
	<tr><td>Name</td><td><input name="name" value="$name"></td></tr>
	<tr><td>First night</td><td><input name="first_night" value="$fnight"></td></tr>
	<tr><td>Arrival time</td><td><input name="arrival_time"></td></tr>
	<tr><td>Comment</td><td><textarea name="comment"></textarea></td></tr>
	<tr><td colspan="2"><input type="submit" value="Confirm booking"></td></tr>
</table>
</form>

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

html_end('BookingAndPrices', null);

?>
