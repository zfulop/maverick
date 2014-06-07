<?php

require('includes.php');
require('includes/common_booking.php');
require('../recepcio/room_booking.php');


$location = getLocation();

$link = db_connect($location);

$lang = getCurrentLanguage();
$currency = getCurrency();

$onloadScript = '';
if(isset($_SESSION['booking_error'])) {
	$onloadScript = 'alert(\'' . $_SESSION['booking_error'] . '\');';
	unset($_SESSION['booking_error']);
}

html_start(constant('LOCATION_NAME_' . strtoupper($location)), '', $onloadScript);

$checkin = CHECKIN;
$checkinDate = CHECKIN_DATE;
$checkoutDate = CHECKOUT_DATE;
$from = FROM_DATE;
$to = TO_DATE;
$latitude = constant('LATITUDE_' . strtoupper($location));
$longitude = constant('LONGITUDE_' . strtoupper($location));
if(isset($_SESSION['from_date'])) {
	$fromDate = $_SESSION['from_date'];
} else {
	$fromDate = date('Y-m-d');
}
if(isset($_SESSION['to_date'])) {
	$toDate = $_SESSION['to_date'];
} else {
	$toDate = date('Y-m-d', strtotime(date('Y-m-d') . '+1 day'));
}

$checkAvailability = CHECK_AVAILABILITY;
$aboutLocation = constant('ABOUT_' . strtoupper($location));
$aboutLocationDescr = constant(strtoupper($location) . '_SHORT_DESCRIPTION');
$aboutLocationDescrExtra = constant('ABOUT_' . strtoupper($location) . '_DESCRIPTION_EXTRA');
$nights = NIGHTS;
$locationTitle = LOCATION_TITLE;
$howToGetHere = HOW_TO_GET_HERE;
$directions = DIRECTIONS;
$directionsToLocation = constant('DIRECTIONS_TO_' . strtoupper($location));
$onlineRoutePlanner = ONLINE_ROUTE_PLANNER;
$addressTitle = ADDRESS_TITLE;
$addressValue = constant('ADDRESS_VALUE_' . strtoupper($location));
$publicTransport = PUBLIC_TRANSPORT;
$railwayStations = RAILWAY_STATIONS;
$airport = AIRPORT;
$internationalBusStation = INTERNATIONAL_BUS_STATION;

$rooms = ROOMS;
$services = SERVICES;
$freeServicesTitle = FREE_SERVICES;
$extraServicesTitle = SERVICES_FOR_EXTRA_FEE;
$linksTitle = LINKS;

$phone = PHONE;
$email = EMAIL;
$fax = FAX;

$more = MORE;
$awards = AWARDS;
$viewAwards = VIEW_AWARDS;
$photos = PHOTOS;
$oneAwardHtml = '';
$sql = "SELECT a.*, d.value AS description FROM awards a INNER JOIN lang_text d ON (d.table_name='awards' AND d.column_name='description' AND d.row_id=a.id and d.lang='$lang')";
$result = mysql_query($sql, $link);
if(mysql_num_rows($result) > 0) {
	$idx = rand(0, mysql_num_rows($result)-1);
	$row = null;
	for($i = 0; $i <= $idx; $i++) {
		$row = mysql_fetch_assoc($result);
	}
	$oneAwardHtml = "<img src=\"" . constant('AWARDS_IMG_URL_' . strtoupper($location)) . $row['img'] . "\">" . $row['description'];
}


// constants are defined in config.php
$contactPhone = constant('CONTACT_PHONE_' . strtoupper($location));
$contactEmail = constant('CONTACT_EMAIL_' . strtoupper($location));
$contactFax = constant('CONTACT_FAX_' . strtoupper($location));

$slides = getCarousel($location, $lang);

$roomTypesData = loadRoomTypes($link);

