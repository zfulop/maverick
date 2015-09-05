<?php

require("includes.php");



$link = db_connect();

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

$comment = '';
if(isset($_REQUEST['comment'])) {
	$comment = $_REQUEST['comment'];
}

$type = array();
if(isset($_REQUEST['type'])) {
	foreach($_REQUEST['type'] as $t) {
		$type[] = $t;
	}
}

$gtDest = '';
if(isset($_REQUEST['gt_destination'])) {
	$gtDest = $_REQUEST['gt_destination'];
}

$payMode = array();
if(isset($_REQUEST['pay_mode'])) {
	$payMode = $_REQUEST['pay_mode'];
}

$tables = array('SC' => 'Service charges', 'CIO' => 'Cash In/Out', 'P' => 'Payment');

$tablesSelected = array_keys($tables);
if(isset($_REQUEST['tables'])) {
	$tablesSelected = $_REQUEST['tables'];
}


$filterTypes = '';
$sql = "SELECT * FROM cashout_type ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get cashout types: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$filterTypes .= '<option value="' . $row['type'] . '"' . (in_array($row['type'], $type) ? ' selected' : '') . '>' . $row['type'] . '</option>';
	}
}

$guestTransferDest = '<option value="">[no guest transfer]]</option>';
$sql = "SELECT distinct destination FROM guest_transfer WHERE destination<>'' ORDER BY destination";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get guest transfer destinations: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$guestTransferDest .= '<option value="' . $row['destination'] . '"' . ($row['destination'] == $gtDest ? ' selected' : '') . '>' . $row['destination'] . '</option>';
	}
}


$payModeOptions = '';
foreach(array('CASH','CASH2','CASH3','BANK_TRANSFER','CREDIT_CARD') as $pm) {
	$payModeOptions .= '<option value="' . $pm . '"' . (in_array($pm,$payMode) ? ' selected' : '') . '>' . ucwords(strtolower($pm)) . '</option>';
}

$tablesOption = '';
foreach($tables as $key => $desc) {
	$tablesOption .= ' <input type="checkbox" name="tables[]" value="' . $key . '"' . (in_array($key, $tablesSelected) ? ' checked' : '') . '> ' . $desc . '<br>'; 
}


$cashOuts = array();
if(in_array('CIO', $tablesSelected)) {
	$sql = "SELECT c.type AS type, c.time_of_payment, c.pay_mode, c.receiver, c.comment, c.currency, c.amount FROM cash_out c WHERE SUBSTR(c.time_of_payment,1,10)>='$startDate' AND SUBSTR(c.time_of_payment,1,10)<='$endDate' AND c.storno<>1";
	if(strlen($comment) > 0) {
		$sql .= " AND c.comment LIKE '%$comment%'";
	}
	if(count($payMode) > 0) {
		$sql .= " AND c.pay_mode IN ('" . implode("','",$payMode) . "')";
	}
	if(count($type) > 0) {
		$sql .= " AND c.type IN ('" . implode("','", $type) . "')";
	}
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get cashout for report: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$cashOuts[] = $row;
		}
	}
}

$payments = array();
if(in_array('P', $tablesSelected)) {
	$sql = "SELECT p.* FROM payments p WHERE SUBSTR(p.time_of_payment,1,10)>='$startDate' AND SUBSTR(p.time_of_payment,1,10)<='$endDate' AND p.storno<>1";
	if(strlen($comment) > 0) {
		$sql .= " AND p.comment LIKE '%$comment%'";
	}
	if(count($type) > 0) {
		$sql .= " AND p.type IN ('" . implode("','", $type) . "')";
	}
	if(count($payMode) > 0) {
		$sql .= " AND p.pay_mode IN ('" . implode("','",$payMode) . "')";
	}
	$sql .= " ORDER BY time_of_payment";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get cashout for report: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$payments[] = $row;
		}
	}
}

