<?php

require("includes.php");

header('Location: view_lists.php');

$link = db_connect();

$type = $_REQUEST['type'];
$item_name = $_REQUEST['item_name'];
$item = $_REQUEST['item'];

$sql = "DELETE FROM $type WHERE $item_name='$item'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete $type in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error("Cannot delete $item_name");
	mysql_close($link);
	return;
}

set_message("$item_name deleted");
mysql_close($link);

?>
