<?php

ini_set("include_path", '/home/zolika/php:' . ini_get("include_path") );

require('includes/config.php');
require('includes/error_handler.php');
require('includes/language.php');
require('includes/location.php');
require('../includes/exchange.php');
require('../includes/booking_ref_gen.php');
require('includes/exchange.php');
require('includes/db.php');
require('includes/html_frame.php');
require('includes/message.php');
require('includes/ip_to_location.php');
require('includes/audit.php');
require('includes/mail.php');

set_error_handler('printOutErrorHandler');
//set_error_handler('sessionErrorHandler');
//set_error_handler('nullErrorHandler');
session_start();
initLanguage();
initCurrency();


?>