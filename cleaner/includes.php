<?php

date_default_timezone_set('Europe/Budapest');

define('ROOT_DIR', '/home/zolika/dev/roomcaptain_cleaner/');

require(ROOT_DIR . 'includes/config.php');
require(ROOT_DIR . '../includes/message.php');
require(ROOT_DIR . 'includes/frame.php');
require(ROOT_DIR . '../includes/language.php');
require(ROOT_DIR . '../includes/error_handler.php');
require(ROOT_DIR . '../includes/db.php');
require(ROOT_DIR . '../includes/db_config.php');
require(ROOT_DIR . '../includes/audit.php');
require(ROOT_DIR . '../includes/mail.php');
require(ROOT_DIR . '../includes/login.php');
require(ROOT_DIR . '../includes/logger.php');
require(ROOT_DIR . '../includes/dao.php');
require(ROOT_DIR . 'includes/CleanerUtils.php');



set_error_handler('log4phpErrorHandler');


session_start();

if(isset($_SESSION['login_hotel'])) {
	$configFile = ROOT_DIR . '../includes/config/' . $_SESSION['login_hotel'] . '.php';
	if(file_exists($configFile)) {
		require($configFile);
	}
}


?>
