<?php

function getLanguages() {
	return array(
		'eng' =>  'English', 
		'deu' => 'German',
		'fra' => 'French',
		'esp' => 'Spanish',
		'por' => 'Portugese',
		'ita' => 'Italian');
}

function stripAccents($str) {
	$str = utf8_decode($str);
	$str = strtr($str,	
		utf8_decode("()!$'?: ,&+-/.ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØŐŰÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöőøùúûüűýÿ"),
			    "______________SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOOUUUUUYsaaaaaaaceeeeiiiionooooooouuuuuyy");

	$str = utf8_encode($str);
	return $str;
}


?>
