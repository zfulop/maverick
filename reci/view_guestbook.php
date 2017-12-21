<?php

error_reporting(E_ALL);
ini_set('display_errors',1);


require("includes.php");

error_reporting(E_ALL);
ini_set('display_errors',1);


$link = db_connect();

error_reporting(E_ALL);
ini_set('display_errors',1);


//gets the previous bookings

$error = "";
$guestbook_data = array();
$booking_description_amounts = array();
$from_date = "";
$to_date = "";

if ($_POST['submit']=='Previous bookings'){

    if (!empty($_POST['from_date'])) $from_date = $_POST['from_date'];
    else $error .= "Please provide from date<br>";

    if (!empty($_POST['to_date'])) $to_date = $_POST['to_date'];
    else $error .= "Please provide to date<br>";

    if (empty($error)){

        $guestbook_data = BookingDao::getPreviousGuestBookings($from_date,$to_date,$link);

        //kiszedjuk a booking data tombbol a booking description id - check in date tombot
        //ez alapjan lesz lekerve a payment data
        $booking_desc_day_ids = array();
        foreach ($guestbook_data as $cid=>$curr_row){
            $curr_booking_desc_id = $curr_row['booking_description_id'];
            $booking_desc_day_ids[$curr_booking_desc_id]['day'] = $curr_row['check_in'];
        }

        //lekerjuk a payment adatokat a booking description id-k alapjan
        $payment_filters['booking_description_ids'] = $booking_desc_day_ids;
        $payment_filters['storno'] = 0;
        $payment_filters['types'] = array('IFA / City Tax','Szobabevétel');
        $payment_filters['pay_modes'] = array('CASH2','CASH3','BANK_TRANSFER','CREDIT_CARD');
        $payments = PaymentDao::getPayments($payment_filters,'HUF',$link);

        //osszefuzzuk a payment adatokat
        foreach($guestbook_data as $cid=>$curr_row){
            $curr_booking_desc_id = $curr_row['booking_description_id'];
            $guestbook_data[$cid]['payments'] = $payments[$curr_booking_desc_id];
        }


        if (!empty($guestbook_data)){

            //ha egy booking description id hoz tobb sor tartozik, akkor a room paymentet atlagolni kell
            //ehhez csoportositjuk a booking_description_id alapjan, es ossze szamoljuk, hogy hany person volt ott.

            $booking_description_amounts = array();
            foreach($guestbook_data as $bid=>$curr_data){
                $curr_bd_id = $curr_data['booking_description_id'];
                if (empty($booking_description_amounts[$curr_bd_id])){
                    $booking_description_amounts[$curr_bd_id]['payments']  = $curr_data['payments'];    //ez mar Ft
                    $booking_description_amounts[$curr_bd_id]['person_num'] =1;
                } else {
                    $booking_description_amounts[$curr_bd_id]['person_num']++;
                }
            }

            //kiszamoljuk az atlagokat

            foreach($booking_description_amounts as $bid=>$curr_data){
                $curr_avg = round($curr_data['payments']/$curr_data['person_num'],2);
                $booking_description_amounts[$bid]['avg_huf'] = $curr_avg;
            }
        }

        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        $file_name = "Guestbook_".$from_date.'_'.$to_date.".csv";
        header("Content-Disposition: attachment;filename=$file_name");
        header("Content-Transfer-Encoding: binary");

        // CSV export
        $csv_header = array(
            'Number','First name','Last name','Nationality','Passport number',
            'Check in','Check out','Room','Invoice number','Gross booking amout HUF','IFA HUF','Valid date interval'
        );

        $temp_csv = fopen("php://output", 'w');

        fputcsv($temp_csv, $csv_header);

        foreach($guestbook_data as $bid=>$curr_data){

            $name_parts = explode(' ',trim($curr_data['name']));
            $first_name = $name_parts[0];
            array_shift($name_parts);   //leszedjuk az elso reszet
            $last_names = implode(' ',$name_parts);

            $curr_booking_description_id = $curr_data['booking_description_id'];
            $curr_IFA = round($booking_description_amounts[$curr_booking_description_id]['avg_huf']/1.18*0.04);
            $curr_amount = $booking_description_amounts[$curr_booking_description_id]['avg_huf'] - $curr_IFA;

            $curr_csv_row = array();

            $curr_csv_row[] = $curr_data['booking_guest_id'];
            $curr_csv_row[] = $first_name;
            $curr_csv_row[] = $last_names;
            $curr_csv_row[] = $curr_data['nationality'];
            $curr_csv_row[] = $curr_data['id_card_number'];
            $curr_csv_row[] = $curr_data['check_in'];
            $curr_csv_row[] = $curr_data['check_out'];
            $curr_csv_row[] = $curr_data['room_name'];
            $curr_csv_row[] = $curr_data['invoice_number'];
            $curr_csv_row[] = $curr_amount;
            $curr_csv_row[] = $curr_IFA;
            $curr_csv_row[] = $curr_data['in_interval'];

            fputcsv($temp_csv, $curr_csv_row);

        }

        fclose($temp_csv);

        ob_get_clean();

        exit;
    }
}


