<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



header('Location: view_schedule.php');

$link = db_connect();

$id = $_REQUEST['id'];
$sql = "SELECT * FROM reception_schedule WHERE id=$id";
$result = mysql_query($sql, $link);

$sql = "DELETE FROM reception_schedule WHERE id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot delete receptionist schedule: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot delete receptionist schedule");
} else {
	set_message("Receptionist schedule deleted");
	audit(AUDIT_REMOVE_SCHEDULE, $_REQUEST, -1, -1, $link);
}

mysql_close($link);

?>
