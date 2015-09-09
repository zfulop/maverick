<?php

require("includes.php");

$link = db_connect();

$minMaxStay = array();
$sql = "SELECT * FROM min_max_stay ORDER BY from_date";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get min max stay data in mgmt interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$minMaxStay[] = $row;
	}
}


html_start("Maverick Mgmt - Min/Max Stay");

$fromDate = date('Y-m-d');

echo <<<EOT

<h2>Min/Max Stays</h2>
<table>
	<tr><th>From date</th><th>To date</th><th>Min stay</th><th>Max stay</th><th>&nbsp;</th></tr>
	<form action="save_min_max_stay.php" method="post" accept-charset="utf-8">
	<tr>
		<td><input id="from_date" name="from_date" size="10" maxlength="10" type="text" value="$fromDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'from_date', 'chooserSpanFD', 2008, 2025, 'Y-m-d', false);"><div id="chooserSpanFD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div></td>
		<td><input id="to_date" name="to_date" size="10" maxlength="10" type="text" value=""><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'to_date', 'chooserSpanTD', 2008, 2025, 'Y-m-d', false);"><div id="chooserSpanTD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div></td>
		<td><input name="min_stay" size="3" maxlength="3" type="text"></td>
		<td><input name="max_stay" size="3" maxlength="3" type="text"></td>
		<td><input type="submit" value="Save"></td>
	</tr>
	</form>

EOT;
foreach($minMaxStay as $row) {
	$id = $row['id'];
	echo "	<tr><td>" . $row['from_date'] . "</td><td>" . $row['to_date'] . "</td><td>" . $row['min_stay'] . "</td><td>" . $row['max_stay'] . "</td><td><a href=\"delete_min_max_stay.php?id=$id\">Delete</a></td></tr>\n";
}

echo <<<EOT
<table>

EOT;

mysql_close($link);

html_end();



?>