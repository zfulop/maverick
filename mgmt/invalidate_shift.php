<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}

$link = db_connect();

header('Location: view_shifts.php');

$id = $_REQUEST['id'];
$valid_to = date('Y-m-d');

$sql = "UPDATE working_shift SET valid_to='$valid_to' WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot invalidate shift in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot invalidate shift');
} else {
	set_message('Shift invalidated.');
}

mysql_close($link);

?>
