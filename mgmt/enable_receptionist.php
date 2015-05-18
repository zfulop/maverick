<?php

require("includes.php");

if(!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data) {
        $f = fopen($filename, 'w');
        if (!$f) {
            return false;
        } else {
            $bytes = fwrite($f, $data);
            fclose($f);
            return $bytes;
        }
    }
}

if(!function_exists('file_get_contents')) {
	function file_get_contents($filename) {
		$fhandle = fopen($filename, "r");
		$fcontents = fread($fhandle, filesize($filename));
		fclose($fhandle);
		return $fcontents;
	}
}


header('Location: view_receptionists.php');

$id = intval($_REQUEST['id']);
$login = $_REQUEST['login'];
$pwd1 = $_REQUEST['password1'];
$pwd2 = $_REQUEST['password2'];

if($pwd1 != $pwd2) {
	set_error("Cannot enable receptionist because the passwords do not match.");
	return;
}

set_message("getting password file...");
$content = file_get_contents(RECEPCIO_BASE_DIR . '.htpasswd');
$content .= $login . ":" . crypt($pwd1) . "\n";
$content .= $login . "_:" . crypt($pwd1) . "\n";
file_put_contents(RECEPCIO_BASE_DIR . '.htpasswd', $content);
//$output = shell_exec("$HTPASSWD_CMD -b ../recepcio/.htpasswd $login $pwd1 2>&1");
//set_message($output);
set_message("new password file saved.");

$link = db_connect();

$sql = "UPDATE receptionists SET enabled=1 WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot enable receptionist in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot enable receptionist');
	mysql_close($link);
	return;
}

set_message('Receptionist enabled');
mysql_close($link);


?>
