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
	$amount = intval($amount);
	if($currency == 'HUF') {
		// round to the closest hundred
		$amt = strval(100 * intval($amount / 100));
		$amount = '';
		while(strlen($amt) > 0) {
			if(strlen($amount) > 0) {
				$amount = '.' . $amount;
			}
			$amount = substr($amt, max(-3, -1*strlen($amt))) . $amount;
			$amt = substr($amt, 0, max(-3, -1*strlen($amt)));
		}
	}
	if(isCurrencySymbolAppending($currency)) {
		return $amount .  "" . getCurrencySymbol($currency);
	} else {
		return getCurrencySymbol($currency) . "" . $amount;
	}
}


function isCurrencySymbolAppending($currency) {
	if(getCurrency() == 'HUF') {
		return true;
	}
	return false;
}

?>
