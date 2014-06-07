<?php

require("includes.php");
require("includes/fpdf.php");

$link = db_connect();

$descrId = intval($_REQUEST['description_id']);

$rooms = array();
$sql = "SELECT rooms.*, lang_text.value AS room_name FROM rooms INNER JOIN lang_text ON (lang_text.row_id=rooms.room_type_id AND lang_text.table_name='room_types' AND lang_text.column_name='name' AND lang_text.lang='eng')";
$result = mysql_query($sql, $link);
if(!$result) {
	set_error("Cannot get rooms.");
	trigger_error("Cannot get rooms in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}
$roomsHtmlOptions = '';
while($row = mysql_fetch_assoc($result)) {
	$rooms[$row['id']] = $row;
	$roomsHtmlOptions .= '<option value="' . $row['id'] . '">' . $row['room_name'] . '</option>';
}

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
$sql = "SELECT * FROM bookings WHERE description_id=$descrId";
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
	$row['room_name'] = $rooms[$row['room_id']]['room_name'];
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
	$numOfNights = intval((strtotime(str_replace('/', '-', $lnight)) - strtotime(str_replace('/', '-', $fnight))) / (60*60*24)) + 1;
}

$sql = "SELECT * FROM booking_guest_data WHERE booking_description_id=$descrId";
$result = mysql_query($sql, $link);
if(!$result) {
	$err = "Cannot get guest data of booking (with description_id: $descrId).";
	set_error($err);
	trigger_error($err . " SQL Error: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
	mysql_close($link);
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}

$payments = array();
$sql = "SELECT * FROM payments WHERE booking_description_id=$descrId";
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
$pdf->SetX(70);
$pdf->Cell(100, 7, "Booking Summary", 0);
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
$pdf->Cell(30, 7, "Type");
$pdf->Cell(50, 7, "# of beds");
$pdf->Cell(50, 7, "Price");
$pdf->Ln();

$pdf->SetFont('Helvetica','',10);
$roomTotal = 0;
foreach($bookings as $booking) {
	$bid = $booking['id'];
	$pdf->Cell(70, 7, $booking['room_name']);
	$pdf->Cell(30, 7, $booking['booking_type']);
	$pdf->Cell(50, 7, $booking['num_of_person']);
	$pdf->Cell(50, 7, floatval($booking['room_payment']) . " euro");
	$pdf->Ln();
	$roomTotal += $booking['room_payment'];
}

$pdf->Cell(100, 7, "");
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(50, 7, "Total room price");
$pdf->Cell(50, 7, $roomTotal . " euro");

$pdf->Ln();
$pdf->Ln();


$serviceChargeTotal = 0;
if(count($serviceCharges) > 0) {
	$pdf->SetFont('Helvetica','B',14);
	$pdf->Cell(40, 7, "Service charges", 0);
	$pdf->Ln();
	$pdf->SetFont('Helvetica','B',10);
	$pdf->Cell(50, 7, "Name");
	$pdf->Cell(50, 7, "Date");
	$pdf->Cell(50, 7, "Price");
	$pdf->Cell(50, 7, "Comment");
	$pdf->Ln();

	$pdf->SetFont('Helvetica','',10);
	foreach($serviceCharges as $sc) {
		$pdf->Cell(50, 7, $sc['type']);
		$pdf->Cell(50, 7, $sc['time_of_service']);
		$pdf->Cell(50, 7, sprintf('%.2f', $sc['amount']) . ' ' . $sc['currency']);
		$pdf->Cell(50, 7, $sc['comment']);
		$pdf->Ln();

		$dt= min(date('Y-m-d'), $lnight);
		$serviceChargeTotal += convertAmount($sc['amount'], $sc['currency'], 'EUR', $dt);
	}
	$pdf->SetFont('Helvetica','B',10);
	$pdf->Cell(50, 7, '');
	$pdf->Cell(50, 7, 'Service total');
	$pdf->Cell(50, 7, sprintf("%.2f", $serviceChargeTotal) . ' euro');

	$serviceChargeTotal = sprintf("%.2f", $serviceChargeTotal);

	$pdf->Ln();
	$pdf->Ln();
}

$paymentTotal = 0;
if(count($payments) > 0) {
	$pdf->SetFont('Helvetica','B',14);
	$pdf->Cell(40, 7, "Payments", 0);
	$pdf->Ln();
	$pdf->SetFont('Helvetica','B',10);
	$pdf->Cell(50, 7, "Date");
	$pdf->Cell(50, 7, "Comment");
	$pdf->Cell(50, 7, "Amount");
	$pdf->Ln();

	$pdf->SetFont('Helvetica','',10);
	foreach($payments as $payment) {
		$amount = sprintf('%.2f', $payment['amount'])	. ' ' . $payment['currency'];
		$pdf->Cell(50, 7, $payment['time_of_payment']);
		$pdf->Cell(50, 7, $payment['comment']);
		$pdf->Cell(50, 7, $amount);
		$pdf->Ln();
		$paymentTotal += convertAmount($payment['amount'], $payment['currency'], 'EUR', $payment['time_of_payment']);
	}

	$pdf->SetFont('Helvetica','B',10);
	$pdf->Cell(50, 7, '');
	$pdf->Cell(50, 7, 'Total');
	$pdf->Cell(50, 7, sprintf("%.2f", $paymentTotal));
	$paymentTotal = sprintf("%.2f", $paymentTotal);
}

$balance = $roomTotal + $serviceChargeTotal - $paymentTotal;
$balanceHuf = convertAmount($balance, 'EUR', 'HUF', date('Y-m-d'));
$balance = sprintf("%.2f", $balance);
$balanceHuf = floor($balanceHuf/100) * 100;

$pdf->Ln();
$pdf->Ln();


$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(100, 7, "Balance: $balance euro ($balanceHuf Ft)", 0);


header('Content-type: applcation/pdf');
$pdf->Output('booking_summary_' . $descrId . '.pdf', 'I');



?>
