<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");
require("common_booking.php");

$link = db_connect();


$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

<script type="text/javascript" src="js/prototype.js"></script>

<!--
<script src="http://api.simile-widgets.org/timeline/2.3.1/timeline-api.js?bundle=true" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/tooltip/themes/1/tooltip.css"/>
<script type="text/javascript" src="js/tooltip/themes/1/tooltip.js"></script>
-->
<script type="text/javascript" src="js/opentip-native.js"></script><!-- Change to the adapter you actually use -->
<link href="opentip.css" rel="stylesheet" type="text/css" />



<style>

table.stat tr td, table.stat tr th  {
    border-top: 1px solid rgb(20,20,20);
    border-collapse: collapse;
}

table.stat tr td.weekday, table.stat tr th.weekday  {
	background: rgb(255, 255, 255);
}

table.stat tr td.weekend, table.stat tr th.weekend  {
	background: rgb(200, 200, 200);
}

table.stat tr th {
	padding: 2px 5px;
}


form {
	display: block;
}

table.bookings {
	border: 1px solid black;
	background: rgb(120, 240, 120);
}

div.relative_value {
	display: block;
	color: rgb(255,255,255);
}


div.absolute_value {
	display: block;
	color: rgb(255,255,255);
}

div.relative_value a, div.absolute_value a {
	text-decoration: none;
	color: rgb(255,255,255);
}

table.bookings tr td.left_aligned {
	text-align: right;
	padding-right: 10px;
}


</style>


EOT;

if(isset($_SESSION['pricing_start_date'])) {
	$startDate = $_SESSION['pricing_start_date'];
} else {
	$startDate = date('Y-m') . '-01';
}

if(isset($_SESSION['pricing_end_date'])) {
	$endDate = $_SESSION['pricing_end_date'];
} else {
	$endDate = date('Y-m-d', strtotime($startDate . " +13 day"));
}

if(isset($_REQUEST['start_date'])) {
	$startDate = $_REQUEST['start_date'];
}

if(isset($_REQUEST['end_date'])) {
	$endDate = $_REQUEST['end_date'];
}

$_SESSION['pricing_start_date'] = $startDate;
$_SESSION['pricing_end_date'] = $endDate;


$startDateSlash = str_replace('-', '/', $startDate);
$endDateSlash = str_replace('-', '/', $endDate);

list($startYear, $startMonth, $startDay) = explode('-', $startDate);
list($endYear, $endMonth, $endDay) = explode('-', $endDate);

$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

$roomTypes = loadRoomTypesWithAvailableBeds($link, $startDate, $endDate);

$roomTypesHtmlOptions = '';
foreach($roomTypes as $roomTypeId => $oneRoomType) {
	$roomTypesHtmlOptions .= '		<option value="' . $oneRoomType['id'] . '">' . $oneRoomType['name'] . "</option>\n";
}

mysql_close($link);


html_start("Maverick Admin - Pricing", $extraHeader);

if(!isset($_SESSION['room_price_start_year'])) {
	$_SESSION['room_price_start_year'] = date('Y');
}
if(!isset($_SESSION['room_price_start_month'])) {
	$_SESSION['room_price_start_month'] = date('m');
}
if(!isset($_SESSION['room_price_start_day'])) {
	$_SESSION['room_price_start_day'] = date('d');
}
if(!isset($_SESSION['room_price_end_year'])) {
	$_SESSION['room_price_end_year'] = date('Y');
}
if(!isset($_SESSION['room_price_end_month'])) {
	$_SESSION['room_price_end_month'] = date('m');
}
if(!isset($_SESSION['room_price_end_day'])) {
	$_SESSION['room_price_end_day'] = date('d');
}
if(!isset($_SESSION['room_price_days'])) {
	$_SESSION['room_price_days'] = array(1,2,3,4,5,6,7);
}



$startYearOptions = '';
for($y = date('Y'); $y < date('Y') + 3; $y++) {
	$startYearOptions .= "	<option value=\"$y\"" . ($y == $_SESSION['room_price_start_year'] ? ' selected' : '') . ">$y</option>\n";
}
$startMonthOptions = '';
for($m = 0; $m <= 12; $m++) {
	$month  = ($m < 10 ? '0' . $m : $m);
	$startMonthOptions .= "	<option value=\"$month\"" . ($month == $_SESSION['room_price_start_month'] ? ' selected' : '') . ">$month</option>\n";
}
$startDayValue = $_SESSION['room_price_start_day'];

$endYearOptions = '';
for($y = date('Y'); $y < date('Y') + 3; $y++) {
	$endYearOptions .= "	<option value=\"$y\"" . ($y == $_SESSION['room_price_end_year'] ? ' selected' : '') . ">$y</option>\n";
}
$endMonthOptions = '';
for($m = 0; $m <= 12; $m++) {
	$month  = ($m < 10 ? '0' . $m : $m);
	$endMonthOptions .= "	<option value=\"$month\"" . ($month == $_SESSION['room_price_end_month'] ? ' selected' : '') . ">$month</option>\n";
}
$endDayValue = $_SESSION['room_price_end_day'];

