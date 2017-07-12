<?php

require("includes.php");

$name = $_REQUEST['username'];
$pwd = $_REQUEST['password'];
$hostel = $_REQUEST['hostel'];

if(doLogin($name, $pwd, $hostel)) {
	if(isset($_REQUEST['test_runner_response'])) {
		echo "Successfully logged in";
		return;
	}
	set_message("Successfully logged in");
	if(isset($_SESSION['login_redirect'])) {
		header("Location: " . $_SESSION['login_redirect']);
	} else {
		header("Location: /index.php");
	}
} else {
	if(isset($_REQUEST['test_runner_response'])) {
		echo "Cannot login. ";
		return;
	}
	set_error("Cannot login");
	header("Location: /view_login.php");
}


?>
