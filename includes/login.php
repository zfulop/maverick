<?php

define('SITE_ADMIN', 'ADMIN');
define('SITE_MGMT', 'MGMT');
define('SITE_RECEPTION', 'RECEPTION');

define('ROLE_ADMIN', 'ADMIN');
define('ROLE_MGMT', 'MGMT');
define('ROLE_RECEPTION', 'RECEPTION');

$ROLES = array(
	SITE_ADMIN => array(ROLE_ADMIN),
	SITE_MGMT => array(ROLE_ADMIN, ROLE_MGMT),
	SITE_RECEPTION => array(ROLE_ADMIN, ROLE_MGMT, ROLE_RECEPTION)
);

function checkLogin($site) {
	global $ROLES;
	if(!isset($_SESSION['logged_in'])) {
		header('Location: view_login.php');
		return false;
	}
	if(!isset($ROLES[$site])) {
		header('Location: view_login.php');
		return false;
	}
	if(!in_array($_SESSION['login_role'], $ROLES[$site])) {
		header('Location: view_login.php');
		return false;
	}
	return true;
}

function doLogin($name, $pwd, $hotel) {
	$link = db_connect($hotel, true);
	$sql = "SELECT * FROM users WHERE username='$name'";
	$result = mysql_query($sql, $link);
	if(mysql_num_rows($result) < 1) {
		mysql_close($link);
		return false;
	}
	$row = mysql_fetch_assoc($result);
	mysql_close($link);
	if(hash_equals($row['password'], crypt($pwd, $row['password']))) {
		$_SESSION['logged_in'] = true;
		$_SESSION['login_user'] = $name;
		$_SESSION['login_role'] = $row['role'];
		$_SESSION['login_hotel'] = $row['hotel'];
		return true;
	}
	return false;
}

function logout() {
	unset($_SESSION['logged_in']);
	unset($_SESSION['login_user']);
	unset($_SESSION['login_role']);
}

?>
