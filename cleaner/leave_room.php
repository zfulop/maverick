<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$roomId = $_REQUEST['room_id'];
$roomPart = $_REQUEST['room_id'];
$cleaner = $_SESSION['login_user'];

$link = db_connect();

$type = ($roomPart = 'ROOM' ? 'LEAVE_ROOM' : 'LEAVE_BATHROOM');

$now = date('Y-m-d H:i:s');
if(!CleanerDao::insertCleanerAction($cleaner, $roomId, $type, $link)) {
	set_error('Cannot save leaving the room');
} else {
	set_message('Left room');
}

	$msg = '';	
	if(count($notes) > 0) {
		$msg = implode("\n", $notes);
	}
	
	sendMail("Cleaner", "cleaner", CONTACT_EMAIL, CONTACT_EMAIL, "Room " . $roomData['name'] . " is clean", $msg);
}


header('Location: index.php');

?>
