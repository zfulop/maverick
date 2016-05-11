<?php

function gen_booking_ref() {
	$hostel = '';
	if(isset($_SESSION['login_hotel'])) {
		$hostel = $_SESSION['login_hotel'] . '-';
	}
	return uniqid('RC-' . $hostel, false);
}

?>