$scharges = array();
if(in_array('SC', $tablesSelected)) {
	$sql = "SELECT sc.* FROM service_charges sc WHERE SUBSTR(sc.time_of_service,1,10)>='$startDate' AND SUBSTR(sc.time_of_service,1,10)<='$endDate'";
	if(strlen($comment) > 0) {
		$sql .= " AND comment LIKE '%$comment%'";
	}
	if(count($type) > 0) {
		$sql .= " AND sc.type IN ('" . implode('\',\'', $type) . "')";
	}
	if(count($payMode) > 0) {
		$sql .= " AND 1=0";
	}
	$sql .= " ORDER BY time_of_service";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get service charges for report: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$scharges[] = $row;
		}
	}
}

$gtransfers = array();
if(in_array('GT', $tablesSelected)) {
	$sql = "SELECT * FROM guest_transfer WHERE SUBSTR(time_of_enter,1,10)>='$startDate' AND SUBSTR(time_of_enter,1,10)<='$endDate'";
	if(strlen($comment) > 0) {
		$sql .= " AND comment LIKE '%$comment%'";
	}
	if(strlen($gtDest) > 0) {
		$sql .= " AND destination='$gtDest'";
	}
	if(count($payMode) > 0) {
		$sql .= " AND pay_mode IN ('" . implode("','",$payMode) . "')";
	}
	$sql .= " ORDER BY time_of_enter";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get guest transfers for report: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$gtransfers[] = $row;
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
			padding: 0px 5px 0px 5px;
		}
		table.report tr th {
			margin: 0px 2px 0px 2px;
			padding: 0px 5px 0px 5px;
		}
		table.report tr td.amount {
			border-left: 1px solid black;
			padding: 1px 5px 1px 5px;
			text-align: right;
		}
	</style>

EOT;



html_start("Maverick Mgmt - Report", $extraHeader);


$fromName = $_SERVER['REMOTE_USER'];

echo <<<EOT

<form action="view_money_report.php" method="GET" accept-charset="utf-8">
<table>
	<tr><th colspan="2">Generate report</th></tr>
	<tr>
		<td>Include:</td>
		<td>$tablesOption</td>
	</tr>
	<tr>
		<td>Type</td>
		<td><select name="type[]" size="8" multiple="multiple" style="height: 140px;">$filterTypes</select></td>
	</tr>
	<tr>
		<td>Guest transfer destination</td>
		<td><select name="gt_destination">$guestTransferDest</select></td>
	</tr>
	<tr>
		<td>Pay mode</td>
		<td><select name="pay_mode[]" size="6" multiple="multiple" style="height: 100px;">$payModeOptions</select></td>
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


<table class="report">
	<tr><th>Entity</th><th>Type</th><th>Date</th><th>Name</th><th>Pay mode</th><th>Comment</th><th>Amount EUR</th><th>Amount HUF</th></tr>

EOT;


