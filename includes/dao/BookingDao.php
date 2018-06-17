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

	/**
	 * Returns the list of bookings that are active (checked in) on a particular date and is booked for the room tpye.
	 * If the room type id is null the function will return all bookings for the day.
	 */
	public static function getBookingsForDay($roomTypeId, $day, $link) {
		$day = str_replace('-', '/', $day);
		$sql = "SELECT b.* FROM bookings b INNER JOIN booking_descriptions bd ON b.description_id=bd.id WHERE bd.first_night<='$day' AND bd.last_night>='$day'";
		if(!is_null($roomTypeId)) {
			$sql .= " AND b.original_room_type_id=$roomTypeId";
		}
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get bookings for date: $day. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		$bookings = array();
		while($row = mysql_fetch_assoc($result)) {
			$bookings[] = $row;
		}
		return $bookings;
	}

	/**
	 * Gets the previous bookings in the given date interval for the guestbook where booking_descriptions.cancelled = 0
	 * @param $from <= last_night
	 * @param $to   => first_night
	 */
	public static function getPreviousGuestBookings($from,$to,$link)
	{

		$from = substr(str_replace('.','/',$from),0,10);
		$to = substr(str_replace('.','/',$to),0,10);
		$from = mysql_real_escape_string(str_replace('-','/',$from));
		$to = mysql_real_escape_string(str_replace('-','/',$to));

		//itt csak a booking guest idkat gyujtjuk le, kesobb minden adatot lekerunk
		$sql ="select bgd.id as booking_guest_id
        from booking_descriptions bd 
        join booking_guest_data bgd on (bgd.booking_description_id = bd.id)
        join rooms r on (r.id = bgd.room_id)
        where bd.last_night >= '$from' and bd.first_night <= '$to'
        and bd.cancelled = 0
        ";


		$booking_desc_day_ids = array();
		$booking_guest_ids = array();
		$result = mysql_query($sql, $link);
		$guestbook_data = array();
		if(!$result) {
			trigger_error("Cannot get previous guest booking. Error: "  . mysql_error($link) . " SQL: $sql");
			return null;
		}

		//ez a datum szerinti lekerdezes
		while($row = mysql_fetch_assoc($result)) {
			$booking_guest_ids[] = $row['booking_guest_id'];
		}

		//meghatarozzuk a minimum es maximum booking guest data id kat, ezek alapjan lesz a guestbook data lekerve
		$min_bg_id = min($booking_guest_ids);
		$max_bg_id = max($booking_guest_ids);

		//levalogatjuk azokat a sorokat, amik lyukakkent szerepelnek a mostani listaban


		$sql ="select bgd.id as booking_guest_id, bd.id as booking_description_id,  (case when bgd.`name`='' then bd.name else bgd.name end) as name, 
               (case when bgd.`nationality`='' then bd.nationality else bgd.nationality end) as nationality,
               bd.first_night, bd.num_of_nights, 
               date(bd.first_night) as check_in,
               DATE(DATE_ADD(bd.last_night, INTERVAL 1 DAY)) as check_out,
               bd.last_night, bd.checked_in, bd.cancelled, bd.confirmed, bgd.room_id, coalesce(r.name,'No room') as room_name,
               bgd.id_card_number, bgd.invoice_number,
			   rc.name as changed_room_name, rc.id as changed_room_id
        from booking_descriptions bd 
        join booking_guest_data bgd on (bgd.booking_description_id = bd.id)
        left outer join rooms r on (r.id = bgd.room_id)
		left outer join bookings b on (b.description_id=bd.id and b.room_id=r.id)
		left outer join booking_room_changes brc on (brc.booking_id=b.id and brc.date_of_room_change=bd.first_night)
		left outer join rooms rc on (brc.new_room_id=rc.id)
        where bgd.id between $min_bg_id and $max_bg_id
        and bd.cancelled = 0
        order by bd.first_night, bgd.id ";

		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get guestdata for bookings. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		while($row = mysql_fetch_assoc($result)) {

			//megjegyezzuk a toltelek sorokat, amik az ID folytonossag miatt kell bekeruljenek
			if (in_array($row['booking_guest_id'],$booking_guest_ids)) $row['in_interval'] = 'Yes'; // ez egy datum intervallum szerint lekert sor
			else $row['in_interval'] = 'No';

			$guestbook_data[] = $row;
		}

		return $guestbook_data;
	}

	/**
	 * Returns the room changes for the booking description ids (if specified) and the dates (if specified). If any of the parameters are
	 * null or an empty array the filter will omit that parameter
	 * @param $bdids				The array of booking description ids for which we want the room changes. This can be a number as well if we want to search for one.
	 * @param $dateOfRoomChanges	The array of dates for which we want the room changes. Thist can be a string if we are interested only in one value.
	 */
	public static function getRoomChanges($bdIds, $dateOfRoomChanges, $link) {
		$dateClause = '';
		if(!is_null($dateOfRoomChanges)) {
			if(!is_array($dateOfRoomChanges)) {
				$dateOfRoomChanges = array($dateOfRoomChanges);
			}
			array_walk($dateOfRoomChanges, array('BookingDao', 'changeDateToSlashes'));
			$dateClause = "brc.date_of_room_change IN ('" . implode("','", $dateOfRoomChanges) . "')";
		}
		$bdidClause = '';
		if(!is_null($bdIds)) {
			if(!is_array($bdIds)) {
				$bdIds = array($bdIds);
			}
			$bdidClause = "bd.id IN (" . implode(',',$bdIds) . ")" ;
		}
		$sql = "SELECT bd.id AS booking_description_id, b.id AS booking_id, b.room_id, brc.new_room_id 
				FROM booking_descriptions bd 
				INNER JOIN bookings b ON b.description_id=bd.id
				INNER JOIN booking_room_changes brc ON (brc.booking_id=b.id)
				WHERE $bdidClause " . (strlen($bdidClause) > 0 ? ' AND ' : '') . $dateClause;
	
		logDebug("Getting room changes: $sql");
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get room changes for bookings. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		$roomChanges = array();
		while($row = mysql_fetch_assoc($result)) {
			$roomChanges[] = $row;
		}

		return $roomChanges;
	}

	public static function changeDateToSlashes(&$date) {
		$date = str_replace('-','/',$date);
	}

	/**
	 * When a BCR email is sent (one week ahead of arrivel to confirm reservation) to a guest, this method is called
	 * to record the fact that the email was sent out.
	 */
	public static function updateBcr($descrId, $email, $link) {
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
	}

	/**
	 * Retuns the booking descriptions of all the bookings that were create/modified/cancelled after the specified timestamp. 
	 * The $timestamp must be in a format: YYYY-MM-DD HH:MM:SS
	 */
	public static function getBookingsAfterCreationDate($timestamp, $link) {
		$sql = "SELECT bd.* FROM booking_descriptions bd INNER JOIN audit a ON bd.id=a.booking_description_id WHERE a.type IN ('CREATE_BOOKING','CANCEL_BOOKING','ADD_BOOKING','DELETE_BOOKING') AND a.time_of_event>='$timestamp'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get booking descriptions modified after a certain time: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			return null;
		}
		$bookings = array();
		while($row = mysql_fetch_assoc($result)) {
			$bookings[] = $row;
		}
		return $bookings;
	}

}




?>