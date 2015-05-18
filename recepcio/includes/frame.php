<?php

$incldeWzTooltip = true;

function html_start($title = "Maverick Reception", $extraHeader = '', $showMenu = true, $onloadScript = '') {
	global $incldeWzTooltip;

	$actualities = ROOT_URL . 'index.php';
	$availability = ROOT_URL . 'view_availability.php';
	$dblRoomChange = ROOT_URL . 'view_dbl_room_changes.php';
	$booking = ROOT_URL . 'view_booking.php';
	$gtransfer = ROOT_URL . 'view_guest_transfer.php';
	$schedule = ROOT_URL . 'view_schedule.php';
	$mendingList = ROOT_URL . 'view_mending_list.php';
	$shoppingList = ROOT_URL . 'view_shopping_list.php';
	$vacations = ROOT_URL . 'view_vacations.php';
	$exchangeRates = ROOT_URL . 'view_exchange_rates.php';
	$serviceCharges = ROOT_URL . 'view_service_charges.php';
	$cashRegister = ROOT_URL . 'view_cash_register.php';
	$services = ROOT_URL . 'view_services.php';
	$specialOffers = ROOT_URL . 'view_special_offers.php';
	$awards = ROOT_URL . 'view_awards.php';
	$haveFun = ROOT_URL . 'view_have_fun.php';
	$bonApetit = ROOT_URL . 'view_bon_apetit.php';
	$links = ROOT_URL . 'view_links.php';
	$logout = ROOT_URL . 'logout.php';

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
		<a href="$actualities" style="float: left; font-size: 14px; padding-left: 20px; padding-right: 20px;">TODAY</a>
		<a href="#" style="float: left; font-size: 14px; padding-right: 20px;" id="availMainMenu" onclick="showMenu('availMenu', this);return false;">Availability</a>
		<div id="availMenu" class="submenu" onmouseleave="$(this).hide();">
			<ul>
				<li><a href="$availability" style="font-size: 14px; padding-right: 20px;">Availability</a></li>
				<li><a href="$gtransfer" style="font-size: 14px; padding-right: 20px;">Guest transfer</a></li>
				<li><a href="$dblRoomChange" style="font-size: 14px; padding-right: 20px;">Multiple room changes for a day</a></li>
			</ul>
		</div>
		<a href="$booking" style="float: left; font-size: 14px; padding-right: 20px;">Booking</a>
		<a href="#" style="float: left; font-size: 14px; padding-right: 20px;" id="maintenanceMainMenu" onclick="showMenu('maintenanceMenu', this);return false;">Maintenance</a>
		<div id="maintenanceMenu" class="submenu" onmouseleave="$(this).hide();">
			<ul style="list-style: none;">
				<li><a href="$schedule" style="font-size: 14px; padding-right: 20px;">Reception/Cleaning schedule</a></li>
				<li><a href="$vacations" style="font-size: 14px; padding-right: 20px;">Vacations</a></li>
				<li><a href="$mendingList" style="font-size: 14px; padding-right: 20px;">Mending List</a></li>
				<li><a href="$shoppingList" style="font-size: 14px; padding-right: 20px;">Shopping List</a></li>
			</ul>
		</div>
		<a href="#" style="float: left; font-size: 14px; padding-right: 20px;" id="moneyMainMenu" onclick="showMenu('moneyMenu', this);return false;">Money</a>
		<div id="moneyMenu" class="submenu" onmouseleave="$(this).hide();">
			<ul style="list-style: none;">
				<li><a href="$exchangeRates" style="font-size: 14px; padding-right: 20px;">Exchange Rates</a></li>
				<li><a href="$serviceCharges" style="font-size: 14px; padding-right: 20px;">Service Charges</a></li>
				<li><a href="$cashRegister" style="font-size: 14px; padding-right: 20px;">Cash Register</a></li>
			</ul>
		</div>
		<a href="#" style="float: left; font-size: 14px; padding-right: 20px;" id="webMainMenu" onclick="showMenu('webMenu', this);return false;">Website</a>
		<div id="webMenu" class="submenu" onmouseleave="$(this).hide();">
			<ul style="list-style: none;">
				<li><a href="$specialOffers" style="font-size: 14px; padding-right: 20px;">Special Offers</a></li>
				<li><a href="$services" style="font-size: 14px; padding-right: 20px;">Services</a></li>
				<li><a href="$awards" style="font-size: 14px; padding-right: 20px;">Awards</a></li>
				<li><a href="$haveFun" style="font-size: 14px; padding-right: 20px;">Have Fun</a></li>
				<li><a href="$bonApetit" style="font-size: 14px; padding-right: 20px;">Bon Apetit</a></li>
				<li><a href="$links" style="font-size: 14px; padding-right: 20px;">Links</a></li>
			</ul>
		</div>
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
