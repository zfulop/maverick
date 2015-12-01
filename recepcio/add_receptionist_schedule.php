<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



header('Location: view_schedule.php');


$link = db_connect();

$day = $_REQUEST['day'];
$ws_id = $_REQUEST['shift'];
$login = $_REQUEST['login'];

$sql = "select * from reception_schedule  where day='$day' and working_shift_id='$ws_id'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save receptionist schedule: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot save receptionist schedule");
	mysql_close($link);
	return;
}

if(mysql_num_rows($result) > 0) {
	set_error("Cannot save receptionist schedule - There is already someone assigned for the day and shift. You have to delete that entry before adding another one.");
	mysql_close($link);
	return;
}

$sql = "INSERT INTO reception_schedule (day, working_shift_id, login) VALUES ('$day', $ws_id, '$login')";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save receptionist schedule: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot save receptionist schedule");
} else {
	set_message("Receptionist schedule saved");
	audit(AUDIT_ADD_SCHEDULE, $_REQUEST, -1, -1, $link);
}

mysql_close($link);

?>
