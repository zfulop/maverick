<?php

require("includes.php");
require("cashregister_common.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}


$link = db_connect();

$lastDayClose = null;
$lastDayCloseTime = date('Y-m-d');
$sql = "SELECT * FROM day_close ORDER BY time_of_day_close DESC LIMIT 1";
$result = mysql_query($sql, $link);
$dayCloseHuf = 0;
$dayCloseEur = 0;
$dayCloseHuf2 = 0;
$dayCloseEur2 = 0;
if(!$result) {
	trigger_error("Cannot get last day close: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} elseif(mysql_num_rows($result) > 0) {
	$lastDayClose = mysql_fetch_assoc($result);
	$lastDayCloseTime = $lastDayClose['time_of_day_close'];
	$dayCloseEur = $lastDayClose['casseEUR'];
	$dayCloseHuf = $lastDayClose['casseHUF'];
	$dayCloseHuf2 = $lastDayClose['casseHUF2'];
	$dayCloseEur2 = $lastDayClose['casseEUR2'];
}

list($payments, $cashOuts, $gtransfers, $eurCasse, $hufCasse, $eurCasse2, $hufCasse2) = loadCashRegisterData(null, $lastDayCloseTime, $dayCloseEur, $dayCloseHuf, $dayCloseHuf2, $dayCloseEur2, $link);

if(intval($hufCasse) != intval($_REQUEST['casseHUF'])) {
	logError("HUF value changed, please resubmit the day close. submitted: " . $_REQUEST['casseHUF'] . " calculated: $hufCasse lastDayCloseTime=$lastDayCloseTime");
	set_error("HUF value changed, please resubmit the day close.");
	header("Location: view_cash_register.php");
	mysql_close($link);
	return;
}
if(intval($eurCasse) != intval($_REQUEST['casseEUR'])) {
	set_error("EUR value changed, please resubmit the day close");
	logError("EUR value changed, please resubmit the day close. submitted: " . $_REQUEST['casseEUR'] . " calculated: $eurCasse lastDayCloseTime=$lastDayCloseTime");
	header("Location: view_cash_register.php");
	mysql_close($link);
	return;
}
if(intval($hufCasse2) != intval($_REQUEST['casseHUF2'])) {
	set_error("HUF2 value changed, please resubmit the day close");
	logError("HUF2 value changed, please resubmit the day close. submitted: " . $_REQUEST['casseHUF2'] . " calculated: $hufCasse2 lastDayCloseTime=$lastDayCloseTime");
	header("Location: view_cash_register.php");
	mysql_close($link);
	return;
}
if(intval($eurCasse2) != intval($_REQUEST['casseEUR2'])) {
	set_error("EUR2 value changed, please resubmit the day close");
	logError("EUR value changed, please resubmit the day close. submitted: " . $_REQUEST['casseEUR2'] . " calculated: $eurCasse2 lastDayCloseTime=$lastDayCloseTime");
	header("Location: view_cash_register.php");
	mysql_close($link);
	return;
}



$timeOfDayClose = date('Y-m-d H:i:s');
$hufCasse = $_REQUEST['casseHUF'];
$eurCasse = $_REQUEST['casseEUR'];
$hufCasse2 = $_REQUEST['casseHUF2'];
$eurCasse2 = $_REQUEST['casseEUR2'];
$fromLogin = $_SESSION['login_user'];
$toLogin = $_REQUEST['to_login'];
$toPwd = $_REQUEST['to_pwd'];
$comment = $_REQUEST['comment'];



if(!doLogin($toLogin,$toPwd,$_SESSION['login_hotel'])) {
	set_error("Cannot authenticate 'to' receptionist");
	header("Location: view_cash_register.php");
	mysql_close($link);
	return;
}


$sql = "INSERT INTO day_close (`from`, `to`, time_of_day_close, casseHUF, casseEUR, casseHUF2, casseEUR2, comment) VALUES ('$fromLogin', '$toLogin', '$timeOfDayClose', $hufCasse, $eurCasse,  $hufCasse2, $eurCasse2,  '$comment')";

if(!mysql_query($sql, $link)) {
	trigger_error("Could not save day close: " . mysql_error($link) . " (SQL: $sql)");
	set_error("Could not save day close");
	header("Location: view_cash_register.php");
} else {
	set_message("Day close entry saved.");
	$data = array('from' => $fromLogin, 'to' => $toLogin, 'casseHUF' => $hufCasse, 'casseEUR' => $eurCasse, 'commnet' => $comment);
	audit(AUDIT_SAVE_DAY_CLOSE, $_REQUEST, 0, 0, $link);
	header("Location: logout.php");
}

mysql_close($link);



?>