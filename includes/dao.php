<?php

// load the Dao classes from the dao subfolder
$dir = dirname(__FILE__) . '/dao/';
foreach(scandir($dir) as $item) {
	if(strpos($item, "Dao.php") > 0) {
		require($dir . $item);
	}
}

?>