$monChecked = in_array(1, $_SESSION['room_price_days']) ? 'checked' : '';
$tueChecked = in_array(2, $_SESSION['room_price_days']) ? 'checked' : '';
$wedChecked = in_array(3, $_SESSION['room_price_days']) ? 'checked' : '';
$thuChecked = in_array(4, $_SESSION['room_price_days']) ? 'checked' : '';
$friChecked = in_array(5, $_SESSION['room_price_days']) ? 'checked' : '';
$satChecked = in_array(6, $_SESSION['room_price_days']) ? 'checked' : '';
$sunChecked = in_array(7, $_SESSION['room_price_days']) ? 'checked' : '';


echo <<<EOT

<form id="price_btn">
<input type="button" onclick="document.getElementById('price_form').reset();document.getElementById('price_form').style.display='block'; document.getElementById('price_btn').style.display='none'; return false;" value="Set price for a room type">
</form>
<br>


<form action="save_room_prices.php" method="POST" style="display: none;" id="price_form">
<table style="border: 1px solid rgb(0,0,0);">
<tr><th colspan="2">Set price of a room for a date interval.</strong></th></tr>
<tr><td colspan="2">To delete special price, set the date and leave the price field empty.</td></tr>
<tr><td>Room type: </td><td><select style="display: inline; float: none;" name="room_type_id">
$roomTypesHtmlOptions
</select></td></tr>
<tr><td>Start date: </td><td><select style="display: inline; float: none;" name="start_year">
$startYearOptions
</select>/<select style="display: inline; float: none;" name="start_month">
$startMonthOptions
</select>/<input name="start_day" value="$startDayValue" size="2" style="display: inline; float: none;"></td></tr>
<tr><td>End date: </td><td><select style="display: inline; float: none;" name="end_year">
$endYearOptions
</select>/<select style="display: inline; float: none;" name="end_month">
$endMonthOptions
</select>/<input name="end_day" value="$endDayValue" size="2" style="display: inline; float: none;"></td></tr>
<tr><td>Days</td><td>
	<div>Mon <input style="float: left; display: block;" type="checkbox" name="days[]" value="1" $monChecked></div>
	<div>Tue <input style="float: left; display: block;" type="checkbox" name="days[]" value="2" $tueChecked></div>
	<div>Wed <input style="float: left; display: block;" type="checkbox" name="days[]" value="3" $wedChecked></div>
	<div>Thu <input style="float: left; display: block;" type="checkbox" name="days[]" value="4" $thuChecked></div>
	<div>Fri <input style="float: left; display: block;" type="checkbox" name="days[]" value="5" $friChecked></div>
	<div>Sat <input style="float: left; display: block;" type="checkbox" name="days[]" value="6" $satChecked></div>
	<div>Sun <input style="float: left; display: block;" type="checkbox" name="days[]" value="7" $sunChecked></div>
</td></tr>
<tr><td>Bed or Room Price: </td><td><input name="price" size="4"></td></tr>
<tr><td colspan="2">
	<input type="submit" value="Set price(s)">
	<input type="button" onclick="document.getElementById('price_form').style.display='none'; document.getElementById('price_btn').style.display='block'; return false;" value="Cancel">
</td></tr>
</table>
</form>
<br>
<br>



<form action="view_pricing.php" method="GET" style="border: 1px solid black; float: left; padding: 10px; margin: 10px;">
<input type="hidden" name="action" value="pricing">
<table class="input">
	<tr><th colspan="2">Pricing</th></tr>
	<tr><td>Start date of pricing check:</td><td>
		<input id="start_date" name="start_date" size="10" maxlength="10" type="text" value="$startDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2008, 2025, 'Y-m-d', false);">
		<div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>End date of pricing check:</td><td>
		<input id="end_date" name="end_date" size="10" maxlength="10" type="text" value="$endDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date', 'chooserSpanED', 2008, 2025, 'Y-m-d', false);">
		<div id="chooserSpanED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
</table><br>
<input type="submit" value="View Pricing"><br>
</form>

<form action="view_price_changes_for_day.php" method="GET" style="border: 1px solid black; float: left; padding: 10px; margin: 10px;">
<table class="input">
	<tr><th colspan="2">View price changes for a day</th></tr>
	<tr><td>Date of price change:</td><td>
		<input id="price_change_date" name="price_change_date" size="10" maxlength="10" type="text" value="$startDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'price_change_date', 'chooserSpanDC', 2008, 2025, 'Y-m-d', false);">
		<div id="chooserSpanDC" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
</table><br>
<input type="submit" value="View price change for the day"><br>
</form>

<div style="clear: both;"></div>
EOT;


echo "<table><tr>";
for($i = 0; $i <= 100; $i += 10) {
	$red = intval((4 - 3*$i / 100.0) * 171);
	$green = intval((4 - 3*$i / 100.0) * 26);
	$blue = intval((4 - 3*$i / 100.0) * 11);
	echo "<td style=\"background: rgb($red, $green, $blue); color:white;\">$i%</td>";
}
echo "</tr></table>\n";

