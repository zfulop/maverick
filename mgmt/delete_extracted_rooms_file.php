<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$fwd = 'view_rooms.php';
if(isset($_REQUEST['forward'])) {
	$fwd = $_REQUEST['forward'];
}

$dir = JSON_DIR . getLoginHotel();
logDebug("Deleting extracted room info from folder: $dir");

$files = glob($dir . '/rooms*');
foreach($files as $file) {
	logDebug("\t$file");
	unlink($file);
}

set_message("Extracted files containing room data removed");

header('Location: ' . $fwd);

?>
