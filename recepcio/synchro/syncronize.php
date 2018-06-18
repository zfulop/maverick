<?php


$location = $argv[1];

$configFile = '../../includes/config/' . $location . '.php';
if(!file_exists($configFile)) {
	echo "invalid location parameter";
	exit;
}
require($configFile);
require('../includes.php');

$now = date("Y-m-d H:i:s");
$link = db_connect($location);

echo "Syncronizing: $location\n";
logDebug("Syncronizing: $location at $now");

// Get last syncronization
$lastSync = SyncDao::getLastSync($link);
$timestamp = $lastSync['time_of_sync'];
logDebug("Last sync time was: $timestamp");

// Get bookings that were created or modified since last syncronization
$bookings = BookingDao::getBookingsAfterCreationDate($timestamp, $link);
$bookingDescrIds = array();

// Collect the dates (combine them as possible to start-end blocks)
echo "collect dates\n";
$dateBlocks = array();
logDebug("Bookings changed since last sync:");
foreach($bookings as $booking) {
	logDebug("\t" . $booking['id'] . ": " . $booking['first_night'] . " - " . $booking['last_night']);
	if(!in_array($booking['id'], $bookingDescrIds)) {
		$bookingDescrIds[] = $booking['id'];
	}
	$bookingConsumed = false;
	foreach($dateBlocks as &$block) {
		if(blockContains($block, $booking)) {
			$block['start'] = min($block['start'], $booking['first_night']);
			$block['end'] = max($block['end'], $booking['last_night']);
			$bookingConsumed = true;
			break;
		}
	}
	if(!$bookingConsumed) {
		$dateBlocks[] = array('start' => $booking['first_night'], 'end' => $booking['last_night']);
	}
}

// extract availability for those start/end dates
echo "extracting availabilities\n";
logDebug("extracting availability for date blocks:");
foreach($dateBlocks as $block) {
	logDebug("\t" . $block['start'] . " - " . $block['end']);
	$cmd = "php -c ../../php.ini extract_availability.php $location " . str_replace("/", "-", $block['start']) . " " . str_replace("/", "-", $block['end']).
	logDebug("\t extracting availability with command: " . $cmd);
	$output = array();
	exec($cmd, $output);
	foreach($output as $line) {
		logDebug("cmd output:\t\t$line");
	}
}

// run myallocator for those dates
logDebug("running myallocator sync for date blocks:");
echo "calling myalloc\n";
foreach($dateBlocks as $block) {
	logDebug("\t" . $block['start'] . " - " . $block['end']);
	list($startYear,$startMonth,$startDay) = explode("-", str_replace("/", "-", $block['start']));
	list($endYear,$endMonth,$endDay) = explode("-", str_replace("/", "-", $block['end']));
	$cmd = "php -c ../../php.ini myallocator.php -login_hotel " . $location . 
		" -start_year " . $startYear . " -start_month " . $startMonth . " -start_day " . $startDay . 
		" -end_year " . $endYear . " -end_month " . $endMonth . " -end_day " . $endDay;
	logDebug("Running myallocator sync with command: $cmd");
	$output = array();
	exec($cmd, $output);
	foreach($output as $line) {
		logDebug("cmd output:\t\t$line");
	}
}

echo "updating syncronizations\n";
SyncDao::saveNewSync($now, $bookingDescrIds, $link);

echo "done\n\n";

mysql_close($link);


function blockContains($block, $booking) {
	if($block['start'] <= $booking['last_night'] and $block['end'] >= $booking['first_night']) {
		return true;
	}
	return false;
}

?>
