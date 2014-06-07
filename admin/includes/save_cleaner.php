<?php

require("includes.php");

$link = db_connect();

header('Location: view_cleaners.php');

$name = $_REQUEST['name'];
$login = $_REQUEST['login'];
$telephone = $_REQUEST['telephone'];
$id = intval($_REQUEST['id']);

$sql = "SELECT * FROM cleaners WHERE login='$login' AND id<>$id";
$result = mysql_query($sql, $link);
if(mysql_num_rows($result) > 0 or $login == 'zolika') {
	set_error('Cannot save cleaner. The login already exists.');
	mysql_close($link);
	return;
}

if(preg_match("/[^a-zA-Z0-9_-]/", $login) > 0) {
	set_error('Cannot save cleaner. Invalid login specified. The login may contain only the following characters: A-Z, a-z, 0-9, -, _');
	mysql_close($link);
	return;
}


if($id > 0) {
	$sql = "UPDATE cleaners SET name='$name', telephone='$telephone' WHERE id=$id";
} else {
	$sql = "INSERT INTO cleaners (login, name, telephone) VALUES ('$login', '$name', '$telephone')";
}
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save cleaner in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save cleaner');
	mysql_close($link);
	return;
}

set_message('Cleaner saved');
mysql_close($link);

?>
