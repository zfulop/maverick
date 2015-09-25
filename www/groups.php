<?php

require('includes.php');
require('includes/common_booking.php');
require(RECEPCIO_BASE_DIR . 'room_booking.php');


$roomTypes = array(PRIVATE_ROOM, DORM, BOTH);

$groupTypes = array(UNIVERSITY_TRIP, SCHOOL_GROUP, SPORT_TOUR, OTHER);

$location = getLocation();
$lang = getCurrentLanguage();
$currency = getCurrency();

$link = db_connect($location);

$groupBooking = GROUP_BOOKING;
$intro = GROUP_BOOKING_INFO;
$contactDetailsExplanation = CONTACT_DETAILS_EXPLANATION;
$yourName = YOUR_NAME;
$firstname = FIRST_NAME;
$lastname = LASTNAME;
$email = EMAIL;
$confirmEmail = CONFIRM_EMAIL;
$phone = PHONE;
$numOfParticipants = NUMBER_OF_PARTICIPANTS;
$nationality = NATIONALITY;
$address = ADDRESS_TITLE;
$city = CITY;
$zipcode= ZIP_CODE;
$country = COUNTRY;
$countryCode= COUNTRY;
$pleaseSelect = PLEASE_SELECT;
$roomTypePreference = ROOM_TYPE_PREFERENCE;
$provideContactDetails = PROVIDE_CONTACT_DETAILS;
$groupBookingInfo = GROUP_BOOKING_INFO;
$destination = DESTINATION;
$dateOfArrival = DATE_OF_ARRIVAL;
$dateOfDeparture = DATE_OF_DEPARTURE;
$groupType = GROUP_TYPE;
$numOfParticipats = NUMBER_OF_PARTICIPANTS;
$roomTypePreference = ROOM_TYPE_PREFERENCE;
$comment = COMMENT;
$sendInquiry = SEND_INQUIRY;
$both = BOTH;

$firstnameValue = isset($_SESSION['group_firstname']) ? $_SESSION['group_firstname'] : '';
$firstnameError = '';
$firstnameErrorClass = '';
if(isset($_SESSION['group_firstnameError'])) {
	$firstnameErrorClass = 'error';
	$firstnameError = '<p class="error">' . $_SESSION['group_firstnameError'] . '</p>';
	unset($_SESSION['group_firstnameError']);
}

$lastnameValue = isset($_SESSION['group_lastname']) ? $_SESSION['group_lastname'] : '';
$lastnameError = '';
$lastnameErrorClass = '';
if(isset($_SESSION['group_lastnameError'])) {
	$lastnameErrorClass = 'error';
	$lastnameError = '<p class="error">' . $_SESSION['group_lastnameError'] . '</p>';
	unset($_SESSION['group_lastnameError']);
}

$emailValue = isset($_SESSION['group_email']) ? $_SESSION['group_email'] : '';
$emailError = '';
$emailErrorClass = '';
if(isset($_SESSION['group_emailError'])) {
	$emailErrorClass = 'error';
	$emailError = '<p class="error">' . $_SESSION['group_emailError'] . '</p>';
	unset($_SESSION['group_emailError']);
}

$confirmEmailValue = isset($_SESSION['group_email2']) ? $_SESSION['group_email2'] : '';
$confirmEmailError = '';
$confirmEmailErrorClass = '';
if(isset($_SESSION['group_confirmEmailError'])) {
	$confirmEmailErrorClass = 'error';
	$confirmEmailError = '<p class="error">' . $_SESSION['group_confirmEmailError'] . '</p>';
	unset($_SESSION['group_confirmEmailError']);
}

$countryCodeOptions = "                      <option value=\"\">$pleaseSelect</option>\n";
foreach($countryCodes as $country => $code) {
	$selected = (isset($_SESSION['group_countryCode']) and $code == $_SESSION['group_countryCode']) ? ' selected="selected"' : '';
	$countryCodeOptions .= "                        <option value=\"+$code\"$selected>$country (+$code)</option>\n";
}
$countryCodeError = '';
$countryCodeErrorClass = '';
if(isset($_SESSION['group_countryCodeError'])) {
	$countryCodeErrorClass = 'error';
	$countryCodeError = '<p class="error">' . $_SESSION['group_countryCodeError'] . '</p>';
	unset($_SESSION['group_countryCodeError']);
}

$dataPhoneValue = isset($_SESSION['group_phone']) ? $_SESSION['group_phone'] : '';
$dataPhoneError = '';
$dataPhoneErrorClass = '';
if(isset($_SESSION['group_dataPhoneError'])) {
	$dataPhoneErrorClass = 'error';
	$dataPhoneError = '<p class="error">' . $_SESSION['group_dataPhoneError'] . '</p>';
	unset($_SESSION['group_dataPhoneError']);
}

