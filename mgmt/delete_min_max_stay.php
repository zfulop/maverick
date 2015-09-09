<?php

require("includes.php");

header('Location: view_min_max_stay.php');

$link = db_connect();

$id = $_REQUEST['id'];

$sql = "DELETE FROM min_max_stay WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete min_max_stay in mgmt interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error("Cannot delete min_max_stay");
	mysql_close($link);
	return;
}

set_message("min_max_stay item deleted");
mysql_close($link);

?>
