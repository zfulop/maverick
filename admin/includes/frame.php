<?php

function html_start($title = null, $extraHeader = '', $onloadScript = '', $onresizeScript = '') {

	$title = $_SESSION['login_hotel_name'] . ' - Admin - ' . $title;
	$loginName = $_SESSION['login_user'];


	$logout = ADMIN_ROOT_URL . 'logout.php';
	$changePassword = ADMIN_ROOT_URL . 'change_password.php';
	$audit = ADMIN_ROOT_URL . 'view_audit.php';
	$stats = ADMIN_ROOT_URL . 'view_statistics.php';
	$occupancy = ADMIN_ROOT_URL . 'view_occupancy.php';
	$pastProjectedPayment = ADMIN_ROOT_URL . 'view_past_projected_payments.php';
	$report = ADMIN_ROOT_URL . 'view_payment_report.php';

	$tooltipJs = ADMIN_ROOT_URL . 'js/wz_tooltip.js';
	$prototypeJs = ADMIN_ROOT_URL . 'js/prototype.js';


	echo <<<EOT
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="maverick-admin.css" rel="stylesheet" type="text/css"/>
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

	</script>

	$extraHeader

</head>

<body style="font-size: 12px;" onload="hideSubMenu();$onloadScript" onresize="$onresizeScript">

<script type="text/javascript" src="$tooltipJs"></script>

<div style="text-align: center; border-style: solid; height: 25px; padding-left: 15px;">
<a href="$audit" style="float: left; font-size: 14px; padding-right: 20px;">Audit</a>
<a href="$stats" style="float: left; font-size: 14px; padding-right: 20px;">Statistics</a>
<a href="$occupancy" style="float: left; font-size: 14px; padding-right: 20px;">Occupancy</a>
<a href="$pastProjectedPayment" style="float: left; font-size: 14px; padding-right: 20px;">Past/Projeced Payments</a>
<a href="$report" style="float: left; font-size: 14px; padding-right: 20px;">Payments</a>
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
