<?php

$usersInUserDao = null;

class UserDao {


	/**
	 * Gets all the users
	 * Parameters:
	 *   link            - the db connection 
	 * Return            - list of items where each item is an assoc array with attributes: 
	 *                     * id              id of the user
	 *                     * username        the username used to login
	 * 				       * name            the real name of the user
	 *                     * role            role of the user. Possible values: RECEPTION, MANAGER, ADMIN, CLEANER, CLEANER_SUPERVISOR
	 *                     * password        encripted password
	 *                     * email           email of the user
	 *                     * telephone       telephone of the user
	 */
	public static function getUsers($link) {
		global $usersInUserDao;
		if(is_null($usersInUserDao)) {
			$sql = "SELECT * FROM users";
			$result = mysql_query($sql, $link);
			if(!$result) {
				trigger_error("Cannot get users. Error: " . mysql_error($link) . " (SQL: $sql)");
				return null;
			}
			$usersInUserDao = array();
			while($row = mysql_fetch_assoc($result)) {
				$usersInUserDao[] = $row;
			}
		}
		
		return $usersInUserDao;
	}

	/**
	 * Gets the users with a specific role(s)
	 * Parameters:
	 *   roles - the array of roles that the users should be filtered for. If only 1 role is needed this parameter can be a string instead of an array
	 *   link            - the db connection 
	 * Return            - same result type as the UserDao::getUsers() function.
	 */
	public static function getUsersForRole($roles, $link) {
		if(!is_array($roles)) {
			$roles = array($roles);
		}
		$users = UserDao::getUsers($link);
		$retVal = array();
		foreach($users as $oneUser) {
			if(in_array($oneUser['role'], $roles)) {
				$retVal[] = $oneUser;
			}
		}
		return $retVal;
	}
}
?>