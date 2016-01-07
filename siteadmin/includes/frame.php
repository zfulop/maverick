<?php

function html_start($title = null, $extraHeader = '', $onloadScript = '') {
	$title = $_SESSION['login_hotel_name'] . ' - Website Admin - ' . $title;

	$index = ROOT_URL . 'index.php';
	$awards = ROOT_URL . 'view_awards.php';
	$roomImages = ROOT_URL . 'view_room_images.php';
	$siteTexts = ROOT_URL . 'view_site_text.php';

	$prototypeJs = ROOT_URL . 'js/prototype.js';
	$css = ROOT_URL . 'maverick-recepcio.css';

	echo <<<EOT
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="$css" rel="stylesheet" type="text/css"/>
	<title>$title</title>
        <script type="text/javascript" src="$prototypeJs"></script>

	$extraHeader

</head>

<body style="font-size: 12px; padding: 0px; margin: 0px 10px 10px 10px;" onload="hideSubMenu();sendHeartbeat();$onloadScript">

EOT;

	echo <<<EOT

<div style="position: fixed; width: 98%; height: 50px; background: white; margin: 0px; padding: 0px;">
	<div style="text-align: center; border-style: solid; height: 25px; position: relative; background: rgb(220, 220, 220);">
		<a href="$index" style="float: left; font-size: 14px; padding-left: 20px; padding-right: 20px;">Home</a>
		<a href="$awards" style="float: left; font-size: 14px; padding-left: 20px; padding-right: 20px;">Awards</a>
		<a href="$roomImages" style="float: left; font-size: 14px; padding-left: 20px; padding-right: 20px;">Room Images</a>
		<a href="$siteTexts" style="float: left; font-size: 14px; padding-left: 20px; padding-right: 20px;">Site Texts</a>

	</div>
</div>
<div style="height: 60px;">
</div>


EOT;

	echo <<<EOT
<h1>$title</h1>

EOT;
	$errors = get_errors();
	foreach($errors as $error) {
		echo "	<div style=\"background-color: #FF0000; margin: 10px;\">ERROR: $error</div>\n";
	}
	clear_errors();
	$warnings = get_warnings();
	foreach($warnings as $warning) {
		echo "	<div style=\"background-color: #FFFF00; margin: 10px;\">WARNING: $warning</div>\n";
	}
	clear_warnings();
	$messages = get_messages();
	foreach($messages as $msg) {
		echo "	<div style=\"background-color: #00FF00; margin: 10px;\">INFO: $msg</div>\n";
	}
	clear_messages();
	$debug = get_debug();
	foreach($debug as $msg) {
		echo "	<div style=\"background-color: #DDDDDD; margin: 10px;\">DEBUG: $msg</div>\n";
	}
	clear_debug();


}

function html_end() {
	echo <<<EOT

</body>
</html>

EOT;
}

?>
