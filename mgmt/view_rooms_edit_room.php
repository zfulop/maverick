<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}

$roomId = intval($_REQUEST['room_id']);

$link = db_connect();

$sql = "SELECT * FROM room_types ORDER BY _order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get room types in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	mysql_close($link);
	return;
}
$roomTypes = array();
while($row = mysql_fetch_assoc($result)) {
	$roomTypes[$row['id']] = $row;
}


$roomData = array('name' => '', 'room_type_id' => -1, 'valid_from' => '', 'valid_to' => '');
if($roomId > 0) {
	$sql = "SELECT * FROM rooms WHERE id=$roomId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get rooms in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo "Cannot find room with id provided. ";
		mysql_close($link);
		return;
	}
	$roomData = mysql_fetch_assoc($result);
}

$additionalRoomTypes = array();
$sql = "SELECT * FROM rooms_to_room_types WHERE room_id=$roomId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo "Cannot get additional room types for the room.";
	mysql_close($link);
	return;
}
while($row = mysql_fetch_assoc($result)) {
	$additionalRoomTypes[] = $row['room_type_id'];
}

$roomTypeOptions = '';
$additionalRoomTypesOptions = '';
foreach($roomTypes as $rtId => $roomType) {
	$rtName = $roomType['name'];
	$roomTypeOptions .= "<option value=\"$rtId\"" . ($roomData['room_type_id'] == $rtId ? ' selected' : '') . ">$rtName</option>";
	if($roomData['room_type_id'] != $rtId and $roomType['type'] != 'DORM') {
		$additionalRoomTypesOptions .= "<option value=\"$rtId\"" . (in_array($rtId, $additionalRoomTypes) ? ' selected' : '') . ">$rtName</option>";
	}
}

$name = $roomData['name'];
$validFrom = $roomData['valid_from'];
$validTo = $roomData['valid_to'];

$title = 'Edit Room';
if($roomId < 1) {
	$title = 'New Room';
}

$additionalRoomTypesRow = '';
if(isset($roomTypes[$roomData['room_type_id']]) and $roomTypes[$roomData['room_type_id']]['type'] != 'DORM')
$additionalRoomTypesRow = <<<EOT
	<tr><td><label>Additional room types</label></td><td><select name="additional_types[]" multiple="multiple" style="width: 200px; height: 100px; font-size: 11px;">
$additionalRoomTypesOptions
	</select></td></tr>

EOT;

echo <<<EOT

<form action="save_room.php" accept-charset="utf-8" method="POST">
<fieldset>
<h3>$title</h3>
<input type="hidden" name="id" value="$roomId">
<table>
	<tr><td><label>Name</label></td><td><input name="name" value="$name" style="width: 200px;"></td></tr>
	<tr><td><label>Type</label></td><td><select name="type" style="width: 200px; font-size: 11px;">
$roomTypeOptions
	</select></td></tr>
$additionalRoomTypesRow
	<tr><td><label>Valid from</label></td><td><input name="valid_from" value="$validFrom" style="width: 80px;"> <span> (YYYY/MM/DD) - inclusive</span></td></tr>
	<tr><td><label>Valid to</label></td><td><input name="valid_to" value="$validTo" style="width: 80px;"> <span> (YYYY/MM/DD) - inclusive</span></td></tr>
</table>
</fieldset>
<fieldset>
<input type="submit" value="Save room">

</fieldset>
</form>

EOT;


mysql_close($link);


?>
