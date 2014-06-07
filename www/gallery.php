<?php

require('includes.php');

$location = getLocation();
$lang = getCurrentLanguage();

$link = db_connect($location);

echo "<ul class=\"gallery\">\n";

$roomTypeId = $_REQUEST['room_type_id'];
$sql = "SELECT ri.*, l.value AS description FROM room_images ri LEFT OUTER JOIN lang_text l ON (l.table_name='room_images' AND l.column_name='description' AND l.row_id=ri.id AND l.lang='$lang') WHERE ri.room_type_id=$roomTypeId order by ri._order";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	$roomImg = constant('ROOMS_IMG_URL_' . strtoupper($location)) . $row['filename'];
	$width = $row['width'];
	$height = $row['height'];
	$descr = str_replace('"', '\"', $row['description']);
	echo "<li><img src=\"$roomImg\" title=\"$descr\" data-description=\"$descr\" width=\"$width\" height=\"$height\"></li>\n";
}
echo "</ul>\n";

mysql_close($link);

?>
