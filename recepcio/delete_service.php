<?php

require("includes.php");

header('Location: view_services.php');

$link = db_connect();

$id = intval($_REQUEST['id']);
$sql = "DELETE FROM services WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete service in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete service');
	mysql_close($link);
	return;
}

$sql = "DELETE FROM lang_text WHERE table_name='services' and row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete services in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete services');
	mysql_close($link);
	return;
}

if(isset($_REQUEST['file'])) {
	unlink(SERVICES_IMG_DIR . $_REQUEST['file']);
}

set_message('Service deleted');
mysql_close($link);

?>
