<?php

$hostel = $argv[1];


$configFile = '../includes/config/' . $hostel . '.php';
if(file_exists($configFile)) {
	require($configFile);
}

require("includes.php");
require("bcr.php");

$_SESSION['login_user'] = 'bcr';

echo "BCR Sending - 7 day before arrival notification for $hostel.\n";

$link = db_connect($hostel);

$sql = "SELECT * FROM lang_text WHERE table_name='website'";
$result = mysql_query($sql, $link);
$dict = array();
while($row = mysql_fetch_assoc($result)) {
	$dict[$row['lang']][$row['column_name']] = $row['value'];
}

$bdId = null;
if(count($argv) > 2) {
	$bdId = intval($argv[2]);
}
$oneWeekFromToday = date('Y/m/d', strtotime(date('Y-m-d') . ' +1 week'));
$bcrTo = array();
$sendBcr = array();
if(is_null($bdId)) {
	$sql = "SELECT * FROM booking_descriptions WHERE first_night='$oneWeekFromToday' AND cancelled=0 AND confirmed=0 AND checked_in=0 AND bcr_sent IS NULL";
} else {
	$sql = "SELECT * FROM booking_descriptions WHERE id=$bdId";
}
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot bookings without BCR: " . mysql_error($link) . " (SQL: $sql)");
	echo "Cannot get bookings without BCR\n";
} else {
	while($row = mysql_fetch_assoc($result)) {
		if(is_null($row['language'])) {
			$row['language'] = 'eng';
		}
		sendBcr($row, $hostel, $link, $dict);
	}
}

mysql_close($link);


function sendBcr($bookingDescr, $location, $link, &$dict) {
	$lang = $bookingDescr['language'];
	$descrId = $bookingDescr['id'];
	$email = $bookingDescr['email'];
	$name = $bookingDescr['name'];
	$fnight = $bookingDescr['first_night'];
	
	if($email == '') {
		echo "ERROR: cannot send email to $name $fnight beause email not specified\n";
		return;
	}

	$bcr = new BCR($bookingDescr, $location, $dict, $link);
	$result = $bcr->sendBcrMessage($dict[$lang]['BCR_MESSAGE_ONE_WEEK_SUBJECT'], $dict[$lang]['BCR_MESSAGE_ONE_WEEK']);

	echo "BCR Email sent from " . CONTACT_EMAIL . " to $name $email $fnight [result: $result]\n";

	BookingDao::updateBcr($descrId, $email, $link);

	audit(AUDIT_BCR_SENT, array('hostel' => $location, 'lang' => $lang), 0, $descrId, $link);
}


?>
