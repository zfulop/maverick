<?php

require("includes.php");

$link = db_connect();

$sources = array();
$sql = "SELECT * FROM sources ORDER BY source";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get sources in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$sources[] = $row['source'];
	}
}


$types = array();
$sql = "SELECT * FROM cashout_type ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get cashout type in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$types[] = $row['type'];
	}
}


html_start("Maverick Mgmt - Lists");


echo <<<EOT

<table>
<tr>

<td valign="top">
<h2>Sources</h2>
<form action="save_list_item.php" method="post" accept-charset="utf-8">
<input type="hidden" name="type" value="sources">
<input type="hidden" name="item_name" value="source">
New source: <input name="item" style="display: inline; float: none;"> <input type="submit" value="Save" style="display: inline; float: none;">
</form>
<table>

EOT;
foreach($sources as $s) {
	echo "	<tr><td>$s</td><td><a href=\"delete_list_item.php?type=sources&item_name=source&item=" . urlencode($s) . "\">Delete</a></td></tr>\n";
}

echo <<<EOT
</table>
</td>

<td valign="top">
<h2>Cash in/out, payment types</h2>
<form action="save_list_item.php" method="post" accept-charset="utf-8">
<input type="hidden" name="type" value="cashout_type">
<input type="hidden" name="item_name" value="type">
New type: <input name="item" style="display: inline; float: none;"> <input type="submit" value="Save" style="display: inline; float: none;">
</form>
<table>
EOT;
foreach($types as $t) {
	echo "	<tr><td>$t</td><td><a href=\"delete_list_item.php?type=cashout_type&item_name=type&item=" . urlencode($t) . "\">Delete</a></td></tr>\n";
}

echo <<<EOT
</table>

</td>

</tr>
</table>

EOT;

mysql_close($link);

html_end();



?>
