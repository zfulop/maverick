<?php

require("includes.php");

$link = db_connect();

$id = $_REQUEST['id'];
$descr = $_REQUEST['description'];
$today = date('Y-m-d');
$color = $_REQUEST['color'];
$bgcolor = $_REQUEST['bgcolor'];
$type = $_REQUEST['type'];
$dueDate = $_REQUEST['due_date'];

$sql = "UPDATE mending_list SET description='$descr',create_date='$today',color='$color',bgcolor='$bgcolor',type='$type',due_date='$dueDate' WHERE id='$id'";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save mending item: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save mending item');
} else {
	set_message('Mending item saved');
	audit(AUDIT_SAVE_MENDING_ITEM, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: view_mending_list.php");

?>
