<?php

require("includes.php");

$link = db_connect();

header('Location: view_receptionists.php');

$name = $_REQUEST['name'];
$login = $_REQUEST['login'];
$email = $_REQUEST['email'];
$telephone = $_REQUEST['telephone'];
$id = intval($_REQUEST['id']);

$sql = "SELECT * FROM receptionists WHERE login='$login' AND id<>$id";
$result = mysql_query($sql, $link);
if(mysql_num_rows($result) > 0 or $login == 'zolika') {
	set_error('Cannot save receptionist. The login already exists.');
	mysql_close($link);
	return;
}

if(preg_match("/[^a-zA-Z0-9_-]/", $login) > 0) {
	set_error('Cannot save receptionist. Invalid login specified. The login may contain only the following characters: A-Z, a-z, 0-9, -, _');
	mysql_close($link);
	return;
}


if($id > 0) {
	$sql = "UPDATE receptionists SET name='$name', email='$email', telephone='$telephone' WHERE id=$id";
} else {
	$sql = "INSERT INTO receptionists (login, name, email, telephone, enabled) VALUES ('$login', '$name', '$email', '$telephone', 0)";
}
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save receptionist in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save receptionist');
	mysql_close($link);
	return;
}

set_message('Receptionist saved');
mysql_close($link);

?>
