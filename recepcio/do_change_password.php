<?php

require("includes.php");

if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$pwd = $_REQUEST['curr_pwd'];
$nPwd = $_REQUEST['new_pwd'];
$nPwd2 = $_REQUEST['new_pwd_2'];

if(!doLogin($_SESSION['login_user'], $pwd, $_SESSION['login_hotel'])) {
	set_error("Current password is invalid");
	header("Location: change_password.php");
	return;
}
if($nPwd <> $nPwd2) {
	set_error("The new passwords does not match");
	header("Location: change_password.php");
	return;
}

$link = db_connect();

$sql = "UPDATE users SET password='" . mysql_real_escape_string(crypt($nPwd), $link) . "' WHERE id=" . $_SESSION['login_user_id'];
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Could not change password. " . mysql_error($link) . " (SQL: $sql)");
	set_error("Could not change password");
	header("Location: change_password.php");
} else {
	set_message("Password changed successfully");
	header("Location: index.php");

}

mysql_close($link);


?>
