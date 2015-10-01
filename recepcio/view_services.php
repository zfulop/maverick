<?php

require("includes.php");

$link = db_connect();

$serviceChargeTypeOptions = "";
$sql = "SELECT * FROM cashout_type ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
	$err = "Cannot get service charges types.";
	set_error($err);
	trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
while($row = mysql_fetch_assoc($result)) {
	$serviceChargeTypeOptions .= "		<option value=\"" . $row['type'] .  "\">" . $row['type'] . "</option>\n";
}

$currencyOptions = "<option value=\"EUR\">EUR</option><option value=\"HUF\">HUF</option>";

html_start("Maverick Reception - Services");


echo <<<EOT

<form id="create_btn">
<input type="button" onclick="document.getElementById('so_form').reset();document.getElementById('so_form').style.display='block'; document.getElementById('create_btn').style.display='none'; return false;" value="Create new service">
</form>
<br>


<form id="so_form" style="display: none;" action="save_service.php" accept-charset="utf-8" enctype="multipart/form-data" method="POST">
<fieldset>
<input type="hidden" id="id" name="id" value="">
<table>

EOT;
$languages = getLanguages();
foreach($languages as $langCode => $langName) {
	echo <<<EOT
	<tr><td><label>Title ($langName)</label></td><td><input name="title_$langCode" id="title_$langCode" style="width: 600px;"></td></tr>
	<tr><td><label>Description ($langName)</label></td><td><textarea name="description_$langCode" id="description_$langCode" style="width: 600px; height:300px;"></textarea></td></tr>
	<tr><td><label>Name of unit ($langName)</label></td><td><input name="unit_name_$langCode" id="unit_name_$langCode" style="width: 600px;"></td></tr>

EOT;
}
echo <<<EOT
	<tr><td><label>Order</label></td><td><input style="width: 40px;" name="order" id="order"></td></tr>
	<tr><td><label>Price</label></td><td><input style="width: 40px;" name="price" id="price"><select name="currency" id="currency">$currencyOptions</select>/person</td></tr>
	<tr><td><label>Image</label></td><td><input name="img" type="file" style="width: 200px;"></td></tr>
	<tr><td><label>Service charge type</label></td><td><select name="service_charge_type" id="service_charge_type" style="width: 200px;">
$serviceChargeTypeOptions
</option></td></tr>
	<tr><td><label>Free</label><input type="checkbox" name="free" id="free" value="free"></td></tr>
</table>
</fieldset>
<fieldset>
<input type="submit" value="Save Service">
<input type="button" onclick="document.getElementById('so_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;" value="Cancel">
</fieldset>
</form>
</div>


<h2>Existing Services</h2>
<table border="1">

EOT;

$sql = "SELECT * FROM services ORDER BY _order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get services: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

if($result) {
	if(mysql_num_rows($result) > 0)
		echo "	<tr><th>Order</th><th>Lang</th><th>Title</th><th>Description</th><th>Unit name</th><th>Image</th><th>Free service</th><th>Price</th><th>Currency</th><th></th></tr>\n";
	else
		echo "	<tr><td><i>No record found.</i></td></tr>\n";

	while($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT * FROM lang_text WHERE table_name='services' and row_id=" . $row['id'];
		$result2 = mysql_query($sql, $link);
		if(!$result2) {
			trigger_error("Cannot get services texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
		$record = array();
		while($row2 = mysql_fetch_assoc($result2)) {
			$record[$row2['lang']][$row2['column_name']] = $row2['value'];
		}

		echo "<script type=\"text/javascript\">\n";
		echo "	function edit" . $row['id'] . "() {\n";
		echo "		document.getElementById('so_form').reset();\n";
		echo "		document.getElementById('so_form').style.display='block';\n";
		echo "		document.getElementById('create_btn').style.display='none';\n";
		echo "		document.getElementById('id').value='" . $row['id'] . "';\n";
		echo "		document.getElementById('order').value='" . $row['_order'] . "';\n";
		echo "		document.getElementById('price').value='" . $row['price'] . "';\n";
		echo "		document.getElementById('currency').value='" . $row['currency'] . "';\n";
		echo "		document.getElementById('service_charge_type').value='" . $row['service_charge_type'] . "';\n";
		foreach($record as $lang => $cols) {
			if(!isset($cols['unit_name'])) {
				$cols['unit_name'] = '';
			}
			$string = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cols['title']);
			echo "		document.getElementById('title_$lang').value='" . str_replace('\'', '\\\'', $string) . "';\n";
			$string = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cols['description']);
			echo "		document.getElementById('description_$lang').value='" . str_replace("\n", '', str_replace('\'', '\\\'', $string)) . "';\n";
			$string = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cols['unit_name']);
			echo "		document.getElementById('unit_name_$lang').value='" . str_replace('\'', '\\\'', $string) . "';\n";
		}
		echo "		document.getElementById('free').checked=" . ($row['free_service'] == 1 ? 'true' : 'false') . ";\n";
		echo "	}\n";
		echo "</script>\n";

		$first = true;
		foreach($languages as $lang => $langName) {
			$cols = array('title' => '','description'=>'','unit_name'=>'');
			if(isset($record[$lang])) {
				$cols = $record[$lang];
			}
			echo "	<tr>";
			if($first) {
				echo "<td valign=\"middle\" rowspan=\"" . count($languages) . "\">" . $row['_order'] . ".</td>";
			}
			if(!isset($cols['unit_name'])) {
				$cols['unit_name'] = '';
			}
			echo "<td>$lang</td><td>" . $cols['title'] . "</td><td>" . $cols['description'] . "</td><td>" . $cols['unit_name'] . "</td>";
			if($first) {
				$imgTag = '&nbsp;';
				$fileParam = '';
				if(!is_null($row['img']) and strlen($row['img']) > 0) {
					$imgTag = "<img src=\"" . SERVICES_IMG_URL . $row['img'] . "\">";
					$fileParam = '&file=' . $row['img'];
				}
				echo "<td valign=\"middle\" rowspan=\"" . count($languages) . "\">$imgTag</td>";
				echo "<td valign=\"middle\" rowspan=\"" . count($languages) . "\">" . ($row['free_service'] == 1 ? 'Free' : 'Paying') . '</td>';
				echo "<td valign=\"middle\" rowspan=\"" . count($languages) . "\">" . $row['price'] . '</td>';
				echo "<td valign=\"middle\" rowspan=\"" . count($languages) . "\">" . $row['currency'] . '</td>';
				echo "<td valign=\"middle\" rowspan=\"" . count($languages) . "\"><a href=\"#\" onclick=\"edit" . $row['id'] . "();\">Edit</a> <a href=\"delete_service.php?id=" . $row['id'] . "\">Delete</a></td>";
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
