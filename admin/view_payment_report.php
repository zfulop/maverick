<?php

require("includes.php");



$link = db_connect();

$searchCriteria = 'payments';

$selectedPayModes = array();
$ccChecked = 'checked';
$btChecked = 'checked';
$cashChecked = 'checked';
if(isset($_REQUEST['pay_mode'])) {
	$selectedPayModes = $_REQUEST['pay_mode'];
	$searchCriteria .= " matching paymodes: ";
	foreach($selectedPayModes as $mode) {
		$searchCriteria .= ucwords(strtolower($mode)) . ', ';
	}
}

if(!in_array('CASH', $selectedPayModes)) {
	$cashChecked = '';
}
if(!in_array('BANK_TRANSFER', $selectedPayModes)) {
	$btChecked = '';
}
if(!in_array('CREDIT_CARD', $selectedPayModes)) {
	$ccChecked = '';
}

if(isset($_REQUEST['end_date'])) {
	$_SESSION['money_report_end_date'] = $_REQUEST['end_date'];
}
if(!isset($_SESSION['money_report_end_date'])) {
	$_SESSION['money_report_end_date'] = date('Y-m-d');
}
$endDate = $_SESSION['money_report_end_date'];

if(isset($_REQUEST['start_date'])) {
	$_SESSION['money_report_start_date'] = $_REQUEST['start_date'];
}
if(!isset($_SESSION['money_report_start_date'])) {
	$_SESSION['money_report_start_date'] = date('Y-m-d', strtotime($endDate . ' -1 month'));
}
$startDate = $_SESSION['money_report_start_date'];
$searchCriteria .= ', from: ' . $startDate . ' to: ' . $endDate;

if(isset($_REQUEST['type'])) {
	$_SESSION['money_report_type'] = $_REQUEST['type'];
}
if(!isset($_SESSION['money_report_type'])) {
	$_SESSION['money_report_type'] = array();
} elseif(in_array('', $_SESSION['money_report_type'])) {
	$_SESSION['money_report_type'] = array();
}
$type = $_SESSION['money_report_type'];
$selectAllTypes = (count($type) < 1);
if(!$selectAllTypes) {
	$searchCriteria .= ', type is one of [' . implode(', ', $type) . ']';
}
$typeOptions = '<option value=""' .  ($selectAllTypes ? ' selected' : '') . '>All<option>';
$sql = "SELECT * FROM cashout_type ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get list of payment types: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$typeOptions .= '<option value="' . $row['type'] . '"' . (in_array($row['type'], $type) ? ' selected' : '') . '>' . $row['type'] . '</option>';
	}
}

$comment = '';
if(isset($_REQUEST['comment'])) {
	$comment = $_REQUEST['comment'];
	if(strlen($comment) > 0) {
		$searchCriteria .= ', comment containing \'' . $comment . '\'';
	}
}

$selectedRooms = array();
if(isset($_REQUEST['rooms'])) {
	$selectedRooms = $_REQUEST['rooms'];
}
$selectAllRooms = in_array('', $selectedRooms);
$roomIdToName = array();
$roomNames = array();
$rooms = '<option value=""' .  ($selectAllRooms ? ' selected' : '') . '>All rooms</option>';
$sql = "SELECT r.id, r.name as room_name FROM rooms r ORDER BY r.name";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get list of rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		if(in_array($row['id'], $selectedRooms)) {
			$roomNames[] = $row['room_name'];
		}
		$rooms .= '<option value="' . $row['id'] . '"' . (in_array($row['id'], $selectedRooms) ? ' selected' : '') . '>' . $row['room_name'] . '</option>';
		$roomIdToName[$row['id']] = $row['room_name'];
	}
}

if($selectAllRooms) {
	$searchCriteria .= ', concerning all rooms';
} elseif(count($roomNames) > 0) {
	$searchCriteria .= ', concerning rooms: (' . implode(',', $roomNames) . ')';
}


$bdIds = array();
$payments = array();
$sql = "SELECT DISTINCT p.*, bd.name FROM payments p INNER JOIN booking_descriptions bd ON  (p.booking_description_id=bd.id)";
if(count($selectedRooms) > 0 and !$selectAllRooms) {
	$sql .= "INNER JOIN bookings b ON (b.description_id=bd.id AND b.room_id IN (" . implode(',', $selectedRooms) . "))";
}
$sql .= " WHERE p.comment<>'*booking deposit*' AND SUBSTR(time_of_payment,1,10)>='$startDate' AND SUBSTR(time_of_payment,1,10)<='$endDate' AND storno<>1";
if(strlen($comment) > 0) {
	$sql .= " AND comment LIKE '%$comment%'";
}
if(count($type) > 0) {
	$sql .= " AND type IN ('" . implode('\',\'', $type) . "')";
}
$sql .= " AND pay_mode IN ('" . implode("','", $selectedPayModes) . "')";
$sql .= " ORDER BY time_of_payment";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get payment for report: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$payments[] = $row;
		$bdIds[] = $row['booking_description_id'];
	}
}

