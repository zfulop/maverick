<?php

require('includes.php');
require('includes/common_booking.php');
require('../recepcio/room_booking.php');

$lang = getCurrentLanguage();
$currency = getCurrency();

$firstname = $_REQUEST['data_firstname'];
$lastname = $_REQUEST['data_last_name'];
$email = $_REQUEST['data_email'];
$email2 = $_REQUEST['data_email2'];
$countryCode = $_REQUEST['data_countrycode'];
$phone = $_REQUEST['data_phone'];
$destination = $_REQUEST['data_destination'];
$dateOfArrival = $_REQUEST['data_arrival'];
$dateOfDeparture = $_REQUEST['data_departure'];
$groupType = $_REQUEST['data_grouptype'];
$numOfParticipants = $_REQUEST['data_number'];
$nationality = $_REQUEST['data_nationality'];
$roomType = $_REQUEST['data_roomtype'];
$comment = $_REQUEST['data_comment'];

$_SESSION['group_firstname'] = $_REQUEST['data_firstname'];
$_SESSION['group_lastname'] = $_REQUEST['data_last_name'];
$_SESSION['group_email'] = $_REQUEST['data_email'];
$_SESSION['group_email2'] = $_REQUEST['data_email2'];
$_SESSION['group_countryCode'] = $_REQUEST['data_countrycode'];
$_SESSION['group_phone'] = $_REQUEST['data_phone'];
$_SESSION['group_destination'] = $_REQUEST['data_destination'];
$_SESSION['group_dateOfArrival'] = $_REQUEST['data_arrival'];
$_SESSION['group_dateOfDeparture'] = $_REQUEST['data_departure'];
$_SESSION['group_groupType'] = $_REQUEST['data_grouptype'];
$_SESSION['group_numOfParticipants'] = $_REQUEST['data_number'];
$_SESSION['group_nationality'] = $_REQUEST['data_nationality'];
$_SESSION['group_roomType'] = $_REQUEST['data_roomtype'];
$_SESSION['group_comment'] = $_REQUEST['data_comment'];


$error = false;
if(strlen(trim($firstname)) < 1) {
	$_SESSION['group_firstnameError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($lastname)) < 1) {
	$_SESSION['group_lastnameError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($email)) < 1) {
	$_SESSION['group_emailError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($email2)) < 1) {
	$_SESSION['group_confirmError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if($email != $email2) {
	$_SESSION['group_confirmError'] = EMAIL_MISMATCH;
	$error = true;
}

if(strlen(trim($countryCode)) < 1) {
	$_SESSION['group_countryCodeError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($phone)) < 1) {
	$_SESSION['group_dataPhoneError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($nationality)) < 1) {
	$_SESSION['group_nationalityError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($destination)) < 1) {
	$_SESSION['group_destinationError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($dateOfArrival)) < 1) {
	$_SESSION['group_dateOfArrivalError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($dateOfDeparture)) < 1) {
	$_SESSION['group_dateOfDepartureError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($groupType)) < 1) {
	$_SESSION['group_groupTypeError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(strlen(trim($numOfParticipants)) < 1) {
	$_SESSION['group_numOfParticipantsError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}
if(intval($numOfParticipants) < 1) {
	$_SESSION['group_numOfParticipantsError'] = FIELD_HAS_TO_BE_POSITIVE_NUMBER;
	$error = true;
}

if(strlen(trim($roomType)) < 1) {
	$_SESSION['group_roomTypeError'] = FIELD_CANNOT_BE_EMPTY;
	$error = true;
}

if($error) {
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}

$groupBooking = GROUP_BOOKING;
$dontForgetToLikeUs = DONT_FORGET_TO_LIKE_US;
$thankYou = THANK_YOU;
$thanksForContactingForGroupBooking = THANKS_FOR_CONTACTING_FOR_GROUP_BOOKING;
$ourTeamWillContactYouShortly = OUR_TEAM_WILL_CONTACT_YOU_SHORTLY;
$nameTitle = NAME;
$nameValue = $firstname . ' ' . $lastname;
$emailTitle = EMAIL;
$emailValue = $email;
$phoneTitle = PHONE;
$phoneValue = $countryCode . ' ' . $phone;
$destinationTitle = DESTINATION;
$destinationValue = $destination;
$arriveDateTitle = DATE_OF_ARRIVAL;
$arriveDateValue = $dateOfArrival;
$departureDateTitle = DATE_OF_DEPARTURE;
$departureDateValue = $dateOfDeparture;
$groupTypeTitle = GROUP_TYPE;
$groupTypeValue = $groupType;
$numOfParticipantsTitle = NUMBER_OF_PARTICIPANTS;
$numOfParticipantsValue = $numOfParticipants;
$nationalityTitle = NATIONALITY;
$nationalityValue = $nationality;
$roomTypeTitle = ROOM_TYPE;
$roomTypeValue = $roomType;
$commentTitle = COMMENT;
$commentValue = $comment;


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
              <img width="130" height="64" src="logo.jpg" style="display: block;">
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
                      $thanksForContactingForGroupBooking<br>
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
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$arriveDateTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$arriveDateValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$departureDateTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$departureDateValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$groupTypeTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$groupTypeValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$numOfParticipantsTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$numOfParticipantsValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$nationalityTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$nationalityValue</font></td>
                            </tr>
                            <!-- space --><tr><td colspan="2" height="10"></td></tr>
                            <tr>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$roomTypeTitle:</font></td>
                              <td><font face="arial" color="#252525" style="font-size: 14px;">$roomTypeValue</font></td>
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

if($destination == 'both') {
	$toEmail = CONTACT_EMAIL_LODGE;
	$toName = 'Reservation - Maverick Hostels';
} else {
	$toEmail = constant('CONTACT_EMAIL_' . strtoupper($destination));
	$toName = 'Reservation - ' . getLocationName();
}
sendMail($email, $firstname . ' ' . $lastname, $toEmail, $toName, 'Group booking request', $message, $inlineAttachments = array(), $attachments = array());
sendMail($toEmail, $toName, $email, $firstname . ' ' . $lastname, 'Group booking request', $message, $inlineAttachments = array(), $attachments = array());

html_start(GROUP_BOOKING);

echo <<<EOT

      <h1 class="page-title page-title-groups">$thankYou</h1>
      
      <div class="fluid-wrapper booking">
        <section id="thank-you" class="clearfix">
          <h1>$thanksForContactingForGroupBooking</h1>
          
          <p class="info">$ourTeamWillContactYouShortly</p>
          
          <iframe class="likebox" src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FMaverick-Hostel%2F115569091837790&amp;width&amp;height=258&amp;colorscheme=light&amp;show_faces=true&amp;header=false&amp;stream=false&amp;show_border=false"></iframe>
        </section>
      </div>


EOT;

html_end();

?>

