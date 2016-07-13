<?php

require('includes.php');
require('includes/common_booking.php');
require(RECEPCIO_BASE_DIR . 'room_booking.php');

$lang = getCurrentLanguage();
$currency = getCurrency();

$firstname = $_REQUEST['firstname'];
$lastname = $_REQUEST['lastname'];
$email = $_REQUEST['email'];
$email2 = $_REQUEST['email2'];
$countryCode = $_REQUEST['countrycode'];
$phone = $_REQUEST['phone'];
$destination = $_REQUEST['destination'];
$nationality = $_REQUEST['nationality'];
$comment = $_REQUEST['comment'];

$_SESSION['contact_firstname'] = $_REQUEST['firstname'];
$_SESSION['contact_lastname'] = $_REQUEST['lastname'];
$_SESSION['contact_email'] = $_REQUEST['email'];
$_SESSION['contact_email2'] = $_REQUEST['email2'];
$_SESSION['contact_countryCode'] = $_REQUEST['countrycode'];
$_SESSION['contact_phone'] = $_REQUEST['phone'];
$_SESSION['contact_destination'] = $_REQUEST['destination'];
$_SESSION['contact_nationality'] = $_REQUEST['nationality'];
$_SESSION['contact_comment'] = $_REQUEST['comment'];


$error = false;
if(strlen(trim($firstname)) < 1) {
	$_SESSION['contact_firstnameError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($lastname)) < 1) {
	$_SESSION['contact_lastnameError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($email)) < 1) {
	$_SESSION['contact_emailError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($email2)) < 1) {
	$_SESSION['contact_confirmError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if($email != $email2) {
	$_SESSION['contact_confirmError'] = EMAIL_MISMATCH;
	$error = true;
}

if(strlen(trim($countryCode)) < 1) {
	$_SESSION['contact_countryCodeError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($phone)) < 1) {
	$_SESSION['contact_dataPhoneError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($nationality)) < 1) {
	$_SESSION['contact_nationalityError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($destination)) < 1) {
	$_SESSION['contact_destinationError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}

if($error) {
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}

$_SESSION['contact_firstname'] = '';
$_SESSION['contact_lastname'] = '';
$_SESSION['contact_email'] = '';
$_SESSION['contact_email2'] = '';
$_SESSION['contact_countryCode'] = '';
$_SESSION['contact_phone'] = '';
$_SESSION['contact_destination'] = '';
$_SESSION['contact_nationality'] = '';
$_SESSION['contact_comment'] = '';


$thankYou = THANK_YOU;
$thanksForContacting = THANKS_FOR_CONTACTING;
$ourTeamWillContactYouShortly = OUR_TEAM_WILL_CONTACT_YOU_SHORTLY;
$nameTitle = NAME;
$nameValue = $firstname . ' ' . $lastname;
$emailTitle = EMAIL;
$emailValue = $email;
$phoneTitle = PHONE;
$phoneValue = $countryCode . ' ' . $phone;
$destinationTitle = DESTINATION;
$destinationValue = $destination;
$nationalityTitle = NATIONALITY;
$nationalityValue = $nationality;
$commentTitle = COMMENT;
$commentValue = $comment;

$location = getLocation();
$link = db_connect($location);

$message = <<<EOT
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<body>
  <table width="100%" cellspacing="0" border="0" cellpadding="0" bgcolor="#ffffff">
    <tr>
      <td align="center">
        <table width="600" cellspacing="0" border="0" cellpadding="0">
          <tr>
            <td width="40" bgcolor="#1d0328"></td>
            <td width="520" height="120" bgcolor="#1d0328" valign="middle">
              <img width="130" height="64" src="cid:logo" style="display: block;">
            </td>
            <td width="40" bgcolor="#1d0328"></td>
          </tr>
          <tr>
            <td colspan="3" height="10" bgcolor="#f7fac1"></td>
          </tr>
          <tr>
            <td></td>
            <td>
              <table width="100%" cellspacing="0" border="0" cellpadding="0">
                <!-- space --><tr><td height="35"></td></tr>
                <tr>
                  <td>
                    <font face="arial" color="#252525" style="font-size: 25px; line-height: 1.2;">
                      $thanksForContacting<br>
                      $ourTeamWillContactYouShortly<br>
                    </font>
                  </td>
                </tr>
                <!-- space --><tr><td height="25"></td></tr>
                <tr>
                  <td>
                    <table width="100%" cellspacing="0" border="0" cellpadding="0">
                      <tr>
                        <td width="15"></td>
                        <td width="490">
                          <table width="100%" cellspacing="0" border="0" cellpadding="0">
                            <tr>
                              <td width="160"><font face="arial" color="#252525" style="font-size: 14px;">$nameTitle:</font></td>
                              <td width="330"><font face="arial" color="#252525" style="font-size: 14px;">$nameValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$emailTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$emailValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$phoneTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$phoneValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$destinationTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$destinationValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$nationalityTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$nationalityValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$commentTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$commentValue</font></td>
                            </tr>
                          </table>
                        </td>
                        <td width="15"></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>

EOT;

$toEmail = constant('CONTACT_EMAIL_' . strtoupper($destination));
$toName = 'Reservation - ' . getLocationName();
$inlineAttachments = array(	
	'logo' => EMAIL_IMG_DIR . 'logo-' . $location . '.jpg',
);

sendMail($email, $firstname . ' ' . $lastname, $toEmail, $toName, 'Website contact request', $message, $inlineAttachments);
sendMail($toEmail, $toName, $email, $firstname . ' ' . $lastname, 'Maverick - ' . CONTACT, $message, $inlineAttachments);

html_start(CONTACT);

echo <<<EOT

      <h1 class="page-title page-title-groups">$thankYou</h1>
      
      <div class="fluid-wrapper booking">
        <section id="thank-you" class="clearfix">
          <h1>$thanksForContacting</h1>
          
          <p class="info">$ourTeamWillContactYouShortly</p>
          
          <iframe class="likebox" src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FMaverick-Hostel%2F115569091837790&amp;width&amp;height=258&amp;colorscheme=light&amp;show_faces=true&amp;header=false&amp;stream=false&amp;show_border=false"></iframe>
        </section>
      </div>


<!-- Google Code for Kapcsolati_oldalon_urlap_bekuldes Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 999565014;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "CP_CCLzD0mcQ1s3Q3AM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/999565014/?label=CP_CCLzD0mcQ1s3Q3AM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>


EOT;

html_end();
mysql_close($link);

?>

