<?php

require("includes.php");

$link = db_connect();

$viewDay = null;
if(isset($_REQUEST['view_day'])) {
	$viewDay = $_REQUEST['view_day'];
}

$lastDayClose = null;
$lastDayCloseTime = date('Y-m-d');
$sql = "SELECT * FROM day_close ORDER BY time_of_day_close DESC LIMIT 1";
$result = mysql_query($sql, $link);
$dayCloseHuf = 0;
$dayCloseEur = 0;
$dayCloseHuf2 = 0;
$dayCloseEur2 = 0;
if(!$result) {
	trigger_error("Cannot get last day close: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} elseif(mysql_num_rows($result) > 0) {
	$lastDayClose = mysql_fetch_assoc($result);
	$lastDayCloseTime = $lastDayClose['time_of_day_close'];
	$dayCloseEur = $lastDayClose['casseEUR'];
	$dayCloseHuf = $lastDayClose['casseHUF'];
	$dayCloseHuf2 = $lastDayClose['casseHUF2'];
	$dayCloseEur2 = $lastDayClose['casseEUR2'];
}

$sql = "SELECT payments.*, booking_descriptions.name FROM payments LEFT OUTER JOIN booking_descriptions ON payments.booking_description_id=booking_descriptions.id WHERE ";
if(!is_null($viewDay)) {
	$sql .= "SUBSTR(payments.time_of_payment,1,10)='$viewDay'";
} else {
	$sql .= "payments.time_of_payment>'$lastDayCloseTime'";
}
$sql .= " ORDER BY payments.time_of_payment";
$payments = array();
$result = mysql_query($sql, $link);
$eurCasse = $dayCloseEur;
$hufCasse = $dayCloseHuf;
$eurCasse2 = $dayCloseEur2;
$hufCasse2 = $dayCloseHuf2;
if(!$result) {
	trigger_error("Cannot get payments at cash register: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		if($row['comment'] == '*booking deposit*')
			continue;

		if($row['pay_mode'] != 'CASH3') {
			$payments[] = $row;
		}
		if(($row['pay_mode'] == 'CASH' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
			if($row['currency'] == 'EUR')
				$eurCasse += $row['amount'];
			else
				$hufCasse += $row['amount'];
		}
		if(($row['pay_mode'] == 'CASH3' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
			if($row['currency'] == 'EUR')
				$eurCasse2 += $row['amount'];
			else
				$hufCasse2 += $row['amount'];
		}

	}
}

if(!is_null($viewDay)) {
	$sql = "SELECT * FROM cash_out WHERE SUBSTR(time_of_payment,1,10)='$viewDay' ORDER BY time_of_payment";
} else {
	$sql = "SELECT * FROM cash_out WHERE time_of_payment>'$lastDayCloseTime' ORDER BY time_of_payment";
}
$cashOuts = array();
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get cashout at cash register: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		if($row['pay_mode'] != 'CASH3') {
			$cashOuts[] = $row;
		}
		if(($row['pay_mode'] == 'CASH' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
			if($row['currency'] == 'EUR')
				$eurCasse -= $row['amount'];
			else
				$hufCasse -= $row['amount'];
		}
		if(($row['pay_mode'] == 'CASH3' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
			if($row['currency'] == 'EUR')
				$eurCasse2 -= $row['amount'];
			else
				$hufCasse2 -= $row['amount'];
		}

	}
}

if(!is_null($viewDay)) {
	$sql = "SELECT * FROM guest_transfer WHERE SUBSTR(time_of_enter,1,10)='$viewDay' AND amount_value>0 ORDER BY time_of_enter";
} else {
	$sql = "SELECT * FROM guest_transfer WHERE time_of_enter>'$lastDayCloseTime' AND amount_value>0 ORDER BY time_of_enter";
}
$gtransfers = array();
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get guest transfers at cash register: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		if($row['pay_mode'] != 'CASH3') {
			$gtransfers[] = $row;
		}
		if(($row['pay_mode'] == 'CASH' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
			if($row['amount_currency'] == 'EUR')
				$eurCasse += $row['amount_value'];
			else
				$hufCasse += $row['amount_value'];
		}
		if(($row['pay_mode'] == 'CASH3' or $row['pay_mode'] == 'CASH2') and $row['storno'] != 1) {
			if($row['amount_currency'] == 'EUR')
				$eurCasse2 += $row['amount_value'];
			else
				$hufCasse2 += $row['amount_value'];
		}

	}
}



$eurCasse = sprintf('%.2f', $eurCasse);

$cashTypes = '';
$sql = "SELECT * FROM cashout_type ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get cashout types: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$cashTypes .= '<option value="' . $row['type'] . '">' . $row['type'] . '</option>';
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

	<script type="text/javascript">
		function copyViewDayValue(value) {
			$("view_day").setValue(value);
		}
	</script>

	<style type="text/css">
		table.cash_register {
			border-spacing:0;
			border-collapse:collapse;
		}

		table.cash_register tr td {
			border-top: 1px solid black;
			margin: 0px 2px 0px 2px;
		}
		table.cash_register tr td.amount {
			border-left: 1px solid black;
			padding: 1px 5px 1px 5px;
		}
	</style>

EOT;



html_start("Maverick Reception - Cash Register", $extraHeader);


$fromName = $_SERVER['REMOTE_USER'];

echo <<<EOT

<form action="save_cash_out.php" method="POST" accept-charset='UTF-8' style="display: block; float: right; clear: right;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Save Cashout</th></tr>
	<tr>
		<td style="width: 100px;">Type: </td>
		<td><select name="type" style="width: 100px;">$cashTypes</select></td>
	</tr>
	<tr>
		<td style="width: 100px;">Mode: </td>
		<td><select name="pay_mode" style="width: 100px;"><option value="CASH">Cash</option><option value="CASH2">Cash2</option></select></td>
	</tr>
	<tr>
		<td style="width: 100px;">Receiver: </td>
		<td><input name="receiver"  style="width: 100px;"/></td>
	</tr>
	<tr>
		<td style="width: 100px;">Amount: </td>
		<td style="width: 100px;">
			<input id="amount" name="amount" style="width: 50px;" maxlength="10" type="text"> <select name="currency"  style="width: 50px;"><option value="EUR">EUR</option><option value="HUF">HUF</option></select>
		</td>
	</tr>
	<tr>
		<td style="width: 100px;">Comment: </td>
		<td>
			<input style="width: 100px;" name="comment"> 
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="Save cashout">
	</td></tr>
</table>

</form>

<form action="save_cash_in.php" method="POST" accept-charset='UTF-8' style="display: block; float: right; clear: right;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Save Cash-in</th></tr>
	<tr>
		<td style="width: 100px;">Type: </td>
		<td><select name="type" style="width: 100px;">$cashTypes</select></td>
	</tr>
	<tr>
		<td style="width: 100px;">Mode: </td>
		<td><select name="pay_mode" style="width: 100px;"><option value="CASH">Cash</option><option value="CASH2">Cash2</option></select></td>
	</tr>
	<tr>
		<td>Payee: </td>
		<td><input name="payee" style="width: 100px;"/></td>
	</tr>
	<tr>
		<td>Amount: </td>
		<td style="width: 100px;">
			<input id="amount" name="amount" style="width: 50px;" maxlength="10" type="text"> <select style="width: 50px;" name="currency"><option value="EUR">EUR</option><option value="HUF">HUF</option></select>
		</td>
	</tr>
	<tr>
		<td style="width: 100px;">Comment: </td>
		<td>
			<input style="width: 100px;" name="comment"> 
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="Save cash-in">
	</td></tr>
</table>
</form>



<form action="view_cash_register.php" method="GET" style="display: block; float: right; clear: right;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">View cash reg. for one day</th></tr>
	<tr>
		<td style="width: 100px;">
			<div id="view_day_chooser_pos" style="width:0px;height:20px;float:left;"></div>
			View day: 
		</td>
		<td style="width: 100px;">
			<input id="view_day" name="view_day" style="width: 70px; maxlength="10" type="text" value="$viewDay"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'view_day', 'chooserSpanVD', 2008, 2025, 'Y-m-d', false, 'view_day_chooser_pos');"> 
			<div id="chooserSpanVD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="View selected day"><br>
		<input type="button" value="View active" style="font-weight: bold;" onclick="window.location='view_cash_register.php';">
	</td></tr>
</table>
</form>



EOT;

if(is_null($viewDay)) {
	echo <<<EOT

<form action="save_day_close.php" method="POST" accept-charset='UTF-8' style="display: block; float: right; clear: right;">
<input type="hidden" name="casseHUF" value="$hufCasse">
<input type="hidden" name="casseEUR" value="$eurCasse">
<input type="hidden" name="casseHUF2" value="$hufCasse2">
<input type="hidden" name="casseEUR2" value="$eurCasse2">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Close Day</th></tr>
	<tr>
		<td style="width: 100px;">From: </td>
		<td style="width: 100px;">$fromName</td>
	</tr>
	<tr>
		<td style="width: 100px;">To (login): </td>
		<td><input style="width: 100px;" name="to_login"></td>
	</tr>
	<tr>
		<td style="width: 100px;">To (pwd): </td>
		<td><input style="width: 100px;" type="password" name="to_pwd"></td>
	</tr>
	<tr>
		<td colspan="2"><b>In cash register</b></td>
	</tr>
	<tr>
		<td style="width: 100px;">EUR: </td>
		<td style="width: 100px;">$eurCasse euro</td>
	</tr>
	<tr>
		<td style="width: 100px;">HUF: </td>
		<td style="width: 100px;">$hufCasse Ft</td>
	</tr>
	<tr>
		<td style="width: 100px;">Comment: </td>
		<td><input style="width: 100px;" name="comment"></td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="Close Day">
	</td></tr>
</table>
</form>

EOT;
}

if(is_null($viewDay)) {
	echo "<h2><span style=\"color: red;\">ACTIVE DAY</span> - List of payments and cash outs since <strong>$lastDayCloseTime</strong></h2>\n";
} else {
	echo "<h2>PAST DAY - List of payments and cash outs for the day: <strong>$viewDay</strong></h2>\n";
}

echo <<<EOT

<form style="display: block;">
	<input style="float: none; display: none;" type="button" id="showStornoBtn" value="Show storno items" onclick="$$('tr.tr_storno').invoke('show');$('hideStornoBtn').show();$('showStornoBtn').hide();return false;">
	<input style="float: none; display: inline;" type="button" id="hideStornoBtn" value="Hide storno items" onclick="$$('tr.tr_storno').invoke('hide');$('hideStornoBtn').hide();$('showStornoBtn').show();return false;">
	<br>
	<input style="float: none; display: none;" type="button" id="showNonCashBtn" value="Show non-cash items" onclick="$$('tr.tr_non_cash').invoke('show');$('hideNonCashBtn').show();$('showNonCashBtn').hide();return false;">
	<input style="float: none; display: inline;" type="button" id="hideNonCashBtn" value="Hide non-cash items" onclick="$$('tr.tr_non_cash').invoke('hide');$('hideNonCashBtn').hide();$('showNonCashBtn').show();return false;">
</form>

<table class="cash_register">
	<tr><th rowspan="2">Type</th><th rowspan="2">Date</th><th rowspan="2">Name</th><th rowspan="2">Comment</th><th colspan="2">Received</th><th colspan="2">Cash Out</th></tr>
	<tr><th>HUF</th><th>EUR</th><th>HUF</th><th>EUR</th></tr>

EOT;


$takePymt = false;
$takeCo = false;
$takeGt = false;
$pymtIdx = 0;
$coIdx = 0;
$gtIdx = 0;
$paymentEur = 0;
$paymentHuf = 0;
$cashoutEur = 0;
$cashoutHuf = 0;
while(count($payments) > $pymtIdx or count($cashOuts) > $coIdx or count($gtransfers) > $gtIdx) {
	$p = null;
	$c = null;
	$g = null;
	$arr = array();
	if(count($payments) > $pymtIdx) {
		$p = $payments[$pymtIdx]['time_of_payment'];
		$arr[] = $p;
	}
	if(count($cashOuts) > $coIdx) {
		$c = $cashOuts[$coIdx]['time_of_payment'];
		$arr[] = $c;
	}
	if(count($gtransfers) > $gtIdx) {
		$g = $gtransfers[$gtIdx]['time_of_enter'];
		$arr[] = $g;
	}

	$takePymt = false;
	$takeCo = false;
	$takeGt = false;

	$m = min($arr);

	$takePymt = ($p == $m);
	$takeCo = ($c == $m);
	$takeGt = ($g == $m);


	if(count($arr) < 1) {
		break;
	}

	$bid = -1;
	$bdid = -1;
	$storno = false;
	$style = '';
	$cashTr = true;

	if($takePymt) {
		$payment = $payments[$pymtIdx];
		$id = $payment['id'];
		$storno = $payment['storno'] == 1;
		$cashTr = (($payment['pay_mode'] == 'CASH') or ($payment['pay_mode'] == 'CASH2'));
		$bdid = $payment['booking_description_id'];
		if($cashTr and !$storno) {
			if($payment['currency'] == 'EUR') {
				$paymentEur += $payment['amount'];
			} else {
				$paymentHuf += $payment['amount'];
			}
		}
		$type = "Payment";
		$time = $payment['time_of_payment'];
		$name = "<a href=\"edit_booking.php?description_id=" . $payment['booking_description_id'] . "\">" . $payment['name'] . "</a>";
		$comment = $payment['comment'];
		if($payment['currency'] == 'EUR') {
			$amtColumns = "<td class=\"amount\">&nbsp;</td><td align=\"right\" class=\"amount\">" . sprintf('%.2f', $payment['amount']) . "&nbsp;EUR</td><td class=\"amount\">&nbsp;</td><td class=\"amount\">&nbsp;</td>\n";
		} else {
			$amtColumns = "<td align=\"right\" class=\"amount\">" . $payment['amount'] . "&nbsp;HUF</td><td class=\"amount\">&nbsp;</td><td class=\"amount\">&nbsp;</td><td class=\"amount\">&nbsp;</td>\n";
		}
		$stornoType = "payments";

		$pymtIdx += 1;
	} elseif($takeCo) {
		$co = $cashOuts[$coIdx];
		$id = $co['id'];
		$storno = $co['storno'] == 1;
		$cashTr = (($co['pay_mode'] == 'CASH') or ($co['pay_mode'] == 'CASH2'));
		$time = $co['time_of_payment'];
		$name = $co['receiver'];
		$type = $co['type'];
		$comment = $co['comment'];
		if($co['amount'] < 0) {
			$type .=  ' (cash-in)';
			if($co['currency'] == 'EUR') {
				if(!$storno and $cashTr) {
					$paymentEur += abs($co['amount']);
				}
				$amtColumns = "<td class=\"amount\">&nbsp;</td><td align=\"right\" class=\"amount\">" . sprintf('%.2f', abs($co['amount'])) . "&nbsp;EUR</td>" ;
			} else {
				if(!$storno) {
					$paymentHuf += abs($co['amount']);
				}
				$amtColumns = "<td align=\"right\" class=\"amount\">" . abs($co['amount']) . "&nbsp;HUF</td><td class=\"amount\">&nbsp;</td>";
			}
			$amtColumns .= "<td class=\"amount\">&nbsp;</td><td class=\"amount\">&nbsp;</td>";
		} else {
			$type .=  ' (cash-out)';
			$amtColumns = "<td class=\"amount\">&nbsp;</td><td class=\"amount\">&nbsp;</td>";
			if($co['currency'] == 'EUR') {
				if(!$storno and $cashTr) {
					$cashoutEur += abs($co['amount']);
				}
				$amtColumns .= "<td class=\"amount\">&nbsp;</td><td align=\"right\" class=\"amount\">" . sprintf('%.2f', abs($co['amount'])) . "&nbsp;EUR</td>" ;
			} else {
				if(!$storno and $cashTr) {
					$cashoutHuf += abs($co['amount']);
				}
				$amtColumns .= "<td class=\"amount\" align=\"right\">" . abs($co['amount']) . "&nbsp;HUF</td><td class=\"amount\">&nbsp;</td>";
			}
		}

		$stornoType = "cash_out";
		$coIdx += 1;
	} elseif($takeGt) {
		$gt = $gtransfers[$gtIdx];
		$id = $gt['id'];
		$storno = $gt['storno'] == 1;
		$cashTr = (($gt['pay_mode'] == 'CASH') or ($gt['pay_mode'] == 'CASH2'));
		if($cashTr and !$storno) {
			if($gt['amount_currency'] == 'EUR') {
				$paymentEur += $gt['amount_value'];
			} else {
				$paymentHuf += $gt['amount_value'];
			}
		}
		$type = "Guest transfer to " . $gt['destination'];
		$time = $gt['time_of_enter'];
		$name = $gt['name'];
		$comment = $gt['comment'];
		if($gt['amount_currency'] == 'EUR') {
			$amtColumns = "<td class=\"amount\">&nbsp;</td><td align=\"right\" class=\"amount\">" . sprintf('%.2f',$gt['amount_value']) . "&nbsp;EUR</td>" ;
		} else {
			$amtColumns =  "<td class=\"amount\" align=\"right\">" . $gt['amount_value'] . "&nbsp;HUF</td><td class=\"amount\">&nbsp;</td>";
		}
		$amtColumns .= "<td class=\"amount\">&nbsp;</td><td class=\"amount\">&nbsp;</td>";
		$stornoType = "guest_transfer";
		$gtIdx += 1;
	}

	$rowClass = '';
	if($storno) {
		$style .= 'background-color: #ffdddd;';
		$rowClass .= " tr_storno";
	}

	if(!$cashTr) {
		$rowClass .= " tr_non_cash";
		$style .= 'color: #999999;';
	}
	if($comment == '') {
		$comment = '&nbsp;';
	}
	if($name == '') {
		$name = '&nbsp;';
	}

	if($storno or $time < $lastDayCloseTime) {
		$stornoUrl = '&nbsp;';
	} else {
		$stornoUrl = "<a href=\"#\" onclick=\"if(confirm('Sztorno?')) { window.location='storno.php?type=$stornoType&id=$id&bdid=$bdid&bid=$bid'; }\">Sztorno</a>";
	}

	echo "	<tr class=\"$rowClass\" style=\"$style\"><td>$type</td><td>$time</td><td>$name</td><td>$comment</td>$amtColumns<td>$stornoUrl</td></tr>";
}

$paymentEur = sprintf('%.2f', $paymentEur);
$cashoutEur = sprintf('%.2f', $cashoutEur);
$dayCloseEur = sprintf('%.2f', $dayCloseEur);

echo <<<EOT
	<tr><td colspan="9"><hr></td></tr>
	<tr><td colspan="4"><strong>Total</strong></td><td align="right" class="amount">$paymentHuf&nbsp;HUF</td><td align="right" class="amount">$paymentEur&nbsp;EUR</td><td align="right" class="amount">$cashoutHuf&nbsp;HUF</td><td align="right" class="amount">$cashoutEur&nbsp;EUR</td><td>&nbsp;</td></tr>
	<tr><td colspan="4"><strong>Balance from previous day close</strong></td><td align="right" class="amount">$dayCloseHuf&nbsp;HUF</td><td align="right" class="amount">$dayCloseEur&nbsp;EUR</td><td align="right" class="amount">&nbsp;</td><td align="right" class="amount">&nbsp;</td><td>&nbsp;</td></tr>
</table>


EOT;

if(is_null($viewDay)) {
	echo "<h2>Total HUF: $hufCasse Ft</h2>\n";
	echo "<h2>Total EUR: $eurCasse euro</h2>\n";
}


mysql_close($link);

html_end();



?>
