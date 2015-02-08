<?php

require("includes.php");
require("room_booking.php");

$link = db_connect();

$startDate = $_REQUEST['start_date'];
$endDate = $_REQUEST['end_date'];
$startDate = str_replace('-', '/', $startDate);
$endDate = str_replace('-', '/', $endDate);

$_SESSION['rearrange_start_date'] = $startDate;
$_SESSION['rearrange_end_date'] = $endDate;
$_SESSION['rearrange_room_changes'] = array();


$rooms = array();
$roomIdToPosition = array();
$sql = "SELECT r.id, r.name AS name, rt.name AS room_type_name, rt.price_per_bed, rt.price_per_room, rt.type, rt.num_of_beds FROM rooms r INNER JOIN room_types rt ON r.room_type_id=rt.id WHERE r.valid_to>='$endDate' AND r.valid_from<='$startDate' ORDER BY rt._order, r.name";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	$pos = 1;
	while($row = mysql_fetch_assoc($result)) {
		$rooms[$row['id']] = $row;
		$roomIdToPosition[$row['id']] = $pos;
		$pos += 1 + $row['num_of_beds'] * 20;
	}
}


$bookings = array();
$sql = "SELECT bookings.*, booking_descriptions.first_night, booking_descriptions.name, booking_descriptions.name_ext, booking_descriptions.last_night, booking_descriptions.source FROM bookings INNER JOIN booking_descriptions ON bookings.description_id=booking_descriptions.id WHERE booking_descriptions.last_night>='$startDate' AND booking_descriptions.first_night<='$endDate' AND booking_descriptions.cancelled<>1 AND booking_descriptions.first_night>='" . START_DATE_SLASH . "'";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$bookings[$row['id']] = $row;
		$bookings[$row['id']]['room_changes'] = array();
	}
}

