<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



header('Location: view_links.php');

$link = db_connect();

mysql_query("START TRANSACTION", $link);

$id = intval($_REQUEST['id']);

$sql = "DELETE FROM links WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete link in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete link');
	mysql_close($link);
	return;
}

$sql = "DELETE FROM lang_text WHERE table_name='links' and row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete link in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete link');
	mysql_close($link);
	return;
}

set_message('Link deleted');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
