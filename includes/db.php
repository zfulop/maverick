<?php

function db_connect($dbName = null, $forceNew = false) {
	if(is_null($dbName)) {
		$dbName = $_SESSION['login_hotel'];
	}
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, $forceNew);
	mysql_select_db($dbName, $link);
	if(!mysql_query("SET NAMES utf8", $link))
		trigger_error("Error calling 'SET NAMES utf8' sql command: " . mysql_error($link));

	//if(!mysql_query("SET CHARACTER SET utf8", $link))
	//	trigger_error("Cannot calling 'SET CHARACTER SET utf8' command: " . mysql_error($link));

	return $link;
}

?>
