<?php

function getBookings($roomTypeId, &$rooms, $startPeriod, $endPeriod, $startDateBookingRec = '', $endDateBookingRec = '', &$archRooms = array()) {
	$retVal = array();
	$oneDay = $startPeriod;
	if(strlen($endPeriod) < 1) {
		$endPeriod = date('Y-m-d');
	}
	while($oneDay <= $endPeriod) {
		$oneDaySlash = str_replace('-', '/', $oneDay);
		foreach(array_merge(array_values($rooms), array_values($archRooms)) as $oneRoom) {
			if(!isRoomOfType($oneRoom, $roomTypeId)) {
				continue;
			}
			foreach($oneRoom['bookings'] as $oneBooking) {
				if($oneBooking['cancelled'] == 1) {
					continue;
				}

				if(isset($oneBooking['changes'])) {
					$isThereRoomChangeForDay = false;
					foreach($oneBooking['changes'] as $oneChange) {	
						if($oneChange['date_of_room_change'] == $oneDaySlash) {
							$isThereRoomChangeForDay = true;
						}
					}
					if($isThereRoomChangeForDay)
						continue;
				}

				$cd = substr($oneBooking['creation_time'], 0, 10);
				if(($oneBooking['first_night'] <= $oneDaySlash) and ($oneBooking['last_night'] >= $oneDaySlash) and 
					((strlen($startDateBookingRec) < 1) or ($cd >= $startDateBookingRec)) and 
					((strlen($endDateBookingRec) < 1) or ($cd <= $endDateBookingRec))) {
					$retVal[] = $oneBooking;
				}
			}
			foreach($oneRoom['room_changes'] as $oneRoomChange) {
				if($oneRoomChange['cancelled'] == 1) {
					continue;
				}


				$cd = substr($oneRoomChange['creation_time'], 0, 10);
				if($oneRoomChange['date_of_room_change'] == $oneDaySlash and 
					((strlen($startDateBookingRec) < 1) or ($cd >= $startDateBookingRec)) and 
					((strlen($endDateBookingRec) < 1) or ($cd <= $endDateBookingRec))) {
					$oneRoomChange['room_change'] = true;
					$retVal[] = $oneRoomChange;
				}

			}

		}
		$oneDay = date('Y-m-d', strtotime($oneDay . ' +1 day'));
	}
	return $retVal;
}




function getAvgNumOfBedsOccupied($selectedBookings, $startPeriod, $endPeriod, $numOfBedsInRoom = null) {
	$numOfBookedBeds = array();
	$oneDay = $startPeriod;
	$numOfBookedBeds = 0;
	$cntr = 0;
	while($oneDay <= $endPeriod) {
		foreach($selectedBookings as $booking) {
			if(is_null($numOfBedsInRoom)) {
				$numOfBookedBeds += $booking['num_of_person'];
			} else {
				// set_message('adding ' . $numOfBedsInRoom);
				$numOfBookedBeds += $numOfBedsInRoom;
			}
		}
		$oneDay = date('Y-m-d', strtotime($oneDay . ' +1 day'));
		$cntr += 1;
	}
	$occupancy = $numOfBookedBeds / $cntr;
	return $occupancy;
}



?>
