<?php

require("includes.php");

$link = db_connect();

header('Location: view_rooms.php');

$id = intval($_REQUEST['id']);
$name = $_REQUEST['name'];
$type = intval($_REQUEST['type']);
$validFrom = $_REQUEST['valid_from'];
$validTo = $_REQUEST['valid_to'];
if(strlen($validTo) != 10)
	$validTo = '2099/12/31';

mysql_query("START TRANSACTION", $link);


if($id < 1) {
	$sql = "INSERT INTO rooms (name, valid_from, valid_to, room_type_id) VALUES ('$name', '$validFrom', '$validTo', $type)";
} else {
	$sql = "UPDATE rooms SET name='$name', valid_from='$validFrom', valid_to='$validTo', room_type_id=$type WHERE id=$id";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new room');
	mysql_close($link);
	return;
}

set_message('Room saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