$destinationOptions = "                      <option value=\"\">$pleaseSelect</option>\n";
foreach(getLocations() as $oneLocation) {
	$selected = (isset($_SESSION['group_destination']) and $oneLocation == $_SESSION['group_destination']) ? ' selected="selected"' : '';
	$destinationOptions .= "                        <option value=\"$oneLocation\"$selected>" . getLocationName($oneLocation) . "</option>\n";
}
$destinationError = '';
$destinationErrorClass = '';
if(isset($_SESSION['group_destinationError'])) {
	$destinationErrorClass = 'error';
	$destinationError = '<p class="error">' . $_SESSION['group_destinationError'] . '</p>';
	unset($_SESSION['group_destinationError']);
}

$dateOfArrivalValue = isset($_SESSION['group_dateOfArrival']) ? $_SESSION['group_dateOfArrival'] : date('Y-m-d');
$dateOfArrivalError = '';
$dateOfArrivalErrorClass = '';
if(isset($_SESSION['group_dateOfArrivalError'])) {
	$dateOfArrivalErrorClass = 'error';
	$dateOfArrivalError = '<p class="error">' . $_SESSION['group_dateOfArrivalError'] . '</p>';
	unset($_SESSION['group_dateOfArrivalError']);
}

$dateOfDepartureValue = isset($_SESSION['group_dateOfDeparture']) ? $_SESSION['group_dateOfDeparture'] : date('Y-m-d', strtotime(date('Y-m-d') . ' +3 day'));
$dateOfDepartureError = '';
$dateOfDepartureErrorClass = '';
if(isset($_SESSION['group_dateOfDepartureError'])) {
	$dateOfDepartureErrorClass = 'error';
	$dateOfDepartureError = '<p class="error">' . $_SESSION['group_dateOfDepartureError'] . '</p>';
	unset($_SESSION['group_dateOfDepartureError']);
}

$groupTypeOptions = "                      <option value=\"\">$pleaseSelect</option>\n";
foreach($groupTypes as $oneGroupType) {
	$selected = (isset($_SESSION['group_groupType']) and $oneGroupType == $_SESSION['group_groupType']) ? ' selected="selected"' : '';
	$groupTypeOptions .= "                        <option value=\"$oneGroupType\"$selected>$oneGroupType</option>\n";
}
$groupTypeError = '';
$groupTypeErrorClass = '';
if(isset($_SESSION['group_groupTypeError'])) {
	$groupTypeErrorClass = 'error';
	$groupTypeError = '<p class="error">' . $_SESSION['group_groupTypeError'] . '</p>';
	unset($_SESSION['group_groupTypeError']);
}

$numOfParticipantsValue = isset($_SESSION['group_numOfParticipants']) ? $_SESSION['group_numOfParticipants'] : '';
$numOfParticipantsError = '';
$numOfParticipantsErrorClass = '';
if(isset($_SESSION['group_numOfParticipantsError'])) {
	$numOfParticipantsErrorClass = 'error';
	$numOfParticipantsError = '<p class="error">' . $_SESSION['group_numOfParticipantsError'] . '</p>';
	unset($_SESSION['group_numOfParticipantsError']);
}

$nationalityOptions = "                      <option value=\"\">$pleaseSelect</option>\n";
foreach($countryCodes as $country => $code) {
	$selected = (isset($_SESSION['group_nationality']) and $country == $_SESSION['group_nationality']) ? ' selected="selected"' : '';
	$nationalityOptions .= "                        <option value=\"$country\"$selected>$country</option>\n";
}
$nationalityError = '';
$nationalityErrorClass = '';
if(isset($_SESSION['group_nationalityError'])) {
	$nationalityErrorClass = 'error';
	$nationalityError = '<p class="error">' . $_SESSION['group_nationalityError'] . '</p>';
	unset($_SESSION['group_nationalityError']);
}

$roomTypeOptions = "                      <option value=\"\">$pleaseSelect</option>\n";
foreach($roomTypes as $oneRoomType) {
	$selected = (isset($_SESSION['group_roomType']) and $oneRoomType == $_SESSION['group_roomType']) ? ' selected="selected"' : '';
	$roomTypeOptions .= "                        <option value=\"$oneRoomType\"$selected>$oneRoomType</option>\n";
}
$roomTypeError = '';
$roomTypeErrorClass = '';
if(isset($_SESSION['group_roomTypeError'])) {
	$roomTypeErrorClass = 'error';
	$roomTypeError = '<p class="error">' . $_SESSION['group_roomTypeError'] . '</p>';
	unset($_SESSION['group_roomTypeError']);
}

$commentValue = isset($_SESSION['group_comment']) ? $_SESSION['group_comment'] : '';
$commentError = '';
$commentErrorClass = '';
if(isset($_SESSION['group_commentError'])) {
	$commentErrorClass = 'error';
	$commentError = '<p class="error">' . $_SESSION['group_commentError'] . '</p>';
	unset($_SESSION['group_commentError']);
}



