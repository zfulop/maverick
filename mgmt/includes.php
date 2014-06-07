<?php

date_default_timezone_set('Europe/Budapest');

require('includes/config.php');
require('../recepcio/includes/message.php');
require('includes/frame.php');
require('../recepcio/includes/image_upload.php');
require('../recepcio/includes/language.php');
require('../recepcio/includes/error_handler.php');
require('../recepcio/includes/db.php');
require('../recepcio/includes/audit.php');
require('../recepcio/includes/exchange.php');

//set_error_handler('printOutErrorHandler');
set_error_handler('sessionErrorHandler');


session_start();

?>
