<?php

require('../includes.php');
require('../../recepcio/room_booking.php');
require('dict.php');

$aYear = $_REQUEST['arrive_year'];
$aMonth = $_REQUEST['arrive_month'];
$aDay = $_REQUEST['arrive_day'];

$dYear = $_REQUEST['depart_year'];
$dMonth = $_REQUEST['depart_month'];
$dDay = $_REQUEST['depart_day'];

if($aMonth < 10) {
	$aMonth = '0' . $aMonth;
}
if($aDay < 10) {
	$aDay = '0' . $aDay;
}
$year = $aYear;
$month = $aMonth;
$day = $aDay;

if($dMonth < 10) {
	$dMonth = '0' . $dMonth;
}
if($dDay < 10) {
	$dDay = '0' . $dDay;
}

$arriveDate = $aYear . '/' . $aMonth . '/' . $aDay;
$lastNightTS = strtotime("$dYear-$dMonth-$dDay -1 day");
$lastNightDate = date('Y/m/d', $lastNightTS);

$nights = round((strtotime("$dYear-$dMonth-$dDay") - strtotime("$aYear-$aMonth-$aDay")) / (60*60*24));

html_start('BookingAndPrices', 'Booking', VIEW_AVAILABILITY);

$firstNight = $year . ' ' . $MONTHS[intval($month)] . ' ' . $day;
$lastNight = date('Y', $lastNightTS) . ' ' . $MONTHS[date('n', $lastNightTS)]	. ' ' . date('d', $lastNightTS);
$departDate = $dYear . ' ' . $MONTHS[intval($dMonth)] . ' ' . $dDay;


$link = db_connect();

list($startYear, $startMonth, $startDay) = explode('/', $arriveDate);
list($endYear, $endMonth, $endDay) = explode('/', $lastNightDate);
$lang = getCurrentLanguage();
$rooms  = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link, $lang);

$arriveTS = strtotime("$startYear-$startMonth-$startDay");

$oneWeekBeforeArriveTS = strtotime("$aYear-$aMonth-$aDay -7 day");
$oneWeekBeforeDepartTS = strtotime("$dYear-$dMonth-$dDay -7 day");
$oneWeekAfterArriveTS = strtotime("$aYear-$aMonth-$aDay +7 day");
$oneWeekAfterDepartTS = strtotime("$dYear-$dMonth-$dDay +7 day");
$oneWeekBeforeLink = "view_availability.php?arrive_year=" . date("Y", $oneWeekBeforeArriveTS) . "&arrive_month=" . date("n", $oneWeekBeforeArriveTS) . "&arrive_day=" . date("j", $oneWeekBeforeArriveTS) . "&depart_year=" . date("Y", $oneWeekBeforeDepartTS) . "&depart_month=" . date("n", $oneWeekBeforeDepartTS) . "&depart_day=" . date("j", $oneWeekBeforeDepartTS);
$oneWeekAfterLink = "view_availability.php?arrive_year=" . date("Y", $oneWeekAfterArriveTS) . "&arrive_month=" . date("n", $oneWeekAfterArriveTS) . "&arrive_day=" . date("j", $oneWeekAfterArriveTS) . "&depart_year=" . date("Y", $oneWeekAfterDepartTS) . "&depart_month=" . date("n", $oneWeekAfterDepartTS) . "&depart_day=" . date("j", $oneWeekAfterDepartTS);
$oneWeekBeforeDate = date("Y/n/j", $oneWeekBeforeArriveTS);
$oneWeekAfterDate = date("Y/n/j", $oneWeekAfterArriveTS);


