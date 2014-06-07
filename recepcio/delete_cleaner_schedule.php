<?php

require("includes.php");

header('Location: view_schedule.php');

$link = db_connect();

$id = $_REQUEST['id'];
$sql = "SELECT * FROM cleaning_schedule WHERE id=$id";
$result = mysql_query($sql, $link);

$sql = "DELETE FROM cleaning_schedule WHERE id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot delete cleaner schedule: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot delete cleaner schedule");
} else {
	set_message("Cleaner schedule deleted");
	audit(AUDIT_REMOVE_C_SCHEDULE, $_REQUEST, -1, -1, $link);
}

mysql_close($link);

?>
