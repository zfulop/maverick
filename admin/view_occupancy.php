<?php

require("includes.php");
require("../recepcio/room_booking.php");
require("common_booking.php");

$link = db_connect();


$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

<!--
<link rel="stylesheet" type="text/css" href="js/tooltip/themes/1/tooltip.css"/>
<script type="text/javascript" src="js/tooltip/themes/1/tooltip.js"></script>
-->

<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/opentip-native.js"></script><!-- Change to the adapter you actually use -->
<link href="opentip.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">

	function hideAlternativeOccupancy() {
		$$('a.relative_value').invoke('hide');
	}

</script>

<style>

table.stat tr td, table.stat tr th  {
    border-top: 1px solid rgb(20,20,20);
    border-collapse: collapse;
}

table.stat tr td.odd, table.stat tr th.odd  {
	background: rgb(255, 255, 255);
}

table.stat tr td.even, table.stat tr th.even  {
	background: rgb(200, 200, 200);
}

form {
	display: block;
}

table.bookings {
	border: 1px solid black;
	background: rgb(120, 240, 120);
}

a.relative_value {
	display: block;
	color: rgb(255,255,255);
}


a.absolute_value {
	display: block;
	color: rgb(255,255,255);
}

a.relative_value, a.absolute_value {
	text-decoration: none;
	color: rgb(255,255,255);
}


</style>


EOT;

if(isset($_SESSION['occupancy_start_date'])) {
	$startDate = $_SESSION['occupancy_start_date'];
} else {
	$startDate = date('Y-m') . '-01';
}

if(isset($_SESSION['occupancy_end_date'])) {
	$endDate = $_SESSION['occupancy_end_date'];
} else {
	$endDate = date('Y-m-t');
}

if(isset($_SESSION['occupancy_group_days'])) {
	$groupDays = $_SESSION['occupancy_group_days'];
} else {
	$groupDays = 7;
}

if(isset($_SESSION['occupancy_start_date_booking_rec'])) {
	$startDateBookingRec = $_SESSION['occupancy_start_date_booking_rec'];
} else {
	$startDateBookingRec = '';
}

if(isset($_SESSION['occupancy_end_date_booking_rec'])) {
	$endDateBookingRec = $_SESSION['occupancy_end_date_booking_rec'];
} else {
	$endDateBookingRec = '';
}


if(isset($_REQUEST['start_date'])) {
	$startDate = $_REQUEST['start_date'];
}

if(isset($_REQUEST['end_date'])) {
	$endDate = $_REQUEST['end_date'];
}

if(isset($_REQUEST['group_days'])) {
	$groupDays = $_REQUEST['group_days'];
}

if(isset($_REQUEST['start_date_booking_rec'])) {
	$startDateBookingRec = $_REQUEST['start_date_booking_rec'];
}

if(isset($_REQUEST['end_date_booking_rec'])) {
	$endDateBookingRec = $_REQUEST['end_date_booking_rec'];
}

$_SESSION['occupancy_start_date'] = $startDate;
$_SESSION['occupancy_end_date'] = $endDate;
$_SESSION['occupancy_group_days'] = $groupDays;
$_SESSION['occupancy_start_date_booking_rec'] = $startDateBookingRec;
$_SESSION['occupancy_end_date_booking_rec'] = $endDateBookingRec;

$groupDaysValues = array('1' => '1 day',
	'2' => '2 days',
	'3' => '3 days',
	'4' => '4 days',
	'5' => '5 days',
	'6' => '6 days',
	'7' => '1 week',
	'14' => '2 week',
	'month' => '1 month');

$groupDaysOptions = '';
foreach($groupDaysValues as $value => $display){
	$groupDaysOptions .= "		<option value=\"$value\"" . ($groupDays == $value ? ' selected' : '') . ">$display</option>";
}
$groupDaysOptions .= "		<option value=\"7\"" . ($groupDays == 7 ? ' selected' : '') . ">1 week</option>";
$groupDaysOptions .= "		<option value=\"14\"" . ($groupDays == 14 ? ' selected' : '') . ">2 weeks</option>";
$groupDaysOptions .= "		<option value=\"month\"" . ($groupDays == 'month' ? ' selected' : '') . ">1 month</option>";

