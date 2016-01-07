<?php

require("includes.php");

echo <<<EOT

<html>
	<body style="text-align: center;">

EOT;

	$errors = get_errors();
	foreach($errors as $error) {
		echo "	<div style=\"background-color: #FF0000; margin: 10px;\">ERROR: $error</div>\n";
	}
	clear_errors();
	$warnings = get_warnings();
	foreach($warnings as $warning) {
		echo "	<div style=\"background-color: #FFFF00; margin: 10px;\">WARNING: $warning</div>\n";
	}
	clear_warnings();
	$messages = get_messages();
	foreach($messages as $msg) {
		echo "	<div style=\"background-color: #00FF00; margin: 10px;\">INFO: $msg</div>\n";
	}
	clear_messages();


echo <<<EOT
		<div  style="width: 300px; margin: auto;">
		<form action="do_login.php" method="POST">
		<table style="border: 1px solid black;margin-top: 50px;">
			<tr><th colspan="2">Website Admin Login</th></tr>
			<tr><td>Hostel</td><td><input name="hostel"></td></tr>
			<tr><td>Username</td><td><input name="username"></td></tr>
			<tr><td>Password</td><td><input name="password" type="password"></td></tr>
			<tr><th colspan="2"><input type="submit" value="Login"></th></tr>
		</table>
		</form>
		</div>
	</body>
</html>

EOT;

?>
