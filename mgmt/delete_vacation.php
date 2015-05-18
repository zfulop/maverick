<?php

require("includes.php");

header('Location: view_vacations.php');

$link = db_connect();

$id = intval($_REQUEST['id']);

$sql = "DELETE FROM vacations WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete vacation in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete vacation');
	mysql_close($link);
	return;
}

set_message('Vacation deleted');
mysql_close($link);

?>
