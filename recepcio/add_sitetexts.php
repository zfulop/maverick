<?php


require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();


$today = date('Y-m-d');

$texts = explode(' ', $_REQUEST['keys']);

$alreadyInDB = array();
$sql = "SELECT distinct column_name FROM lang_text WHERE table_name='website' AND column_name IN ('" . implode("','", $texts) . "')";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get website texts in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$alreadyInDB[] = $row['column_name'];
	}
}

foreach($texts as $key) {
	if(in_array($key, $alreadyInDB)) {
		set_error("$key is already in the db");
	} elseif(strlen($key)>0) {
		$sql = "INSERT INTO lang_text (table_name,column_name,value,lang,row_id) VALUES ('website','$key','','eng',0)";
		$result = mysql_query($sql, $link);
		if(!$result) {
			trigger_error("Cannot save text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
			set_error("Cannot save $key in the DB.");
		} else {
			set_message("$key saved");
		}
	}
}


mysql_close($link);

header("Location: view_sitetexts.php");


?>
