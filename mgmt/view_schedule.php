<?php

require("includes.php");


if(!checkLogin(SITE_MGMT)) {
	return;
}



$link = db_connect();

if(!isset($_SESSION['schedule_year'])) {
	$_SESSION['schedule_year'] = date('Y');
}
if(!isset($_SESSION['schedule_month'])) {
	$_SESSION['schedule_month'] = date('m');
}

if(isset($_REQUEST['year'])) {
	$_SESSION['schedule_year'] = $_REQUEST['year'];
}
if(isset($_REQUEST['month'])) {
	$_SESSION['schedule_month'] = $_REQUEST['month'];
}

$currYear = $_SESSION['schedule_year'];
$currMonth = $_SESSION['schedule_month'];

$currDate = strtotime($currYear . "-" . $currMonth . "-01");

if($currMonth == 1) {
	$prevMonth = 12;
	$prevYear = $currYear - 1;
} else {
	$prevMonth = $currMonth - 1;
	$prevYear = $currYear;
}

if($currMonth == 12) {
	$nextMonth = 1;
	$nextYear = $currYear + 1;
} else {
	$nextMonth = $currMonth + 1;
	$nextYear = $currYear;
}

if(strlen($currMonth) < 2) {
	$currMonth = '0' . $currMonth;
}

$today = date('Y-m-d');
$sql = "SELECT * FROM working_shift WHERE valid_to IS NULL or valid_to>='$today' ORDER BY start_time ";
$result = mysql_query($sql, $link);

$shifts = array();
if(!$result) {
	trigger_error("Cannot get work shifts: " . mysql_error($link) . " (SQL: $sql)");
}
while($row=mysql_fetch_assoc($result)) {
		$shifts[] = $row;
}

$r_entries = array();
$sql = "SELECT rs.*, ws.start_time, ws.end_time, ws.duration_hour FROM reception_schedule rs INNER JOIN working_shift ws ON rs.working_shift_id=ws.id WHERE rs.day LIKE '$currYear-$currMonth-%'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get reception schedule for month: $currYear-$currMonth. Error: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Error loading reception schedule for month: $currYear-$currMonth");
} else {
	while($row = mysql_fetch_assoc($result)) {
		$key = $row['day'];
		if(!isset($r_entries[$key]))
			$r_entries[$key] = array();

		$startTime = $row['start_time'];
		if(strlen($startTime) == 4)
			$startTime = '0' . $startTime;

		$r_entries[$key][$startTime] = $row;
	}
}

$c_entries = array();
$sql = "SELECT cs.*, ws.start_time, ws.end_time, ws.duration_hour FROM cleaning_schedule cs INNER JOIN working_shift ws ON cs.working_shift_id=ws.id WHERE cs.day LIKE '$currYear-$currMonth-%'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get cleaning schedule for month: $currYear-$currMonth. Error: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Error loading cleaning schedule for month: $currYear-$currMonth");
} else {
	while($row = mysql_fetch_assoc($result)) {
		$key = $row['day'];
		if(!isset($c_entries[$key]))
			$c_entries[$key] = array();

		$startTime = $row['start_time'];
		if(strlen($startTime) == 4)
			$startTime = '0' . $startTime;

		$c_entries[$key][$startTime] = $row;
	}
}


$receptionistHtmlOptions = '';
$cleanerHtmlOptions = '';
$sql = "SELECT * FROM users ORDER BY name";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get receptionists Error: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Error loading receptionists");
} else {
	while($row = mysql_fetch_assoc($result)) {
		if(($row['role'] == 'RECEPTION') or ($row['role'] == 'MANAGER') or ($row['role'] == 'ADMIN')) {
			$receptionistHtmlOptions .= "\t\t\t<option value=\"" . $row['username'] . "\">" . $row['name'] . "</option>\n";
		}
		if($row['role'] == 'CLEANER') {
			$cleanerHtmlOptions .= "\t\t\t<option value=\"" . $row['username'] . "\">" . $row['name'] . "</option>\n";
		}
	}
}


