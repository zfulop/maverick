<?php

require("includes.php");

$link = db_connect();

header('Location: view_special_offers.php');

mysql_query("START TRANSACTION", $link);

$order = intval($_REQUEST['order']);

$sql = "UPDATE special_offers SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change special_offers orders in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new special offer');
	mysql_close($link);
	return;
}



$sql = "INSERT INTO special_offers (_order) VALUES ($order)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create special offers in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new special offer');
	mysql_close($link);
	return;
}

$rowId = mysql_insert_id($link);


foreach(getLanguages() as $lang => $name) {
	$title = $_REQUEST["title_$lang"];
	$text = $_REQUEST["text_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('special_offers', 'title', $rowId, '$lang', '$title')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot create special offers text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new special offer');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('special_offers', 'text', $rowId, '$lang', '$text')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot create special offers text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new special offer');
		mysql_close($link);
		return;
	}
}

set_message('New special offer saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
