<?php

session_start();

foreach($_SESSION as $key => $value) {
	if(substr($key, 0, 8) == 'booking_')
		unset($_SESSION[$key]);
}

header("Location: booking_prices_and_special_offers.php");
return;

?>
