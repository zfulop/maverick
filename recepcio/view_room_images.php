<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$location = $_SESSION['login_hotel'];

$images = array();
$roomTypeOptions = '';
$roomTypes = RoomDao::getRoomTypes('eng', $link);
foreach($roomTypes as $roomTypeId => $row) {
	$imagesPerRoomType[$row['id']] = array();
}

$roomImages = RoomDao::getRoomImages(array_keys(getLanguages()), $link);

$extraHeader = <<<EOT
<script src="js/dropzone.js" type="text/javascript"></script>

<link rel="stylesheet" href="css/dropzone.css">

<script type="text/javascript">

	function editImage(imgId) {
		new Ajax.Request('edit_room_image.php', {
			method: 'post',
			parameters: {'id': imgId},
			onSuccess: function(transport) {
				Tip(transport.responseText, STICKY, true, FIX, ['room_image_' + imgId, 0, 0], CLICKCLOSE, false, CLOSEBTN, true);
			},
			onFailure: function(transport) {
				alert('HTTP Error in response. Please try again.');
			}
		});

		return false;
	}
	
	function loadImages() {
		new Ajax.Updater('images', 'get_room_image_list.php', {});
	}

	Dropzone.options.dropzoneForm = {
		init: function() {
			this.on("success", function(file) { loadImages(); });
		}
	};
</script>

EOT;

html_start("Room images", $extraHeader);

echo <<<EOT

<form action="save_room_image.php" id="dropzone-form" class="dropzone"></form>

<h2>Existing Room images</h2>


<div id="images"></div>
<div style="clear:both;"></div>

<hr>

<h2>Room images per room types</h2>

EOT;

foreach($roomTypes as $rtId => $rtData) {
	echo "<h3>" . $rtData['name'] . "</h3>\n";
	foreach($roomImages as $riId => $img) {
		if(!in_array($rtId, $img['room_types'])) {
			continue;
		}
		$id = $img['id'];
		$imgUrl = ROOMS_IMG_URL . $location . '/' . $img['thumb'];
		$bgColor='black';
		if(in_array($rtId, $img['default_for_room_types'])) {
			$bgColor='rgb(255, 200, 200)';
		}		
		echo <<<EOT
<div style="float: left; margin: 10px; text-align: center; border: dotted $bgColor; position: relative; padding: 10px;">
	<a href="set_room_image_default.php?room_type_id=$rtId&room_image_id=$id" title="Set default image">
	<img src="$imgUrl" height="100"><br>
	</a>
	$defaultTxt
</div>

EOT;
	}
	echo "<div style=\"clear:both;\"></div><hr style=\"clear:both;\">\n";
}

echo <<<EOT

<script type="text/javascript">
	loadImages();
</script>


EOT;

html_end();
mysql_close($link);


?>
