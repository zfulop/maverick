<?php

require dirname(__FILE__) . '/log4php/Logger.php';

$today = date('Ymd');

if(isset($_SERVER['SCRIPT_NAME'])) {
	$scriptName = $_SERVER['SCRIPT_NAME']; 
	if(substr($scriptName,0,1) == '/') {
		$scriptName = substr($scriptName, 1);
	}
	$logFile = LOG_DIR . $scriptName . '_' . $today . '.log';
} elseif(isset($argv[0])) {
	$logFile = LOG_DIR . $argv[0] . '_' . $today . ".log";
} else {
	$logFile = LOG_DIR . 'default_' . $today . '.log';
}

Logger::configure(array(
    'rootLogger' => array(
        'appenders' => array('default'),
    ),
    'appenders' => array(
        'default' => array(
            'class' => 'LoggerAppenderDailyFile',
            'layout' => array(
                'class' => 'LoggerLayoutPattern'
            ),
            'params' => array(
            	'file' => $logFile,
				'threshold' => 'all',
            	'append' => true
            )
        )
    )
));

function logDebug($msg) {
	$logger = Logger::getRootLogger();
	$logger->debug($msg);
}

function logError($msg) {
	$logger = Logger::getRootLogger();
	$logger->error($msg);
}

function logInfo($msg) {
	$logger = Logger::getRootLogger();
	$logger->info($msg);
}

function logWarn($msg) {
	$logger = Logger::getRootLogger();
	$logger->warn($msg);
}

?>