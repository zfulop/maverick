<?php


require('../includes.php');
require('../../recepcio/room_booking.php');
require('dict.php');



foreach($_REQUEST as $key => $value) {
	$_SESSION["booking_$key"] = $value;
}

$name = $_SESSION["booking_name"];
$address = $_SESSION["booking_address"];
$gender = $_SESSION["booking_gender"];
$email = $_SESSION["booking_email"];
$telephone = $_SESSION["booking_telephone"];
$nationality = $_SESSION["booking_nationality"];
$comment = $_SESSION["booking_comment"];


$hasError = false;
if(strlen($name) < 1) {
	set_error(NAME_MISSING);
	$hasError = true;
}
if(strlen($address) < 1) {
	set_error(ADDRESS_MISSING);
	$hasError = true;
}
if(strlen($nationality) < 1) {
	set_error(NATIONAILITY_MISSING);
	$hasError = true;
}
if(strlen($gender) < 1) {
	set_error(GENDER_MISSING);
	$hasError = true;
}
if(strlen($email) < 1) {
	set_error(EMAIL_MISSING);
	$hasError = true;
}
if(strlen($telephone) < 1) {
	set_error(TELEPHONE_MISSING);
	$hasError = true;
}

if($hasError) {
	header("Location: book_now.php");
	return;
}


$link = db_connect();

$year = intval($_SESSION['booking_year']);
$month = intval($_SESSION['booking_month']);
$day = intval($_SESSION['booking_day']);
$nights = intval($_SESSION['booking_nights']);

if($month < 10) {
	$month = '0' . $month;
}
if($day < 10) {
	$day = '0' . $day;
}

$startDate = "$year-$month-$day";
$endDate = date('Y-m-d', strtotime("$startDate +" . ($nights - 1) . " day"));

$lang = getCurrentLanguage();

list($startYear, $startMonth, $startDay) = explode('-', $startDate);
list($endYear, $endMonth, $endDay) = explode('-', $endDate);
$rooms  = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link, $lang);

$numOfPersonForRoomCode = array();
foreach($ROOM_IDS_FOR_ROOM_CODE as $roomCode => $roomIds) {
	$type = 'PRIVATE';
	if(in_array($roomCode, $DORM_ROOMS_BY_ROOM_CODE)) {
		$type = 'DORM';
	}
	if(isset($_SESSION['booking_room_' . $type . '_' . $roomCode])) {
		$numOfPersonForRoomCode[$roomCode] = $_SESSION['booking_room_' . $type . '_' . $roomCode];
	}
}
if(count($numOfPersonForRoomCode) < 1) {
	set_error(NO_ROOM_SELECTED);
	header("Location: view_availability.php?year=$year&month=$month&day=$day&nights=$nights");
	mysql_close($link);
	return;
}
$overbookings = getOverbookings($numOfPersonForRoomCode, $startDate, $endDate, $rooms);
if(count($overbookings) > 0) {
	foreach($overbookings as $roomCode => $datesUnavailable) {
		$roomName = $roomCode;
		$roomIds = $ROOM_IDS_FOR_ROOM_CODE[$roomCode];
		$numOfPerson = $numOfPersonForRoomCode[$roomCode];
		if(count($roomIds) == 1)
			$roomName = $rooms[$roomIds[0]]['name'];

		set_error(sprintf(NOT_AVAILABLE, $roomName));
	}

	header("Location: view_availability.php?year=$year&month=$month&day=$day&nights=$nights");
	mysql_close($link);
	return;
}


// Now create an array: $toBook that contains the roomId as a key and the value contains the number
// of people and the type (ROOM or BED) of the booking.
list($toBook, $roomChanges) = getBookingData($numOfPersonForRoomCode, $startDate, $endDate, $rooms);

$sql = "INSERT INTO booking_descriptions (name, gender, address, nationality, email, telephone, first_night, last_night, num_of_nights, cancelled, confirmed, paid, checked_in, comment, source, arrival_time) VALUES ('$name', '$gender', '$address', '$nationality', '$email', '$telephone', '" . str_replace("-", "/", $startDate) . "', '" . str_replace("-", "/", $endDate) . "', $nights, 0, 0, 0, 0, '$comment', 'saját', '')";
set_debug($sql);

