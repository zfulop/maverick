<?php

function db_connect($dbName, $forceNew = false) {
	$link = mysql_connect(constant("DB_HOST_" . strtoupper($dbName)), constant("DB_USER_" . strtoupper($dbName)), constant("DB_PASSWORD_" . strtoupper($dbName)), $forceNew);
	if(!$link) {
		trigger_error("Cannot connect to DB: " . mysql_error());
	}
	mysql_select_db(constant("DB_NAME_" . strtoupper($dbName)), $link);
	if(!mysql_query("SET NAMES utf8", $link))
		trigger_error("Error calling 'SET NAMES utf8' sql command: " . mysql_error($link));

	return $link;
}

?>
