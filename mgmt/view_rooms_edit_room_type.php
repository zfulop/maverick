<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}

$roomTypeId = intval($_REQUEST['room_type_id']);

$link = db_connect();

$roomType = array('id' => 0, 'name' => '', 'type' => '', 'num_of_beds' => '', 'num_of_extra_beds' => '', 'price_per_bed' => '', 'price_per_room' => '', 'surcharge_per_bed' => '', '_order' => '');
if($roomTypeId > 0) {
	$sql = "SELECT * FROM room_types where id=$roomTypeId";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get room types in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		echo "Cannot find room type with the provided id";
		mysql_close($link);
		return;
	}
	$roomType = mysql_fetch_assoc($result);
}

$id = $roomType['id'];
$name = $roomType['name'];
$dormSelected = ($roomType['type'] == 'DORM' ? ' selected' : '');
$privateSelected = ($roomType['type'] == 'PRIVATE' ? ' selected' : '');
$apartmentSelected = ($roomType['type'] == 'APARTMENT' ? ' selected' : '');
$numOfBeds = $roomType['num_of_beds'];
$numOfExtraBeds = $roomType['num_of_extra_beds'];
$pricePerRoom = $roomType['price_per_room'];
$pricePerBed = $roomType['price_per_bed'];
$surchargePerBed = $roomType['surcharge_per_bed'];
$order = $roomType['_order'];

$sql = "SELECT * FROM lang_text WHERE table_name='room_types' and row_id=$roomTypeId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get room texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
$langText = array();
while($row = mysql_fetch_assoc($result)) {
	$langText[$row['lang']][$row['column_name']] = $row['value'];
}

$title = 'Edit Room Type';
if($id < 1) {
	$title = 'New Room Type';	
}

echo <<<EOT

<form action="save_room_type.php" accept-charset="utf-8" method="POST">
<h3>$title</h3>
<fieldset>
<input type="hidden" id="id" name="id" value="$id">
<table>
	<tr><td><label>Name</label></td><td><input name="name" value="$name" style="width: 200px;"></td></tr>
	<tr><td><label>Type</label></td><td><select name="type" style="width: 200px; font-size: 11px;">
		<option value="DORM"$dormSelected>Dormitory</option>
		<option value="PRIVATE"$privateSelected>Private</option>
		<option value="APARTMENT"$apartmentSelected>Apartment</option>
	</select></td></tr>
	<tr><td><label>Number of beds</label></td><td><input name="num_of_beds" value="$numOfBeds" style="width: 40px;"></td></tr>
	<tr><td><label>Number of extra beds</label></td><td><input name="num_of_extra_beds" value="$numOfExtraBeds" style="width: 40px;"></td></tr>
	<tr><td><label>Price per room</label></td><td><input name="price_per_room" value="$pricePerRoom" style="width: 40px;"> <span>Euro</span></td></tr>
	<tr><td><label>Price per bed</label></td><td><input name="price_per_bed" value="$pricePerBed" style="width: 40px;"> <span>Euro</span></td></tr>
	<tr><td><label>Surcharge per bed (for apartments)</label></td><td><input name="surcharge_per_bed" value="$surchargePerBed" style="width: 40px;"><span>%</span></td></tr>

EOT;
foreach(getLanguages() as $langCode => $langName) {
	$name = $langText[$langCode]['name'];
	$shortDescription = $langText[$langCode]['short_description'];
	$description = $langText[$langCode]['description'];
	$size = $langText[$langCode]['size'];
	$location = $langText[$langCode]['location'];
	$bathroom = $langText[$langCode]['bathroom'];

	echo <<<EOT
	<tr><td colspan="2" style="border-top:1px dotted black;"><b>$langName</b></td></tr>
	<tr><td><label>Name</label></td><td><input name="name_$langCode" value="$name" style="width: 200px"></td></tr>
	<tr><td><label>Short description</label></td><td><input style="width: 600px;" name="short_description_$langCode" value="$shortDescription"></td></tr>
	<tr><td><label>Description</label></td><td><textarea style="width: 600px; height=400px;" name="description_$langCode">$description</textarea></td></tr>
	<tr><td><label>Size</label></td><td><textarea style="width: 600px; height=400px;" name="size_$langCode">$size</textarea></td></tr>
	<tr><td><label>Location</label></td><td><textarea style="width: 600px; height=400px;" name="location_$langCode">$location</textarea></td></tr>
	<tr><td><label>Bathroom</label></td><td><textarea style="width: 600px; height=400px;" name="bathroom_$langCode">$bathroom</textarea></td></tr>

EOT;
}
echo <<<EOT
</table>
</fieldset>
<fieldset>
<input type="submit" value="Save room type">
</fieldset>
</form>
<br>

EOT;

mysql_close($link);


?>
