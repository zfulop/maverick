<?php

require('includes.php');

$location = getLocation();
$lang = getCurrentLanguage();
$currency = getCurrency();


if(!isset($_SESSION['confirm_booking_validated']) or !$_SESSION['confirm_booking_validated']) {
	header('Location: index.php');
	return;
}

$descrId = $_SESSION['confirm_booking_descr_id'];

$arrivalTime = $_REQUEST['arrive_time'];
$comment = $_REQUEST['comment'];

$link = db_connect($location);
$hasError = false;
$sql = "UPDATE bcr SET checkin_time='$arrivalTime', comment='$comment' WHERE booking_description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	//set_error('Cannot confirm booking: Cannot update booking data');
	//set_error("sql: $sql. error: " . mysql_error($link));
	$hasError = true;
}

$sql = "UPDATE booking_descriptions SET arrival_time='$arrivalTime', confirmed=1 WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	//set_error('Cannot confirm booking: Cannot update booking data');
	//set_error("sql: $sql. error: " . mysql_error($link));
	$hasError = true;
}

$sql = $sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	//set_error('Cannot confirm booking: Cannot load booking data');
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

$sql = "SELECT b.*, l.value AS room_name FROM bookings b INNER JOIN rooms r ON b.room_id=r.id INNER JOIN lang_text l on (l.table_name='room_types' and l.column_name='name' and l.row_id=r.room_type_id AND l.lang='$lang') WHERE b.description_id=$descrId";
$result = mysql_query($sql, $link);

if(!$result) {
	//set_error('Cannot confirm booking: Cannot get booking data');
	//set_error("sql: $sql. error: " . mysql_error($link));
	$hasError = true;
}

$rooms = '<table><tr><th>Room name</th><th>Type</th><th>Number of person</th></tr>';
while($row = mysql_fetch_assoc($result)) {
	$rooms .= '<tr><td>' . $row['room_name'] . '</td><td>' . strtolower($row['booking_type']) . '</td><td>' . $row['num_of_person'] . '</td></tr>';
}
$rooms .= '</table>';

if($location == 'hostel') {
	$editBookingUrl = "http://recepcio.maverickhostel.com/edit_booking.php?description_id=$descrId";
} else {
	$editBookingUrl = "http://recepcio.mavericklodges.com/edit_booking.php?description_id=$descrId";
}

$msg = <<<EOT

<a href="$editBookingUrl">Edit booking</a><br>

Booking confirmation info:
<table>
	<tr><td>Name</td><td>$name></td></tr>
	<tr><td>First night</td><td>$fnight</td></tr>
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

$headers =	"From: reservation@mavericklodges.com" . "\r\n" .
   			'Content-type: text/html' . "\r\n";


mail(constant('CONTACT_EMAIL_' . strtoupper($location)), 'Booking confirmation', $msg, $headers);
//set_message('Mail to: reservation@maverickhostel.com<br><br' . $msg);
html_start(CONFIRM_BOOKING);

$thankYou = THANK_YOU;

$bcMessage = BOOKING_CONFIRMED_MESSAGE;

echo <<<EOT

      <h1 class="page-title page-title-booknow">$thankYou</h1>
      
      <div class="fluid-wrapper booking">
		<section id="thank-you" class="clearfix">
          $bcMessage<br>
          <iframe class="likebox" src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FMaverick-Hostel%2F115569091837790&amp;width&amp;height=258&amp;colorscheme=light&amp;show_faces=true&amp;header=false&amp;stream=false&amp;show_border=false"></iframe>
        </section>
      </div>


EOT;



html_end();



?>
