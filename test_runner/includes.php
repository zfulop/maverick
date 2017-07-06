<?php

date_default_timezone_set('Europe/Budapest');

define('ROOT_DIR', '/home/maveric3/dev/test_runner/');
define('LOG_DIR', '/home/maveric3/logs/dev/');


require(ROOT_DIR . '../includes/message.php');
require(ROOT_DIR . '../includes/language.php');
require(ROOT_DIR . '../includes/error_handler.php');
require(ROOT_DIR . '../includes/db.php');
require(ROOT_DIR . '../includes/db_config.php');
require(ROOT_DIR . '../includes/dao.php');
require(ROOT_DIR . '../includes/booking_ref_gen.php');
require(ROOT_DIR . '../includes/exchange.php');
require(ROOT_DIR . '../includes/logger.php');

set_error_handler('printOutErrorHandler');


?>
