<?php

require("includes.php");

$link = db_connect();
$descrId = intval($_REQUEST['description_id']);

$today=date('Y/m/d');
$sql = "UPDATE booking_descriptions SET bcr_sent='$today' WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot set BCR sent in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo 'Cannot set BCR sent for booking';
} else {
	echo 'BCR set for booking';
	audit(AUDIT_SET_BCR_SENT, $_REQUEST, 0, $descrId, $link);
}

mysql_close($link);

?>
