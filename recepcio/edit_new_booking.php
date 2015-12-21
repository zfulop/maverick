<?php

require("includes.php");
require("room_booking.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$SOURCES = array();


$link = db_connect();


$sql = "SELECT * FROM sources ORDER BY source";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get sources in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$SOURCES[] = $row['source'];
	}
}



$rooms = array();
$sql = "SELECT rooms.* FROM rooms";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error("Cannot get rooms.");
	trigger_error("Cannot get rooms in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
$roomsHtmlOptions = '';
while($row = mysql_fetch_assoc($result)) {
	$rooms[$row['id']] = $row;
	$roomsHtmlOptions .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
}


$booking = array();
$booking['name'] = '';
$booking['name_ext'] = '';
$booking['gender'] = '';
$booking['address'] = '';
$booking['nationality'] = '';
$booking['email'] = '';
$booking['telephone'] = '';
$booking['first_night'] = date('Y/m/d');
$booking['last_night'] = date('Y/m/d');
$booking['num_of_person'] = '1';
$booking['balance'] = '';
$booking['comment'] = '';
$booking['source'] = '';
$booking['payment'] = '0';
$booking['balance'] = '0';
$booking['deposit'] = '0';
$booking['arrival_time'] = '';
$booking['deposit_currency'] = 'EUR';
$sql = "SELECT rt.*, count(*) as num_of_rooms FROM room_types rt inner join rooms r on (rt.id=r.room_type_id) group by rt.id";
$result = mysql_query($sql, $link);
$roomTypes = array();
while($row = mysql_fetch_assoc($result)) {
	$booking['num_of_person_' . $row['id']] = 0;
	$roomTypes[$row['id']] = $row;
}

foreach($_SESSION as $code => $val) {
	if(substr($code, 0, 4) == 'ENB_') {
		$booking[substr($code, 4)] = $val;
	}
}

$cancelUrl = "view_availability.php";

$extraHeader = <<<EOT


	<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
	<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
	<!--[if lte IE 6.5]>
	<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
	<![endif]-->


	<script type="text/javascript" src="js/json2.js"></script>

	<script type="text/javascript">

		function recalculatePayment() {
			new Ajax.Request('recalculate_new_booking_payment.php', {
				method: 'post',
				parameters: $('edit_new_booking_form').serialize(true),
				onSuccess: function(transport) {
					var data = JSON.parse(transport.responseText);
					$('room_payment_field').value = data.roomPayment;
				}
			});
		}

	</script>

EOT;

html_start("Edit New Booking - Maverick Reception", $extraHeader);


$name = $booking['name'];
$nameExt = $booking['name_ext'];
$maleSelected = $booking['gender'] == 'MALE' ? ' selected' : '';
$femaleSelected = $booking['gender'] == 'FEMALE' ? ' selected' : '';
$address = $booking['address'];
$nationality = $booking['nationality'];
$email = $booking['email'];
$tel = $booking['telephone'];

$arrivalTime = $booking['arrival_time'];
$fnight = $booking['first_night'];
$lnight = $booking['last_night'];
$comment = $booking['comment'];
$source = $booking['source'];
$sourceOptions = "		<option value=''>Other</option>\n";
foreach($SOURCES as $oneOption) {
	$sourceOptions .= "		<option value='$oneOption'" . ($oneOption == $source ? ' selected' : '') . ">$oneOption</option>\n";
}

$payment = $booking['payment'];
$balance = $booking['balance'];
$deposit = $booking['deposit'];
$depositCurrencyOptions = '';
foreach(array('EUR', 'HUF') as $currency) {
	$depositCurrencyOptions .= "<option value=\"$currency\"" . ($currency == $booking['deposit_currency'] ? ' selected' : '') . ">$currency</option>";
}


$roomsHtml = '';
foreach($roomTypes as $roomTypeId => $roomType) {
	$excludeNums = array();
	if(isPrivate($roomType)) {
		for($i = 1; $i < $roomType['num_of_rooms'] * $roomType['num_of_beds']; $i++) {
			if($i % $roomType['num_of_beds'] > 0) {
				$excludeNums[] = $i;
			}
		}
	} elseif(isApartment($roomType)) {
		$excludeNums[] = 1;
	}
	$options = getOptions($roomType['num_of_rooms'] * $roomType['num_of_beds'], $booking['num_of_person_' . $roomTypeId], $excludeNums);
	$roomsHtml .= "<tr><td>" . str_replace(" ", "&nbsp;", $roomType['name']) . "</td><td><select name=\"num_of_person_$roomTypeId\" id=\"num_of_person_$roomTypeId\" onchange=\"recalculatePayment();\">$options</select></td></tr>\n";
}

$nationalityOptions = '';
$countries = file_get_contents(COUNTRIES_FILE);
foreach(explode("\n", $countries) as $cntry) {
	$cntry = trim($cntry);
	if(strlen($cntry) < 1)
		continue;

	$nationalityOptions .= "<option value=\"$cntry\"" . ($cntry == $nationality ? " selected" : "") . ">$cntry</option>\n";
}



echo <<<EOT

<form id="edit_new_booking_form" action="create_booking.php" method="POST" accept-charset="utf-8">
<fieldset>
<table>
	<tr><td>Name of booker: </td><td><input name="name" value="$name"> <span style="float:left;">(</span><input name="name_ext" value="$nameExt">)</td></tr>
	<tr><td>Gender: </td><td><select name="gender">
		<option value="MALE"$maleSelected>Male</option>
		<option value="FEMALE"$femaleSelected>Female</option>
	</select></td></tr>
	<tr><td>Address: </td><td><textarea name="address">$address</textarea></td></tr>
	<tr><td>Nationality: </td><td><select name="nationality">$nationalityOptions</select></td></tr>
	<tr><td>Email: </td><td><input name="email" value="$email"></td></tr>
	<tr><td>Tel: </td><td><input name="telephone" value="$tel"></td></tr>
	<tr><td colspan="2"><hr></td></tr>
	<tr><td></td><td></td><td rowspan="16" valign="top"><b>Comment: </b><br><textarea cols="60" rows="25" name="comment">$comment</textarea></td></tr>
	<tr>
		<td>First night: </td>
		<td>
			<input name="first_night" id="first_night" value="$fnight" onchange="recalculatePayment();"> <img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'first_night', 'chooserSpanFN', 2010, 2025, 'Y/m/d', false);">(format: YYYY/MM/DD)
			<div id="chooserSpanFN" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>Last night: </td>
		<td>
			<input name="last_night" id="last_night" value="$lnight" onchange="recalculatePayment();"> <img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'last_night', 'chooserSpanLN', 2010, 2025, 'Y/m/d', false);"> (format: YYYY/MM/DD)
			<div id="chooserSpanLN" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr><td>Arrival time: </td><td><input name="arrival_time" value="$arrivalTime"></td></tr>
	<tr><td><b>Rooms</b></td><td><b>Number of guests</b></td></tr>
$roomsHtml
	<tr><td>Deposit: </td><td><input name="deposit" id="deposit_field" value="$deposit"><select name="deposit_currency">$depositCurrencyOptions</select></td></tr>
	<tr><td>Total payment: </td><td><input id="room_payment_field" name="room_payment" value="$payment" disabled="true"> EUR</td></tr>
	<tr><td>Source of booking: </td><td><select name="source">
$sourceOptions
	</select></td></tr>

	<tr><td colspan="2"><hr></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="2"><input type="submit" value="Save"><input type="button" value="Cancel" onclick="window.location='$cancelUrl';"></td></tr>
</table>
</fieldset>
</form>

EOT;



mysql_close($link);

html_end();


function getOptions($max, $selected, $excludedItems = array()) {
	$options = '';
	for($i = 0; $i <= $max; $i++) {
		if(in_array($i, $excludedItems))
			continue;

		$options .= "<option value=\"$i\"" . ($i == $selected ? ' selected' : '') . ">$i</option>";
	}
	return $options;	
}


function js_encode($str) {
	$str = str_replace('"', '\u0022', $str);
	$str = str_replace('\'', '\u0027', $str);
	$str = str_replace("\n", '\n', $str);
	return $str;
}


?>
