<?php

require('includes/config.php');
require('includes/html_frame.php');
require('includes/error_handler.php');
require('includes/language.php');
require('includes/message.php');
require('includes/ip_to_location.php');
require('includes/audit.php');
require('includes/db.php');

//set_error_handler('printOutErrorHandler');
//set_error_handler('sessionErrorHandler');
set_error_handler('nullErrorHandler');

session_start();

?>
