<?php

require("includes.php");

$link = db_connect();
$descrId = intval($_REQUEST['description_id']);
$maint = intval($_REQUEST['maintenance']);

$sql = "UPDATE booking_descriptions SET maintenance=$maint WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot checkin booking in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot set maintenance flag on booking');
} else {
	set_message('Booking\'s maintenance flag set to ' . $maint);
	audit(AUDIT_MAINTENANCE_BOOKING, $_REQUEST, 0, $descrId, $link);
}

mysql_close($link);

?>
