<?php

require('includes.php');
require('includes/common_booking.php');
require(RECEPCIO_BASE_DIR . 'room_booking.php');


$retVal = array('details' => array(), 'total' => '');
$retVal['details']['rooms'] = array();
$retVal['details']['services'] = array();
$total = 0;
$arriveDate = $_SESSION['from_date'];
$arriveDateTs = strtotime($_SESSION['from_date']);
$nights = $_SESSION['nights'];
$lastNightTs = strtotime($_SESSION['to_date']);
$lastNight = $_SESSION['to_date'];

$lang = getCurrentLanguage();
$currency = getCurrency();
$total = 0;
$location = getLocation();

$link = db_connect($location);

$specialOffers = loadSpecialOffers($arriveDate,$lastNight, $link, $lang);
$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
$roomTypesData = loadRoomTypes($link, $lang);

$bookings = getBookingsWithDiscount($location, $arriveDateTs, $nights, $roomTypesData, $rooms, $specialOffers);
foreach($bookings as $roomTypeId => $oneRoomBooked) {
	$roomType = $roomTypesData[$roomTypeId];
	$name = $roomType['name'];
	$numOfGuests = $oneRoomBooked['numOfGuests'];
	$numNightsForNumPerson = sprintf(NUM_NIGHTS_FOR_NUM_PERSON, $nights, $numOfGuests);
	$roomData = getRoomData($rooms, $roomTypeId);
	$price = $oneRoomBooked['discountedPrice'];
	$total += $price;
	if(isClientFromHU() and $roomType['num_of_beds'] > 4) {
		$nob = $roomType['num_of_beds'];
		$roomType['num_of_beds'] = 4;
		$name = str_replace('5', '4', $name);
	}
	$retVal['details']['rooms'][] = array('name' => $name, 'description' => $numNightsForNumPerson, 'price' => formatMoney(convertCurrency($price, 'EUR', $currency), $currency));
}

$services = loadServices($link);
$bookedServices = getBookedServices($services, $location, 'EUR');
foreach($bookedServices as $service) {
	$title = $service['title'];
	$unitName = $service['unit_name'];
	if($unitName == '') {
		$unitName = OCCASION;
	}
	$forNumOfOccasion = sprintf(FOR_NUM_OF_OCCASIONS, $service['occasion'], $unitName);
	$serviceCurrency = $service['currency'];
	$price = convertCurrency($service['price'], $serviceCurrency, 'EUR');
	$total += $price;
	$retVal['details']['services'][] = array('name' => $title, 'description' => $forNumOfOccasion, 'price' => formatMoney(convertCurrency($price, 'EUR', $currency), $currency));
}

mysql_close($link);


$retVal['total'] = formatMoney(convertCurrency($total, 'EUR', $currency), $currency);
$retVal['status'] = $_SESSION['booking_status'];

header("Content-type: application/json; charset=utf-8");
echo json_encode($retVal);




?>
