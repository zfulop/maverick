<?php

require("includes.php");
require("room_booking.php");

foreach($_SESSION as $code => $val) {
	if(substr($code, 0, 3) == 'EB_') {
		unset($_SESSION[$code]);
	}
}

$link = db_connect();

$selectedYear = date('Y');
$selectedMonth = date('m');
if(isset($_REQUEST['year']))
	$_SESSION['view_availability_year'] = $_REQUEST['year'];
if(isset($_SESSION['view_availability_year']))
	$selectedYear = $_SESSION['view_availability_year'];
else
	$_SESSION['view_availability_year'] = $selectedYear;

if(isset($_REQUEST['month']))
	$_SESSION['view_availability_month'] = $_REQUEST['month'];
if(isset($_SESSION['view_availability_month']))
	$selectedMonth = $_SESSION['view_availability_month'];
else
	$_SESSION['view_availability_month'] = $selectedMonth;

if(strlen($selectedMonth) < 2) { 
	$selectedMonth = '0' . $selectedMonth;
}


$fd = mktime(1, 1, 1, $selectedMonth, 1, $selectedYear);
$endDay = date('t', $fd);
$previousMonthYear = $selectedYear;
$previousMonthMonth = $selectedMonth - 1;
if($previousMonthMonth < 1) {
	$previousMonthMonth = 12;
	$previousMonthYear = $previousMonthYear - 1;
}
$nextMonthYear = $selectedYear;
$nextMonthMonth = $selectedMonth + 1;
if($nextMonthMonth > 12) {
	$nextMonthMonth = 1;
	$nextMonthYear = $nextMonthYear + 1;
}


$rooms = loadRooms($selectedYear, $selectedMonth, '01', $selectedYear, $selectedMonth, $endDay, $link);

$sql = "SELECT DISTINCT source FROM booking_descriptions ORDER BY source";
$result = mysql_query($sql, $link);
$sourceOptions = '';
while($row = mysql_fetch_assoc($result)) {
	$sourceOptions .= '<option value="' . $row['source'] . '">' . $row['source'] . '</option>';
}

mysql_close($link);

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
            });
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

	var tooltipShownDate = '';
	var tooltipShownRoomId = '';
	function showTooltip(currDate, roomId, refHtmlId) {
		if(tooltipShownDate == currDate && tooltipShownRoomId == roomId) {
			return false;
		}

		new Ajax.Request('view_availability_view_bookings_for_day.php', {
			method: 'post',
			parameters: {'date': currDate, 'roomId': roomId},
			onSuccess: function(transport) {
				Tip(transport.responseText, STICKY, true, FIX, [refHtmlId, 0, 0], CLICKCLOSE, true);
			},
			onFailure: function(transport) {
				alert('HTTP Error in response. Please try again.');
			}
		});

		tooltipShownDate = currDate;
		tooltipShownRoomId = roomId;
		return false;
	}


</script>


EOT;


$onloadScript = 'UpdateTableHeaders();jQuery(window).scroll(function() { UpdateTableHeaders(); });';


html_start("Maverick Reception - Availability ($selectedYear/$selectedMonth)", $extraHeader, true, $onloadScript);

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

$syncStartDate = date('Y-m-d');
$syncEndDate = date('Y-m-d', strtotime($syncStartDate . ' +7 day'));

echo <<<EOT

<div style="position: fixed;background: white; height: 20px;">
	<input type="button" value="Show forms" id="show_forms_btn" onclick="$('avail_forms').show();$('show_forms_btn').hide();$('hide_forms_btn').show();">
	<input type="button" value="Hide forms" style="display: none;" id="hide_forms_btn" onclick="$('avail_forms').hide();$('show_forms_btn').show();$('hide_forms_btn').hide();">
</div>

<div id="avail_forms" style="position: fixed;background: white; height: 225px; display: none; margin-top: 30px;">

<form action="view_availability.php" method="POST" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">View availability for a month</th></tr>
	<tr>
		<td>Year</td><td><input name="year" size="4" value="$selectedYear"></td>
	</tr>
	<tr>
		<td>Month</td><td><select name="month">$va_monthOptions</select></td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="View availability">
	</td></tr>
</table>
</form>


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




<form action="synchro/main.php" method="POST" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Synchronization with booker sites</th></tr>
	<tr>
		<td>From: </td>
		<td>
			<input id="start_date" name="start_date" size="10" maxlength="10" type="text" value="$syncStartDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>To: </td>
		<td>
			<input id="end_date" name="end_date" size="10" maxlength="10" type="text" value="$syncEndDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date', 'chooserSpanED', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>Sites: </td>
		<td>
			<select name="sites[]" size="6" multiple style="height: 70px;">
				<option value="myallocator" selected>MyAllocator</option>
				<option value="hrs">HRS</option>
			</select>
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="Start synchronization">
	</td></tr>
