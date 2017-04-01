<?php

$hostel = $argv[1];

$configFile = ROOT_DIR . '../includes/config/' . $hostel . '.php';
if(file_exists($configFile)) {
	require($configFile);
}

require("includes.php");
require("bcr_common.php");

$link = db_connect($hostel);

$sql = "SELECT * FROM lang_text WHERE table_name='website'";
$result = mysql_query($sql, $link);
$dict = array();
while($row = mysql_fetch_assoc($result)) {
	$dict[$row['lang']][$row['column_name']] = $row['value'];
}

$threeDaysFromToday = date('Y/m/d', strtotime(date('Y-m-d') . ' +3 day'));
$bcrTo = array();
$sendBcr = array();
echo "3 day before arrival notification for $hostel. \n";
$sql = "SELECT * FROM booking_descriptions WHERE first_night='$threeDaysFromToday' AND cancelled=0 AND checked_in=0";
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



function sendBcr($row, $location, $link, &$dict) {
	$bcrMessage = '';
	$confirmed = false;
	if($row['confirmed'] <> 1 OR is_null($row['arrival_time']) OR strlen($row['arrival_time']) < 1) {
		$bcrMessage = $dict[$row['language']]['BCR_MESSAGE_THREE_DAYS_NOT_CONFIRMED'];
		$subject = $dict[$row['language']]['BCR_MESSAGE_THREE_DAYS_NOT_CONFIRMED_SUBJECT'];
	} else {
		$confirmed = true;
		$bcrMessage = $dict[$row['language']]['BCR_MESSAGE_THREE_DAYS_CONFIRMED'];
		$subject = $dict[$row['language']]['BCR_MESSAGE_THREE_DAYS_CONFIRMED_SUBJECT'];
	}
	$descrId = $row['id'];
	$email = $row['email'];
	$name = $row['name'];
	$fnight = $row['first_night'];
	
	if($email == '') {
		return;
	}
	
	$mailMessage = getBcrMessage($row, $bcrMessage, $link, $dict, $location);

	$inlineAttachments = array(	
		'logo' => EMAIL_IMG_DIR . 'logo-' . $location . '.jpg',
		'airport' => EMAIL_IMG_DIR . 'airport.jpg',
		'bullet' => EMAIL_IMG_DIR . 'bullet.jpg',
		'map' => EMAIL_IMG_DIR . 'map-' . $location . '.jpg',
		'railwaystation' => EMAIL_IMG_DIR . 'railwaystation.jpg'
	);

	$locationName = $dict[$row['language']]['LOCATION_NAME_' . strtoupper($location)];
	$subject = str_replace('LOCATION', $locationName, $subject);
	$result = sendMail(CONTACT_EMAIL, $locationName, $email, $name, $subject, $mailMessage, $inlineAttachments);
	if($confirmed) {
		echo "Confirmed email sent to $name $email $fnight  [result: $result]\n";
	} else {
		echo "Requesting confirmation email sent to $name $email $fnight [result: $result]\n";
	}

}

?>
