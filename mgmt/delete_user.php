<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}




header('Location: view_users.php');

$id = intval($_REQUEST['id']);


$link = db_connect();

$sql = "DELETE FROM users WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete user in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete user');
	mysql_close($link);
	return;
}

set_message('User deleted');
mysql_close($link);

?>
