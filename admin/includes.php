<?php

require('includes/config.php');
require('includes/message.php');
require('includes/frame.php');
require('includes/image_upload.php');
require('includes/language.php');
require('includes/error_handler.php');
require('includes/db.php');

set_error_handler('sessionErrorHandler');


session_start();

?>
