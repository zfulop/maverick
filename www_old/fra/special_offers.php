<?php

require('../includes.php');


$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);

$lang = getCurrentLanguage();

$sql = "SELECT lang_text1.value AS title, lang_text2.value AS text FROM special_offers INNER JOIN lang_text AS lang_text1 ON (lang_text1.row_id=special_offers.id AND lang_text1.table_name='special_offers' AND lang_text1.column_name='title' AND lang_text1.lang='$lang') INNER JOIN lang_text AS lang_text2 ON (lang_text2.row_id=special_offers.id AND lang_text2.table_name='special_offers' AND lang_text2.column_name='text' AND lang_text2.lang='$lang')";
$result = mysql_query($sql, $link);
$offers = array();
if(!$result) {
	trigger_error("Cannot get special offer texts: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

html_start('IandI', null, 'Offres spéciales');

if($result) {
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

html_end('IandI', null);

mysql_close($link);

?>
