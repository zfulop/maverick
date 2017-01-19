<?php

function convertCurrency($amount, $from_code, $to_code){
	$amount = convertAmount($amount, $from_code, $to_code, date('Y-m-d'));
    return $amount;
}

function initCurrency() {
	if(isset($_REQUEST['currency'])) {
		$_SESSION['currency'] = $_REQUEST['currency'];
	}
	if(!isset($_SESSION['currency'])) {
		$_SESSION['currency'] = 'EUR';
	}
}

function getCurrency() {
	return $_SESSION['currency'];
}


?>
