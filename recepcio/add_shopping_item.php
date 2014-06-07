<?php

require("includes.php");

$link = db_connect();

$descr = $_REQUEST['description'];
$today = date('Y-m-d');

$sql = "INSERT INTO shopping_list (description, create_date) VALUES ('$descr', '$today')";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save shopping item: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save shopping item');
} else {
	set_message('Shopping item saved');
	audit(AUDIT_ADD_SHOPPING_ITEM, print_r($_REQUEST, true), 0, 0, $link);
}

mysql_close($link);
header("Location: view_shopping_list.php");

?>
