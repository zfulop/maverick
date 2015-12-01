<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



header('Location: view_have_fun.php');

$link = db_connect();

mysql_query("START TRANSACTION", $link);

$id = intval($_REQUEST['id']);

$sql = "DELETE FROM have_fun WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete event in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete event');
	mysql_close($link);
	return;
}

$sql = "DELETE FROM lang_text WHERE table_name='have_fun' and row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete event in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete event');
	mysql_close($link);
	return;
}

if(isset($_REQUEST['file'])) {
	unlink(HAVE_FUN_IMG_DIR . '/' . $_REQUEST['file']);
}

set_message('Event deleted');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
