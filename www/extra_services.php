<?php

require('includes.php');
require('includes/common_booking.php');
require(RECEPCIO_BASE_DIR . 'room_booking.php');


$_SESSION['booking_status'] = array('rooms', 'services');

$location = getLocation();
$lang = getCurrentLanguage();
$currency = getCurrency();

$link = db_connect($location);

$arriveDateTs = strtotime($_SESSION['from_date']);
$arriveDate = $_SESSION['from_date'];
$arriveDateStr = strftime(DATE_FORMAT, $arriveDateTs);
$nights = $_SESSION['nights'];
$lastNightTs = strtotime($_SESSION['to_date']);
$lastNight = $_SESSION['to_date'];


$extraServices = EXTRA_SERVICES;
$extras = EXTRAS;
$occasion = OCCASION;


$specialOffers = loadSpecialOffers("start_date<='$arriveDate' AND end_date>='$lastNight'", $link, $lang);
$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
$roomTypesData = loadRoomTypes($link, $lang);

$bookings = getBookingsWithDiscount($location, $arriveDateTs, $nights, $roomTypesData, $rooms, $specialOffers);

if(count($bookings) < 1) {
	$_SESSION['booking_error'] = MUST_SELECT_BOOKING;
	header('Location: available_rooms.php');
	mysql_close($link);
	return;
}

$contactDetailsUrl = $location . '_contact_details.php';

html_start(EXTRA_SERVICES);

echo <<<EOT

      <h1 class="page-title page-title-extra-services">
        $extraServices
      </h1>
      
      <div class="fluid-wrapper booking">
        <form class="update-summary" action="$contactDetailsUrl" data-refresh="json_update_summary.php" method="post">
          <fieldset>
            <section id="extras">
              <h1>$extras</h1>
              
              <ul>

EOT;


$services = loadServices($link);

foreach($services as $serviceId => $oneSvc) {
	if($oneSvc['free_service'] == 1) {
		continue;
	}
	$svcImg = '';
	if(!is_null($oneSvc['img']) and strlen($oneSvc['img']) > 0) {
		$svcImg = '<img src="' . constant('SERVICES_IMG_URL_' . strtoupper($location)) . $oneSvc['img'] . '">';
	}
	$price = $oneSvc['price'];
	$title = $oneSvc['title'];
	$description = $oneSvc['description'];
	$unitName = $oneSvc['unit_name'];
	if($unitName == '') {
		$unitName = OCCASION;
	}
	$pricePerOccasion = sprintf(PRICE_PER_OCCASION, formatMoney(convertCurrency($price, $oneSvc['currency'], $currency), $currency), $unitName);
	$fieldName = 'service_' . $location . '_' . $serviceId;
	$options = '';
	for($i = 0; $i < 20; $i++) {
		$selected = (isset($_SESSION[$fieldName]) and $_SESSION[$fieldName] == $i) ? ' selected="selected"' : '';
		$options .= "                      <option value=\"$i\"$selected>$i</option>\n";
	}
	echo <<<EOT

                <li class="clearfix">
                  $svcImg
                  
                  <h2>$title</h2>
                  
                  <p class="description">$description</p>
                  
                  <p class="price">
                    <strong>$pricePerOccasion</strong>
                  </p>
                  
                  <div class="fake-select">
                    <span class="value"></span>
                    <span class="open-select icon-down"></span>
                    <select id="$fieldName" name="$fieldName">
$options
                    </select>
                  </div>
                </li>

EOT;
}

echo <<<EOT

              </ul>
            </section>

EOT;

echo getBookingSummaryHtml(CONTINUE_BOOKING);
            
echo <<<EOT
          </fieldset>
        </form>
      </div>

EOT;


html_end();
mysql_close($link);


?>

