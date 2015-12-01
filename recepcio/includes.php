<?php

date_default_timezone_set('Europe/Budapest');

require('includes/config.php');
require('../includes/message.php');
require('includes/frame.php');
require('../includes/image_upload.php');
require('../includes/language.php');
require('../includes/error_handler.php');
require('../includes/db.php');
require('../includes/db_config.php');
require('../includes/audit.php');
require('../includes/exchange.php');
require('../includes/mail.php');
require('../includes/login.php');

//set_error_handler('printOutErrorHandler');
set_error_handler('sessionErrorHandler');


session_start();

?>
