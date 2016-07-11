<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

header('Location: view_users.php');

$id = $_REQUEST['id'];
$pwd = mysql_real_escape_string(crypt(''), $link);

$sql = "SELECT * FROM users where id=$id";
$result = mysql_query($sql, $link);
$user = mysql_fetch_assoc($result);
if($user['role'] == 'ADMIN' and $id <> $_SESSION['login_user_id']) {
	set_error('ADMIN user can only be modified by him or herself');
	mysql_close($link);
	return;
}
if($user['role'] == 'MANAGER' and $_SESSION['login_role'] != 'ADMIN') {
	set_error('MANAGER user can only be modified by an ADMIN user');
	mysql_close($link);
	return;
}

$sql = "UPDATE users SET password='$pwd' WHERE id=$id";

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save user in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save user');
	mysql_close($link);
	return;
}

set_message('User password reset to empty string.');
mysql_close($link);

?>
