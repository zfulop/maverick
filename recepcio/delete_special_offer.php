<?php

require("includes.php");

header('Location: view_special_offers.php');

$link = db_connect();

$id = intval($_REQUEST['id']);
$sql = "DELETE FROM special_offers WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete special offers in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete special offer');
	mysql_close($link);
	return;
}

$sql = "DELETE FROM lang_text WHERE table_name='special_offers' and row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete special offers in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete special offer');
	mysql_close($link);
	return;
}

set_message('Special offer deleted');
mysql_close($link);

?>
