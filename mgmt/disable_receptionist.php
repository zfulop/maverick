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


set_message("getting password file...");
$content = file_get_contents(RECEPCIO_BASE_DIR . '.htpasswd');
$lines = split("\n", $content);
$content = '';
foreach($lines as $oneLine) {
	if(!strpos($oneLine, ":"))
		continue;

	list($name, $pwd) = split(":", $oneLine);
	if(($login != $name) and (($login . '_') != $name)) {
		$content .= $oneLine . "\n";
	}
}
file_put_contents(RECEPCIO_BASE_DIR . '.htpasswd', $content);
set_message("new password file saved.");


$link = db_connect();

$sql = "UPDATE receptionists SET enabled=0 WHERE id=$id";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot disable receptionist in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot disable receptionist');
	mysql_close($link);
	return;
}

set_message('Receptionist disabled');
mysql_close($link);

?>
