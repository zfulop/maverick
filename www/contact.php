<?php

require('includes.php');
require('includes/common_booking.php');


html_start(CONTACT);

$lang = getCurrentLanguage();

$slides = getCarousel('home', $lang);

$contact = CONTACT;
$locationNameLodge = LOCATION_NAME_LODGE_MENU;
$locationNameHostel = LOCATION_NAME_HOSTEL_MENU;
$locationNameApartment = LOCATION_NAME_APARTMENTS_MENU;
$directions = DIRECTIONS;
$directionsToLodge = DIRECTIONS_TO_LODGE;
$directionsToHostel = DIRECTIONS_TO_HOSTEL;
$directionsToApartment = DIRECTIONS_TO_APARTMENTS;
$addressTitle = ADDRESS_TITLE;
$phoneTitle = PHONE;
$emailTitle = EMAIL;
$faxTitle = FAX;
$addressValueHostel = ADDRESS_VALUE_HOSTEL;
$addressValueLodge = ADDRESS_VALUE_LODGE;
$addressValueApartment = ADDRESS_VALUE_APARTMENTS;
$phoneValueHostel = CONTACT_PHONE_HOSTEL;
$phoneValueLodge = CONTACT_PHONE_LODGE;
$phoneValueApartment = CONTACT_PHONE_APARTMENTS;
$emailValueHostel = CONTACT_EMAIL_HOSTEL;
$emailValueLodge = CONTACT_EMAIL_LODGE;
$emailValueApartments = CONTACT_EMAIL_APARTMENTS;
$faxValueHostel = CONTACT_FAX_HOSTEL;
$faxValueLodge = CONTACT_FAX_LODGE;
$faxValueApartment = CONTACT_FAX_APARTMENTS;

$lodgeLatitude = LATITUDE_LODGE;
$lodgeLongitude = LONGITUDE_LODGE;
$hostelLatitude = LATITUDE_HOSTEL;
$hostelLongitude = LONGITUDE_HOSTEL;
$apartmentLatitude = LATITUDE_APARTMENTS;
$apartmentLongitude = LONGITUDE_APARTMENTS;


echo <<<EOT

      <h1 class="page-title page-title-contact">
        $contact
      </h1>



      <div class="fluid-wrapper columns">
        <section id="contact">
          <ul class="clearfix">
            <li class="lodge">
              <h2>$locationNameLodge</h2>

              <div class="info">
                <p>
                  <strong>$phoneTitle:</strong>
                  $phoneValueLodge
                </p>
                <p>
                  <strong>$emailTitle:</strong>
                  <a href="mailto:$emailValueLodge">$emailValueLodge</a>
                </p>

                <div class="map">
                  <img width="470" height="181" src="http://maps.googleapis.com/maps/api/staticmap?center=47.496666,19.058404&amp;zoom=12&amp;size=470x181&amp;maptype=roadmap&amp;markers=icon:http://www.mavericklodges.com/img/poi-lodge-small.png%7C$lodgeLatitude,$lodgeLongitude&amp;sensor=false&amp;style=feature:poi.business|visibility:off">
                </div>

                <p class="condensed">
                  <strong>$addressTitle:</strong>
				  $addressValueLodge
                </p>
                <p>
                  <strong>$faxTitle:</strong>
                  $faxValueLodge
                </p>
              </div>

              <div class="directions">
                <p class="condensed">
                  <a class="open-overlay" href="" data-overlay-title="$directionsToLodge" data-overlay-content-url="directions.php?location=lodge">$directions</a>
                  </a>
                </p>
              </div>
            </li>

            <li class="hostel">
              <h2>$locationNameHostel</h2>

              <div class="info">
                <p>
                  <strong>$phoneTitle:</strong>
                  $phoneValueHostel
                </p>
                <p>
                  <strong>$emailTitle:</strong>
                  <a href="mailto:$emailValueHostel">$emailValueHostel</a>
                </p>

                <div class="map">
                  <img width="470" height="181" src="http://maps.googleapis.com/maps/api/staticmap?center=47.496666,19.058404&amp;zoom=12&amp;size=470x181&amp;maptype=roadmap&amp;markers=icon:http://www.mavericklodges.com/img/poi-hostel-small.png%7C$hostelLatitude,$hostelLongitude&amp;sensor=false&amp;style=feature:poi.business|visibility:off">
                </div>

                <p class="condensed">
                  <strong>$addressTitle:</strong>
				  $addressValueHostel
                </p>
                <p>
                  <strong>$faxTitle:</strong>
                  $faxValueHostel
                </p>
              </div>

              <div class="directions">
                <p class="condensed">
                  <a class="open-overlay" href="" data-overlay-title="$directionsToHostel" data-overlay-content-url="directions.php?location=hostel">$directions</a>
                  </a>
                </p>
              </div>
            </li>

            <li class="apartments">
              <h2>$locationNameApartment</h2>

              <div class="info">
                <p>
                  <strong>$phoneTitle:</strong>
                  $phoneValueApartment
                </p>
                <p>
                  <strong>$emailTitle:</strong>
                  <a href="mailto:$emailValueApartments">$emailValueApartments</a>
                </p>

                <div class="map">
                  <img width="470" height="181" src="http://maps.googleapis.com/maps/api/staticmap?center=47.496666,19.058404&amp;zoom=12&amp;size=470x181&amp;maptype=roadmap&amp;markers=icon:http://www.mavericklodges.com/img/poi-lodge-small.png%7C$apartmentLatitude,$apartmentLongitude&amp;sensor=false&amp;style=feature:poi.business|visibility:off">
                </div>

                <p class="condensed">
                  <strong>$addressTitle:</strong>
				  $addressValueApartment
                </p>
                <p>
                  <strong>$faxTitle:</strong>
                  $faxValueApartment
                </p>
              </div>

              <div class="directions">
                <p class="condensed">
                  <a class="open-overlay" href="" data-overlay-title="$directionsToApartment" data-overlay-content-url="directions.php?location=apartments">$directions</a>
                  </a>
                </p>
              </div>
            </li>




          </ul>
        </section>
      </div>

EOT;

html_end();

?>
