<?php

require('includes.php');
require('includes/common_booking.php');
require(RECEPCIO_BASE_DIR . 'room_booking.php');

$countryCodes = array(
	'Afghanistan' => '93',
	'Albania' => '355',
	'Algeria' => '213',
	'American Samoa' => '684',
	'Andorra' => '376',
	'Angola' => '244',
	'Anguilla' => '809',
	'Antigua' => '268',
	'Argentina' => '54',
	'Armenia' => '374',
	'Aruba' => '297',
	'Ascension Island' => '247',
	'Australia' => '61',
	'Australian External Territories' => '672',
	'Austria' => '43',
	'Azerbaijan' => '994',
	'Bahamas' => '242',
	'Barbados' => '246',
	'Bahrain' => '973',
	'Bangladesh' => '880',
	'Belarus' => '375',
	'Belgium' => '32',
	'Belize' => '501',
	'Benin' => '229',
	'Bermuda' => '809',
	'Bhutan' => '975',
	'British Virgin Islands' => '284',
	'Bolivia' => '591',
	'Bosnia and Hercegovina' => '387',
	'Botswana' => '267',
	'Brazil' => '55',
	'British V.I.' => '284',
	'Brunei Darussalm' => '673',
	'Bulgaria' => '359',
	'Burkina Faso' => '226',
	'Burundi' => '257',
	'Cambodia' => '855',
	'Cameroon' => '237',
	'Canada' => '1',
	'CapeVerde Islands' => '238',
	'Caribbean Nations' => '1',
	'Cayman Islands' => '345',
	'Cape Verdi' => '238',
	'Central African Republic' => '236',
	'Chad' => '235',
	'Chile' => '56',
	'China' => '86',
	'China-Taiwan' => '886',
	'Colombia' => '57',
	'Comoros and Mayotte' => '269',
	'Congo' => '242',
	'Cook Islands' => '682',
	'Costa Rica' => '506',
	'Croatia' => '385',
	'Cuba' => '53',
	'Cyprus' => '357',
	'Czech Republic' => '420',
	'Denmark' => '45',
	'Diego Garcia' => '246',
	'Dominca' => '767',
	'Dominican Republic' => '809',
	'Djibouti' => '253',
	'Ecuador' => '593',
	'Egypt' => '20',
	'El Salvador' => '503',
	'Equatorial Guinea' => '240',
	'Eritrea' => '291',
	'Estonia' => '372',
	'Ethiopia' => '251',
	'Falkland Islands' => '500',
	'Faroe (Faeroe) Islands (Denmark)' => '298',
	'Fiji' => '679',
	'Finland' => '358',
	'France' => '33',
	'French Antilles' => '596',
	'French Guiana' => '594',
	'Gabon' => '241',
	'Gambia' => '220',
	'Georgia' => '995',
	'Germany' => '49',
	'Ghana' => '233',
	'Gibraltar' => '350',
	'Greece' => '30',
	'Greenland' => '299',
	'Grenada/Carricou' => '473',
	'Guam' => '671',
	'Guatemala' => '502',
	'Guinea' => '224',
	'Guinea-Bissau' => '245',
	'Guyana' => '592',
	'Haiti' => '509',
	'Honduras' => '504',
	'Hong Kong' => '852',
	'Hungary' => '36',
	'Iceland' => '354',
	'India' => '91',
	'Indonesia' => '62',
	'Iran' => '98',
	'Iraq' => '964',
	'Ireland' => '353',
	'Israel' => '972',
	'Italy' => '39',
	'Ivory Coast' => '225',
	'Jamaica' => '876',
	'Japan' => '81',
	'Jordan' => '962',
	'Kazakhstan' => '7',
	'Kenya' => '254',
	'Khmer Republic (Cambodia/Kampuchea)' => '855',
	'Kiribati Republic (Gilbert Islands)' => '686',
	'Korea (South)' => '82',
	'Korea (North)' => '850',
	'Kuwait' => '965',
	'Kyrgyz Republic' => '996',
	'Latvia' => '371',
	'Laos' => '856',
	'Lebanon' => '961',
	'Lesotho' => '266',
	'Liberia' => '231',
	'Lithuania' => '370',
	'Libya' => '218',
	'Liechtenstein' => '423',
	'Luxembourg' => '352',
	'Macao' => '853',
	'Macedonia' => '389',
	'Madagascar' => '261',
	'Malawi' => '265',
	'Malaysia' => '60',
	'Maldives' => '960',
	'Mali' => '223',
	'Malta' => '356',
	'Marshall Islands' => '692',
	'Martinique (French Antilles)' => '596',
	'Mauritania' => '222',
	'Mauritius' => '230',
	'Mayolte' => '269',
	'Mexico' => '52',
	'Micronesia (F.S. of Polynesia)' => '691',
	'Moldova' => '373',
	'Monaco' => '33',
	'Mongolia' => '976',
	'Montserrat' => '473',
	'Morocco' => '212',
	'Mozambique' => '258',
	'Myanmar (former Burma)' => '95',
	'Namibia (former South-West Africa)' => '264',
	'Nauru' => '674',
	'Nepal' => '977',
	'Netherlands' => '31',
	'Netherlands Antilles' => '599',
	'Nevis' => '869',
	'New Caledonia' => '687',
	'New Zealand' => '64',
	'Nicaragua' => '505',
	'Niger' => '227',
	'Nigeria' => '234',
	'Niue' => '683',
	'North Korea' => '850',
	'North Mariana Islands (Saipan)' => '1 670',
	'Norway' => '47',
	'Oman' => '968',
	'Pakistan' => '92',
	'Palau' => '680',
	'Panama' => '507',
	'Papua New Guinea' => '675',
	'Paraguay' => '595',
	'Peru' => '51',
	'Philippines' => '63',
	'Poland' => '48',
	'Portugal (includes Azores)' => '351',
	'Puerto Rico' => '1 787',
	'Qatar' => '974',
	'Reunion (France)' => '262',
	'Romania' => '40',
	'Russia' => '7',
	'Rwanda (Rwandese Republic)' => '250',
	'Saipan' => '670',
	'San Marino' => '378',
	'Sao Tome and Principe' => '239',
	'Saudi Arabia' => '966',
	'Senegal' => '221',
	'Serbia and Montenegro' => '381',
	'Seychelles' => '248',
	'Sierra Leone' => '232',
	'Singapore' => '65',
	'Slovakia' => '421',
	'Slovenia' => '386',
	'Solomon Islands' => '677',
	'Somalia' => '252',
	'South Africa' => '27',
	'Spain' => '34',
	'Sri Lanka' => '94',
	'St. Helena' => '290',
	'St. Kitts/Nevis' => '869',
	'St. Pierre &(et) Miquelon (France)' => '508',
	'Sudan' => '249',
	'Suriname' => '597',
	'Swaziland' => '268',
	'Sweden' => '46',
	'Switzerland' => '41',
	'Syrian Arab Republic (Syria)' => '963',
	'Tahiti (French Polynesia)' => '689',
	'Taiwan' => '886',
	'Tajikistan' => '7',
	'Tanzania (includes Zanzibar)' => '255',
	'Thailand' => '66',
	'Togo (Togolese Republic)' => '228',
	'Tokelau' => '690',
	'Tonga' => '676',
	'Trinidad and Tobago' => '1 868',
	'Tunisia' => '216',
	'Turkey' => '90',
	'Turkmenistan' => '993',
	'Tuvalu (Ellice Islands)' => '688',
	'Uganda' => '256',
	'Ukraine' => '380',
	'United Arab Emirates' => '971',
	'United Kingdom' => '44',
	'Uruguay' => '598',
	'USA' => '1',
	'Uzbekistan' => '7',
	'Vanuatu (New Hebrides)' => '678',
	'Vatican City' => '39',
	'Venezuela' => '58',
	'Viet Nam' => '84',
	'Virgin Islands' => '1 340',
	'Wallis and Futuna' => '681',
	'Western Samoa' => '685',
	'Yemen' => '381',
	'Yemen Arab Republic (North Yemen)' => '967',
	'Zaire' => '243',
	'Zambia' => '260',
	'Zimbabwe' => '263',	
);

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
                  
                  <div class="field $firstnameErrorClass clearfix">
                    <label for="data_firstname">$firstname:</label>
                    <input type="text" id="data_firstname" name="data_firstname" placeholder="$firstname" value="$firstnameValue">
					$firstnameError
                  </div>
                  
                  <div class="field $lastnameErrorClass clearfix">
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
                
                <div class="group clearfix">
                  <span class="group-label">$phone:</span>
                  
                  <div class="field $countryCodeErrorClass clearfix">
                    <label for="data_countrycode">Country code:</label>
                    <div class="fake-select">
                      <span class="value"></span>
                      <span class="open-select icon-down"></span>
                      <select id="data_countrycode" name="data_countrycode">