html_start("Guestbook");



if (!empty($error)){
    echo "<div class='danger'>$error</div>";
}

echo <<<EOT
<h2>Previous bookings</h2>

<form action="view_guestbook.php" method="post" style="border: 1px solid black; margin: 10px; padding: 5px;">
<table>
	<tr><th colspan="2">Select date interval</th></tr>
	<tr>
		<td>From</td>
		<td><input type="date" name="from_date" value="$from_date"></td>
	</tr>
	<tr>
		<td>To</td>
		<td><input type="date" name="to_date" value="$to_date"></td>
	</tr>
	<tr><td colspan="2"><input type="submit" name="submit" value="Previous bookings"></td></tr>
</table>
</form>

EOT;

if (!empty($guestbook_data)){

//    print_r($booking_data);

echo "<h2>Result</h2>";

echo "<table border='1' style='width:100%'>
	<tr>
	<th >Number</th>
	<th >Booking description id</th>
	<th >First name</th>
	<th >Last name</th>
	<th >Nationality</th>
	<th >Passport number</th>
	<th >Check in</th>
	<th >Check out</th>
	<th >Room</th>
	<th >Invoice number</th>
	<th >Gross booking amout<br>HUF</th>
	<th >IFA<BR>HUF</th>
	<th >Valid date interval</th>
	</tr>";

    foreach($guestbook_data as $bid=>$curr_data){

        $name_parts = explode(' ',trim($curr_data['name']));
        $first_name = $name_parts[0];
        array_shift($name_parts);   //leszedjuk az elso reszet
        $last_names = implode(' ',$name_parts);

        $curr_booking_description_id = $curr_data['booking_description_id'];
        $curr_IFA = round($booking_description_amounts[$curr_booking_description_id]['avg_huf']/1.18*0.04);
        $curr_amount = $booking_description_amounts[$curr_booking_description_id]['avg_huf'] - $curr_IFA;

        echo "<tr>
            <td >".$curr_data['booking_guest_id']."</td>
            <td >".$curr_data['booking_description_id']."</td>
            <td >".$first_name."</td>
            <td >".$last_names."</td>
            <td >".$curr_data['nationality']."</td>
            <td >".$curr_data['id_card_number']."</td>
            <td >".$curr_data['check_in']."</td>
            <td >".$curr_data['check_out']."</td>
            <td >".$curr_data['room_name']."</td>
            <td >".$curr_data['invoice_number']."</td>
            <td >".$curr_amount."</td>
            <td >".$curr_IFA."</td>            
            <td >".$curr_data['in_interval']."</td>            
            </tr>";
    }

    echo "</table>";
}
