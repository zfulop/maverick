<?php

require("includes.php");

$link = db_connect();

$id = $_REQUEST['id'];
$descr = $_REQUEST['description'];
$today = date('Y-m-d');

$sql = "UPDATE shopping_list SET description='$descr',create_date='$today' WHERE id='$id'";

if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save shopping item: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save shopping item');
} else {
	set_message('Shopping item saved');
	audit(AUDIT_SAVE_SHOPPING_ITEM, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: view_shopping_list.php");

?>
