<?php

require("includes.php");

if(!checkLogin(SITE_CLEANER)) {
	return;
}

$role = $_REQUEST['target'];

if(($_SESSION['login_role'] == $role) or ($role == 'CLEANER')) {
	$_SESSION['login_role_override'] = $role;
}

header('Location: index.php');

return;

?>
