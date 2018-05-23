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
	/**
	 * Returns all vacations for a date range. 
	 * The returned array's key is the login name of the user and the value is the list of vacation requests.
	 * The fromDate and toDate parameters should have the format of YYYY-MM-DD (or YYYY/MM/DD)
	 * Ex.:
	 * {
	 *  peter: [{id: 1, login: peter, from_date: 2018-02-03, to_date: 2018-02-05}, {id: 2, login: peter, from_date: 2018-03-01, to_date: 2018-03-03}],
	 *  erik:  [{id: 3, login: eirk, from_date: 2018-05-03, to_date: 2018-05-04}]
	 * }
	 */
	public static function getVacations($fromDate, $toDate, $link) {
		$fromDate = str_replace("/", "-", $fromDate);
		$toDate = str_replace("/", "-", $toDate);
		$sql = "SELECT * FROM vacations WHERE to_date>='$fromDate' AND from_date<='$toDate'" ;
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot get vacations in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			return nul;
		}
		$vacations = array();
		if($result) {
			while($row = mysql_fetch_assoc($result)) {
				if(!isset($vacations[$row['login']])) {
					$vacations[$row['login']] = array();
				}
				$vacations[$row['login']][] = $row;
			}
		}
		return $vacations;
	}
	
	public static function sortUsersByName($user1, $user2) {
		if($user1['name'] < $user2['name']) return -1;
		if($user2['name'] < $user1['name']) return 1;
		return 0;
	}

	public static function sortUsersByRoleName($user1, $user2) {
		if($user1['role'] < $user2['role']) return -1;
		if($user2['role'] < $user1['role']) return 1;
		if($user1['name'] < $user2['name']) return -1;
		if($user2['name'] < $user1['name']) return 1;
		return 0;
	}
	
}
?>