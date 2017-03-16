<?php

require("includes.php");

$locations = array('lodge','hostel');

foreach($locations as $location) {
	$link = db_connect($location);
	echo "Saving image into directory: " . ROOMS_IMG_DIR . $location . "/\n";
	$sql = "SELECT * FROM room_images";
	$result = mysql_query($sql, $link);
	while($row = mysql_fetch_assoc($result)) {
		$id = $row['id'];
		$fullpath = ROOMS_IMG_DIR . $location . '/' . $row['filename'];
		echo "Full path of the file: $fullpath \n";
		$thumb = createThumbnail('thumb_', $fullpath, 375, 375);
		$mid = createThumbnail('mid_', $fullpath, 700, 700);
		$thumb = basename($thumb);
		$mid = basename($mid);
		$sql = "UPDATE room_images SET thumb='$thumb', medium='$mid' WHERE id=$id";
		if(!mysql_query($sql, $link)) {
			echo "Cannot update image data in db: " . mysql_error($link) . " (SQL: $sql)\n";
		} else {
			echo "Image updated $thumb $mid\n";
		}
	}
	mysql_close($link);
}

?>
