<?php

require('../includes.php');
require('../../recepcio/room_booking.php');
require('dict.php');


$link = db_connect();

foreach($_REQUEST as $key => $value) {
	$_SESSION["booking_" . $key] = $value;
}

$year = $_SESSION['booking_year'];
$month = $_SESSION['booking_month'];
$day = $_SESSION['booking_day'];
$nights = $_SESSION['booking_nights'];

if(strlen($month) < 2) {
	$month = '0' . $month;
}
if(strlen($day) < 2) {
	$day = '0' . $day;
}

$lastNightTS = strtotime("$year-$month-$day");
if($nights > 1)  {
	$lastNightTS = strtotime("$year-$month-$day +" . ($nights-1) . " day");
}

html_start('BookingAndPrices', 'Booking', BOOK_NOW);

$datesTitle = DATES;
$firstNightTitle = FIRST_NIGHT;
$lastNightTitle = LAST_NIGHT;
$firstNight = $year . ' ' . $MONTHS[$month] . ' ' . $day;
$lastNight = date('Y', $lastNightTS) . ' ' . $MONTHS[date('n', $lastNightTS)]	. ' ' . date('d', $lastNightTS);
echo <<<EOT
<table style="width: 100%;">
<tr class="title"><th style="width: 300px;" colspan="2">$datesTitle</th></tr>
<tr class="content"><td><strong>$firstNightTitle:</strong></td><td>$firstNight</td></tr>
<tr class="content"><td><strong>$lastNightTitle:</strong></td><td>$lastNight</td></tr>
</table>
EOT;

require('preview_booking_sum.inc');

echo "			<form action=\"do_book_now.php\" accept-charset=\"utf-8\" method=\"POST\"><fieldset style=\"padding:0px;\">\n";
$name = "";
if(isset($_SESSION["booking_name"])) {
	$name = $_SESSION["booking_name"];
}
$address = "";
if(isset($_SESSION["booking_address"])) {
	$address = $_SESSION["booking_address"];
}
$femaleSelected = "";
$maleSelected = "";
$maleAndFemaleSelected = "";
if(isset($_SESSION["booking_gender"])) {
	if($_SESSION["booking_gender"] == "FEMALE")
		$femaleSelected = " selected";
	if($_SESSION["booking_gender"] == "MALE")
		$maleSelected = " selected";
	if($_SESSION["booking_gender"] == "MALE_AND_FEMALE")
		$maleAndFemaleSelected = " selected";
}
$email = "";
if(isset($_SESSION["booking_email"])) {
	$email = $_SESSION["booking_email"];
}
$telephone = "";
if(isset($_SESSION["booking_telephone"])) {
	$telephone= $_SESSION["booking_telephone"];
}
$nationality = "";
if(isset($_SESSION["booking_nationality"])) {
	$nationality = $_SESSION["booking_nationality"];
}
if(strlen(trim($nationality)) < 1) {
	$nationality = getClientCountryName();
}
$nationalityOptions = "\t\t\t\t\t\t<option value=\"\">" . SELECT_COUNTRY . "</option>";
$countries = file_get_contents('../includes/countries.txt');
foreach(explode("\n", $countries) as $cntry) {
	$cntry = trim($cntry);
	if(strlen($cntry) < 1)
		continue;

	$nationalityOptions .= "\t\t\t\t\t\t<option value=\"$cntry\"" . ($cntry == $nationality ? " selected" : "") . ">$cntry</option>\n";
}
$comment = '';
if(isset($_SESSION["booking_comment"])) {
	$comment = $_SESSION["booking_comment"];
}

$nameTitle = NAME;
$addressTitle = ADDRESS;
$nationalityTitle = NATIONALITY;
$genderTitle = GENDER;
$femaleTitle = FEMALE;
$maleTitle = MALE;
$maleAndFemaleTitle = MALE_AND_FEMALE;
$emailTitle = EMAIL;
$emailCommentTitle = EMAIL_COMMENT;
$telephoneTitle = TELEPHONE;
$telephoneCommentTitle = TELEPHONE_COMMENT;
$commentTitle = COMMENT;
$bookNowTitle = BOOK_NOW;
$bookingInfo = BOOKING_INFORMATION;
echo <<<EOT
			<table style="width: 100%; margin-top: 15px; margin-bottom: 15px;">
				<tr class="title"><th colspan="2">$bookingInfo</th></tr>
				<tr class="content">
					<td><strong>$nameTitle</strong></td>
					<td><input name="name" value="$name" style="width: 200px;"></td>
				</tr>
				<tr class="content">
					<td><strong>$addressTitle</strong></td>
					<td><textarea name="address" style="width: 200px; height: 50px;">$address</textarea></td>
				</tr>
				<tr class="content">
					<td><strong>$nationalityTitle</strong></td>
					<td><select name="nationality" style="width: 200px;">
$nationalityOptions
					</select</td>
				</tr>
				<tr class="content">
					<td><strong>$genderTitle</strong></td>
					<td><select name="gender">
						<option value="FEMALE"$femaleSelected>$femaleTitle</option>
						<option value="MALE"$maleSelected>$maleTitle</option>
						<option value="MALE_AND_FEMALE"$maleAndFemaleSelected>$maleAndFemaleTitle</option>
					</select></td>
				</tr>
				<tr class="content">
					<td><strong>$emailTitle</strong></td>
					<td><input name="email" value="$email" style="width: 200px;"> ($emailCommentTitle)</td>
				</tr>
				<tr class="content">
					<td><strong>$telephoneTitle</strong></td>
					<td><input name="telephone" value="$telephone" style="width: 200px;"> ($telephoneCommentTitle)</td>
				</tr>
				<tr class="content">
					<td><strong>$commentTitle</strong></td>
					<td><input name="comment" value="$comment" style="width: 200px;"></td>
				</tr>

			</table>

			<input type="submit" class="input_btn2" value="$bookNowTitle">
			</fieldset></form>

EOT;

html_end('BookingAndPrices', 'Booking');


mysql_close($link);

?>
