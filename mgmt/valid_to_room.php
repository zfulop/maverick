<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

header('Location: view_rooms.php');

$validTo = $_REQUEST['valid_to'];
$id = intval($_REQUEST['id']);

$sql = "UPDATE rooms SET valid_to='$validTo' WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot update valid_to room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save room');
} else {
	set_message('Room saved');
}

mysql_close($link);

?>
