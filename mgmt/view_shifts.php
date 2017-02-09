<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

$searchOrder = "start_time";
if(isset($_REQUEST['order'])) {
	$searchOrder = $_REQUEST['order'];
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

$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

<script type="text/javascript">
	function newShift() {
		document.getElementById("shift_id").value = "";
		document.getElementById("name").value = "";
		document.getElementById("valid_from").value = "";
		document.getElementById("valid_to").value = "";
		document.getElementById("start_time").value = "";
		document.getElementById("end_time").value = "";
		document.getElementById("duration_hour").value = "";
		document.getElementById('formTable').style.display='block';
		document.getElementById('showForm').style.display='none';
		}
	
EOT;

foreach($shifts as $row) {
	$id = $row['id'];
	$name = $row['name'];
	$validFrom = $row['valid_from'];
	$validTo = $row['valid_to'];
	$durationHour = $row['duration_hour'];
	$startTime = $row['start_time'];
	$endTime = $row['end_time'];
	$extraHeader .= <<<EOT
	
	function editShift$id() {
		document.getElementById("shift_id").value = "$id";
		document.getElementById("name").value = "$name";
		document.getElementById("valid_from").value = "$validFrom";
		document.getElementById("valid_to").value = "$validTo";
		document.getElementById("start_time").value = "$startTime";
		document.getElementById("end_time").value = "$endTime";
		document.getElementById("duration_hour").value = "$durationHour";			

EOT;
	if($row['shift_type'] == 'reception') {
		$extraHeader .=  "		document.getElementById(\"shift_type\").selectedIndex = 0;\n";
	} elseif($row['shift_type'] == 'cleaner') {
		$extraHeader .=  "		document.getElementById(\"shift_type\").selectedIndex = 1;\n";
	}
	if($row['highlighted'] == 1) {
		$extraHeader .=  "		document.getElementById(\"highlighted\").checked = true;\n";
	} else {
		$extraHeader .=  "		document.getElementById(\"highlighted\").checked = false;\n";
	}
	$extraHeader .= "		document.getElementById('formTable').style.display='block';\n";
	$extraHeader .= "		document.getElementById('showForm').style.display='none';\n";
	$extraHeader .= "	}\n";
}

$extraHeader .= "\n</script>\n";


html_start("Work Shifts", $extraHeader);



echo <<<EOT

<form action="save_shift.php" accept-charset="utf-8" method="GET">
<input type="hidden" name="order" value="$searchOrder">
<input type="hidden" name="shift_id" id="shift_id" value="$searchOrder">
<input type="button" id="showForm" value="New Shift..." onclick="newShift();return false;">
<table id="formTable" style="display:none;">
	<tr><td>Name:</td><td><input name="name" id="name" value=""></td></tr>
	<tr><td>Valid From:</td><td>
		<input id="valid_from" name="valid_from" id="valid_from" size="10" maxlength="10" type="text" value=""><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'valid_from', 'chooserSpanVF', 2008, 2025, 'Y/m/d', false);">
		<div id="chooserSpanVF" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>Valid To:</td><td>
		<input id="valid_to" name="valid_to" id="valid_to" size="10" maxlength="10" type="text" value=""><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'valid_to', 'chooserSpanVT', 2008, 2025, 'Y/m/d', false);">
		<div id="chooserSpanVT" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>Start time:</td><td><input name="start_time" id="start_time" value=""></td></tr>
	<tr><td>End time:</td><td><input name="end_time" id="end_time" value=""></td></tr>
	<tr><td>Duration:</td><td><input name="duration_hour" id="duration_hour"> hours</td></tr>
	<tr><td>Shift type:</td><td><select name="shift_type" id="shift_type"><option value="reception">reception</option><option value="cleaner">cleaner</option></select></td></tr>
	<tr><td>Highlighted:</td><td><input type="checkbox" name="highlighted" id="highlighted" value="yes"></td></tr>
	<tr><td colspan="2">
		<input type="submit" value="Save">
		<input type="button" id="showForm" value="Cancel" onclick="document.getElementById('formTable').style.display='none';document.getElementById('showForm').style.display='block';">
	</td></tr>
</table>
</form>
<br>

EOT;
$today = date('Y-m-d');
$sql = "SELECT * FROM working_shift WHERE valid_to IS NULL or valid_to>='$today' ORDER BY start_time ";
$result = mysql_query($sql, $link);

echo "<table>\n";

if(count($shifts) > 0) {
	echo "	<tr><th>Name</th><th>Valid From</th><th>Valid To</th><th>Start Time</th><th>End Time</th><th>Duration (hrs)</th><th>Shift type</th><th>Highlighted</th></tr>\n";
} else {
	echo "<i>No record found.</i><br>\n";
}

foreach($shifts as $row) {
	$id = $row['id'];
	echo "	<tr>";
	echo "		<td>" . $row['name'] . "</td>\n";
	echo "		<td>" . $row['valid_from'] . "</td>\n";
	echo "		<td>" . (is_null($row['valid_to']) ? 'OPEN' : $row['valid_to']) . "</td>\n";
	echo "		<td>" . $row['start_time'] . "</td>\n";
	echo "		<td>" . $row['end_time'] . "</td>\n";
	echo "		<td>" . $row['duration_hour'] . "</td>\n";
	echo "		<td>" . $row['shift_type'] . "</td>\n";
	echo "		<td>" . ($row['highlighted'] ? 'yes' : '') . "</td>\n";
	echo "		<td><a href=\"#\" onclick=\"editShift$id();return false;\">Edit</a> <a href=\"invalidate_shift.php?id=$id\">Invalidate</a></td>\n";
	echo "</tr>\n";
}

echo <<<EOT
</table>

EOT;


mysql_close($link);

html_end();



?>
