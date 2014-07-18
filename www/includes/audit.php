<?php

define('AUDIT_CREATE_BOOKING', 'CREATE_BOOKING');

function audit($type, $data, $bookingId, $bookingDescriptionId, $link, $login = 'WWW') {
	$time = date('Y-m-d H:i:s');
	if(is_null($bookingId)) {
		$bookingId = 'NULL';
	}
	$sql = "INSERT INTO audit (time_of_event, type, booking_id, booking_description_id, data, login) VALUES ('$time', '$type', '$bookingId', '$bookingDescriptionId', '" . mysql_real_escape_string(print_r($data, true), $link) . "', '$login')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save audit log: " . mysql_error($link) . " (SQL: $sql)");
	}
}

?>
