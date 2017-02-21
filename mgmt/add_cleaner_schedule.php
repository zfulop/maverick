<?php

require("includes.php");


if(!checkLogin(SITE_MGMT)) {
	return;
}



header('Location: view_schedule.php');


$link = db_connect();

$day = $_REQUEST['day'];
$type = $_REQUEST['type'];

if($type=='normal') {
	$ws_id = $_REQUEST['shift'];
	$login = $_REQUEST['login'];

	$sql = "DELETE FROM cleaning_schedule WHERE day='$day' AND working_shift_id='$ws_id' LIMIT 1";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot delete existing cleaner schedule: " . mysql_error($link) . " (SQL: $sql)");
		set_error("Cannot delete existing cleaner schedule");
		mysql_close($link);
		return;
	}

	$sql = "INSERT INTO cleaning_schedule (day, working_shift_id, cleaner) VALUES ('$day', $ws_id, '$login')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save cleaner schedule: " . mysql_error($link) . " (SQL: $sql)");
		set_error("Cannot save cleaner schedule");
	} else {
		set_message("Cleaner schedule saved");
		audit(AUDIT_ADD_C_SCHEDULE, $_REQUEST, -1, -1, $link);
	}
} elseif($type == 'simple') {
	$sql = "select * from working_shift where shift_type='cleaner' and highlighted=1";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot save cleaner schedule: " . mysql_error($link) . " (SQL: $sql)");
		set_error("Cannot save cleaner schedule");
		mysql_close($link);
		return;
	}
	$sqls = array();
	while($row = mysql_fetch_assoc($result)) {
		$ws_id = $row['id'];
		$login = $_REQUEST[$ws_id];
		$sqls[] = "DELETE FROM cleaning_schedule WHERE day='$day' AND working_shift_id=$ws_id";
		$sqls[] = "INSERT INTO cleaning_schedule (day, working_shift_id, cleaner) VALUES ('$day', $ws_id, '$login')";
	}
	$errorCnt = 0;
	foreach($sqls as $sql) {
		$result = mysql_query($sql, $link);
		if(!$result) {
			$errorCnt += 1;
			trigger_error("Cannot save cleaner schedule: " . mysql_error($link) . " (SQL: $sql)");
		}
	}
	if($errorCnt > 0) {
		set_error("Cannot save cleaner schedule. There were $errorCnt errors.");
	} else {
		audit(AUDIT_ADD_C_SCHEDULE, $_REQUEST, -1, -1, $link);
		set_message("Cleaner schedules were saved.");
	}
}

mysql_close($link);

?>