$vaNextWeek = sprintf(VIEW_AVAILABILITY_FOR_NEXT_WEEK, $oneWeekAfterDate);
$vaPrevWeek = sprintf(VIEW_AVAILABILITY_FOR_PREVIOUS_WEEK, $oneWeekBeforeDate);
$datesTitle = DATES;
$fnTitle = FIRST_NIGHT;
$lnTitle = LAST_NIGHT;
$departTitle = DEPARTURE;
$numOfCols = 3 + $nights;
$availTitle = AVAILABILITY;
$roomNameTitle = ROOM_NAME;
$roomTypeTitle = ROOM_TYPE;
$numOfGuestsTitle = NUMBER_OF_GUESTS;
$bookNowTitle = BOOK_NOW;
$restartBookingTitle = RESTART_BOOKING;
echo <<<EOT

			<div style="width: 100%; display: block;">
				<div id="next_week_link" style="float:right;">
					<a href="$oneWeekAfterLink">$vaNextWeek</a>
				</div>
				<div id="prev_week_link">
					<a href="$oneWeekBeforeLink">$vaPrevWeek</a>
				</div>
			</div>

			<table style="width: 100%;">
				<tr class="title"><th style="width: 300px;" colspan="2">Dates</th></tr>
				<tr class="content"><td style="width: 70px;"><strong>$fnTitle:</strong></td><td>$firstNight</td></tr>
				<tr class="content"><td style="width: 70px;"><strong>$departTitle:</strong></td><td>$departDate</td></tr>
			</table>

			<form id="availability_form" action="book_now.php" method="POST">
			<input type="hidden" name="year" value="$year">
			<input type="hidden" name="month" value="$month">
			<input type="hidden" name="day" value="$day">
			<input type="hidden" name="nights" value="$nights">
			<fieldset>
			<table style="width: 100%">
				<tr class="title"><th colspan="$numOfCols">$availTitle</th></tr>
				<tr class="content">
					<td><strong>$roomNameTitle</strong></td>
					<td><strong>$roomTypeTitle</strong></td>

EOT;
$oneDayTS = $arriveTS;
for($i = 0; $i < $nights; $i++) {
	$oneDay = $DAYS[date('w', $oneDayTS)] . ' ' . date("m/d", $oneDayTS);
	echo "\t\t\t\t\t<td style=\"text-align: center;\"><strong>$oneDay</strong></td>\n";
	$oneDayTS += 24 * 60 * 60;
}
echo <<<EOT
					<td><strong>$numOfGuestsTitle</strong></td>
				</tr>

EOT;


foreach($ROOM_IDS_FOR_ROOM_CODE as $roomCode => $roomIds) {
	$name = $roomCode;
	$descr = $rooms[$roomIds[0]]['description'];
	if(count($roomIds) == 1) {
		$name = $rooms[$roomIds[0]]['name'];
	} else {
		$name = constant($roomCode);
	}
	echo "				<tr class=\"content\">\n";
	echo "					<td>$name</td>\n";
	echo "					<td>$descr</td>\n";
	$oneDayTS = $arriveTS;
	$type = $rooms[$roomIds[0]]['type'];
	$maxOccupBeds = 0;
	for($i = 0; $i < $nights; $i++) {
		$oneDay =  date('Y/m/d', $oneDayTS);
		$occupBeds = 0;
		$allBooked = true;
		foreach($roomIds as $oneRoomId) {
			$ob = getNumOfOccupBeds($rooms[$oneRoomId], $oneDay);
			$occupBeds += $ob;
			if($type == 'DORM' and $ob < $rooms[$oneRoomId]['num_of_beds'])
				$allBooked = false;
			if($type == 'PRIVATE' and $ob < 1)
				$allBooked = false;
		}
		$maxOccupBeds = max($maxOccupBeds, $occupBeds);

		if($allBooked)
			$price = 'X';
		else
			$price = getPriceCell($rooms[$roomIds[0]], $type, $oneDay);

		echo "					<td style=\"text-align: center\">$price</td>\n";
		$oneDayTS += 24 * 60 * 60;
	}
	echo "					<td style=\"padding-left: 20px;\"><div id=\"sel_num_guest_room_$type$roomCode\">\n";
	echo getSelectNumberOfGuests($rooms, $roomCode, $roomIds, $type, $maxOccupBeds);
	echo "					</div></td>\n";
	echo "				</tr>\n";
}

