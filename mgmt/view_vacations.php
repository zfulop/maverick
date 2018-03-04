<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


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

$vacations = UserDao::getVacations($currentMonthStart, $currentMonthEnd, $link);

$users = UserDao::getUsers($link);
usort($users, array("UserDao", "sortUsersByName"));
$usersOptions = '';
foreach($users as $row) {
	$usersOptions .= '<option value="' . $row['username'] . '">' . $row['name'] . ' [' . $row['role'] . ']</option>';
}

mysql_close($link);


html_start("Vacations");


echo <<<EOT

<form id="create_btn">
<input type="button" onclick="document.getElementById('rec_form').reset();document.getElementById('rec_form').style.display='block'; document.getElementById('create_btn').style.display='none'; document.getElementById('login').disabled=false; return false;" value="New vacation">
</form>
<br>

<form action="save_vacation.php" id="rec_form" accept-charset="utf-8" method="POST" style="display: none;">
<fieldset>
<label>Receptionist</label><select name="login">$usersOptions</select><br>
<label>1st day</label><input name="start_date" size="10" id="start_date"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2013, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div><br>
<label>Last day</label><input name="end_date" size="10" id="end_date"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date', 'chooserSpanED', 2013, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div><br>
</fieldset>
<fieldset>
<input type="submit" value="Save vacation">
<input type="button" value="Cancel" onclick="document.getElementById('rec_form').reset();document.getElementById('rec_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;">
</fieldset>
</form>

<div style="font-size:130%;">
<a href="view_vacations.php?vacation_month=$prevMonth">&lt;&lt;&lt; Previous month</a> | <a href="view_vacations.php?vacation_month=$nextMonth">Next month &gt;&gt;&gt;</a>
</div>

<h2>Vacations for the month: $currentMonth</h2>
<table border="1">

EOT;

$prevRole = '';
usort($users, array("UserDao", "sortUsersByRoleName"));
foreach($users as $row) {
	if($prevRole != $row['role']) {
		echo "<tr><th colspan=\"" . ($currentMonthNumDays + 1) . "\">" . $row['role'] . "</th></tr>\n"; 
		echo "	<tr><th>Name</th>";
		for($i = 1; $i <= $currentMonthNumDays; $i++) {
			echo "<th>$i</th>";
		}
		echo "</tr>\n";
		$prevRole = $row['role'];
	}
	$login = $row['username'];
	$name = $row['name'];
	$role = $row['role'];
	echo "<tr><th>$name</th>";
	for($i = 1; $i <= $currentMonthNumDays; $i++) {
		$currDate = $currentMonth . '-' . ($i < 10 ? '0' : '') . $i;
		$cell = '&nbsp;';
		if(isset($vacations[$login])) {
			foreach($vacations[$login] as $oneVacation) {
				if($oneVacation['from_date'] <= $currDate and $oneVacation['to_date'] >= $currDate) {
					$cell = "<a href=\"delete_vacation.php?id=" . $oneVacation['id'] . "\" title=\"click to delete\">X</a>";
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
