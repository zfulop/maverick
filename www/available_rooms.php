<?php

require('includes.php');
require('includes/common_booking.php');
require(RECEPCIO_BASE_DIR . 'room_booking.php');


// If a new search is executed from the availability screen, we have to adjust the location 
// and the apartment setting accordingly (location can be hostel,lodge,apartment)


$location = getLocation();
$lang = getCurrentLanguage();

if(isset($_REQUEST['source'])) {
	$_SESSION['booking_source'] = $_REQUEST['source'];
}

$_SESSION['booking_status'] = array('rooms');
if(isset($_REQUEST['from'])) {
	$_SESSION['from_date'] = $_REQUEST['from'];
	$_SESSION['to_date'] = $_REQUEST['to'];
	$_SESSION['nights'] = round((strtotime($_SESSION['to_date']) - strtotime($_SESSION['from_date'])) / (60*60*24));
}

if($_SESSION['from_date'] < date('Y-m-d')) {
	$_SESSION['booking_error'] = BOOKING_DATE_MUST_BE_IN_THE_FUTURE;
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}

if($_SESSION['to_date'] <= date('Y-m-d')) {
	$_SESSION['booking_error'] = BOOKING_DATE_MUST_BE_IN_THE_FUTURE;
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}

if($_SESSION['to_date'] <= $_SESSION['from_date']) {
	$_SESSION['booking_error'] = CHECKOUT_DATE_MUST_BE_AFTER_CHECKIN_DATE;
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}


$link = db_connect($location);


