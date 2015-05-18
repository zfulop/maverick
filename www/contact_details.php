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

$location = getLocation();
$lang = getCurrentLanguage();
$currency = getCurrency();

$_SESSION['booking_status'] = array('rooms', 'services', 'contact');

$link = db_connect($location);

$provideContactDetailsBelow = PROVIDE_CONTACT_DETAILS_BELOW;
$contactDetailsExplanation = CONTACT_DETAILS_EXPLANATION;
$yourNameTitle = YOUR_NAME;
$firstnameTitle = FIRST_NAME;
$lastnameTitle = LASTNAME;
$emailTitle = EMAIL;
$confirmEmailTitle = CONFIRM_EMAIL;
$phoneTitle = PHONE;
$nationalityTitle = NATIONALITY;
$addressTitle = ADDRESS_TITLE;
$streetTitle = STREET;
$cityTitle = CITY;
$zipcodeTitle = ZIP_CODE;
$countryTitle = COUNTRY;
$pleaseSelect = PLEASE_SELECT;
$bookNow = BOOK_NOW;
$commentTitle = COMMENT;

//$sessionStr = 'before: ' . print_r($_SESSION, true) . "\n";

if(isset($_SESSION['firstname']) and ($_SESSION['firstname'] == FIRST_NAME)) {
	unset($_SESSION['firstname']);
}
if(isset($_SESSION['lastname']) and ($_SESSION['lastname'] == LASTNAME)) {
	unset($_SESSION['lastname']);
}
if(isset($_SESSION['email']) and ($_SESSION['email'] == EMAIL)) {
	unset($_SESSION['email']);
}
if(isset($_SESSION['email2']) and ($_SESSION['email2'] == CONFIRM_EMAIL)) {
	unset($_SESSION['email2']);
}
if(isset($_SESSION['city']) and ($_SESSION['city'] == CITY)) {
	unset($_SESSION['city']);
}
if(isset($_SESSION['street']) and ($_SESSION['street'] == STREET)) {
	unset($_SESSION['street']);
}
if(isset($_SESSION['zip']) and ($_SESSION['zip'] == ZIP_CODE)) {
	unset($_SESSION['zip']);
}
if(isset($_SESSION['comment']) and ($_SESSION['comment'] == COMMENT)) {
	unset($_SESSION['comment']);
}
if(isset($_SESSION['nationality']) and ($_SESSION['nationality'] == NATIONALITY)) {
	unset($_SESSION['nationality']);
}
if(isset($_SESSION['countryCode']) and ($_SESSION['countryCode'] == '')) {
	unset($_SESSION['countryCode']);
}
if(isset($_SESSION['country']) and ($_SESSION['country'] == '')) {
	unset($_SESSION['country']);
}
if(isset($_SESSION['phone']) and ($_SESSION['phone'] == PHONE)) {
	unset($_SESSION['phone']);
}

//$sessionStr .= 'after: ' . print_r($_SESSION, true) . "\n";

$firstnameValue = isset($_SESSION['firstname']) ? $_SESSION['firstname'] : '';
$firstnameError = '';
$firstnameErrorClass = '';
if(isset($_SESSION['firstnameError'])) {
	$firstnameErrorClass = 'error';
	$firstnameError = '<p class="error">' . $_SESSION['firstnameError'] . '</p>';
	unset($_SESSION['firstnameError']);
}

$lastnameValue = isset($_SESSION['lastname'])  ? $_SESSION['lastname'] : '';
$lastnameError = '';
$lastnameErrorClass = '';
if(isset($_SESSION['lastnameError'])) {
	$lastnameErrorClass = 'error';
	$lastnameError = '<p class="error">' . $_SESSION['lastnameError'] . '</p>';
	unset($_SESSION['lastnameError']);
}

$emailValue = isset($_SESSION['email'])  ? $_SESSION['email'] : '';
$emailError = '';
$emailErrorClass = '';
if(isset($_SESSION['emailError'])) {
	$emailErrorClass = 'error';
	$emailError = '<p class="error">' . $_SESSION['emailError'] . '</p>';
	unset($_SESSION['emailError']);
}

