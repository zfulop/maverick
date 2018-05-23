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

echo "Syncronizing: $location at $now\n";

// Get last syncronization

// Get bookings that were created or modified since last syncronization

// Collect the dates (combine them as possible to start-end blocks)

// extract availability for those start/end dates

// run myallocator for those dates


mysql_close($link);



?>
