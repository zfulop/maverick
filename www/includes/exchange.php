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

function formatMoney($amount, $currency) {
	$decimalCount = 1;
	if($currency == 'HUF') {
		// round to the closest hundred
		$amout = strval(100 * intval($amount / 100));
		$decimalCount = 0;
	}
	if(isCurrencySymbolAppending($currency)) {
		return number_format($amount, $decimalCount) .  "" . getCurrencySymbol($currency);
	} else {
		return getCurrencySymbol($currency) . "" . number_format($amount, $decimalCount);
	}
}


function isCurrencySymbolAppending($currency) {
	if(getCurrency() == 'HUF') {
		return true;
	}
	return false;
}

?>
