<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

$descr = $_REQUEST['description'];
$color = $_REQUEST['color'];
$bgcolor = $_REQUEST['bgcolor'];
$today = date('Y-m-d');
$type = $_REQUEST['type'];
$dueDate = $_REQUEST['due_date'];


$sql = "UPDATE mending_list SET priority=priority+1";
if(mysql_query($sql, $link)) {
	trigger_error("Cannot update priority of previous mending items: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot update priority of previous mending items');
}

$sql = "INSERT INTO mending_list (description, create_date, priority,color,bgcolor,type,due_date) VALUES ('$descr', '$today', 1,'$color','$bgcolor','$type','$dueDate')";
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
