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
$_SESSION['login_user'] = 'bcr';

echo "BCR Sending - 7 day before arrival notification for $hostel. Getting bookings with language: $lang\n";

$link = db_connect($hostel);

$oneWeekFromToday = date('Y/m/d', strtotime(date('Y-m-d') . ' +1 week'));
$bcrTo = array();
$sendBcr = array();
$orLangNull = '';
if($lang == 'eng') {
	$orLangNull = ' OR language IS NULL';
}
$sql = "SELECT * FROM booking_descriptions WHERE first_night<='$oneWeekFromToday' AND cancelled=0 AND confirmed=0 AND checked_in=0 AND bcr_sent IS NULL AND (language='$lang'$orLangNull)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot bookings without BCR: " . mysql_error($link) . " (SQL: $sql)");
	echo "Cannot get bookings without BCR\n";
} else {
	while($row = mysql_fetch_assoc($result)) {
		sendBcr($row, $hostel, $link);
	}
}

mysql_close($link);


function sendBcr($row, $location, $link) {
	$descrId = $row['id'];
	$email = $row['email'];
	$name = $row['name'];
	$fnight = $row['first_night'];
	
	if($email == '') {
		return;
	}
	
	$mailMessage = getBcrMessage($row, BCR_MESSAGE_ONE_WEEK, $link);
	$inlineAttachments = array(	
		'logo' => EMAIL_IMG_DIR . 'logo-' . $location . '.jpg',
		'airport' => EMAIL_IMG_DIR . 'airport.jpg',
		'bullet' => EMAIL_IMG_DIR . 'bullet.jpg',
		'map' => EMAIL_IMG_DIR . 'map-' . $location . '.jpg',
		'railwaystation' => EMAIL_IMG_DIR . 'railwaystation.jpg'
	);

	$locationName = constant('LOCATION_NAME_' . strtoupper($location));
	$subject = str_replace('LOCATION', $locationName, BCR_MESSAGE_ONE_WEEK_SUBJECT);
	
	$result = sendMail(CONTACT_EMAIL, $locationName, 
		$email, $name, sprintf(BCR_MESSAGE_ONE_WEEK_SUBJECT, $locationName), $mailMessage, $inlineAttachments);

	echo "BCR Email sent to $name $email $fnight [result: $result]";
		
	mysql_query('START TRANSACTION', $link);

	$today=date('Y/m/d');
	$sql = "UPDATE booking_descriptions SET bcr_sent='$today' WHERE id=$descrId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot set BCR sent in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo "Cannot set BCR sent for booking when sending BCR\n";
		mysql_query("rollback", $link);
		return;
	}

	$sql = "DELETE FROM bcr WHERE booking_description_id=$descrId";
	mysql_query($sql, $link);

	$today = date('Y-m-d');
	$sql = "INSERT INTO bcr (booking_description_id, mail_sent, email) VALUES ($descrId, '$today', '$email')";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot set BCR sent in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo "Cannot create BCR record when sending BCR\n";
		mysql_query("rollback", $link);
		return;
	}

	mysql_query('COMMIT', $link);

	audit(AUDIT_BCR_SENT, array('hostel' => $location, 'lang' => $row['language']), 0, $descrId, $link);
}


?>
