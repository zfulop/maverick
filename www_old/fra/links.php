<?php

require('../includes.php');


$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);


html_start('Links', null);

$lang = getCurrentLanguage();

$sql = "SELECT links.url, lang_text1.value AS name, lang_text2.value AS description FROM links INNER JOIN lang_text AS lang_text1 ON (links.id=lang_text1.row_id AND lang_text1.table_name='links' AND lang_text1.column_name='name' AND lang_text1.lang='$lang') INNER JOIN lang_text AS lang_text2 ON (links.id=lang_text2.row_id AND lang_text2.table_name='links' AND lang_text2.column_name='description' AND lang_text2.lang='$lang')";
$result = mysql_query($sql, $link);
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		$url = $row['url'];
		$name = $row['name'];
		$description = $row['description'];
		echo "				<div class=\"link\"><h2><a href=\"$url\" target=\"_blank\">$name</a></h2><p>$description</p></div>\n";
	}
} else {
	trigger_error("Cannot get links: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}


html_end('Links', null);

mysql_close($link);

?>
