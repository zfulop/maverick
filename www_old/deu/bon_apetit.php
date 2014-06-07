<?php

require('../includes.php');


$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);

html_start('BonApetit', null);

$lang = getCurrentLanguage();
$sql = "SELECT bon_apetit.*, lang_text.value AS description FROM bon_apetit INNER JOIN lang_text ON (lang_text.row_id=bon_apetit.id AND lang_text.table_name='bon_apetit' AND lang_text.column_name='description' AND lang_text.lang='$lang') ORDER BY bon_apetit._order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get restaurants: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		if(!is_null($row['img']))
			$imgTag = '<img style="vertical-align:middle;" src="' . BON_APETIT_IMG_URL . '/' . $row['img'] . '">';
		else
			$imgTag = '';

		$name = $row['name'];
		$location = $row['location'];
		$url = $row['url'];
		$telephone = $row['telephone'];
		$hours = $row['hours'];
		$description = $row['description'];
		echo <<<EOT
			<div class="bon_apetit">
				<div class="image">$imgTag</div>
				<div class="text">
					<h2>$name</h2>
					<p>
						$location<br>
						Telefon: $telephone<br>
						URL: $url<br>
						Betriebsstunden: $hours
					</p>
					<p>$description</p>
				</div>
			</div>

EOT;
	}
}

html_end('BonApetit', null);

mysql_close($link);

?>