$today = date('Y-m-d');
$specialOfferSection = '';
$specialOffers = loadSpecialOffers("end_date>'$today'", $link, $lang);
$dateFormat = DATE_FORMAT;
if((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) {
	$dateFormat = str_replace('%e', '%#d', $dateFormat);
}
foreach($specialOffers as $spId => $so) {
	if($so['visible'] != 1) {
		continue;
	}
	$title = $so['title'];
	$descr = $so['text'];
	$offValue = sprintf(PERCENT_OFF, $so['discount_pct']);
	$roomName = getRoomTypeNames($so['room_type_ids'], $roomTypesData);
	if(is_null($roomName)) {
		$roomName = EVERY_ROOM;
	}
	$startDate = strftime($dateFormat, strtotime($so['start_date']));
	$endDate = strftime($dateFormat, strtotime($so['end_date']));
	$offerForRoomBetweenDates = sprintf(FOR_ROOM_BETWEEN_DATES, $roomName, $startDate, $endDate);
	$specialOfferSection .= <<<EOT
<li>
              <h2>$offValue</h2>
              <h3>$title</h3>
			  <p>$offerForRoomBetweenDates</p><br>
			  <p>$descr</p>
            </li>
EOT;

}

$specialOffersTitle = SPECIAL_OFFERS;
$specialOfferExplain = SPECIAL_OFFER_EXPLAIN;
if(strlen($specialOfferSection) > 0) {
	$specialOfferSection = <<<EOT
        <section id="special-offers">
          <h1>$specialOffersTitle</h1>
          <ul>
$specialOfferSection
          </ul>
          <p>$specialOfferExplain</p>
        </section>

EOT;
}

$availableRoomsUrl = $location . '_available_rooms.php';

if (isset($_GET['test'])) {
  $banners = <<<EOT
    <section id="banners">
      <ul class="clearfix">
        <li><img width="78" height="78" src="/img/europes_famous_hostels.jpg"></li>
        <li><img width="117" height="78" src="/img/europes_famous_hostels.jpg"></li>
        <li><img width="39" height="78" src="/img/europes_famous_hostels.jpg"></li>
        <li><img width="100" height="78" src="/img/europes_famous_hostels.jpg"></li>
        <li><img width="70" height="78" src="/img/europes_famous_hostels.jpg"></li>
      </ul>
    </section>
EOT;
} else {
  $banners = '';
}

echo <<<EOT


      <section id="carousel" class="small">
        <ul class="slides">
$slides
        </ul>
        
        <nav class="navigation">
          <span class="prev"></span>
          <span class="next"></span>
        </nav>
      </section>




      <div class="fluid-wrapper">
        $banners
        <section id="checkin" class="box"  data-top="50%">
          <h1>$checkin</h1>

          <form action="$availableRoomsUrl" method="GET">
            <fieldset>
              <h2>$checkinDate</h2>
              <div class="field date from clearfix">
                <label for="from">$from:</label>
                <input type="date" id="from" name="from" value="$fromDate">
              </div>
              
              <h2>$checkoutDate</h2>
              <div class="field date to clearfix">
                <label for="to">$to:</label>
                <input type="date" id="to" name="to" value="$toDate">
              </div>
              
              <div class="submit">
                <button type="submit">Check availability</button>
              </div>
            </fieldset>
          </form>

        </section>

        <section id="about">
          <h1>$aboutLocation</h1>
          
		  <p>
		    <strong>$aboutLocationDescr</strong>
            <span class="overlay-related">
              <span class="extra">
                <br><br>
              </span>
              <a class="open-overlay" href="" data-overlay-title="$aboutLocation" data-overlay-content-selector="#overlay-content-1">$more</a>
              <span id="overlay-content-1" class="overlay-content">
			    $aboutLocationDescrExtra
              </span>
            </span>

          </p>

EOT;

if(strlen($oneAwardHtml) > 0) {
	echo <<<EOT
		  <h2>$awards</h2>
          <p class="more">
            <a class="open-overlay" href="" data-overlay-title="$awards" data-overlay-content-url="awards.php">$viewAwards</a>
          </p>
          <p>
            $oneAwardHtml
          </p>

EOT;
}

if (isset($_GET['test'])) {
  $video = <<<EOT
    <h1>Video</h1>
    <div class="video-wrapper">
      <div class="video-container">
        <iframe class="video" width="560" height="315" src="//www.youtube.com/embed/R-emTwEMGnA?showinfo=0&amp;theme=light&amp;modestbranding=1" allowfullscreen></iframe>
      </div>
    </div>
EOT;
  $specialOfferSection = <<<EOT
    <section id="special-offers" class="rewamp">
      <h1>Special offers</h1>
      <ul>
        <li>
          <h3>5 NIGHTS = 15% OFF!</h3>
          <p>
            <span>From: Apr 27</span>
            <span>To: May 31</span>
          </p>
          <p>
            <strong>Standard 5 bedroom ensuite</strong>
          </p>
          <p>
            Book your private ensuite room for 5 nights and enjoy 15% off from the total price!
          </p>
        </li>
        <li>
          <h3>5 NIGHTS = 15% OFF!</h3>
          <p>
            <span>From: Apr 27</span>
            <span>To: May 31</span>
          </p>
          <p>
            <strong>Standard 5 bedroom ensuite</strong>
          <p>
          </p>
            Book your private ensuite room for 5 nights and enjoy 15% off from the total price! test row
          </p>
        </li>
        <li>
          <h3>5 NIGHTS = 15% OFF!</h3>
          <p>
            <span>From: Apr 27</span>
            <span>To: May 31</span>
          </p>
          <p>
            <strong>Standard 5 bedroom ensuite</strong>
          <p>
          </p>
            Book your private ensuite room for 5 nights and enjoy 15% off from the total price!
          </p>
        </li>
        <li>
          <h3>5 NIGHTS = 15% OFF!</h3>
          <p>
            <span>From: Apr 27</span>
            <span>To: May 31</span>
          </p>
          <p>
            <strong>Standard 5 bedroom ensuite</strong>
          <p>
          </p>
            Book your private ensuite room for 5 nights and enjoy 15% off from the total price!
          </p>
        </li>
        <li>
          <h3>5 NIGHTS = 15% OFF!</h3>
          <p>
            <span>From: Apr 27</span>
            <span>To: May 31</span>
          </p>
          <p>
            <strong>Standard 5 bedroom ensuite</strong>
          <p>
          </p>
            Book your private ensuite room for 5 nights and enjoy 15% off from the total price!
          </p>
        </li>
        <li>
          <h3>5 NIGHTS = 15% OFF!</h3>
          <p>
            <span>From: Apr 27</span>
            <span>To: May 31</span>
          </p>
          <p>
            <strong>Standard 5 bedroom ensuite</strong>
          <p>
          </p>
            Book your private ensuite room for 5 nights and enjoy 15% off from the total price!
          </p>
        </li>
      </ul>
      <p class="disclaimer">
        The discount will be automatically calculated into the price if you select the right period.
      </p>
    </section>
EOT;
} else {
  $video = '';
}

echo <<<EOT

        </section>
        
        <section id="location" class="clearfix">
          $video
          <h1><a href="#rooms">$rooms</a></h1>
          
          <p class="route">
              <a class="open-overlay" href="" data-overlay-title="$directionsToLocation" data-overlay-content-url="directions.php">$directions</a>
            <a class="open-overlay" href="" data-overlay-title="$onlineRoutePlanner" data-overlay-type="map" data-latitude="$latitude" data-longitude="$longitude">$howToGetHere</a>
          </p>

		  <div id="map-container">
            <div class="map" data-poi-url="poi-$location-$lang.json"></div>
		  </div>
          
          <div class="address">
            <p class="condensed">
              <strong>$addressTitle</strong>
              $addressValue
            </p>
            <p>
              <strong>$phone</strong>
              $contactPhone
            </p>
            <p>
              <strong>$email</strong>
              <a href="mailto:$contactEmail">$contactEmail</a>
            </p>
            <p>
              <strong>$fax</strong>
              $contactFax
			</p>
		  </div>

        </section>

$specialOfferSection

        <section class="rooms">
          <h1><a name="rooms">$rooms</a></h1>
          
          <ul>

EOT;

$facilities = FACILITIES;
$close = CLOSE;
$gallery = GALLERY;

$locationName = constant('LOCATION_NAME_' . strtoupper($location));
foreach($roomTypesData as $roomTypeId => $roomType) {
	$price = ($roomType['type'] == 'DORM' ? $roomType['price_per_bed'] : $roomType['price_per_room']);
	$name = $roomType['name'];
	$descr = $roomType['description'];
	$shortDescr = $roomType['short_description'];
	$price = convertCurrency($price, 'EUR', getCurrency());
	$priceStartingFrom = sprintf($roomType['type'] == 'DORM' ? PRICE_STARTING_FROM_PER_BED : PRICE_STARTING_FROM_PER_ROOM, formatMoney(convertCurrency($price, 'EUR', $currency), getCurrency()));
	$sql = "SELECT * FROM room_images WHERE room_type_id=$roomTypeId";
	$result = mysql_query($sql, $link);
	$roomImg = '';
	if(mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_assoc($result)) {
			if(($row['default'] == 1) or (strlen($roomImg) < 1)) {
				$host = '';
				if($location == 'hostel') {
					$host = 'http://img.maverickhostel.com/';
				}
				$roomImg = $host . 'get_image.php?type=ROOM&width=587&height=387&file=' . $row['filename'];
			}
		}
	}

	$extrasHtml = getExtrasHtml($location, $roomType['type']);
	echo <<<EOT
			<li>
			  <div class="card clearfix">
                <h2>
                  <a href="">$name</a>
                </h2>            
                <a class="open-overlay" href="" data-overlay-title="$gallery" data-overlay-gallery-url="gallery.php?room_type_id=$roomTypeId">
                  <img src="$roomImg" width="587" height="387">
                </a>
           
                <div class="data">
                  <p class="type condensed">
                    <a class="open-overlay" href="" data-overlay-title="$gallery" data-overlay-gallery-url="gallery.php?room_type_id=$roomTypeId">$photos</a>
					<strong>$shortDescr</strong>
					$locationName
                  </p>
                  <p class="price condensed">$priceStartingFrom</p>
                </div>
              
                <p class="details">
				  <a class="open" href="">$facilities</a>
				  <a class="close" href="">$close</a>
                </p>
              </div>

              <div class="extra clearfix">
				<p class="details">
                  $descr
                </p>
                
                <ul class="extras">
$extrasHtml
				</ul>
              </div>
            </li>

EOT;
}

