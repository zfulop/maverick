<?php

require('includes.php');
require('includes/common_booking.php');


html_start(HOME);

$lang = getCurrentLanguage();

$slides = getCarousel('home', $lang);

$lodgeShortDescription = LODGE_DESCRIPTION_HOME;
$hostelShortDescription = HOSTEL_DESCRIPTION_HOME;
$apartmentShortDescription = APARTMENTS_DESCRIPTION_HOME;
$locations = LOCATIONS;
$addressTitle = ADDRESS_TITLE;
$addressValueHostel = ADDRESS_VALUE_HOSTEL;
$addressValueHostelGeneral = ADDRESS_VALUE_HOSTEL_GENERAL;
$addressValueLodge = ADDRESS_VALUE_LODGE;
$addressValueLodgeGeneral = ADDRESS_VALUE_LODGE_GENERAL;
$addressValueApartment = ADDRESS_VALUE_APARTMENTS;
$addressValueApartmentGeneral = ADDRESS_VALUE_APARTMENTS_GENERAL;
$phone = PHONE;
$email = EMAIL;
$fax = FAX;
$phoneHostel = CONTACT_PHONE_HOSTEL;
$emailHostel = CONTACT_EMAIL_HOSTEL;
$faxHostel = CONTACT_FAX_HOSTEL;
$phoneLodge = CONTACT_PHONE_LODGE;
$emailLodge = CONTACT_EMAIL_LODGE;
$faxLodge = CONTACT_FAX_LODGE;

$lodgeLatitude = LATITUDE_LODGE;
$lodgeLongitude = LONGITUDE_LODGE;
$hostelLatitude = LATITUDE_HOSTEL;
$hostelLongitude = LONGITUDE_HOSTEL;



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


      <div class="fluid-wrapper columns">
        <section id="location-list">
          <ul class="clearfix">
            <li class="hostel">
              <a href="maverick_hostel_ensuites.php">
                <h2>
                  <img width="470" height="470" src="/img/location-hostel.jpg">
                  <span class="title">$addressValueHostelGeneral</span>
                </h2>
                
                <div class="map">
                  <img width="470" height="181" src="http://maps.googleapis.com/maps/api/staticmap?center=47.496666,19.058404&amp;zoom=12&amp;size=470x181&amp;maptype=roadmap&amp;markers=icon:http://www.mavericklodges.com/img/poi-hostel-small.png%7C$hostelLatitude,$hostelLongitude&amp;sensor=false&amp;style=feature:poi.business|visibility:off">
                </div>
              </a>
              
              <div class="info">
				<p>
                  $hostelShortDescription
                </p>
<!--
                <br>
                <p class="condensed">
                  <strong>$addressTitle</strong>
                  $addressValueHostel
                </p>
                <p>
                  <strong>$phone</strong>
                  $phoneHostel
                </p>
                <p>
                  <strong>$email</strong>
                  <a href="mailto:$emailHostel">$emailHostel</a>
                </p>
                <p>
                  <strong>$fax</strong>
                  $faxHostel
				</p>
-->
              </div>
            </li>
            
            <li class="lodge">
              <a href="maverick_city_lodge.php">
                <h2>
                  <img width="470" height="470" src="/img/location-lodge.jpg">
                  <span class="title">$addressValueLodgeGeneral</span>
                </h2>
                
                <div class="map">
                  <img width="470" height="181" src="http://maps.googleapis.com/maps/api/staticmap?center=47.496666,19.058404&amp;zoom=12&amp;size=470x181&amp;maptype=roadmap&amp;markers=icon:http://www.mavericklodges.com/img/poi-lodge-small.png%7C$lodgeLatitude,$lodgeLongitude&amp;sensor=false&amp;style=feature:poi.business|visibility:off">
                </div>
              </a>
              
              <div class="info">
				<p>
                  $lodgeShortDescription<br>
				</p>
<!--
				<br>
				<p class="condensed">
				  <strong>$addressTitle</strong>
                  $addressValueLodge
                </p>
                <p>
                  <strong>$phone</strong>
                  $phoneLodge
                </p>
                <p>
                  <strong>$email</strong>
                  <a href="mailto:$emailLodge">$emailLodge</a>
                </p>
                <p>
                  <strong>$fax</strong>
                  $faxLodge
				</p>
-->
              </div>
            </li>
            <li class="apartments">
              <a href="maverick_apartments.php">
                <h2>
                  <img width="470" height="470" src="/img/location-apartment.jpg">
                  <span class="title">$addressValueApartmentGeneral</span>
                </h2>
                
                <div class="map">
                  <img width="470" height="181" src="http://maps.googleapis.com/maps/api/staticmap?center=47.496666,19.058404&amp;zoom=12&amp;size=470x181&amp;maptype=roadmap&amp;markers=icon:http://www.mavericklodges.com/img/poi-hostel-small.png%7C$hostelLatitude,$hostelLongitude&amp;sensor=false&amp;style=feature:poi.business|visibility:off">
                </div>
              </a>
              
              <div class="info">
				<p>
                  $apartmentShortDescription
                </p>
<!--
                <br>
                <p class="condensed">
                  <strong>$addressTitle</strong>
                  $addressValueHostel
                </p>
                <p>
                  <strong>$phone</strong>
                  $phoneHostel
                </p>
                <p>
                  <strong>$email</strong>
                  <a href="mailto:$emailHostel">$emailHostel</a>
                </p>
                <p>
                  <strong>$fax</strong>
                  $faxHostel
				</p>
-->
              </div>
            </li>
          </ul>
        </section>
      </div>

EOT;

html_end();

?>
