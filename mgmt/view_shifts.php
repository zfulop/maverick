<?php

require("includes.php");

$link = db_connect();

$searchOrder = "start_time";
if(isset($_REQUEST['order'])) {
	$searchOrder = $_REQUEST['order'];
}

$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

EOT;

html_start("Maverick Mgmt - Work Shifts", $extraHeader);



echo <<<EOT

<form action="save_shift.php" accept-charset="utf-8" method="GET">
<input type="hidden" name="order" value="$searchOrder">
<input type="button" id="showForm" value="New Shift..." onclick="document.getElementById('formTable').style.display='block';this.style.display='none';">
<table id="formTable" style="display:none;">
	<tr><td>Name:</td><td><input name="name" value=""></td></tr>
	<tr><td>Valid From:</td><td>
		<input id="valid_from" name="valid_from" size="10" maxlength="10" type="text" value=""><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'valid_from', 'chooserSpanVF', 2008, 2025, 'Y/m/d', false);">
		<div id="chooserSpanVF" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>Valid To:</td><td>
		<input id="valid_to" name="valid_to" size="10" maxlength="10" type="text" value=""><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'valid_to', 'chooserSpanVT', 2008, 2025, 'Y/m/d', false);">
		<div id="chooserSpanVT" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>Start time:</td><td><input name="start_time" value=""></td></tr>
	<tr><td>End time:</td><td><input name="end_time" value=""></td></tr>
	<tr><td>Duration:</td><td><input name="duration_hour"> hours</td></tr>
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

if(!$result) {
	trigger_error("Cannot get audit trail: " . mysql_error($link) . " (SQL: $sql)");
} else {
	if(mysql_num_rows($result) > 0) {
		echo "<table>\n";
		echo "	<tr><th>Name</th><th>Valid From</th><th>Valid To</th><th>Start Time</th><th>End Time</th><th>Duration (hrs)</th></tr>\n";
	} else {
		echo "<i>No record found.</i><br>\n";
	}

	while($row = mysql_fetch_assoc($result)) {
		echo "	<tr>";
		echo "		<td>" . $row['name'] . "</td>\n";
		echo "		<td>" . $row['valid_from'] . "</td>\n";
		echo "		<td>" . (is_null($row['valid_to']) ? 'OPEN' : $row['valid_to']) . "</td>\n";
		echo "		<td>" . $row['start_time'] . "</td>\n";
		echo "		<td>" . $row['end_time'] . "</td>\n";
		echo "		<td>" . $row['duration_hour'] . "</td>\n";
		echo "		<td><a href=\"invalidate_shift.php?id=" . $row['id'] . "\">Invalidate</a></td>\n";
		echo "</tr>\n";
	}
}

echo <<<EOT
</table>

EOT;


mysql_close($link);

html_end();



?>
