<?php

function getLanguages() {
	return array(
		'eng' => 'English', 
		'deu' => 'Deutsch',
		'fra' => 'Francais',
		'ita' => 'Italiano',
		'esp' => 'Espagnol'/*,
		'por' => 'Portugal'*/);
}

function getCurrentLanguage() {
	$lang = $_SESSION['language'];
	return $lang;
}

function guessLanguage() {
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$reqLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$tokens = explode(",", $reqLang);
		foreach($tokens as $token) {
			$token = substr($token, 0, 2);
			if($token == "en") {
				return "eng";
			} elseif($token == "fr") {
				return "fra";
			} elseif($token == "de") {
				return "deu";
			} elseif($token == "sp") {
				return "esp";
			} elseif($token == "it") {
				return "ita";
			}
		}
	}
	return 'eng';
}


function initLanguage() {
	if(isset($_REQUEST['language'])) {
		$_SESSION['language'] = $_REQUEST['language'];
	}
	if(!isset($_SESSION['language'])) {
		$_SESSION['language'] = guessLanguage();
	}

	require('language/' . $_SESSION['language'] . '.php');
	setlocale(LC_ALL, LOCALE);
}

?>
