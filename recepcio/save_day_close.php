<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
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
	return;
}


$link = db_connect();

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