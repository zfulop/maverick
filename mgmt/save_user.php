<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

header('Location: view_users.php');

$id = $_REQUEST['id'];
$username = mysql_real_escape_string($_REQUEST['username'], $link);
$name = mysql_real_escape_string($_REQUEST['name'], $link);
$email = mysql_real_escape_string($_REQUEST['email'], $link);
$telephone = mysql_real_escape_string($_REQUEST['telephone'], $link);
$role = mysql_real_escape_string($_REQUEST['role'], $link);
$pwd = mysql_real_escape_string(crypt(''), $link);

if($role == 'MANAGER' and $_SESSION['login_role'] != 'ADMIN') {
	set_error('MANAGER role can only be assigned by an ADMIN user');
	mysql_close($link);
	return;
}

if($role == 'ADMIN' and $_SESSION['login_role'] != 'ADMIN') {
	set_error('ADMIN role can only be assigned by an ADMIN user');
	mysql_close($link);
	return;
}

$msg = '';
if($id > 0) {
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
	$sql = "UPDATE users SET name='$name', email='$email', telephone='$telephone', role='$role' WHERE id=$id";
	$msg = 'User saved. ';
} else {
	$sql = "INSERT INTO users (username, name, email, telephone, role, password) VALUES ('$username', '$name', '$email', '$telephone', '$role', '$pwd')";
	$msg = 'User created with empty password';
}


$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save user in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save user');
	mysql_close($link);
	return;
}

set_message($msg);
mysql_close($link);

?>
