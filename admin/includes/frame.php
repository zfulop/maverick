<?php

function html_start($title = "Maverick Admin", $extraHeader = '', $onloadScript = '', $onresizeScript = '') {

	$audit = ADMIN_ROOT_URL . 'view_audit.php';
	$stats = ADMIN_ROOT_URL . 'view_statistics.php';
	$occupancy = ADMIN_ROOT_URL . 'view_occupancy.php';
	$pricing = ADMIN_ROOT_URL . 'view_pricing.php';
	$report = ADMIN_ROOT_URL . 'view_payment_report.php';

	$tooltipJs = ADMIN_ROOT_URL . 'js/wz_tooltip.js';

	echo <<<EOT
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="maverick-admin.css" rel="stylesheet" type="text/css"/>
	<title>$title</title>

	$extraHeader

</head>

<body style="font-size: 12px;" onload="$onloadScript" onresize="$onresizeScript">

<script type="text/javascript" src="$tooltipJs"></script>

<div style="text-align: center; border-style: solid; height: 25px; padding-left: 15px;">
<a href="$audit" style="float: left; font-size: 14px; padding-right: 20px;">Audit</a>
<a href="$stats" style="float: left; font-size: 14px; padding-right: 20px;">Statistics</a>
<a href="$occupancy" style="float: left; font-size: 14px; padding-right: 20px;">Occupancy</a>
<a href="$pricing" style="float: left; font-size: 14px; padding-right: 20px;">Pricing</a>
<a href="$report" style="float: left; font-size: 14px; padding-right: 20px;">Payments</a>
</div>

<h1>$title</h1>

EOT;
	$errors = get_errors();
	foreach($errors as $error) {
		echo "	<div style=\"background-color: #FF0000; margin: 10px;\">ERROR: $error</div>\n";
	}
	clear_errors();
	$messages = get_messages();
	foreach($messages as $msg) {
		echo "	<div style=\"background-color: #00FF00; margin: 10px;\">INFO: $msg</div>\n";
	}
	clear_messages();

}

function html_end() {
	echo <<<EOT

</body>
</html>

EOT;
}

?>
