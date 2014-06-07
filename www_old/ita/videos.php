<?php

require('../includes.php');


$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);


html_start('TheMaverick', 'Videos');

$lang = getCurrentLanguage();

$sql = "SELECT lang_text1.value AS title, lang_text2.value AS html FROM videos INNER JOIN lang_text AS lang_text1 ON (videos.id=lang_text1.row_id AND lang_text1.table_name='videos' AND lang_text1.column_name='title' AND lang_text1.lang='$lang') INNER JOIN lang_text AS lang_text2 ON (videos.id=lang_text2.row_id AND lang_text2.table_name='videos' AND lang_text2.column_name='html' AND lang_text2.lang='$lang')";
$result = mysql_query($sql, $link);
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		$title = $row['title'];
		$html = $row['html'];
		if(strlen($html) < 1)
			continue;
		echo "				<div class=\"video\"><h2>$title</h2>$html</div>\n";
	}
} else {
	trigger_error("Cannot get videos: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}


html_end('TheMaverick', 'Videos');

mysql_close($link);

?>
