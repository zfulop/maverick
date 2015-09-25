<?php

require('includes.php');
require('includes/common_booking.php');

$location = getLocation();
$link = db_connect($location);

html_start(CONTACT);

$lang = getCurrentLanguage();

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


$getInTouch = GET_IN_TOUCH;
$getInTouchExplain = GET_IN_TOUCH_EXPLAIN;
$yourName = YOUR_NAME;
$firstname = FIRST_NAME;
$lastname = LASTNAME;
$confirmEmail = CONFIRM_EMAIL;
$nationality = NATIONALITY;
$countryCode= COUNTRY;
$country = COUNTRY;
$pleaseSelect = PLEASE_SELECT;
$comment = COMMENT;
$chooseMaverickToContact = CHOOSE_MAVERICK_TO_CONTACT;
$sendMessage = SEND_MESSAGE;

$poiJson = "poi-contact.json";


$firstnameValue = isset($_SESSION['contact_firstname']) ? $_SESSION['contact_firstname'] : '';
$firstnameError = '';
$firstnameErrorClass = '';
if(isset($_SESSION['contact_firstnameError'])) {
	$firstnameErrorClass = 'error';
	$firstnameError = '<p class="error">' . $_SESSION['contact_firstnameError'] . '</p>';
	unset($_SESSION['contact_firstnameError']);
}

$lastnameValue = isset($_SESSION['contact_lastname']) ? $_SESSION['contact_lastname'] : '';
$lastnameError = '';
$lastnameErrorClass = '';
if(isset($_SESSION['contact_lastnameError'])) {
	$lastnameErrorClass = 'error';
	$lastnameError = '<p class="error">' . $_SESSION['contact_lastnameError'] . '</p>';
	unset($_SESSION['contact_lastnameError']);
}

$emailValue = isset($_SESSION['contact_email']) ? $_SESSION['contact_email'] : '';
$emailError = '';
$emailErrorClass = '';
if(isset($_SESSION['contact_emailError'])) {
	$emailErrorClass = 'error';
	$emailError = '<p class="error">' . $_SESSION['contact_emailError'] . '</p>';
	unset($_SESSION['contact_emailError']);
}

$confirmEmailValue = isset($_SESSION['contact_email2']) ? $_SESSION['contact_email2'] : '';
$confirmEmailError = '';
$confirmEmailErrorClass = '';
if(isset($_SESSION['contact_confirmEmailError'])) {
	$confirmEmailErrorClass = 'error';
	$confirmEmailError = '<p class="error">' . $_SESSION['contact_confirmEmailError'] . '</p>';
	unset($_SESSION['contact_confirmEmailError']);
}

$countryCodeOptions = "                      <option value=\"\">$pleaseSelect</option>\n";
foreach($countryCodes as $country => $code) {
	$selected = (isset($_SESSION['contact_countryCode']) and $code == $_SESSION['contact_countryCode']) ? ' selected="selected"' : '';
	$countryCodeOptions .= "                        <option value=\"+$code\"$selected>$country (+$code)</option>\n";
}
$countryCodeError = '';
$countryCodeErrorClass = '';
if(isset($_SESSION['contact_countryCodeError'])) {
	$countryCodeErrorClass = 'error';
	$countryCodeError = '<p class="error">' . $_SESSION['contact_countryCodeError'] . '</p>';
	unset($_SESSION['contact_countryCodeError']);
}

$dataPhoneValue = isset($_SESSION['contact_phone']) ? $_SESSION['contact_phone'] : '';
$dataPhoneError = '';
$dataPhoneErrorClass = '';
if(isset($_SESSION['contact_dataPhoneError'])) {
	$dataPhoneErrorClass = 'error';
	$dataPhoneError = '<p class="error">' . $_SESSION['contact_dataPhoneError'] . '</p>';
	unset($_SESSION['contact_dataPhoneError']);
}

