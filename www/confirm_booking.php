<?php

require('includes.php');

$location = getLocation();
$lang = getCurrentLanguage();
$currency = getCurrency();

$link = db_connect($location);

$nameValue = '';
$emailValue = '';
$phoneValue = '';
$fnightValue = '';
$lnightValue = '';

$message = '';
$_SESSION['confirm_booking_validated'] = false;
if(isset($_REQUEST['confirmCode'])) {
	$confirmCode = $_REQUEST['confirmCode'];
	$idx = strpos($confirmCode, 'A');
	$descrId = substr($confirmCode, 0, $idx);
	$code = substr($confirmCode, $idx + 1);
	$message .= "id: $descrId<br>\n";
	$message .= "code: $code<br>\n";
	$sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
	$result = mysql_query($sql, $link);
	$message .= "sql: $sql<br>\n";
	$message .= "rows returned: " . mysql_num_rows($result) . "<br>\n";
	if($result and (mysql_num_rows($result) > 0)) {
		$row = mysql_fetch_assoc($result);
		$nameValue = $row['name'];
		$emailValue = $row['email'];
		$phoneValue = $row['telephone'];
		$fnightValue = $row['first_night'];
		$lnightValue = $row['last_night'];
		$message .= "<pre>" . print_r($row, true) . "</pre><br>\n";
		$message .= "email: $emailValue<br>\n";
		if(crypt($emailValue, $code) == $code) {
			$row = mysql_fetch_assoc($result);
			$_SESSION['confirm_booking_validated'] = true;
		} else {
			$message .= "not validated<br>\n";
		}
	} else {
		$message .= "no result for $sql<br>\n";
	}
} else {
	$message .= "confirm code is not set<br>\n";
}


$confirmBooking = CONFIRM_BOOKING;

html_start(CONFIRM_BOOKING);

$cannotConfirmBooking = CANNOT_CONFIRM_BOOKING;
if(!$_SESSION['confirm_booking_validated']) {
	echo <<<EOT
      <h1 class="page-title page-title-booknow">$confirmBooking</h1>

      <div class="fluid-wrapper page">
		<section id="group-booking" class="common-form">
           $cannotConfirmBooking
		</section>
      </div>

EOT;
	html_end();
	mysql_close($link);
	return;
}

$_SESSION['confirm_booking_descr_id'] = $descrId;

$sql = "SELECT b.*, l.value AS room_name FROM bookings b INNER JOIN rooms r ON b.room_id=r.id INNER JOIN lang_text l on (l.table_name='room_types' and l.column_name='name' and l.row_id=r.room_type_id AND l.lang='$lang') WHERE b.description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo $cannotConfirmBooking;
	html_end();
	mysql_close($link);
	return;
}

$total = 0;
$roomsValue = '<table style="border-spacing: 10px;border-collapse: separate;"><tr><th>' . ROOM . '</th><th>' . TYPE . '</th><th>' . NUMBER_OF_PERSON . '</th>' . /*'<th>' . PAYMENT . '</th>' . */'</tr>';
while($row = mysql_fetch_assoc($result)) {
	$payment = intval(convertAmount($row['room_payment'], 'EUR', $currency, substr($row['creation_time'], 0, 10)));
	$roomsValue .= '<tr><td>' . $row['room_name'] . '</td><td>' . constant($row['booking_type']) . '</td><td style="text-align:center;">' . $row['num_of_person'] . '</td>' . /*'<td style="text-align:right;">' . $payment . $currency . '</td>' . */'</tr>';
	$total += $payment;
}
$roomsValue .= '</table>';


$sql = "SELECT p.* FROM payments p WHERE p.booking_description_id=$descrId AND storno<>1";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get payments for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo 'Cannot get booking data';
	mysql_close($link);
	return;
}
$hasPayments = (mysql_num_rows($result) > 0);
$payments = '<table style="border-spacing: 10px;border-collapse: separate;"><tr><th>' . NAME . '</th><th>' . PRICE . '</th></tr>';
while($row = mysql_fetch_assoc($result)) {
	$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_payment'], 0, 10)));
	$amount = intval(convertAmount($amount, 'EUR', $currency, substr($row['time_of_payment'], 0, 10)));
	$title = $row['type'];
	if($row['comment'] == '*booking deposit*') {
		$title = DEPOSIT;
	}
	$payments .= '<tr><td>' . $title . '</td><td align="right">' . $amount . $currency . '</td></tr>';
	$total -= $amount;
}
$payments .= '</table>';


