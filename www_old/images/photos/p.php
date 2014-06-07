<?php

if ($dh = opendir(".")) {
	$sql = "INSERT INTO images (filename, type) VALUES ";
	while ($file = readdir($dh)) {
		if(is_dir("./" . $file))
			continue;
		if(substr($file, 0, 7) == '_thumb_')
			continue;

		$sql .= "('$file', 'HOSTEL'),";
	}
	echo $sql;
}