$destinationOptions = "                      <option value=\"\">$pleaseSelect</option>\n";
foreach(getLocations() as $oneLocation) {
	$selected = (isset($_SESSION['contact_destination']) and $oneLocation == $_SESSION['contact_destination']) ? ' selected="selected"' : '';
	$destinationOptions .= "                        <option value=\"$oneLocation\"$selected>" . getLocationName($oneLocation) . "</option>\n";
}
$destinationError = '';
$destinationErrorClass = '';
if(isset($_SESSION['contact_destinationError'])) {
	$destinationErrorClass = 'error';
	$destinationError = '<p class="error">' . $_SESSION['contact_destinationError'] . '</p>';
	unset($_SESSION['contact_destinationError']);
}

$nationalityOptions = "                      <option value=\"\">$pleaseSelect</option>\n";
foreach($countryCodes as $country => $code) {
	$selected = (isset($_SESSION['contact_nationality']) and $country == $_SESSION['contact_nationality']) ? ' selected="selected"' : '';
	$nationalityOptions .= "                        <option value=\"$country\"$selected>$country</option>\n";
}
$nationalityError = '';
$nationalityErrorClass = '';
if(isset($_SESSION['contact_nationalityError'])) {
	$nationalityErrorClass = 'error';
	$nationalityError = '<p class="error">' . $_SESSION['contact_nationalityError'] . '</p>';
	unset($_SESSION['contact_nationalityError']);
}

$commentValue = isset($_SESSION['contact_comment']) ? $_SESSION['contact_comment'] : '';
$commentError = '';
$commentErrorClass = '';
if(isset($_SESSION['contact_commentError'])) {
	$commentErrorClass = 'error';
	$commentError = '<p class="error">' . $_SESSION['contact_commentError'] . '</p>';
	unset($_SESSION['contact_commentError']);
}



$awards = AWARDS;
$sql = "SELECT * FROM awards";
$result = mysql_query($sql, $link);
$awardsHtml = '';
while($row = mysql_fetch_assoc($result)) {
	$awardsHtml .= '                        <a href="' . $row['url'] . '" target="_blank"><img class="footerAward" src="' . $row['img'] . '" alt="' . $row['name'] . '"></a>' . "\n";	
}


