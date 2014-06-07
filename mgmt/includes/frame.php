<?php

$incldeWzTooltip = true;

function html_start($title = "Maverick Mgmt", $extraHeader = '', $showMenu = true, $onloadScript = '') {
	global $incldeWzTooltip;

	$index = MGMT_ROOT_URL . 'index.php';
	$receptionists = MGMT_ROOT_URL . 'view_receptionists.php';
	$report = MGMT_ROOT_URL . 'view_money_report.php';
	$cleaners = MGMT_ROOT_URL . 'view_cleaners.php';
	$shifts = MGMT_ROOT_URL . 'view_shifts.php';
	$lists = MGMT_ROOT_URL . 'view_lists.php';
	$photos = MGMT_ROOT_URL . 'view_room_images.php';
	$videos = MGMT_ROOT_URL . 'view_videos.php';
	$rooms = MGMT_ROOT_URL . 'view_rooms.php';
	$texts = MGMT_ROOT_URL . 'view_site_text.php';

	$tooltipJs = MGMT_ROOT_URL . 'js/wz_tooltip.js';

	if($incldeWzTooltip) {
		$tooltipJsHtml = "<script type=\"text/javascript\" src=\"$tooltipJs\"></script>\n";
	} else {
		$tooltipJsHtml = '';
	}

	$prototypeJs = MGMT_ROOT_URL . 'js/prototype.js';
	$css = MGMT_ROOT_URL . 'maverick-recepcio.css';

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
	</script>

	$extraHeader

</head>

<body style="font-size: 12px; padding: 0px; margin: 0px 10px 10px 10px;" onload="hideSubMenu();$onloadScript">

$tooltipJsHtml

EOT;

	if($showMenu) {
		echo <<<EOT

	<div style="text-align: center; border-style: solid; height: 25px; position: relative; background: rgb(220, 220, 220);">
		<a href="$index" style="float: left; font-size: 14px; padding-left: 20px; padding-right: 20px;">Home</a>
		<a href="$report" style="float: left; font-size: 14px; padding-right: 20px;">Money Report</a>
		<a href="$receptionists" style="float: left; font-size: 14px; padding-right: 20px;">Receptionists</a>
		<a href="$cleaners" style="float: left; font-size: 14px; padding-right: 20px;">Cleaners</a>
		<a href="$shifts" style="float: left; font-size: 14px; padding-right: 20px;">Work Shifts</a>
		<a href="$lists" style="float: left; font-size: 14px; padding-right: 20px;">Lists</a>
		<a href="$photos" style="float: left; font-size: 14px; padding-right: 20px;">Room images</a>
		<a href="$videos" style="float: left; font-size: 14px; padding-right: 20px;">Videos</a>
		<a href="$rooms" style="float: left; font-size: 14px; padding-right: 20px;">Rooms</a>
		<a href="$texts" style="float: left; font-size: 14px; padding-right: 20px;">Site texts</a>
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
