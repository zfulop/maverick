<?php

class CleanerDao {

	/**
	 * Gets all the actions taken by cleaners for a specific date
	 * Parameters:
	 *   dateOfAction - the date of departure, format: yyyy-mm-dd
	 *   link            - the db connection 
	 * Return            - list of items where each item is an assoc array with attributes: 
	 *                     * id              id of the cleaner action
	 *                     * room_id         the id of the room where the action took place in  
	 * 				       * time_of_event   the timestamp when the action happened
	 *                     * comment         date until the room is valid (can be used)
	 *                     * type            type of event. Possible values: 
	 *                                         ENTER_ROOM                 Cleaner enters room
	 *                                         LEAVE_ROOM                 Cleaner leaves room
	 *                                         FINISH_ROOM                Cleaner finishes room
	 *                                         CONFIRM_FINISH_ROOM        Supervisor confirmes room cleaned
	 *                                         REJECT_FINISH_ROOM         Supervisor rejects room cleaned
	 *                                         ENTER_BATHROOM             Cleaner enters bathroom
	 *                                         LEAVE_BATHROOM             Cleaner leaves bathroom
	 *                                         FINISH_BATHROOM            Cleaner finishes bathroom
	 *                                         CONFIRM_FINISH_BATHROOM    Supervisor confirmes bathroom cleaned
	 *                                         REJECT_FINISH_BATHROOM     Supervisor rejects bathroom cleaned
	 *                                         NOTE                       Cleaner makes note
	 */
	public static function getCleanerActions($dateOfAction, $link) {
		// Get cleaner actions
		$nextDay = date('Y-m-d', strtotime($dateOfAction . " +1 day"));
		$sql = "SELECT * FROM cleaner_action WHERE time_of_event>'$dateOfAction' AND time_of_event<'$nextDay' ORDER BY time_of_event";
		$result = mysql_query($sql, $link);
		$actions = array();
		if(!$result) {
			trigger_error("Cannot get clear actions for date: $dateOfAction. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		while($row = mysql_fetch_assoc($result)) {
			$actions[] = $row;
		}
		
		return $actions;
	}

	/**
	 * Gets cleaner actions for a day for a particular cleaner. It also returns any CONFIRM or REJECT actions because they are executed by the supervisor, not the actual cleaner
	 * Parameters:
	 *   clenaer         - the cleaner whose actions we want
	 *   dateOfAction    - the date for which we need the actions for. Format: yyyy-mm-dd
	 *   link            - the db connection 
	 * Return            - list of items where each item is an assoc array. For details plese see CleanerDao::getCleanerActions()
	 */
	public static function getCleanerActionsForCleaner($cleaner, $dateOfAction, $link) {
		$actions = CleanerDao::getCleanerActions($dateOfAction, $link);
		$retVal = array();
		foreach($actions as $oneAction) {
			if($oneAction['cleaner'] == $cleaner or in_array($oneAction['type'], array('CONFIRM_FINISH_ROOM', 'CONFIRM_FINISH_BATHROOM', 'REJECT_FINISH_ROOM', 'REJECT_FINISH_BATHROOM'))) {
				$retVal[] = $oneAction;
			}
		}
		return $retVal;
	}

	/**
	 * Gets cleaner actions for a day for a particular cleaner and room. It also returns any CONFIRM or REJECT actions for the room and day because they are executed by the supervisor, 
	 * not the actual cleaner
	 * Parameters:
	 *   cleaner         - the cleaner whose actions we want
	 *   roomId          - the id of the room
	 *   dateOfAction    - the date for which we need the actions for. Format: yyyy-mm-dd
	 *   link            - the db connection 
	 * Return            - list of items where each item is an assoc array. For details plese see CleanerDao::getCleanerActions()
	 */
	public static function getCleanerActionsForCleanerAndRoom($cleaner, $roomId, $dateOfAction, $link) {
		$actions = CleanerDao::getCleanerActions($dateOfAction, $link);
		$retVal = array();
		foreach($actions as $oneAction) {
			if($oneAction['room_id'] == $roomId and ($oneAction['cleaner'] == $cleaner or in_array($oneAction['type'], array('CONFIRM_FINISH_ROOM', 'CONFIRM_FINISH_BATHROOM', 'REJECT_FINISH_ROOM', 'REJECT_FINISH_BATHROOM')))) {
				$retVal[] = $oneAction;
			}
		}
		return $retVal;
	}

	/**
	 * Creates a new cleaner action in the DB.
	 * Parameters:
	 *   cleaner - the cleaner who is doing the action (if action is CONFIRM or REJECT, this is the supervisor)
	 *   roomId  - the id of the room where the action took place in  
	 *   type    - type of event. Possible values: 
	 *               ENTER_ROOM                 Cleaner enters room
	 *               LEAVE_ROOM                 Cleaner leaves room
	 *               FINISH_ROOM                Cleaner finishes room
	 *               CONFIRM_FINISH_ROOM        Supervisor confirmes room cleaned
	 *               REJECT_FINISH_ROOM         Supervisor rejects room cleaned
	 *               ENTER_BATHROOM             Cleaner enters bathroom
	 *               LEAVE_BATHROOM             Cleaner leaves bathroom
	 *               FINISH_BATHROOM            Cleaner finishes bathroom
	 *               CONFIRM_FINISH_BATHROOM    Supervisor confirmes bathroom cleaned
	 *               REJECT_FINISH_BATHROOM     Supervisor rejects bathroom cleaned
	 *               NOTE                       Cleaner makes note
	 *  comment  - the comment relating to the action
	 *  link     - db conection
	 */
	public static function insertCleanerAction($cleaner, $roomId, $type, $comment, $link) {
		$now = date('Y-m-d H:i:s');
		$comment = mysql_real_escape_string($comment, $link);
		$sql = "INSERT INTO cleaner_action (cleaner, room_id, time_of_event, type, comment) VALUES ('$cleaner',$roomId, '$now', '$type', '$comment')";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot insert clear action. Error: " . mysql_error($link) . " (SQL: $sql)");
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Gets cleaner assignments for a day.
	 * Parameters:
	 *   dateOfAction    - the date for which we need the assignments for. Format: yyyy-mm-dd
	 *   link            - the db connection 
	 * Return            - list of items where each item is an assoc array with attributes: 
	 *                     * id                id of the cleaner action
	 *                     * cleaner           the cleaner doing the job
	 *                     * room_id           the id of the room where the cleaning is taking place
	 *                     * room_part         can be either ROOM or BATHROOM depending what to clean
	 *                     * date_of_cleaning  which day to clean the room on
	 *                     * comment           any note relating to the cleaning. This will show to the cleaner
	 *                     * booking_ids       coma separated list of booking ids where there is either a room change or a leave
	 */
	public static function getCleanerAssignments($dateOfAssignment, $link) {
		$sql = "SELECT * FROM cleaner_assignment WHERE date_of_cleaning='$dateOfAssignment'";
		$result = mysql_query($sql, $link);
		$assignments = array();
		if(!$result) {
			trigger_error("Cannot get clear assignments for date: $dateOfAssignment. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		while($row = mysql_fetch_assoc($result)) {
			$assignments[] = $row;
		}
		return $assignments;
	}

	/**
	 * Gets cleaner assignments for a day for a particular cleaner.
	 * Parameters:
	 *   cleaner         - the cleaner whose assignments we want
	 *   dateOfAction    - the date for which we need the assignments for. Format: yyyy-mm-dd
	 *   link            - the db connection 
	 * Return            - list of items where each item is an assoc array. For details plese see CleanerDao::getCleanerAssignments()
	 */
	public static function getCleanerAssignmentsForCleaner($cleaner, $dateOfAssignment, $link) {
		$assignments = CleanerDao::getCleanerAssignments($dateOfAssignment, $link);
		$retVal = array();
		foreach($assignments as $oneAssignment) {
			if($oneAssignment['cleaner'] == $cleaner) {
				$retVal[] = $oneAssignment;
			}
		}
		return $retVal;
	}

	/**
	 * Inserts (or replaces if exists) a cleaner assignment. Replacement happens if there is already an assignment for the day, room and room part.
	 * Parameters:
	 * Parameters:
	 *   cleaner         - the cleaner whose assignments we want
	 *   roomId          - the id of the room
	 *   roomPart        - what part of the room to clean. Can be ROOM or BATHROOM
	 *   bookingIds      - coma separated list of booking ids that the assignment is originated from (bookings where there is a room change or a departure on the day involving the room)
	 *   comment         - a comment for the assignment
	 *   link            - the db connection 
	 * Return            - true if successfull, false otherwise
	 */
	public static function replaceCleanerAssignment($cleaner, $roomId, $roomPart, $bookingIds, $comment, $link) {
		$today = date('Y-m-d');
		$sql = "DELETE FROM cleaner_assignment WHERE room_id=$roomId AND room_part='$roomPart' AND date_of_cleaning='$today'";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot delete existing clear assignments for $today, room: $roomId, roomPart: $roomPart. Error: " . mysql_error($link) . " (SQL: $sql)");
			return false;
		}
		$comment = mysql_real_escape_string($comment, $link);
		$sql = "INSERT INTO cleaner_assignment (cleaner,room_id,room_part,booking_ids,comment,date_of_cleaning) VALUES ('$cleaner',$roomId,'$roomPart','$bookingIds','$comment','$today')";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot insert new clear assignments for $today, room: $roomId, roomPart: $roomPart. Error: " . mysql_error($link) . " (SQL: $sql)");
			return false;
		}

		return  true;		
	}

}

?>