html_start("Maverick Admin - Occupancy", $extraHeader, "hideAlternativeOccupancy()");


echo <<<EOT

<form id="view_occupancy" action="view_occupancy.php" method="GET">
<input type="hidden" name="action" value="occupancy">
<table class="input">
	<tr><th colspan="2">Occupancy</th></tr>
	<tr><td>Start date of occupancy check:</td><td>
		<input id="start_date" name="start_date" size="10" maxlength="10" type="text" value="$startDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2008, 2025, 'Y-m-d', false);">
		<div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>End date of occupancy check:</td><td>
		<input id="end_date" name="end_date" size="10" maxlength="10" type="text" value="$endDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date', 'chooserSpanED', 2008, 2025, 'Y-m-d', false);">
		<div id="chooserSpanED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>Group number of days</td><td><select name="group_days">
$groupDaysOptions
	</select></td></tr>
	<tr><td>Start date of booking received (optional):</td><td>
		<input id="start_date_booking_rec" name="start_date_booking_rec" size="10" maxlength="10" type="text" value="$startDateBookingRec"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date_booking_rec', 'chooserSpanSDBR', 2008, 2025, 'Y-m-d', false);">
		<div id="chooserSpanSDBR" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>End date of booking received (optional):</td><td>
		<input id="end_date_booking_rec" name="end_date_booking_rec" size="10" maxlength="10" type="text" value="$endDateBookingRec"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date_booking_rec', 'chooserSpanEDBR', 2008, 2025, 'Y-m-d', false);">
		<div id="chooserSpanEDBR" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
</table><br>
<input type="submit" value="View Statistics"><br>
</form>


<b>Absolute value</b> - the occupancy based on all the bookings for the inspected period<br>
<b>Relative value</b> - the occupancy based on the bookings that have been created within the period specified<br>
<form id="buttons">
	<input style="float: none; display: none;" type="button" id="showAbsValueBtn" value="Show absolute value" onclick="$$('a.absolute_value').invoke('show');$('hideAbsValueBtn').show();$('showAbsValueBtn').hide();return false;">
	<input style="float: none; display: block;" type="button" id="hideAbsValueBtn" value="Hide absolute value" onclick="$$('a.absolute_value').invoke('hide');$('hideAbsValueBtn').hide();$('showAbsValueBtn').show();return false;">
	<input style="float: none; display: block;" type="button" id="showRelValueBtn" value="Show relative value" onclick="$$('a.relative_value').invoke('show');$('hideRelValueBtn').show();$('showRelValueBtn').hide();return false;">
	<input style="float: none; display: none;" type="button" id="hideRelValueBtn" value="Hide relative value" onclick="$$('a.relative_value').invoke('hide');$('hideRelValueBtn').hide();$('showRelValueBtn').show();return false;">
</form>



EOT;

$startDateSlash = str_replace('-', '/', $startDate);
$endDateSlash = str_replace('-', '/', $endDate);

list($startYear, $startMonth, $startDay) = explode('-', $startDate);
list($endYear, $endMonth, $endDay) = explode('-', $endDate);

$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

$roomTypes = loadRoomTypesWithAvailableBeds($link, $startDate, $endDate);

mysql_close($link);



echo "<table class=\"stat\">\n\t<tr><td>&nbsp;</td><th>Available Beds</th>\n";

$endDateTs = strtotime($endDate);
$currDateTs = strtotime($startDate);
$cssClass = 'odd';
while($currDateTs <= $endDateTs) {
	$cssClass = ($cssClass == 'odd' ? 'even' : 'odd');
	$startPeriod = date('Y-m-d', $currDateTs);
	if($groupDays == 'month') { 
		$endPeriod = date('Y-m-d', strtotime("$startPeriod +1 month"));
	} else {
		$endPeriod = date('Y-m-d', strtotime("$startPeriod +$groupDays day"));
	}
	if($groupDays == 1) {
		echo "\t<th class=\"$cssClass\">$startPeriod</th>\n";
	} else {
		echo "\t<th class=\"$cssClass\">$startPeriod<br>-<br>$endPeriod</th>\n";
	}
	if($groupDays == 'month') {
		$currDateTs = strtotime("$startPeriod +1 month");
	} else {
		$currDateTs = strtotime("$startPeriod +$groupDays day");
	}
}
echo "</tr>\n";

