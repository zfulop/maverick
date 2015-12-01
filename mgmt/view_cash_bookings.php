<?php

$START_DATE = '2014-09-20';
require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

$sql = "SELECT bd.* FROM booking_descriptions bd INNER JOIN payments pc ON (bd.id=pc.booking_description_id AND pc.pay_mode='CASH' AND pc.storno<>1 AND pc.type='Szobabevétel') LEFT OUTER JOIN payments pc3 ON (bd.id=pc3.booking_description_id AND pc3.pay_mode='CASH3' AND pc3.storno<>1) WHERE bd.cancelled<>1 AND pc3.id is null AND pc.time_of_payment>'$START_DATE'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get bookings with cash payments in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
$bookings = array();
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT sum(room_payment) FROM bookings WHERE description_id=" . $row['id'];
		$result2 = mysql_query($sql, $link);
		$row['room_payment'] = mysql_result($result2, 0);
		$sql = "SELECT amount, currency, time_of_payment, comment FROM payments where storno<>1 AND type='Szobabevétel' AND pay_mode='CASH' AND booking_description_id=" . $row['id'];
		$result2 = mysql_query($sql, $link);
		$payments = array();
		while($row2 = mysql_fetch_assoc($result2)) {
			$payments[] = $row2;
		}
		$row['payments'] = $payments;
		$bookings[] = $row;
	}
}

mysql_close($link);

html_start("Cash payment bookings");

echo <<<EOT

Cash bookings that are paid and not cancelled since $START_DATE<br>

<table>
	<tr><th>1st day</th><th>Last day</th><th>Name</th><th>Room payment</th><th>Cash payment</th><th>CASH3 payment</th></tr>

EOT;

foreach($bookings as $row) {
	$id = $row['id'];
	$firstNight = $row['first_night'];
	$lastNight = $row['last_night'];
	$name = $row['name'];
	$roomPayment = $row['room_payment'];
	$payments = "<table>";
	$amount = 0;
	$currency = null;
	foreach($row['payments'] as $onePayment) {
		$payments .= "<tr><td>" . $onePayment['amount']  . "</td><td>" . $onePayment['currency'] . "</td></tr>";
		if(is_null($currency)) { $currency = $onePayment['currency']; }
		if($currency == $onePayment['currency']) { $amount += $onePayment['amount']; }
	}
	$payments .= "</table>";
	echo "	<tr>\n";
	echo "		<td>$firstNight</td><td>$lastNight</td><td>$name</td><td>$roomPayment EUR</td><td>$payments</td><td><form style=\"margin: 0px;\" action=\"save_cash_payment.php\" method=\"POST\"><input type=\"hidden\" name=\"booking_description_id\" value=\"$id\"><input name=\"amount\" value=\"$amount\" size=\"4\"><input name=\"currency\" value=\"$currency\" size=\"2\"><input type=\"submit\" value=\"Save payment\"></form></td>\n";
	echo "	</tr>\n";
}

echo <<<EOT
</table>

EOT;


html_end();



?>
