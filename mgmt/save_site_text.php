<?php

require("includes.php");
require("site_text_common.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}



$langTexts = loadConstants();
foreach($_REQUEST as $key => $value) {
	if(strpos($key, '-') != 3) {
		continue;
	}
	list($langCode, $constName) = explode('-', $key);
	$value = str_replace("\r\n", "<br>", $value);
	$value = str_replace("\n", "<br>", $value);
	$value = str_replace("\\\"", "\"", $value);
	$value = str_replace("\\'", "'", $value);
	$value = str_replace("'", "\\'", $value);
	$langTexts[$constName][$langCode] = $value;
}

foreach(getLanguages() as $langCode => $langName) {
	$saveFile = getNextFileName(LANG_DIR, $langCode);
	if(file_exists(LANG_DIR . $langCode . '.php')) {
		set_message("Backup existing file to $saveFile");
		copy(LANG_DIR . $langCode . '.php', $saveFile);
	}
	$fh = fopen(LANG_DIR . $langCode . '.php', "w");
	fwrite($fh, "<?php\n\n");
	$cntr = 0;
	foreach($langTexts as $constName => $texts) {
		if(!isset($texts[$langCode])) {
			continue;
		}
		$value = $texts[$langCode];
		fwrite($fh, "define('$constName', '$value');\n");
		$cntr += 1;
	}
	fwrite($fh, "\n\n?>");
	fclose($fh);
	set_message("Saved new content ($cntr entries) to file: " . LANG_DIR . $langCode . '.php');
}

header('Location: view_site_text.php');

function getNextFileName($dir, $langCode) {
	$cntr = 0;
	while(file_exists($dir . $langCode . $cntr . '.php')) {
		$cntr += 1;
	}
	return $dir . $langCode . $cntr . '.php';
}

?>
