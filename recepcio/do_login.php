<?php

require("includes.php");

$name = $_REQUEST['username'];
$pwd = $_REQUEST['password'];
$hostel = $_REQUEST['hostel'];

if(doLogin($name, $pwd, $hostel)) {
	set_message("Successfully logged in");
	header("Location: index.php");
} else {
	set_error("Cannot login");
	header("Location: view_login.php");
}


?>
