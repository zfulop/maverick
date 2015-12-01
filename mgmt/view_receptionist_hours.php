<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

$login = $_REQUEST['login'];
$year = $_REQUEST['year'];
$month = $_REQUEST['month'];

$sql = "SELECT rs.*, ws.name AS ws_name, ws.start_time, ws.end_time, ws.duration_hour FROM reception_schedule rs INNER JOIN working_shift ws ON rs.working_shift_id=ws.id WHERE rs.login='$login' AND rs.day>='$year-$month-01' AND rs.day<='$year-$month-31' ORDER BY rs.day";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get receptionis schedule in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
$schedule = array();
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		$schedule[] = $row;
	}
}

mysql_close($link);


html_start("Receptionists - Hours worked for month $year-$month");


echo <<<EOT

<table>
	<tr><th>Day</th><th>Shift Name</th><th>Duration</th></tr>

EOT;

$shifts = array();
foreach($schedule as $row) {
	$id = $row['id'];
	$login = $row['login'];
	$wsName = $row['ws_name'];
	echo "	<tr>\n";
	echo "		<td>" . $row['day'] . "</td><td>" . $row['ws_name'] . "</td><td>" . $row['duration_hour'] . " hrs</td>\n";
	echo "	</tr>\n";
	if(!isset($shifts[$wsName])) {
		$shifts[$wsName] = 0;
	}
	$shifts[$wsName] += 1;
}

echo <<<EOT
</table>

<br>
<table>
	<tr><th colspan="2">Total number of shifts worked:</th></tr>
	<tr><th>Shift name</th><th>Number</th></tr>

EOT;

foreach($shifts as $name => $num) {
	echo "	<tr><td>$name</td><td align=\"right\">$num</td></tr>\n";
}

echo <<<EOT
</table>

EOT;

html_end();



?>
