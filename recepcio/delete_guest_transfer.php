<?php

require("includes.php");

$link = db_connect();

$id = $_REQUEST['id'];
$destination = $_REQUEST['destination'];

$sql = "DELETE FROM guest_transfer WHERE id=$id LIMIT 1";

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot delete guest transfer: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot delete guest transfer');
} else {
	set_message('guest transfer deleted');
	audit(AUDIT_GUEST_TRANSFER_DELETED, $_REQUEST, 0, 0, $link);
}

mysql_close($link);

header("Location: view_guest_transfer.php?destination=" . urlencode($destination)); 


?>
