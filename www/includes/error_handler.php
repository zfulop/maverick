<?php

/////////////////////////////////////////////////////
// This file contains the error hahdling function. //
/////////////////////////////////////////////////////

// This is the error handler that is set when reporting errors to avoid infinite loop of
// errors.
function nullErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
}


function printOutErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
	echo "[$errno] $errstr ($errfile:$errline)<br>\n\t";
	/*echo "Request: \n";
	var_export($_REQUEST, false);
	if(isset($_SESSION)) {
		echo "\n\nSession: ";
		var_export($_SESSION, false);
	}
	 */
}


// error handler function
function sessionErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
	$msg = "[$errno] $errstr ($errfile:$errline)\n\t";
	/*
	$msg .= "Request: \n";
	$msg .= print_r($_REQUEST, true);
	if(isset($_SESSION)) {
		$msg .= "\n\nSession: ";
		$msg .= print_r($_SESSION, true);
	}
	*/
	set_error($msg);
}

function dbErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
	$errorLink = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, true);
	mysql_select_db(DB_NAME, $errorLink);
	$errCtx = "Request: \n";
	$errCtx .= print_r($_REQUEST, true);
	$errCtx .= "\n\nSession: ";
	$errCtx .= print_r($_SESSION, true);
	$errCtx .= "\n\nServer: ";
	$errCtx .= print_r($_SERVER, true);
	$sql = sprintf("INSERT INTO errors (errno, errstr, errfile, errline, errcontext, userid, site) VALUES (%d, '%s', '%s', %d, '%s', %d, '%s')", $errno, mysql_real_escape_string($errstr), $errfile, $errline, mysql_real_escape_string($errCtx), 0, $_SERVER['SERVER_NAME']);
	$result = mysql_query($sql, $errorLink);
	if(!$result) {
		$errstr = $errstr . "\n Error inserting error into db: " . mysql_error($errorLink);
		emailError($errno, $errstr, $errfile, $errline, $errcontext);
	} else {
		$id = mysql_insert_id($errorLink);
		if($id % 11 == 1) {
			// get the last 10 errors.
			$sql = "SELECT * FROM errors WHERE id>" . ($id-10);
			$result = mysql_query($sql, $errorLink);
			$errors = array();
			while($row = mysql_fetch_assoc($result)) {
				$errors[] = $row;
			}
			emailErrors($errors);
		}
	}
	mysql_close($errorLink);
}


function emailErrors($errors) {
	$message = "10th Error happened at squash-player.com\n\n";
	$message .= "Time is: " . date("G:i:s e") . "\n";
	foreach($errors as $error) {
		$message .= "errno: " . $error['errno'] . "\n";
		$message .= "errstr: " . $error['errstr'] . "\n";
		$message .= "errfile: " . $error['errfile'] . "\n";
		$message .= "errline: " . $error['errline'] . "\n";
		$message .= "errcontext: " . $error['errcontext'] . "\n\n\n";
	}
	mail("zolika@zolilla.com", "10th Error on Maverick", $message);
}



function emailErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
	emailError($errno, $errstr, $errfile, $errline, $errcontext);
}

function emailError($errno, $errstr, $errfile, $errline, $errcontext) {
	$errCtx = "Request: \n";
	$errCtx .= print_r($_REQUEST, true);
	$errCtx .= "\n\nSession: ";
	$errCtx .= print_r($_SESSION, true);
	$message = "Error happened at Maverick.\n\n";
	$message .= "Time is: " . date("G:i:s e") . "\n";
	$message .= "request URI: " . $_SERVER['REQUEST_URI'] . "\n";
	$message .= "errno: " . $errno . "\n";
	$message .= "errstr: " . $errstr . "\n";
	$message .= "errfile: " . $errfile . "\n";
	$message .= "errline: " . $errline . "\n";
	$message .= "errcontext: $errCtx\n";
	mail("zolika@zolilla.com", "Error on Maverick", $message);
}

?>
