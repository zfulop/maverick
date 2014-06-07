<?php

require("includes.php");

header('Location: view_bon_apetit.php');

$link = db_connect();

mysql_query("START TRANSACTION", $link);

$id = intval($_REQUEST['id']);

$sql = "DELETE FROM bon_apetit WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete restaurant in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete restaurant');
	mysql_close($link);
	return;
}

$sql = "DELETE FROM lang_text WHERE table_name='bon_apetit' and row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete restaurant in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete restaurant');
	mysql_close($link);
	return;
}

if(isset($_REQUEST['file'])) {
	unlink(BON_APETIT_IMG_DIR . '/' . $_REQUEST['file']);
}

set_message('Restaurant deleted');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
