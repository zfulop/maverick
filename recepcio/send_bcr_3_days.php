<?php

$hostel = $argv[1];

$configFile = '../includes/config/' . $hostel . '.php';
if(file_exists($configFile)) {
	require($configFile);
}

require("includes.php");
require("bcr.php");

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

$threeDaysFromToday = date('Y/m/d', strtotime(date('Y-m-d') . ' +3 day'));
$bcrTo = array();
$sendBcr = array();
echo "3 day before arrival notification for $hostel. \n";
if(is_null($bdId)) {
	$sql = "SELECT * FROM booking_descriptions WHERE first_night='$threeDaysFromToday' AND cancelled=0 AND checked_in=0";
} else {
	$sql = "SELECT * FROM booking_descriptions WHERE id=$bdId";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot bookings without BCR: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot get bookings without BCR");
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
	$bcrMessage = '';
	$confirmed = false;
	$lang = $bookingDescr['language'];
	if($bookingDescr['confirmed'] <> 1 OR is_null($bookingDescr['arrival_time']) OR strlen($bookingDescr['arrival_time']) < 1) {
		$bcrMessage = $dict[$lang]['BCR_MESSAGE_THREE_DAYS_NOT_CONFIRMED'];
		$subject = $dict[$lang]['BCR_MESSAGE_THREE_DAYS_NOT_CONFIRMED_SUBJECT'];
	} else {
		$confirmed = true;
		$bcrMessage = $dict[$lang]['BCR_MESSAGE_THREE_DAYS_CONFIRMED'];
		$subject = $dict[$lang]['BCR_MESSAGE_THREE_DAYS_CONFIRMED_SUBJECT'];
	}
	$descrId = $bookingDescr['id'];
	$email = $bookingDescr['email'];
	$name = $bookingDescr['name'];
	$fnight = $bookingDescr['first_night'];
	
	if($email == '') {
		echo "ERROR: cannot send email to $name $fnight beause email not specified\n";
		return;
	}

	$bcr = new BCR($bookingDescr, $location, $dict, $link);
	$result = $bcr->sendBcrMessage($subject, $bcrMessage, 'bcr.tpl');

	if($confirmed) {
		echo "Confirmed email sent to $name $email $fnight  [result: $result]\n";
	} else {
		echo "Requesting confirmation email sent to $name $email $fnight [result: $result]\n";
	}

}

?>
