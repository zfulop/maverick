<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

header('Location: view_vacations.php');

$login = $_REQUEST['login'];
$start_date = $_REQUEST['start_date'];
$end_date = $_REQUEST['end_date'];

$sql = "INSERT INTO vacations (login, from_date, to_date) VALUES ('$login', '$start_date', '$end_date')";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save vacation in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save vacation');
	mysql_close($link);
	return;
}

set_message('Vacation saved');
mysql_close($link);

?>
