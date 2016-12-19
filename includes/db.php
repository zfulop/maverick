<?php

function db_connect($dbName = null, $forceNew = false) {
	if(is_null($dbName)) {
		$dbName = $_SESSION['login_hotel'];
	}
	if(!defined('DB_' . strtoupper($dbName) . '_USERNAME')) {
		logError("Error openning connection to DB: No const defined for " . 'DB_' . strtoupper($dbName) . '_USERNAME');
		return null;
	}
	$dbUser = constant('DB_' . strtoupper($dbName) . '_USERNAME');
	$dbPwd = constant('DB_' . strtoupper($dbName) . '_PASSWORD');
	$link = mysql_connect('localhost', $dbUser, $dbPwd, $forceNew);
	mysql_select_db($dbName, $link);
	$error = mysql_error();
	if(!is_null($error)) {
		logError("Error openning connection to DB. DB name: $dbName, username: $dbUser. DB error: $error");
	}
	if(!mysql_query("SET NAMES utf8", $link)) {
		$err = mysql_error($link);
		logError("Error calling 'SET NAMES utf8' sql command: $err");
		trigger_error("Error calling 'SET NAMES utf8' sql command: $err");
	}

	return $link;
}

?>