$receptionShiftHtmlOptions = '';
$cleanerShiftHtmlOptions = '';
foreach($shifts as $row) {
	if($row['shift_type'] == 'reception') {
		$receptionShiftHtmlOptions .= "\t\t\t<option value=\"" . $row['id'] . "\">" . $row['name'] . ' (' . $row['start_time'] . ' - ' . $row['end_time'] . ")</option>\n";
	} elseif($row['shift_type'] == 'cleaner') {
		$cleanerShiftHtmlOptions .= "\t\t\t<option value=\"" . $row['id'] . "\">" . $row['name'] . ' (' . $row['start_time'] . ' - ' . $row['end_time'] . ")</option>\n";
	}
}


$extraHeader =<<<EOT

<script type="text/javascript">

	function addReceptionist(day) {
		document.getElementById('add_receptionist_schedule_div').style.display = 'block';
		$('day').setValue(day);
		$('day_td').update(day);
	}

	function addCleaner(day) {
		document.getElementById('add_cleaner_schedule_div').style.display = 'block';
		$('day_cleaner').setValue(day);
		$('day_td_cleaner').update(day);
	}

	function addSimpleReceptionist(day) {
		document.getElementById('add_simple_receptionist_schedule_div').style.display = 'block';
		$('simple_day').setValue(day);
		$('simple_day_td').update(day);
	}

	function addSimpleCleaner(day) {
		document.getElementById('add_simple_cleaner_schedule_div').style.display = 'block';
		$('simple_day_cleaner').setValue(day);
		$('simple_day_td_cleaner').update(day);
	}

</script>

EOT;

html_start("Reception/Cleaning Schedule", $extraHeader);


echo <<<EOT

<div id="add_receptionist_schedule_div" style="display: none; position: absolute; left: 100px; top: 100px; padding: 10px; width: 400px; height: 150px; border: 1px solid black; background: rgb(200, 200, 200);">
	<form action="add_receptionist_schedule.php" method="POST" accept-charset="utf-8">
	<input type="hidden" name="day" id="day">
	<input type="hidden" name="type" value="normal">
	<table>
		<tr><th colspan="2">Add receptionist schedule</th></tr>
		<tr><td>Day: </td><td id="day_td"></td></tr>
		<tr><td>Shift: </td><td><select name="shift">
$receptionShiftHtmlOptions
		</select></td></tr>
		<tr><td>Receptionist: </td><td><select name="login">
$receptionistHtmlOptions
		</select></td></tr>
	</table>
	<input type="submit" value="Add receptionist schedule">&nbsp;
	<input type="button" value="Cancel" onclick="$('add_receptionist_schedule_div').hide();">
	</form>
</div>


<div id="add_cleaner_schedule_div" style="display: none; position: absolute; left: 100px; top: 100px; padding: 10px; width: 400px; height: 150px; border: 1px solid black; background: rgb(200, 200, 200);">
	<form action="add_cleaner_schedule.php" method="POST" accept-charset="utf-8">
	<input type="hidden" name="day" id="day_cleaner">
	<input type="hidden" name="type" value="normal">
	<table>
		<tr><th colspan="2">Add cleaner schedule</th></tr>
		<tr><td>Day: </td><td id="day_td_cleaner"></td></tr>
		<tr><td>Shift: </td><td><select name="shift">
$cleanerShiftHtmlOptions
		</select></td></tr>
		<tr><td>Cleaner: </td><td><select name="login">
$cleanerHtmlOptions
		</select></td></tr>
	</table>
	<input type="submit" value="Add cleaner schedule">&nbsp;
	<input type="button" value="Cancel" onclick="$('add_cleaner_schedule_div').hide();">
	</form>
</div>


