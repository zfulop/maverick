<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



header('Location: ' . $_SERVER['HTTP_REFERER']);

$link = db_connect();
$direction = $_REQUEST['direction'];
$table = $_REQUEST['table'];
$id = $_REQUEST['id'];
$order = $_REQUEST['order'];

$ids = array();
$sql = "SELECT id FROM $table ORDER BY _order";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error("Cannot get data from $table");
	mysql_close();
	return;
}
while($row = mysql_fetch_assoc($result)) {
	if($direction == 'up' and $row['id'] == $id and count($ids) > 0) {
		$ids[] = $ids[count($ids) - 1];
		$ids[count($ids) - 2] = $row['id'];
		$direction = 'none';
	} else 	if($direction == 'down' and count($ids) > 0 and $ids[count($ids) - 1] == $id ) {
		$ids[] = $ids[count($ids) - 1];
		$ids[count($ids) - 2] = $row['id'];
		$direction = 'none';
	} else {
		$ids[] = $row['id'];
	}
}


$ord = 1;
foreach($ids as $id) {
	$sql = "UPDATE $table SET _order=$ord WHERE id=$id";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error(" Cannot update order data from $table: "  . mysql_error($link));
		set_error("Cannot update order data from $table");
		mysql_close();
		return;
	}


	$ord += 1;
}

set_message("Reordering done.");
mysql_close($link);

?>
