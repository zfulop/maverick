<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}



$TYPES = array(
	'DORM' => 0,
	'PRIVATE' => 1,
	'APARTMENT' => 2);

$thisYear =  date('Y');
$nextYear = $thisYear + 1;

if(!isset($_SESSION['room_price_room_type_ids'])) {
	$_SESSION['room_price_room_type_ids'] = array();
}
if(!isset($_SESSION['room_price_start_date'])) {
	$_SESSION['room_price_start_date'] = date('Y-m-d');
}
if(!isset($_SESSION['room_price_end_date'])) {
	$_SESSION['room_price_end_date'] = date('Y-m-d');
}
if(!isset($_SESSION['room_price_days'])) {
	$_SESSION['room_price_days'] = array(1,2,3,4,5,6,7);
}

$rpRoomTypeIds = $_SESSION['room_price_room_type_ids'];

$rpStartDate = $_SESSION['room_price_start_date'];
$rpEndDate = $_SESSION['room_price_end_date'];

$monChecked = in_array(1, $_SESSION['room_price_days']) ? 'checked' : '';
$tueChecked = in_array(2, $_SESSION['room_price_days']) ? 'checked' : '';
$wedChecked = in_array(3, $_SESSION['room_price_days']) ? 'checked' : '';
$thuChecked = in_array(4, $_SESSION['room_price_days']) ? 'checked' : '';
$friChecked = in_array(5, $_SESSION['room_price_days']) ? 'checked' : '';
$satChecked = in_array(6, $_SESSION['room_price_days']) ? 'checked' : '';
$sunChecked = in_array(7, $_SESSION['room_price_days']) ? 'checked' : '';



$link = db_connect();

$roomTypesHtmlOptions = '';
$rpRoomTypesHtmlOptions = '';


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
	$roomTypesHtmlOptions .= '		<option value="' . $row['id'] . '">' . $row['name'] . "</option>\n";
	$rpRoomTypesHtmlOptions .= '		<option value="' . $row['id'] . '"' . (in_array($row['id'], $rpRoomTypeIds) ? ' selected' : '') . '>' . $row['name'] . "</option>\n";
	$row['rooms'] = array();
	$roomTypes[$row['id']] = $row;
}
$sql = "SELECT * FROM rooms";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	mysql_close($link);
	return;
}
while($row = mysql_fetch_assoc($result)) {
	$roomTypes[$row['room_type_id']]['rooms'][] = $row;
}

$roomsToRoomTypes = array();
$sql = "SELECT * FROM rooms_to_room_types";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	mysql_close($link);
	return;
}
while($row = mysql_fetch_assoc($result)) {
	if(!isset($roomsToRoomTypes[$row['room_id']])) {
		$roomsToRoomTypes[$row['room_id']] = array();
	}
	$roomsToRoomTypes[$row['room_id']][] = $row['room_type_id'];
}





$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->
<script type="text/javascript">
function submitPriceForm() {
	$('price_form').submit();
	if($('sync').checked) {
		setTimeout(function(){ location.reload(); }, 5000);
	}	


}


function includesArr(arr, searchElement) {
    var len = parseInt(arr.length);
    if (len === 0) {
      return false;
    }
    var i = 0;
    var currentElement;
    while (i < len) {
      currentElement = arr[i];
      if (searchElement === currentElement) {
	return true;
      }
      i++;
    }
    return false;
}


</script>

EOT;


html_start("Rooms ", $extraHeader);


echo <<<EOT

<form id="create_type_btn">
<input type="button" onclick="document.getElementById('room_type_form').reset();document.getElementById('room_type_form').style.display='block'; document.getElementById('create_type_btn').style.display='none'; return false;" value="Register new room type">
</form>
<br>

