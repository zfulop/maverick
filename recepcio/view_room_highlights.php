<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$roomTypes = RoomDao::getRoomTypes('eng', $link);

$roomHighlights = RoomDao::getRoomHighlights($link);

html_start("Room Highlights");

echo <<<EOT

Select the highlighted rooms <br>  
<form action="save_room_highlights.php" method="post" accept-charset="utf-8">
<ul>

EOT;
foreach($roomTypes as $rtId => $roomType) {
	$name = $roomType['name'];
	$checked = (in_array($rtId, $roomHighlights) ? ' checked' : '');
	echo "	<li style=\"padding-left: 20px;\"><input type=\"checkbox\" name=\"$rtId\" value=\"1\"$checked> $name</li>\n";
}

echo <<<EOT
</ul>
<input type="submit" value="Save room highlights">
</form>

EOT;

mysql_close($link);

html_end();


?>
