<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$roomId = $_REQUEST['room_id'];
$cleaner = $_SESSION['login_user'];

$link = db_connect();

if(!CleanerDao::insertCleanerAction($cleaner, $roomId, 'NOTE', $_REQUEST['note'], $link) {
	set_error("Cannot insert note.");
} else {
	set_message('Note saved');
}

header("Location: view_room.php?room_id=$roomId");

?>
