<?php

$rooms = null;
$roomTypes = null;

class RoomDao {

	/**
	 * Gets the bookings where people are leaving on the date of departure.
	 * If there is an error with loading the data from the DB an error will be logged and the function will return null.
	 * Parameters:
	 *   dateOfDeparture - the date of departure, format: yyyy-mm-dd
	 *   link            - the db connection 
	 * Return            - assoc array where the key is the room id and the value is an assoc array with attributes: 
	 *                     * id                     id of the room
	 *                     * room_type_id           id of the room type
	 * 				       * name                   name of the room
	 *                     * valid_from             date from which the room is valid (can be used)
	 *                     * valid_to               date until the room is valid (can be used)
	 *                     * room_types             The additional room types that the room can be. This is an assoc array with key: room type id, value: room type name
	 */
	public static function getRooms($link) {
		global $rooms;
		if(is_null($rooms)) {
			$sql = "SELECT * FROM rooms";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot load rooms data. Error: " . mysql_error($link) . " (SQL: $sql)");
				return null;
			}
			logDebug("There are " . mysql_num_rows($result) . " rooms loaded");
			$rooms = array();
			while($row = mysql_fetch_assoc($result)) {
				$rooms[$row['id']] = $row;
			}
			
			$sql = "SELECT rtrt.*, rt.name AS room_type_name FROM rooms_to_room_types rtrt INNER JOIN room_types rt ON (rtrt.room_type_id=rt.id)";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot get additional room types for rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
				return null;
			}
			while($row = mysql_fetch_assoc($result)) {
				$theRoom = $rooms[$row['room_id']];
				$theRoom['room_types'][$row['room_type_id']] = $row['room_type_name'];
				$rooms[$row['room_id']] = $theRoom;
			}

		}
		return $rooms;
	}


	/**
	 * Gets the room data for a specific room id. See RoomDao::getRooms() for the description of the array returned.
	 */ 
	public static function getRoom($roomId, $link) {
		$rooms = RoomDao::getRooms($link);
		return $rooms[$roomId];
	}

	/**
	 * Returns the room types in an associative array where the key is the room type id and the value is an assoc array with attributes:
	 *  id                    id of the room type
	 *  price_per_bed         price for bed in the room. Applicable for DORM room types
	 *  price_per_room        price for the room. Applicable for PRIVATE and APARTMENT room types. For APARTMENT room types this amount is when 2 guest are occupying the room
	 *  surcharge_per_bed     Applicable for APARTMENT room types. The price of the room is calculated by using the price_per_room and any additional quests over 2 is calculated with this value
	 *  type                  Can be DORM, PRIVATE or APARTMENT
	 *  num_of_beds           The number of beds in the room
	 *  name                  Language specific name of the room
	 *  description           Language specific description of the room
	 *  short_description     Language specific short description of the room
	 *  size                  Language specific size information of the room
	 *  location              Language specific location information of the room
	 *  bathroom              Language specific bathroom information of the room
	 */
	public static function getRoomTypes($lang, $link) {
		$sql = "SELECT rt.id, rt.name AS rt_name, rt.price_per_bed, rt.price_per_room, rt.surcharge_per_bed, rt.type, rt.num_of_beds, lt1.value AS name, lt2.value AS description, " .
			"lt3.value AS short_description, lt4.value AS size, lt5.value AS location, lt6.value AS bathroom, rt._order, 0 AS num_of_beds_avail FROM room_types rt " . 
			"INNER JOIN lang_text lt1 ON (lt1.table_name='room_types' AND lt1.column_name='name' AND lt1.row_id=rt.id AND lt1.lang='$lang') " . 
			"INNER JOIN lang_text lt2 ON (lt2.table_name='room_types' AND lt2.column_name='description' AND lt2.row_id=rt.id AND lt2.lang='$lang') " . 
			"LEFT OUTER JOIN lang_text lt3 ON (lt3.table_name='room_types' AND lt3.column_name='short_description' AND lt3.row_id=rt.id AND lt3.lang='$lang') " .
			"LEFT OUTER JOIN lang_text lt4 ON (lt4.table_name='room_types' AND lt4.column_name='size' AND lt4.row_id=rt.id AND lt4.lang='$lang') " .
			"LEFT OUTER JOIN lang_text lt5 ON (lt5.table_name='room_types' AND lt5.column_name='location' AND lt5.row_id=rt.id AND lt5.lang='$lang') " .
			"LEFT OUTER JOIN lang_text lt6 ON (lt6.table_name='room_types' AND lt6.column_name='bathroom' AND lt6.row_id=rt.id AND lt6.lang='$lang') " .
			"ORDER BY rt._order";

		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot load rooms types data. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		$roomTypesData = array();
		while($row = mysql_fetch_assoc($result)) {
			$roomTypesData[$row['id']] = $row;
		}

		return $roomTypesData;
	}

	/**
	 * Returns only the room types that have rooms associated with it.
	 */
	public static function getRoomTypesWithRooms($lang, $link) {
		$roomTypes = RoomDao::getRoomTypes($lang, $link);
		$today = date('Y/m/d');
		$sql = "SELECT id FROM room_types rt WHERE rt.id NOT IN (SELECT DISTINCT r.room_type_id FROM rooms r WHERE r.valid_from<='$today' AND r.valid_to>='$today' UNION SELECT DISTINCT rtrt.room_type_id FROM rooms_to_room_types rtrt)";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot load rooms types that has not rooms. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		while($row = mysql_fetch_assoc($result)) {
			unset($roomTypes[$row['id']]);
		}
		return $roomTypes;
	}
	
	/**
	 * Returns the one room type that is specified in the 1st parameter by the id. The return value is an associative array. For the keys please check the documentation of the
	 * getRoomTypes function
	 */
	public static function getRoomType($roomTypeId, $lang, $link) {
		$roomTypes = RoomDao::getRoomTypes($lang, $link);
		return $roomTypes[$roomTypeId];
	}

	/**
	 * Gets the room images that are saved in the system. 
	 * Parameters
	 *  lang                  can be a single value or an array. If its an array the description attribute of each image will as many elements as this array.
	 * Returns the room types in an associative array where the key is the room type id and the value is an assoc array with attributes:
	 *  id                    id of the room images
	 *  filename              the name of the original file containing the image data
	 *  medium                the name of the file containing the resized image data (medium size)
	 *  thumb                 the name of the file containing the resized image data (thumbnail)
	 *  room_types            the array of ids of the room_type that the image is associated with
	 *  width                 the width of the original image
	 *  height                the height of the original image
	 *  default               if the image is the default image for the room type this is 1 otherwise 0. The default image should be displayed 1st for the room type
	 *  _order                the order of the image for the room type
	 *  description           array of key-value pairs where the key is the language code and the value is the description for the image in the specified language
	 *  
	 */
	public static function getRoomImages($lang, $link) {
		if(!is_array($lang)) {
			$lang = array($lang);
		}
		$sql = "SELECT ri.*, lt.value AS description, lt.lang FROM room_images ri LEFT OUTER JOIN lang_text lt ON (lt.table_name='room_images' AND lt.column_name='description' AND lt.row_id=ri.id AND lt.lang IN ('" . implode("','", $lang) . "')) ORDER BY ri._order";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot load rooms images. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}		
		$roomImages = array();
		while($row = mysql_fetch_assoc($result)) {
			$descr = $row['description'];
			$lang = $row['lang'];
			if(!isset($roomImages[$row['id']])) {
				$roomImages[$row['id']] = $row;
				$roomImages[$row['id']]['description'] = array();
				$roomImages[$row['id']]['room_types'] = array();
				$roomImages[$row['id']]['default_for_room_types'] = array();
			}
			$roomImages[$row['id']]['description'][$lang] = $descr;
		}
		logDebug("RoomDao::getRoomImages() - There are " . count($roomImages) . " room images from db.");
		
		$sql = "SELECT * FROM room_images_room_types";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot load rooms images. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		while($row = mysql_fetch_assoc($result)) {
			if(!isset($roomImages[$row['room_image_id']])) {
				continue;
			}
			$roomImages[$row['room_image_id']]['room_types'][] = $row['room_type_id'];
			if($row['default_img'] == 1) {
				logDebug("RoomDao::getRoomImages() - for room type " . $row['room_type_id'] . " the default image is " . $row['room_image_id']);
				$roomImages[$row['room_image_id']]['default_for_room_types'][] = $row['room_type_id'];
			}
		}

		return $roomImages;
	}

	/**
	 * Returns an array of room type ids that are the selected highlighted room types
	 */
	public static function getRoomHighlights($link) {
		$sql = "SELECT room_type_id FROM room_type_highlight";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot load room highlights. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		$retVal = array();
		for($i = 0; $i < mysql_num_rows($result); $i++) {
			$retVal[] = mysql_result($result, $i);
		}
		return $retVal;
	}
	

	public static function saveRoomHighlights($roomTypeIds, $link) {
		$sql = "DELETE FROM room_type_highlight";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot delete room highlights. Error: " . mysql_error($link) . " (SQL: $sql)");
			return null;
		}
		if(count($roomTypeIds) > 0) {
			$sql = "INSERT INTO room_type_highlight (room_type_id) VALUES (" . implode("),(", $roomTypeIds) . ")";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot create new room highlights. Error: " . mysql_error($link) . " (SQL: $sql)");
				return null;
			}
		}
		return true;
	}
	

}

?>