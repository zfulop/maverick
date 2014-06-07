<?php

require("includes.php");

$link = db_connect();

$sql = "SELECT images.*, lang_text.value AS description, lang_text.lang FROM images LEFT OUTER JOIN lang_text ON (lang_text.table_name='images' AND lang_text.column_name='description' AND row_id=images.id)";
$result = mysql_query($sql, $link);
$images = array();
while($row = mysql_fetch_assoc($result)) {
	if(!isset($images[$row['filename']])) {
		$images[$row['filename']] = $row;
		foreach(getLanguages() as $langCode => $langName)
			$images[$row['filename']][$langCode] = '';
	}
	$images[$row['filename']][$row['lang']] = $row['description'];
}

html_start("Maverick Mgmt - Photos");

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

	function showUploadPhotoForm(title, id, type, $langDescrParams) {
		document.getElementById('new_photo_button').style.display='none';
		document.getElementById('save_photo_form').style.display='block';
		document.getElementById('save_photo_title').innerHTML=title;
		document.getElementById('id').value=id;
		var typeSelect = document.getElementById('type');
		for(var i=0; i < typeSelect.length; i++) {
			if(typeSelect[i].value == type) {
				typeSelect[i].selected = true;
			}
		}

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
	<input type="button" value="Upload new photo" onclick="showUploadPhotoForm('Upload new photo', '', 'HOSTEL', $langEmptyParams);">
</form><br>
<form action="save_photo.php" style="display:none;" id="save_photo_form" enctype="multipart/form-data" method="POST">
<h2 id="save_photo_title">Upload new photo</h2>
<fieldset>
<label>File</label><input name="photo" id="photo" type="file"><br>
<input type="hidden" name="photo_id" id="id">
<label>Hostel or Ensuite</label><select name="type" id="type">
	<option value="HOSTEL">Hostel</option>
	<option value="ENSUITE">Ensuite</option>
	<option value="ENSUITE_TOP">Ensuite on the top</option>
</select><br>

EOT;
foreach(getLanguages() as $langCode => $langName) {
	echo <<<EOT
<label>Description ($langName)</label><input id="description_$langCode" style="width: 200px;" name="description_$langCode"><br>

EOT;
}

echo <<<EOT
</fieldset>
<fieldset>
<input type="submit" value="Save">
<input type="button" value="Cancel" onClick="hideUploadPhotoForm()">
</fieldset>
</form>
<br>

<h2>Existing Photos</h2>

EOT;


if ($dh = opendir(PHOTOS_DIR)) {
	while ($file = readdir($dh)) {
		if(is_dir(PHOTOS_DIR . "/" . $file))
			continue;
		if(substr($file, 0, 7) != '_thumb_')
			continue;

		$filename = substr($file, 7);

		if(!isset($images[$filename])) {
			echo "deleting: $file";
//			unlink(PHOTOS_DIR . "/" . $file);
//			unlink(PHOTOS_DIR . "/" . $filename);
			continue;
		}

		$imgUrl = PHOTOS_URL . "/$file";
		echo <<<EOT
<div style="float: left; margin: 10px; text-align: center; width: 200px; height: 310px; border: dotted; position: relative; padding: 10px;">
	<img src="$imgUrl"><br>
	<div style="text-align: left">

EOT;
		$langDescrParams = '';
		$id = $images[$filename]['id'];
		$type = $images[$filename]['type'];
		echo $images[$filename]['type'] . ' [' . $filename . "]<br>\n";
		foreach(getLanguages() as $langCode => $langName) {
			$langDescrParams .= '\'' . js_enc($images[$filename][$langCode]) . '\',';
			echo $langName . ': ' . $images[$filename][$langCode] . "<br>\n";
		}
		$langDescrParams = substr($langDescrParams, 0, strlen($langDescrParams) - 1);
		$editFunction = "showUploadPhotoForm('Edit photo properties', '$id', '$type', $langDescrParams)";
		echo <<<EOT
	</div><br>
	<div style="position: absolute; bottom: 0px; width: 100%; text-align: center;">
		<a style="font-size: 12px;" href="delete_photo.php?id=$id&file=$filename">Delete</a>
		<a style="font-size: 12px;" href="#" onclick="$editFunction">Edit</a>
	</div>

</div>

EOT;
	}
	closedir($dh);
}


html_end();
mysql_close($link);

function js_enc($str) {
	return str_replace("'", "\\'", $str);
}


?>