foreach($roomTypes as $roomTypeId => $roomType) {
	echo "\t<tr><th>" . $roomType['name'] . "</th><td>" . $roomType['available_beds'] . "</td>";
	$currDateTs = strtotime($startDate);
	$cssClass = 'odd';
	while($currDateTs <= $endDateTs) {
		$cssClass = ($cssClass == 'odd' ? 'even' : 'odd');
		$startPeriod = date('Y-m-d', $currDateTs);
		$numOfDays = $groupDays;
		if($groupDays == 'month') { 
			$endPeriod = date('Y-m-d', strtotime("$startPeriod +1 month"));
			$numOfDays = date('t', strtotime($startPeriod));;
		} else {
			$endPeriod = date('Y-m-d', strtotime("$startPeriod +$groupDays day"));
		}
		$endPeriod = date('Y-m-d', strtotime("$endPeriod -1 day"));
		$relBookings = getBookings($roomTypeId, $rooms, $startPeriod, $endPeriod, $startDateBookingRec, $endDateBookingRec);
		$absBookings = getBookings($roomTypeId, $rooms, $startPeriod, $endPeriod);
		$relAvgNumOfBeds = getAvgNumOfBedsOccupied($relBookings, $startPeriod, $endPeriod);
		$absAvgNumOfBeds = getAvgNumOfBedsOccupied($absBookings, $startPeriod, $endPeriod);
		$relativeOccupancy = round($relAvgNumOfBeds / ($numOfDays*$roomType['available_beds']) * 100);
		$absoluteOccupancy = round($absAvgNumOfBeds / ($numOfDays*$roomType['available_beds']) * 100);
		$absRed = intval((4 - 3*$absoluteOccupancy / 100.0) * 171);
		$absGreen = intval((4 - 3*$absoluteOccupancy / 100.0) * 26);
		$absBlue = intval((4 - 3*$absoluteOccupancy / 100.0) * 11);
		$relRed = intval((4 - 3*$relativeOccupancy / 100.0) * 12);
		$relGreen = intval((4 - 3*$relativeOccupancy / 100.0) * 57);
		$relBlue = intval((4 - 3*$relativeOccupancy / 100.0) * 132);
		$htmlId = $roomTypeId . str_replace('-','',$startPeriod);
		echo "		<td class=\"$cssClass\">\n";
		echo "			<a class=\"relative_value\" style=\"background: rgb($relRed, $relGreen, $relBlue);\" href=\"view_occupancy_bookings.php?room_type_id=$roomTypeId&start_date=$startPeriod&end_date=$endPeriod&start_date_booking_rec=$startDateBookingRec&end_date_booking_rec=$endDateBookingRec\" data-ot=\"\" data-ot-group=\"tips\" data-ot-hide-trigger=\"tip\" data-ot-show-on=\"click\" data-ot-hide-on=\"click\" data-ot-fixed=\"true\" data-ot-ajax=\"true\">$relativeOccupancy %</a>\n";
		echo "			<a class=\"absolute_value\" style=\"background: rgb($absRed, $absGreen, $absBlue);\" href=\"view_occupancy_bookings.php?room_type_id=$roomTypeId&start_date=$startPeriod&end_date=$endPeriod\" data-ot=\"\" data-ot-group=\"tips\" data-ot-hide-trigger=\"tip\" data-ot-show-on=\"click\" data-ot-hide-on=\"click\" data-ot-fixed=\"true\" data-ot-ajax=\"true\">$absoluteOccupancy %</a></div>\n";

		echo "		</td>\n";
		if($groupDays == 'month') { 
			$currDateTs = strtotime("$startPeriod +1 month");
		} else {
			$currDateTs = strtotime("$startPeriod +$groupDays day");
		}

	}
	echo "</tr>\n";
}
echo "</table><br><br><br>\n";


html_end();


?>