$sql = "SELECT sc.*, s.price, s.currency AS svcCurr, l.value AS title FROM service_charges sc INNER JOIN services s ON (sc.type=s.service_charge_type AND s.free_service=0) INNER JOIN lang_text l on (l.table_name='services' and l.column_name='title' and l.row_id=s.id AND l.lang='$lang') WHERE sc.booking_description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get rooms for the booking when sending BCR in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	echo 'Cannot get booking data';
	mysql_close($link);
	return;
}
$hasServices = (mysql_num_rows($result) > 0);
$services = '<table style="border-spacing: 10px;border-collapse: separate;"><tr><th>' . NAME . '</th><th>' . OCCASION . '</th>'. /*'<th>' . PRICE . '</th>' . */'</tr>';
while($row = mysql_fetch_assoc($result)) {
	$amount = intval(convertAmount($row['amount'], $row['currency'], 'EUR', substr($row['time_of_service'], 0, 10)));
	$prc = intval(convertAmount($row['price'], $row['svcCurr'], 'EUR', substr($row['time_of_service'], 0, 10)));
	$occasion = intval($amount / $prc);
	$amount = intval(convertAmount($amount, 'EUR', $currency, substr($row['time_of_service'], 0, 10)));
	$services .= '<tr><td>' . $row['title'] . '</td><td>' . $occasion . '</td>' . /*'<td align="right">' . $amount . $currency . '</td>' . */'</tr>';
	$total += $amount;
}
$services .= '</table>';






$fnightValue = str_replace('/', '-', $fnightValue);
$lnightValue = str_replace('/', '-', $lnightValue);

$name = NAME;
$email = EMAIL;
$phone = PHONE;
$arriveDate = DATE_OF_ARRIVAL;
$arriveDateValue = $fnightValue;
$departureDate = DATE_OF_DEPARTURE;
$departureDateValue = date('Y-m-d', strtotime($lnightValue . ' +1 day'));
$locationTitle = LOCATION_TITLE;
$locationValue = constant('LOCATION_NAME_' . strtoupper($location));
$rooms = ROOMS;
$servicesTitle = EXTRA_SERVICES;
$paymentsTitle = PAYMENT;
$arriveTime = ARRIVE_TIME;
$comment = COMMENT;
$balance = BALANCE;

$servicesHtml = '';
if($hasServices) {
	$serivcesHtml = <<<EOT
                <div class="field clearfix">
                  <label for="services">$servicesTitle:</label>
				  <div>$services</div>
                </div>

EOT;
}
$paymentsHtml = '';
/*
if($hasPayments) {
	$paymentsHtml = <<<EOT
                <div class="field clearfix">
                  <label for="payments">$paymentsTitle:</label>
				  <div>$payments</div>
                </div>

EOT;
}
*/

echo <<<EOT

      <h1 class="page-title page-title-groups">$confirmBooking</h1>

      <div class="fluid-wrapper page">
        <section id="group-booking" class="common-form">
          <form action="confirm_booking_submit.php" method="post" accept-charset="utf-8">
            <input type="hidden" name="confirmCode" value="$confirmCode">
            <fieldset>
              <div class="fields">
                <div class="field clearfix">
                  <label for="name">$name:</label>
				  <span style="line-height: 45px;">$nameValue</span>
                </div>

                <div class="field clearfix">
                  <label for="email">$email:</label>
				  <span style="line-height: 45px;">$emailValue</span>
                </div>

                <div class="field clearfix">
                  <label for="phone">$phone:</label>
                  <span style="line-height: 45px;">$phoneValue</span>
                </div>

                <div class="field date clearfix">
                  <label for="arriveDate">$arriveDate:</label>
                  <span style="line-height: 45px;">$arriveDateValue</span>
                </div>
<!--
                <div class="field date clearfix">
                  <label for="arriveDate">$arriveDate:</label>
                  <input type="date" name="arriveDate" value="$arriveDateValue">
                </div>
-->
                <div class="field clearfix">
                  <label for="departureDate">$departureDate:</label>
                  <span style="line-height: 45px;">$departureDateValue</span>
                </div>

                <div class="field clearfix">
                  <label for="location">$locationTitle:</label>
                  <span style="line-height: 45px;">$locationValue</span>
                </div>

                <div class="field clearfix">
                  <label for="rooms">$rooms:</label>
				  <div>$roomsValue</div>
                </div>

$servicesHtml

$paymentsHtml

<!--
                <div class="field clearfix">
                  <label for="balance">$balance:</label>
				  <div>$total $currency</div>
                </div>
-->

                <div class="field clearfix">
                  <label for="departureDate">$arriveTime:</label>
                  <input name="arrive_time" value="15:00">
                </div>

                <div class="field clearfix">
                  <label for="comment">$comment:</label>
                  <textarea name="comment"></textarea>
                </div>
                
                <button type="submit">$confirmBooking</button>

              </div>
            </section>

          </fieldset>
        </form>
      </div>

EOT;


html_end($link);
mysql_close($link);


?>

