<?php

require("includes.php");

$link = db_connect();

if(isset($_REQUEST['vacation_month'])) {
	$_SESSION['vacation_month'] = $_REQUEST['vacation_month'];
}
if(!isset($_SESSION['vacation_month'])) {
	$_SESSION['vacation_month'] = date('Y-m');
}

$currentMonth = $_SESSION['vacation_month'];
$currentMonthStart = $currentMonth . '-01';
$currentMonthNumDays = date('t', strtotime($currentMonthStart));
$currentMonthEnd = $currentMonth . '-' . $currentMonthNumDays;
$prevMonth = date('Y-m', strtotime($currentMonthStart . ' -1 month'));
$nextMonth = date('Y-m', strtotime($currentMonthStart . ' +1 month'));

$sql = "SELECT * FROM vacations WHERE to_date>='$currentMonthStart' AND from_date<='$currentMonthEnd'" ;
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get vacations in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
$vacations = array();
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		if(!isset($vacations[$row['login']])) {
			$vacations[$row['login']] = array();
		}
		$vacations[$row['login']][] = $row;
	}
}


$sql = "SELECT * FROM receptionists ORDER BY name";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get receptionists in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
$receptionists = array();
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		if(in_array($row['login'], array('Peter','Suni','Kristof'))) {
			continue;
		}
		$receptionists[] = $row;
	}
}

mysql_close($link);


html_start("Maverick Reception - Vacations");


echo <<<EOT

<div style="font-size:130%;">
<a href="view_vacations.php?vacation_month=$prevMonth">&lt;&lt;&lt; Previous month</a> | <a href="view_vacations.php?vacation_month=$nextMonth">Next month &gt;&gt;&gt;</a>
</div>

<h2>Vacations for the month: $currentMonth</h2>
<table border="1">

EOT;

echo "	<tr><th>Receptionist</th>";
for($i = 1; $i <= $currentMonthNumDays; $i++) {
	echo "<th>$i</th>";
}
echo "</tr>\n";

foreach($receptionists as $row) {
	$login = $row['login'];
	$name = $row['name'];
	echo "<tr><th>$name</th>";
	for($i = 1; $i <= $currentMonthNumDays; $i++) {
		$currDate = $currentMonth . '-' . ($i < 10 ? '0' : '') . $i;
		$cell = '&nbsp;';
		if(isset($vacations[$login])) {
			foreach($vacations[$login] as $oneVacation) {
				if($oneVacation['from_date'] <= $currDate and $oneVacation['to_date'] >= $currDate) {
					$cell = "X";
					break;
				}
			}
		}
		echo "<td>$cell</td>";
	}
	echo "	</tr>\n";
}



echo <<<EOT
</table>

EOT;


html_end();



?>
