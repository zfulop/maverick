<?php

require("includes.php");

$link = db_connect();

$sql = "SELECT * FROM videos ORDER BY _order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get videos in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}

html_start("Maverick Mgmt - Videos");


echo <<<EOT

<form id="create_btn">
<input type="button" onclick="document.getElementById('video_form').reset();document.getElementById('video_form').style.display='block'; document.getElementById('create_btn').style.display='none'; return false;" value="Create new video">
</form>
<br>

<form action="save_video.php" id="video_form" accept-charset="utf-8" method="POST" style="display: none;">
<fieldset>
<input type="hidden" name="id" id="id" value="">

EOT;
foreach(getLanguages() as $langCode => $langName) {
	echo <<<EOT
<label>Title ($langName)</label><input name="title_$langCode"  id="title_$langCode"  style="width: 200px;"><br>
<label style="height: 60px;">HTML ($langName)</label><textarea style="width: 200px; height: 100px" name="html_$langCode" id="html_$langCode"></textarea><br>

EOT;
}
echo <<<EOT
<label>Order</label><input name="order" id="order" style="width: 40px;"><br>
</fieldset>
<fieldset>
<input type="submit" value="Save video">
<input type="button" value="Cancel" onclick="document.getElementById('video_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;">
</fieldset>
</form>


<h2>Existing Videos</h2>
<table border="1">

EOT;
if($result) {
	if(mysql_num_rows($result) > 0)
		echo "	<tr><th>Order</th><th>Lang</th><th>Title</th><th>Video</th><th>HTML</th><th></th></tr>\n";
	else
		echo "	<tr><td><i>No record found.</i></td></tr>\n";

	$searches = array( "'", "\n" );
	$replacements = array( "\\'", "\\n'\n\t+'" );
	while($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT * FROM lang_text WHERE table_name='videos' and row_id=" . $row['id'];
		$result2 = mysql_query($sql, $link);
		if(!$result2) {
			trigger_error("Cannot get video texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		}
		$record = array();
		while($row2 = mysql_fetch_assoc($result2)) {
			$record[$row2['lang']][$row2['column_name']] = $row2['value'];
		}
		$first = true;
		foreach($record as $lang => $cols) {
			echo "	<tr>";
			if($first) {
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">" . $row['_order'] . ".</td>";
			}
			echo "<td>$lang</td><td><strong>" . $cols['title'] . "</strong></td><td>" . $cols['html'] . "</td><td>" . htmlspecialchars($cols['html']) . "</td>";
			if($first) {
				echo "<td valign=\"middle\" rowspan=\"" . count($record) . "\">\n";
				echo "	<a href=\"delete_video.php?id=" . $row['id'] . "\">Delete</a>\n";
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
