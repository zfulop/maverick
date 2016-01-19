<?php

require("includes.php");


$hostel = $argv[1];

$configFile = '../includes/config/' . $hostel . '.php';
if(file_exists($configFile)) {
	require($configFile);
}

$link = db_connect($hostel);

$yesterday = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 day'));
$sql = "SELECT * FROM booking_descriptions WHERE create_time>='$yesterday 00:00:00' and create_time<='$yesterday 23:59:59'";

echo "Getting bookings that were created on $yesterday\n";
$content = "Getting bookings/cancellations for " . LOCATION . " and date: $yesterday\n\n";

$result = mysql_query($sql, $link);
if(!$result) {
	echo "ERROR: " . mysql_error($link) . " (SQL: $sql)\n";
	mysql_close($link);
	return;
}

$numOfBookings = mysql_num_rows($result);

$content .= "Number of bookings: $numOfBookings\n"


$sql = "SELECT * FROM audit WHERE type='CANCEL_BOOKING' AND time_of_event>='$yesterday 00:00:00' and time_of_event<='$yesterday 23:59:59'";
$result = mysql_query($sql, $link);
if(!$result) {
	echo "ERROR: " . mysql_error($link) . " (SQL: $sql)\n";
	mysql_close($link);
	return;
}
$recCancel = 0;
$guestCancel = 0;
$noshowCancel = 0;
while($row = mysql_fetch_assoc($result)) {
	if(strstr($row['data'],'[type] => no_show')) {
		$noshowCancel += 1;
	} elseif(strstr($row['data'],'[type] => reception')) {
		$recCancel += 1;
	} elseif(strstr($row['data'],'[type] => guest')) {
		$guestCancel += 1;
	}
}

$content .= "Number of reception cancel: $recCancel\n"
$content .= "Number of guest cancel: $guestCancel\n"
$content .= "Number of noshow: $noshowCancel\n"

mysql_close($link);

echo "Sending mail with content: $content\n";
sendMail(CONTACT_EMAIL, LOCATION, 'zfulop@zolilla.com', 'FS', LOCATION . " - Daily stats", $content);

?>