$confirmEmailValue = isset($_SESSION['email2']) ? $_SESSION['email2'] : '';
$confirmEmailError = '';
$confirmEmailErrorClass = '';
if(isset($_SESSION['confirmEmailError'])) {
	$confirmEmailErrorClass = 'error';
	$confirmEmailError = '<p class="error">' . $_SESSION['confirmEmailError'] . '</p>';
	unset($_SESSION['confirmEmailError']);
}

$countryCodeOptions = '';
foreach($countryCodes as $countryName => $code) {
	$selected = (isset($_SESSION['countryCode']) and $code == $_SESSION['countryCode']) ? ' selected="selected"' : '';
	$countryCodeOptions .= "                        <option value=\"+$code\"$selected>$countryName (+$code)</option>\n";
}
$countryCodeError = '';
$countryCodeErrorClass = '';
if(isset($_SESSION['countryCodeError'])) {
	$countryCodeErrorClass = 'error';
	$countryCodeError = '<p class="error">' . $_SESSION['countryCodeError'] . '</p>';
	unset($_SESSION['countryCodeError']);
}

$dataPhoneValue = (isset($_SESSION['phone']) and $_SESSION['phone'] != PHONE) ? $_SESSION['phone'] : '';
$dataPhoneError = '';
$dataPhoneErrorClass = '';
if(isset($_SESSION['dataPhoneError'])) {
	$dataPhoneErrorClass = 'error';
	$dataPhoneError = '<p class="error">' . $_SESSION['dataPhoneError'] . '</p>';
	unset($_SESSION['dataPhoneError']);
}

$nationalityOptions = '';
foreach($countryCodes as $countryName => $code) {
	$selected = (isset($_SESSION['nationality']) and $countryName == $_SESSION['nationality']) ? ' selected="selected"' : '';
	$nationalityOptions .= "                        <option value=\"$countryName\"$selected>$countryName</option>\n";
}
$nationalityError = '';
$nationalityErrorClass = '';
if(isset($_SESSION['nationalityError'])) {
	$nationalityErrorClass = 'error';
	$nationalityError = '<p class="error">' . $_SESSION['nationalityError'] . '</p>';
	unset($_SESSION['nationalityError']);
}

$cityValue = isset($_SESSION['city']) ? $_SESSION['city'] : '';
$cityError = '';
$cityErrorClass = '';
if(isset($_SESSION['cityError'])) {
	$cityErrorClass = 'error';
	$cityError = '<p class="error">' . $_SESSION['cityError'] . '</p>';
	unset($_SESSION['cityError']);
}

$streetValue = isset($_SESSION['street']) ? $_SESSION['street'] : '';
$streetError = '';
$streetErrorClass = '';
if(isset($_SESSION['streetError'])) {
	$streetErrorClass = 'error';
	$streetError = '<p class="error">' . $_SESSION['streetError'] . '</p>';
	unset($_SESSION['streetError']);
}


$zipcodeValue = isset($_SESSION['zip']) ? $_SESSION['zip'] : '';
$zipcodeError = '';
$zipcodeErrorClass = '';
if(isset($_SESSION['zipcodeError'])) {
	$zipcodeErrorClass = 'error';
	$zipcodeError = '<p class="error">' . $_SESSION['zipcodeError'] . '</p>';
	unset($_SESSION['zipcodeError']);
}

$countryOptions = "";
foreach($countryCodes as $countryName => $code) {
	$selected = (isset($_SESSION['country']) and ($_SESSION['country'] == $countryName)) ? ' selected="selected"' : '';
	$countryOptions .= "                        <option value=\"$countryName\"$selected>$countryName</option>\n";
}
$countryError = '';
$countryErrorClass = '';
if(isset($_SESSION['countryError'])) {
	$countryErrorClass = 'error';
	$countryError = '<p class="error">' . $_SESSION['countryError'] . '</p>';
	unset($_SESSION['countryError']);
}

$commentValue = isset($_SESSION['comment']) ? $_SESSION['comment'] : '';




html_start(CONTACT_DETAILS);

// echo "<pre>" . print_r($_SESSION, true) . "</pre>\n";

$bookNowUrl = $location . '_book_now.php';

