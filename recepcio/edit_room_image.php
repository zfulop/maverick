<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$location = $_SESSION['login_hotel'];
$id = $_REQUEST['id'];

$roomTypes = RoomDao::getRoomTypes('eng', $link);
$roomImages = RoomDao::getRoomImages(array_keys(getLanguages()), $link);

$roomImage = $roomImages[$id];
$src = ROOMS_IMG_URL . $location . '/' . $roomImage['thumb'];

$rtOptions = '';
foreach($roomTypes as $rtId => $rt) {
	$name = $rt['name'];
	$rtOptions .= "<option value=\"$rtId\"" . (in_array($rtId, $roomImage['room_types']) ? ' selected' : '') . ">$name</option>\n";
}
echo <<<EOT

<img src="$src"><br>
<form action="save_room_image_data.php">
<input type="hidden" name="room_image_id" value="$id">
<table>
<tr><td>Room types: </td><td><select multiple="multiple" name="room_types[]" style="height:80px;">
$rtOptions;
</select></td></tr>
<tr><td colspan="2"><b>Descriptions:</b></td></tr>

EOT;
foreach(getLanguages() as $code => $langName) {
	$value = $roomImage['description'][$code];
	echo "<tr><td>$langName: </td><td><input name=\"$code\" value=\"$value\"></td></tr>\n";
}
echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Save\"></td></tr>\n";
echo "</table>\n";
echo "</form>\n";

mysql_close($link);


?>