$takeSc = false;
$takeCo = false;
$takeP = false;
$takeGt = false;
$scIdx = 0;
$pIdx = 0;
$coIdx = 0;
$gtIdx = 0;
$amountEur = 0;
$amountHuf = 0;
while(count($scharges) > $scIdx or count($cashOuts) > $coIdx or count($payments) > $pIdx or count($gtransfers) > $gtIdx) {
	$s = null;
	$c = null;
	$g = null;
	$p = null;
	$arr = array();
	if(count($scharges) > $scIdx) {
		$s = $scharges[$scIdx]['time_of_service'];
		$arr[] = $s;
	}
	if(count($cashOuts) > $coIdx) {
		$c = $cashOuts[$coIdx]['time_of_payment'];
		$arr[] = $c;
	}
	if(count($payments) > $pIdx) {
		$p = $payments[$pIdx]['time_of_payment'];
		$arr[] = $p;
	}
	if(count($gtransfers) > $gtIdx) {
		$g = $gtransfers[$gtIdx]['time_of_enter'];
		$arr[] = $g;
	}

	$takeSc = false;
	$takeCo = false;
	$takeGt = false;
	$takeP = false;

	$m = min($arr);

	$takeSc = ($s == $m);
	$takeCo = ($c == $m);
	$takeGt = ($g == $m);
	$takeP = ($p == $m);


	if(count($arr) < 1) {
		break;
	}

	$eurCell = "";
	$hufCell = "";
	$bookingDescriptionId = null;
	if($takeSc) {
		$scharge = $scharges[$scIdx];
		if($scharge['currency'] == 'EUR') {
			$amountEur += $scharge['amount'];
			$eurCell = sprintf('%.2f', $scharge['amount']);
		} else {
			$amountHuf += $scharge['amount'];
			$hufCell = $scharge['amount'];
		}
		$table = 'Service Charge';
		$type = $scharge['type'];
		$time = $scharge['time_of_service'];
		$comment = $scharge['comment'];
		$name = '';
		$paymode = '';
		$bookingDescriptionId = $scharge['booking_description_id'];
		$scIdx += 1;
	} elseif($takeCo) {
		$co = $cashOuts[$coIdx];
		if($co['amount'] >= 0) {
			$table = 'Cash Out';
		} else {
			$table = 'Cash In';
		}
		$type = $co['type'];
		$time = $co['time_of_payment'];
		$name = $co['receiver'];
		$comment = $co['comment'];
		$paymode = $co['pay_mode'];
		if($co['currency'] == 'EUR') {
			$amountEur += (-1 * $co['amount']);
			$eurCell = sprintf('%.2f', (-1 * $co['amount']));
		} else {
			$amountHuf += (-1 * $co['amount']);
			$hufCell = (-1 * $co['amount']);
		}
		$coIdx += 1;
	} elseif($takeP) {
		$p = $payments[$pIdx];
		$table = 'Payment';
		$type = $p['type'];
		$time = $p['time_of_payment'];
		$name = '';
		$paymode = $p['pay_mode'];
		$comment = $p['comment'];
		if($p['currency'] == 'EUR') {
			$amountEur += $p['amount'];
			$eurCell = sprintf('%.2f', $p['amount']);
		} else {
			$amountHuf += $p['amount'];
			$hufCell = $p['amount'];
		}
		$bookingDescriptionId = $p['booking_description_id'];
		$pIdx += 1;
	} elseif($takeGt) {
		$gt = $gtransfers[$gtIdx];
		$table = 'Guest Transfer';
		if($gt['amount_currency'] == 'EUR') {
			$amountEur += $gt['amount_value'];
			$eurCell = sprintf('%.2f', $gt['amount_value']);
		} else {
			$amountHuf += $gt['amount_value'];
			$hufCell = $gt['amount_value'];
		}
		$type = "Destination: " . $gt['destination'];
		$time = $gt['time_of_enter'];
		$name = $gt['name'];
		$paymode = $gt['pay_mode'];
		$comment = $gt['comment'];
		$gtIdx += 1;
	}
	if($comment == '') {
		$comment = '&nbsp;';
	}
	if($name == '') {
		$name = '&nbsp;';
	}

	if(!is_null($bookingDescriptionId)) {
		$editBookingUrl = RECEPCIO_BASE_URL . "edit_booking.php?description_id=$bookingDescriptionId";
		$eurCell = "<a href=\"$editBookingUrl\">$eurCell</a>";
		$hufCell = "<a href=\"$editBookingUrl\">$hufCell</a>";
	}
	echo "	<tr><td>$table</td><td>$type</td><td>$time</td><td>$name</td><td>$paymode</td><td>$comment</td><td class=\"amount\">$eurCell</td><td class=\"amount\">$hufCell</td></tr>";
}

$amountEur = sprintf('%.2f', $amountEur);

echo <<<EOT
	<tr><td colspan="8"><hr></td></tr>
	<tr><td colspan="6"><strong>Total</strong></td><td class="amount">$amountEur</td><td class="amount">$amountHuf</td></tr>
</table>


EOT;

mysql_close($link);

html_end();



?>
