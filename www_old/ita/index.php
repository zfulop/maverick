<?php

require('../includes.php');


html_start('IandI', null, 'Welcome to Maverick Hostel Budapest');

echo <<<EOT
			<a href="photos.php"><img style="border:none; float:right; width: 427px; height: 500px;" src="/images/Image.jpg"></a>

<p>
Vuoi sentire l'energia vitale di Budapest ma al tempo stesso sei alla ricerca di un ambiente calmo e pulito con una bella atmosfera? Noi possiamo offrirti tutto questo.
</p>

<p>
Il Maverick Hostel si trova nel cuore della città, in un edificio completamente rinnovato, costruito dalla dinastia degli Asburgo, che ti accoglierà in tutta la sua originale magnificenza.
</p>

<p>
Nella struttura non vi sono letti a castello e nelle aree comuni sono presenti camini, sgabelli imbottiti e piante per creare una splendida atmosfera.
</p>

<p>
La posizione centrale del nostro ostello consente di evitare spostamenti inutili poiché la maggior parte delle attrazioni della città sono raggiungibili in 15 minuti. Grazie alla nostra esperienza nella gestione di ostelli in tutto il mondo, prestiamo particolare attenzione alla pulizia e alla tranquillità e il nostro personale è pronto a darti il benvenuto dopo una faticosa giornata ricca di eventi.
</p>


			<div style="display:block; height: 29px;">
				<div class="special_offer_button_left"></div>
				<div class="special_offer_button_text"><a href="special_offers.php">Special offers!</a></div>
				<div class="special_offer_button_right"></div>
			</div>

EOT;


$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);

$lang = getCurrentLanguage();

$sql = "SELECT lang_text1.value AS title, lang_text2.value AS text FROM special_offers INNER JOIN lang_text AS lang_text1 ON (lang_text1.row_id=special_offers.id AND lang_text1.table_name='special_offers' AND lang_text1.column_name='title' AND lang_text1.lang='$lang') INNER JOIN lang_text AS lang_text2 ON (lang_text2.row_id=special_offers.id AND lang_text2.table_name='special_offers' AND lang_text2.column_name='text' AND lang_text2.lang='$lang') ORDER BY special_offers._order";
$result = mysql_query($sql, $link);
$offers = array();
if(!$result) {
	trigger_error("Cannot get special offer texts: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$title = trim($row['title']);
		$text = trim($row['text']);
		if(strlen($title) > 0 and strlen($text) > 0) {
			echo <<<EOT
			<div class="special_offer">
				<h2>$title</h2>
				<p>$text</p>
			</div>

EOT;
		}
	}
}


echo <<<EOT

			<div style="clear:both;"></div>

EOT;

mysql_close($link);


html_end('IandI', null);

?>