$roomsForBD = array();
if(count($bdIds) > 0) {
	$sql = "SELECT * FROM bookings WHERE description_id IN (" . implode(',', $bdIds) . ")";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get rooms for payments when generating report: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			if(!isset($roomsForBD[$row['description_id']])) {
				$roomsForBD[$row['description_id']] = array();
			}
			$roomsForBD[$row['description_id']][] = $roomIdToName[$row['room_id']];
		}
	}
}


$extraHeader = <<<EOT

	<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
	<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
	<!--[if lte IE 6.5]>
	<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
	<![endif]-->


	<script type="text/javascript" src="js/prototype.js"></script>


	<style type="text/css">
		table.report {
			border-spacing:0;
			border-collapse:collapse;
		}

		table.report tr td {
			border-top: 1px solid black;
			margin: 0px 2px 0px 2px;
		}
		table.report tr td.amount {
			border-left: 1px solid black;
			padding: 1px 5px 1px 5px;
			text-align: right;
		}
	</style>

EOT;



html_start("Maverick Admin - Payment reporting", $extraHeader);


$fromName = $_SERVER['PHP_AUTH_USER'];

echo <<<EOT

<form action="view_payment_report.php" method="GET" accept-charset="utf-8">
<table>
	<tr><th colspan="2">Generate report</th></tr>
	<tr>
		<td>Room</td>
		<td><select name="rooms[]" size="4" multiple="multiple" style="height: 70px;">$rooms</select></td>
	</tr>
	<tr>
		<td>Type</td>
		<td><select name="type[]" size="4" multiple="multiple" style="height: 70px;">$typeOptions</select></td>
	</tr>
	<tr>
		<td>Cash vs. CC payments</td>
		<td><input type="checkbox" style="float: none; display: inline;" name="pay_mode[]" value="CASH" $cashChecked> Cash <br><input type="checkbox" style="float: none; display: inline;" name="pay_mode[]" value="CREDIT_CARD" $ccChecked> Credit Card <br><input type="checkbox" style="float: none; display: inline;" name="pay_mode[]" value="BANK_TRANSFER" $btChecked> Bank Transfer </td>
	</tr>
	<tr>
		<td>From</td>
		<td>
			<input id="start_date" name="start_date" size="10" maxlength="10" type="text" value="$startDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>To</td>
		<td>
			<input id="end_date" name="end_date" size="10" maxlength="10" type="text" value="$endDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date', 'chooserSpanED', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>Comment</td>
		<td><input name="comment" value="$comment"></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="Generate report">
		</td>
	</tr>


</table>
</form>

Searching for $searchCriteria<br>

<table class="report">
	<tr><th>Date</th><th>Name</th><th>Room(s)</th><th>Type</th><th>Comment</th><th>Amount EUR</th><th>Amount HUF</th></tr>

EOT;


$amountEur = 0;
$amountHuf = 0;
foreach($payments as $onePymt) {
	$eurCell = "";
	$hufCell = "";
	$roomNames = '';
	if(isset($roomsForBD[$onePymt['booking_description_id']])) {
		$roomNames = implode('<br>', $roomsForBD[$onePymt['booking_description_id']]);
	}
	if($onePymt['currency'] == 'EUR') {
		$amountEur += $onePymt['amount'];
		$eurCell = sprintf('%.2f', $onePymt['amount']);
	} else {
		$amountHuf += $onePymt['amount'];
		$hufCell = $onePymt['amount'];
	}
	$time = $onePymt['time_of_payment'];
	$name = $onePymt['name'];
	$comment = $onePymt['comment'];
	$type = $onePymt['type'];
	echo "	<tr><td>$time</td><td>$name</td><td>$roomNames</td><td>$type</td><td>$comment</td><td class=\"amount\">$eurCell</td><td class=\"amount\">$hufCell&nbsp;</td></tr>";
}

$amountEur = sprintf('%.2f', $amountEur);

echo <<<EOT
	<tr><td colspan="7"><hr></td></tr>
	<tr><td colspan="5"><strong>Total</strong></td><td class="amount">$amountEur</td><td class="amount">$amountHuf</td></tr>
</table>


EOT;

mysql_close($link);

html_end();



?>
