<?php

date_default_timezone_set('Europe/Budapest');

define('LOG_DIR', '/home/maveric3/logs/');
define('JSON_DIR', '/home/maveric3/json/');

require('includes/db.php');
require('includes/db_config.php');
require('includes/logger.php');

$hostels = array('lodge','hostel');

$output = array();

set_time_limit(180);

foreach($hostels as $hostel) {
	echo "Processing $hostel\n";
	echo "\tBCR 1 week\n";
	$output['bcr 1 week ' . $hostel] = shell_exec('cd /home/maveric3/reception; php -q -c ../php.ini send_bcr_one_week.php ' . $hostel);
	echo "\tBCR 3 days\n";
	$output['bcr 3 days ' . $hostel] = shell_exec('cd /home/maveric3/reception; php -q -c ../php.ini send_bcr_3_days.php ' . $hostel);
	echo "\thowazit\n";
	$output['howazit ' . $hostel] = shell_exec('cd /home/maveric3/reception; php -q -c ../php.ini howazit_extract.php ' . $hostel);
	echo "\tsending booking summary\n";
	$output['send booking summary ' . $hostel] = shell_exec('cd /home/maveric3/reception; php -q -c ../php.ini send_booking_summary.php ' . $hostel);
	echo "\tbackup db\n";
	$output['db backup ' . $hostel] = shell_exec("cd /home/maveric3/; php -q -c php.ini backup_db.php $hostel");
	echo "\tdeleting extracted rooms data\n";
	$output['delete extracted room data ' . $hostel] = "Deleting extracted files containing rooms data\n";
	$files = glob(JSON_DIR . $hostel . '/rooms*');
	foreach($files as $file) {
		$output['delete extracted room data ' . $hostel] .= "\t$file\n";
		unlink($file);
	}
	
	
	if(intval(date('d')) == 1) {
		echo "\tFirst day of the month backup the archive db\n";
		$output['db backup archive ' . $hostel] = shell_exec('cd /home/maveric3/; php -q -c php.ini backup_db.php archive_' . $hostel);
	}

	$link = db_connect($hostel);
	deleteOutOfDateRoomChanges($link, $hostel, $output);
	moveDataToArchive($link, $hostel, $output);
	mysql_close($link);

	deleteOldBackup($hostel, $output);
}

deleteReceipt($link, $output);


foreach($output as $title => $data) {
	echo $title . "\n" . str_repeat("-", strlen($title)) . "\n" . $data . "\n\n";
}

// END OF DAILY TASKS

function deleteOldBackup($hostel, &$output) {
	$msg = '';
	$dh = opendir('/home/maveric3/db_backup/');
	$fileOneWeekOld = 'db-backup-'.$hostel.'-'.date('Ymd', strtotime(date('Y-m-d') . ' -1 week'));
	while (($file = readdir($dh)) !== false) {
		if((substr($file, 0, strlen('db-backup-'.$hostel)) == 'db-backup-'.$hostel) and $file < $fileOneWeekOld) {
			unlink('/home/maveric3/db_backup/' . $file);
			$msg = $file . "\n";
		}
	}
	closedir($dh);	
	if(strlen($msg) > 0) {
		$output["Deleting receipt"] = $msg;
	}
}


function deleteReceipt($link, &$output) {
	$msg = '';
	$dh = opendir('/home/maveric3/reception');
	while (($file = readdir($dh)) !== false) {
		if(isReceipt($file)) {
			unlink('/home/maveric3/reception/' . $file);
			$msg = $file . "\n";
		}
	}
	closedir($dh);
	if(strlen($msg) > 0) {
		$output["Deleting receipt"] = $msg;
	}
}

function isReceipt($file) {
	return (strpos($file, 'payment_receipt') !== FALSE);
}

function deleteOutOfDateRoomChanges($link, $hostel, &$output) {
	echo "\tDeleting out of date room changes\n";
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
}

