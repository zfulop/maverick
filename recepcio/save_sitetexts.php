<?php


require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}

$link = db_connect();

$sql = array();
foreach($_REQUEST as $key => $value) {
	$value = mysql_real_escape_string($value, $link);	
	if('WEBSITETEXT_' == substr($key, 0, strlen('WEBSITETEXT_'))) {
		$textKey = substr($key, strlen('WEBSITETEXT_'), -4);
		$textLang = substr($key, -3);
		logDebug("For $textKey ($textLang) the value is $value");
		$sql[] = "DELETE FROM lang_text WHERE table_name='website' AND column_name='$textKey' AND lang='$textLang'";
		$sql[] = "INSERT INTO lang_text (table_name, column_name,value,lang,row_id) VALUES ('website','$textKey','$value','$textLang',0)";
	}
}

$error = 0;
foreach($sql as $s) {
	logDebug("Executing: $s");
	if(!mysql_query($s, $link)) {
		trigger_error("Cannot get website texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		$error += 1;
	}
}

if($error > 0) {
	set_error("There were $error errors when saving the texts");
} else {
	set_message("All the texts (" . count($sql) . ") were saved");
}

mysql_close($link);

header("Location: view_sitetexts.php");


?>
