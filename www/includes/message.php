<?php

define('DEBUG', true);


function set_error($msg) {
	if(!isset($_SESSION["error"]))
		$_SESSION["error"] = array();

	$_SESSION["error"][] = $msg;
}


function set_message($msg) {
	if(!isset($_SESSION["message"]))
		$_SESSION["message"] = array();

	$_SESSION["message"][] = $msg;
}

function set_debug($msg) {
	if(!DEBUG)
		return;

	if(!isset($_SESSION["debug"]))
		$_SESSION["debug"] = array();

	$_SESSION["debug"][] = $msg;
}

function get_messages() {
	if(isset($_SESSION["message"]))
		return $_SESSION["message"];
	else
		return array();
}

function get_errors() {
	if(isset($_SESSION["error"]))
		return $_SESSION["error"];
	else
		return array();
}

function get_debugs() {
	if(isset($_SESSION["debug"]))
		return $_SESSION["debug"];
	else
		return array();
}


function clear_messages() {
	unset($_SESSION["message"]);
}

function clear_errors() {
	unset($_SESSION["error"]);
}


function clear_debugs() {
	unset($_SESSION["debug"]);
}


?>
