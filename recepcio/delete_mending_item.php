<?php

require("includes.php");

$link = db_connect();

$id = $_REQUEST['id'];

$sql = "DELETE FROM mending_list WHERE id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot delete mending item: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot delete mending item');
} else {
	set_message('Mending item deleted');
	audit(AUDIT_DELETE_MENDING_ITEM, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: view_mending_list.php");

?>
