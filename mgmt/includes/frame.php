<?php

$incldeWzTooltip = true;

function html_start($title = null, $extraHeader = '', $showMenu = true, $onloadScript = '') {
	global $incldeWzTooltip;
	$title = $_SESSION['login_hotel_name'] . ' - Mgmt - ' . $title;
	$loginName = $_SESSION['login_user'];

	$logout = ROOT_URL . 'logout.php';
	$changePassword = ROOT_URL . 'change_password.php';
	$index = ROOT_URL . 'index.php';
	$users = ROOT_URL . 'view_users.php';
	$report = ROOT_URL . 'view_money_report.php';
	$cashBookings = ROOT_URL . 'view_cash_bookings.php';
	$shifts = ROOT_URL . 'view_shifts.php';
	$vacations = ROOT_URL . 'view_vacations.php';
	$lists = ROOT_URL . 'view_lists.php';
	$minMax = ROOT_URL . 'view_min_max_stay.php';
	$photos = ROOT_URL . 'view_room_images.php';
	$rooms = ROOT_URL . 'view_rooms.php';
	$texts = ROOT_URL . 'view_site_text.php';
	$audit = ROOT_URL . 'view_audit.php';
	$pricing = ROOT_URL . 'view_pricing.php';
	$schedule = ROOT_URL . 'view_schedule.php';

	$tooltipJs = ROOT_URL . 'js/wz_tooltip.js';

	if($incldeWzTooltip) {
		$tooltipJsHtml = "<script type=\"text/javascript\" src=\"$tooltipJs\"></script>\n";
	} else {
		$tooltipJsHtml = '';
	}

	$prototypeJs = ROOT_URL . 'js/prototype.js';
	$css = ROOT_URL . 'maverick-mgmt.css';

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

		function toggleDaySelection(checkbox) {
			$$('input.dayselect').each(function(chkBx) {
				chkBx.checked = checkbox.checked;
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
		echo <<<EOT

	<div style="text-align: center; border-style: solid; height: 25px; position: relative; background: rgb(220, 220, 220);">
		<a href="$index" style="float: left; font-size: 14px; padding-left: 20px; padding-right: 20px;">Home</a>
		<a href="#" style="float: left; font-size: 14px; padding-right: 20px;" id="employeeMainMenu" onclick="showMenu('employeeMenu', this);return false;">Employees</a>
		<div id="employeeMenu" class="submenu" onmouseleave="$(this).hide();">
			<ul>
				<li><a href="$users" style="float: left; font-size: 14px; padding-right: 20px;">Users</a></li>
				<li><a href="$shifts" style="clear: both; float: left; font-size: 14px; padding-right: 20px;">Work Shifts</a></li>
				<li><a href="$vacations" style="clear: both; float: left; font-size: 14px; padding-right: 20px;">Vacations</a></li>
				<li><a href="$schedule" style="float: left; font-size: 14px; padding-right: 20px;">Reception / Cleaner schedule</a></li>
			</ul>
		</div>
		<a href="#" style="float: left; font-size: 14px; padding-right: 20px;" id="roomMainMenu" onclick="showMenu('roomMenu', this);return false;">Rooms</a>
		<div id="roomMenu" class="submenu" onmouseleave="$(this).hide();">
			<ul>
				<li><a href="$rooms" style="float: left; font-size: 14px; padding-right: 20px;">Rooms/Room Types</a></li>
				<li><a href="$pricing" style="float: left; font-size: 14px; padding-right: 20px;">View pricing</a></li>
			</ul>
		</div>
		<a href="#" style="float: left; font-size: 14px; padding-right: 20px;" id="moneyMainMenu" onclick="showMenu('moneyMenu', this);return false;">Money</a>
		<div id="moneyMenu" class="submenu" onmouseleave="$(this).hide();">
			<ul>
				<li><a href="$report" style="float: left; font-size: 14px; padding-right: 20px;">Money Report</a></li>
				<li><a href="$cashBookings" style="float: left; font-size: 14px; padding-right: 20px;">Booking cash payment</a></li>
			</ul>
		</div>
		<a href="#" style="float: left; font-size: 14px; padding-right: 20px;" id="miscMainMenu" onclick="showMenu('miscMenu', this);return false;">Miscelanious</a>
		<div id="miscMenu" class="submenu" onmouseleave="$(this).hide();">
			<ul>
				<li><a href="$lists" style="float: left; font-size: 14px; padding-right: 20px;">Lists</a><br></li>
				<li><a href="$audit" style="float: left; font-size: 14px; padding-right: 20px;">Audit</a></li>
				<li><a href="$minMax" style="float: left; font-size: 14px; padding-right: 20px;">Min/Max Stay</a></li>
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

function html_end() {
	echo <<<EOT

</body>
</html>

EOT;
}

?>
