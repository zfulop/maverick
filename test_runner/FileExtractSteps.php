<?php

function givenThereIsNoAvailabilityExtracted($table) {
	echo "Deleting availability files from " . JSON_DIR . "teszt_hostel/\n";
	$files = glob(JSON_DIR . 'teszt_hostel/avail_*');
	foreach($files as $file) {
		echo "\t$file\n";
		unlink($file);
	}
}

function givenThereIsNoRoomInfoExtracted($table) {
	echo "Deleting rooms files from " . JSON_DIR . "teszt_hostel/\n";
	$files = glob(JSON_DIR . 'teszt_hostel/rooms_*');
	foreach($files as $file) {
		echo "\t$file\n";
		unlink($file);
	}
}

function whenIExtractTheAvailabilityIntoAFile($table) {
	foreach($table['rows'] as $row) {
		$startDate = $row['from'];
		$endDate = $row['to'];

		$cmd = "cd /home/maveric3/dev/reception/synchro/; php -c /home/maveric3/php.ini extract_availability.php teszt_hostel $startDate $endDate";
		echo "Executing command to extract availability: $cmd\n";
		$output = shell_exec("cd /home/maveric3/reception/synchro; $cmd");
		$output = str_replace("\n", "\n\t>\t", $output);
		echo "program output: \n\t>\t$output\n";
	}
}

function thenTheFollowingBookingsWillBeSavedInTheFile($table) {
	$expectedArray = array();
	$dates = array();
	foreach($table['rows'] as $row) {
		$expectedArray[] = array('room_name' => $row['room name'], 'type' => $row['type'], 'num_of_person' => $row['number of person'], 'date' => $row['date']);
		if(!in_array($row['date'], $dates)) {
			echo "Will check bookings for date: " . $row['date'] . "\n";
			$dates[] = $row['date'];
		}
	}
	$actualArray = array();
	foreach($dates as $oneDate) {
		$file = JSON_DIR . 'teszt_hostel' . '/avail_' . $oneDate . '.json';
		$json = file_get_contents($file);
		$data = json_decode($json, true);
		foreach($data as $rId => $bookingsForRoom) {
			foreach($bookingsForRoom as $oneBooking) {
				$actualArray[] = array('room_name' => $oneBooking['room_name'], 'type' => $oneBooking['booking_type'], 'num_of_person' => $oneBooking['num_of_person'], 'date' => $oneDate);
			}
		}
	}
	
	compareList($expectedArray, $actualArray, 'compareExtractedBooking');
}

function compareExtractedBooking($booking1, $booking2) {
	if($booking1['room_name'] > $booking2['room_name']) { return 1; }
	if($booking1['room_name'] < $booking2['room_name']) { return -1; }
	if($booking1['date'] > $booking2['date']) { return 2; }
	if($booking1['date'] < $booking2['date']) { return -2; }
	if($booking1['type'] > $booking2['type']) { return 3; }
	if($booking1['type'] < $booking2['type']) { return -3; }
	if($booking1['num_of_person'] > $booking2['num_of_person']) { return 4; }
	if($booking1['num_of_person'] < $booking2['num_of_person']) { return -4; }
	return 0;
}

?>