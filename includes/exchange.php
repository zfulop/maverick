<?php

if(!defined('EXCHANGE_TABLE_FILE')) {
	define('EXCHANGE_TABLE_FILE', '/home/zolika/includes/config/exchange_table.php');
}

require EXCHANGE_TABLE_FILE;

// the exchange rate table is in the exchange_table.php file. That file
// can be edited manualy, or on the site a new exchange rate can be 
// entered, and the calling script will update the php file.

// params:
//	$amount - double value o be converted
//	$sourceCurency - EUR or HUF
//	$destCurency - EUR or HUF
//	$date - Date of conversion. Format: YYYY-MM-DD
function convertAmount($amount, $sourceCurrency, $destCurrency, $dateOfConversion) {
	$rateOfConversion = getExchangeRate($sourceCurrency, $destCurrency, $dateOfConversion);
	return $amount * $rateOfConversion;
}

function getExchangeRate($sourceCurrency, $destCurrency, $dateOfConversion) {
	global $EXCHANGE_TABLE;
	if($sourceCurrency == $destCurrency) {
		return 1;
	}

	$prevDate = null;
	$prevRate = null;
	$rateOfConversion = null;
	$arr = array();
	if(isset($EXCHANGE_TABLE[$sourceCurrency][$destCurrency])) {
		$arr = $EXCHANGE_TABLE[$sourceCurrency][$destCurrency];
	} elseif(isset($EXCHANGE_TABLE[$destCurrency][$sourceCurrency])) {
		$arr = $EXCHANGE_TABLE[$destCurrency][$sourceCurrency];
	}
	foreach($arr as $date => $rate) {
		if($date < $dateOfConversion and is_null($prevDate)) {
			// If the most recent conversion rate is before the 
			// request date
			$rateOfConversion = $rate;
			break;
		}
		if($date == $dateOfConversion) {
			$rateOfConversion = $rate;
			break;
		}
		elseif($date < $dateOfConversion and !is_null($prevDate)) {
			// find the date that is closer to the dateOfConversion
			// and use that conversion
			$t = strtotime($date);
			$tc = strtotime($dateOfConversion);
			$pt = strtotime($prevDate);
			if(($t - $tc) > ($tc - $pt)) {
				$rateOfConversion = $prevRate;
			} else {
				$rateOfConversion = $rate;
			}
			break;
		}
		$prevDate = $date;
		$prevRate = $rate;
	}
	if(is_null($rateOfConversion)) {
		$rateOfConversion = $prevRate;
	}

	if(!isset($EXCHANGE_TABLE[$sourceCurrency][$destCurrency]) and !is_null($rateOfConversion)) {
		$rateOfConversion = 1 / $rateOfConversion;
	}

	if(is_null($rateOfConversion)) {
		set_error("Cannot convert from $sourceCurrency to $destCurrency on $dateOfConversion");
	}

	return $rateOfConversion;
}




//
function addExchangeRate($sourceCurrency, $destCurrency, $rate, $date) {
	global $EXCHANGE_TABLE;
	set_message("Saving exchange: 1 $sourceCurrency=$rate $destCurrency for $date");
	set_message("Saving into file: " . EXCHANGE_TABLE_FILE);
	$EXCHANGE_TABLE[$sourceCurrency][$destCurrency][$date] = $rate;
	krsort($EXCHANGE_TABLE[$sourceCurrency][$destCurrency]);
	$EXCHANGE_TABLE[$destCurrency][$sourceCurrency][$date] = 1/$rate;
	krsort($EXCHANGE_TABLE[$destCurrency][$sourceCurrency]);
	$fh = fopen(EXCHANGE_TABLE_FILE, 'w');
	fwrite($fh, "<?php\n\n");
	fwrite($fh, '$EXCHANGE_TABLE = ' . var_export($EXCHANGE_TABLE, true) . ";\n\n");
	fwrite($fh, '?' . ">\n");
	fclose($fh);
}

function getCurrencies() {
	return array('EUR','USD','GBP','HUF');
}

function getCurrencySymbol($currency) {
	global $SYMBOLS;
	return $SYMBOLS[$currency];
}

$SYMBOLS = array(
	'EUR' => '€',
	'USD' => '$',
	'GBP' => '£',
	'HUF' => 'Ft'
);

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
	if($currency == 'HUF') {
		return true;
	}
	return false;
}



?>
