<?php

$incldeWzTooltip = true;

function html_start($title = "Maverick Reception", $extraHeader = '', $showMenu = true, $onloadScript = '') {
	global $incldeWzTooltip;

	$logout = ROOT_URL . 'logout.php';
	$actualities = ROOT_URL . 'index.php';
	$booking = ROOT_URL . 'view_booking.php';
	$exchangeRates = ROOT_URL . 'view_exchange_rates.php';
	$cashRegister = ROOT_URL . 'view_cash_register.php';

	$tooltipJs = ROOT_URL . 'js/wz_tooltip.js';

	if($incldeWzTooltip) {
		$tooltipJsHtml = "<script type=\"text/javascript\" src=\"$tooltipJs\"></script>\n";
	} else {
		$tooltipJsHtml = '';
	}

	$prototypeJs = ROOT_URL . 'js/prototype.js';
	$css = ROOT_URL . 'maverick-recepcio.css';
	$location = strtoupper(LOCATION);

	echo <<<EOT
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="$css" rel="stylesheet" type="text/css"/>
	<title>$title [$location]</title>
    <script type="text/javascript" src="$prototypeJs"></script>
	<script type="text/javascript">
		function showMenu(menuId, hrefEl) {
			$$('div.submenu').each(function(sm) {
				sm.hide();
			});
			if(menuId != '') {
				$(menuId).setStyle( {
					left: hrefEl.offsetLeft,
					display: 'block'
				});
			}
		}

		function hideSubMenu() {
			$$('div.submenu').each(function(sm) {
				sm.hide();
			});
		}
	</script>

	$extraHeader

</head>

<body style="font-size: 12px; padding: 0px; margin: 0px 10px 10px 10px;" onload="hideSubMenu();$onloadScript">

$tooltipJsHtml

EOT;

	if($showMenu) {
		$myself = $_SERVER['REMOTE_USER'];
		$ex = getExchangeRate('EUR', 'HUF', date('Y-m-d'));
		echo <<<EOT

<div style="position: fixed; width: 98%; height: 50px; background: white; margin: 0px; padding: 0px;">
	<div style="text-align: right; height: 25px;">
		<span style="font-size: 14px; padding-right: 20px;">Current exchange rate: 1 EUR = $ex Ft</span>
		Logged in as $myself, <a href="$logout" style="font-size: 14px; padding-right: 20px; font-weight: bold;">LOGOUT</a>
	</div>
	<div style="text-align: center; border-style: solid; height: 25px; position: relative; background: rgb(220, 220, 220);">
		<a href="$actualities" style="float: left; font-size: 14px; padding-left: 20px; padding-right: 20px;">Availability</a>
		<a href="$booking" style="float: left; font-size: 14px; padding-right: 20px;">Booking</a>
		<a href="$cashRegister" style="float: left; font-size: 14px; padding-right: 20px;">Cash Register</a></li>
	</div>
</div>
<div style="height: 60px;">
</div>


EOT;
	}

	echo <<<EOT
<h1>$title [$location]</h1>

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
