<?php

require('includes.php');
require('includes/common_booking.php');
require(RECEPCIO_BASE_DIR . 'room_booking.php');


$location = getLocation();

$link = db_connect($location);

$lang = getCurrentLanguage();
$currency = getCurrency();

$onloadScript = '';
if(isset($_SESSION['booking_error'])) {
	$onloadScript = 'alert(\'' . $_SESSION['booking_error'] . '\');';
	unset($_SESSION['booking_error']);
}

if(isset($_REQUEST['apartment'])) {
	$_SESSION['apartment'] = $_REQUEST['apartment'];
} elseif(!isset($_SESSION['apartment'])) {
	$_SESSION['apartment'] = 'no';
}


$afterBody = <<<EOT
    <div id='gallery'>
        <h1 class='gallery-title'>
        </h1>
          <span class='galleryClose' onClick="$('#gallery').fadeOut(); $('iframe.gallery').attr('src','');">X</span>
        <center>
        <iframe class='gallery'  frameborder='0' src=''></iframe>
        </center>
    </div>

EOT;

set_show_tripadvisor();

html_start(getLocationName($location), '', $onloadScript, $afterBody);

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
$aboutLocationDescr = constant('ABOUT_' . strtoupper($location) . '_DESCRIPTION');
$aboutLocationDescrExtra = constant('ABOUT_' . strtoupper($location) . '_DESCRIPTION_EXTRA');
$directionsToLocation = constant('DIRECTIONS_TO_' . strtoupper($location));
$addressValue = constant('ADDRESS_VALUE_' . strtoupper($location));
// constants are defined in config.php
$contactPhone = constant('CONTACT_PHONE_' . strtoupper($location));
$contactEmail = constant('CONTACT_EMAIL_' . strtoupper($location));
$contactFax = constant('CONTACT_FAX_' . strtoupper($location));
if(showApartments()) {
	$aboutLocation = constant('ABOUT_APARTMENTS');
	$aboutLocationDescr = constant('ABOUT_APARTMENTS_DESCRIPTION');
	$aboutLocationDescrExtra = constant('ABOUT_APARTMENTS_DESCRIPTION_EXTRA');
	$directionsToLocation = constant('DIRECTIONS_TO_APARTMENTS');
	$addressValue = constant('ADDRESS_VALUE_APARTMENTS');
	// constants are defined in config.php
	$contactPhone = constant('CONTACT_PHONE_APARTMENTS');
	$contactEmail = constant('CONTACT_EMAIL_APARTMENTS');
	$contactFax = constant('CONTACT_FAX_APARTMENTS');
}
$nights = NIGHTS;
$locationTitle = LOCATION_TITLE;
$howToGetHere = HOW_TO_GET_HERE;
$directions = DIRECTIONS;
$onlineRoutePlanner = ONLINE_ROUTE_PLANNER;
$addressTitle = ADDRESS_TITLE;
$publicTransport = PUBLIC_TRANSPORT;
$railwayStations = RAILWAY_STATIONS;
$airport = AIRPORT;
$internationalBusStation = INTERNATIONAL_BUS_STATION;

$rooms = ROOMS;
if(showApartments()) {
	$rooms = APARTMENTS;
}
$servicesTitle = SERVICES;
$freeServicesTitle = FREE_SERVICES;
$extraServicesTitle = SERVICES_FOR_EXTRA_FEE;
$linksTitle = LINKS;

$phone = PHONE;
$email = EMAIL;
$fax = FAX;

$more = MORE;
$photos = PHOTOS;

// constants are defined in config.php
$contactPhone = constant('CONTACT_PHONE_' . strtoupper($location));
$contactEmail = constant('CONTACT_EMAIL_' . strtoupper($location));
$contactFax = constant('CONTACT_FAX_' . strtoupper($location));

$roomTypesData = loadRoomTypes($link, $lang);