</table>
</form>


<form action="edit_new_booking.php" method="POST" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="4">Create new booking</th></tr>
	<tr><td colspan="4"><input type="submit" value="New"></td></tr>

</table>
</form>


<form action="view_rearrange_bookings.php" method="GET" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Rearrange bookings</th></tr>
	<tr>
		<td>From: </td>
		<td>
			<input id="start_date2" name="start_date" size="10" maxlength="10" type="text" value="$syncStartDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date2', 'chooserSpanSD2', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanSD2" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>To: </td>
		<td>
			<input id="end_date2" name="end_date" size="10" maxlength="10" type="text" value="$syncEndDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date2', 'chooserSpanED2', 2008, 2025, 'Y-m-d', false);"> 
			<div id="chooserSpanED2" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="View...">
	</td></tr>
</table>
</form>

</div>


<div style="clear: both;">
</div>


<h2 style="margin-top: 60px;"><a class="next_month_btn" href="view_availability.php?year=$previousMonthYear&month=$previousMonthMonth" title="Previous month">&lt;</a> Room availability for: $selectedYear/$selectedMonth <a class="next_month_btn" href="view_availability.php?year=$nextMonthYear&month=$nextMonthMonth" title="Next month">&gt;</a></h2>
<div style="float:left;">Font size: </div>
<form style="float: left; margin-bottom: 5px;">
	<input type="button" value="+" onclick="increaseFontSize();" style="font-size: 14px; font-weight: bold;"> <input type="button" value="-" onclick="decreaseFontSize();" style="font-size: 14px; font-weight: bold;">
</form>

<div style="clear:both;"></div>

	<table class="tableWithFloatingHeader" border="1">

EOT;

$startDate = "$selectedYear-$selectedMonth-01";
$endDate = "$selectedYear-$selectedMonth-$endDay";
$dates = array();
for($currDate = $startDate; $currDate <= $endDate; $currDate = date('Y-m-d', strtotime("$currDate +1 day"))) {
	$dates[] = $currDate;
}


$cntr = 0;


foreach($rooms as $roomId => $room) {
	if($cntr % 6 === 0) {
		echo "		<tr><th></th>\n";
		foreach($dates as $currDate) {
			if(date('N', strtotime($currDate)) > 5) {
				echo "		<th style=\"background: rgb(230, 230, 230)\">" . substr($currDate, 8, 2) . ' ' . date('D', strtotime($currDate)) . "</th>\n";
			} else {
				echo "		<th>" . substr($currDate, 8, 2) . ' ' . date('D', strtotime($currDate)) . "</th>\n";
			}
		}
		echo "	</tr>\n";
	}

	$roomName = $room['name'] . '<br>[' . $room['room_type_name'] . ']';
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
		$avail = getNumOfAvailBeds($room, $currDate);
		$occup = getNumOfOccupBeds($room, $currDate);
		$htmlId = $currDate . '_' . $roomId;
		$style = "background: " . getCSSBackgroundColor($currDate, $room) . ";";
		if($occup > $room['num_of_beds']) {
			$style .= 'border: 3px dotted black;';
		}
		echo "		<td align=\"center\" style=\"$style\">\n";
		$hasBooking = hasBookingForDay($room, $currDate);
		if($hasBooking) {
			echo "			<a id=\"$htmlId\" href=\"#\" onclick=\"showTooltip('$currDate','$roomId', '$htmlId');return false;\" style=\"display: block;text-decoration: none; color: black;\">\n";
		}
		echo "				$avail" . " / " . $room['num_of_beds'];
		if($occup > $room['num_of_beds']) {
			echo " (" . ($occup - $room['num_of_beds']) . ')';
		}
		echo "<br>\n";
		foreach(getBookerNamesForDay($room, $currDate) as $oneBookerName) {
			echo "				$oneBookerName\n";
		}
		if($hasBooking) {
			echo "			</a>\n";
		}
//		printBookingsTooltipDivPerDay($room, $currDate, "bookings_per_day_per_room_$cntr");
		echo "		</td>\n";
		$cntr += 1;
	}
	echo "	</tr>\n";
}
echo "	<tr>\n";
echo "		<th>Room name</th>\n";
foreach($dates as $currDate) {
	if(date('N', strtotime($currDate)) > 5) {
		echo "		<th style=\"background: rgb(230, 230, 230)\">" . substr($currDate, 8, 2) . ' ' . date('D', strtotime($currDate)) . "</th>\n";
	} else {
		echo "		<th>" . substr($currDate, 8, 2) . ' ' . date('D', strtotime($currDate)) . "</th>\n";
	}
}
echo "	</tr>\n";

