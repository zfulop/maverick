<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

$roomTypeId = $_REQUEST['room_type_id'];
$year = date('Y');
if(isset($_REQUEST['year'])) {
	$year = $_REQUEST['year'];
} else {
	$year = date('Y');
}


$sql = "SELECT rt.* FROM room_types rt WHERE rt.id=$roomTypeId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get room in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
$roomType = mysql_fetch_assoc($result);

$prices = array();
$sql = "SELECT * FROM prices_for_date WHERE room_type_id=$roomTypeId AND date>'$year/01/00' AND date<'$year/13/00'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get room prices in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
while($row = mysql_fetch_assoc($result)) {
	$prices[$row['date']] = $row;
}

$type = $roomType['type'];


$syncResult = '';
if(isset($_SESSION['sync_result'])) {
	$syncResult = 'Sync result: <div style="border: 1px solid black; font-family: Couriel">' . $_SESSION['sync_result'] . "</div>\n";
	unset($_SESSION['sync_result']);
}


html_start("Room Prices ");

echo $syncResult;
echo "<h2>" . $roomType['name'] . "</h2>\n";
echo "<table>\n";
echo "<tr><td>Price per bed: </td><td>" . $roomType['price_per_bed'] . "</td></tr>\n";
echo "<tr><td>Price per room: </td><td>" . $roomType['price_per_room'] . "</td></tr>\n";
echo "<tr><td>Num of beds: </td><td>" . $roomType['num_of_beds'] . "</td></tr>\n";
echo "<tr><td>Num of extra beds: </td><td>" . $roomType['num_of_extra_beds'] . "</td></tr>\n";
echo "<tr><td>Type: </td><td>" . $roomType['type'] . "</td></tr>\n";
echo "</table><br>\n";

echo "<a href=\"view_room_prices.php?room_type_id=$roomTypeId&year=" . ($year - 1) . "\">View prices for previous year</a> \n";
echo "<strong>$year</strong> \n";
echo "<a href=\"view_room_prices.php?room_type_id=$roomTypeId&year=" . ($year + 1) . "\">View prices for next year</a><br>\n";

echo "<p>\n";
echo "<form action=\"save_room_prices.php\" target=\"_blank\" method=\"POST\" id=\"price_form\">\n";
echo "<input type=\"hidden\" name=\"room_type_id\" value=\"$roomTypeId\">\n";
echo "<input type=\"hidden\" name=\"year\" value=\"$year\">\n";
echo "<input type=\"hidden\" name=\"start_date\" value=\"$year-01-01\">\n";
echo "<input type=\"hidden\" name=\"end_date\" value=\"$year-12-31\">\n";
echo "<table border=\"1\">\n";
echo "	<tr><td></td>";
for($i = 1; $i <= 31; $i++) {
	echo "	<td>$i</td>";
}
echo "</tr>\n";

if($roomType['type'] == 'DORM') {
	echo "<strong style=\"font-size: 150%;\">Prices are per BED</strong><br>";
} elseif($roomType['type'] == 'PRIVATE') {
	echo "<strong style=\"font-size: 150%;\">Prices are per ROOM</strong><br>";
} elseif($roomType['type'] == 'APARTMENT') {
	echo "<strong style=\"font-size: 150%;\">Input format in each cell: room price &lt;new line&gt; surcharge per bed</strong><br>";
}

for($i = 1; $i <= 12; $i++) {
	$monthDt = mktime(10, 1, 1, $i, 1, $year);
	$dayOfWeek = date("w", $monthDt);
	echo "	<tr><td>" . date("F", $monthDt) . "</td>\n";
	for($day = 1; $day <= date("t", $monthDt); $day++) {
		$dateStr = "$year/";
		$dateStr .= ($i < 10 ? "0$i/" : "$i/");
		$dateStr .= ($day < 10 ? "0$day" : $day);
		$bgColor = ((($dayOfWeek+$day) % 7) < 2) ? "#AAAA00" : "#FFFFFF";
		echo "		<td style=\"background-color: $bgColor\">";
		$val = '';
		$val2 = '';
		if(isset($prices[$dateStr])) {
			$val = $type == 'DORM' ? $prices[$dateStr]['price_per_bed'] : $prices[$dateStr]['price_per_room'];
			$val2 = $prices[$dateStr]['surcharge_per_bed'];
		}
		if($val2 == '' or $val2 === 0 or is_null($val2)) {
			$val2 = $roomType['surcharge_per_bed'];
		}
		echo "<input style=\"width: 20px; font-size: 9px;\" name=\"$dateStr\" value=\"$val\">";
		if($type == 'APARTMENT') {
			echo "<br><input style=\"width: 20px; font-size: 9px;\" name=\"spb_$dateStr\" value=\"$val2\">";
		}
		echo "</td>\n";
	}
	if(date("t", $monthDt) < 31) {
		echo "		<td colspan=\"" . (31 - date("t", $monthDt)) . "\"></td>\n";
	}
	echo "		<td>EUR&nbsp;for&nbsp;room<br>%&nbsp;surcharge&nbsp;per&nbsp;bed</td>\n";
	echo "	</tr>\n";
}

echo <<<EOT
</table>
Automatic sync: <input name="sync" type="checkbox" value="true" checked="true"><br>
<input type="submit" value="Save Prices">
</form>

EOT;


mysql_close($link);

html_end();



?>
