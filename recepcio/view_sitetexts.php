<?php


require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();


$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

<style>
	table tr td {
		border-bottom: 1px solid black;
	}
	
	a.selected {
		font-size: 130%;
	}
</style>

EOT;


html_start("Website texts", $extraHeader);

$today = date('Y-m-d');

$texts = array();
$sql = "SELECT * FROM lang_text WHERE table_name='website' ORDER BY column_name";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get website texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		if(!isset($texts[$row['column_name']])) {
			$texts[$row['column_name']] = array();
		}
		$texts[$row['column_name']][$row['lang']] = $row['value'];
	}
}


echo <<<EOT

<form action="add_sitetexts.php" method="post" accept-charset="utf-8">
<table style="border: 1px solid black;">
	<tr><th>Add website text items</th></tr>
	<tr>
		<td><b>Text keys</b> space separated items you can list as many as you want to be added. The values for each language can be added (in the below table) after the keys are saved.</b></td>
	</tr>
	<tr>
		<td><input name="keys"></td>
	</tr>
	<tr>
		<td><input type="submit" name="Add site texts"></td>
	</tr>	
</table>
</form>

<br><br>

<form action="save_sitetexts.php" method="post" accept-charset="utf-8">
<table style="border: 1px solid black;">
	<tr><th>Key</th>
EOT;
foreach(getLanguages() as $code => $langName) {
	echo "<td>$langName</td>";
}
echo "</tr>\n";
foreach($texts as $key => $values) {
	echo "	<tr><td>$key</td>";
	foreach(getLanguages() as $code => $langName) {
		$name = 'WEBSITETEXT_' . $key . '_' . $code;
		echo "<td><input name=\"$name\" value=\"" . (isset($values[$code]) ? $values[$code] : '') . "\"></td>";
	}
	echo "</tr>\n";
}

echo <<<EOT
</table>
<input type="submit" value="Save website texts">
</form>

EOT;

mysql_close($link);


html_end();


?>
