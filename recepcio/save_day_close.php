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
$fromLogin = $_SERVER['PHP_AUTH_USER'];
$toLogin = $_REQUEST['to_login'];
$toPwd = $_REQUEST['to_pwd'];
$comment = $_REQUEST['comment'];


if(!http_authenticate($toLogin,$toPwd)) {
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


function http_authenticate($user,$pass,$pass_file='.htpasswd',$crypt_type='DES'){
	$fp = fopen($pass_file, 'r');
	if(!$fp) {
		trigger_error("Cannot open password file: $pass_file");
		return false;
	}

	while($line=fgets($fp)){
		// for each line in the file remove line endings
		$line=preg_replace('`[\r\n]$`','',$line);
		list($fuser,$fpass)=explode(':',$line);
		if($fuser!=$user){
			continue;
		}

		// the submitted user name matches this line in the file
		switch($crypt_type){
			case 'DES':
				// the salt is the first 2 characters for DES encryption
				$salt=substr($fpass,0,2);
				// use the salt to encode the submitted password
				$test_pw=crypt($pass,$fpass);
				break;
			case 'PLAIN':
				$test_pw=$pass;
				break;
			case 'SHA':
				case 'MD5':
				default:
					// unsupported crypt type
					fclose($fp);
					trigger_error("Unsupported crypt type: $crypt_type");
					return false;
		}
		if($test_pw == $fpass){
			// authentication success.
			fclose($fp);
			return true;
		}
		set_error("$test_pw is not equal to saved $fpass");

	} // end of while loop

	return false;
	fclose($fp);

}



?>
