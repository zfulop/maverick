<?php

require("includes.php");


$link = db_connect();

$startDate = '';
$endDate = '';
$serviceOptions = '';
$selectedService =  '';
if(isset($_REQUEST['start_date'])) {
	$startDate = $_REQUEST['start_date'];
}
if(isset($_REQUEST['end_date'])) {
	$endDate = $_REQUEST['end_date'];
} else {
	$endDate = date('Y-m-d');
}

if(isset($_REQUEST['service'])) {
	$selectedService = $_REQUEST['service'];
}

$sql = "SELECT * FROM cashout_type ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
	$err = "Cannot get service charges types.";
	set_error($err);
	trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}


while($row = mysql_fetch_assoc($result)) {
	$serviceOptions .= "<option value=\"" . $row['type'] .  "\"" . ($selectedService == $row['type'] ? ' selected' : '') . ">" . $row['type'] . "</option>";
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
	function updateStartEndDate(selectEl) {
		var type = selectEl.options[selectEl.selectedIndex].value;
		new Ajax.Request('service_charges_start_date.php', {
			method: 'GET',
			parameters: {'type':type},
			onComplete: function(transport) {
				document.getElementById('start_date').value = transport.responseText;
			}
		});
	}
</script>


EOT;


$serviceCharges = array();
if($selectedService != '') {
	$sql = "SELECT sc.* FROM service_charges sc WHERE sc.type='$selectedService' AND SUBSTR(sc.time_of_service,1,10)>='$startDate' AND SUBSTR(sc.time_of_service,1,10)<='$endDate' ORDER BY sc.time_of_service";
	//set_message($sql);
	$result = mysql_query($sql, $link);
	if(!$result) {
		$err = "Cannot get service charges for period: $startDate - $endDate";
		set_error($err);
		trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$serviceCharges[] = $row;
		}
	}
}

$cashOuts = array();
if($selectedService != '') {
	$sql = "SELECT * FROM cash_out WHERE type='$selectedService' AND SUBSTR(time_of_payment,1,10)>='$startDate' AND SUBSTR(time_of_payment,1,10)<='$endDate' ORDER BY time_of_payment";
	$result = mysql_query($sql, $link);
	if(!$result) {
		$err = "Cannot get cash outs for period: $startDate - $endDate";
		set_error($err);
		trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$cashOuts[] = $row;
		}
	}
}



mysql_close($link);


html_start("Maverick Reception - Service Charges", $extraHeader);

echo <<<EOT
<form action="view_service_charges.php" method="GET"  style="display: block; float: left; ">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Count service charges for period</th></tr>
	<tr>
		<td>Service: </td>
		<td><select name="service" onchange="updateStartEndDate(this);">$serviceOptions</select>
	</tr>
	<tr>
		<td>From: </td>
		<td>
			<input id="start_date" name="start_date" size="10" maxlength="10" type="text" value="$startDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>To: </td>
		<td>
			<input id="end_date" name="end_date" size="10" maxlength="10" type="text" value="$endDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date', 'chooserSpanED', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="Count charges">
	</td></tr>
</table>
</form>


EOT;

if($selectedService == '') {
	html_end();
	return;
}

if(count($serviceCharges) < 1) {
	echo "No service charge found.";
	html_end();
	return;
}

echo <<<EOT

<form action="save_cash_out.php" method="POST" style="display: block; float: left;">
<input type="hidden" name="type" value="$selectedService">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Save Cashout</th></tr>
	<tr>
		<td>Service: </td>
		<td>$selectedService</td>
	</tr>
	<tr>
		<td>Receiver: </td>
		<td><input name="receiver"/></td>
	</tr>
	<tr>
		<td>Amount: </td>
		<td>
			<input id="amount" name="amount" size="8" maxlength="10" type="text"> <select name="currency"><option value="EUR">EUR</option><option value="HUF">HUF</option></select>
		</td>
	</tr>
	<tr>
		<td style="width: 100px;">Mode: </td>
		<td><select name="pay_mode" style="width: 100px;"><option value="CASH">Cash</option><option value="CASH2">Cash2</option></select></td>
	</tr>
	<tr>
		<td>Comment: </td>
		<td>
			<input name="comment"> 
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="Save cashout">
	</td></tr>
</table>
</form>

<h2 style="clear: both;">List of service chanrges for service: <strong>$selectedService [$startDate - $endDate]</strong></h2>

<table>
	<tr><th>Type</th><th>Date</th><th>Comment</th><th>Payment Received</th><th>Cash Out</th></tr>

EOT;

$takeSc = false;
$scIdx = 0;
$coIdx = 0;
$hufPayments = 0;
$eurPayments = 0;
$totalPriceHUF = 0;
$totalPriceEUR = 0;
while(count($serviceCharges) > $scIdx or count($cashOuts) > $coIdx) {
	if(count($serviceCharges) > $scIdx and count($cashOuts) > $coIdx) {
		$takeSc = $serviceCharges[$scIdx]['time_of_service'] < $cashOuts[$coIdx]['time_of_payment'];
	} elseif(count($serviceCharges) > $scIdx) {
		$takeSc = true;
	} elseif(count($cashOuts) > $coIdx) {
		$takeSc = false;
	} else {
		break;
	}

	if($takeSc) {
		$sc = $serviceCharges[$scIdx];
		if($sc['currency'] == 'EUR') {
			$eurPayments += $sc['amount'];
			$totalPriceHUF += convertAmount($sc['amount'], 'EUR', 'HUF', $sc['time_of_service']);
			$totalPriceEUR += $sc['amount'];
		} else {
			$totalPriceHUF += $sc['amount'];
			$hufPayments += $sc['amount'];
			$totalPriceEUR += convertAmount($sc['amount'], 'HUF', 'EUR', $sc['time_of_service']);
		}

		echo "<tr><td>Usage</td><td>" . $sc['time_of_service'] . "</td><td>" . $sc['comment'] . "</td><td align=\"right\">" . $sc['amount'] . " " . $sc['currency'] . ($sc['currency'] != 'EUR' ? ' (' . sprintf('%.2f', convertAmount($sc['amount'], $sc['currency'], 'EUR', $sc['time_of_service'])) . ' EUR)' : '') . "</td></tr>";
		$scIdx += 1;
	} else {
		$co = $cashOuts[$coIdx];
echo "<tr><td>Cashout to " . $co['receiver'] . "</td><td>" . $co['time_of_payment'] . "</td><td>" . $co['comment'] . "</td><td></td><td align=\"right\">" . $co['amount'] . " " . $co['currency'] . ($co['currency'] != 'EUR' ? ' (' . sprintf('%.2f', convertAmount($co['amount'], $co['currency'], 'EUR', $co['time_of_payment'])) . ' EUR)' : '') . "</td></tr>";
		$coIdx += 1;
	}
}

$totalPriceEUR = sprintf('%.2f', $totalPriceEUR);

echo <<<EOT
	<tr><td colspan="3"><b>Total payments received</b></td><td align="right"><b>$totalPriceEUR EUR ($totalPriceHUF HUF)</b></td></tr>
</table>


EOT;


html_end();

?>