echo <<<EOT

      <div class="fluid-wrapper booking">
        <form action="$bookNowUrl" class="update-summary" data-refresh="json_update_summary.php" method="post" accept-charset="utf-8">
          <fieldset>
            <section id="contact-details" class="common-form clearfix">
              <h1>$provideContactDetailsBelow</h1>
              
              <p class="info">
                $contactDetailsExplanation
              </p>

              <div class="fields">
                <div class="group clearfix">
                  <span class="group-label">$yourNameTitle *:</span>
                  
                  <div class="field $firstnameErrorClass clearfix">
                    <label for="data_firstname">$firstnameTitle:</label>
                    <input type="text" id="data_firstname" name="data_firstname" placeholder="$firstnameTitle" value="$firstnameValue">
					$firstnameError
                  </div>
                  
                  <div class="field $lastnameErrorClass clearfix">
                    <label for="data_last_name">$lastnameTitle:</label>
                    <input type="text" id="data_last_name" name="data_last_name" placeholder="$lastnameTitle" value="$lastnameValue">
					$lastnameError
                  </div>
                </div>
                
                <div class="field $emailErrorClass clearfix">
                  <label for="data_email">$emailTitle *:</label>
				  <input type="email" id="data_email" name="data_email" value="$emailValue">
                  $emailError
                </div>
                
                <div class="field $confirmEmailErrorClass clearfix">
                  <label for="data_email2">$confirmEmailTitle *:</label>
                  <input type="email" id="data_email2" name="data_email2" value="$confirmEmailValue">
                  $confirmEmailError
                </div>
                
                <div class="group clearfix">
                  <span class="group-label">$phoneTitle *:</span>
                  
                  <div class="field $countryCodeErrorClass clearfix">
                    <label for="data_countrycode">Country code *:</label>
                    <div class="fake-select">
                      <span class="value"></span>
                      <span class="open-select icon-down"></span>
                      <select id="data_countrycode" name="data_countrycode">
                        <option value="">$pleaseSelect</option>
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
                
                <div class="field $nationalityErrorClass clearfix">
                  <label for="data_nationality">$nationalityTitle *:</label>
                  <div class="fake-select">
                    <span class="value"></span>
                    <span class="open-select icon-down"></span>
                    <select id="data_nationality" name="data_nationality">
                      <option value="">$pleaseSelect</option>
$nationalityOptions
                    </select>
				  </div>
                  $nationalityError
                </div>

                <div class="field $streetErrorClass clearfix">
                  <label for="data_street">$addressTitle *:</label>
                  <input type="text" id="data_street" name="data_street" placeholder="$streetTitle" value="$streetValue">
                  $streetError
                </div>
                 
                <div class="group hidden-label clearfix">
                  <div class="field $cityErrorClass clearfix" style="width: 49%;margin-right:2%;">
                    <label for="data_city">$cityTitle:</label>
                    <input type="text" id="data_city" name="data_city" placeholder="$cityTitle" value="$cityValue">
                    $cityError
                  </div>
                  
                  <div class="field $zipcodeErrorClass clearfix" style="width: 49%;">
                    <label for="data_zip">$zipcodeTitle:</label>
					<input type="text" id="data_zip" name="data_zip" placeholder="$zipcodeTitle" value="$zipcodeValue">
                    $zipcodeError
                  </div>
                </div>
                
                <div class="field $countryErrorClass hidden-label clearfix">
                  <label for="data_country">$countryTitle:</label>
                  <div class="fake-select">
                    <span class="value"></span>
                    <span class="open-select icon-down"></span>
                    <select id="data_country" name="data_country">
					  <option value="">$pleaseSelect</option>
$countryOptions
                    </select>
				  </div>
                  $countryError
                </div>

                <div class="field clearfix">
                  <label for="data_comment">$commentTitle:</label>
                  <textarea id="data_comment" name="data_comment">$commentValue</textarea>
                </div>
                
                <button type="submit">$bookNow</button>
              </div>
            </section>

EOT;

echo getBookingSummaryHtml(BOOK_NOW);

echo <<<EOT
          </fieldset>
        </form>
      </div>

EOT;


html_end();
mysql_close($link);


?>

