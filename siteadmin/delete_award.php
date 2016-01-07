<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



header('Location: view_awards.php');

$link = db_connect();

mysql_query("START TRANSACTION", $link);

$id = intval($_REQUEST['id']);

$sql = "DELETE FROM awards WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete award in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete award');
	mysql_close($link);
	return;
}

$sql = "DELETE FROM lang_text WHERE table_name='awards' and row_id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete award in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete award');
	mysql_close($link);
	return;
}

if(isset($_REQUEST['file'])) {
	unlink(AWARDS_IMG_DIR . $_REQUEST['file']);
}

set_message('Award deleted');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
