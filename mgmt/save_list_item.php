<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


header('Location: view_lists.php');

$link = db_connect();

$type = $_REQUEST['type'];
$item_name = $_REQUEST['item_name'];
$item = $_REQUEST['item'];

$sql = "INSERT INTO $type ($item_name) VALUES ('$item')";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot insert $type in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error("Cannot create $item_name");
	mysql_close($link);
	return;
}

set_message("$type ($item) created");
mysql_close($link);

?>
