<?php

require("includes.php");

$link = db_connect();

$sql = "SELECT * FROM awards ORDER BY _order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get awards in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

html_start("Maverick Reception - Awards");


echo <<<EOT

<form id="create_btn">
<input type="button" onclick="document.getElementById('rest_form').reset();document.getElementById('rest_form').style.display='block'; document.getElementById('create_btn').style.display='none'; return false;" value="Register new award">
</form>
<br>


<form id="rest_form" style="display: none;" action="save_award.php" accept-charset="utf-8" enctype="multipart/form-data" method="POST">
<fieldset>
<input type="hidden" id="id" name="id" value="">
<table>
<tr><td><label>Name</label><input name="name" id="name" style="width: 120px;"></td></tr>
<tr><td><label>Javascript</label><textarea name="javascript" id="javascript" style="width: 320px; height: 120px;"></textarea></td></tr>
<tr><td><label>HTML</label><textarea name="html" id="html" style="width: 320px; height: 120px;"></textarea></td></tr>
<tr><td><label>URL</label><input name="url" id="url" style="width: 120px;"></td></tr>
<tr><td><label>Image</label><input name="img" type="file" style="width: 120px;"></td></tr>
<tr><td><label>Order</label><input name="order" id="order" style="width: 40px;"></td></tr>


EOT;
foreach(getLanguages() as $langCode => $langName) {
	echo <<<EOT
<tr><td><label>Description ($langName)</label><input id="description_$langCode" style="width: 240px;" name="description_$langCode"></td></tr>

EOT;
}
echo <<<EOT
</table>
</fieldset>
<fieldset>
<input type="submit" value="Save award">
<input type="button" onclick="document.getElementById('rest_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;" value="Cancel">
</fieldset>
</form>


<h2>Existing Awards</h2>
<table border="1">

EOT;
if($result) {
	if(mysql_num_rows($result) > 0)
		echo "	<tr><th>Order</th><th>Name</th><th>Javascript</th><th>Html</th><th>Image</th><th>URL</th><th>Description</th><th></th></tr>\n";
	else
		echo "	<tr><td><i>No record found.</i></td></tr>\n";

	while($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT * FROM lang_text WHERE table_name='awards' and row_id=" . $row['id'];
		$result2 = mysql_query($sql, $link);
		if(!$result2) {
			trigger_error("Cannot get award description texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
		$record = array();

		while($row2 = mysql_fetch_assoc($result2)) {
			$record[$row2['lang']][$row2['column_name']] = $row2['value'];
		}
		echo "<script language=\"JavaScript\">\n";
		echo "	function edit" . $row['id'] . "() {\n";
		echo "		document.getElementById('rest_form').reset();\n";
		echo "		document.getElementById('rest_form').style.display='block';\n";
		echo "		document.getElementById('create_btn').style.display='none';\n";
		echo "		document.getElementById('id').value='" . $row['id'] . "';\n";
		echo "		document.getElementById('name').value='" . $row['name'] . "';\n";
		echo "		document.getElementById('url').value='" . $row['url'] . "';\n";
		echo "		document.getElementById('order').value='" . $row['_order'] . "';\n";
		foreach($record as $lang => $cols) {
			echo "		document.getElementById('description_$lang').value='" . str_replace("'", "\\'", $cols['description']) . "';\n";
		}
		echo "	}\n";
		echo "</script>\n";


		echo "	<tr>\n";
		echo "		<td><table><tr><td rowspan=\"2\">" . $row['_order'] . ".</td><td><input type=\"button\" value=\"Move up\" onclick=\"window.location='change_order.php?direction=up&table=awards&id=" . $row['id'] . "&order=" . $row['_order'] . "';\"></td></tr><tr><td><input type=\"button\" value=\"Move down\" onclick=\"window.location='change_order.php?direction=down&table=awards&id=" . $row['id'] . "&order=" . $row['_order'] . "';\"></td></tr></table></td>\n";
		echo "</td><td>" . $row['name'] . "</td>\n";
		$js = strlen($row['javascript']) > 0 ? 'Yes' : '';
		$html = strlen($row['html']) > 0 ? 'Yes' : '';
		echo "<td>$js</td>\n";
		echo "<td>$html</td>\n";
		echo "		<td>";
		$fileParam = "";
		if(!is_null($row['img']) and file_exists(AWARDS_IMG_DIR . $row['img'])) {
			echo "<img src=\"" . AWARDS_IMG_URL . $row['img'] . "\">";
			$fileParam = "&file=" . urlencode($row['img']);
		}
		echo "</td><td>" . $row['url'] . "</td>\n";
		echo "		<td>\n";
		foreach($record as $lang => $cols) {
			echo "			<strong>$lang</strong> " . $cols['description'] . "<br>\n";
		}
		echo "		</td>\n" ;
		echo "		<td><a href=\"#\" onclick=\"edit" . $row['id'] . "();\">Edit</a> <a href=\"delete_award.php?id=" . $row['id'] . "$fileParam\">Delete</a></td>\n";
		echo "	</tr>\n";
	}
}

echo <<<EOT
</table>

EOT;


mysql_close($link);

html_end();



?>
