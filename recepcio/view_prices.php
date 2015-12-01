<?php

require("includes.php");
require("room_booking.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



foreach($_SESSION as $code => $val) {
	if(substr($code, 0, 3) == 'EB_') {
		unset($_SESSION[$code]);
	}
}

$link = db_connect();

$selectedYear = date('Y');
$selectedMonth = date('m');
if(isset($_REQUEST['year']))
	$_SESSION['view_prices_year'] = $_REQUEST['year'];
if(isset($_SESSION['view_prices_year']))
	$selectedYear = $_SESSION['view_prices_year'];
else
	$_SESSION['view_prices_year'] = $selectedYear;

if(isset($_REQUEST['month']))
	$_SESSION['view_prices_month'] = $_REQUEST['month'];
if(isset($_SESSION['view_prices_month']))
	$selectedMonth = $_SESSION['view_prices_month'];
else
	$_SESSION['view_prices_month'] = $selectedMonth;


$fd = mktime(1, 1, 1, $selectedMonth, 1, $selectedYear);
$endDay = date('t', $fd);

$roomTypes = loadRoomTypes($link);
$prices = array();
foreach($roomTypes as $id => $data) {
	$prices[$id] = array();
}
$sql = "SELECT * FROM prices_for_date WHERE date>='$selectedYear/$selectedMonth/01' AND date<='$selectedYear/$selectedMonth/$endDay'";
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	$rtId = $row['room_type_id'];
	$date = $row['date'];
	$prices[$rtId][$date] = $row;
}


$extraHeader = <<<EOT

<script src="js/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
	 jQuery.noConflict();
</script>
<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->


<script type="text/javascript">

        function UpdateTableHeaders() {
            jQuery("div.divFloatingHeader").each(function() {
                scrollLeft = jQuery(window).scrollLeft();
				tableWidth = jQuery("table.tableWithFloatingHeader").width()
                if ((scrollLeft > 0) && (scrollLeft < tableWidth)) {
                    jQuery(".tableFloatingHeader", this).css("visibility", "visible");
                    jQuery(".tableFloatingHeader", this).css("left", scrollLeft + "px");
                }
                else {
                    jQuery(".tableFloatingHeader", jQuery("table.tableWithFloatingHeader")).css("visibility", "hidden");
                    jQuery(".tableFloatingHeader", jQuery("table.tableWithFloatingHeader")).css("left", "0px");
                }
            })
        }


	function increaseFontSize() {
		changeFontSize(function(toChange) { return toChange + 1; });
	}

	function decreaseFontSize() {
		changeFontSize(function(toChange) { return toChange - 1; });
	}

	function changeFontSize(changerFunction) {
		var size = jQuery('table.tableWithFloatingHeader').css('font-size');
		size = parseInt(size.substring(0, size.length - 2));
		size = changerFunction(size);
		jQuery('table.tableWithFloatingHeader').css('font-size', '' + size + 'px');
	}

	function updateSearchField(controlFieldId, labelFieldId, inputFieldId) {
		var cselected = document.getElementById(controlFieldId);
		if(cselected.checked) {
			document.getElementById(labelFieldId).style.color = '#000000';
			document.getElementById(inputFieldId).disabled = false;
		} else {
			document.getElementById(labelFieldId).style.color = '#aaaaaa';
			document.getElementById(inputFieldId).disabled = true;
		}
	}

</script>


EOT;


$onloadScript = 'UpdateTableHeaders();jQuery(window).scroll(function() { UpdateTableHeaders(); });';


html_start("View Prices", $extraHeader, true, $onloadScript);

$thisyear = date('Y');

$monthOptions = '';
$va_monthOptions = '';
for($i = 1; $i <= 12; $i++) {
	$m = $i;
	if(strlen($m) < 2)
		$m = '0' . $m;
	$monthOptions .= "			<option value=\"$m\">" . date('M', mktime(1,1,1, $i, 1, 2000)) . "</option>\n";
	$va_monthOptions .= "			<option value=\"$m\"" . ($m == $selectedMonth ? ' selected' : '') . ">" . date('F', mktime(1,1,1, $i, 1, 2000)) . "</option>\n";
}


echo <<<EOT


<form action="view_prices.php" method="POST" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">View prices for a month</th></tr>
	<tr>
		<td>Year</td><td><input name="year" size="4" value="$selectedYear"></td>
	</tr>
	<tr>
		<td>Month</td><td><select name="month">$va_monthOptions</select></td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="View prices">
	</td></tr>
</table>
</form>

<div style="clear: both;">
</div>

EOT;

echo <<<EOT

<h2>Room prices for: $selectedYear/$selectedMonth</h2>
<div style="float:left;">Font size: </div>
<form style="float: left; margin-bottom: 5px;">
	<input type="button" value="+" onclick="increaseFontSize();" style="font-size: 14px; font-weight: bold;"> <input type="button" value="-" onclick="decreaseFontSize();" style="font-size: 14px; font-weight: bold;">
</form>

<div style="clear:both;"></div>

EOT;

$startDate = "$selectedYear-$selectedMonth-01";
$endDate = "$selectedYear-$selectedMonth-$endDay";
$dates = array();
for($currDate = $startDate; $currDate <= $endDate; $currDate = date('Y-m-d', strtotime("$currDate +1 day"))) {
	$dates[] = $currDate;
}

echo <<<EOT
	<table class="tableWithFloatingHeader" border="1">
		<tr>
			<th></th>

EOT;
foreach($dates as $currDate) {
	if(date('N', strtotime($currDate)) > 5) {
		echo "		<th style=\"background: rgb(240, 240, 240)\">" . substr($currDate, 8, 2) . ' ' . date('D', strtotime($currDate)) . "</th>\n";
	} else {
		echo "		<th>" . substr($currDate, 8, 2) . ' ' . date('D', strtotime($currDate)) . "</th>\n";
	}
}
echo "	</tr>\n";
$cntr = 0;
foreach($roomTypes as $rtId => $roomType) {
	$roomName = $roomType['name'];
	echo <<<EOT
	<tr>
		<td style="width: 100px; background: rgb(49, 236, 243);">
			<div class="divFloatingHeader" style="position:relative">
				<div class="tableFloatingHeader" style="width: 150px; padding: 10px; background: rgb(49, 236, 243); border: 1px solid rgb(0,0,0); position: absolute; top: 0px; left: 0px; visibility: hidden;">$roomName</div>
			</div>
			$roomName
		</td>

EOT;
	foreach($dates as $currDate) {
		$currDateSlash = str_replace('-','/',$currDate);
		$style="";
		if(date('N', strtotime($currDate)) > 5) {
			$style= 'background: rgb(240, 240, 240);';
		}
		$price = '';
		echo "		<td align=\"center\" style=\"$style\">";
		$column = 'price_per_room';
		if(isDorm($roomType)) {
			$column = 'price_per_bed';
		}
		if(isset($prices[$rtId][$currDateSlash]) and !is_null($prices[$rtId][$currDateSlash][$column])) {
			$price = $prices[$rtId][$currDateSlash][$column];
		} else {
			$price = $roomType[$column];
		}
		echo $price . "</td>\n";
		$cntr += 1;
	}
	echo "	</tr>\n";
}
echo "</table>\n";

mysql_close($link);

html_end();





?>
