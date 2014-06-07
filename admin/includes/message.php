<?php

function set_error($msg) {
	$_SESSION['error'][] = $msg;
}

function set_message($msg) {
	$_SESSION['message'][] = $msg;
}

function get_errors() {
	if(isset($_SESSION['error'])) {
		return $_SESSION['error'];
	} else {
		return array();
	}
}

function get_messages() {
	if(isset($_SESSION['message'])) {
		return $_SESSION['message'];
	} else {
		return array();
	}
}

function clear_errors() {
	$_SESSION['error'] = array();
}

function clear_messages() {
	$_SESSION['message'] = array();
}