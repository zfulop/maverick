<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

$searchOrder = "audit.time_of_event";
if(isset($_REQUEST['order'])) {
	$searchOrder = $_REQUEST['order'];
}

$bookerName = '';
if(isset($_REQUEST['booker_name'])) {
	$bookerName = $_REQUEST['booker_name'];
}
$dateOfStay = '';
if(isset($_REQUEST['date_of_stay'])) {
	$dateOfStay = str_replace('-', '/', $_REQUEST['date_of_stay']);
}
$dateOfAudit = '';
if(isset($_REQUEST['date_of_audit'])) {
	$dateOfAudit = str_replace('-', '/', $_REQUEST['date_of_audit']);
}
$recLogin = array();
if(isset($_REQUEST['rec_login'])) {
	$recLogin = $_REQUEST['rec_login'];
}

$recOptions = '<option value="">All</option>';
$sql = "SELECT name, username as login FROM users WHERE role='RECEPTION' ORDER BY name";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	$login = $row['login'];
	$name = $row['name'];
	$selected = (in_array($login, $recLogin) ? " selected" : "");
	$recOptions .= "<option value=\"$login\"$selected>$name</option>";
}



$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

EOT;

html_start("Audit", $extraHeader);



echo <<<EOT

<form action="view_audit.php" accept-charset="utf-8" method="GET">
<input type="hidden" name="order" value="$searchOrder">
<table>
	<tr><td>Booker's name:</td><td><input name="booker_name" value="$bookerName"></td></tr>
	<tr><td>Guest was here on this date:</td><td>
		<input id="date_of_stay" name="date_of_stay" size="10" maxlength="10" type="text" value="$dateOfStay"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'date_of_stay', 'chooserSpanDOS', 2008, 2025, 'Y/m/d', false);">
		<div id="chooserSpanDOS" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>Audit happened on this date:</td><td>
		<input id="date_of_audit" name="date_of_audit" size="10" maxlength="10" type="text" value="$dateOfAudit"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'date_of_audit', 'chooserSpanDOA', 2008, 2025, 'Y/m/d', false);">
		<div id="chooserSpanDOA" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
	</td></tr>
	<tr><td>By receptionist(s):</td><td><select size="4" multiple="true" style="height: auto;" name="rec_login[]">$recOptions</select></td></tr>
</table>
<input type="submit" value="Search Audit">
</form>
<br>

EOT;

if(strlen($bookerName) < 1 and strlen($dateOfStay) != 10 and strlen($dateOfAudit) != 10 and count($recLogin) < 1) {
	mysql_close($link);
	return;
}

$sql = "SELECT audit.*, booking_descriptions.name, booking_descriptions.first_night, booking_descriptions.num_of_nights FROM audit ";
if(strlen($bookerName) > 0 or strlen($dateOfStay) > 0) {
	$sql .= " INNER JOIN booking_descriptions ON audit.booking_description_id=booking_descriptions.id WHERE ";
} else {
	$sql .= " LEFT OUTER JOIN booking_descriptions ON audit.booking_description_id=booking_descriptions.id WHERE ";
}
$first = true;
if(strlen($bookerName) > 0) {
	if($first) {
		$first = false;
	} else {
		$sql .= " AND ";
	}
	$sql .= "booking_descriptions.name LIKE '%$bookerName%'";
}
if(strlen($dateOfStay) == 10) {
	if($first) {
		$first = false;
	} else {
		$sql .= " AND ";
	}
	$sql .= "booking_descriptions.first_night<='$dateOfStay' AND booking_descriptions.last_night>='$dateOfStay'";
}
if(strlen($dateOfAudit) == 10) {
	if($first) {
		$first = false;
	} else {
		$sql .= " AND ";
	}
	$dateOfAudit = str_replace('/', '-', $_REQUEST['date_of_audit']);
	$sql .= "audit.time_of_event LIKE '$dateOfAudit%'";
}
if(count($recLogin) > 0) {
	if($first) {
		$first = false;
	} else {
		$sql .= " AND ";
	}
	$sql .= "audit.login IN ('" . implode("','", $recLogin) . "')";
}

$sql .= " ORDER BY $searchOrder";

$result = mysql_query($sql, $link);

if(!$result) {
	trigger_error("Cannot get audit trail: " . mysql_error($link) . " (SQL: $sql)");
} else {
	if(mysql_num_rows($result) > 0) {
		echo "<table>\n";
		echo "	<tr><th>Time</th><th>Login</th><th>Action</th><th>Booking</th><th>Data</th></tr>\n";
	} else {
		echo "<i>No record found.</i><br>\n";
	}

	while($row = mysql_fetch_assoc($result)) {
		echo "	<tr style=\"margin-bottom: 10px;\">";
		echo "		<td style=\"border-bottom: 1px solid black;\">" . $row['time_of_event'] . "</td>\n";
		echo "		<td style=\"border-bottom: 1px solid black;\">" . $row['login'] . "</td>\n";
		echo "		<td style=\"border-bottom: 1px solid black;\">" . $row['type'] . "</td>\n";
		echo "		<td style=\"border-bottom: 1px solid black;\">" . $row['name'] . ' ' . $row['first_night'] . ' (' . $row['num_of_nights'] . ")</td>\n";
		echo "		<td style=\"border-bottom: 1px solid black;\">&nbsp;" . $row['data'] . "</td>\n";
		echo "	</tr>\n";
	}
}

echo <<<EOT
</table>

EOT;


mysql_close($link);

html_end();



?>
