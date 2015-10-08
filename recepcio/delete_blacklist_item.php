<?php

require("includes.php");

$link = db_connect();

$id = $_REQUEST['id'];

$sql = "DELETE FROM blacklist WHERE id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot delete blacklist item: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot delete blacklist item');
} else {
	set_message('Blacklist item deleted');
	audit(AUDIT_DELETE_BLACKLIST_ITEM, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: view_blacklist.php");

?>
