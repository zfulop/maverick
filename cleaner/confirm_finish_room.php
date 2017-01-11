<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$roomId = $_REQUEST['room_id'];
$supervisor = $_SESSION['login_user'];

$link = db_connect();

$dayToShow = date('Y-m-d');

if(!CleanerDao::insertCleanerAction($supervisor, $roomId, 'CONFIRM_FINISH_ROOM', '', $link)) {
	set_error("could not save room finish confirmation");
} else {
	set_message("room finish confirmed");
}

header("Location: view_cleaner_assignments.php");

mysql_close($link);

?>