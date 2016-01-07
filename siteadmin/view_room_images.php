<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$images = array();
$roomTypeOptions = '';
$roomTypes = array();
$sql = "SELECT * FROM room_types ORDER BY name";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	$roomTypeOptions .= "	<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>\n";
	$images[$row['id']] = array();
	$roomTypes[$row['id']] = $row;
}

$sql = "SELECT room_images.*, lang_text.value AS description, lang_text.lang, room_types.name as room_type FROM room_images INNER JOIN room_types ON room_images.room_type_id=room_types.id LEFT OUTER JOIN lang_text ON (lang_text.table_name='room_images' AND lang_text.column_name='description' AND row_id=room_images.id) ORDER BY room_images._order";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	if(!isset($images[$row['room_type_id']][$row['filename']])) {
		$images[$row['room_type_id']][$row['filename']] = $row;
		foreach(getLanguages() as $langCode => $langName)
			$images[$row['room_type_id']][$row['filename']][$langCode] = '';
	}
	$images[$row['room_type_id']][$row['filename']][$row['lang']] = $row['description'];
}

html_start("Room images");

echo <<<EOT

<script src="js/jquery.js" type="text/javascript"></script>


<script type="text/javascript">
	function hideUploadPhotoForm() {
		document.getElementById('new_photo_button').style.display='block';
		document.getElementById('save_photo_form').style.display='none';
	}

	function showUploadPhotoForm() {
		document.getElementById('new_photo_button').style.display='none';
		document.getElementById('save_photo_form').style.display='block';
	}
</script>

<form id="new_photo_button">
	<input type="button" value="Upload new photo" onclick="showUploadPhotoForm();">
</form><br>
<form action="save_room_image.php" style="display:none;" id="save_photo_form" enctype="multipart/form-data" method="POST">
<h2 id="save_photo_title">Upload new room images</h2>
<fieldset>
<table>

EOT;
for($i = 0; $i < 10; $i++) {
	printImageFormBlock($i, $roomTypeOptions);
}
echo <<<EOT
</table>
</fieldset>
<fieldset>
<input type="submit" value="Save">
<input type="button" value="Cancel" onClick="hideUploadPhotoForm()">
</fieldset>
</form>
<br>

<h2>Existing Room images</h2>

EOT;

foreach($roomTypes as $rtId => $rtData) {
	echo "<hr style=\"clear:both;\">\n";
	echo "<h1>" . $rtData['name'] . "</h1>\n";
	foreach($images[$rtId] as $file => $img) {
		$id = $img['id'];
		$imgUrl = ROOMS_IMG_URL . $file;
		$bgColor='white';
		if($img['default'] == 1) {
			$bgColor='rgb(255, 200, 200)';
		}
		echo <<<EOT
<div style="float: left; margin: 10px; text-align: center; border: dotted; position: relative; padding: 10px; background: $bgColor;">
	<img src="$imgUrl" height="100"><br>
	<div style="text-align: left;display: block;" id="view_$id">

EOT;
		echo $rtData['name'] . ' [' . $file . "]<br>\n";
		echo "Order: " . $img['_order'] . "<br>\n";
		foreach(getLanguages() as $langCode => $langName) {
			echo $langName . ': ' . $img[$langCode] . "<br>\n";
		}
		echo <<<EOT
	</div>
	<div style="text-align: left;display: none;" id="edit_$id">
		<form action="save_room_image_data.php" method="POST" accept-charset="utf-8">
		<input type="hidden" name="rtid" value="$rtId">
		<input type="hidden" name="id" value="$id">
		<table>

EOT;
		echo "			<tr><td>Default:</td><td><input name=\"default\" type=\"checkbox\" value=\"true\"" . ($img['default'] == 1 ? ' checked' : '') . "></td></tr>\n";
		echo "			<tr><td>Order:</td><td><input name=\"order\" value=\"" . $img['_order'] . "\"></td></tr>\n";
		foreach(getLanguages() as $langCode => $langName) {
			echo "			<tr><td>$langName:</td><td><input name=\"$langCode\" value=\"" . $img[$langCode] . "\"></td></tr>\n";
		}
		echo <<<EOT
		<tr><td colspan="2"><input type="submit" value="Save"></td></tr>
		</table>
		</form>
	</div>
	<br>
	<div style="position: absolute; bottom: 0px; width: 100%; text-align: center;">
		<a style="font-size: 12px;" href="delete_room_image.php?id=$id&file=$file">Delete</a>
		<a style="font-size: 12px;" href="#" onclick="document.getElementById('edit_$id').style.display='block';document.getElementById('view_$id').style.display='none';return false;">Edit</a>
		<a style="font-size: 12px;" href="#" onclick="document.getElementById('view_$id').style.display='block';document.getElementById('edit_$id').style.display='none';return false;">View</a>
	</div>

</div>


EOT;
	}
}


html_end();
mysql_close($link);

function js_enc($str) {
	return str_replace("'", "\\'", $str);
}

function printImageFormBlock($cntr, $roomTypeOptions) {
	$trStyle="display:block;";
	$addRowStyle = "display:none;";
	if($cntr > 0) {
		$trStyle="display:none;";
	}
	$rtiFormName = "room_type_id_$cntr" . '[]';
	echo <<<EOT
<tr class="r_$cntr" style="$trStyle"><td><label>File</label></td><td><input name="photo_$cntr" type="file"></td></tr>
<tr class="r_$cntr" style="$trStyle"><td><label>Default</label></td><td><input type="checkbox" name="default_img_$cntr"></td></tr>
<tr class="r_$cntr" style="$trStyle"><td><label>Order</label></td><td><input name="order_$cntr"></td></tr>
<tr class="r_$cntr" style="$trStyle"><td><label>Room type</label></td><td><select name="$rtiFormName" multiple="yes" style="wdth:200px;height:100px;">
$roomTypeOptions
</select></td></tr>


EOT;
	foreach(getLanguages() as $langCode => $langName) {
		$formName = 'description_' . $langCode . '_' . $cntr;
		echo <<<EOT
<tr class="r_$cntr" style="$trStyle"><td><label>Description ($langName)</label></td><td><input style="width: 200px;" name="$formName"></td></tr>

EOT;
	}

	$nextCntr = $cntr + 1;
	echo <<<EOT
<tr class="r_$cntr" id="ar_$cntr" style="$trStyle"><td colspan="2"><a href="#" onclick="$('#ar_$cntr').hide();$('tr.r_$nextCntr').css('display', 'block');return false;">Add more image</a></td></tr>

EOT;

}

?>
