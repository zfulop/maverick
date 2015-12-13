<?php

require('includes.php');
require('includes/common_booking.php');
require(RECEPCIO_BASE_DIR . 'room_booking.php');


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
            <section id="group-booking" class="common-form">
              <h1>$provideContactDetailsBelow</h1>
              
              <p class="info">
                $contactDetailsExplanation
              </p>

              <div class="fields">
                <div class="group">
                  <span class="group-label">$yourNameTitle *:</span>
                  
                  <div class="field $firstnameErrorClass">
                    <label for="data_firstname">$firstnameTitle:</label>
                    <input type="text" id="data_firstname" name="data_firstname" placeholder="$firstnameTitle" value="$firstnameValue">
					$firstnameError
                  </div>
                  
                  <div class="field $lastnameErrorClass">
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
                
                <div class="group">
                  <span class="group-label">$phoneTitle *:</span>
                  
                  <div class="field $countryCodeErrorClass">
                    <label for="data_countrycode">Country code *:</label>
                    <div class="fake-select">
                      <span class="value">$pleaseSelect</span>
                      <span class="open-select icon-down"></span>
                      <select id="data_countrycode" name="data_countrycode">
                        <option value="">$pleaseSelect</option>
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
                
                <div class="field $nationalityErrorClass clearfix">
                  <label for="data_nationality">$nationalityTitle *:</label>
                  <div class="fake-select">
                    <span class="value">$pleaseSelect</span>
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
                 
                <div class="group">
                  <span class="group-label">&nbsp;</span>
                  <div class="field $cityErrorClass">
                    <label for="data_city">$cityTitle:</label>
                    <input type="text" id="data_city" name="data_city" placeholder="$cityTitle" value="$cityValue">
                    $cityError
                  </div>
                  
                  <div class="field $zipcodeErrorClass">
                    <label for="data_zip">$zipcodeTitle:</label>
					<input type="text" id="data_zip" name="data_zip" placeholder="$zipcodeTitle" value="$zipcodeValue">
                    $zipcodeError
                  </div>
                </div>
                
                <div class="field $countryErrorClass hidden-label">
                  <label for="data_country">$countryTitle *:</label>
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

