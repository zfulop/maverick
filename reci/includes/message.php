<?php

define('DEBUG', false);

function set_error($msg) {
	$_SESSION['error'][] = $msg;
}

function set_warning($msg) {
	$_SESSION['warning'][] = $msg;
}

function set_message($msg) {
	$_SESSION['message'][] = $msg;
}

function logDebug($msg) {
	set_debug($msg);
}

function logError($msg) {
	set_error($msg);
}

function set_debug($msg) {
	if(!DEBUG)
		return;

	$_SESSION['debug'][] = $msg;
}


function get_errors() {
	if(isset($_SESSION['error']) and is_array($_SESSION['error'])) {
		return $_SESSION['error'];
	} else {
		return array();
	}
}

function get_warnings() {
	if(isset($_SESSION['warning']) and is_array($_SESSION['warning'])) {
		return $_SESSION['warning'];
	} else {
		return array();
	}
}

function get_messages() {
	if(isset($_SESSION['message']) and is_array($_SESSION['message'])) {
		return $_SESSION['message'];
	} else {
		return array();
	}
}

function get_debug() {
	if(isset($_SESSION['debug'])) {
		return $_SESSION['debug'];
	} else {
		return array();
	}
}


function clear_errors() {
	$_SESSION['error'] = array();
}

function clear_warnings() {
	$_SESSION['warning'] = array();
}

function clear_messages() {
	$_SESSION['message'] = array();
}

function clear_debug() {
	$_SESSION['debug'] = array();
}
