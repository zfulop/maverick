<?php

require("includes.php");

header('Location: view_cash_register.php');


$link = db_connect();

$type = $_REQUEST['type'];
$id = $_REQUEST['id'];
$bid = $_REQUEST['bid'];
$bdid = $_REQUEST['bdid'];

$sql = "UPDATE $type SET storno=1 WHERE id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot storno $type: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Cannot storno $type");
} else {
	set_message("Success");
	audit(AUDIT_STORNO_ITEM, $_REQUEST, $bid, $bdid, $link);
}

mysql_close($link);

?>
