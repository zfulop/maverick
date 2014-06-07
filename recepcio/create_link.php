<?php

require("includes.php");

$link = db_connect();

header('Location: view_links.php');

$url = $_REQUEST['url'];
$order = $_REQUEST['order'];

mysql_query("START TRANSACTION", $link);

$sql = "UPDATE links SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change links orders in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new URL');
	mysql_close($link);
	return;
}



$sql = "INSERT INTO links (url, _order) VALUES ('$url', $order)";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create link in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new URL');
	mysql_close($link);
	return;
}

$rowId = mysql_insert_id($link);


foreach(getLanguages() as $lang => $name) {
	$name = $_REQUEST["name_$lang"];
	$description = $_REQUEST["description_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('links', 'name', $rowId, '$lang', '$name')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Can create link text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new link');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('links', 'description', $rowId, '$lang', '$description')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot create link text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save new link');
		mysql_close($link);
		return;
	}
}

set_message('New link saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
