<?php

require 'includes.php';


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$sourceCurrency = $_REQUEST['source_currency'];
$destCurrency = $_REQUEST['destination_currency'];
$date = $_REQUEST['date_of_conversion'];
$rate = $_REQUEST['rate'];

addExchangeRate($sourceCurrency, $destCurrency, $rate, $date);

header('Location: view_exchange_rates.php');

echo "Today: 100 euro = " . convertAmount(100, 'EUR', 'HUF', date('Y-m-d')) . " ft<br>\n";
echo "Today: 100 ft = " . convertAmount(100, 'HUF', 'EUR', date('Y-m-d')) . " euro<br>\n";

?>
