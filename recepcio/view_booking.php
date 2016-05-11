<?php

require("includes.php");
require("room_booking.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}




foreach($_SESSION as $code => $val) {
	if(substr($code, 0, 3) == 'EB_') {
		unset($_SESSION[$code]);
	}
}

$fromDate = date('Y-m-d');
$toDate = date('Y-m-d');
$bookedAfterDate = null;
$bookedBeforeDate = null;
$name = '';
$source = '';
$bookingRef = '';
$confirmedSelected = false;
$confirmed = false;
$guestCancelledSelected = false;
$guestCancelled = false;
$recCancelledSelected = false;
$recCancelled = false;
$noShowCancelledSelected = false;
$noShowCancelled = false;
$checkedinSelected = false;
$checkedin = false;
$paidSelected = false;
$paid = false;


if(isset($_REQUEST['new_search'])) {
	$fromDate = $_REQUEST['from_date'];
	$toDate = $_REQUEST['to_date'];
	$bookedAfterDate = $_REQUEST['booked_after_date'];
	$bookedBeforeDate = $_REQUEST['booked_before_date'];
	$name = $_REQUEST['booker_name'];
	$source = $_REQUEST['source'];
	$bookingRef = $_REQUEST['booking_ref'];
	$confirmedSelected = isset($_REQUEST['confirmed_selected']);
	$confirmed = isset($_REQUEST['confirmed']);
	$guestCancelledSelected = isset($_REQUEST['guest_cancelled_selected']);
	$guestCancelled = isset($_REQUEST['guest_cancelled']);
	$recCancelledSelected = isset($_REQUEST['rec_cancelled_selected']);
	$recCancelled = isset($_REQUEST['rec_cancelled']);
	$noShowCancelledSelected = isset($_REQUEST['noshow_cancelled_selected']);
	$noShowCancelled = isset($_REQUEST['noshow_cancelled']);
	$checkedinSelected = isset($_REQUEST['checkedin_selected']);
	$checkedin = isset($_REQUEST['checkedin']);
	$paidSelected = isset($_REQUEST['paid_selected']);
	$paid = isset($_REQUEST['paid']);
	$_SESSION['view_booking_param_set'] = true;
	$_SESSION['view_booking_from_date'] = $fromDate;
	$_SESSION['view_booking_to_date'] = $toDate;
	$_SESSION['view_booking_booked_after_date'] = $bookedAfterDate;
	$_SESSION['view_booking_booked_before_date'] = $bookedBeforeDate;
	$_SESSION['view_booking_booker_name'] = $name;
	$_SESSION['view_booking_booker_source'] = $source;
	$_SESSION['view_booking_booking_ref'] = $bookingRef;
	$_SESSION['view_booking_confirmed_selected'] = $confirmedSelected;
	$_SESSION['view_booking_confirmed'] = $confirmed;
	$_SESSION['view_booking_guest_cancelled_selected'] = $guestCancelledSelected;
	$_SESSION['view_booking_guest_cancelled'] = $guestCancelled;
	$_SESSION['view_booking_rec_cancelled_selected'] = $recCancelledSelected;
	$_SESSION['view_booking_rec_cancelled'] = $recCancelled;
	$_SESSION['view_booking_noshow_cancelled_selected'] = $noShowCancelledSelected;
	$_SESSION['view_booking_noshow_cancelled'] = $noShowCancelled;
	$_SESSION['view_booking_checkedin_selected'] = $checkedinSelected;
	$_SESSION['view_booking_checkedin'] = $checkedin;
	$_SESSION['view_booking_paid_selected'] = $paidSelected;
	$_SESSION['view_booking_paid'] = $paid;
} elseif(isset($_SESSION['view_booking_param_set'])) {
	$fromDate = $_SESSION['view_booking_from_date'];
	$toDate = $_SESSION['view_booking_to_date'];
	$bookedAfterDate = $_SESSION['view_booking_booked_after_date'];
	$bookedBeforeDate = $_SESSION['view_booking_booked_before_date'];
	$name = $_SESSION['view_booking_booker_name'];
	$source = $_SESSION['view_booking_booker_source'];
	$bookingRef = $_SESSION['view_booking_booking_ref'];
	$confirmedSelected = $_SESSION['view_booking_confirmed_selected'];
	$confirmed = $_SESSION['view_booking_confirmed'];
	$guestCancelledSelected = $_SESSION['view_booking_guest_cancelled_selected'];
	$guestCancelled = $_SESSION['view_booking_guest_cancelled'];
	$recCancelledSelected = $_SESSION['view_booking_rec_cancelled_selected'];
	$recCancelled = $_SESSION['view_booking_rec_cancelled'];
	$noShowCancelledSelected = $_SESSION['view_booking_noshow_cancelled_selected'];
	$noShowCancelled = $_SESSION['view_booking_noshow_cancelled'];
	$checkedinSelected = $_SESSION['view_booking_checkedin_selected'];
	$checkedin = $_SESSION['view_booking_checkedin'];
	$paidSelected = $_SESSION['view_booking_paid_selected'];
	$paid = $_SESSION['view_booking_paid'];
}

$confirmChecked = $confirmedSelected ? 'checked' : '';
$guestCancelChecked = $guestCancelledSelected ? 'checked' : '';
$recCancelChecked = $recCancelledSelected ? 'checked' : '';
$noShowCancelChecked = $noShowCancelledSelected ? 'checked' : '';
$checkinChecked = $checkedinSelected ? 'checked' : '';
$paidChecked = $paidSelected ? 'checked' : '';

$confirmValueChecked = $confirmed ? 'checked' : '';
$guestCancelValueChecked = $guestCancelled ? 'checked' : '';
$recCancelValueChecked = $recCancelled ? 'checked' : '';
$noShowCancelValueChecked = $noShowCancelled ? 'checked' : '';
$checkinValueChecked = $checkedin ? 'checked' : '';
$paidValueChecked = $paid ? 'checked' : '';

$cancelSelected = ($noShowCancelledSelected or $recCancelledSelected or $guestCancelledSelected);


$link = db_connect();


$sql = "SELECT DISTINCT source FROM booking_descriptions ORDER BY source";
$result = mysql_query($sql, $link);
$sourceOptions = '';
while($row = mysql_fetch_assoc($result)) {
	$sourceOptions .= '<option value="' . $row['source'] . '"' . ($row['source'] == $source ? ' selected' : '') . '>' . $row['source'] . '</option>';
}

$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->



<script type="text/javascript">


	function updateSearchField(controlFieldId, labelFieldId, inputFieldId) {
		var cselected = document.getElementById(controlFieldId);
		if(cselected.checked) {
			document.getElementById(labelFieldId).style.color = '#000000';
			document.getElementById(inputFieldId).disabled = false;
		} else {
			document.getElementById(labelFieldId).style.color = '#aaaaaa';
			document.getElementById(inputFieldId).disabled = true;
		}
	}

</script>

<style>

.rtOption {
  font-size: 120%;
}

.rOption {
  padding-left: 10px;
}

</style>


EOT;

$roomsHtmlOptions = '';
$sql = "SELECT r.id, r.name as room_name, rt.name as room_type_name FROM rooms r INNER JOIN room_types rt on r.room_type_id=rt.id ORDER BY rt.name, r.name";
$result = mysql_query($sql, $link);
$rtName = null;
while($row = mysql_fetch_assoc($result)) {
	if($rtName != $row['room_type_name']) {
		$rtName = $row['room_type_name'];
		$roomsHtmlOptions .= "<option disabled=\"true\" class=\"rtOption\">$rtName</option>\n";
	}
	$roomsHtmlOptions .= "<option class=\"rOption\" value=\"" . $row['id'] . "\">" . $row['room_name'] . "</option>\n";
}



html_start("Booking", $extraHeader);

$thisyear = date('Y');

$monthOptions = '';
for($i = 1; $i <= 12; $i++) {
	$m = $i;
	if(strlen($m) < 2)
		$m = '0' . $m;
	$monthOptions .= "			<option value=\"$m\">" . date('M', mktime(1,1,1, $i, 1, 2000)) . "</option>\n";
}

$syncStartDate = date('Y-m-d');
$syncEndDate = date('Y-m-d', strtotime($syncStartDate . ' +7 day'));


echo <<<EOT


<form action="view_booking.php" method="POST" style="float: left;">
<input type="hidden" name="new_search" value="true">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="3">Search bookings</th></tr>
	<tr>
		<td>&nbsp;</td><td>Guest stays from:</td>
		<td>
			<input id="sb_from_date" name="from_date" value="$fromDate" size="10" maxlength="10" type="text" value=""><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'sb_from_date', 'chooserSpanSBF', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSBF" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td><td>Guest stays to:</td>
		<td>
			<input id="sb_to_date" name="to_date" value="$toDate" size="10" maxlength="10" type="text" value=""><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'sb_to_date', 'chooserSpanSBT', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSBT" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td><td>Reservation booked on or after:</td>
		<td>
			<input id="sb_booked_after_date" name="booked_after_date" value="$bookedAfterDate" size="10" maxlength="10" type="text" value=""><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'sb_booked_after_date', 'chooserSpanSBBA', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSBBA" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td><td>Reservation booked before:</td>
		<td>
			<input id="sb_booked_before_date" name="booked_before_date" value="$bookedBeforeDate" size="10" maxlength="10" type="text" value=""><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'sb_booked_before_date', 'chooserSpanSBBB', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSBBB" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr><td>&nbsp;</td><td>Name of booker:</td><td><input name="booker_name" value="$name"></td></tr>
	<tr><td>&nbsp;</td><td>Source:</td><td><select name="source">$sourceOptions</select></td></tr>
	<tr><td>&nbsp;</td><td>Booking ref:</td><td><input name="booking_ref" value="$bookingRef"></td></tr>
	<tr><td><input type="checkbox" name="confirmed_selected" value="1" $confirmChecked id="confirmed_selected" onchange="updateSearchField('confirmed_selected', 'confirmed_label', 'confirmed_input');"><td id="confirmed_label" style="color: #aaaaaa;">Confirmed:</td><td><input type="checkbox"  id="confirmed_input" disabled="true" name="confirmed" $confirmValueChecked value="1"></td></tr>
	<tr><td><input type="checkbox" name="guest_cancelled_selected" $guestCancelChecked value="1" id="guest_cancelled_selected" onchange="updateSearchField('guest_cancelled_selected', 'guest_cancelled_label', 'guest_cancelled_input');"><td id="guest_cancelled_label" style="color: #aaaaaa;">Guest cancelled:</td><td><input type="checkbox"  id="guest_cancelled_input" disabled="true" name="guest_cancelled" value="1" $guestCancelValueChecked ></td></tr>
	<tr><td><input type="checkbox" name="rec_cancelled_selected" $recCancelChecked value="1" id="rec_cancelled_selected" onchange="updateSearchField('rec_cancelled_selected', 'rec_cancelled_label', 'rec_cancelled_input');"><td id="rec_cancelled_label" style="color: #aaaaaa;">Reception cancelled:</td><td><input type="checkbox"  id="rec_cancelled_input" disabled="true" name="rec_cancelled" value="1" $recCancelValueChecked ></td></tr>
	<tr><td><input type="checkbox" name="noshow_cancelled_selected" $noShowCancelChecked value="1" id="noshow_cancelled_selected" onchange="updateSearchField('noshow_cancelled_selected', 'noshow_cancelled_label', 'noshow_cancelled_input');"><td id="noshow_cancelled_label" style="color: #aaaaaa;">No Show:</td><td><input type="checkbox"  id="noshow_cancelled_input" disabled="true" name="noshow_cancelled" value="1" $noShowCancelValueChecked ></td></tr>
	<tr><td><input type="checkbox" name="checkedin_selected" $checkinChecked value="1" id="checkedin_selected" onchange="updateSearchField('checkedin_selected', 'checkedin_label', 'checkedin_input');"><td id="checkedin_label" style="color: #aaaaaa;">Checked in:</td><td><input type="checkbox"  id="checkedin_input" disabled="true" name="checkedin" value="1" $checkinValueChecked></td></tr>
	<tr><td><input type="checkbox" name="paid_selected" value="1" $paidChecked id="paid_selected" onchange="updateSearchField('paid_selected', 'paid_label', 'paid_input');"><td id="paid_label" style="color: #aaaaaa;">Paid:</td><td><input type="checkbox"  id="paid_input" disabled="true" name="paid" value="1" $paidValueChecked></td></tr>
	<tr><td colspan="3"><input type="submit" value="Search"></td></tr>

</table>
</form>



<form action="edit_new_booking.php" method="POST" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="4">Create new booking</th></tr>
	<tr><td colspan="4"><input type="submit" value="New"></td></tr>

</table>
</form>


<form action="view_rearrange_bookings.php" method="GET" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Rearrange bookings</th></tr>
	<tr>
		<td>From: </td>
		<td>
			<input id="start_date2" name="start_date" size="10" maxlength="10" type="text" value="$syncStartDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date2', 'chooserSpanSD2', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSD2" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>To: </td>
		<td>
			<input id="end_date2" name="end_date" size="10" maxlength="10" type="text" value="$syncEndDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date2', 'chooserSpanED2', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanED2" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="View...">
	</td></tr>
</table>
</form>



<form action="lock_rooms.php" method="GET" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Lock rooms (make them unavailable)</th></tr>
	<tr>
		<td>Rooms: </td>
		<td>
			<select name="rooms[]" style="height: 150px;" multiple="multiple">
$roomsHtmlOptions
			</select>
		</td>
	</tr>
	<tr>
		<td>First night: </td>
		<td>
			<input id="start_date3" name="first_night" size="10" maxlength="10" type="text"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date3', 'chooserSpanSD3', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSD3" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>Last night: </td>
		<td>
			<input id="end_date3" name="last_night" size="10" maxlength="10" type="text"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date3', 'chooserSpanED3', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanED3" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="Lock rooms">
	</td></tr>
</table>
</form>




<div style="clear: both;">
</div>

EOT;

if(isset($_REQUEST['order'])) {
	$_SESSION['view_booking_order'] = $_REQUEST['order'];
} elseif(!isset($_SESSION['view_booking_order'])) {
	$_SESSION['view_booking_order'] = 'source';
}
$order = $_SESSION['view_booking_order'];


$sql = "SELECT bd.name, bd.source, bd.first_night, bd.num_of_nights, bd.last_night, bd.confirmed, bd.email, bd.telephone, bd.nationality, bd.cancelled, bd.cancel_type, bd.checked_in, bd.paid, bd.booking_ref, bd.my_allocator_id, b.*, r.name AS room_name FROM bookings b INNER JOIN booking_descriptions bd ON b.description_id=bd.id INNER JOIN rooms r ON r.id=b.room_id WHERE 1=1";
$searchFor = '';
if(strlen($fromDate) > 0) {
	$sql .= " AND bd.last_night>='" . str_replace('-', '/', $fromDate) . "'";
	$searchFor .= "<br>From date: $fromDate";
}
if(strlen($toDate) > 0) {
	$sql .= " AND bd.first_night<='" . str_replace('-', '/', $toDate) . "'";
	$searchFor .= "<br>To date: $toDate";
}
if(strlen($bookedAfterDate) > 0) {
	$sql .= " AND bd.create_time>'$bookedAfterDate'";
	$searchFor .= "<br>Booked on or after date: $bookedAfterDate";
}
if(strlen($bookedBeforeDate) > 0) {
	$sql .= " AND bd.create_time<'$bookedBeforeDate'";
	$searchFor .= "<br>Booked before date: $bookedBeforeDate";
}
if(strlen(trim($source)) > 0) {
	$sql .= " AND bd.source LIKE '%" . $source . "%'";
	$searchFor .= "<br>Source contains: $source";
}
if(strlen(trim($name)) > 0) {
	$name = str_replace(',', ' ', $name);
	$name = str_replace('.', ' ', $name);
	foreach(explode(' ', $name) as $namePart) {
		$sql .= " AND bd.name LIKE '%" . $namePart . "%'";
	}
	$searchFor .= "<br>Name contains: $name";
}
if(strlen(trim($bookingRef)) > 0) {
	$sql .= " AND (bd.booking_ref='$bookingRef' OR bd.my_allocator_id='$bookingRef')";
	$searchFor .= "<br>booking ref is: $bookingRef";
}
if($confirmedSelected) {
	$sql .= " AND bd.confirmed=" . ($confirmed ? 1 : 0);
	$searchFor .= "<br>" . ($confirmed ? '' : 'not ') . "confirmed";
}
if($cancelSelected) {
	$ors = array();
	if($guestCancelledSelected) {
		$ors[] = "(bd.cancelled=" . ($guestCancelled ? 1 : 0) . " AND bd.cancel_type='guest')";
		$searchFor .= "<br>" . ($guestCancelled ? '' : 'not ') . "guest cancelled";
	}
	if($recCancelledSelected) {
		$ors[] = "(bd.cancelled=" . ($recCancelled ? 1 : 0) . " AND bd.cancel_type='reception')";
		$searchFor .= "<br>" . ($recCancelled ? '' : 'not ') . "reception cancelled";
	}
	if($noShowCancelledSelected) {
		$ors[] = "(bd.cancelled=" . ($noShowCancelled ? 1 : 0) . " AND bd.cancel_type='no_show')";
		$searchFor .= "<br>" . ($noShowCancelled ? '' : 'not ') . "no show";
	}
	if(count($ors) > 0) {
		$sql .= " AND (" . implode(' OR ',$ors) . ")";
	}
}
if($checkedinSelected) {
	$sql .= " AND bd.checked_in=" . ($checkedin ? 1 : 0);
	$searchFor .= "<br>" . ($checkedin ? '' : 'not ') . "checked in";
}
if($paidSelected) {
	$sql .= " AND bd.paid=" . ($paid ? 1 : 0);
	$searchFor .= "<br>" . ($paid ? '' : 'not ') . "paid";
}



$sql .= " ORDER BY $order,bd.id";
$searchSql = $sql;

$result = mysql_query($sql, $link);
$cnt = 0;
$bookings = array();
$bookingDescrIds = array();
$guestDataByBookingDescrId = array();
if(!$result) {
	trigger_error("Cannot get bookings in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	$cnt = mysql_num_rows($result);
	$rowsCell = null;
	$bookingCounter = 1;
	while($row = mysql_fetch_assoc($result)) {
		$row['rows'] = 1;
		if(!in_array($row['description_id'], $bookingDescrIds)) {
			$bookingDescrIds[] = $row['description_id'];
			$guestDataByBookingDescrId[$row['description_id']] = array();
			if(!is_null($rowsCell)) {
				$rowsCell = $bookingCounter;
			}
			$rowsCell = &$row['rows'];
			$bookingCounter = 0;
		}
		$bookings[] = $row;
		$bookingCounter += 1;
	}
	if(!is_null($rowsCell)) {
		$rowsCell = $bookingCounter;
	}
}

if(count($bookingDescrIds) > 0) {
	$sql = "SELECT * FROM booking_guest_data WHERE booking_description_id IN (" . implode(',', $bookingDescrIds) . ") ORDER BY name";
	$result = mysql_query($sql, $link);
	while($row = mysql_fetch_assoc($result)) {
		$guestDataByBookingDescrId[$row['booking_description_id']][] = $row;
	}
}

echo <<<EOT
<br><br>

<b>Search criteria:</b> 
$searchFor
<br>
<br>

There are $cnt bookings<br>
<table border="1">

EOT;


if($order == 'source') {
	$sourceTitle = '<a href="view_booking.php?order=source" style="font-size: 130%;">Source</a>';
} else {
	$sourceTitle = '<a href="view_booking.php?order=source">Source</a>';
}
if($order == 'first_night') {
	$dateTitle = '<a href="view_booking.php?order=first_night" style="font-size: 130%;">Date</a>';
} else {
	$dateTitle = '<a href="view_booking.php?order=first_night">Date</a>';
}
if($order == 'num_of_nights') {
	$nightsTitle = '<a href="view_booking.php?order=num_of_nights" style="font-size: 130%;">Nights</a>';
} else {
	$nightsTitle = '<a href="view_booking.php?order=num_of_nights">Nights</a>';
}
if($order == 'name') {
	$nameTitle = '<a href="view_booking.php?order=name" style="font-size: 130%;">Name</a>';
} else {
	$nameTitle = '<a href="view_booking.php?order=name">Name</a>';
}
if($order == 'nationality') {
	$nationalityTitle = '<a href="view_booking.php?order=nationality" style="font-size: 130%;">Nationality</a>';
} else {
	$nationalityTitle = '<a href="view_booking.php?order=nationality">Nationality</a>';
}
if($order == 'email') {
	$emailTitle = '<a href="view_booking.php?order=email" style="font-size: 130%;">Email</a>';
} else {
	$emailTitle = '<a href="view_booking.php?order=email">Email</a>';
}
if($order == 'telephone') {
	$telTitle = '<a href="view_booking.php?order=telephone" style="font-size: 130%;">Telephone</a>';
} else {
	$telTitle = '<a href="view_booking.php?order=telephone">Telephone</a>';
}


if($cnt > 0)
	echo "	<tr><th>$sourceTitle</th><th>$dateTitle</th><th>$nightsTitle</th><th>$nameTitle</th><th>Guest data</th><th>$nationalityTitle</th><th>$emailTitle</th><th>$telTitle</th><th>Room Name</th><th># of guests</th><th>Room payment</th><th>Status</th><th>Booking Ref</th><th>Actions</th><th></th></tr>\n";
else
	echo "	<tr><td><i>No record found.</i></td></tr>\n";

$prevDescrId = null;
$bgColor = "rgb(240, 240, 240)";
foreach($bookings as $toShow) {
	$newBooking = false;
	if($toShow['description_id'] != $prevDescrId) {
		$newBooking = true;
		$bgColor = ($bgColor == "rgb(240, 240, 240)" ? "rgb(220, 220, 220)" : "rgb(240, 240, 240)");
		$prevDescrId = $toShow['description_id'];
	}

	$style = "background: $bgColor;";
	if($toShow['confirmed']) {
		$style .= "font-weight: bold;";
	}
	$guestData = '';
	foreach($guestDataByBookingDescrId[$toShow['description_id']] as $oneGD) {
		$guestData .= $oneGD['name'] . " " . "<a href=\"mailto:" . $oneGD['email'] . "\">" . $oneGD['email'] . "</a><br>";
	}
	echo "	<tr style=\"$style\">";
	if($newBooking) {
		$rows = $toShow['rows'];
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['source'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['first_night'] . " - " . $toShow['last_night'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['num_of_nights'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['name'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\" style=\"font-size: 70%;\">$guestData</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['nationality'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['email'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['telephone'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['room_name'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['num_of_person'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['room_payment'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\"><ul>\n";
		if($toShow['confirmed'])
			echo "			<li>confirmed</li>\n";
		if($toShow['cancelled'])
			echo "			<li>" . $toShow['cancel_type'] . " cancelled</li>\n";
		if($toShow['checked_in'])
			echo "			<li>checked in</li>\n";
		if($toShow['paid'])
			echo "			<li>paid</li>\n";
		echo "		</ul></td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['booking_ref'] . ' ' . $toShow['my_allocator_id'] . "</td>\n";
		$descrId = $toShow['description_id'];
		echo "		<td rowspan=\"$rows\" valign=\"middle\"><ul>\n";
		echo "			<li><a href=\"edit_booking.php?description_id=$descrId\">Edit...</a></li>\n";
		if(!$toShow['cancelled']) {
			echo "			<li><a href=\"#\" onclick=\"if(confirm('Are you sure to cancel the booking?')) { window.location='cancel_booking.php?description_id=$descrId';} return false;\">Cancel</a></li>\n";
			if($toShow['confirmed']) {
				echo "			<li><a href=\"unconfirm_booking.php?description_id=$descrId\">Unconfirm</a></li>\n";
			} else {
				echo "			<li><a href=\"confirm_booking.php?description_id=$descrId\">Confirm</a></li>\n";
			}
			if($toShow['checked_in']) {
				echo "			<li><a href=\"checkout_booking.php?description_id=$descrId\">Checkout</a></li>\n";
			} else {
				echo "			<li><a href=\"checkin_booking.php?description_id=$descrId\">Checkin</a></li>\n";
			}
			if($toShow['paid']) {
				echo "			<li><a href=\"not_paid_booking.php?description_id=$descrId\">Not paid</a></li>\n";
			} else {
				echo "			<li><a href=\"paid_booking.php?description_id=$descrId\">Paid</a></li>\n";
			}
		}
		echo "		</ul></td>\n";
	} else {
		echo "		<td valign=\"middle\">" . $toShow['room_name'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['num_of_person'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['room_payment'] . "</td>\n";
	}
	echo "	</tr>\n";
}


echo <<<EOT
</table>

EOT;




mysql_close($link);

html_end();




?>
