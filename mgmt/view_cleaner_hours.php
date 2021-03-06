<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

$login = $_REQUEST['login'];
$year = $_REQUEST['year'];
$month = $_REQUEST['month'];

$sql = "SELECT cs.*, ws.name AS ws_name, ws.start_time, ws.end_time, ws.duration_hour FROM cleaning_schedule cs INNER JOIN working_shift ws ON cs.working_shift_id=ws.id WHERE cs.cleaner='$login' AND cs.day>='$year-$month-01' AND cs.day<='$year-$month-31' ORDER BY cs.day";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get cleaner schedule in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
$schedule = array();
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		$schedule[] = $row;
	}
}

mysql_close($link);


html_start("Cleaners - Hours worked for month $year-$month");


echo <<<EOT

<table>
	<tr><th>Day</th><th>Shift Name</th><th>Duration</th></tr>

EOT;

$shifts = array();
foreach($schedule as $row) {
	$id = $row['id'];
	$wsName = $row['ws_name'];
	echo "	<tr>\n";
	echo "		<td>" . $row['day'] . "</td><td>$wsName</td><td>" . $row['duration_hour'] . " hrs</td>\n";
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
