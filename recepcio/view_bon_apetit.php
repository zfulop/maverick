<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

$sql = "SELECT * FROM bon_apetit ORDER BY _order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get restaurants in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

html_start("Bon Apetit");


echo <<<EOT

<form id="create_btn">
<input type="button" onclick="document.getElementById('rest_form').reset();document.getElementById('rest_form').style.display='block'; document.getElementById('create_btn').style.display='none'; return false;" value="Register new restaurant">
</form>
<br>


<form id="rest_form" style="display: none;" action="save_bon_apetit.php" accept-charset="utf-8" enctype="multipart/form-data" method="POST">
<fieldset>
<input type="hidden" id="id" name="id" value="">
<label>Name</label><input name="name" id="name" style="width: 120px;"><br>
<label>Location</label><input name="location" id="location" style="width: 120px;"><br>
<label>Distance from hostel</label><input name="distance" id="distance" style="width: 50px;"><br>
<label>Hours of Operation</label><input name="hours" id="hours" style="width: 120px;"><br>
<label>Image</label><input name="img" type="file" style="width: 120px;"><br>
<label>URL</label><input name="url" id="url" style="width: 120px;"><br>
<label>Telephone</label><input name="telephone" id="telephone" style="width: 120px;"><br>
<label>Order</label><input name="order" id="order" style="width: 40px;"><br>


EOT;
foreach(getLanguages() as $langCode => $langName) {
	echo <<<EOT
<label>Description ($langName)</label><input id="description_$langCode" style="width: 240px;" name="description_$langCode"><br>

EOT;
}
echo <<<EOT
</fieldset>
<fieldset>
<input type="submit" value="Save restaurant">
<input type="button" onclick="document.getElementById('rest_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;" value="Cancel">
</fieldset>
</form>


<h2>Existing Restaurants</h2>
<table border="1">

EOT;
if($result) {
	if(mysql_num_rows($result) > 0)
		echo "	<tr><th>Order</th><th>Image</th><th>Name</th><th>Location</th><th>Distance</th><th>Hours</th><th>URL</th><th>Telephone</th><th>Description</th><th></th></tr>\n";
	else
		echo "	<tr><td><i>No record found.</i></td></tr>\n";

	while($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT * FROM lang_text WHERE table_name='bon_apetit' and row_id=" . $row['id'];
		$result2 = mysql_query($sql, $link);
		if(!$result2) {
			trigger_error("Cannot get restaurant description texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
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
		echo "		document.getElementById('location').value='" . $row['location'] . "';\n";
		echo "		document.getElementById('distance').value='" . $row['distance_from_hostel'] . "';\n";
		echo "		document.getElementById('hours').value='" . $row['hours'] . "';\n";
		echo "		document.getElementById('telephone').value='" . $row['telephone'] . "';\n";
		echo "		document.getElementById('url').value='" . $row['url'] . "';\n";
		echo "		document.getElementById('order').value='" . $row['_order'] . "';\n";
		foreach($record as $lang => $cols) {
			echo "		document.getElementById('description_$lang').value='" . str_replace("'", "\\'", $cols['description']) . "';\n";
		}
		echo "	}\n";
		echo "</script>\n";


		echo "	<tr>\n";
		echo "		<td><table><tr><td rowspan=\"2\">" . $row['_order'] . ".</td><td><input type=\"button\" value=\"Move up\" onclick=\"window.location='change_order.php?direction=up&table=bon_apetit&id=" . $row['id'] . "&order=" . $row['_order'] . "';\"></td></tr><tr><td><input type=\"button\" value=\"Move down\" onclick=\"window.location='change_order.php?direction=down&table=bon_apetit&id=" . $row['id'] . "&order=" . $row['_order'] . "';\"></td></tr></table></td>\n";
		echo "		<td>";
		$fileParam = "";
		if(!is_null($row['img']) and file_exists(BON_APETIT_IMG_DIR . '/' . $row['img'])) {
			echo "<img src=\"" . BON_APETIT_IMG_URL . '/' . $row['img'] . "\">";
			$fileParam = "&file=" . urlencode($row['img']);
		}
		echo "</td><td>" . $row['name'] . "</td><td>" . $row['location'] . "</td><td>" . $row['distance_from_hostel'] . "</td><td>" . $row['hours'] . "</td><td>" . $row['url'] . "</td><td>" . $row['telephone'] . "</td>\n";
		echo "		<td>\n";
		foreach($record as $lang => $cols) {
			echo "			<strong>$lang</strong> " . $cols['description'] . "<br>\n";
		}
		echo "		</td>\n" ;
		echo "		<td><a href=\"#\" onclick=\"edit" . $row['id'] . "();\">Edit</a> <a href=\"delete_bon_apetit.php?id=" . $row['id'] . "$fileParam\">Delete</a></td>\n";
		echo "	</tr>\n";
	}
}

echo <<<EOT
</table>

EOT;


mysql_close($link);

html_end();



?>
