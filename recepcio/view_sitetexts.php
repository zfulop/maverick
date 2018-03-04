<?php


require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();



$extraHeader = <<<EOT

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

if(isset($_SESSION['notify_sitetext_update'])) {
	unset($_SESSION['notify_sitetext_update']);
	echo <<<EOT

<script type="text/javascript">
	if(confirm("Refresh site texts on public website?")) {
		new Ajax.Request("https://www.mavericklodges.com/?refreshTrans", {
			onSuccess: function(response) {
				alert("Translations refreshed on the website");
			}
		});
	}
</script>

EOT;

}

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

EOT;

$cntr = 0;
foreach($texts as $key => $values) {
	if($cntr % 30 < 1) {
		if($cntr > 0) {
			echo <<<EOT
</table>
<input type="submit" value="Save website texts">
</form>
<br><br>

EOT;
		}
		echo <<<EOT
<form action="save_sitetexts.php" method="post" accept-charset="utf-8">
<table style="border: 1px solid black;">
	<tr><th>Key</th>
EOT;
		foreach(getLanguages() as $code => $langName) {
			echo "<td>$langName</td>";
		}
		echo "</tr>\n";
	}
	echo "	<tr><td>$key</td>";
	foreach(getLanguages() as $code => $langName) {
		$name = 'WEBSITETEXT_' . $key . '_' . $code;
		echo "<td><textarea name=\"$name\">" . (isset($values[$code]) ? $values[$code] : '') . "</textarea></td>";
	}
	echo "</tr>\n";
	$cntr += 1;
}

echo <<<EOT
</table>
<input type="submit" value="Save website texts">
</form>

EOT;


mysql_close($link);


html_end();


?>
