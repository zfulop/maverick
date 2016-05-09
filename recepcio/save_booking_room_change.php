<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}

$brcIds = array();
$leaveBrcId = null;
if(isset($_REQUEST['leave_room_brc'])) {
	$leaveBrcId = $_REQUEST['leave_room_brc'];
	$brcIds[] = $leaveBrcId;
}
$enterBrcId = null;
if(isset($_REQUEST['enter_room_brc'])) {
	$enterBrcId = $_REQUEST['enter_room_brc'];
	$brcIds[] = $enterBrcId ;
}
$today = $_REQUEST['today'];

header('Location: ' . $_SERVER['HTTP_REFERER']);

$link = db_connect();

$todayDash = str_replace('/','-', $today);
$yesterday = date('Y/m/d', strtotime($todayDash . ' -1 day'));

$sql = "SELECT * FROM booking_room_changes WHERE id in (" . implode(',',$brcIds) . ")";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get room change for id: $brcId. Error: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot get room change data");
	mysql_close($link);
	return;
}

$now = date('Y-m-d H:m:i');
$sqls = array();
while($row = mysql_fetch_assoc($result)) {
	if($row['date_of_room_change'] == $today and !is_null($row['enter_new_room_time'])) {
		set_error("Room change already recorded");
		mysql_close($link);
		return;
	}
	if($row['date_of_room_change'] == $yesterday and !is_null($row['leave_new_room_time'])) {
		set_error("Room change already recorded");
		mysql_close($link);
		return;
	}
	if($row['date_of_room_change'] == $today and $row['id'] == $enterBrcId) {
		$sqls[] = "UPDATE booking_room_changes SET enter_new_room_time='$now' WHERE id=" . $row['id'];
	}
	if($row['date_of_room_change'] == $yesterday and $row['id'] == $leaveBrcId) {
		$sqls[] = "UPDATE booking_room_changes SET leave_new_room_time='$now' WHERE id=" . $row['id'];
	}
}

foreach($sqls as $sql) {
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot update room change for $today. Error: " . mysql_error($link) . " (SQL: $sql)");
		set_error("Cannot update room change data");
	} else {
		set_message("room change data updated");
	}
}

mysql_close($link);

?>
