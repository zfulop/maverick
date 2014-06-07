<?php

require("includes.php");

$link = db_connect();

$descr = $_REQUEST['description'];
$today = date('Y-m-d');

$sql = "INSERT INTO mending_list (description, create_date) VALUES ('$descr', '$today')";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save mending item: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save mending item');
} else {
	set_message('Mending item saved');
	audit(AUDIT_ADD_MENDING_ITEM, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: view_mending_list.php");

?>
