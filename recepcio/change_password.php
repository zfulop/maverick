<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}



html_start("Change password");

echo <<<EOT


		<div  style="width: 300px; margin: auto;">
		<form action="do_change_password.php" method="POST">
		<table style="border: 1px solid black;margin-top: 50px;">
			<tr><th colspan="2">Change password</th></tr>
			<tr><td>Current password</td><td><input type="password" name="curr_pwd"></td></tr>
			<tr><td>New password</td><td><input type="password" name="new_pwd"></td></tr>
			<tr><td>New&nbsp;password&nbsp;again</td><td><input type="password" name="new_pwd_2"></td></tr>
			<tr><th colspan="2"><input type="submit" value="Change password"></th></tr>
		</table>
		</form>
		</div>
	</body>
</html>

EOT;

?>