<form id="room_type_form" style="display: none;" action="save_room_type.php" accept-charset="utf-8" method="POST">
<h3>Edit Room Type</h3>
<fieldset>
<input type="hidden" id="id" name="id" value="">
<table>
	<tr><td><label>Name</label></td><td><input name="name" id="room_type_name" style="width: 200px;"></td></tr>
	<tr><td><label>Type</label></td><td><select name="type" id="type" style="width: 200px; font-size: 11px;">
		<option value="DORM">Dormitory</option>
		<option value="PRIVATE">Private</option>
		<option value="APARTMENT">Apartment</option>
	</select></td></tr>
	<tr><td><label>Number of beds</label></td><td><input name="num_of_beds" id="num_of_beds" style="width: 40px;"></td></tr>
	<tr><td><label>Number of extra beds</label></td><td><input name="num_of_extra_beds" id="num_of_extra_beds" style="width: 40px;"></td></tr>
	<tr><td><label>Price per room</label></td><td><input name="price_per_room" id="price_per_room" style="width: 40px;"> <span>Euro</span></td></tr>
	<tr><td><label>Price per bed</label></td><td><input name="price_per_bed" id="price_per_bed" style="width: 40px;"> <span>Euro</span></td></tr>
	<tr><td><label>Surcharge per bed (for apartments)</label></td><td><input name="surcharge_per_bed" id="surcharge_per_bed" style="width: 40px;"><span>%</span></td></tr>
	<tr><td><label>Order</label></td><td><input name="order" id="order" style="width: 40px;"></td></tr>

EOT;
foreach(getLanguages() as $langCode => $langName) {
	echo <<<EOT
	<tr><td><label>Name ($langName)</label></td><td><input name="name_$langCode" id="name_$langCode" style="width: 200px"></td></tr>
	<tr><td><label>Short description ($langName) (eg. dbl room)</label></td><td><input style="width: 600px;" name="short_description_$langCode" id="short_description_$langCode"></td></tr>
	<tr><td><label>Description ($langName)</label></td><td><textarea style="width: 600px; height=400px;" name="description_$langCode" id="description_$langCode"></textarea></td></tr>

EOT;
}
echo <<<EOT
</table>
</fieldset>
<fieldset>
<input type="submit" value="Save room type">
<input type="button" onclick="document.getElementById('room_type_form').style.display='none'; document.getElementById('create_type_btn').style.display='block'; return false;" value="Cancel">

</fieldset>
</form>
<br>


<form id="create_btn">
<input type="button" onclick="document.getElementById('room_form').reset();document.getElementById('room_form').style.display='block'; document.getElementById('create_btn').style.display='none'; return false;" value="Register new room">
</form>
<br>

<form id="room_form" style="display: none;" action="save_room.php" accept-charset="utf-8" method="POST">
<fieldset>
<h3>Edit Room</h3>
<input type="hidden" id="room_id" name="id" value="">
<table>
	<tr><td><label>Name</label></td><td><input name="name" id="room_name" style="width: 200px;"></td></tr>
	<tr><td><label>Type</label></td><td><select name="type" id="room_type" style="width: 200px; font-size: 11px;">
$roomTypesHtmlOptions
	</select></td></tr>
	<tr><td><label>Additional room types</label></td><td><select name="additional_types[]" multiple="multiple" id="additional_room_types" style="width: 200px; height: 100px; font-size: 11px;">
$roomTypesHtmlOptions
	</select></td></tr>
	<tr><td><label>Valid from</label></td><td><input name="valid_from" id="valid_from" style="width: 80px;"> <span> (YYYY/MM/DD) - inclusive</span></td></tr>
	<tr><td><label>Valid to</label></td><td><input name="valid_to" id="valid_to" style="width: 80px;"> <span> (YYYY/MM/DD) - inclusive</span></td></tr>
</table>
</fieldset>
<fieldset>
<input type="submit" value="Save room">
<input type="button" onclick="document.getElementById('room_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;" value="Cancel">

</fieldset>
</form>
<br>


<form id="price_btn">
<input type="button" onclick="document.getElementById('price_form').reset();document.getElementById('price_form').style.display='block'; document.getElementById('price_btn').style.display='none'; return false;" value="Set price for a room type">
</form>
<br>




