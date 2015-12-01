<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->


EOT;

$link = db_connect();

$destinationOptions = '				<option value="">All</option>' . "\n";
$sql = "SELECT DISTINCT destination FROM guest_transfer ORDER BY destination";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	$dest = $row['destination'];
	if(isset($_REQUEST['destination']) and $dest == $_REQUEST['destination']) {
		$destinationOptions .= "\t\t\t\t<option selected value=\"$dest\">$dest</option>\n";
	} else {
		$destinationOptions .= "\t\t\t\t<option value=\"$dest\">$dest</option>\n";
	}
}

html_start("Guest Transfer", $extraHeader);

$today = date('Y-m-d');
echo <<<EOT

<form action="view_guest_transfer.php" method="get" accept-charset="utf-8" style="float: left; border: 1px solid black; margin: 10px; padding: 5px;">
<table>
	<tr><th colspan="2">Search Guest Transfer</th></tr>
	<tr>
		<td>Destination</td>
		<td>
			<select name="destination">
$destinationOptions
			</select>
		</td>
	</tr>
	<tr><td colspan="2"><input type="submit" value="Search"></td></tr>
</table>
</form>

<form action="add_guest_transfer.php" method="post" accept-charset="utf-8" style="float: left; border: 1px solid black; margin: 10px; padding: 5px;">
<table>
	<tr><th colspan="2">New Guest Transfer</th></tr>
	<tr>
		<td>Destination</td>
		<td><input name="destination"></td>
	</tr>
	<tr>
		<td>Name</td>
		<td><input name="name"></td>
	</tr>
	<tr>
		<td>Arrival</td>
		<td>
			<input id="arrival_date" name="arrival_date" size="10" maxlength="10" type="text"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'arrival_date', 'chooserSpanAD', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanAD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>Number of nights</td>
		<td><input name="nights" size="2"></td>
	</tr>
	<tr>
		<td>Amount</td>
		<td><input name="amount_value" size="4"> <select name="amount_currency">
			<option value="EUR">EUR</option>
			<option value="HUF">HUF</option>
		</select></td>
	</tr>
	<tr>
		<td>Payment mode</td>
		<td><input type="radio" name="pay_mode" value="CASH" checked>Cash<br><input type="radio" name="pay_mode" value="BANK_TRANSFER">Bank Transfer<br><input type="radio" name="pay_mode" value="CREDIT_CARD">Credit Card</td>
	</tr>
	<tr>
		<td>Comment</td>
		<td><textarea name="comment"></textarea></td>
	</tr>
	<tr><td colspan="2"><input type="submit" value="Save"></td></tr>
</table>
</form>

<div style="clear: both;"></div>

EOT;

if(!isset($_REQUEST['destination'])) {
	mysql_close($link);
	return;
}

$destination = $_REQUEST['destination'];


$sql = "SELECT * FROM guest_transfer ";
if(strlen($destination) > 0) {
	$sql .= " WHERE destination='$destination'";
}
$sql .= " ORDER BY first_night";

$result = mysql_query($sql, $link);

$dest = $destination;
if(strlen($dest) <= 0) {
	$dest = 'All';
}

echo <<<EOT
<h2>Guest Transfers to $dest</h2>
<table>
	<tr><th>Name</th><th>Destination</th><th>1st night</th><th># nights</th><th>Amount</th><th>Payment Mode</th><th>Comment</th></tr>

EOT;
$totalEur = 0;
while($row = mysql_fetch_assoc($result)) {
	$id = $row['id'];
	$name = $row['name'];
	$dest = $row['destination'];
	$fnight = $row['first_night'];
	$numOfNights = $row['num_of_nights'];
	$amtVal = $row['amount_value'];
	$amtCurr = $row['amount_currency'];
	$comment = $row['comment'];
	$mode = str_replace('_', ' ', $row['pay_mode']);
	$mode = ucwords(strtolower($mode));
	$param = urlencode($destination);
	if($amtVal <= 0) {
		$amtVal = '';
		$amtCurr = '';
	} elseif($row['storno'] != 1) {
		$totalEur += convertAmount($amtVal, $amtCurr, 'EUR', substr($row['time_of_enter'], 0, 10));
	}
	if($amtCurr == 'HUF' && $row['storno'] != 1) {
		$comment .= ' (' . intval(convertAmount($amtVal, $amtCurr, 'EUR', substr($row['time_of_enter'], 0, 10))) . ' EUR)';
	}
	$style = '';
	if($row['storno'] == 1) {
		$style = 'text-decoration: line-through';
	}
	echo <<<EOT
	<tr style="$style"><td>$name</td><td>$dest</td><td>$fnight</td><td>$numOfNights</td><td align="right">$amtVal $amtCurr</td><td>$mode</td><td>$comment</td><td><a href="delete_guest_transfer.php?id=$id&destination=$param">Delete</a></td></tr>

EOT;
}

$totalEur = intval($totalEur);
echo <<<EOT
	<tr><td colspan="4"><strong>Total</strong></td><td align="right"><strong>$totalEur EUR</strong></td></tr>
</table>


EOT;

mysql_close($link);

html_end();


?>
