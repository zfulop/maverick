<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

header('Location: view_cleaners.php');

$name = $_REQUEST['name'];
$login = $_REQUEST['login'];
$telephone = $_REQUEST['telephone'];

$sql = "INSERT INTO cleaners (login, name, telephone) VALUES ('$login', '$name', '$telephone')";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save cleaner in mgmt interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save cleaner');
	mysql_close($link);
	return;
}

set_message('Cleaner saved');
mysql_close($link);

?>
