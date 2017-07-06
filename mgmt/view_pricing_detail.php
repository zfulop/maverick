<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");
require(ADMIN_BASE_DIR . "common_booking.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();


$roomTypeId = $_REQUEST['room_type_id'];
$currDate = $_REQUEST['date'];

$currDateSlash = str_replace('-', '/', $currDate);

list($currYear, $currMonth, $currDay) = explode('-', $currDate);

$sql = <<<EOT
SELECT pfdh.price_set_date, pfdh.price_unset_date, pfdh.price_per_room, pfdh.price_per_bed, pfdh.occupancy FROM prices_for_date_history pfdh WHERE room_type_id=$roomTypeId AND date='$currDateSlash'
UNION ALL
SELECT pfd.price_set_date, '' as price_unset_date, pfd.price_per_room, pfd.price_per_bed, 0 as occupancy FROM prices_for_date pfd WHERE room_type_id=$roomTypeId AND date='$currDateSlash'
EOT;

$roomTypes = loadRoomTypes($link);

$prices = array();
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	$key = str_replace('/','-',$row['price_set_date']) . '|' . str_replace('/','-',$row['price_unset_date']);
	if(strlen($row['price_unset_date']) < 1) {
		$key = str_replace('/','-',$row['price_set_date']) . '|' . date('Y-m-d H:i:s');
	}
	$prices[$key] = $row;
}

ksort($prices);

$isDorm = ($roomTypes[$roomTypeId]['type'] == 'DORM');
$priceTitle = 'Room price';
if($isDorm) {
	$priceTitle = 'Bed price';
}


echo <<<EOT

<table class="bookings" style="width: 600px;">
	<tr><th>Valid on</th><th title="Occupancy of the room at the end of validity">Occupancy</th><th>$priceTitle</th><th># of bookings</th><th>Booking amount</th><th>Special offer</th></tr>

EOT;
foreach($prices as $priceKey => $priceData) {
	$bookingAmount = 0;
	list($from, $to) = explode('|', $priceKey);
	$sql = "select b.id, b.room_payment, bd.num_of_nights, b.special_offer_id, so.name as so_name from bookings b inner join booking_descriptions bd on b.description_id=bd.id left outer join special_offers so on b.special_offer_id=so.id where b.original_room_type_id=$roomTypeId and bd.first_night<='$currDateSlash' and bd.last_night>='$currDateSlash' and b.creation_time<='$to'";
	if(strlen($from) > 0) {
		$sql .= " and b.creation_time>='$from'";
	}
	$result = mysql_query($sql, $link);
	$bookingCnt = 0;
	$bookingIds = '';
	$specialOfferArr = array();
	$specialOffer = '';
	while($booking = mysql_fetch_assoc($result)) {
		$bookingAmount += intval($booking['room_payment'] / $booking['num_of_nights']);
		$bookingCnt += 1;
		$bookingIds .= $booking['id'] . ',';
		if(!is_null($booking['special_offer_id'])) {
			if(isset($specialOfferArr[$booking['so_name']])) {
				$specialOfferArr[$booking['so_name']] = $specialOfferArr[$booking['so_name']] + 1;
			} else {
				$specialOfferArr[$booking['so_name']] = 1;
			}
		}
	}
	foreach($specialOfferArr as $name => $cnt) {
		$specialOffer .= $cnt . '&nbsp;X&nbsp;' . $name . ', ';
	}
	if(strlen($specialOffer) > 2) {
		$specialOffer = substr($specialOffer, 0, -2);
	}

	$occupancy = '';
	if($priceData['occupancy'] > 0) {
		$occupancy = $priceData['occupancy'] . ' %';
	}
	$prc = $priceData['price_per_room'];
	if($isDorm) {
		$prc = $priceData['price_per_bed'];
	}

	echo "	<tr><td style=\"width: 190px;\">$from&nbsp;-&nbsp;$to</td><td class=\"left_aligned\" style=\"padding-right: 20px;\">$occupancy</td><td class=\"left_aligned\"style=\"padding-right: 20px;\">$prc&nbsp;&#8364;</td><td class=\"left_aligned\" style=\"padding-right: 30px;\">$bookingCnt</td><td class=\"left_aligned\" style=\"padding-right: 30px;\">$bookingAmount&nbsp;&#8364;</td><td>$specialOffer</td><!-- td>$bookingIds</td><td>$sql</td --></tr>\n";
}

mysql_close($link);

echo <<<EOT

</table>

<!--
<div id="timeline" style="width:400px; height: 200px"></div>
<script type="text/javascript">
   var eventSource = new Timeline.DefaultEventSource();
   var bandInfos = [
	 Timeline.createBandInfo({
         eventSource:    eventSource,
         width:          "100%", 
         intervalUnit:   Timeline.DateTime.MONTH, 
         intervalPixels: 100
     }),
   ];
   
   tl = Timeline.create(document.getElementById("timeline"), bandInfos);
   Timeline.loadXML("get_timeline_xml.php?room_type_id=$roomTypeId&start_date=$currDate&end_date=$currDate", function(xml, url) { eventSource.loadXML(xml, url); });
</script>
-->

EOT;


function priceSort($priceKey1, $priceKey2) {
	if(strlen($priceKey1['from']) === 0) {
		return -1;
	}
	if(strlen($priceKey2['from']) === 0) {
		return 1;
	}
	if($priceKey1['from'] < $priceKey2['from']) {
		return -1;
	}
	if($priceKey2['from'] < $priceKey1['from']) {
		return 1;
	}
	return 0;
}



?>
