<?php

$cleanerItems = null;

class ListsDao {

	/**
	 * Gets the list of cleaner items
	 * Parameters:
	 *   link            - the db connection 
	 * Return            - list of strings
	 */
	public static function getCleanerItemTypes($link) {
		global $cleanerItems;
		if(is_null($cleanerItems)) {
			$sql = "SELECT * FROM cleaner_item_types ORDER BY type";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot load rooms data. Error: " . mysql_error($link) . " (SQL: $sql)");
				return null;
			}
			logDebug("There are " . mysql_num_rows($result) . " cleaner items loaded");
			$cleanerItems = array();
			while($row = mysql_fetch_assoc($result)) {
				$cleanerItems[] = $row['type'];
			}
		}
		return $cleanerItems;
	}



}

?>