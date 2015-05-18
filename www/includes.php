<?php

require('includes/config.php');
require('includes/error_handler.php');
require('includes/language.php');
require('includes/location.php');
require('includes/exchange.php');
require(RECEPCIO_BASE_DIR . 'includes/exchange.php');
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
