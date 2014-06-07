<?php

require("includes.php");

$link = db_connect();

header('Location: view_videos.php');

$order = $_REQUEST['order'];

$sql = "UPDATE videos SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change videos orders in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new video');
	mysql_close($link);
	return;
}


$vId = intval($_REQUEST['id']);
if($vId > 0) {
	$sql = "UPDATE videos SET _order=$order WHERE id=$vId";
} else {
	$sql = "INSERT INTO videos (_order) VALUES ($order)";
}
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot save video in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save video');
	mysql_close($link);
	return;
}

$inserted = false;
if($vId < 1) {
	$inserted = true;
	$vId = mysql_insert_id($link);
}

$sql = "DELETE FROM lang_text WHERE table_name='videos' AND row_id=$vId";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save video text in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save video');
	mysql_close($link);
	return;
}

foreach(getLanguages() as $lang => $name) {
	$title = $_REQUEST["title_$lang"];
	$html = $_REQUEST["html_$lang"];
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('videos', 'title', $vId, '$lang', '$title')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save video title in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error("Cannot save video title ($lang)");
	}
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('videos', 'html', $vId, '$lang', '$html')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save video html in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error("Cannot video html ($lang)");
	}
}

set_message('Video saved');
mysql_query("COMMIT", $link);
mysql_close($link);

?>
