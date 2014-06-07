<?php

$year = intval($_SESSION['booking_year']);
$month = intval($_SESSION['booking_month']);
$day = intval($_SESSION['booking_day']);
$nights = intval($_SESSION['booking_nights']);

$arriveTS = mktime(12, 0, 0, $month, $day, $year);
$lastNightTS = $arriveTS + ($nights - 1) * 24 * 60 * 60;

$arriveDate = date('Y/m/d', $arriveTS);
$lastNightDate = date('Y/m/d', $lastNightTS);

list($startYear, $startMonth, $startDay) = explode('/', $arriveDate);
list($endYear, $endMonth, $endDay) = explode('/', $lastNightDate);
$lang = getCurrentLanguage();
$rooms  = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link, $lang);

$hasBooking = false;
foreach($ROOM_IDS_FOR_ROOM_CODE as $roomCode => $roomIds) {
	$roomType = $rooms[$roomIds[0]]['type'];
	if(($roomType == 'DORM' or $roomType == 'BOTH') and isset($_SESSION["booking_room_DORM_$roomCode"]) and intval($_SESSION["booking_room_DORM_$roomCode"]) > 0) {
		$hasBooking = true;
		break;
	}
	if(($roomType == 'PRIVATE' or $roomType == 'BOTH') and isset($_SESSION["booking_room_PRIVATE_$roomCode"]) and intval($_SESSION["booking_room_PRIVATE_$roomCode"]) > 0) {
		$hasBooking = true;
		break;
	}
}

$roomNameTitle = ROOM_NAME;
$roomTypeTitle = ROOM_TYPE;
$numOfGuestsTitle = NUMBER_OF_GUESTS;
$pricePerPersonTitle = sprintf(PRICE_PER_PERSON_FOR_X_NIGHTS, $nights);
$totalPriceTitle = TOTAL_PRICE;
$selectedRoomsTitle = SELECTED_ROOMS;

if($hasBooking) {
	echo <<<EOT
			<table style="width: 100%; padding-right: 25px;">
				<tr class="title"><th colspan="5">$selectedRoomsTitle</th></tr>
				<tr class="content">
					<td><strong>$roomNameTitle</strong></td>
					<td><strong>$roomTypeTitle</strong></td>
					<td><strong>$numOfGuestsTitle</strong></td>
					<td><strong>$pricePerPersonTitle</strong></td>
					<td><strong>$totalPriceTitle</strong></td>
				</tr>

EOT;
}

$totalPrice = 0;
foreach($ROOM_IDS_FOR_ROOM_CODE as $roomCode => $roomIds) {
	$roomType = $rooms[$roomIds[0]]['type'];
	$name = $roomCode;
	$descr = $rooms[$roomIds[0]]['description'];
	if(count($roomIds) == 1) {
		$name = $rooms[$roomIds[0]]['name'];
	} else {
		$name = constant($roomCode);
	}
	$numOfGuests = 0;
	if(($roomType == 'DORM' or $roomType == 'BOTH') and isset($_SESSION["booking_room_DORM_$roomCode"]) and intval($_SESSION["booking_room_DORM_$roomCode"]) > 0) {
		$numOfGuests = intval($_SESSION["booking_room_DORM_$roomCode"]);
	}
	if(($roomType == 'PRIVATE' or $roomType == 'BOTH') and isset($_SESSION["booking_room_PRIVATE_$roomCode"]) and intval($_SESSION["booking_room_PRIVATE_$roomCode"]) > 0) {
		$numOfGuests = intval($_SESSION["booking_room_PRIVATE_$roomCode"]);
	}
	if($numOfGuests < 1) {
		continue;
	}
	$pricePerGuest = getPriceForInterval(date('Y-m-d', $arriveTS), date('Y-m-d', $lastNightTS), 'BED', $rooms[$roomIds[0]]);
	$pricePerBooking = $pricePerGuest * $numOfGuests;
	echo <<<EOT
				<tr class="content">
					<td>$name</td>
					<td>$descr</td>
					<td style="text-align: center;">$numOfGuests</td>
					<td style="text-align: right; padding-right: 70px;">$pricePerGuest &#8364</td>
					<td style="text-align: right; padding-right: 20px;">$pricePerBooking &#8364</td>
				</tr>

EOT;
	$totalPrice += ($pricePerGuest * $numOfGuests);
}
if($hasBooking) {
	echo <<<EOT
				<tr class="content" style="background-color: #00437D; color: #FFFFFF">
					<td colspan="4" style="background-color: #00437D; color: #FFFFFF"><strong>$totalPriceTitle</strong></td>
					<td style="text-align: right; padding-right: 20px; background-color: #00437D; color: #FFFFFF">$totalPrice &#8364</td>
				</tr>
			</table>

EOT;
}

echo <<<EOT
			<input type="hidden" id="has_booking" name="has_booking" value="$hasBooking">

EOT;


?>
