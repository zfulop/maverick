<?php

require('includes.php');

$lang = getCurrentLanguage();

echo "<table>\n";
foreach(getLocations() as $location) {
	$link = db_connect($location);
	$sql = "SELECT a.*, d.value AS description FROM awards a INNER JOIN lang_text d ON (d.table_name='awards' AND d.column_name='description' AND d.row_id=a.id and d.lang='$lang') ORDER BY _order";
	$result = mysql_query($sql, $link);
	while($row = mysql_fetch_assoc($result)) {
		echo "	" . $row['javascript'] . "\n";
		if(strlen($row['html']) > 0) {
			echo "	<tr><td>" . $row['html'] . "</td></tr>\n";
		} else {
			echo "	<tr><td><img src=\"" . constant('AWARDS_IMG_URL_' . strtoupper($location)) . $row['img'] . "\"></td><td>" . $row['description'] . "</td></tr>\n";
		}
	}
	mysql_close($link);
}
echo "</table>";


?>