echo "<form action=\"save_room_prices_from_table.php\" method=\"post\" style=\"padding: 0; margin: 0;\" >\n";

echo "<table class=\"stat\">\n\t<tr>\n\t\t<td>&nbsp;</td><th>Available Beds</th>\n";

$endDateTs = strtotime($endDate);
$currDateTs = strtotime($startDate);
while($currDateTs <= $endDateTs) {
	$currDate = date('Y-m-d', $currDateTs);
	$currDay = date('D', $currDateTs);
	$cssClass = '';
	$cssClass = 'weekday';
	if(date('N', $currDateTs) > 5) {
		$cssClass = 'weekend';
	}
	echo "\t\t<th class=\"$cssClass\">$currDate<br>$currDay</th>\n";
	$currDateTs = strtotime("$currDate +1 day");
}
echo "\t</tr>\n";

foreach($roomTypes as $roomTypeId => $roomType) {
	echo "\t<tr><th>" . $roomType['name'] . "</th><td>" . $roomType['available_beds'] . "</td>";
	$currDateTs = strtotime($startDate);
	$cssClass = 'odd';
	while($currDateTs <= $endDateTs) {
		$cssClass = ($cssClass == 'odd' ? 'even' : 'odd');
		$currDate = date('Y-m-d', $currDateTs);
		list($currYear, $currMonth, $currDay) = explode('-', $currDate);
		$bookings = getBookings($roomTypeId, $rooms, $currDate, $currDate);
		if($roomType['type'] == 'DORM') {
			$avgNumOfBeds = getAvgNumOfBedsOccupied($bookings, $currDate, $currDate);
			$occupancy = round($avgNumOfBeds / $roomType['available_beds'] * 100);
		} else {
			$numOfRoomsBooked = count($bookings);
			if($roomType['num_of_rooms'] < 1) {
				$occupancy = 0;
			} else {
				$occupancy = round($numOfRoomsBooked / $roomType['num_of_rooms'] * 100);
			}
		}
		$red = intval((4 - 3*$occupancy / 100.0) * 171);
		$green = intval((4 - 3*$occupancy / 100.0) * 26);
		$blue = intval((4 - 3*$occupancy / 100.0) * 11);
		$price = admin_getRoomPrice($currDate, $rooms, $roomType);
		$roomTypeDump = print_r($roomType, true);
		$color = 'black';
		if($occupancy >= 100) {
			$color = 'white';
		}
		$dpbHtml = '';
		if(isApartment($roomType)) {
			$room = findRoom($rooms, $roomTypeId);
			$dpbValue = getDiscountPerBed($currYear, $currMonth, $currDay, $room);
			$dpbHtml = ", <input name=\"dpb$roomTypeId|$currYear-$currMonth-$currDay\" value=\"$dpbValue\" style=\"float: none; display: inline; font-size=70%; width: 25px; height: 20px;\" >%";
		}
		echo <<<EOT
		<td class="$cssClass" style="background: rgb($red, $green, $blue); color: $color;">
			<!-- $roomTypeDump -->
			<div style="float: right; font-size: 60%;">$occupancy%</div>
			<div class="absolute_value" ><a href="view_pricing_detail.php?room_type_id=$roomTypeId&date=$currDate" data-ot="" data-ot-group="tips" data-ot-hide-trigger="tip" data-ot-show-on="click" data-ot-hide-on="click" data-ot-fixed="true" data-ot-ajax="true">$price &#8364;</a><a href="#" style="font-size: 70%;" onclick="$('setprice_$roomTypeId$currDate').show();return false;">▼</a></div>
			<div id="setprice_$roomTypeId$currDate" style="display: none;"><input name="$roomTypeId|$currYear-$currMonth-$currDay" style="float: none; display: inline; font-size=70%; width: 25px; height: 20px;">&#8364;$dpbHtml</div>
		</td>

EOT;
		$currDateTs = strtotime("$currDate +1 day");

	}
	echo "\t</tr>\n";
}
echo "</table><br>\n";
echo "<input type=\"submit\" value=\"Save prices\"></form><br><br><br>\n";


html_end();

function findRoom(&$rooms, $roomTypeId) {
	$selectedRoom = null;
	foreach($rooms as $roomId => $roomData) {
		if($roomData['room_type_id'] == $roomTypeId) {
			$selectedRoom = $roomData;
			break;
		}
	}
	return $selectedRoom;
}


function admin_getRoomPrice($currDate, &$rooms, &$roomType) {
	$currDateTs = strtotime($currDate);
	$selectedRoom = findRoom($rooms, $roomType['id']);
	$price = 0;
	if(!is_null($selectedRoom)) {
		$year = date('Y', $currDateTs);
		$month = date('m', $currDateTs);
		$day = date('d', $currDateTs);
		if(isDorm($roomType)) {
			$price = getBedPrice($year, $month, $day, $selectedRoom);
		} else {
			$price = getRoomPrice($year, $month, $day, $selectedRoom);
		}
	}
	return $price;

}

?>