<div id="add_simple_receptionist_schedule_div" style="display: none; position: absolute; left: 100px; top: 100px; padding: 10px; width: 300px; height: 150px; border: 1px solid black; background: rgb(200, 200, 200);">
	<form action="add_receptionist_schedule.php" method="POST" accept-charset="utf-8">
	<input type="hidden" name="day" id="simple_day">
	<input type="hidden" name="type" value="simple">
	<table>
		<tr><th colspan="2">Add receptionist schedule</th></tr>
		<tr><td>Day: </td><td id="simple_day_td"></td></tr>

EOT;
foreach($shifts as $oneShift) {
	if($oneShift['shift_type'] == 'reception' and $oneShift['highlighted'] == 1) {
		echo "		<tr><td>" . $oneShift['name'] . "</td><td><select name=\"" . $oneShift['id'] . "\">$receptionistHtmlOptions</select></td></tr>\n";
	}
}
echo <<<EOT
	</table>
	<input type="submit" value="Add receptionist schedule">&nbsp;
	<input type="button" value="Cancel" onclick="$('add_simple_receptionist_schedule_div').hide();">
	</form>
</div>


<div id="add_simple_cleaner_schedule_div" style="display: none; position: absolute; left: 100px; top: 100px; padding: 10px; width: 300px; height: 150px; border: 1px solid black; background: rgb(200, 200, 200);">
	<form action="add_cleaner_schedule.php" method="POST" accept-charset="utf-8">
	<input type="hidden" name="type" value="simple">
	<input type="hidden" name="day" id="simple_day_cleaner">
	<table>
		<tr><th colspan="2">Add cleaner schedule</th></tr>
		<tr><td>Day: </td><td id="day_td_cleaner"></td></tr>

EOT;
foreach($shifts as $oneShift) {
	if($oneShift['shift_type'] == 'cleaner' and $oneShift['highlighted'] == 1) {
		echo "		<tr><td>" . $oneShift['name'] . "</td><td><select name=\"" . $oneShift['id'] . "\">$receptionistHtmlOptions</select></td></tr>\n";
	}
}
echo <<<EOT
	</table>
	<input type="submit" value="Add cleaner schedule">&nbsp;
	<input type="button" value="Cancel" onclick="$('add_simple_cleaner_schedule_div').hide();">
	</form>
</div>



Current month: $currYear-$currMonth<br>
<table><tr>
<td><form>
	<input type="hidden" name="year" value="$prevYear">
	<input type="hidden" name="month" value="$prevMonth">
	<input type="submit" value="&lt;&lt; Previous month">
</form></td>
<td><form>
	<input type="hidden" name="year" value="$nextYear">
	<input type="hidden" name="month" value="$nextMonth">
	<input type="submit" value="Next month &gt;&gt;">
</form></td>
</tr></table>
<br>

<h2>Reception Schedule</h2>
<table class="reception_schedule">
	<tr><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th></tr>
	<tr>

EOT;

$dayOfWeek = date('w', $currDate);
if($dayOfWeek < 1) {
	$dayOfWeek = 7;
}

if($dayOfWeek > 1) {
	echo "		<td colspan=\"" . ($dayOfWeek - 1) . "\">&nbsp;</td>\n";
}

