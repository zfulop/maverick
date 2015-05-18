<?php

date_default_timezone_set('Europe/Budapest');

require('includes/config.php');
require(RECEPCIO_BASE_DIR . 'includes/message.php');
require('includes/frame.php');
require(RECEPCIO_BASE_DIR . 'includes/image_upload.php');
require(RECEPCIO_BASE_DIR . 'includes/language.php');
require(RECEPCIO_BASE_DIR . 'includes/error_handler.php');
require(RECEPCIO_BASE_DIR . 'includes/db.php');
require(RECEPCIO_BASE_DIR . 'includes/audit.php');
require(RECEPCIO_BASE_DIR . 'includes/exchange.php');

//set_error_handler('printOutErrorHandler');
set_error_handler('sessionErrorHandler');


session_start();

?>
