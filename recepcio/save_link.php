<?php

require("includes.php");

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);

header('Location: view_links.php');

$url = $_REQUEST['url'];
$order = intval($_REQUEST['order']);
if($order < 1) $order = 1;


mysql_query("START TRANSACTION", $link);

$sql = "UPDATE links SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change links orders in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new URL');
	mysql_close($link);
	return;
}


$linkId = intval($_REQUEST['id']);
if($linkId > 0) {
	$sql = "UPDATE links SET url='$url', _order=$order WHERE id=$linkId";
} else {
	$sql = "INSERT INTO links (url, _order) VALUES ('$url', $order)";
}
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save link: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save link');
	mysql_close($link);
	return;
}

$inserted = false;
if($linkId < 1) {
	$inserted = true;
	$linkId = mysql_insert_id($link);
}

$sql = "DELETE FROM lang_text WHERE table_name='links' AND row_id=$linkId";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save link text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save link');
	mysql_close($link);
	return;
}

foreach(getLanguages() as $lang => $name) {
	$name = $_REQUEST["name_$lang"];
	$description = $_REQUEST["description_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('links', 'name', $linkId, '$lang', '$name')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save link name in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save link');
		mysql_close($link);
		return;
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('links', 'description', $linkId, '$lang', '$description')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save link description in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save link');
		mysql_close($link);
		return;
	}
}

set_message('Link saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
