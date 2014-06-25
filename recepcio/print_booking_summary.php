<?php

require("includes.php");
require("includes/fpdf.php");

$link = db_connect();

$descrId = intval($_REQUEST['description_id']);


require(LANG_DIR . 'eng.php');

$locationName = constant('LOCATION_NAME_' . strtoupper(LOCATION));

$bookingDescription = null;
$sql = "SELECT * FROM booking_descriptions WHERE id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error("Cannot get booking (with description_id: $descrId).");
	trigger_error("Cannot get booking (with description_id: $descrId) in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
$bookingDescription = mysql_fetch_assoc($result);


$bookings = array();
$type = '';
$sql = "SELECT b.*, lt.value as room_type_name FROM bookings b INNER JOIN rooms r ON b.room_id=r.id INNER JOIN lang_text lt ON (lt.table_name='room_types' AND lt.column_name='name' AND lt.lang='eng' AND lt.row_id=r.room_type_id) WHERE b.description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error("Cannot get booking (with description_id: $descrId).");
	trigger_error("Cannot get booking (with description_id: $descrId) in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
if(mysql_num_rows($result) < 1) {
	set_error("Cannot find booking with description_id: $descrId");
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}

while($row = mysql_fetch_assoc($result)) {
	$bookings[] = $row;
}

$name = $bookingDescription['name'];
$address = $bookingDescription['address'];
$nationality = $bookingDescription['nationality'];
$email = $bookingDescription['email'];
$tel = $bookingDescription['telephone'];
$comment = $bookingDescription['comment'];

$arrivalTime = $bookingDescription['arrival_time'];

$deposit = '';
$depositCurrency = '';
$sql = "SELECT * FROM payments WHERE booking_description_id=$descrId AND comment='booking deposit'";
$result = mysql_query($sql, $link);
if(mysql_num_rows($result) == 1) {
	$row = mysql_fetch_assoc($result);
	$deposit = $row['amount'];
	$depositCurrency = $row['currency'];
}


$fnight = str_replace('/', '-', $bookingDescription['first_night']);
$lnight = str_replace('/', '-', $bookingDescription['last_night']);
$numOfNights = $bookingDescription['num_of_nights'];
if($numOfNights < 1) {
	$numOfNights = round((strtotime(str_replace('/', '-', $lnight)) - strtotime(str_replace('/', '-', $fnight))) / (60*60*24)) + 1;
}

$payments = array();
$sql = "SELECT * FROM payments WHERE booking_description_id=$descrId AND storno<>1";
$result = mysql_query($sql, $link);
if(!$result) {
	$err = "Cannot get payment(s) of booking (with description_id: $descrId).";
	set_error($err);
	trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
while($row = mysql_fetch_assoc($result)) {
	$payments[] = $row;
}

$serviceCharges = array();
$sql = "SELECT * FROM service_charges WHERE booking_description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	$err = "Cannot get service charge(s) of booking (with description_id: $descrId).";
	set_error($err);
	trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
while($row = mysql_fetch_assoc($result)) {
	$serviceCharges[] = $row;
}

mysql_close($link);


$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Helvetica','B',16);
$pdf->SetX(30);
$pdf->Ln();
$pdf->Ln();
$pdf->Cell(200, 7, "$locationName - Payment Receipt", 0);
$pdf->Image('maverick_' . LOCATION . '.jpg', 145, 5, 40, 20);
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(40, 7, "Name:", 0);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(40, 7, $name, 0);
$pdf->Ln();
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(40, 7, "First Night:", 0);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(40, 7, $fnight, 0);
$pdf->Ln();
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(40, 7, "Last Night:", 0);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(40, 7, $lnight, 0);
$pdf->Ln();
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(40, 7, "Number of Nights:", 0);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(40, 7, $numOfNights, 0);

$pdf->Ln();
$pdf->Ln();

$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(40, 7, "Rooms/Beds:", 0);
$pdf->Ln();
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(70, 7, "Room name");
$pdf->Cell(25, 7, "Type");
$pdf->Cell(40, 7, "# of beds");
$pdf->Cell(50, 7, "Price");
$pdf->Ln();

$pdf->SetFont('Helvetica','',10);
$roomTotal = 0;
foreach($bookings as $booking) {
	$bid = $booking['id'];
	$pdf->Cell(70, 7, $booking['room_type_name']);
	$pdf->Cell(25, 7, $booking['booking_type']);
	$pdf->Cell(40, 7, $booking['num_of_person']);
	$pdf->Cell(50, 7, floatval($booking['room_payment']) . " EUR");
	$pdf->Ln();
	$roomTotal += $booking['room_payment'];
}

$pdf->Cell(95, 7, "");
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(40, 7, "Total room price");
$pdf->Cell(50, 7, $roomTotal . " EUR");

$pdf->Ln();
$pdf->Ln();


$serviceChargeTotal = 0;
if(count($serviceCharges) > 0) {
	/*
	$pdf->SetFont('Helvetica','B',14);
	$pdf->Cell(40, 7, "Service charges", 0);
	$pdf->Ln();
	$pdf->SetFont('Helvetica','B',10);
	$pdf->Cell(50, 7, "Date");
	$pdf->Cell(85, 7, "Comment");
	$pdf->Cell(50, 7, "Price");
	$pdf->Ln();

	$pdf->SetFont('Helvetica','',10);
	 */
	foreach($serviceCharges as $sc) {
		$prc = sprintf('%.2f', $sc['amount']) . ' ' . $sc['currency'];
		if($sc['currency'] != 'EUR') {
			$prc .= ' (' . sprintf('%.2f', convertAmount($sc['amount'], $sc['currency'], 'EUR', $dt)) . ' EUR)';
		}
		/*
		$pdf->Cell(50, 7, $sc['time_of_service']);
		$pdf->Cell(85, 7, $sc['type'] . ' ' . $sc['comment']);
		$pdf->Cell(50, 7, $prc);
		$pdf->Ln();
		*/

		$dt= min(date('Y-m-d'), $lnight);
		$serviceChargeTotal += convertAmount($sc['amount'], $sc['currency'], 'EUR', $dt);
	}
	/*
	$pdf->SetFont('Helvetica','B',10);
	$pdf->Cell(50, 7, '');
	$pdf->Cell(85, 7, 'Total charges');
	$pdf->Cell(50, 7, sprintf("%.2f", $serviceChargeTotal) . ' EUR');
	*/

	$serviceChargeTotal = sprintf("%.2f", $serviceChargeTotal);

	/*
	$pdf->Ln();
	$pdf->Ln();
	*/
}

$paymentTotal = 0;
if(count($payments) > 0) {
	$pdf->SetFont('Helvetica','B',14);
	$pdf->Cell(40, 7, "Payments", 0);
	$pdf->Ln();
	$pdf->SetFont('Helvetica','B',10);
	$pdf->Cell(50, 7, "Date");
	$pdf->Cell(85, 7, "Comment");
	$pdf->Cell(50, 7, "Price");
	$pdf->Ln();

	$pdf->SetFont('Helvetica','',10);
	foreach($payments as $payment) {
		$dt = $payment['time_of_payment'];
		$pdf->Cell(50, 7, $dt);
		$pdf->Cell(85, 7, $payment['comment']);
		$prc = sprintf('%.2f', $payment['amount']) . ' ' . $payment['currency'];
		if($payment['currency'] != 'EUR') {
			$prc .= ' (' . sprintf('%.2f', convertAmount($payment['amount'], $payment['currency'], 'EUR', $dt)) . ' EUR)';
		}
		$pdf->Cell(50, 7, $prc);
		$pdf->Ln();
		$paymentTotal += convertAmount($payment['amount'], $payment['currency'], 'EUR', $dt);
	}

	$pdf->SetFont('Helvetica','B',10);
	$pdf->Cell(50, 7, '');
	$pdf->Cell(85, 7, 'Total payment');
	$pdf->Cell(50, 7, sprintf("%.2f", $paymentTotal) . ' EUR');
	$paymentTotal = sprintf("%.2f", $paymentTotal);
}

$balance = $roomTotal + $serviceChargeTotal - $paymentTotal;
$balanceHuf = convertAmount($balance, 'EUR', 'HUF', date('Y-m-d'));
$balance = intval($balance);
$balanceHuf = floor($balanceHuf/100) * 100;

$pdf->Ln();
$pdf->Ln();


$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(30, 7, "Balance:");
$pdf->Cell(60, 7, "$balance EUR ($balanceHuf Ft)", 0);
$pdf->Ln();
$pdf->Cell(30, 7, "Date: ");
$pdf->Cell(60, 7, date('Y-m-d'), 0);


if(isset($_REQUEST['action']) and ($_REQUEST['action'] == 'email')) {
	$pdf->Output('payment_receipt_' . $descrId . '.pdf', 'F');
	$result = sendMail(CONTACT_EMAIL, $locationName, $email, $name, $locationName . ' - Payment Receipt', "Dear $name,\n\nThank you for visiting $locationName. Please find the payment receipt attached.", array(), array('payment_receipt_' . $descrId . '.pdf'));
	set_message("email was sent to $email. View receipt: <a href=\"payment_receipt_$descrId" . ".pdf\">PDF</a>");
	header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
	header('Content-type: applcation/pdf');
$pdf->Output('payment_receipt_' . $descrId . '.pdf', 'I');
}



?>
