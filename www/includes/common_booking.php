<?php

$extras = array(
	'LODGE' => array(
		'DORM' => array(
			array('img' => '/img/icon/svc_icon_free_wifi.png', 'name' => FREE_WIFI),
			array('img' => '/img/icon/svc_icon_towel_included.png', 'name' => LINEN_AND_TOWEL_INCLUDED),
			array('img' => '/img/icon/svc_icon_reading_lights.png', 'name' => READING_LIGHTS),
			array('img' => '/img/icon/svc_icon_lockers.png', 'name' => LOCKERS),
			array('img' => '/img/icon/svc_icon_smoke_fee_environment.png', 'name' => SMOKE_FREE_ENVIRONMENT),
			array('img' => '/img/icon/svc_icon_air_conditioning.png', 'name' => AIR_CONDITIONING),
			array('img' => '/img/icon/svc_icon_air_hair_dryer_in_bath.png', 'name' => HAIR_DRYER_IN_BATH)
		),
		'PRIVATE' => array(
			array('img' => '/img/icon/svc_icon_free_wifi.png', 'name' => FREE_WIFI),
			array('img' => '/img/icon/svc_icon_flat_screen_tv.png', 'name' => FLAT_SCREEN_TV),
			array('img' => '/img/icon/svc_icon_reading_lights.png', 'name' => READING_LIGHTS),
			array('img' => '/img/icon/svc_icon_smoke_fee_environment.png', 'name' => SMOKE_FREE_ENVIRONMENT),
			array('img' => '/img/icon/svc_icon_air_conditioning.png', 'name' => AIR_CONDITIONING),
			array('img' => '/img/icon/svc_icon_air_hair_dryer_in_bath.png', 'name' => HAIR_DRYER_IN_BATH),
			array('img' => '/img/icon/svc_icon_towel_included.png', 'name' => LINEN_AND_TOWEL_INCLUDED),
		)
	),
	'HOSTEL' => array(
		'DORM' => array(
			array('img' => '/img/icon/svc_icon_free_wifi.png', 'name' => FREE_WIFI),
			array('img' => '/img/icon/svc_icon_towel_included.png', 'name' => LINEN_AND_TOWEL_INCLUDED),
			array('img' => '/img/icon/svc_icon_lockers.png', 'name' => LOCKERS),
			array('img' => '/img/icon/svc_icon_reading_lights.png', 'name' => READING_LIGHTS),
			array('img' => '/img/icon/svc_icon_no_bunk_beds.png', 'name' => NO_BUNK_BEDS),
			array('img' => '/img/icon/svc_icon_smoke_fee_environment.png', 'name' => SMOKE_FREE_ENVIRONMENT),
			array('img' => '/img/icon/svc_icon_air_hair_dryer_for_request.png', 'name' => HAIR_DRYER_FOR_REQUEST)
		),
		'PRIVATE' => array(
			array('img' => '/img/icon/svc_icon_free_wifi.png', 'name' => FREE_WIFI),
			array('img' => '/img/icon/svc_icon_reading_lights.png', 'name' => READING_LIGHTS),
			array('img' => '/img/icon/svc_icon_smoke_fee_environment.png', 'name' => SMOKE_FREE_ENVIRONMENT),
			array('img' => '/img/icon/svc_icon_cable_tv.png', 'name' => CABLE_TV),
			array('img' => '/img/icon/svc_icon_air_hair_dryer_for_request.png', 'name' => HAIR_DRYER_FOR_REQUEST),
			array('img' => '/img/icon/svc_icon_towel_included.png', 'name' => LINEN_AND_TOWEL_INCLUDED)
		)
	)
);


function getExtrasHtml($location, $typeOfRoom) {
	global $extras;
	$extrasHtml = '';
	foreach($extras[strtoupper($location)][$typeOfRoom] as $oneExtra) {
		$extraImg = $oneExtra['img'];
		$extraName = $oneExtra['name'];
		$extrasHtml .=<<<EOT
                  <li>
                    <img width="52" height="51" src="$extraImg">
                    <div>
                      <p class="name">$extraName</p>
                    </div>
                  </li>

EOT;
	}
	return $extrasHtml;
}

