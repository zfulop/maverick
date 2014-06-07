<?php

require("includes.php");

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

html_start("Maverick Mgmt - Room images");

$langEmptyParams = '';
$langDescrParams = '';
foreach(getLanguages() as $langCode => $langName) {
	$langDescrParams .= "$langCode,";
	$langEmptyParams .= '\'\',';
}
$langDescrParams = substr($langDescrParams, 0, strlen($langDescrParams) - 1);
$langEmptyParams = substr($langEmptyParams, 0, strlen($langEmptyParams) - 1);

echo <<<EOT

<script type="text/javascript">
	function hideUploadPhotoForm() {
		document.getElementById('new_photo_button').style.display='block';
		document.getElementById('save_photo_form').style.display='none';
	}

	function showUploadPhotoForm(title, id, roomTypeId, defaultImg, orderVal, $langDescrParams) {
		document.getElementById('new_photo_button').style.display='none';
		document.getElementById('save_photo_form').style.display='block';
		document.getElementById('save_photo_title').innerHTML=title;
		document.getElementById('id').value=id;
		document.getElementById('room_type_id').value = roomTypeId;
		document.getElementById('default_img').checked = defaultImg;
		document.getElementById('orderField').value = orderVal;

EOT;

foreach(getLanguages() as $langCode => $langName) {
	echo <<<EOT
		document.getElementById('description_$langCode').value=$langCode;

EOT;
}

echo <<<EOT
	}
</script>

<form id="new_photo_button">
	<input type="button" value="Upload new photo" onclick="showUploadPhotoForm('Upload new room image', '','', false, 0, $langEmptyParams);">
</form><br>
<form action="save_room_image.php" style="display:none;" id="save_photo_form" enctype="multipart/form-data" method="POST">
<h2 id="save_photo_title">Upload new room image</h2>
<input type="hidden" name="photo_id" id="id">
<fieldset>
<table>
<tr><td><label>File</label></td><td><input name="photo" id="photo" type="file"></td></tr>
<tr><td><label>Default</label></td><td><input type="checkbox" name="default_img" id="default_img"></td></tr>
<tr><td><label>Order</label></td><td><input name="order" id="orderField"></td></tr>
<tr><td><label>Room type</label></td><td><select name="room_type_id" id="room_type_id">
$roomTypeOptions
</select></td></tr>

EOT;
foreach(getLanguages() as $langCode => $langName) {
	echo <<<EOT
<tr><td><label>Description ($langName)</label></td><td><input id="description_$langCode" style="width: 200px;" name="description_$langCode"></td></tr>

EOT;
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
		$imgUrl = ROOMS_IMG_URL . $file;
		$bgColor='white';
		if($img['default'] == 1) {
			$bgColor='rgb(255, 200, 200)';
		}
		echo <<<EOT
<div style="float: left; margin: 10px; text-align: center; border: dotted; position: relative; padding: 10px; background: $bgColor;">
	<img src="$imgUrl" height="100"><br>
	<div style="text-align: left">

EOT;
		$langDescrParams = '';
		$id = $img['id'];
		$rtid = $img['room_type_id'];
		$def = ($img['default'] == 1 ? 'true' : 'false');
		$ord = $img['_order'];
		echo $rtData['name'] . ' [' . $file . "]<br>\n";
		echo "Order: $ord<br>\n";
		foreach(getLanguages() as $langCode => $langName) {
			$langDescrParams .= '\'' . js_enc($img[$langCode]) . '\',';
			echo $langName . ': ' . $img[$langCode] . "<br>\n";
		}
		$langDescrParams = substr($langDescrParams, 0, strlen($langDescrParams) - 1);
		$editFunction = "showUploadPhotoForm('Edit photo properties', '$id', $rtid, $def, $ord, $langDescrParams)";
		echo <<<EOT
	</div><br>
	<div style="position: absolute; bottom: 0px; width: 100%; text-align: center;">
		<a style="font-size: 12px;" href="delete_room_image.php?id=$id&file=$file">Delete</a>
		<a style="font-size: 12px;" href="#" onclick="$editFunction">Edit</a>
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


?>
