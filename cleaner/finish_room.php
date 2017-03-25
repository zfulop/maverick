<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$roomId = $_REQUEST['room_id'];
$roomPart = $_REQUEST['room_part'];
$cleaner = $_SESSION['login_user'];

$link = db_connect();

$now = date('Y-m-d H:i:s');
if(!CleanerDao::insertCleanerAction($cleaner, $roomId, 'FINISH_' . $roomPart, '', $link)) {
	set_error('Cannot save finishing the room');
} else {
	set_message('Finished room');
}

header('Location: index.php');

?>