function moveDataToArchive($link, $hostel, &$output) {
	echo "\tMoving data to archive db\n";
	$oneYearAgoSlash = date('Y/m/d', strtotime(date('Y-m-d') . ' -1 year'));
	$oneYearAgoDash = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 year'));
	$oneMonthAgoDash = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 month'));

	$dbs = array('lodge' => array('active' => 'maveric3_lodge', 'archive' => 'maveric3_archive_lodge'),
			'hostel' => array('active' => 'maveric3_hostel', 'archive' => 'maveric3_archive_hostel'));

	$activeSchema = $dbs[$hostel]['active'];
	$archiveSchema = $dbs[$hostel]['archive'];
	$sql = array();
	$sql[] = "INSERT INTO $archiveSchema.booking_descriptions SELECT * FROM $activeSchema.booking_descriptions WHERE last_night<'$oneYearAgoSlash'";
	$sql[] = "INSERT INTO $archiveSchema.bookings SELECT b.* FROM $activeSchema.bookings b inner join $activeSchema.booking_descriptions bd ON b.description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "INSERT INTO $archiveSchema.booking_guest_data SELECT bgd.* FROM $activeSchema. booking_guest_data bgd inner join $activeSchema.booking_descriptions bd ON bgd.booking_description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "INSERT INTO $archiveSchema.payments SELECT p.* FROM $activeSchema.payments p inner join $activeSchema.booking_descriptions bd ON p.booking_description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "INSERT INTO $archiveSchema.service_charges SELECT sc.* FROM $activeSchema.service_charges sc inner join $activeSchema.booking_descriptions bd ON sc.booking_description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "INSERT INTO $archiveSchema.booking_room_changes SELECT brc.* FROM $activeSchema.booking_room_changes brc inner join $activeSchema.bookings b on brc.booking_id=b.id inner join $activeSchema.booking_descriptions bd ON b.description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "INSERT INTO $archiveSchema.bcr SELECT bcr.* FROM $activeSchema.bcr inner join $activeSchema.booking_descriptions bd ON bcr.booking_description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "INSERT INTO $archiveSchema.audit SELECT * FROM $activeSchema.audit WHERE time_of_event<'$oneYearAgoDash'";
	$sql[] = "INSERT INTO $archiveSchema.prices_for_date SELECT * FROM $activeSchema.prices_for_date WHERE date<'$oneYearAgoSlash'";
	$sql[] = "INSERT INTO $archiveSchema.prices_for_date_history SELECT * FROM $activeSchema.prices_for_date_history WHERE date<'$oneYearAgoSlash'";
	$sql[] = "INSERT INTO $archiveSchema.cash_out SELECT * FROM $activeSchema.cash_out WHERE time_of_payment<'$oneYearAgoDash'";

	$sql[] = "DELETE brc FROM $activeSchema.booking_room_changes brc inner join $activeSchema.bookings b on brc.booking_id=b.id inner join $activeSchema.booking_descriptions  bd ON b.description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "DELETE b FROM $activeSchema.bookings b inner join $activeSchema.booking_descriptions  bd ON b.description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "DELETE bgd FROM $activeSchema.booking_guest_data bgd inner join $activeSchema.booking_descriptions  bd ON bgd.booking_description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "DELETE p FROM $activeSchema.payments p inner join $activeSchema.booking_descriptions  bd ON p.booking_description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "DELETE sc FROM $activeSchema.service_charges sc inner join $activeSchema.booking_descriptions  bd ON sc.booking_description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "DELETE bcr FROM $activeSchema.bcr inner join $activeSchema.booking_descriptions bd ON bcr.booking_description_id=bd.id WHERE bd.last_night<'$oneYearAgoSlash'";
	$sql[] = "DELETE FROM $activeSchema.booking_descriptions WHERE last_night<'$oneYearAgoSlash'";
	$sql[] = "DELETE FROM $activeSchema.audit WHERE time_of_event<'$oneYearAgoDash'";
	$sql[] = "DELETE FROM $activeSchema.prices_for_date WHERE date<'$oneYearAgoSlash'";
	$sql[] = "DELETE FROM $activeSchema.prices_for_date_history WHERE date<'$oneYearAgoSlash'";
	$sql[] = "DELETE FROM $activeSchema.cash_out WHERE time_of_payment<'$oneYearAgoDash'";
	$sql[] = "DELETE si FROM $activeSchema.syncronization_item si inner join $activeSchema.synchronizations s ON si.sync_id=s.id WHERE s.time_of_sync<'$oneMonthAgoDash'";
	$sql[] = "DELETE FROM $activeSchema.synchronizations WHERE time_of_sync<'$oneMonthAgoDash'";
	$msg = '';
	foreach($sql as $s) {
		$result = mysql_query($s, $link);
		if(!$result) {
			$msg .= " " . mysql_error($link) . " (SQL: $s) ";
		}
	}
	if(strlen($msg) < 1) $msg = 'OK';
	$output["Archiving data for: $hostel. Result:"] = $msg;
}
	
?>