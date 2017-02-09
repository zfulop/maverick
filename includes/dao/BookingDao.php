<?php 

class BookingDao {

	/**
	 * Returns the list of booking structures. The return value is an array where the key is the booking id and the value is the booking structure
	 */
	public static function getBookings($bookingIds, $link) {
		$bids = '';
		if(is_array($bookingIds)) {
			$bids = implode(",", $bookingIds);
		} elseif(strlen($bookingIds) > 0) {
			$bids = $bookingIds;
		} else {
			return array();
		}
		$sql = "SELECT * FROM bookings WHERE id in ($bids)";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get bookings for ids: $bids. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		$retVal = array();
		while($row = mysql_fetch_assoc($result)) {
			$retVal[$row['id']] = $row;
		}
		return $retVal;
	}


	/**
	 * Returns the list of booking guest data structures
	 */
	public static function getBookingGuestData($bookingDescriptionIds, $link) {
		$bdids = '';
		if(is_array($bookingDescriptionIds)) {
			$bdids = implode(",", $bookingDescriptionIds);
		} elseif(strlen($bookingDescriptionIds) > 0) {
			$bdids = $bookingDescriptionIds;
		} else {
			return array();
		}
		$sql = "SELECT * FROM booking_guest_data WHERE booking_description_id in ($bdids)";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get bookings for ids: $bdids. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		$retVal = array();
		while($row = mysql_fetch_assoc($result)) {
			$retVal[] = $row;
		}
		return $retVal;
	}


	/*
	 * Gets the bokings where people are leaving on the date of departure.
	 * Parameters:
	 *   dateOfDeparture - the date of departure, format: yyyy-mm-dd
	 *   link            - the db connection 
	 * Return            - list of assoc array with attributes: 
	 *                     * bdid                     booking description id that that booking belongs to
	 *                     * bid                      booking id
	 *                     * name                     name on the booking
	 *                     * room_id                  id of the room where the booking is in
	 *                     * extra_beds               the number of extra beds of the booking (applicable for bookings to PRIVATE  room types only)
	 *                     * original_room_type_id    The room type id that the booking was made in
	 *                     * num_of_person            The number of person that booking is for (the extra beds are not included in this number)
	 *                     * new_room_id              If there was a room change, guest will leave from this room. If there was no room change this value will be null
	 *                     * checked_in               1 if the guest is still checked in, 0 if the guest already left
	 */
	public static function getLeavingBookings($dateOfDeparture, $link) {
		$today = date('Y/m/d', strtotime($dateOfDeparture));
		$yesterday = date('Y/m/d', strtotime($dateOfDeparture . ' -1 day'));
		$sql = "SELECT bd.id as bdid, bd.name, b.id as bid, b.room_id, b.extra_beds, b.original_room_type_id, b.num_of_person, brc.new_room_id, bd.checked_in " . 
				" FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id " .
				" LEFT OUTER JOIN booking_room_changes brc ON (b.id=brc.booking_id AND brc.date_of_room_change='$yesterday') " .
				" WHERE bd.cancelled=0 AND bd.maintenance=0 AND bd.last_night='$yesterday'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get departures for date: $today. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		logDebug("There are " . mysql_num_rows($result) . " departures on $today");
		$leaving = array();
		while($row = mysql_fetch_assoc($result)) {
			$leaving[] = $row;
		}
		return $leaving;
	}
	/**
	 * Returns the bookings where there is a room change on the specified day.
	 */
	public static function getRoomChangeBookings($dateOfChange, $link) {
		$today = date('Y/m/d', strtotime($dateOfChange));
		$yesterday = date('Y/m/d', strtotime($dateOfChange . ' -1 day'));
		$sql = "SELECT bd.id as bdid, bd.name, b.id as bid, b.room_id, b.extra_beds, b.original_room_type_id, b.num_of_person, brcy.new_room_id AS yesterday_new_room_id, brct.new_room_id AS today_new_room_id, brcy.leave_new_room_time AS left_room_time, brct.enter_new_room_time AS enter_room_time " .
				" FROM booking_descriptions bd INNER JOIN bookings b ON bd.id=b.description_id " .
				" LEFT OUTER JOIN booking_room_changes brcy ON (b.id=brcy.booking_id AND brcy.date_of_room_change='$yesterday') " . 
				" LEFT OUTER JOIN booking_room_changes brct ON (b.id=brct.booking_id AND brct.date_of_room_change='$today') " . 
				" INNER JOIN room_types rt ON b.original_room_type_id=rt.id " .
				" WHERE bd.first_night<='$yesterday' AND bd.last_night>='$today' AND brcy.new_room_id<>brct.new_room_id";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get room changes for date: $today. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		logDebug("There are " . mysql_num_rows($result) . " room changes on $today");
		$rcs = array();
		while($row = mysql_fetch_assoc($result)) {
			if($row['room_id'] != $row['yesterday_new_room_id'] or $row['room_id'] != $row['today_new_room_id']) {
				$rcs[] = $row;
			}
		}
		return $rcs;
	}
	
}


?>