$numOfDaysInMonth = date('t', $currDate);
for($i = 0; $i < $numOfDaysInMonth; $i++) {
	$currDateStr = date('Y-m-d', $currDate);
	echo "		<td style=\"padding: 4px;\">\n";
	echo "			<div style=\"text-align: left; float: left; margin: 5px;\"><a href=\"#\" onclick=\"addSimpleReceptionist('" . date('Y-m-d', $currDate) . "');\">Simple Add</a></div>\n";
	echo "			<div style=\"text-align: left; float: left; margin: 5px;\"><a href=\"#\" onclick=\"addReceptionist('" . date('Y-m-d', $currDate) . "');\">Add</a></div>\n";
	echo "			<div style=\"text-align: right; float: right; font-weight: bold;\">" . date('j', $currDate) . "</div><div style=\"clear: both;\"></div>\n";
	echo "			<table class=\"schedule_inner_table\">\n";
	if(isset($r_entries[$currDateStr])) {
		$arr = $r_entries[$currDateStr];
		ksort($arr);
		foreach($arr as $startTime => $entry) {
			echo "\t\t\t\t<tr><td>" . $entry['login'] . '</td><td>' . $entry['start_time'] . '-' . $entry['end_time'] . "</td><td><a href=\"delete_receptionist_schedule.php?id=" . $entry['id'] . "\">Delete</a></td></tr>\n";
		}
	}
	echo "			</table>\n";
	echo "		</td>\n";
	$dayOfWeek = date('w', $currDate);
	if($dayOfWeek < 1) {
		$dayOfWeek = 7;
	}
	if($dayOfWeek == 7) {
		echo "	</tr>\n\t<tr>\n";
	}
	$currDate = strtotime($currDateStr . " +1 day");
}
$dayOfWeek = date('w', $currDate);
if($dayOfWeek < 1) {
	$dayOfWeek = 7;
}

if($dayOfWeek > 1) {
	echo "		<td colspan=\"" . (7 - $dayOfWeek + 1) . "\">&nbsp;</td>\n";
}
echo <<<EOT
	</tr>
</table>


<br>
<h2>Cleaning Schedule</h2>
<table class="reception_schedule">
	<tr><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th></tr>
	<tr>

EOT;

$currYear = $_SESSION['schedule_year'];
$currMonth = $_SESSION['schedule_month'];

$currDate = strtotime($currYear . "-" . $currMonth . "-01");


$dayOfWeek = date('w', $currDate);
if($dayOfWeek < 1) {
	$dayOfWeek = 7;
}

if($dayOfWeek > 1) {
	echo "		<td colspan=\"" . ($dayOfWeek - 1) . "\">&nbsp;</td>\n";
}

$numOfDaysInMonth = date('t', $currDate);
for($i = 0; $i < $numOfDaysInMonth; $i++) {
	$currDateStr = date('Y-m-d', $currDate);
	echo "		<td>\n";
	echo "			<div style=\"text-align: left; float: left; margin: 5px;\"><a href=\"#\" onclick=\"addSimpleCleaner('" . date('Y-m-d', $currDate) . "');\">Simple Add</a></div>\n";
	echo "			<div style=\"text-align: left; float: left; margin: 5px;\"><a href=\"#\" onclick=\"addCleaner('" . date('Y-m-d', $currDate) . "');\">Add</a> </div>\n";
	echo "			<div style=\"text-align: right; float: right; font-weight: bold;\">" . date('j', $currDate) . "</div><div style=\"clear: both;\"></div>\n";
	echo "			<table class=\"schedule_inner_table\">\n";
	if(isset($c_entries[$currDateStr])) {
		ksort($c_entries[$currDateStr]);
		foreach($c_entries[$currDateStr] as $startTime => $entry) {
			echo "\t\t\t\t<tr><td>" . $entry['cleaner'] . '</td><td>' . $entry['start_time'] . '-' . $entry['end_time'] . "</td><td><a href=\"delete_cleaner_schedule.php?id=" . $entry['id'] . "\">Delete</a></td></tr>\n";
		}
	}
	echo "			</table>\n";
	echo "		</td>\n";
	$dayOfWeek = date('w', $currDate);
	if($dayOfWeek < 1) {
		$dayOfWeek = 7;
	}
	if($dayOfWeek == 7) {
		echo "	</tr>\n\t<tr>\n";
	}
	$currDate = strtotime($currDateStr . " +1 day");
}
$dayOfWeek = date('w', $currDate);
if($dayOfWeek < 1) {
	$dayOfWeek = 7;
}

if($dayOfWeek > 1) {
	echo "		<td colspan=\"" . (7 - $dayOfWeek + 1) . "\">&nbsp;</td>\n";
}
echo <<<EOT
	</tr>
</table>
<br><br>

EOT;


mysql_close($link);

html_end();

?>