html_start(GROUP_BOOKING);

echo <<<EOT

      <h1 class="page-title page-title-groups">$groupBooking</h1>

      <div class="fluid-wrapper page">
        <section id="group-booking" class="common-form">
          <form action="groups_submit.php" method="post" accept-charset="utf-8">
            <fieldset>
              <h1>$provideContactDetails</h1>
              
              <p class="info">$groupBookingInfo</p>
              
              <div class="fields">
                <div class="group clearfix">
                  <span class="group-label">$yourName:</span>
                  
                  <div class="field $firstnameErrorClass">
                    <label for="data_firstname">$firstname:</label>
                    <input type="text" id="data_firstname" name="data_firstname" placeholder="$firstname" value="$firstnameValue">
					$firstnameError
                  </div>
                  
                  <div class="field $lastnameErrorClass">
                    <label for="data_last_name">$lastname:</label>
                    <input type="text" id="data_last_name" name="data_last_name" placeholder="$lastname" value="$lastnameValue">
					$lastnameError
                  </div>
                </div>
                
                <div class="field $emailErrorClass clearfix">
                  <label for="data_email">$email:</label>
				  <input type="email" id="data_email" name="data_email" value="$emailValue">
                  $emailError
                </div>
                
                <div class="field $confirmEmailErrorClass clearfix">
                  <label for="data_email2">$confirmEmail:</label>
                  <input type="email" id="data_email2" name="data_email2" value="$confirmEmailValue">
                  $confirmEmailError
                </div>
                
                <div class="group">
                  <span class="group-label">$phone:</span>
                  
                  <div class="field $countryCodeErrorClass ">
                    <label for="data_countrycode">$countryCode:</label>
                    <div class="fake-select">
                      <span class="value">$pleaseSelect</span>
                      <span class="open-select icon-down"></span>
                      <select id="data_countrycode" name="data_countrycode">
$countryCodeOptions
                      </select>
                    </div>
                    $countryCodeError
                  </div>
                  
                  <div class="field $dataPhoneErrorClass">
                    <label for="data_phone"></label>
                    <input type="text" id="data_phone" name="data_phone" value="$dataPhoneValue">
                    $dataPhoneError
                  </div>
                </div>

                <div class="field $destinationErrorClass clearfix">
                  <label for="data_destination">$destination:</label>
                  <div class="fake-select">
                    <span class="value">$pleaseSelect</span>
                    <span class="open-select icon-down"></span>
					<select id="data_destination" name="data_destination">
$destinationOptions
                    </select>
				  </div>
				  $destinationError
                </div>

                <div class="field short date from $dateOfArrivalErrorClass clearfix">
                  <label for="data_arrival">$dateOfArrival:</label>
                  <input type="date" id="data_arrival" name="data_arrival" value="$dateOfArrivalValue">
                  $dateOfArrivalError
                </div>

                <div class="field short date to $dateOfDepartureErrorClass clearfix">
                  <label for="data_departure">$dateOfDeparture:</label>
                  <input type="date" id="data_departure" name="data_departure" value="$dateOfDepartureValue">
                  $dateOfDepartureError
                </div>

                <div class="field $groupTypeErrorClass clearfix">
                  <label for="data_grouptype">$groupType:</label>
                  <div class="fake-select">
                    <span class="value"></span>
                    <span class="open-select icon-down"></span>
                    <select id="data_grouptype" name="data_grouptype">
$groupTypeOptions
                    </select>
				  </div>
                  $groupTypeError
                </div>

                <div class="field short $numOfParticipantsErrorClass clearfix">
                  <label for="data_number">$numOfParticipants:</label>
				  <input type="number" id="data_number" name="data_number" value="$numOfParticipantsValue">
                  $numOfParticipantsError
                </div>

                <div class="field $nationalityErrorClass clearfix">
                  <label for="data_nationality">$nationality:</label>
                  <div class="fake-select">
                    <span class="value">$pleaseSelect</span>
                    <span class="open-select icon-down"></span>
                    <select id="data_nationality" name="data_nationality">
$nationalityOptions
                    </select>
				  </div>
                  $nationalityError
                </div>

                <div class="field $roomTypeErrorClass clearfix">
                  <label for="data_roomtype">$roomTypePreference:</label>
                  <div class="fake-select">
                    <span class="value"></span>
                    <span class="open-select icon-down"></span>
                    <select id="data_roomtype" name="data_roomtype">
$roomTypeOptions
                    </select>
				  </div>
                  $roomTypeError
                </div>
                
                <div class="field $commentErrorClass clearfix">
                  <label for="data_comment">$comment:</label>
                  <textarea id="data_comment" name="data_comment">$commentValue</textarea>
                  $commentError
                </div>
                
                <button type="submit">$sendInquiry</button>

              </div>
            </fieldset>
          </form>
        </section>
      </div>

EOT;


html_end($link);
mysql_close($link);


?>