echo "</table>\n";




html_end();


function getCSSBackgroundColor($currDate, &$room) {
	$isWeekend = false;
	if(date('N', strtotime($currDate)) > 5) {
		$isWeekend = true;
	}
	$r = 255;
	$g = 255;
	$b = 255;
	$style="";
	$avail = getNumOfAvailBeds($room, $currDate);
	if($avail < 1) {
		$r = 255;
		$g = 0;
		$b = 0;
	} elseif($avail < $room['num_of_beds']) {
		if($room['type'] == 'PRIVATE') {
			$r = 255;
			$g = 0;
			$b = 0;
		} else {
			$r = 255;
			$g = 255;
			$b = 0;
		}
	}
	if($isWeekend) {
		$r = max(0, $r - 30);
		$g = max(0, $g - 30);
		$b = max(0, $b - 30);
	}
	return "rgb($r,$g,$b)";
}



function hasBookingForDay(&$room, $currDate) {
	$currDate = str_replace('-', '/', $currDate);
	$hasBooking = false;
	foreach($room['bookings'] as $oneBooking) {
		if($oneBooking['cancelled']) {
			continue;
		}
		if(($oneBooking['first_night'] <= $currDate) and ($oneBooking['last_night'] >= $currDate)) {
			$hasBooking = true;
			break;
		}
	}
	foreach($room['room_changes'] as $oneRoomChange) {
		if($oneRoomChange['cancelled']) {
			continue;
		}
		if($oneRoomChange['date_of_room_change'] == $currDate) {
			$hasBooking = true;
			break;
		}
	}
	return $hasBooking;
}




function getBookerNamesForDay(&$oneRoom, $oneDay) {
	$names = array();
	$oneDay = str_replace('-', '/', $oneDay);
	foreach($oneRoom['bookings'] as $oneBooking) {
		if($oneBooking['cancelled'] == 1) {
			continue;
		}

		if(isset($oneBooking['changes'])) {
			$isThereRoomChangeForDay = false;
			foreach($oneBooking['changes'] as $oneChange) {	
				if($oneChange['date_of_room_change'] == $oneDay) {
					$isThereRoomChangeForDay = true;
				}
			}
			if($isThereRoomChangeForDay)
				continue;
		}

		if(($oneBooking['first_night'] <= $oneDay) and ($oneBooking['last_night'] >= $oneDay)) {
			$count = 0;
			if($oneBooking['booking_type'] == 'BED') {
				$count = $oneBooking['num_of_person'];
			} else {
				$count = $oneRoom['num_of_beds'];
			}
			for($i = 0; $i < $count; $i++) {
				$style = '';
				if($oneBooking['confirmed'] == 1) {
					$style .= 'font-weight: bold;';
				}
				if($oneBooking['checked_in'] == 1) {
					$style .= 'background: rgb(0, 255, 0);';
				}
				if($oneBooking['paid'] == 1) {
					$style .= 'border: 2px solid rgb(0, 0, 255);';
				}
				$names[] = "<span style=\"margin: 3px;$style\">" . str_replace(" ", "&nbsp;", $oneBooking['name']) . '&nbsp;' . str_replace(" ", "&nbsp;", $oneBooking['name_ext']) . "</span><br>";
			}
		}
	}

	foreach($oneRoom['room_changes'] as $oneRoomChange) {
		if($oneRoomChange['cancelled'] == 1) {
			continue;
		}

		if($oneRoomChange['date_of_room_change'] == $oneDay) {
			if($oneRoomChange['booking_type'] == 'BED')
				$count = $oneRoomChange['num_of_person'];
			else {
				$count = $oneRoom['num_of_beds'];
			}
			for($i = 0; $i < $count; $i++) {
				$style = '';
				if($oneRoomChange['confirmed'] == 1) {
					$style .= 'font-weight: bold;';
				}
				if($oneRoomChange['checked_in'] == 1) {
					$style .= 'background: rgb(0, 255, 0);';
				}
				if($oneRoomChange['paid'] == 1) {
					$style .= 'border: 2px solid rgb(0, 0, 255);';
				}
				$names[] = "<span style=\"margin: 3px;$style\">" . str_replace(" ", "&nbsp;", $oneRoomChange['name'] . ' ' . $oneRoomChange['name_ext']) . "&nbsp;(RC)</span><br>";
			}

		}
	}

	return $names;
}



?>