$today = date('Y-m-d');
$specialOfferSection = '';
$specialOffers = loadSpecialOffers(null, $today, $link, $lang);
$dateFormat = DATE_FORMAT;
if((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) {
	$dateFormat = str_replace('%e', '%#d', $dateFormat);
}
foreach($specialOffers as $spId => $so) {
	if($so['visible'] != 1) {
		continue;
	}
	if(showApartments() and !isSOForApartment($so, $roomTypesData)) {
		continue;
	}
	if(!showApartments() and !isSOForDormOrPrivate($so, $roomTypesData)) {
		continue;
	}
	$title = $so['title'];
	$descr = $so['text'];
	$offValue = sprintf(PERCENT_OFF, $so['discount_pct']);
	$dscountPct = $so['discount_pct'];
	$roomName = $so['room_name'];
	if(is_null($roomName)) {
		$roomName = getRoomTypeNames($so['room_type_ids'], $roomTypesData);
	}
	if(is_null($roomName)) {
		$roomName = EVERY_ROOM;
	}
	$startDate = strftime($dateFormat, strtotime($so['start_date']));
	$endDate = strftime($dateFormat, strtotime($so['end_date']));
	$offerForRoomBetweenDates = sprintf(FOR_ROOM_BETWEEN_DATES, $roomName, $startDate, $endDate);
	$specialOfferSection .= <<<EOT
            <div class='offer-box'>
                <div class='offer-box-cont'>
                    <div class="offerdesc">$title</div>
                    <table align='center'>
                        <tr>
                            <td class='offer-percent'>$dscountPct</td>
                            <td class='offer-percent2'><span style='font-size: 30px;'>%</span></td>
                        </tr>
                    </table>
                   <div class='offer-box-triangle'></div>
                </div>
				<div class='offer-box-desc'>
    			  $offerForRoomBetweenDates<br>
	    		  $descr
				</div>
            </div>

EOT;

}

$specialOffersTitle = SPECIAL_OFFERS;
$specialOfferExplain = SPECIAL_OFFER_EXPLAIN;
if(strlen($specialOfferSection) > 0) {
	$specialOfferSection = <<<EOT
        <section id="special-offers">
          <h1>$specialOffersTitle</h1>
$specialOfferSection
		  <div class='clearfix'></div>
          <p>$specialOfferExplain</p>
        </section>

EOT;
}

$availableRoomsUrl = $location . '_available_rooms.php';

$checkOutOurRooms = CHECKOUT_OUR_ROOMS;
$watchTheIntroVideo = WATCH_INTRO_VIDEO;

$awards = AWARDS;
$isApt = 0;
if($location == 'apartments') {
	$isApt = 1;
}
$sql = "SELECT * FROM awards WHERE is_apartment=$isApt ORDER BY _order";
$result = mysql_query($sql, $link);
$awardsHtml = '';
while($row = mysql_fetch_assoc($result)) {
	$awardsHtml .= '                        <a href="' . $row['url'] . '" target="_blank"><img class="footerAward" src="' . constant('AWARDS_IMG_URL_' . strtoupper($location)) . $row['img'] . '" alt="' . $row['name'] . '"></a>' . "\n";	
}



echo <<<EOT

<style>	@media screen and (max-width: 768px) { .hidden-xs { display: none }	}</style>
<iframe style='border: 0px; margin: 0px; width: 100%;' id='newcarousel' src='carousel.php?page=$location' frameborder='0' class='hidden-xs'></iframe>

<section id="checkin2" class="show1280">
    <form action="$availableRoomsUrl" method="GET">
       
            <table style='width: 100%'>
            <tr>
                <td><h2>$checkinDate</h2>
                    <input type="date" class='cifrom' id="from2" name="from" value="$fromDate">
                </td>
                <td>
                    <h2>$checkoutDate</h2>
                    <input type="date" class='cito' id="to2" name="to" value="$toDate">
                </td>
 
                <td>
                    <h2>&nbsp;</h2>
                    <button type="submit">$checkAvailability</button>
                </td>
            </tr>
            </table>
    </form>
  
</section>  

<section id="checkin3">
    
    <form action="$availableRoomsUrl" method="GET">
       <div class='centered'>
        <div class="field date from left">
          <h2>$checkinDate</h2>
          <input type="date" class='cifrom' id="from2" name="from" value="$fromDate">
        </div>

       
        <div class="field date to left">
          <h2>$checkoutDate</h2>
          <input type="date" class='cito' id="to2" name="to" value="$toDate">
        </div>

        <div class="field left submit">
            <h2>&nbsp;</h2>
          <button type="submit">$checkAvailability</button>
        </div>
        
        <div class='clearfix'></div>
       </div> 
            
    </form>
   
</section>  




      <div class="fluid-wrapper">
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
                <button type="submit">$checkAvailability</button>
              </div>
            </fieldset>
          </form>

        </section>

<div style="max-width:1000px;">

        <section id="about">
          <h1>$aboutLocation</h1>
          
		  <p>
			<strong>$aboutLocationDescr</strong>
            <span class="overlay-related">
              <span class="extra">
                <br><br>
              </span>
              <a class="open-overlay" href="" data-overlay-title="$aboutLocation" data-overlay-content-selector="#overlay-content-1">$more</a>
            </span>
          </p>
          
        <section id="awards">

              <h1>$awards</h1><br>
$awardsHtml
              <div class='clearfix'></div>

        </section>
        



          <div id='moodVideoControl'>
            <div class='whiteRight'><i class='fa fa-fw  fa-bed' style='margin-right: 10px; font-size: 30px;'></i><a href='#rooms'>$checkOutOurRooms</a></div>
            <div onClick='handleMoodVideo()' style='cursor: pointer'><i class='fa fa-fw fa-youtube-play vicon' style='margin-right: 10px;  font-size: 30px;'></i><span class='vtext1'>$watchTheIntroVideo</span><span class='vtext2'>close the intro video </span></div>
          </div>
          <div id='moodVideo' class='video-container'>
            <iframe src='https://www.youtube.com/embed/wJfQYUzmnqY?showinfo=0'></iframe>
          </div>

        </section>
        
        <section id="location" class="clearfix">
          <!-- h1><a href="#rooms">$rooms</a></h1 -->
          
          <p class="route">
              <a class="open-overlay" href="" data-overlay-title="$directionsToLocation" data-overlay-content-url="directions.php">$directions</a>
            <a class="open-overlay" href="" data-overlay-title="$onlineRoutePlanner" data-overlay-type="map" data-latitude="$latitude" data-longitude="$longitude">$howToGetHere</a>
          </p>

		  <div id="map-container">
            <div class="map" data-poi-url="poi-$location-$lang.json"></div>
		  </div>
          

		  <p class="hidden-xs">
            <br>
            <span id="overlay-content-1" class="overlay-content">
              $aboutLocationDescrExtra
            </span>
          </p>


<!--
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
-->

        </section>

$specialOfferSection

        <section class="rooms">
          <h1><a name="rooms">$rooms</a></h1>
          
EOT;


$details = DETAILS;
$close = CLOSE;
$gallery = GALLERY;

$locationName = getLocationName($location);
foreach($roomTypesData as $roomTypeId => $roomType) {
	if(showApartments() !== isApartment($roomType)) {
		continue;
	}
	if(isClientFromHU() and $roomType['num_of_beds'] > 5) {
		continue;
	}
	if(isDorm($roomType)) {
		$price = $roomType['price_per_bed'];
		$priceStartingFrom = sprintf(PRICE_STARTING_FROM_PER_BED, formatMoney(convertCurrency($price, 'EUR', $currency), getCurrency()));
	} elseif(isPrivate($roomType)) {
		$price = $roomType['price_per_room'];
		$priceStartingFrom = sprintf(PRICE_STARTING_FROM_PER_ROOM, formatMoney(convertCurrency($price, 'EUR', $currency), getCurrency()));
	} elseif(isApartment($roomType)) {
		$price = $roomType['price_per_room'];
		$priceStartingFrom = sprintf(PRICE_STARTING_FROM_PER_APARTMENT, formatMoney(convertCurrency($price, 'EUR', $currency), getCurrency()));
	}
	$name = $roomType['name'];
	$descr = $roomType['description'];
	$shortDescr = $roomType['short_description'];
	if(isClientFromHU() and $roomType['num_of_beds'] > 4) {
		$nob = $roomType['num_of_beds'];
		$roomType['num_of_beds'] = 4;
		$name = str_replace('5', '4', $name);
		$descr = str_replace('5', '4', $descr);
		$shortDescr = str_replace('5', '4', $shortDescr );
	}
	$sql = "SELECT * FROM room_images WHERE room_type_id=$roomTypeId";
	$result = mysql_query($sql, $link);
	$roomImg = '';
	if(mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_assoc($result)) {
			if(($row['default'] == 1) or (strlen($roomImg) < 1)) {
				$host = '';
				$baseDir = BASE_DIR;
				$savedFileName = getFName($row['filename']) . '_587_387.' . getFExt($row['filename']);
				if(file_exists($baseDir . 'img/rooms/' . $savedFileName)) {
					$roomImg = $host . 'img/rooms/' . $savedFileName;
				} else {
					$roomImg = $host . 'get_image.php?type=ROOM&width=587&height=387&file=' . $row['filename'] . '&save_file=' . $savedFileName;
				}
			}
		}
	}

	$extrasHtml = getExtrasHtml($location, $roomType['type']);
	echo <<<EOT
            <div class='roomCard'>
              
              <div class='left roomPic'><img src='$roomImg' alt='pic' class='imgResp open-gallery' data-gallery-title="$gallery" data-gallery-url="gallery.php?room_type_id=$roomTypeId"></div>
              <div class='roomHead left'>
                  <h2><a class='open-gallery' data-gallery-title="$gallery" data-gallery-url="gallery.php?room_type_id=$roomTypeId">$name</a></h2>
                  <strong>$shortDescr</strong><br>
                  $locationName
              </div>
              
              <div class='right roomButtonCont'>
                  <button class='roomButton'>
                      <span class='roomDetOpen'>$details</span>
                      <span class='roomDetClose'>$close</span>
                  </button>
              </div>
              
              <div class='clearfix'></div>

              <div class='roomDetails'> 
                    <img src='/img/expand.png' title='' alt='' class='right open-gallery' style='margin:10px;' data-gallery-title="$gallery" data-gallery-url="gallery.php?room_type_id=$roomTypeId" />
                    <div class='clearfix'></div>
                    <iframe class='roomSlider' src='gallery.php?room_type_id=$roomTypeId' frameborder='0'></iframe>
                    <div class='roomDetailsText'>$descr</div>
                    <div class='roomExtras'>
                      <ul class="extras">
$extrasHtml
                      </ul>
                    <div class='clearfix'></div>
                </div>
            </div>
        </div>

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
          <h1>$servicesTitle</h1>
          
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


function isSOForApartment($so, &$roomTypesData) {
	$forApartment = false;
	if(strlen($so['room_type_ids']) > 1) {
		foreach(explode(",", $so['room_type_ids']) as $rtId) {
			if(isApartment($roomTypesData[$rtId])) {
				$forApartment = true;
				break;
			}
		}
	} else {
		$forApartment = true;
	}
	return $forApartment;
}

function isSOForDormOrPrivate($so, &$roomTypesData) {
	$forApartment = false;
	if(strlen($so['room_type_ids']) > 1) {
		foreach(explode(",", $so['room_type_ids']) as $rtId) {
			if(isDorm($roomTypesData[$rtId]) or isPrivate($roomTypesData[$rtId])) {
				$forApartment = true;
				break;
			}
		}
	} else {
		$forApartment = true;
	}
	return $forApartment;
}




?>
