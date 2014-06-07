<?php

require("includes.php");


header('Location: view_cleaners.php');

$id = intval($_REQUEST['id']);
$login = $_REQUEST['login'];


$link = db_connect();

$sql = "DELETE FROM cleaners WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete cleaner in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete cleaner');
	mysql_close($link);
	return;
}

set_message('Cleaner deleted');
mysql_close($link);

?>
