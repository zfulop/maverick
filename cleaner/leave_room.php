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
if(!CleanerDao::insertCleanerAction($cleaner, $roomId, 'LEAVE_' . $roomPart, '', $link)) {
	set_error('Cannot save leaving the room');
} else {
	set_message('Left room');
}



header('Location: index.php');

?>
