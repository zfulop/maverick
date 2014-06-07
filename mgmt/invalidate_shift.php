<?php

require("includes.php");

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);

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