$minMax = getMinMaxStay($_SESSION['from_date'], $_SESSION['to_date'], $link);
if(!is_null($minMax) and $minMax['min_stay'] > $_SESSION['nights']) {
	$_SESSION['booking_error'] = sprintf(FOR_SELECTED_DATE_MIN_STAY, $minMax['min_stay']);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
if(!is_null($minMax) and !is_null($minMax['max_stay']) and  $minMax['max_stay'] < $_SESSION['nights']) {
	$_SESSION['booking_error'] = sprintf(FOR_SELECTED_DATE_MAX_STAY, $minMax['max_stay']);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}

$arriveDateTs = strtotime($_SESSION['from_date']);
$arriveDate = $_SESSION['from_date'];
$dateFormat = DATE_FORMAT;
if((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) {
	$dateFormat = str_replace('%e', '%#d', $dateFormat);
}
$arriveDateStr = strftime($dateFormat, $arriveDateTs);
$nights = $_SESSION['nights'];
$lastNightTs = strtotime($_SESSION['from_date'] . " +" . ($nights-1) . " day");
$lastNight = date('Y-m-d', $lastNightTs);


$checkin = CHECKIN;
$checkinDate = CHECKIN_DATE;
$checkoutDate = CHECKOUT_DATE;
$from = FROM_DATE;
$to = TO_DATE;
$fromDate = $_SESSION['from_date'];
$toDate = $_SESSION['to_date'];

$dateFormat = DATE_FORMAT_LONG;
if((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) {
	$dateFormat = str_replace('%e', '%#d', $dateFormat);
}
$arriveLongDate = strftime($dateFormat, strtotime($fromDate));
$departLongDate = strftime($dateFormat, strtotime($toDate));

$availableRooms = AVAILABILITY;
$arriveAt = sprintf(FROM_TO_DATES, $arriveLongDate, $departLongDate);
$specialOffer = SPECIAL_OFFER;
$changeDates = CHANGE_DATES;
$nightsTitle = NIGHTS;
$chooseLocation = CHOOSE_LOCATION;
$lodgeLocationSelected = ($location == 'lodge' ? ' selected="selected"' : '');
$lodgeTitle = LOCATION_NAME_LODGE;
$hostelLocationSelected = ($location == 'hostel' ? ' selected="selected"' : '');
$hostelTitle = LOCATION_NAME_HOSTEL;
$apartmentLocationSelected = ($location == 'apartments' ? ' selected="selected"' : '');
$apartmentTitle = LOCATION_NAME_APARTMENTS;
$checkAvailability = CHECK_AVAILABILITY;


$nightsOptions = "";
for($i = 1; $i < 15; $i++) {
	$nightsOptions .= "                    <option value=\"$i\"" . ($i == $nights ? ' selected="selected"' : '') . ">$i</option>\n";
}

$specialOffers = loadSpecialOffers($arriveDate,$lastNight, $link, $lang);
/*
$specialOfferSection = "";
foreach($specialOffers as $soId => $so) {
	if(($so['nights'] == ($nights+1)) and is_null($so['room_type_ids'])) {
		$descr = $so['title'];
		$specialOfferSection .= <<<EOT
          <div>
            <h1>$specialOffer</h1>
            <p>$descr</p>
          </div>
EOT;

	}
}

if(strlen($specialOfferSection) > 0) {
	$specialOfferSection = <<<EOT
		<section id="special-offer" class="clearfix">
$specialOfferSection
        </section>

EOT;
}
*/

$roomTypesData = loadRoomTypes($link, $lang);

$rooms = loadRooms(date('Y', $arriveDateTs), date('m', $arriveDateTs), date('d', $arriveDateTs), date('Y', $lastNightTs), date('m', $lastNightTs), date('d', $lastNightTs), $link, $lang);
foreach($rooms as $roomId => $roomData) {
	foreach($roomData['room_types']	as $roomTypeId => $roomTypeName) {
		processRoomData($arriveDateTs, $nights, $roomData, $roomTypesData[$roomTypeId]);
	}
}

uasort($roomTypesData, 'sortRoomsByAvailOrder');

$afterBody = <<<EOT
    <div id='gallery'>
        <h1 class='gallery-title'></h1>
        <span class='galleryClose' onClick="$('#gallery').fadeOut(); $('iframe.gallery').attr('src','');">X</span>
        <center>
        <iframe class='gallery'  frameborder='0' src=''></iframe>
        </center>
    </div>

EOT;

html_start(AVAILABLE_ROOMS . ' - ' . getLocationName($location), '', '', $afterBody);

if(isset($_SESSION['booking_error'])) {
	$msg = $_SESSION['booking_error'];
	unset($_SESSION['booking_error']);
	echo <<<EOT
	  <script type="text/javascript">
        alert('$msg');
      </script>

EOT;
}

echo <<<EOT

      <h1 class="page-title page-title-availability">
        $availableRooms
      </h1>
      
      <section id="checkin3">
         <form class="filter" action="available_rooms.php" method="GET">
           <div class='centered'>
             <div class="field date from ">
               <input type="date" class='cifrom from' id="from2" name="from" value="$fromDate">
             </div>
       
             <div class="field date to ">
               <input type="date" class='cito to' id="to2" name="to" value="$toDate">
             </div>

             <div class="fake-select clearfix">
                <span class="value">$chooseLocation</span>
                <span class="open-select icon-down"></span>
                <select name="location">
                  <option value="">$chooseLocation</option>
                  <option value="lodge"$lodgeLocationSelected>$lodgeTitle</option>
                  <option value="hostel"$hostelLocationSelected>$hostelTitle</option>
                  <option value="apartments"$apartmentLocationSelected>$apartmentTitle</option>
                </select>
             </div>          
             <br>  
             <div class="field  submit">
               <button type="submit">$checkAvailability</button>
             </div>

             <div class='clearfix'></div>
           </div> 
         </form>
      </section>


EOT;


$roomsTitle = ROOMS;
$details = DETAILS;
$locationName = getLocationName($location);
$extraServicesUrl = $location . '_extra_services.php';

echo <<<EOT

      <div class="fluid-wrapper fluid-wrapper2"><div style="max-width: 1000px;">

        <form class="common-form update-summary" action="$extraServicesUrl" data-refresh="json_update_summary.php" method="post">
          <section class="rooms">
            <h1><a name="rooms">$roomsTitle</a></h1>

EOT;

foreach($roomTypesData as $roomTypeId => $roomType) {
	if(showApartments() !== isApartment($roomType) and $location != 'lodge') {
		continue;
	}
	if($location == 'hostel' and isClientFromHU() and $roomType['num_of_beds'] > 5) {
		continue;
	}

	$html = getRoomHtml($roomType, $roomTypeId, $nights, $arriveDate, $specialOffers, $link);
}

echo <<<EOT
          </section>

EOT;

echo getBookingSummaryHtml(CONTINUE_BOOKING, 1);

echo <<<EOT
        </form>
      </div></div>

EOT;

html_end();
mysql_close($link);


function processRoomData($arriveTS, $nights, &$roomData, &$roomType) {
	$oneDayTS = $arriveTS;
	$type = $roomData['type'];
	$minAvailBeds = $roomType['num_of_beds'];
	$totalPrice = 0;
	for($i = 0; $i < $nights; $i++) {
		$currYear = date('Y', $oneDayTS);
		$currMonth = date('m', $oneDayTS);
		$currDay = date('d', $oneDayTS);
		$oneDay =  date('Y/m/d', $oneDayTS);
		$availBeds = getNumOfAvailBeds($roomData, $oneDay);
		// echo "For room:  " . $roomData['name'] . " (room type: " . $roomData['room_type_id'] . ") for day: $oneDay, there are $availBeds available beds<br>\n";
		$minAvailBeds = min($minAvailBeds, $availBeds);
		$oneDayTS = strtotime(date('Y-m-d',$oneDayTS) . ' +1 day');
	}
	if(isDorm($roomData)) {
		$totalPrice += getBedPrice($currYear, $currMonth, $currDay, $roomData);
	} elseif(isPrivate($roomData)) {
		$totalPrice += getRoomPrice($currYear, $currMonth, $currDay, $roomData);
	} elseif(isApartment($roomData)) {
		$totalPrice += getRoomPrice($currYear, $currMonth, $currDay, $roomData);
	}

	if((isPrivate($roomData) or isApartment($roomData)) and $minAvailBeds < $roomData['num_of_beds']) {
		$minAvailBeds = 0;
	}

	//set_debug("For room type: " . $roomType['name'] . ' adding available beds of ' . $minAvailBeds . ' with daily price: ' . ($totalPrice / $nights));

	if(!isset($roomType['num_of_beds_avail'])) {
		$roomType['num_of_beds_avail'] = 0;
	}
	if(!isset($roomType['num_of_rooms_avail'])) {
		$roomType['num_of_rooms_avail'] = 0;
	}
	if($minAvailBeds == $roomData['num_of_beds']) {
		$roomType['num_of_rooms_avail'] += 1;
	}

	$roomType['num_of_beds_avail'] += $minAvailBeds;
	$roomType['price'] = (getPrice($arriveTS, $nights, $roomData, 1) / $nights);
	if(isApartment($roomType)) {
		for($i=2; $i<= $roomType['num_of_beds']; $i++) {
			$roomType['price_' . $i] = (getPrice($arriveTS, $nights, $roomData, $i) / $nights);
		}
	}
}



function sortRoomsByAvailOrder($rt1, $rt2) {
	if($rt1['num_of_beds_avail'] > 0 and $rt2['num_of_beds_avail'] < 1) {
		return -1;
	}
	if($rt1['num_of_beds_avail'] < 1 and $rt2['num_of_beds_avail'] > 0) {
		return 1;
	}
	if($rt1['_order'] < $rt2['_order']) {
		return -1;
	}
	if($rt1['_order'] > $rt2['_order']) {
		return 1;
	}
}


function getRoomHtml($roomType, $roomTypeId, $nights, $arriveDate, $specialOffers, $link) {
	$location = getLocation();
	$locationName = getLocationName($location);
	$guests = GUESTS;
	$gallery = GALLERY;
	$roomDetails = ROOM_DETAILS;
	$close = CLOSE;
	$photos = PHOTOS;
	$specialOfferTitle = SPECIAL_OFFER;
	$roomOccupancyTitle = ROOM_OCCUPANCY;

	$name = $roomType['name'];
	$descr = $roomType['description'];
	$shortDescr = $roomType['short_description'];
	$guestOptions = '';
	$key = 'room_type_' . $location . '_' . $roomTypeId;
	$guestSelectedValue = isset($_SESSION[$key]) ? $_SESSION[$key] : '';
	if(!isset($roomType['num_of_beds_avail'])) {
		$roomType['num_of_beds_avail'] = 0;
	}
	if(!isset($roomType['num_of_rooms_avail'])) {
		$roomType['num_of_rooms_avail'] = 0;
	}
	if($location == 'hostel' and isClientFromHU() and $roomType['num_of_beds'] > 4) {
		$nob = $roomType['num_of_beds'];
		$roomType['num_of_beds'] = 4;
		$name = str_replace('5', '4', $name);
		$descr = str_replace('5', '4', $descr);
		$shortDescr = str_replace('5', '4', $shortDescr );
		$roomType['num_of_beds_avail'] = min(4, $roomType['num_of_beds_avail']);
	}
	if(isDorm($roomType)) {
		for($i = 0; $i <= $roomType['num_of_beds_avail']; $i++) {
			$selected = (isset($_SESSION[$key]) and $_SESSION[$key] == $i) ? ' selected="selected"' : '';
			$guestOptions .= "                      <option value=\"$i\"$selected>$i</option>\n";
		}
	} elseif(isPrivate($roomType)) {
		for($i = 0; $i <= $roomType['num_of_rooms_avail']*$roomType['num_of_beds']; $i+=$roomType['num_of_beds']) {
			$selected = (isset($_SESSION[$key]) and $_SESSION[$key] == $i) ? ' selected="selected"' : '';
			$guestOptions .= "                      <option value=\"$i\"$selected>$i</option>\n";
		}
	} elseif(isApartment($roomType)) {
		for($i = 0; $i <= $roomType['num_of_rooms_avail']*$roomType['num_of_beds']; $i++) {
			if($i == 1) {
				continue;
			}
			$selected = (isset($_SESSION[$key]) and $_SESSION[$key] == $i) ? ' selected="selected"' : '';
			$guestOptions .= "                      <option value=\"$i\"$selected>$i</option>\n";
		}
	}
	$currency = getCurrency();
	$discount = 0;
	$sale = '';
	$specialOfferForOneMoreDay = null;
	list($discount, $so) = findSpecialOffer($specialOffers, $roomType, $nights, $arriveDate, $roomType['num_of_beds']);
	list($discountPlus1, $specialOfferForOneMoreDay) = findSpecialOffer($specialOffers, $roomType, $nights+1, $arriveDate, $roomType['num_of_beds']);
	$price = $roomType['price'];
	if(isApartment($roomType)) {
		$price = $roomType['price_2'];
	}

	$template = isDorm($roomType) ? PRICE_PER_NIGHT_PER_BED : (isPrivate($roomType) ? PRICE_PER_NIGHT_PER_ROOM : PRICE_PER_NIGHT_PER_2_GUESTS);
	$oldPricePerNight = '';
	if($discount > 0) {
		$percentOff = '-' . $discount . '%';
		$sale = <<<EOT
                    <p class="sale condensed">
                      <span>$percentOff</span>
                    </p>

EOT;
		$oldPricePerNight = sprintf($template, formatMoney(convertCurrency($price, 'EUR', $currency), $currency)) . '<br>';
		$price = $price * (100 - $discount) / 100;
	}
	$pricePerNight = sprintf($template, formatMoney(convertCurrency($price, 'EUR', $currency), $currency));

	$selectionOfAvailability = '';
	$roomOccupancy = <<<EOT
              <div class="roomOccupancy" data-room-type-id="$roomTypeId"><div class='roomOccupancyText'>$roomOccupancyTitle</div>
                <div class="roomCalendar"></div>
                <div class='clearfix'></div>
              </div>
		
EOT;
	if($roomType['num_of_beds_avail'] > 0) {
		$formName = "room_type_" . $location . "_" . $roomTypeId;
		/*if(isApartment($roomType)) {
			$formName .= "_" . $roomType['num_of_beds'];
		}*/
		$selectionOfAvailability = <<<EOT
              <div class='right roomGuestSelect'>
                <div class="fake-select">
                  <label for="guests-room-1">$guests</label>
                  <div class='clearfix'></div>
                  <span class="value">$guestSelectedValue</span>
                  <span class="open-select icon-down"></span>
                  <select id="$formName" name="$formName">
$guestOptions
                  </select>
                </div>
              </div>

EOT;
	} else {
		$alreadyBooked = ALREADY_BOOKED;
		$selectionOfAvailability = "              <div class=\"right roomFullyBooked\">$alreadyBooked</div>";
		$oldPricePerNight = '';
		$pricePerNight = '';
	}

	$specialOfferHtml = '';
	if(!is_null($specialOfferForOneMoreDay)) {
		$specialOfferHtml = "<div class=\"specOff\"><strong>$specialOfferTitle:</strong>" . $specialOfferForOneMoreDay['text'] . "</div>";
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
            <div class="roomCard">
$sale
              <div class='left roomPic roomPic2'><img src='$roomImg' alt='pic' class='imgResp open-gallery' data-gallery-title="$gallery" data-gallery-url="gallery.php?room_type_id=$roomTypeId"></div>
              <div class='roomHead left'>
                  <h2><a class='open-gallery' data-gallery-title="$gallery" data-gallery-url="gallery.php?room_type_id=$roomTypeId">$name</a></h2>
                  <a class="roomDets2">$roomDetails</a>
                  <!-- &nbsp;&nbsp;&nbsp;<a class='open-gallery' data-gallery-title="$gallery" data-gallery-url="gallery.php?room_type_id=$roomTypeId">$photos</a> --></h2>
                  <br><br>
                  <strong>$shortDescr</strong><br>
                  <div class='left' style='min-width: 250px'>$locationName</div>
                  <div class='left'><span style="text-decoration:line-through;">$oldPricePerNight</span>$pricePerNight</div>
                  <div class='clearfix'></div>
              </div>
            
$selectionOfAvailability
              <div class='clearfix'></div>
              
$roomOccupancy            
              $specialOfferHtml            

              <div class='roomDetails'> 
                <img src='/img/expand.png' title='view in fullscreen' alt='fullscreen' class='right open-gallery' style='margin:10px;' data-gallery-title="$gallery" data-gallery-url="gallery.php?room_type_id=$roomTypeId" />
                <div class='clearfix'></div>
                <iframe class="roomSlider" src="gallery.php?room_type_id=$roomTypeId" frameborder="0"></iframe>
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

function getMinMaxStay($fromDate, $toDate, $link) {
	$sql = "SELECT * FROM min_max_stay WHERE (from_date IS NULL OR from_date<='$fromDate') AND (to_date IS NULL OR to_date>='$fromDate')";
	$result = mysql_query($sql, $link);
	if(mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		return $row;
	}
	return null;
}


?>
