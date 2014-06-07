<?php

require('../includes.php');


$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);

html_start('HaveFun', null);

$lang = getCurrentLanguage();
$sql = "SELECT have_fun.*, lang_text.value AS description FROM have_fun INNER JOIN lang_text ON (lang_text.row_id=have_fun.id AND lang_text.table_name='have_fun' AND lang_text.column_name='description' AND lang_text.lang='$lang') ORDER BY have_fun._order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get events: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		if(!is_null($row['img']))
			$imgTag = '<img src="' . HAVE_FUN_IMG_URL . '/' . $row['img'] . '">';
		else
			$imgTag = '';

		$name = $row['name'];
		$location = $row['location'];
		$url = $row['url'];
		$telephone = $row['telephone'];
		$tofs = $row['time_of_show'];
		$description = $row['description'];
		echo <<<EOT
			<div class="have_fun">
				<div class="image">$imgTag</div>
				<div class="text">
					<h2>$name</h2>
					<p>
						$location<br>
						Telephone: $telephone<br>
						URL: $url<br>
						Time of show: $tofs
					</p>
					<p>$description</p>
				</div>
			</div>

EOT;
	}
}

html_end('HaveFun', null);

mysql_close($link);

?>
