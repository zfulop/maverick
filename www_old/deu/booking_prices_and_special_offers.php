<?php

require('../includes.php');

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);

$MONTHS = array(
	1 => 'Januar',
	2 => 'Februar',
	3 => 'März',
	4 => 'April',
	5 => 'Mai',
	6 => 'Juni',
	7 => 'Juli',
	8 => 'August',
	9 => 'September',
	10 => 'Oktober',
	11 => 'November',
	12 => 'Dezember'
);

html_start('BookingAndPrices', null);


$yearOptions = "";
for($i = date('Y'); $i <= date('Y') + 1; $i++) {
	$yearOptions .= "\t\t\t\t\t\t<option value=\"$i\"" . ($i == date('Y') ? ' selected' : '') . ">$i</option>\n";
}

$monthOptions = "";
for($i = 1; $i <= 12; $i++) {
	$monthOptions .= "\t\t\t\t\t\t<option value=\"$i\"" . ($i == date('n') ? ' selected' : '') . ">" . $MONTHS[$i] . "</option>\n";
}

$dayOptions = "";
for($i = 1; $i <= 31; $i++) {
	$dayOptions .= "\t\t\t\t\t\t<option value=\"$i\"" . ($i == date('j') ? ' selected' : '') . ">$i</option>\n";
}	


echo <<<EOT

			<form action="view_availability.php" method="GET"><fieldset>
			<table style="width: 100%;">
			<tr class="title"><th colspan="3">Buchung</td></tr>
			<tr class="content" style="padding-top: 4px; padding-bottom: 4px;">
				<td class="booking">
					<label style="display: inline;">Anreise</label> <select style="float: none;" name="arrive_year">
$yearOptions
					</select> <select name="arrive_month" style="float: none;">
$monthOptions
					</select> <select name="arrive_day" style="float: none;">
$dayOptions
					</select>
				</td>
				<td class="booking">
					<label style="display: inline;">Abfahrt</label>  
					<select style="float: none;" name="depart_year">
$yearOptions
					</select> <select name="depart_month" style="float: none;">
$monthOptions
					</select> <select name="depart_day" style="float: none;">
$dayOptions
					</select>
				</td>
				<td class="booking" style="text-align: right;">
					<input type="submit" class="input_btn" value="Suche Preise">
				</td>
			</tr>
			</table>
			</fieldset></form>
EOT;



echo <<<EOT
			<h2>Preise</h2>
			<p>Unsere Preise beinhalten alle Steuern, Handtuch, Bettwäsche, Internet-Zugang und kostenloses Kaffee und Tee den ganzen Tag lang.</p>

EOT;

$lang = getCurrentLanguage();

$sql = "SELECT lang_text1.value AS title, lang_text2.value AS text FROM special_offers INNER JOIN lang_text AS lang_text1 ON (lang_text1.row_id=special_offers.id AND lang_text1.table_name='special_offers' AND lang_text1.column_name='title' AND lang_text1.lang='$lang') INNER JOIN lang_text AS lang_text2 ON (lang_text2.row_id=special_offers.id AND lang_text2.table_name='special_offers' AND lang_text2.column_name='text' AND lang_text2.lang='$lang') ORDER BY special_offers._order";

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get special offer texts: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
		echo <<<EOT
			<h2>Sonderangebot</h2>

EOT;
	while($row = mysql_fetch_assoc($result)) {
		$title = trim($row['title']);
		$text = trim($row['text']);
		if(strlen($title) > 0 and strlen($text) > 0) {
			echo <<<EOT
			<div class="special_offer">
				<h3>$title</h3>
				<p>$text</p>
			</div>

EOT;
		}
	}
} 



html_end('BookingAndPrices', 'PricesAndSpecialOffers');

mysql_close($link);

?>
