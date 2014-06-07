<?php

require("includes.php");

$link = db_connect();

$sql = "SELECT * FROM have_fun ORDER BY _order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get events in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

html_start("Maverick Reception - Have Fun");


echo <<<EOT

<form id="create_btn">
<input type="button" onclick="document.getElementById('have_fun_form').reset();document.getElementById('have_fun_form').style.display='block'; document.getElementById('create_btn').style.display='none'; return false;" value="Create new event">
</form>
<br>


<form action="save_have_fun.php" style="display: none;" id="have_fun_form" accept-charset="utf-8" enctype="multipart/form-data" method="POST">
<fieldset>
<input type="hidden" name="id" id="id" value="">
<label>Name</label><input name="name" id="name" style="width: 120px;"><br>
<label>Location</label><input name="location" id="location" style="width: 120px;"><br>
<label>Distance from hostel</label><input name="distance" id="distance" style="width: 50px;"><br>
<label>Time of event</label><input name="time" id="time" style="width: 120px;"><br>
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
<input type="submit" value="Save event">
<input type="button" onclick="document.getElementById('have_fun_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;" value="Cancel">
</fieldset>
</form>


<h2>Existing Events</h2>
<table border="1">

EOT;
if($result) {
	if(mysql_num_rows($result) > 0)
		echo "	<tr><th>Order</th><th>Image</th><th>Name</th><th>Location</th><th>Distance</th><th>Time</th><th>URL</th><th>Telephone</th><th>Description</th><th></th></tr>\n";
	else
		echo "	<tr><td><i>No record found.</i></td></tr>\n";

	while($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT * FROM lang_text WHERE table_name='have_fun' and row_id=" . $row['id'];
		$result2 = mysql_query($sql, $link);
		if(!$result2) {
			trigger_error("Cannot get event description texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
		$record = array();
		while($row2 = mysql_fetch_assoc($result2)) {
			$record[$row2['lang']][$row2['column_name']] = $row2['value'];
		}
		echo "<script language=\"JavaScript\">\n";
		echo "	function edit" . $row['id'] . "() {\n";
		echo "		document.getElementById('have_fun_form').reset();\n";
		echo "		document.getElementById('have_fun_form').style.display='block';\n";
		echo "		document.getElementById('create_btn').style.display='none';\n";
		echo "		document.getElementById('id').value='" . $row['id'] . "';\n";
		echo "		document.getElementById('name').value='" . $row['name'] . "';\n";
		echo "		document.getElementById('location').value='" . $row['location'] . "';\n";
		echo "		document.getElementById('distance').value='" . $row['distance_from_hostel'] . "';\n";
		echo "		document.getElementById('time').value='" . $row['time_of_show'] . "';\n";
		echo "		document.getElementById('telephone').value='" . $row['telephone'] . "';\n";
		echo "		document.getElementById('url').value='" . $row['url'] . "';\n";
		echo "		document.getElementById('order').value='" . $row['_order'] . "';\n";
		foreach($record as $lang => $cols) {
			echo "		document.getElementById('description_$lang').value='" . str_replace("'", "\\'", $cols['description']) . "';\n";
		}
		echo "	}\n";
		echo "</script>\n";

		echo "	<tr>\n";
		echo "		<td><table><tr><td rowspan=\"2\">" . $row['_order'] . ".</td><td><input type=\"button\" value=\"Move up\" onclick=\"window.location='change_order.php?direction=up&table=have_fun&id=" . $row['id'] . "&order=" . $row['_order'] . "';\"></td></tr><tr><td><input type=\"button\" value=\"Move down\" onclick=\"window.location='change_order.php?direction=down&table=have_fun&id=" . $row['id'] . "&order=" . $row['_order'] . "';\"></td></tr></table></td>\n";
		echo "		<td>";
		$fileParam = "";
		if(!is_null($row['img']) and file_exists(HAVE_FUN_IMG_DIR . '/' . $row['img'])) {
			echo "<img src=\"" . HAVE_FUN_IMG_URL . '/' . $row['img'] . "\">";
			$fileParam = "&file=" . urlencode($row['img']);
		}
		echo "</td><td>" . $row['name'] . "</td><td>" . $row['location'] . "</td><td>" . $row['distance_from_hostel'] . "</td><td>" . $row['time_of_show'] . "</td><td>" . $row['url'] . "</td><td>" . $row['telephone'] . "</td><td>";
		foreach($record as $lang => $cols) {
			echo "<strong>$lang</strong> " . $cols['description'] . "<br>";
		}
		echo "</td><td><a href=\"#\" onclick=\"edit" . $row['id'] . "();\">Edit</a> <a href=\"delete_have_fun.php?id=" . $row['id'] . "$fileParam\">Delete</a></td></tr>\n";
	}
}

echo <<<EOT
</table>

EOT;


mysql_close($link);

html_end();



?>
