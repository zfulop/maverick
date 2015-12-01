<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}



$link = db_connect();

html_start("Home");



html_end();

mysql_close($link);


?>