echo <<<EOT
			</table>
			</fieldset>
			<div id="preview_sum" style="margin-top: 10px; padding-right: 25px;"></div>
			<div style="margin-top: 15px; display: none;" id="book_now_btn_div"><fieldset>
				<input type="submit" class="input_btn2" value="$bookNowTitle"> <input class="input_btn2" type="button" value="$restartBookingTitle" onClick="window.location='restart_booking.php';">
			</fieldset></div>
			</form>

			<script type="text/javascript">
				updatePreviewBooking();
			</script>

EOT;



html_end('BookingAndPrices', 'Booking');

mysql_close($link);



function getPriceCell($oneRoom, $type, $oneDay) {
	if($type == 'DORM') {
		$prc = $oneRoom['price_per_bed'];
		if(isset($oneRoom['prices'][$oneDay])) {
			$prc = $oneRoom['prices'][$oneDay]['price_per_bed'];
		}
		$retVal = $prc . ' &#8364;';
	} elseif($type == 'PRIVATE') {
		$prc = $oneRoom['price_per_room'];
		if(isset($oneRoom['prices'][$oneDay])) {
			$prc = $oneRoom['prices'][$oneDay]['price_per_room'];
		}
		$retVal = ($prc / $oneRoom['num_of_beds']) . ' &#8364;';
	}
	return $retVal;
}


// type: DORM or PRIVATE
function getSelectNumberOfGuests(&$rooms, $roomCode, &$roomIds, $type, $maxOccupBeds) {
	$retVal = "";
	$numOfBedsPerRoom = $rooms[$roomIds[0]]['num_of_beds'];
	$numOfBedsAvailable = $numOfBedsPerRoom * count($roomIds);
	if($type == 'PRIVATE') {
		if($maxOccupBeds < $numOfBedsAvailable) {
			$retVal = "\t\t\t\t\t\t<select name=\"room_$type" . '_' . $roomCode . "\" id=\"room_$type" . '_' . $roomCode . "\" onChange=\"updatePreviewBooking();\">\n";
			$retVal .= "\t\t\t\t\t\t\t<option value=\"0\"> - </option>\n";
			for($i = 1; $i <= count($roomIds); $i++) {
				$optValue = $i * $numOfBedsPerRoom;
				if(($maxOccupBeds + $optValue) > $numOfBedsAvailable) {
					break;
				}
				$isSelected = "";
				if(isset($_SESSION["booking_room_$type" . '_' . $roomCode]) and $_SESSION["booking_room_$type" . '_' . $roomCode] == $optValue)
					$isSelected = " selected";

				$retVal .= "\t\t\t\t\t\t\t<option value=\"$optValue\"$isSelected>$optValue</option>\n";
			}
			$retVal .= "\t\t\t\t\t\t\t</select>\n";
		}
	} else {
		if($maxOccupBeds < $numOfBedsAvailable) {
			$retVal = "\t\t\t\t\t\t<select name=\"room_$type" . '_' . $roomCode . "\" id=\"room_$type" . '_' . $roomCode . "\" onChange=\"updatePreviewBooking();\">\n";
			$retVal .= "\t\t\t\t\t\t\t<option value=\"0\"> - </option>\n";
			for($i = 1; $i <= ($numOfBedsAvailable - $maxOccupBeds); $i++) {
				$isSelected = "";
				if(isset($_SESSION["booking_room_$type" . '_' . $roomCode]) and $_SESSION["booking_room_$type" . '_' . $roomCode] == $i)
					$isSelected = " selected";

				$retVal .= "\t\t\t\t\t\t\t<option value=\"$i\"$isSelected>$i</option>\n";
			}
			$retVal .= "\t\t\t\t\t\t</select>\n";
		}
	}

	return $retVal;
}

?>