<form action="save_room_prices.php" target="_blank" method="POST" style="display: none;" id="price_form">
<table style="border: 1px solid rgb(0,0,0);">
<tr><th colspan="2">Set price of a room for a date interval.</strong></th></tr>
<tr><td colspan="2">To delete special price, set the date and leave the price field empty.</td></tr>
<tr><td>Room type: </td><td><select style="display: inline; float: none; height: 100px;" multiple="true" name="room_type_ids[]">
$rpRoomTypesHtmlOptions
</select></td></tr>
<tr><td>Start date: </td><td><input name="start_date" id="rp_start_date" size="10" maxlength="10" type="text" value="$rpStartDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'rp_start_date', 'chooserSpanRPSD', 2008, 2025, 'Y-m-d', false);"><div id="chooserSpanRPSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div></td></tr>
<tr><td>End date: </td><td><input name="end_date" id="rp_end_date" size="10" maxlength="10" type="text" value="$rpEndDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'rp_end_date', 'chooserSpanRPED', 2008, 2025, 'Y-m-d', false);"><div id="chooserSpanRPED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div></td></tr>
<tr><td>Days (<input style="float: left; display: block;" type="checkbox" onclick="toggleDaySelection(this);"> all)</td><td>
	<div style="clear: left;">Mon <input class="dayselect" style="float: left; display: block;" type="checkbox" name="days[]" value="1" $monChecked></div>
	<div style="clear: left;">Tue <input class="dayselect" style="float: left; display: block;" type="checkbox" name="days[]" value="2" $tueChecked></div>
	<div style="clear: left;">Wed <input class="dayselect" style="float: left; display: block;" type="checkbox" name="days[]" value="3" $wedChecked></div>
	<div style="clear: left;">Thu <input class="dayselect" style="float: left; display: block;" type="checkbox" name="days[]" value="4" $thuChecked></div>
	<div style="clear: left;">Fri <input class="dayselect" style="float: left; display: block;" type="checkbox" name="days[]" value="5" $friChecked></div>
	<div style="clear: left;">Sat <input class="dayselect" style="float: left; display: block;" type="checkbox" name="days[]" value="6" $satChecked></div>
	<div style="clear: left;">Sun <input class="dayselect" style="float: left; display: block;" type="checkbox" name="days[]" value="7" $sunChecked></div>
</td></tr>
<tr><td>Bed or Room Price: </td><td><input name="price" size="4"></td></tr>
<tr><td>Surcharge per bed (for apartments): </td><td><input name="surcharge_per_bed" size="4"></td></tr>
<tr><td>Automatic sync: </td><td><input name="sync" id="sync" type="checkbox" value="true" checked="true" onclick="if(this.checked) { document.getElementById('price_form').target='_blank'; } else { document.getElementById('price_form').target='_self'; }"></td></tr>
<tr><td colspan="2">
	<input type="button" onclick="if(confirm('Are you sure to save the prices?'))submitPriceForm();return false;" value="Set price(s)">
	<input type="button" onclick="document.getElementById('price_form').style.display='none'; document.getElementById('price_btn').style.display='block'; return false;" value="Cancel">
</td></tr>
</table>
</form>



<h2>Existing Rooms</h2>
<table border="1">

EOT;
if(count($roomTypes) > 0)
	echo "	<tr><th>Order</th><th>Name</th><th>Type</th><th>Price per bed</th><th>Price per room</th><th>Surcharge per bed</th><th># of beds</th><th># of extra beds</th><th></th></tr>\n";
else
	echo "	<tr><td><i>No record found.</i></td></tr>\n";

