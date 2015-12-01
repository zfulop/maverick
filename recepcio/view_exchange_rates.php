<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->


EOT;

$currencyOptions = '';
foreach(getCurrencies() as $curr) {
	$currencyOptions .= "   <option value=\"$curr\">$curr</option>\n";
}


html_start("Exchange Rates", $extraHeader);

$currencies = getCurrencies();
$numOfCurrencies = count($currencies);
$todayDate = date('Y-m-d');
echo <<<EOT

<form action="add_exchange_rate.php" method="post">
<table>
	<tr><th colspan="2">Add new exchange rate</th></tr>
	<tr>
		<td><strong>Date</strong></td>
		<td>
			<input id="date" name="date_of_conversion" size="10" maxlength="10" type="text" value="$todayDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'date', 'chooserSpanD', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>Source Currency</td>
		<td>
			<select name="source_currency">
$currencyOptions
			</select>
		</td>
	<tr>
		<td>Destination Currency</td>
		<td>
			<select name="destination_currency">
$currencyOptions
			</select>
		</td>
	</tr>
	<tr>
		<td>Rate</td>
		<td><input name="rate" size="6"></td>
	</tr>
	<tr><td colspan="2"><input type="submit" value="Save rate"></td></tr>
</table>
</form>


<table><tr>
EOT;


foreach($currencies as $curr) {
	if($curr == 'EUR') {
		continue;
	}
	echo "	<td><h2 style=\"padding: 5px 25px;\">EUR - $curr</h2><table>\n";
	if(isset($EXCHANGE_TABLE['EUR'][$curr])) {
		foreach($EXCHANGE_TABLE['EUR'][$curr] as $date => $rate) {
			echo "		<tr><td>$date</td><td>$rate</td></tr>\n";
		}
	}
	echo " </table></td>\n";
}

echo <<<EOT
</tr></table>


EOT;


html_end();


?>
