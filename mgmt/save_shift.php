<?php

require("includes.php");

$link = db_connect();

header('Location: view_shifts.php');

$name = $_REQUEST['name'];
$valid_from = str_replace('/','-',$_REQUEST['valid_from']);
$valid_to = str_replace('/','-',$_REQUEST['valid_to']);
$start_time = $_REQUEST['start_time'];
$end_time = $_REQUEST['end_time'];
$duration = $_REQUEST['duration_hour'];

$sql_valid_to = 'NULL';
if(strlen($valid_to) == 10) {
	$sql_valid_to = "'$valid_to'";
}
$sql_valid_from = 'NULL';
if(strlen($valid_from) == 10) {
	$sql_valid_from = "'$valid_from'";
}

$sql = "INSERT INTO working_shift (name, valid_from, valid_to, start_time, end_time, duration_hour) VALUES ('$name', $sql_valid_from, $sql_valid_to, '$start_time', '$end_time', $duration)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save shift in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save shift');
} else {
	set_message('Shift saved.');
}

mysql_close($link);

?>
