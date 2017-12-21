<?php

date_default_timezone_set('Europe/Budapest');

define('START_DATE', '2014-10-01');
define('START_DATE_SLASH', '2014/10/01');

require('includes/config.php');
require('includes/message.php');
require('includes/frame.php');
require('includes/image_upload.php');
require('includes/language.php');
require('includes/error_handler.php');
require('includes/db.php');
require('includes/audit.php');
require('includes/exchange.php');
require('includes/mail.php');
require('includes/BookingDao.php');
require('includes/PaymentDao.php');

//set_error_handler('printOutErrorHandler');
set_error_handler('sessionErrorHandler');


session_start();

?>
