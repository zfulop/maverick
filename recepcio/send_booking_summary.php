<?php

require("includes.php");


$hostel = $argv[1];

$configFile = ROOT_DIR . '../includes/config/' . $hostel . '.php';
if(file_exists($configFile)) {
	require($configFile);
}

$link = db_connect($hostel);

$yesterday = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 day'));
$sql = "SELECT count(*) AS cnt, source FROM booking_descriptions WHERE create_time>='$yesterday 00:00:00' and create_time<='$yesterday 23:59:59' and maintenance<>1 GROUP BY source";

echo "Getting bookings that were created on $yesterday\n";
$content = "Getting bookings/cancellations for " . LOCATION . " and date: $yesterday<br><br>\n\n";

$result = mysql_query($sql, $link);
if(!$result) {
	echo "ERROR: " . mysql_error($link) . " (SQL: $sql)\n";
	mysql_close($link);
	return;
}

$numOfBookings = array();
$recCancel = array();
$guestCancel = array();
$noshowCancel = array();
while($row = mysql_fetch_assoc($result)) {
	$numOfBookings[$row['source']] = $row['cnt'];
	$recCancel[$row['source']] = 0;
	$guestCancel[$row['source']] = 0;
	$noshowCancel[$row['source']] = 0;
}

$sql = "SELECT a.*, bd.source FROM audit a INNER JOIN booking_descriptions bd ON a.booking_description_id=bd.id WHERE a.type='CANCEL_BOOKING' AND a.time_of_event>='$yesterday 00:00:00' and a.time_of_event<='$yesterday 23:59:59' and bd.maintenance<>1 ";
$result = mysql_query($sql, $link);
if(!$result) {
	echo "ERROR: " . mysql_error($link) . " (SQL: $sql)\n";
	mysql_close($link);
	return;
}
while($row = mysql_fetch_assoc($result)) {
	if(!isset($noshowCancel[$row['source']])) {
		$numOfBookings[$row['source']] = 0;
		$recCancel[$row['source']] = 0;
		$guestCancel[$row['source']] = 0;
		$noshowCancel[$row['source']] = 0;
	}
	if(strstr($row['data'],'[type] => no_show')) {
		$noshowCancel[$row['source']] += 1;
	} elseif(strstr($row['data'],'[type] => reception')) {
		$recCancel[$row['source']] += 1;
	} elseif(strstr($row['data'],'[type] => guest')) {
		$guestCancel[$row['source']] += 1;
	}
}


$content .= "<table>\n";
$content .= "<tr><th></th><th>Bookings</th><th>Recepcio Cancel</th><th>Guest Cancel</th><th>No show</th></tr>\n";
$bookingSum = 0;
$recCancelSum = 0;
$guestCancelSum = 0;
$noshowCancelSum = 0;
foreach(array_keys($numOfBookings) as $source) {
	$bookingSum += $numOfBookings[$source];
	$recCancelSum += $recCancel[$source];
	$guestCancelSum += $guestCancel[$source];
	$noshowCancelSum += $noshowCancel[$source];
	$content .= "<tr><th style=\"text-align:right;\">$source</th><td style=\"text-align:center;\">" . $numOfBookings[$source] . "</td><td style=\"text-align:center;\">" . $recCancel[$source] . "</td><td style=\"text-align:center;\">" . $guestCancel[$source] . "</td><td style=\"text-align:center;\">" . $noshowCancel[$source] . "</td></tr>\n";
}

$content .= <<<EOT
<tr><td colspan="5"><hr></td></tr>
<tr><th style="text-align:right;">Total</th><td style="text-align:center;">$bookingSum</td><td style="text-align:center;">$recCancelSum</td><td style="text-align:center;">$guestCancelSum</td><td style="text-align:center;">$noshowCancelSum</td></tr>
</table>

EOT;

mysql_close($link);

echo "Sending mail with content: $content\n";
sendMail(CONTACT_EMAIL, LOCATION, /*'zfulop@zolilla.com'*/'sfulop@mavericklodges.com', 'FS', LOCATION . " - Daily stats", $content);
sendMail(CONTACT_EMAIL, LOCATION, /*'zfulop@zolilla.com'*/'ipalma@wannasherpa.com', 'FS', LOCATION . " - Daily stats", $content);

?>