<?php

require("includes.php");


$link = db_connect();

$descrId = intval($_REQUEST['description_id']);

$roomTypes = array();
$sql = "SELECT rt.*, count(*) as num_of_rooms FROM room_types rt inner join rooms r on (rt.id=r.room_type_id) group by rt.id";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	$roomTypes[$row['id']] = $row;
}


$rooms = array();
$sql = "SELECT r.*, rt.name AS rt_name FROM rooms r inner join room_types rt on r.room_type_id=rt.id";
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
	$roomsHtmlOptions .= '<option value="' . $row['id'] . '">' . $row['rt_name'] . ' - ' . $row['name'] . '</option>';
}

$bookingDescription = null;
$sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error("Cannot get booking (with description_id: $descrId).");
	trigger_error("Cannot get booking (with description_id: $descrId) in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
$bookingDescription = mysql_fetch_assoc($result);

$bcr = null;
$sql = "SELECT * FROM bcr WHERE booking_description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error("Cannot get bcr (with description_id: $descrId).");
	trigger_error("Cannot get bcr (with description_id: $descrId) in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	return;
} elseif(mysql_num_rows($result) > 0) {
	$bcr = mysql_fetch_assoc($result);
}

$bookings = array();
$type = '';
$sql = "SELECT * FROM bookings WHERE description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error("Cannot get booking (with description_id: $descrId).");
	trigger_error("Cannot get booking (with description_id: $descrId) in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
if(mysql_num_rows($result) < 1) {
	set_error("Cannot find booking with description_id: $descrId");
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}

$totalBookingPayment = 0;
$bookingIds = array();
while($row = mysql_fetch_assoc($result)) {
	$row['room_name'] = $rooms[$row['room_id']]['name'];
	$bookings[] = $row;
	$bookingIds[] = $row['id'];
	$totalBookingPayment += $row['room_payment'];
}

$roomChanges = array();
if(count($bookingIds) > 0) {
	$sql = 'SELECT * FROM booking_room_changes WHERE booking_id IN (' . implode(',', $bookingIds) . ')';
	$result = mysql_query($sql, $link);
	if(!$result) {
		set_error("Cannot get room changes for selected booking (with booking ids: " . implode(',', $bookingIds) . ").");
		trigger_error("Cannot get room changes for selected booking (with booking ids: " . implode(',', $bookingIds) . "). MySQL error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$row['new_room_name'] = $rooms[$row['new_room_id']]['name'];
			$roomChanges[$row['booking_id']][$row['date_of_room_change']] = $row;
		}
	}
}


if(!isset($_SERVER['HTTP_REFERER'])) {
	$_SERVER['HTTP_REFERER'] = "view_availability.php";
}

if(strpos($_SERVER['HTTP_REFERER'], 'edit_booking.php') === FALSE and strpos($_SERVER['HTTP_REFERER'], 'save_booking.php') === FALSE) {
	$_SESSION['return_from_edit_booking'] = $_SERVER['HTTP_REFERER'];
}
$cancelUrl = $_SESSION['return_from_edit_booking'];

$extraHeader =<<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->


<script type="text/javascript">

	function isnumeric(sText) {
		var ValidChars = "0123456789.";
		var IsNumber=true;
		var Char;
 
		for (i = 0; i < sText.length && IsNumber == true; i++)  { 
			Char = sText.charAt(i); 
			if(ValidChars.indexOf(Char) == -1) {
				IsNumber = false;
			}
		}
		return IsNumber;
	}

	function toggleMaintenance(checkbox) {
		if(checkbox.checked) {
			if(confirm('Are you sure to set this booking as maintenance?')) {
				new Ajax.Request('maintenance_booking.php', {
					method: 'post',
					parameters: {description_id: $descrId, maintenance: 1},
					onSuccess: function(transport) {
						alert('The booking is set as maintenance.');
					}
				});
				return true;
			} else {
				checkbox.checked = false;
				return false;
			}
		} else {
			if(confirm('Are you sure to take off maintenance flag from this booking?')) {
				new Ajax.Request('maintenance_booking.php', {
					method: 'post',
					parameters: {description_id: $descrId, maintenance: 0},
					onSuccess: function(transport) {
						alert('Maintenance flag is removed from the booking');
					}
				});
				return true;
			} else {
				checkbox.checked = true;
				return false;
			}
		}
	}
</script>

EOT;

$name = $bookingDescription['name'];
$maleSelected = $bookingDescription['gender'] == 'MALE' ? ' selected' : '';
$femaleSelected = $bookingDescription['gender'] == 'FEMALE' ? ' selected' : '';
$address = $bookingDescription['address'];
$nationality = $bookingDescription['nationality'];
$email = $bookingDescription['email'];
$tel = $bookingDescription['telephone'];

$arrivalTime = $bookingDescription['arrival_time'];

$deposit = '';
$depositCurrency = '';
$depositCurrencyOptions = '';
$sql = "SELECT * FROM payments WHERE booking_description_id=$descrId AND comment='*booking deposit*'";
$result = mysql_query($sql, $link);
if(mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$deposit = $row['amount'];
	$depositCurrency = $row['currency'];
}
foreach(array('EUR', 'HUF') as $currency) {
	$depositCurrencyOptions .= "<option value=\"$currency\"" . ($currency == $depositCurrency ? ' selected' : '') . ">$currency</option>";
}


$fnight = $bookingDescription['first_night'];
$lnight = $bookingDescription['last_night'];
$numOfNights = $bookingDescription['num_of_nights'];
if($numOfNights < 1) {
	$numOfNights = intval((strtotime(str_replace('/', '-', $lnight)) - strtotime(str_replace('/', '-', $fnight))) / (60*60*24)) + 1;
}


$totalPaymentsEur = 0;
$hasCash3Payment = false;
$payments = array();
$sql = "SELECT * FROM payments WHERE booking_description_id=$descrId AND pay_mode<>'CASH' AND storno<>1";
$result = mysql_query($sql, $link);
if(!$result) {
	$err = "Cannot get payment(s) of booking (with description_id: $descrId).";
	set_error($err);
	trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
while($row = mysql_fetch_assoc($result)) {
	$payments[] = $row;
	if($row['pay_mode'] == 'CASH3') {
		$hasCash3Payment = true;
	}
	$totalPaymentsEur += convertAmount($row['amount'], $row['currency'], 'EUR', date('Y-m-d'));

}

$serviceCharges = array();
/*
$sql = "SELECT * FROM service_charges WHERE booking_description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	$err = "Cannot get service charge(s) of booking (with description_id: $descrId).";
	set_error($err);
	trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
while($row = mysql_fetch_assoc($result)) {
	$serviceCharges[] = $row;
}
*/

/*
$sql = "SELECT * FROM cashout_type ORDER BY type";
$paymentTypeOptions = '';
$serviceOptions = '';
$result = mysql_query($sql, $link);
if(!$result) {
	$err = "Cannot get payment types.";
	set_error($err);
	trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
 */

$types = array('Fenntartási költségek','Pénz elvitel Kristóf','Pénz elvitel Levi', 'Pénz elvitel Peti', 'Pénz elvitel Süni', 'Szobabevétel', 'Vásárlás', 'Sztornó korrekció', 'Befizetés bankba', 'Házipénztár');
$paymentTypeOptions = '';
$serviceOptions = '';
foreach($types as $type) {
	$paymentTypeOptions .= "<option value=\"$type\">$type</option>";
	$serviceOptions .= "<option value=\"$type\">$type</option>";
}


$nationalityOptions = '';
$countries = file_get_contents('includes/countries.txt');
foreach(explode("\n", $countries) as $cntry) {
	$cntry = trim($cntry);
	if(strlen($cntry) < 1)
		continue;

	$nationalityOptions .= "<option value=\"$cntry\"" . ($cntry == $nationality ? " selected" : "") . ">$cntry</option>\n";
}


html_start("Maverick Admin - Edit Booking " . ($bookingDescription['cancelled'] ? ' - CANCELLED!!!' : ''), $extraHeader);

$maintenanceChecked = $bookingDescription['maintenance'] == 1 ? 'checked' : '';

$fnightDash = str_replace('/','-',$fnight);
$fnightMinus2Weeks = date('Y/m/d', strtotime("$fnightDash -14 day"));

$bcrHtml = "";
if(is_null($bcr)) {
	$bcrHtml = "BCR will be sent on $fnightMinus2Weeks";
} else {
	$bcrSent = $bcr['mail_sent'];
	$bcrEmail = $bcr['email'];
	$bcrName = $bcr['name'];
	$bcrFnight = $bcr['first_night'];
	$bcrHtml = <<<EOT
			<table style="font-size: 100%;">
				<tr><td>Sent</td><td>$bcrSent</td></tr>
				<tr><td>Name</td><td>$bcrName</td></tr>
				<tr><td>Email</td><td>$bcrEmail</td></tr>
				<tr><td>1st night</td><td>$bcrFnight</td></tr>
			</table>

EOT;
}

echo <<<EOT

<div style="position: absolute; left: 430px; top: 110px; padding: 5px; font-size: 80%; border: 1px solid black;">
	<b>BCR</b><br>
	$bcrHtml
</div>
<a href="print_booking_summary.php?description_id=$descrId">Print payment receipt</a><br>
<a href="#" onclick="if(confirm('Are you sure to email the payment receipt to $email?')) { window.location='print_booking_summary.php?description_id=$descrId&action=email';} return false;">Email payment receipt</a><br>
<a href="#" onclick="if(confirm('Are you sure to cancel the booking?')) { window.location='cancel_booking.php?description_id=$descrId&type=reception';} return false;">Reception cancel</a><br>
<a href="#" onclick="if(confirm('Are you sure to cancel the booking?')) { window.location='cancel_booking.php?description_id=$descrId&type=guest';} return false;">Guest cancel</a><br>
<form>
	<input type="checkbox" $maintenanceChecked onchange="toggleMaintenance(this);">Maintenance<br>
</form>

<table>
	<tr>
		<td>First night: </td><td>$fnight</td>
		<td rowspan="3">
			<form action="change_booking_date.php" accept-charset="utf-8" method="post">
			<input type="hidden" name="booking_description_id" id="gd_booking_description_id" value="$descrId">
			<input type="hidden" name="old_first_night" value="$fnight">
			<input type="hidden" name="old_last_night" value="$lnight">
			<input type="button" value="Change dates" id="change_date_btn" onclick="document.getElementById('change_date_div').style.display='block';document.getElementById('change_date_btn').style.display='none';return false;">
			<div id="change_date_div" style="display: none;">
				<table>
					<tr>
						<td>First night: </td>
						<td>
							<input name="first_night" id="first_night" value="$fnight" onchange="recalculatePayment();"> <img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'first_night', 'chooserSpanFN', 2010, 2025, 'Y/m/d', false);">
							<div id="chooserSpanFN" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
						</td>
					</tr>
					<tr>
						<td>Last night: </td>
						<td>
							<input name="last_night" id="last_night" value="$lnight" onchange="recalculatePayment();"> <img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'last_night', 'chooserSpanLN', 2010, 2025, 'Y/m/d', false);">
							<div id="chooserSpanLN" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="submit" value="Save date change">
							<input type="button" value="Cancel" onclick="document.getElementById('change_date_div').style.display='none';document.getElementById('change_date_btn').style.display='inline';return false;">
						</td>
					</tr>
				</table>
			</div>
			</form>
		</td>
	</tr>
	<tr><td>Last night: </td><td>$lnight</td></tr>
	<tr><td>Number of nights: </td><td>$numOfNights</td></tr>
	<tr><td colspan="3"><hr></td></tr>
		
	<form id="edit_booking_form" action="save_booking.php" method="POST" accept-charset="utf-8">
	<input type="hidden" name="description_id" value="$descrId">

	<tr>
		<td>Name of booker: </td>
		<td style="width: 300px;"><input name="name" value="$name"> <span style="float:left;"></td>
	</tr>
	<tr><td>Arrival time: </td><td><input name="arrival_time" value="$arrivalTime"></td></tr>
	<tr><td>Gender: </td><td><select name="gender">
		<option value="MALE"$maleSelected>Male</option>
		<option value="FEMALE"$femaleSelected>Female</option>
	</select></td></tr>
	<tr><td>Address: </td><td><textarea name="address">$address</textarea></td></tr>
	<tr><td>Nationality: </td><td><select name="nationality">$nationalityOptions</select></td></tr>
	<tr><td>Email: </td><td><input name="email" value="$email"></td></tr>
	<tr><td>Tel: </td><td><input name="telephone" value="$tel"></td></tr>
	<tr><td>Deposit: </td><td><input name="deposit" value="$deposit"><select name="deposit_currency">$depositCurrencyOptions</select></td></tr>
	<tr><td colspan="2"><input type="submit" value="Save"><input type="button" value="Cancel" onclick="window.location='$cancelUrl';"></td></tr>

	</form>

</table>

<br><br>


EOT;

echo "Booking ";
if($bookingDescription['paid']) {
	echo " paid. Change to <a href=\"not_paid_booking.php?description_id=$descrId\">not paid</a>";
} else {
	echo " not yet paid. Change to <a href=\"paid_booking.php?description_id=$descrId\">paid</a>";
}
echo "<br>\n";

echo "Booking ";
if($bookingDescription['confirmed']) {
	echo " confirmed. Change to <a href=\"unconfirm_booking.php?description_id=$descrId\">not confirmed</a>";
} else {
	echo " not confirmed. Change to <a href=\"confirm_booking.php?description_id=$descrId\">confirmed</a>";
}
echo "<br>\n";

echo "Booking ";
if($bookingDescription['checked_in']) {
	echo " checked in. <a href=\"checkout_booking.php?description_id=$descrId\">Check out</a> booking";
} else {
	echo " not checked in. <a href=\"checkin_booking.php?description_id=$descrId\">Check in</a> booking";
}

echo "<br>\n";

$today = date('Y-m-d');
$todayPer = date('Y/m/d');

$dates = array();
$fnightDash = str_replace('/','-',$fnight);
for($i = 0; $i < $numOfNights; $i++) {
	$currDate = date('Y/m/d', strtotime("$fnightDash +$i day"));
	$dates[] = $currDate;
}

echo <<<EOT

<h2>Rooms/Beds: </h2>

<table>
	<tr>
		<td valign="top" style="padding-right: 10px;border-right: 1px solid #000;">
			<table>
				<tr><th rowspan="2">Room name</th><th rowspan="2">Type</th><th rowspan="2"># of beds</th><th rowspan="2">Price</th><th colspan="$numOfNights">Room changes</th></tr>
				<tr>
EOT;

foreach($dates as $oneDate) {
	if($oneDate == $todayPer) {
		echo "<th style=\"color:red;\">$oneDate</th>";
	} else {
		echo "<th>$oneDate</th>";
	}
}

echo "</tr>\n";

$multiplier = 1;
if($hasCash3Payment) {
	$multiplier = ($totalPaymentsEur + convertAmount($deposit, $depositCurrency, 'EUR', date('Y-m-d'))) / $totalBookingPayment;
}


$roomTotal = 0;
foreach($bookings as $booking) {
	$bid = $booking['id'];
	$rn = urlencode($booking['room_name']);
	echo "				<tr><td>" . $booking['room_name'] . "</td><td>" . $booking['booking_type'] . "</td><td align=\"center\">" . $booking['num_of_person'] . "</td><td align=\"right\">" . sprintf("%.1f", floatval($multiplier * $booking['room_payment'])) . " euro</td>";
	foreach($dates as $oneDate) {
		$roomName = '';
		$style = ($oneDate == $todayPer ? 'color:red;' : '') . "width: 50px;";
		if(isset($roomChanges[$bid][$oneDate])) {
			$roomName = $roomChanges[$bid][$oneDate]['new_room_name'];
		}
		echo "<td style=\"$style\">$roomName</td>";
	}
	echo "<td><a href=\"delete_booking.php?description_id=$descrId&id=$bid&room=$rn\">Delete</a></tr>\n";
	$roomTotal += ($multiplier * $booking['room_payment']);
}


$roomsHtml = '';
foreach($roomTypes as $roomTypeId => $roomType) {
	$excludeNums = array();
	if($roomType['type'] == 'PRIVATE') {
		for($i = 1; $i < $roomType['num_of_rooms'] * $roomType['num_of_beds']; $i++) {
			if($i % $roomType['num_of_beds'] > 0) {
				$excludeNums[] = $i;
			}
		}
	}
	$options = getOptions($roomType['num_of_rooms'] * $roomType['num_of_beds'], $excludeNums);
	$roomsHtml .= "\t\t\t\t\t<tr><td>" . str_replace(" ", "&nbsp;", $roomType['name']) . "</td><td><select name=\"num_of_person_$roomTypeId\" id=\"num_of_person_$roomTypeId\" onchange=\"recalculatePayment();\">$options</select></td></tr>\n";
}




echo <<<EOT
				<tr><td colspan="4"><hr></td></tr>
				<tr><td colspan="3"><b>Total room price</b></td><td align="right">$roomTotal euro</td></tr>
			</table>
		</td>
		<td valign="top" style="padding-left: 10px;">
			<h3>Add Booking:</h3>
			<form action="add_booking.php" accept-charset="utf-8" method="post">
				<input type="hidden" name="booking_description_id" value="$descrId">
				<input type="hidden" name="first_night" value="$fnight">
				<input type="hidden" name="last_night" value="$lnight">
				<table>
$roomsHtml
				</table>
				<input type="submit" value="Add">
			</form>
		</td>
	</tr>
</table>


<br><br>

<h2>Service charges: </h2>
<input type="button" value="Add new" onclick="document.getElementById('add_service_charge_row').style.visibility='visible';"><br>
<table style="clear: left;">
	<tr><th>Type</th><th>Date</th><th>Comment</th><th>Price</th></tr>
	<tr id="add_service_charge_row" style="visibility: collapse;"><form action="add_service_charge.php" method="post" accept-charset="utf-8">
		<input type="hidden" name="booking_description_id" value="$descrId">
		<td><select name="type">$serviceOptions</select></td>
		<td>$today</td>
		<td><input name="comment"></td>
		<td><input name="amount" size="4" id="service_amount"><select name="currency" id="service_currency"><option value="EUR">EUR</option><option value="HUF">HUF</option></select></td>
		<td><input type="submit" value="Save"><input type="button" value="Cancel" onclick="document.getElementById('add_service_charge_row').style.visibility='collapse'; return false;"></td>
	</form></tr>

EOT;

$serviceChargeTotal = 0;
foreach($serviceCharges as $sc) {
	echo "	<tr><td>" . $sc['type'] . "</td><td>" . $sc['time_of_service'] . "</td><td>" . $sc['comment'] . "</td><td align=\"right\">" . sprintf('%.2f', $sc['amount']) . ' ' . $sc['currency'] . "</td></tr>\n";
	$serviceChargeTotal += convertAmount($sc['amount'], $sc['currency'], 'EUR', substr($sc['time_of_service'], 0, 10));
}

$serviceChargeTotal = sprintf("%.2f", $serviceChargeTotal);
echo <<<EOT
	<tr><td colspan="4"><hr></td></tr>
	<tr><td colspan="3">Total</td><td align="right">$serviceChargeTotal EUR</td></tr>
</table>


<br><br>

<h2>Payments:</h2>
<input type="button" value="Add new" onclick="document.getElementById('add_payment_row').style.visibility='visible';"><br>
<table style="clear: left;">
	<tr><th>Type</th><th>Date</th><th>Comment</th><th>Amount</th><th>Mode</th></tr>
	<tr id="add_payment_row" style="visibility: collapse;"><form action="add_payment.php" method="post" accept-charset="utf-8"><input type="hidden" name="booking_description_id" value="$descrId"><td><select name="type">$paymentTypeOptions</select></td><td>$today</td><td><input name="comment"></td><td><input name="amount" size="4"><select name="currency"><option value="EUR">EUR</option><option value="HUF">HUF</option></select></td><td><input type="radio" name="pay_mode" value="CASH" checked>Cash<br><input type="radio" name="pay_mode" value="BANK_TRANSFER">Bank Transfer<br><input type="radio" name="pay_mode" value="CREDIT_CARD">Credit Card</td><td><input type="submit" value="Save"><input type="button" value="Cancel" onclick="document.getElementById('add_payment_row').style.visibility='collapse';return false;"></td></form></tr>

EOT;

$paymentTotal = 0;
foreach($payments as $payment) {
	if($payment['comment'] == '*booking deposit*' or $payment['storno'] == 1) {
		continue;
	}
	$amount = sprintf('%.2f', $payment['amount'])	. ' ' . $payment['currency'];
	if($payment['pay_mode'] == 'CASH3' or $payment['pay_mode'] == 'CASH2') {
		$payment['pay_mode'] = 'CASH';
	}
	$mode = str_replace('_', ' ', $payment['pay_mode']);
	$mode = ucwords(strtolower($mode));
	echo "	<tr><td>" . $payment['type'] . "</td><td>" . $payment['time_of_payment'] . "</td><td>" . $payment['comment'] . "</td><td align=\"right\">$amount</td><td>$mode</td></tr>\n";
	$paymentTotal += convertAmount($payment['amount'], $payment['currency'], 'EUR', $payment['time_of_payment']);
}

$paymentTotal = sprintf("%.2f", $paymentTotal);
echo <<<EOT
	<tr><td colspan="5"><hr></td></tr>
	<tr><td colspan="3">Total</td><td align="right">$paymentTotal EUR</td></tr>
</table>

<br>

EOT;

$balance = $roomTotal + $serviceChargeTotal - $paymentTotal;
if($deposit > 0) {
	$balance -= convertAmount($deposit, $depositCurrency, 'EUR', $fnight);
}
$balanceHuf = convertAmount($balance, 'EUR', 'HUF', date('Y-m-d'));
$balance = sprintf("%.2f", $balance);
$balanceHuf = intval($balanceHuf);
echo "<h2>Balance: $balance euro ($balanceHuf Ft)</h2>\n\n";



mysql_close($link);
html_end();


function js_encode($str) {
	$str = str_replace('"', '\u0022', $str);
	$str = str_replace('\'', '\u0027', $str);
	$str = str_replace("\n\r", '\n', $str);
	$str = str_replace("\r\n", '\n', $str);
	$str = str_replace("\n", '\n', $str);
	$str = str_replace(PHP_EOL, '\n', $str);
	return $str;
}


function getOptions($max, $excludedItems = array()) {
	$options = '';
	for($i = 0; $i <= $max; $i++) {
		if(in_array($i, $excludedItems))
			continue;

		$options .= "<option value=\"$i\">$i</option>";
	}
	return $options;	
}


?>