$services = loadServices($link);

$freeServices = '';
$payingServices = '';
foreach($services as $sid => $service) {
	if($service['free_service'] == 1) {
		$freeServices .= "              <li>" . $service['title'] . "</li>\n";
	} else {
		$payingServices .= "              <li>" . $service['title'] . "</li>\n";
	}
}

echo <<<EOT
		  </ul>
		</section>

        <section id="services" class="clearfix">
          <h1>$services</h1>
          
          <div>
            <h2>$freeServicesTitle</h2>
            
			<ul>
$freeServices
            </ul>
          </div>
          
          <div>
            <h2>$extraServicesTitle</h2>
            
            <ul>
$payingServices
            </ul>
          </div>
        </section>
        <section id="services" class="clearfix">
          <div>
            <h2>$linksTitle</h2>
            <a href="http://www.momondo.com" target="_blank">www.momondo.com</a>
            <p>If you're looking for flights to Budapest, try Momondo's free flight search engine</p>
          </div>
        </section>
      </div>

EOT;

html_end();
mysql_close($link);


function getRoomTypeNames($roomTypeIds, &$roomTypesData) {
	$retVal = '';
	if(strlen($roomTypeIds) > 1) {
		foreach(explode(",", $roomTypeIds) as $rtId) {
			$retVal .= $roomTypesData[$rtId]['name'] . ', ';
		}
		$retVal = substr($retVal, 0, -2);
	} else {
		$retVal = null;
	}

	return $retVal;
}

?>