echo <<<EOT

      <h1 class="page-title page-title-contact">
        $contact
      </h1>



      <div class="fluid-wrapper columns">
        <div style="max-width: 1000px;">
          <section id="contact">
            <div class='location'>
                <div class='contWrap'>
                    <h2>$locationNameLodge</h2>
                    <div class='contCat'>$phoneTitle:</div><div class='contCont'>$phoneValueLodge</div><div class='clearfix'></div>
                    <div class='contCat'>$emailTitle:</div><div class='contCont'><a href='mailto:$emailValueLodge'>$emailValueLodge</a></div><div class='clearfix'></div>


                    <div class='contCat'>$addressTitle:</div><div class='contCont'>$addressValueLodge</div><div class='clearfix'></div>
                    <div class='contCat'>$faxTitle:</div><div class='contCont'>$faxValueLodge</div><div class='clearfix'></div>
                    <div class='contCat'><br><a class="open-overlay" href="" data-overlay-title="$directionsToLodge" data-overlay-content-url="directions.php?location=lodge">$directions</a></div><div class='clearfix'></div>
                </div>    
            </div>

            <div class='location'>
                <div class='contWrap'>
                    <h2>$locationNameHostel</h2>
                    <div class='contCat'>$phoneTitle:</div><div class='contCont'>$phoneValueHostel</div><div class='clearfix'></div>
                    <div class='contCat'>$emailTitle:</div><div class='contCont'><a href='mailto:$emailValueHostel'>$emailValueHostel</a></div><div class='clearfix'></div>


                    <div class='contCat'>$addressTitle:</div><div class='contCont'>$addressValueHostel</div><div class='clearfix'></div>
                    <div class='contCat'>$faxTitle:</div><div class='contCont'>$faxValueHostel</div><div class='clearfix'></div>
                    <div class='contCat'><br><a class="open-overlay" href="" data-overlay-title="$directionsToHostel" data-overlay-content-url="directions.php?location=lodge">$directions</a></div><div class='clearfix'></div>
                </div>    
            </div>

            <div class='location'>
                <div class='contWrap'>
                    <h2>$locationNameApartment</h2>
                    <div class='contCat'>$phoneTitle:</div><div class='contCont'>$phoneValueApartment</div><div class='clearfix'></div>
                    <div class='contCat'>$emailTitle:</div><div class='contCont'><a href='mailto:$emailValueApartments'>$emailValueApartments</a></div><div class='clearfix'></div>


                    <div class='contCat'>$addressTitle:</div><div class='contCont'>$addressValueApartment</div><div class='clearfix'></div>
                    <div class='contCat'>$faxTitle:</div><div class='contCont'>$faxValueApartment</div><div class='clearfix'></div>
                    <div class='contCat'><br><a class="open-overlay" href="" data-overlay-title="$directionsToApartment" data-overlay-content-url="directions.php?location=lodge">$directions</a></div><div class='clearfix'></div>
                </div>    
			</div>

            <div class="clearfix"></div>
          </section>

          <section id='location'>
            <div id="map-container">
                <div class="map" data-poi-url="$poiJson"></div>
            </div>            
          </section>

          <section id='group-booking' class='common-form'>
            <form action="contact_submit.php" method="post" accept-charset="utf-8">
              <fieldset>
			   <h1>$getInTouch</h1>
				$getInTouchExplain
              <br><br>
              
              <div class="fields">
                <div class="group">
                  <span class="group-label">$yourName:</span>
                  
                  <div class="field $firstnameErrorClass">
                    <label for="firstname">$firstname:</label>
					<input type="text" id="firstname" name="firstname" placeholder="$firstname"  value="$firstnameValue">
                    $firstnameError
                  </div>
                  
                  <div class="field $lastnameErrorClass">
                    <label for="lastname">$lastname:</label>
					<input type="text" id="lastname" name="lastname" placeholder="$lastname" value="$lastnameValue">
                    $lastnameError
                  </div>
                </div>
                
                <div class="field $emailErrorClass clearfix">
                  <label for="email">$emailTitle:</label>
				  <input type="email" id="email" name="email" value="$emailValue">
                  $emailError 
                </div>
                
                <div class="field $confirmEmailErrorClass clearfix">
                  <label for="email2">$confirmEmail:</label>
				  <input type="email" id="email2" name="email2" value="$confirmEmailValue">
                  $confirmEmailError
                </div>
                
                <div class="group ">
                  <span class="group-label">$phoneTitle:</span>
                  
                  <div class="field $countryCodeErrorClass ">
                    <label for="countrycode">$countryCode:</label>
                    <div class="fake-select">
                      <span class="value">$pleaseSelect</span>
                      <span class="open-select icon-down"></span>
                      <select id="countrycode" name="countrycode">
                        <option value="">$pleaseSelect</option>
$countryCodeOptions
                      </select>
                    </div>
                    $countryCodeError
                  </div>
                  
                  <div class="field $dataPhoneErrorClass">
                    <label for="phone"></label>
					<input type="text" id="phone" name="phone" value="$dataPhoneValue">
                    $dataPhoneError
                  </div>
                </div>

                <div class="field $nationalityErrorClass clearfix">
                  <label for="nationality">$nationality:</label>
                  <div class="fake-select">
                    <span class="value">$pleaseSelect</span>
                    <span class="open-select icon-down"></span>
                    <select id="nationality" name="nationality">
$nationalityOptions
                    </select>
                  </div>
                  $nationalityError
                </div>

                    <div class="field  clearfix">
                  <label for="destination">$chooseMaverickToContact:</label>
                  <div class="fake-select">
                    <span class="value">$pleaseSelect</span>
                    <span class="open-select icon-down"></span>
					<select id="destination" name="destination">
$destinationOptions
                    </select>
				  </div>
				  $destinationError
                </div>

                <div class="field $commentErrorClass clearfix">
                  <label for="comment">$comment:</label>
                  <textarea id="comment" name="comment">$commentValue</textarea>
                  $commentError
                </div>
                
                <button type="submit">$sendMessage</button>

              </div>
            </fieldset>
            </form>
          </section>

          <section id="awards">
              <h1>$awards</h1><br>
$awardsHtml
              <div class='clearfix'></div>
          </section>

        </div>
      </div>

EOT;

html_end($link);
mysql_close($link);


?>
