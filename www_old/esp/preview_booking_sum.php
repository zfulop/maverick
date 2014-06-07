<?php

require('../includes.php');
require('../../recepcio/room_booking.php');
require('dict.php');

$link = db_connect();

foreach($_REQUEST as $key => $value) {
	$_SESSION["booking_" . $key] = $value;
}

require('preview_booking_sum.inc');

mysql_close($link);

?>