if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save booking: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Could not save booking description.');
	header('Location: edit_new_booking.php');
	mysql_close($link);
	return;
}
$descriptionId = mysql_insert_id($link);

$bookingIds = saveBookings($toBook, $roomChanges, $startDate, $endDate, $rooms, $descriptionId, $link);
audit(AUDIT_CREATE_BOOKING, array('booking_data' => $toBook, 'room_change_data' => $roomChanges), $bookingIds[0], $descriptionId, $link);


sendMail($name, $email, $telephone, $startDate, $endDate, $nights, $numOfPersonForRoomCode, $rooms, $nationality, $address, $comment);

foreach($_SESSION as $key => $value) {
	if(substr($key, 0, 8) == 'booking_')
		unset($_SESSION[$key]);
}

$googleAd = <<<EOT
<!-- Google Code for foglal&aacute;s Conversion Page -->

<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 999565014;
var google_conversion_language = "en";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "lBQMCPqVqgQQ1s3Q3AM";
var google_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js"></script>

<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/999565014/?value=0&amp;label=lBQMCPqVqgQQ1s3Q3AM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

EOT;

set_message(sprintf(BOOKING_SAVED, $email) . $googleAd);
mysql_close($link);
header("Location: index.php");
return;




function sendMail($name, $email, $tel, $startNight, $endNight, $numOfDays, $numOfPersonForRoomCode, &$rooms, $nationality, $address, $comment) {
	global $ROOM_IDS_FOR_ROOM_CODE;
	global $DORM_ROOMS_BY_ROOM_CODE;
	global $NUM_OF_BEDS_PER_ROOM_CODE;

	$msg = BOOKING_CONFIRM_HEADER . "\n\n";
	$msg .= NAME . ": $name\n";
	$msg .= FIRST_NIGHT . ": $startNight\n";
	$msg .= LAST_NIGHT . ": $endNight\n";
	$msg .= NUMBER_OF_DAYS . ": $numOfDays\n";
	$adminMsg =<<<EOT
Booking confirmation for:
Name: $name
Email: $email
Telephone: $tel
First night: $startNight
Last night: $endNight
Number of days: $numOfDays
Address: $address
Nationality: $nationality
Comment: $comment


EOT;
	$totalPrice = 0;
	foreach($numOfPersonForRoomCode as $roomCode => $numOfPerson) {
		if($numOfPerson < 1)
			continue;

		$type = 'ROOM';
		if(in_array($roomCode, $DORM_ROOMS_BY_ROOM_CODE)) {
			$type = 'BED';
		}
		$roomIds = $ROOM_IDS_FOR_ROOM_CODE[$roomCode];
		$payment = getPriceForInterval($startNight, $endNight, $type, $rooms[$roomIds[0]]);
		if($type == 'BED') {
			$payment = $payment * $numOfPerson;
		} else {
			$payment = $payment * $numOfPerson / $NUM_OF_BEDS_PER_ROOM_CODE[$roomCode];
		}
		$totalPrice += $payment;
		$roomName = $roomCode;
		if(count($roomIds) == 1) {
			$roomName = $rooms[$roomIds[0]]['name'];
		} elseif($roomName == 'ensuites') {
			$roomName = 'double ensuites';
		}

		$msg .= ROOM . ": $roomName [" . constant($type) . "] - " . NUMBER_OF_PERSON . ": " . $numOfPerson . ", " . PRICE . ": " . $payment . " euro\n";
		$adminMsg .= "* Room: $roomName [$type] - Number of person: $numOfPerson, price: $payment euro\n";
	}

	$msg .= "\n" . TOTAL_PRICE . ": " . $totalPrice . " euro\n";
	$adminMsg .= "\nTotal price: $totalPrice euro\n";
	set_debug("Client message: $msg");
	set_debug("Admin message: $adminMsg");

	$headers =	'From: reservation@maverickhostel.com' . "\r\n" .
    			'Reply-To: reservation@maverickhostel.com' . "\r\n";
	mail("$name <$email>", BOOKING_CONFIRM_SUBJECT, $msg, $headers);
	$headers =	"From: $email" . "\r\n" .
    			"Reply-To: $email" . "\r\n";
	mail("reservation@maverickhostel.com", "Online booking received!", $adminMsg, $headers);
}

?>
