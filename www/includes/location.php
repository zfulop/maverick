<?php

function getLocations() {
	return array('hostel', 'lodge', 'apartments');
}

function getLocation() {
	if(isset($_REQUEST['location'])) {
		$_SESSION['location'] = $_REQUEST['location'];
	}
	if(!isset($_SESSION['location'])) {
		$_SESSION['location'] = guessLocation();
	}

	return $_SESSION['location'];
}

function guessLocation() {
	return 'hostel';
}

function getLocationName($loc = null) {
	if(is_null($loc)) {
		$loc = getLocation();
	}
	return constant('LOCATION_NAME_' . strtoupper($loc));
}



?>
