<?php

class SyncDao {


	/**
	 */
	public static function getLastSync($link) {
		$sql = "SELECT * FROM synchronizations ORDER BY time_of_sync DESC LIMIT 1";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get last syncronization data: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			return null;
		}
		$retVal = mysql_fetch_assoc($result);
		return $retVal;
	}

	public static function saveNewSync($timestamp, $bookingDescriptionIds, $link) {
		mysql_query('START TRANSACTION', $link);

		$sql = "INSERT INTO synchronizations (time_of_sync) VALUES ('$timestamp')";
		$result = mysql_query($sql, $link);
		if(!$result) {
			mysql_query('ROLLBACK', $link);
			trigger_error("Cannot save new syncronization data: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			return false;
		}
		$syncId = mysql_insert_id($link);
		foreach($bookingDescriptionIds as $oneId) {
			$sql = "INSERT INTO syncronization_item (sync_id, booking_description_id, comment) VALUES ($syncId, $oneId, null)";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot save new syncronization item: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			}
		}
		mysql_query('COMMIT', $link);
	}

}
?>