$countryCodeOptions
                      </select>
                    </div>
                    $countryCodeError
                  </div>
                  
                  <div class="field $dataPhoneErrorClass clearfix">
                    <label for="data_phone"></label>
                    <input type="text" id="data_phone" name="data_phone" value="$dataPhoneValue">
                    $dataPhoneError
                  </div>
                </div>

                <div class="field $destinationErrorClass clearfix">
                  <label for="data_destination">$destination:</label>
                  <div class="fake-select">
                    <span class="value"></span>
                    <span class="open-select icon-down"></span>
					<select id="data_destination" name="data_destination">
$destinationOptions
                    </select>
				  </div>
				  $destinationError
                </div>

                <div class="group without-label clearfix">
                  <div class="field date from $dateOfArrivalErrorClass clearfix">
                    <label for="data_arrival">$dateOfArrival:</label>
					<input type="date" id="data_arrival" name="data_arrival" value="$dateOfArrivalValue">
                    $dateOfArrivalError
                  </div>

                  <div class="field date to $dateOfDepartureErrorClass clearfix">
                    <label for="data_departure">$dateOfDeparture:</label>
					<input type="date" id="data_departure" name="data_departure" value="$dateOfDepartureValue">
                    $dateOfDepartureError
                  </div>
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
                    <span class="value"></span>
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
            </section>

          </fieldset>
        </form>
      </div>

EOT;


html_end();
mysql_close($link);


?>

