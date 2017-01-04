<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


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

$mtypes = array();
$sql = "SELECT * FROM mending_type ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get mending type in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$mtypes[] = $row['type'];
	}
}


$citypes = array();
$sql = "SELECT * FROM cleaner_item_type ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get cleaner item type in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$citypes[] = $row['type'];
	}
}



html_start("Lists");


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

<td valign="top">
<h2>Mending owners</h2>
<form action="save_list_item.php" method="post" accept-charset="utf-8">
<input type="hidden" name="type" value="mending_type">
<input type="hidden" name="item_name" value="type">
New owner: <input name="item" style="display: inline; float: none;"> <input type="submit" value="Save" style="display: inline; float: none;">
</form>
<table>
EOT;
foreach($mtypes as $t) {
	echo "	<tr><td>$t</td><td><a href=\"delete_list_item.php?type=mending_type&item_name=type&item=" . urlencode($t) . "\">Delete</a></td></tr>\n";
}

echo <<<EOT
</table>

</td>


<td valign="top">
<h2>Cleaner Item Types</h2>
<form action="save_list_item.php" method="post" accept-charset="utf-8">
<input type="hidden" name="type" value="cleaner_item_type">
<input type="hidden" name="item_name" value="type">
New cleaner item type: <input name="item" style="display: inline; float: none;"> <input type="submit" value="Save" style="display: inline; float: none;">
</form>
<table>
EOT;
foreach($citypes as $t) {
	echo "	<tr><td>$t</td><td><a href=\"delete_list_item.php?type=cleaner_item_type&item_name=type&item=" . urlencode($t) . "\">Delete</a></td></tr>\n";
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
