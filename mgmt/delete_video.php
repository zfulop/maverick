<?php

require("includes.php");

header('Location: view_videos.php');

$link = db_connect();

$id = intval($_REQUEST['id']);

$sql = "DELETE FROM videos WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete video in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete video');
	mysql_close($link);
	return;
}

$sql = "DELETE FROM lang_text WHERE table_name='videos' and row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete video in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete video');
	mysql_close($link);
	return;
}

set_message('Video deleted');
mysql_close($link);

?>
