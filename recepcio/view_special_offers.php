<?php

require("includes.php");

$link = db_connect();

$roomTypes = array();
$roomTypeOptions = "";
$sql = "SELECT * FROM room_types";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get room types when viewing special offers: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$id = $row['id'];
		$roomTypes[$id] = $row['name'];
		$roomTypeOptions .= "		<option id=\"room_type_id_$id\" value=\"" . $row['id'] . "\">" . $row['name'] . "</option>\n";
	}
}

html_start("Maverick Reception - Special Offers");


echo <<<EOT

<form id="create_btn">
<input type="button" onclick="document.getElementById('so_form').reset();document.getElementById('so_form').style.display='block'; document.getElementById('create_btn').style.display='none'; return false;" value="Create new special offer">
</form>
<br>


<form id="so_form" style="display: none;" action="save_special_offer.php" accept-charset="utf-8" method="POST">
<fieldset>
<input type="hidden" id="id" name="id" value="">
<table>

EOT;
foreach(getLanguages() as $langCode => $langName) {
	echo <<<EOT
	<tr><td><label>Title ($langName)</label></td><td><input name="title_$langCode" id="title_$langCode" style="width: 200px;"></td></tr>
	<tr><td><label>Description ($langName)</label></td><td><input style="width: 200px;" name="text_$langCode" id="text_$langCode"></td></tr>
	<tr><td><label>Room name - optional ($langName)</label></td><td><input style="width: 200px;" name="room_name_$langCode" id="room_name_$langCode"></td></tr>

EOT;
}
echo <<<EOT
	<tr><td><label>Room type</label></td><td><select style="width:200px;height:100px;" name="room_type_ids[]" id="room_type_ids" multiple="true">
$roomTypeOptions
	</select></td></tr>
	<tr><td><label>Name</label></td><td><input style="width: 200px;" name="name" id="name"></td></tr>
	<tr><td><label>Start date</label></td><td><input style="width: 80px;" name="start_date" id="start_date"></td></tr>
	<tr><td><label>End date</label></td><td><input style="width: 80px;" name="end_date" id="end_date"></td></tr>
	<tr><td><label>Number of nights</label></td><td><input style="width: 60px;" name="num_of_nights" id="num_of_nights"></td></tr>
	<tr><td><label>Discount</label></td><td><input style="width: 30px;" name="discount" id="discount">%</td></tr>
	<tr><td><label>Start relative to booking</label></td><td><input style="width: 30px;" name="num_of_days_before_arrival" id="num_of_days_before_arrival"> days before arrival</td></tr>
	<tr><td><label>Visible</label></td><td><input type="checkbox" style="width: 30px;" name="visible" id="visible"></td></tr>
</table>
</fieldset>
<fieldset>
<input type="submit" value="Save Special Offer">
<input type="button" onclick="document.getElementById('so_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;" value="Cancel">
</fieldset>
</form>
</div>


<h2>Existing Special Offers</h2>
<table border="1">

EOT;

$order = "room_type_ids";
if(isset($_REQUEST['order'])) {
	$order = $_REQUEST['order'];
}
$sql = "SELECT sp.* FROM special_offers sp ORDER BY sp.$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get special offers: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
if($result) {
	if(mysql_num_rows($result) > 0)
		echo "	<tr><th><a href=\"view_special_offer.php?order=name\">Name</a></th><th><a href=\"view_special_offer.php?order=room_type_name\">Room types</a></th><th><a href=\"view_special_offer.php?order=start_date\">Start</a></th><th><a href=\"view_special_offer.php?order=end_date\">End</a></th><th><a href=\"view_special_offer.php?order=visible\">Visible</a></th><th>Discount</th><th>Nights</th><th>Valid X days before arrival</th><th>Lang</th><th>Title</th><th>Description</th><th>Room name</th><th></th></tr>\n";
	else
		echo "	<tr><td><i>No record found.</i></td></tr>\n";

	while($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT * FROM lang_text WHERE table_name='special_offers' and row_id=" . $row['id'];
		$result2 = mysql_query($sql, $link);
		if(!$result2) {
			trigger_error("Cannot get special offers texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
		$record = array();
		while($row2 = mysql_fetch_assoc($result2)) {
			$record[$row2['lang']][$row2['column_name']] = $row2['value'];
		}

		echo "<script language=\"JavaScript\">\n";
		echo "	function edit" . $row['id'] . "() {\n";
		echo "		document.getElementById('so_form').reset();\n";
		echo "		document.getElementById('so_form').style.display='block';\n";
		echo "		document.getElementById('create_btn').style.display='none';\n";
		echo "		document.getElementById('id').value='" . $row['id'] . "';\n";
		echo "		document.getElementById('name').value='" . $row['name'] . "';\n";
		echo "		document.getElementById('start_date').value='" . $row['start_date'] . "';\n";
		echo "		document.getElementById('end_date').value='" . $row['end_date'] . "';\n";
		echo "		document.getElementById('num_of_nights').value='" . $row['nights'] . "';\n";
		echo "		document.getElementById('discount').value='" . $row['discount_pct'] . "';\n";
		echo "		document.getElementById('num_of_days_before_arrival').value='" . $row['valid_num_of_days_before_arrival'] . "';\n";
		echo "		document.getElementById('visible').checked=" . ($row['visible'] == 1 ? 'true' : 'false') . ";\n";
		if(strlen($row['room_type_ids']) > 0) {
			$ids = explode(",", $row['room_type_ids']);
			foreach($ids as $selectedRtId) {
				echo "		document.getElementById('room_type_id_$selectedRtId').selected = true;\n";
			}
		}
		foreach($record as $lang => $cols) {
			echo "		document.getElementById('title_$lang').value='" . $cols['title'] . "';\n";
			echo "		document.getElementById('text_$lang').value='" . $cols['text'] . "';\n";
			echo "		document.getElementById('room_name_$lang').value='" . (isset($cols['room_name']) ? $cols['room_name'] : '') . "';\n";
		}
		echo "	}\n";
		echo "</script>\n";

		$first = true;
		foreach($record as $lang => $cols) {
			echo "	<tr>";
			if($first) {
				$rtNames = '';
				if(strlen($row['room_type_ids']) > 0) {
					foreach(explode(',', $row['room_type_ids']) as $rtId) {
						$rtNames .= '- ' . $roomTypes[$rtId] . ' <br> ';
					}
					$rtNames = substr($rtNames, 0, -6);
				}
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $row['name'] . "</td>";
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $rtNames . "</td>";
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $row['start_date'] . "</td>";
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $row['end_date'] . "</td>";
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . ($row['visible'] == 1 ? 'Yes' : '') . "</td>";
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $row['discount_pct'] . "%</td>";
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $row['nights'] . "</td>";
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $row['valid_num_of_days_before_arrival'] . "</td>";
			}
			echo "<td>$lang</td><td><strong>" . $cols['title'] . "</strong></td><td>" . $cols['text'] . "</td><td>" . (isset($cols['room_name']) ? $cols['room_name'] : '') . "</td>";
			if($first) {
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\"><a href=\"#\" onclick=\"edit" . $row['id'] . "();\">Edit</a> <a href=\"delete_special_offer.php?id=" . $row['id'] . "\">Delete</a></td>";
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
