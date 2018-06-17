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
	$roomsToRoomTypes[$row['room_id']][] = $roomTypes[$row['room_type_id']]['name'];
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


	function editRoomType(id) {
		new Ajax.Request('view_rooms_edit_room_type.php', {
			method: 'post',
			parameters: {'room_type_id': id},
			onSuccess: function(transport) {
				Tip(transport.responseText, STICKY, true, FIX, ['room_type_' + id, 0, 0], CLICKCLOSE, false, CLOSEBTN, true);
			},
			onFailure: function(transport) {
				alert('HTTP Error in response. Please try again.');
			}
		});

		return false;
	}

	function editRoom(id) {
		new Ajax.Request('view_rooms_edit_room.php', {
			method: 'post',
			parameters: {'room_id': id},
			onSuccess: function(transport) {
				Tip(transport.responseText, STICKY, true, FIX, ['room_' + id, 0, 0], CLICKCLOSE, false, CLOSEBTN, true);
			},
			onFailure: function(transport) {
				alert('HTTP Error in response. Please try again.');
			}
		});

		return false;
	}
	
</script>


EOT;

$syncResult = '';
if(isset($_SESSION['sync_result'])) {
	$syncResult = 'Sync result: <div style="border: 1px solid black; font-family: Couriel">' . $_SESSION['sync_result'] . "</div>\n";
	unset($_SESSION['sync_result']);
}


html_start("Rooms ", $extraHeader);


echo <<<EOT

$syncResult

<form>
<div id="room_type_0">
<input type="button" onclick="editRoomType(0); return false;" value="Register new room type">
</div>
<div id="room_0">
<input type="button" onclick="editRoom(0); return false;" value="Register new room">
</div>
</form>
<br>
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
<tr><td>Automatic sync: </td><td><input name="sync" id="sync" type="checkbox" value="true" checked="true"></td></tr>
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

	echo "	<tr>";
	echo "<td><table><tr><td rowspan=\"2\">" . $roomType['_order'] . ".</td><td><input type=\"button\" value=\"Move up\" onclick=\"window.location='change_order.php?direction=up&table=room_types&id=" . $roomType['id'] . "&order=" . $roomType['_order'] . "';\"></td></tr><tr><td><input type=\"button\" value=\"Move down\" onclick=\"window.location='change_order.php?direction=down&table=room_types&id=" . $roomType['id'] . "&order=" . $roomType['_order'] . "';\"></td></tr></table></td>";
	echo "<td id=\"room_type_" . $roomType['id'] . "\"><strong>" . $roomType['name'] . "</strong></td>";
	echo "<td>" . $roomType['type'] . "</td>";
	echo "<td>" . $roomType['price_per_bed'] . "</td>";
	echo "<td>" . $roomType['price_per_room'] . "</td>";
	echo "<td>" . $roomType['surcharge_per_bed'] . "</td>";
	echo "<td>" . $roomType['num_of_beds'] . "</td>";
	echo "<td>" . $roomType['num_of_extra_beds'] . "</td>";
	echo "<td>\n";
	echo "	<ul>\n";
	echo "	<li><a href=\"#\" onclick=\"editRoomType(" . $roomType['id'] . ");return false;\">Edit</a></li>\n";
	if(count($roomType['rooms']) < 1) {
		echo "	<li><a href=\"delete_room_type.php?id=" . $roomType['id'] . "\">Delete</a></li>\n";
	}
	echo "	<li><a href=\"view_room_prices.php?room_type_id=" . $roomType['id'] . "\">Prices per date</a></li>\n";
	echo "	</ul>\n";
	echo "</td>";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td>&nbsp;</td>\n";
	echo "	<td colspan=\"8\">\n";
	echo "		<table>\n";
	echo "			<tr><th>Name</th><th>Valid</th><th>Additional room types</th></tr>\n";
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
			<tr><td>$roomName</td><td>$roomValidFrom - $roomValidTo</td><td>$roomTypesArr</td>
			    <td><a id="room_$roomId" href="#" onclick="editRoom($roomId);return false;">Edit</a><br>
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


?>
