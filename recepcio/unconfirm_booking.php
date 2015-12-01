<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



header('Location: ' . $_SERVER['HTTP_REFERER']);

$link = db_connect();
$descrId = intval($_REQUEST['description_id']);

$sql = "UPDATE booking_descriptions SET confirmed=0 WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot unconfirm booking: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot unconfirm booking');
} else {
	set_message('Booking confirmation removed');
}

mysql_close($link);

?>