function getCarousel($location, $lang) {
	$slides = '';
	$carouselIdx = 1;
	while(defined("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_TITLE")) {
		$carouselTitle = constant("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_TITLE");
		$bgImage = "img/carousel-$location-$carouselIdx.jpg";
		if(file_exists(BASE_DIR . "img/carousel-$location-$carouselIdx-" . $lang . ".jpg")) {
			$bgImage = "img/carousel-$location-$carouselIdx-" . $lang . ".jpg";
		}
		$bgImage = BASE_URL . $bgImage;
		$slides .= <<<EOT
			  <li style="background-image: url($bgImage)">
				<p class="title">$carouselTitle</p>
				<ul class="tooltips">

EOT;
		$pointIdx = 1;
		while(defined("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_POINT_" . $pointIdx . "_TITLE")) {
			$carouselPointLeft = constant("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_POINT_" . $pointIdx . "_LEFT");
			$carouselPointTop = constant("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_POINT_" . $pointIdx . "_TOP");
			$carouselPointTitle = constant("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_POINT_" . $pointIdx . "_TITLE");
			$carouselPointDescription = constant("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_POINT_" . $pointIdx . "_DESCRIPTION");
			$slides .= <<<EOT
				  <li style="left: $carouselPointLeft; top: $carouselPointTop;">
					<div>
					  <p class="title">$carouselPointTitle</p>
					  <p class="description">$carouselPointDescription</p>
					</div>
				  </li>

EOT;
			$pointIdx += 1;
		} // end of loop one slides points
		$slides .= <<<EOT
				</ul>
			  </li>
EOT;
		$carouselIdx += 1;
	} // end of loop through slides

	return $slides;
}


function loadServices($link) {
	$services = array();
	$lang = getCurrentLanguage();
	$sql = "SELECT s.*, t.value AS title, d.value AS description , u.value AS unit_name FROM services s INNER JOIN lang_text t ON (s.id=t.row_id AND t.table_name='services' AND t.column_name='title' AND t.lang='$lang') INNER JOIN lang_text d ON (s.id=d.row_id AND d.table_name='services' AND d.column_name='description' AND d.lang='$lang') LEFT OUTER JOIN lang_text u ON (s.id=u.row_id AND u.table_name='services' AND u.column_name='unit_name' AND u.lang='$lang') ORDER BY s._order";
	$result = mysql_query($sql, $link);
	while($row = mysql_fetch_assoc($result)) {
		$services[$row['id']] = $row;
	}

	return $services;
}


function getBookingSummaryHtml($buttonText) {
	$bookingSummary = BOOKING_SUMMARY;
	$total = TOTAL;
	$extraServices = ADD_EXTRA_SERVICES;
	$chooseYourRoom = CHOOSE_YOUR_ROOM;
	$submitYourDetails = SUBMIT_YOUR_DETAILS;
	$noBookingFee = NO_BOOKING_FEE;
	$noCreditCardNeeded = NO_CREDIT_CARD_NEEDED;
	$allTaxIncluded = ALL_TAX_INCLUDED;

	$html = <<<EOT
	        <section id="booking-summary" data-top="30px">
              <h1>$bookingSummary</h1>
              
              <ul class="details">
                <li class="rooms inactive" data-label="$chooseYourRoom">
                  $chooseYourRoom
                </li>
                <li class="services inactive" data-label="$extraServices">
                  $extraServices
                </li>
                <li class="contact inactive" data-label="$submitYourDetails">
                  $submitYourDetails
                </li>
              </ul>
              
              <div class="next">
                <h2>$total:</h2>
                <p class="total">€0</p>
                <p class="info" style="font-size: 110%;">$noBookingFee</p>
                <p class="info" style="font-size: 110%;">$noCreditCardNeeded</p>
                <p class="info" style="font-size: 110%;">$allTaxIncluded</p>

                <button type="submit">$buttonText</button>
              </div>
			</section>

EOT;

	return $html;

}

function getBookedServices($services, $location) {
	$retVal = array();
	foreach($services as $serviceId => $service) {
		$occasions = 0;
		$key = 'service_' . $location . '_' . $serviceId;
		if(isset($_REQUEST[$key])) {
			$_SESSION[$key] = $_REQUEST[$key];
		}
		if(isset($_SESSION[$key])) {
			$occasions = $_SESSION[$key];
		}

		if($occasions > 0) {
			$title = $service['title'];
			$price = $service['price'] * $occasions;
			$serviceCurrency = $service['currency'];
			$unitName = $service['unit_name'];
			$retVal[] = array('title' => $title, 'occasion' => $occasions, 'price' => $price, 'currency' => $serviceCurrency, 'serviceId' => $serviceId, 'unit_name' => $unitName);
		}

	}
	return $retVal;
}

?>
