<?php

require '/var/userdata/web/mavericklodges.com/website/recepcio/includes/exchange_table.php';
//require '../../recepcio/includes/exchange_table.php';

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
	$arr = null;
	if(isset($EXCHANGE_TABLE[$sourceCurrency][$destCurrency])) {
		$arr = $EXCHANGE_TABLE[$sourceCurrency][$destCurrency];
	} else {
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

	if(!isset($EXCHANGE_TABLE[$sourceCurrency][$destCurrency])) {
		$rateOfConversion = 1 / $rateOfConversion;
	}

	return $rateOfConversion;
}




//
function addExchangeRate($sourceCurrency, $destCurrency, $rate, $date) {
	global $EXCHANGE_TABLE;
	$EXCHANGE_TABLE[$sourceCurrency][$destCurrency][$date] = $rate;
	krsort($EXCHANGE_TABLE[$sourceCurrency][$destCurrency]);
	$EXCHANGE_TABLE[$destCurrency][$sourceCurrency][$date] = 1/$rate;
	krsort($EXCHANGE_TABLE[$destCurrency][$sourceCurrency]);
	$fh = fopen(EXCHANGE_TABLE_FILE, 'w');
	fwrite($fh, "<?php\n\n");
	fwrite($fh, '$EXCHANGE_TABLE = ' . var_export($EXCHANGE_TABLE, true) . ";\n\n");
	fwrite($fh, '?' . ">\n");
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




?>
