<?php

require("includes.php");
require("bcr_common.php");

$hostel = $argv[1];
$lang = $argv[2];

$configFile = ROOT_DIR . '../includes/config/' . $hostel . '.php';
if(file_exists($configFile)) {
	require($configFile);
}
require(LANG_DIR . $lang . '.php');

$link = db_connect($hostel);

$threeDaysFromToday = date('Y/m/d', strtotime(date('Y-m-d') . ' +3 day'));
$bcrTo = array();
$sendBcr = array();
$orLangNull = '';
if($lang == 'eng') {
	$orLangNull = ' OR language IS NULL';
}
echo "3 day before arrival notification for $hostel. Getting bookings with language: $lang\n";
$sql = "SELECT * FROM booking_descriptions WHERE first_night='$threeDaysFromToday' AND cancelled=0 AND checked_in=0 AND (language='$lang'$orLangNull)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot bookings without BCR: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot get bookings without BCR");
} else {
	while($row = mysql_fetch_assoc($result)) {
		sendBcr($row, $hostel, $link);
	}
}

mysql_close($link);



function sendBcr($row, $location, $link) {
	$bcrMessage = '';
	$confirmed = false;
	if($row['confirmed'] <> 1 OR is_null($row['arrival_time']) OR strlen($row['arrival_time']) < 1) {
		$bcrMessage = BCR_MESSAGE_THREE_DAYS_NOT_CONFIRMED;
		$subject = BCR_MESSAGE_THREE_DAYS_NOT_CONFIRMED_SUBJECT;
	} else {
		$confirmed = true;
		$bcrMessage = BCR_MESSAGE_THREE_DAYS_CONFIRMED;
		$subject = BCR_MESSAGE_THREE_DAYS_CONFIRMED_SUBJECT;
	}
	$descrId = $row['id'];
	$email = $row['email'];
	$name = $row['name'];
	$fnight = $row['first_night'];
	
	$mailMessage = getBcrMessage($row, $bcrMessage, $link);

	$inlineAttachments = array(	
		'logo' => EMAIL_IMG_DIR . 'logo-' . $location . '.jpg',
		'airport' => EMAIL_IMG_DIR . 'airport.jpg',
		'bullet' => EMAIL_IMG_DIR . 'bullet.jpg',
		'map' => EMAIL_IMG_DIR . 'map-' . $location . '.jpg',
		'railwaystation' => EMAIL_IMG_DIR . 'railwaystation.jpg'
	);

	$locationName = constant('LOCATION_NAME_' . strtoupper($location));
	$result = sendMail(CONTACT_EMAIL, $locationName, 
		$email, $name, sprintf($subject, $locationName), $mailMessage, $inlineAttachments);

	if($confirmed) {
		echo "Confirmed email sent to $name $email $fnight  [result: $result]\n";
	} else {
		echo "Requesting confirmation email sent to $name $email $fnight [result: $result]\n";
	}

}

?>
