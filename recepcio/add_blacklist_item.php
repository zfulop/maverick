<?php

require("includes.php");

$link = db_connect();

$name = mysql_escape_string($_REQUEST['name']);
$email = mysql_escape_string($_REQUEST['email']);
$source = mysql_escape_string($_REQUEST['source']);
$today = date('Y-m-d');
$reason = mysql_escape_string($_REQUEST['reason']);


$sql = "INSERT INTO blacklist (name,email,source,reason,date_of_entry) VALUES ('$name', '$email','$source','$reason','$today')";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save blacklist item: " . mysql_error($link) . " (SQL : $sql");
	set_error('Cannot save blacklist item');
} else {
	set_message('Blacklist item saved');
	audit(AUDIT_ADD_BLACKLIST_ITEM, $_REQUEST, 0, 0, $link);
}

mysql_close($link);
header("Location: view_blacklist.php");

?>
