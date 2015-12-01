<?php

require("includes.php");

if(!checkLogin(SITE_ADMIN)) {
	return;
}

html_start();

html_end();

?>