foreach($roomTypes as $roomTypeId => $roomType) {
	$sql = "SELECT * FROM lang_text WHERE table_name='room_types' and row_id=" . $roomTypeId;
	$result2 = mysql_query($sql, $link);
	if(!$result2) {
		trigger_error("Cannot get room texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	}
	$record = array();
	while($row = mysql_fetch_assoc($result2)) {
		$record[$row['lang']][$row['column_name']] = $row['value'];
	}

	echo "<script type=\"text/javascript\">\n";
	echo "	function editType" . $roomTypeId . "() {\n";
	echo "		document.getElementById('room_type_form').reset();\n";
	echo "		document.getElementById('room_type_form').style.display='block';\n";
	echo "		document.getElementById('create_type_btn').style.display='none';\n";
	echo "		document.getElementById('id').value='" . $roomTypeId . "';\n";
	echo "		document.getElementById('room_type_name').value='" . $roomType['name'] . "';\n";
	echo "		document.getElementById('price_per_room').value='" . $roomType['price_per_room'] . "';\n";
	echo "		document.getElementById('price_per_bed').value='" . $roomType['price_per_bed'] . "';\n";
	echo "		document.getElementById('surcharge_per_bed').value='" . $roomType['surcharge_per_bed'] . "';\n";
	echo "		document.getElementById('num_of_beds').value='" . $roomType['num_of_beds'] . "';\n";
	echo "		document.getElementById('num_of_extra_beds').value='" . $roomType['num_of_extra_beds'] . "';\n";
	echo "		document.getElementById('type').selectedIndex=" . $TYPES[$roomType['type']] . ";\n";
	echo "		document.getElementById('order').value='" . $roomType['_order'] . "';\n";
	foreach($record as $lang => $cols) {
		echo "		document.getElementById('name_$lang').value='" . js_escape($cols['name']) . "';\n";
		echo "		document.getElementById('description_$lang').value='" . js_escape($cols['description']) . "';\n";
		if(isset($cols['short_description'])) {
			echo "		document.getElementById('short_description_$lang').value='" . js_escape($cols['short_description']) . "';\n";
		}
	}
	echo "	}\n";
	echo "</script>\n";

	echo "	<tr>";
	echo "<td><table><tr><td rowspan=\"2\">" . $roomType['_order'] . ".</td><td><input type=\"button\" value=\"Move up\" onclick=\"window.location='change_order.php?direction=up&table=room_types&id=" . $roomType['id'] . "&order=" . $roomType['_order'] . "';\"></td></tr><tr><td><input type=\"button\" value=\"Move down\" onclick=\"window.location='change_order.php?direction=down&table=room_types&id=" . $roomType['id'] . "&order=" . $roomType['_order'] . "';\"></td></tr></table></td>";
	echo "<td><strong>" . $roomType['name'] . "</strong></td>";omTypo
	echo "<td>" . $roomType['type'] . "</td>";
	echo "<td>" . $roomType['price_per_bed'] . "</td>";
	echo "<td>" . $roomType['price_per_room'] . "</td>";
	echo "<td>" . $roomType['surcharge_per_bed'] . "</td>";
	echo "<td>" . $roomType['num_of_beds'] . "</td>";
	echo "<td>" . $roomType['num_of_extra_beds'] . "</td>";
	echo "<td>\n";
	echo "	<ul>\n";
	echo "	<li><a href=\"#\" onclick=\"editType" . $roomType['id'] . "();\">Edit</a></li>\n";
	if(count($roomType['rooms']) < 1) {
		echo "	<li><a href=\"delete_room_type.php?id=" . $roomType['id'] . "\">Delete</a></li>\n";
	}
	echo "	<li><a href=\"view_room_prices.php?room_type_id=" . $roomType['id'] . "\">Prices per date</a></li>\n";
	echo "	</ul>\n";
	echo "</td>";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td colspan=\"2\">&nbsp;</td>\n";
	echo "	<td colspan=\"7\">\n";
	echo "		<table>\n";
	foreach($roomType['rooms'] as $room) {
		$roomId = $room['id'];
		$roomName = $room['name'];
		$roomValidFrom = $room['valid_from'];
		$roomValidTo = $room['valid_to'];
		$rtSelectedIdx = array_search($room['room_type_id'], array_keys($roomTypes));
		$roomTypesArr = '';
		if(isset($roomsToRoomTypes[$roomId])) {
			$roomTypesArr = implode(",", $roomsToRoomTypes[$roomId]);
		}
		echo <<<EOT
<script type="text/javascript">
	function edit$roomId() {
		var roomTypesArr = [$roomTypesArr];
		document.getElementById('room_form').reset();
		document.getElementById('room_form').style.display='block';
		document.getElementById('create_btn').style.display='none';
		document.getElementById('room_name').value='$roomName';
		document.getElementById('room_id').value='$roomId';
		document.getElementById('valid_from').value='$roomValidFrom';
		document.getElementById('valid_to').value='$roomValidTo';
		document.getElementById('room_type').selectedIndex=$rtSelectedIdx;
		var select = document.getElementById('additional_room_types');
		l=select.options.length;
		for(var i=0; i < l; i++ ) {
			o = select.options[i];
			if(includesArr(roomTypesArr, parseInt(o.value))) { o.selected = true; }
		}
	}
</script>

			<tr><td>$roomName</td><td>$roomValidFrom - $roomValidTo</td>
			    <td><a href="#" onclick="edit$roomId();">Edit</a><br>
			        <a href="delete_room.php?id=$roomId">Delete</a><br>
			    </td>
			</tr>

EOT;
	}
	echo "		</table>\n";
	echo "	</td>\n";
	echo "</tr>\n";
}

echo <<<EOT
</table>

EOT;


mysql_close($link);

html_end();

function js_escape($text) {
	$text = str_replace("\n", " ", $text);
	$text = str_replace("\r", " ", $text);
	$text = str_replace('\'', '\\\'', $text);
	return $text;
}

?>
