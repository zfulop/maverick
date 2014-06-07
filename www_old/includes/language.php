<?php

function getLanguages() {
	return array('eng' =>  'English', 'hun' => 'Hungarian');
}

function getCurrentLanguage() {
	$lang = substr($_SERVER['SCRIPT_NAME'], 1, 3);
	if(substr($lang, -1, 1) == '/') {
		$lang = substr($lang, 0 , 2);
		if($lang == 'en') $lang = 'eng';
	}
	return $lang;
}

function guessLanguage() {
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$reqLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$tokens = split(",", $reqLang);
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
			}
		}
	}
	return 'eng';
}

?>
