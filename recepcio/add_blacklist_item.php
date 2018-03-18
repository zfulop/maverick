<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

$name = mysql_real_escape_string($_REQUEST['name'], $link);
$email = mysql_real_escape_string($_REQUEST['email'], $link);
$source = mysql_real_escape_string($_REQUEST['source'], $link);
$today = date('Y-m-d');
$reason = mysql_real_escape_string($_REQUEST['reason'], $link);


$sql = "INSERT INTO blacklist (name,email,source,reason,date_of_entry) VALUES ('$name', '$email','$source','$reason','$today')";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save blacklist item: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save blacklist item');
} else {
	set_message('Blacklist item saved');
	logDebug("Saving blacklist item: $sql");
	audit(AUDIT_ADD_BLACKLIST_ITEM, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: view_blacklist.php");

?>
