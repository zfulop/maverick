<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



$link = db_connect();

header('Location: view_services.php');

mysql_query("START TRANSACTION", $link);

$id = intval($_REQUEST['id']);
$order = intval($_REQUEST['order']);
if($order < 1) $order = 1;
$free = isset($_REQUEST['free']) ? 1 : 0;
$name = mysql_real_escape_string($_REQUEST['title_eng'], $link);
$description = mysql_real_escape_string($_REQUEST['description_eng'], $link);
$currency = $_REQUEST['currency'];
$price = $_REQUEST['price'];
$serviceChargeType = $_REQUEST['service_charge_type'];

if(strlen($price) < 1) {
	$price = 0;
}

$img = false;
if(isset($_FILES['img'])) {
	$img = saveUploadedImage('img', SERVICES_IMG_DIR, 400, 400, false);
}

if($img)
	$img = "'" . basename($img) . "'";
else
	$img = 'NULL';



$sql = "UPDATE services SET _order=_order+1 WHERE _order>=$order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot change services in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save service');
	mysql_close($link);
	return;
}


if($id < 1) {
	$sql = "INSERT INTO services (_order,free_service,currency,price,name,description,service_charge_type,img) VALUES ($order, $free,'$currency',$price,'$name','$description','$serviceChargeType',$img)";
} else {
	$sql = "UPDATE services SET _order=$order,free_service=$free,currency='$currency',price=$price,name='$name',description='$description',service_charge_type='$serviceChargeType'";
	if($img != 'NULL') {
		$sql .= ", img=$img";
	}
	$sql .= " WHERE id=$id";
}

$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot create service in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save new service');
	mysql_close($link);
	return;
}

$inserted = false;
if($id < 1) {
	$inserted = true;
	$id = mysql_insert_id($link);
}

$sql = "DELETE FROM lang_text WHERE table_name='services' AND row_id=$id";
if(!mysql_query($sql, $link)) {
	trigger_error("Cannot save service's title in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	set_error('Cannot save service');
	mysql_close($link);
	return;
}

foreach(getLanguages() as $lang => $name) {
	saveLangText('title',$lang, $id, $link);
	saveLangText('description',$lang, $id, $link);
	saveLangText('unit_name',$lang, $id, $link);
}

set_message('Service saved');
mysql_query("COMMIT", $link);
mysql_close($link);

$dir = JSON_DIR . getLoginHotel();
logDebug("Deleting extracted service info from folder: $dir");

$files = glob($dir . '/services*');
foreach($files as $file) {
	logDebug("\t$file");
	unlink($file);
}

set_message("Extracted files containing service data removed");


function saveLangText($paramName,$lang, $id, $link) {
	$value = mysql_real_escape_string($_REQUEST[$paramName . '_' . $lang], $link);
	$sql = "INSERT INTO lang_text (table_name, column_name, row_id, lang, value) VALUES ('services', '$paramName', $id, '$lang', '$value')";
	if(!mysql_query($sql, $link)) {
		trigger_error("Cannot save service $paramName in recepcion interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
		set_error('Cannot save service');
		mysql_close($link);
		return;
	}
}


?>
