<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

$sql = "SELECT * FROM links ORDER BY _order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get links in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

html_start("Links");


echo <<<EOT

<form id="create_btn">
<input type="button" onclick="document.getElementById('link_form').reset();document.getElementById('link_form').style.display='block'; document.getElementById('create_btn').style.display='none'; return false;" value="Create new link">
</form>
<br>

<form action="save_link.php" id="link_form" accept-charset="utf-8" method="POST" style="display: none;">
<fieldset>
<input type="hidden" name="id" id="id" value="">

EOT;
foreach(getLanguages() as $langCode => $langName) {
	echo <<<EOT
<label>Name ($langName)</label><input name="name_$langCode"  id="name_$langCode"  style="width: 100px;"><br>
<label>Description ($langName)</label><input  style="width: 240px;" name="description_$langCode" id="description_$langCode"><br>

EOT;
}
echo <<<EOT
<label>URL</label><input name="url" id="url" style="width: 240px;"><br>
<label>Order</label><input name="order" id="order" style="width: 40px;"><br>
</fieldset>
<fieldset>
<input type="submit" value="Save link">
<input type="button" value="Cancel" onclick="document.getElementById('link_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;">
</fieldset>
</form>


<h2>Existing URLs</h2>
<table border="1">

EOT;
if($result) {
	if(mysql_num_rows($result) > 0)
		echo "	<tr><th>Order</th><th>Lang</th><th>Name</th><th>Description</th><th>URL</th><th></th></tr>\n";
	else
		echo "	<tr><td><i>No record found.</i></td></tr>\n";

	while($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT * FROM lang_text WHERE table_name='links' and row_id=" . $row['id'];
		$result2 = mysql_query($sql, $link);
		if(!$result2) {
			trigger_error("Cannot get url texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
		$record = array();
		while($row2 = mysql_fetch_assoc($result2)) {
			$record[$row2['lang']][$row2['column_name']] = $row2['value'];
		}
		$first = true;
		echo "<script language=\"JavaScript\">\n";
		echo "	function edit" . $row['id'] . "() {\n";
		echo "		document.getElementById('link_form').reset();\n";
		echo "		document.getElementById('link_form').style.display='block';\n";
		echo "		document.getElementById('create_btn').style.display='none';\n";
		echo "		document.getElementById('id').value='" . $row['id'] . "';\n";
		echo "		document.getElementById('url').value='" . $row['url'] . "';\n";
		echo "		document.getElementById('order').value='" . $row['_order'] . "';\n";
		foreach($record as $lang => $cols) {
			echo "		document.getElementById('name_$lang').value='" . $cols['name'] . "';\n";
			echo "		document.getElementById('description_$lang').value='" . $cols['description'] . "';\n";
		}
		echo "	}\n";
		echo "</script>\n";
		foreach($record as $lang => $cols) {
			echo "	<tr>";
			if($first) {
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $row['_order'] . ".</td>";
			}
			echo "<td>$lang</td><td><strong>" . $cols['name'] . "</strong></td><td>" . $cols['description'] . "</td>";
			if($first) {
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $row['url'] . "</td>";
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">\n";
				echo "	<a href=\"#\" onclick=\"edit" . $row['id'] . "(); return false;\">Edit</a>\n";
				echo "	<a href=\"delete_link.php?id=" . $row['id'] . "\">Delete</a>\n";
				echo "</td>";
				$first = false;
			}
			echo "</tr>\n";
		}
	}
}

echo <<<EOT
</table>

EOT;


mysql_close($link);

html_end();



?>
