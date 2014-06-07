<?php

require('includes.php');

if(isClientFromHU()) {
	header("Location: en/index.php");
} else {
	$lang = guessLanguage();
	header("Location: $lang/index.php");
}

?>
