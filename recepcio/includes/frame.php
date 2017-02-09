<?php

$incldeWzTooltip = true;

function html_start($title = null, $extraHeader = '', $showMenu = true, $onloadScript = '', $useBootstrap = false) {
	global $incldeWzTooltip;
	if($useBootstrap) {
		html_start_bootstrap($title, $extraHeader, $showMenu, $onloadScript);
		return;
	}

	$title = $_SESSION['login_hotel_name'] . ' - Recepcio - ' . $title;
	$loginName = $_SESSION['login_user'];

	$logout = ROOT_URL . 'logout.php';
	$changePassword = ROOT_URL . 'change_password.php';

	$actualities = ROOT_URL . 'index.php';
	$availability = ROOT_URL . 'view_availability.php';
	$dblRoomChange = ROOT_URL . 'view_dbl_room_changes.php';
	$booking = ROOT_URL . 'view_booking.php';
	$gtransfer = ROOT_URL . 'view_guest_transfer.php';
	$mendingList = ROOT_URL . 'view_mending_list.php';
	$shoppingList = ROOT_URL . 'view_shopping_list.php';
	$roomsToClean = ROOT_URL . 'view_rooms_to_clean.php';
	$vacations = ROOT_URL . 'view_vacations.php';
	$exchangeRates = ROOT_URL . 'view_exchange_rates.php';
	$serviceCharges = ROOT_URL . 'view_service_charges.php';
	$cashRegister = ROOT_URL . 'view_cash_register.php';
	$services = ROOT_URL . 'view_services.php';
	$specialOffers = ROOT_URL . 'view_special_offers.php';
	$siteTexts = ROOT_URL . 'view_sitetexts.php';
	$logout = ROOT_URL . 'logout.php';
	$blacklist = ROOT_URL . 'view_blacklist.php';
	$schedule = ROOT_URL . 'view_schedule.php';

	$tooltipJs = ROOT_URL . 'js/wz_tooltip.js';

	if($incldeWzTooltip) {
		$tooltipJsHtml = "<script type=\"text/javascript\" src=\"$tooltipJs\"></script>\n";
	} else {
		$tooltipJsHtml = '';
	}

	$prototypeJs = ROOT_URL . 'js/prototype.js';
	$css = ROOT_URL . 'maverick-recepcio.css';

	echo <<<EOT
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="$css" rel="stylesheet" type="text/css"/>
	<title>$title</title>
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
		
		function sendHeartbeat() {
			new Ajax.Request('/heartbeat.php', {
			});
			setTimeout(sendHeartbeat, 60000);
		}
	</script>

	$extraHeader

</head>

<body style="font-size: 12px; padding: 0px; margin: 0px 10px 10px 10px;" onload="hideSubMenu();sendHeartbeat();$onloadScript">

$tooltipJsHtml

EOT;

	if($showMenu) {
		$ex = getExchangeRate('EUR', 'HUF', date('Y-m-d'));
		echo <<<EOT

<div style="position: fixed; width: 98%; height: 50px; background: white; margin: 0px; padding: 0px;">
	<div style="text-align: right; height: 25px;">
		<span style="font-size: 14px; padding-right: 20px;">Current exchange rate: 1 EUR = $ex Ft</span>
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
				<li><a href="$vacations" style="font-size: 14px; padding-right: 20px;">Vacations</a></li>
				<li><a href="$mendingList" style="font-size: 14px; padding-right: 20px;">Mending List</a></li>
				<li><a href="$shoppingList" style="font-size: 14px; padding-right: 20px;">Shopping List</a></li>
				<li><a href="$blacklist" style="font-size: 14px; padding-right: 20px;">Blacklisted guests</a></li>
				<li><a href="$roomsToClean" style="font-size: 14px; padding-right: 20px;">Rooms to clean</a></li>
				<li><a href="$schedule" style="font-size: 14px; padding-right: 20px;">View reception/cleaner schedule</a></li>
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
				<li><a href="$siteTexts" style="font-size: 14px; padding-right: 20px;">Website Texts</a></li>
			</ul>
		</div>
		<div style="float: right;padding-right:50px;">
			<a href="#" style="float: left; font-size: 14px; padding-right: 20px;" id="accountMainMenu" onclick="showMenu('accountMenu', this);return false;">$loginName</a>
			<div id="accountMenu" class="submenu" onmouseleave="$(this).hide();">
				<ul>
					<li><a href="$logout" style="float: left; font-size: 14px; padding-right: 20px;">Logout</a></li>
					<li><a href="$changePassword" style="float: left; font-size: 14px; padding-right: 20px;">Change&nbsp;password</a></li>
				</ul>
			</div>
		</div>

	</div>
</div>
<div style="height: 60px;">
</div>


EOT;
	}

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

function html_start_bootstrap($title = null, $extraHeader = '', $showMenu = true, $onloadScript = '') {
	$title = $_SESSION['login_hotel_name'] . ' - Recepcio - ' . $title;
	$loginName = $_SESSION['login_user'];

	$logout = ROOT_URL . 'logout.php';
	$changePassword = ROOT_URL . 'change_password.php';

	$actualities = ROOT_URL . 'index.php';
	$availability = ROOT_URL . 'view_availability.php';
	$dblRoomChange = ROOT_URL . 'view_dbl_room_changes.php';
	$booking = ROOT_URL . 'view_booking.php';
	$gtransfer = ROOT_URL . 'view_guest_transfer.php';
	$schedule = ROOT_URL . 'view_schedule.php';
	$mendingList = ROOT_URL . 'view_mending_list.php';
	$shoppingList = ROOT_URL . 'view_shopping_list.php';
	$roomsToClean = ROOT_URL . 'view_rooms_to_clean.php';
	$vacations = ROOT_URL . 'view_vacations.php';
	$exchangeRates = ROOT_URL . 'view_exchange_rates.php';
	$serviceCharges = ROOT_URL . 'view_service_charges.php';
	$cashRegister = ROOT_URL . 'view_cash_register.php';
	$services = ROOT_URL . 'view_services.php';
	$specialOffers = ROOT_URL . 'view_special_offers.php';
	$logout = ROOT_URL . 'logout.php';
	$blacklist = ROOT_URL . 'view_blacklist.php';

	$prototypeJs = ROOT_URL . 'js/prototype.js';
	$css = ROOT_URL . 'maverick-recepcio.css';

	echo <<<EOT
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>$title</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">	
	<link href="css/bootstrap-theme.min.css" rel="stylesheet">	
    <script type="text/javascript" src="$prototypeJs"></script>
	<script type="text/javascript">
		function sendHeartbeat() {
			new Ajax.Request('/heartbeat.php', {
			});
			setTimeout(sendHeartbeat, 60000);
		}
	</script>

	$extraHeader
</head>

<body style="padding-top: 70px;" onload="sendHeartbeat();$onloadScript">

EOT;

	if($showMenu) {
		$ex = getExchangeRate('EUR', 'HUF', date('Y-m-d'));
		echo <<<EOT

<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container-fluid">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">RC</a>
		</div>

		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav">
				<li><a href="$actualities">HOME</a></li>			
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Availability</a>
					<ul class="dropdown-menu">
						<li><a href="$availability">Availability</a></li>
						<li><a href="$gtransfer">Guest transfer</a></li>
						<li><a href="$dblRoomChange">Multiple room changes for a day</a></li>
					</ul>
				</li>
				<li><a href="$booking">Booking</a></li>			
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Maintenance</a>
					<ul class="dropdown-menu">
						<li><a href="$schedule">Reception/Cleaning schedule</a></li>
						<li><a href="$vacations">Vacations</a></li>
						<li><a href="$mendingList">Mending List</a></li>
						<li><a href="$shoppingList">Shopping List</a></li>
						<li><a href="$blacklist">Blacklisted guests</a></li>
						<li><a href="$roomsToClean">Rooms to clean</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Money</a>
					<ul class="dropdown-menu">
						<li><a href="$exchangeRates">Exchange Rates</a></li>
						<li><a href="$serviceCharges">Service Charges</a></li>
						<li><a href="$cashRegister">Cash Register</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Website</a>
					<ul class="dropdown-menu">
						<li><a href="$specialOffers">Special Offers</a></li>
						<li><a href="$services">Services</a></li>
					</ul>
				</li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li>1 EUR = $ex Ft</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">$loginName</a>
					<ul class="dropdown-menu">
						<li><a href="$logout">Logout</a></li>
						<li><a href="$changePassword">Change Password</a></li>
					</ul>
				</li>
			</ul>
		</div><!-- /.navbar-collapse -->		
	</div> <!-- .container-fluid -->
</nav>


EOT;
	}

	echo <<<EOT
<h1>$title</h1>

EOT;
	$errors = get_errors();
	foreach($errors as $error) {
		echo "	<div class=\"alert alert-danger\" role=\"alert\">ERROR: $error</div>\n";
	}
	clear_errors();
	$warnings = get_warnings();
	foreach($warnings as $warning) {
		echo "	<div class=\"alert alert-warning\" role=\"alert\">WARNING: $warning</div>\n";
	}
	clear_warnings();
	$messages = get_messages();
	foreach($messages as $msg) {
		echo "	<div class=\"alert alert-info\" role=\"alert\">$msg</div>\n";
	}
	clear_messages();
	$debug = get_debug();
	foreach($debug as $msg) {
		echo "	<div class=\"alert alert-info\" role=\"alert\">DEBUG: $msg</div>\n";
	}
	clear_debug();


}

function html_end($useBootstrap = false) {
	if($useBootstrap) {
		html_end_bootstrap();
		return;
	}
	echo <<<EOT

</body>
</html>

EOT;
}

function html_end_bootstrap() {
	echo <<<EOT
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
EOT;
}

?>