if(count($bookings) > 0) {
	$sql = "SELECT * FROM booking_room_changes WHERE booking_id IN (" . implode(',', array_keys($bookings)) . ")";
	$result = mysql_query($sql, $link);
	if(!$result) {
		trigger_error("Cannot get rooms: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	} else {
		while($row = mysql_fetch_assoc($result)) {
			$bookings[$row['booking_id']]['room_changes'][] = $row;
		}
	}
}


$startDate = str_replace('/', '-', $startDate);
$endDate = str_replace('/', '-', $endDate);


$currDate = $startDate;
$dates = array();
while($currDate <= $endDate) {
	$dates[] = $currDate;
	$currDate = date('Y-m-d', strtotime($currDate . ' +1 day'));
}
$tableWidth = (1 + (count($dates)+1) * 101) . 'px';




$js = "		var div = null;\n";
foreach($bookings as $oneBooking) {
	foreach($dates as $currDate) {
		if($currDate < str_replace('/', '-', $oneBooking['first_night']))
			continue;
		if($currDate > str_replace('/', '-', $oneBooking['last_night']))
			continue;

		$divId = $oneBooking['id'] . '_' . $oneBooking['description_id'] . '_' . $currDate;
		$tdId = $oneBooking['room_id'] . '_' . $currDate;
		foreach($oneBooking['room_changes'] as $rc) {
			if(str_replace('/', '-', $rc['date_of_room_change']) == $currDate) {
				$tdId = $rc['new_room_id'] . '_' . $currDate;
				break;
			}
		}
		$name = str_replace('\'', "\\'", $oneBooking['name']) . ' <i>' . $oneBooking['source'] . '</i>';
		$title = $rooms[$oneBooking['room_id']]['name'] . ' ' . $oneBooking['first_night'] . ' - ' . $oneBooking['last_night'];
		$numOfPers = $oneBooking['num_of_person'];
		$js .= "		div = new Element('div', {'id': '$divId', 'class': 'booking_div', 'title': '$title'}).update('$name ($numOfPers)');\n";
		$js .= "		div.addClassName('$currDate');\n";
//		$js .= "		div.ondoubleclick = function() { window.location='edit_booking.php?description_id=" . $oneBooking['description_id'] . "'; }\n";
		$js .= "		$('$tdId').insert(div);\n";
		$js .= "		d = new Draggable('$divId', {constraint: 'vertical', revert: 'failure'});\n";
	}
}

$js = "	function initBookings() {\n$js\n\t}\n";


$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

<script type="text/javascript" src="js/scriptaculous.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">

	jQuery.noConflict();

	function UpdateTableHeaders() {
		jQuery("div.divFloatingHeader").each(function() {
			scrollLeft = jQuery(window).scrollLeft();
			tableWidth = jQuery("table.tableWithFloatingHeader").width()
			if ((scrollLeft > 0) && (scrollLeft < tableWidth)) {
				jQuery(".tableFloatingHeader", this).css("visibility", "visible");
				jQuery(".tableFloatingHeader", this).css("left", scrollLeft + "px");
			} else {
				jQuery(".tableFloatingHeader", $("table.tableWithFloatingHeader")).css("visibility", "hidden");
				jQuery(".tableFloatingHeader", $("table.tableWithFloatingHeader")).css("left", "0px");
			}
		})
	}

	function saveRoomChange(dropDivId, elementToDrop, roomId, date) {
		var a = elementToDrop.ancestors()[0].id.split("_");
		var origRoomId = a[0];
		$(dropDivId).insert(elementToDrop);
		elementToDrop.style.top = '-10px';
		a = elementToDrop.id.split('_');
		var bookingId = a[0];
		var descrId = a[1];
		new Ajax.Request('save_room_change.php', {
			method: 'POST',
			parameters: {
				'booking_id': bookingId,
				'description_id': descrId,
				'date': date,
				'new_room_id': roomId
			},
			onSuccess: function(transport) {
				checkRoomOverbooking(origRoomId, roomId, date);
			}
		});
	}

	function checkRoomOverbooking(oneRoomId, twoRoomId, date) {
		new Ajax.Request('check_room_overbookings.php', {
			method: 'POST',
			parameters: {
				'date': date,
				'room_id_1': oneRoomId,
				'room_id_2': twoRoomId
			},
			onSuccess: function(transport) {
				var a = transport.responseText.split("|");
				checkTdColor(oneRoomId, date, a[0]);
				checkTdColor(twoRoomId, date, a[1]);
			}
		});
	}

	var problemCells = new Array();

	function checkTdColor(roomId, date, response) {
		var tdEl = $(roomId + '_' + date).ancestors()[0];
		if(response == 'OK') {
			removeProblemCell(roomId + '_' + date);
			tdEl.setStyle({backgroundColor: 'rgb(150, 150, 150)'});
		} else {
			tdEl.setStyle({backgroundColor: 'rgb(240, 20, 10)'});
			addProblemCell(roomId + '_' + date);
		}
	}

	function removeProblemCell(divId) {
		for(var i = 0; i < problemCells.length; i++) {
			if(problemCells[i] == divId) {
				problemCells.splice(i, 1);
			}
		}
//		if(problemCells.length > 0) {
//			$('apply_changes_btn').disabled = true;
//		} else {
//			$('apply_changes_btn').disabled = false;
//		}
	}

	function addProblemCell(divId) {
		var found = false;
		for(var i = 0; i < problemCells.length; i++) {
			if(problemCells[i] == divId) {
				found = true;
				break;
			}
		}
		if(!found) {
			problemCells.splice(problemCells.length, 0, divId);
		}
//		$('apply_changes_btn').disabled = true;
	}

$js

</script>



<style>
	div.booking_div {
		margin-left: 5px;
		width: 80px; 
		border: 1px solid black; 
		background: #FE9540;
		padding: 4px;
		overflow: hidden;
		cursor: n-resize;
		top: -10px;
	}

	.drop_hover {
		background: #DDDDDD;
	}
</style>


EOT;



$incldeWzTooltip = false;

html_start("Maverick Reception - Rearrange bookings for period: $startDate - $endDate", $extraHeader, true, 'UpdateTableHeaders(); jQuery(window).scroll(UpdateTableHeaders);initBookings();initDroppables();');

echo <<<EOT

<form action="view_rearrange_bookings.php" method="GET" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Choose a different period:</th></tr>
	<tr>
		<td>From: </td>
		<td>
			<input id="start_date" name="start_date" size="10" maxlength="10" type="text" value="$startDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'start_date', 'chooserSpanSD', 2008, 2025, 'Y/m/d', false);"> 
			<div id="chooserSpanSD" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr>
		<td>To: </td>
		<td>
			<input id="end_date" name="end_date" size="10" maxlength="10" type="text" value="$endDate"><img src="js/datechooser/calendar.gif" onclick="showChooser(this, 'end_date', 'chooserSpanED', 2008, 2025, 'Y/m/d', false);"> 
			<div id="chooserSpanED" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div>
		</td>
	</tr>
	<tr><td colspan="2">
		<input type="submit" value="View dates">
	</td></tr>
</table>
</form>


<form action="apply_rearrange_bookings.php" method="GET" style="float: left;">
<table style="border: 1px solid black; padding: 5px; margin: 10px;">
	<tr><th colspan="2">Apply changes</th></tr>
	<tr><td colspan="2" align="center">
		<input type="submit" value="Apply change" id="apply_changes_btn">
	</td></tr>
</table>
</form>


<div style="clear: both;"></div>


<table class="tableWithFloatingHeader" style="width: $tableWidth; border-collapse: collapse; z-index: 5;">
	<tr><th style=\"width: 100px;\">Room</th>
EOT;

foreach($dates as $currDate) {
	echo "<th style=\"width: 100px;\">$currDate</th>";
}
echo "</tr>\n";
$tdIds = array();
foreach($rooms as $roomId => $roomData) {
	$roomName = $roomData['name'] . ' - ' . $roomData['room_type_name'];
	$height = ($roomData['num_of_beds'] * 40) . 'px';
	$divHeight = ($roomData['num_of_beds'] * 40 - 2) . 'px';
	echo <<<EOT
	<tr>
		<td style="width: 100px; background: rgb(49, 236, 243); height: $height; border: 1px solid #000;">
			<div class="divFloatingHeader" style="position:relative">
				<div class="tableFloatingHeader" style="width: 150px; padding: 10px; background: rgb(49, 236, 243); border: 1px solid rgb(0,0,0); position: absolute; top: -10px; left: 0px; visibility: hidden;">$roomName</div>
			</div>
			$roomName
		</td>

EOT;
	foreach($dates as $currDate) {
		$tdId = $roomId . '_' . $currDate;
		$tdIds[] = $tdId;
		echo <<<EOT
		<td style="width: 100px; background: rgb(150, 150, 150); height: $height; border: 1px solid black; padding: 1px;"><div style="width: 100px; height: $height;" id="$tdId">&nbsp;</div></td>

EOT;
	}

	echo "	</tr>\n";
}
echo "</table>\n";
echo "<script type=\"text/javascript\">\n";
echo "	function initDroppables() {\n";
foreach($tdIds as $tid) {
	list($roomId, $dt) = explode('_', $tid);
	echo <<<EOT
		Droppables.add('$tid', {
			accept: '$dt',
			hoverclass: 'drop_hover',
			onDrop: function(element) { saveRoomChange('$tid', element, '$roomId', '$dt'); }
		});

EOT;
}
echo "	}\n";
echo "</script>\n";



mysql_close($link);

html_end();

?>
