<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();


html_start("Home");



html_end();


?>
