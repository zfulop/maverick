<?php

require("includes.php");
require("room_booking.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



foreach($_SESSION as $code => $val) {
	if(substr($code, 0, 3) == 'EB_') {
		unset($_SESSION[$code]);
	}
}

$link = db_connect();

if(isset($_REQUEST['new_search'])) {
	$year = intval($_REQUEST['year']);
	$month = intval($_REQUEST['month']);
	$day = intval($_REQUEST['day']);
	$name = $_REQUEST['booker_name'];
	$source = $_REQUEST['source'];
	$confirmedSelected = isset($_REQUEST['confirmed_selected']);
	$confirmed = isset($_REQUEST['confirmed']);
	$cancelledSelected = isset($_REQUEST['cancelled_selected']);
	$cancelled = isset($_REQUEST['cancelled']);
	$checkedinSelected = isset($_REQUEST['checkedin_selected']);
	$checkedin = isset($_REQUEST['checkedin']);
	$paidSelected = isset($_REQUEST['paid_selected']);
	$paid = isset($_REQUEST['paid']);
	$_SESSION['search_bookings_year'] = $year;
	$_SESSION['search_bookings_month'] = $month;
	$_SESSION['search_bookings_day'] = $day;
	$_SESSION['search_bookings_booker_name'] = $name;
	$_SESSION['search_bookings_booker_source'] = $source;
	$_SESSION['search_bookings_confirmed_selected'] = $confirmedSelected;
	$_SESSION['search_bookings_confirmed'] = $confirmed;
	$_SESSION['search_bookings_cancelled_selected'] = $cancelledSelected;
	$_SESSION['search_bookings_cancelled'] = $cancelled;
	$_SESSION['search_bookings_checkedin_selected'] = $checkedinSelected;
	$_SESSION['search_bookings_checkedin'] = $checkedin;
	$_SESSION['search_bookings_paid_selected'] = $paidSelected;
	$_SESSION['search_bookings_paid'] = $paid;
} else {
	$year = $_SESSION['search_bookings_year'];
	$month = $_SESSION['search_bookings_month'];
	$day = $_SESSION['search_bookings_day'];
	$name = $_SESSION['search_bookings_booker_name'];
	$source = $_SESSION['search_bookings_booker_source'];
	$confirmedSelected = $_SESSION['search_bookings_confirmed_selected'];
	$confirmed = $_SESSION['search_bookings_confirmed'];
	$cancelledSelected = $_SESSION['search_bookings_cancelled_selected'];
	$cancelled = $_SESSION['search_bookings_cancelled'];
	$checkedinSelected = $_SESSION['search_bookings_checkedin_selected'];
	$checkedin = $_SESSION['search_bookings_checkedin'];
	$paidSelected = $_SESSION['search_bookings_paid_selected'];
	$paid = $_SESSION['search_bookings_paid'];
}

if(isset($_REQUEST['order'])) {
	$_SESSION['search_bookings_order'] = $_REQUEST['order'];
} elseif(!isset($_SESSION['search_bookings_order'])) {
	$_SESSION['search_bookings_order'] = 'source';
}
$order = $_SESSION['search_bookings_order'];


$sql = "SELECT booking_descriptions.name, booking_descriptions.source, booking_descriptions.first_night, booking_descriptions.num_of_nights, booking_descriptions.last_night, booking_descriptions.confirmed, booking_descriptions.email, booking_descriptions.telephone, booking_descriptions.nationality, booking_descriptions.cancelled, booking_descriptions.checked_in, booking_descriptions.paid, bookings.*, rooms.name AS room_name FROM bookings INNER JOIN booking_descriptions ON bookings.description_id=booking_descriptions.id INNER JOIN rooms ON rooms.id=bookings.room_id WHERE 1=1";
$searchFor = '';
if($year > 0) {
	if(strlen($year) == 2)
		$year = '20' . $year;
	if(strlen($day) < 2)
		$day = '0' . $day;
	if(strlen($month) < 2)
		$month = '0' . $month;

	if($day > 0 and $month > 0) {
		$dt = $year . '/' . $month . '/' . $day;
		$sql .= " AND booking_descriptions.first_night<='$dt' AND booking_descriptions.last_night>='$dt'";
		$searchFor .= "<br>Date: $dt";
	} elseif ($month > 0) {
		$startDt = $year . '/' . $month . '/01';
		$endDt = $year . '/' . $month . '/31';
		$sql .= " AND booking_descriptions.first_night<='$endDt' AND booking_descriptions.last_night>='$startDt'";
		$searchFor .= "<br>Year: $year and month: $month";
	} else {
		$startDt = $year . '/01/01';
		$endDt = $year . '/12/31';
		$sql .= " AND booking_descriptions.first_night<='$endDt' AND booking_descriptions.last_night>='$startDt'";
		$searchFor .= "<br>Year: $year";
	}
} 
if(strlen(trim($source)) > 0) {
	$sql .= " AND booking_descriptions.source LIKE '%" . $source . "%'";
	$searchFor .= "<br>Source contains: $source";
}
if(strlen(trim($name)) > 0) {
	$sql .= " AND booking_descriptions.name LIKE '%" . $name . "%'";
	$searchFor .= "<br>Name contains: $name";
}
if($confirmedSelected) {
	$sql .= " AND booking_descriptions.confirmed=" . ($confirmed ? 1 : 0);
	$searchFor .= "<br>" . ($confirmed ? '' : 'not ') . "confirmed";
}
if($cancelledSelected) {
	$sql .= " AND booking_descriptions.cancelled=" . ($cancelled ? 1 : 0);
	$searchFor .= "<br>" . ($cancelled ? '' : 'not ') . "cancelled";
}
if($checkedinSelected) {
	$sql .= " AND booking_descriptions.checked_in=" . ($checkedin ? 1 : 0);
	$searchFor .= "<br>" . ($checkedin ? '' : 'not ') . "checked in";
}
if($paidSelected) {
	$sql .= " AND booking_descriptions.paid=" . ($paid ? 1 : 0);
	$searchFor .= "<br>" . ($paid ? '' : 'not ') . "paid";
}



$sql .= " ORDER BY $order";

$result = mysql_query($sql, $link);
$cnt = 0;
$bookings = array();
$bookingDescrIds = array();
$guestDataByBookingDescrId = array();
if(!$result) {
	trigger_error("Cannot get bookings in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	$cnt = mysql_num_rows($result);
	$rowsCell = null;
	$bookingCounter = 1;
	while($row = mysql_fetch_assoc($result)) {
		$row['rows'] = 1;
		if(!in_array($row['description_id'], $bookingDescrIds)) {
			$bookingDescrIds[] = $row['description_id'];
			$guestDataByBookingDescrId[$row['description_id']] = array();
			if(!is_null($rowsCell)) {
				$rowsCell = $bookingCounter;
			}
			$rowsCell = &$row['rows'];
			$bookingCounter = 0;
		}
		$bookings[] = $row;
		$bookingCounter += 1;
	}
}

if(count($bookingDescrIds) > 0) {
	$sql = "SELECT * FROM booking_guest_data WHERE booking_description_id IN (" . implode(',', $bookingDescrIds) . ") ORDER BY name";
	$result = mysql_query($sql, $link);
	while($row = mysql_fetch_assoc($result)) {
		$guestDataByBookingDescrId[$row['booking_description_id']][] = $row;
	}
}

html_start("Maverick Reception - Search Bookings - Result");


echo <<<EOT
<br><br>

<b>Search criteria:</b> 
$searchFor
<br>
<br>

There are $cnt bookings<br>
<table border="1">

EOT;


if($order == 'source') {
	$sourceTitle = '<a href="search_bookings.php?order=source" style="font-size: 130%;">Source</a>';
} else {
	$sourceTitle = '<a href="search_bookings.php?order=source">Source</a>';
}
if($order == 'first_night') {
	$dateTitle = '<a href="search_bookings.php?order=first_night" style="font-size: 130%;">Date</a>';
} else {
	$dateTitle = '<a href="search_bookings.php?order=first_night">Date</a>';
}
if($order == 'num_of_nights') {
	$nightsTitle = '<a href="search_bookings.php?order=num_of_nights" style="font-size: 130%;">Nights</a>';
} else {
	$nightsTitle = '<a href="search_bookings.php?order=num_of_nights">Nights</a>';
}
if($order == 'name') {
	$nameTitle = '<a href="search_bookings.php?order=name" style="font-size: 130%;">Name</a>';
} else {
	$nameTitle = '<a href="search_bookings.php?order=name">Name</a>';
}
if($order == 'nationality') {
	$nationalityTitle = '<a href="search_bookings.php?order=nationality" style="font-size: 130%;">Nationality</a>';
} else {
	$nationalityTitle = '<a href="search_bookings.php?order=nationality">Nationality</a>';
}
if($order == 'email') {
	$emailTitle = '<a href="search_bookings.php?order=email" style="font-size: 130%;">Email</a>';
} else {
	$emailTitle = '<a href="search_bookings.php?order=email">Email</a>';
}
if($order == 'telephone') {
	$telTitle = '<a href="search_bookings.php?order=telephone" style="font-size: 130%;">Telephone</a>';
} else {
	$telTitle = '<a href="search_bookings.php?order=telephone">Telephone</a>';
}
if($order == 'room_name') {
	$roomNameTitle = '<a href="search_bookings.php?order=room_name" style="font-size: 130%;">Room Name</a>';
} else {
	$roomNameTitle = '<a href="search_bookings.php?order=room_name">Room Name</a>';
}



if($cnt > 0)
	echo "	<tr><th>$sourceTitle</th><th>$dateTitle</th><th>$nightsTitle</th><th>$nameTitle</th><th>Guest data</th><th>$nationalityTitle</th><th>$emailTitle</th><th>$telTitle</th><th>$roomNameTitle</th><th># of guests</th><th>Room payment</th><th>Status</th><th>Actions</th><th></th></tr>\n";
else
	echo "	<tr><td><i>No record found.</i></td></tr>\n";

$prevDescrId = null;
$bgColor = "rgb(240, 240, 240)";
foreach($bookings as $toShow) {
	$newBooking = false;
	if($toShow['description_id'] != $prevDescrId) {
		$newBooking = true;
		$bgColor = ($bgColor == "rgb(240, 240, 240)" ? "rgb(220, 220, 220)" : "rgb(240, 240, 240)");
		$prevDescrId = $toShow['description_id'];
	}

	$style = "background: $bgColor;";
	if($toShow['confirmed']) {
		$style .= "font-weight: bold;";
	}
	$guestData = '';
	foreach($guestDataByBookingDescrId[$toShow['description_id']] as $oneGD) {
		$guestData .= $oneGD['name'] . " " . "<a href=\"mailto:" . $oneGD['email'] . "\">" . $oneGD['email'] . "</a><br>";
	}
	echo "	<tr style=\"$style\">";
	if($newBooking) {
		$rows = $toShow['rows'];
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['source'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['first_night'] . " - " . $toShow['last_night'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['num_of_nights'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['name'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\" style=\"font-size: 70%;\">$guestData</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['nationality'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['email'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\">" . $toShow['telephone'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['room_name'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['num_of_person'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['room_payment'] . "</td>\n";
		echo "		<td rowspan=\"$rows\" valign=\"middle\"><ul>\n";
		if($toShow['confirmed'])
			echo "			<li>confirmed</li>\n";
		if($toShow['cancelled'])
			echo "			<li>cancelled</li>\n";
		if($toShow['checked_in'])
			echo "			<li>checked in</li>\n";
		if($toShow['paid'])
			echo "			<li>paid</li>\n";
		echo "		</ul></td>\n";
		$descrId = $toShow['description_id'];
		echo "		<td rowspan=\"$rows\" valign=\"middle\"><ul>\n";
		echo "			<li><a href=\"edit_booking.php?description_id=$descrId\">Edit...</a></li>\n";
		if(!$toShow['cancelled']) {
			echo "			<li><a href=\"cancel_booking.php?description_id=$descrId\">Cancel</a></li>\n";
			if($toShow['confirmed']) {
				echo "			<li><a href=\"unconfirm_booking.php?description_id=$descrId\">Unconfirm</a></li>\n";
			} else {
				echo "			<li><a href=\"confirm_booking.php?description_id=$descrId\">Confirm</a></li>\n";
			}
			if($toShow['checked_in']) {
				echo "			<li><a href=\"checkout_booking.php?description_id=$descrId\">Checkout</a></li>\n";
			} else {
				echo "			<li><a href=\"checkin_booking.php?description_id=$descrId\">Checkin</a></li>\n";
			}
			if($toShow['paid']) {
				echo "			<li><a href=\"not_paid_booking.php?description_id=$descrId\">Not paid</a></li>\n";
			} else {
				echo "			<li><a href=\"paid_booking.php?description_id=$descrId\">Paid</a></li>\n";
			}
		}
		echo "		</ul></td>\n";
	} else {
		echo "		<td valign=\"middle\">" . $toShow['room_name'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['num_of_person'] . "</td>\n";
		echo "		<td valign=\"middle\">" . $toShow['room_payment'] . "</td>\n";
	}
	echo "	</tr>\n";
}


echo <<<EOT
</table>

EOT;


mysql_close($link);

html_end();



?>
