<?php

date_default_timezone_set('Europe/Budapest');

define('LOG_DIR', '/home/maveric3/logs/');

require('includes/db.php');
require('includes/db_config.php');
require('includes/logger.php');

$hostels = array('lodge','hostel');

// 1. Get the logs
$output = array();

foreach($hostels as $hostel) {
	echo "Processing $hostel\n";
	echo "\tBCR 1 week\n";
	$output['bcr 1 week ' . $hostel] = shell_exec('cd /home/maveric3/reception; php -c ../php.ini send_bcr_one_week.php ' . $hostel);
	echo "\tBCR 3 days\n";
	$output['bcr 3 days ' . $hostel] = shell_exec('cd /home/maveric3/reception; php -c ../php.ini send_bcr_3_days.php ' . $hostel);
	echo "\thowazit\n";
	$output['howazit ' . $hostel] = shell_exec('cd /home/maveric3/reception; php -c ../php.ini howazit_extract.php ' . $hostel);
	echo "\tsending booking summary\n";
	$output['send booking summary ' . $hostel] = shell_exec('cd /home/maveric3/reception; php -c ../php.ini send_booking_summary.php ' . $hostel);

	echo "\tdeleting out of date room changes\n";
	$link = db_connect($hostel);
	$sql = "SELECT brc.id, bd.name, bd.first_night, bd.last_night, brc.date_of_room_change FROM booking_descriptions bd inner join bookings b on bd.id=b.description_id inner join booking_room_changes brc on brc.booking_id=b.id where brc.date_of_room_change<bd.first_night or brc.date_of_room_change>bd.last_night";
	$result = mysql_query($sql, $link);
	$brcId = array();
	$msg = '';
	while($row = mysql_fetch_assoc($result)) {
		$brcId[] = $row['id'];
		$msg .= str_pad($row['name'], 30) . ' ' . $row['first_night']  . ' ' . $row['last_night'] . '    ' . $row['date_of_room_change'] . "\n";
	}
	if(count($brcId) > 0) {
		$sql = "DELETE FROM booking_room_changes WHERE id IN (" . implode(",",$brcId) . ")";
		$result = mysql_query($sql, $link);
		if(!$result) {
			$msg .= "Error: cannot delete room changes: " . mysql_error($link);
		}
		$output["Deleting room changes that are outside of the booked interval for location: $hostel"] = $msg;
	}
	mysql_close($link);
}

$msg = '';
$dh = opendir('/home/maveric3/reception');
while (($file = readdir($dh)) !== false) {
	if(isReceipt($file)) {
		unlink($file);
		$msg = $file . "\n";
	}
}
closedir($dh);
if(strlen($msg) > 0) {
	$output["Deleting receipt"] = $msg;
}

foreach($output as $title => $data) {
	echo $title . "\n" . str_repeat("-", strlen($title)) . "\n" . $data . "\n\n";
}

function isReceipt($file) {
	return (strpos($file, 'payment_receipt') !== FALSE);
}

?>