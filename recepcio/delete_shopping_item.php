<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

$id = $_REQUEST['id'];

$sql = "DELETE FROM shopping_list WHERE id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot delete shopping item: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot delete shopping item');
} else {
	set_message('Shopping item deleted');
	audit(AUDIT_DELETE_SHOPPING_ITEM, print_r($_REQUEST, true), 0, 0, $link);
}

mysql_close($link);
header("Location: view_shopping_list.php");

?>
