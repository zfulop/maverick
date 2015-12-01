<?php

require("includes.php");
require(RECEPCIO_BASE_DIR . "room_booking.php");
require("common_booking.php");

if(!checkLogin(SITE_ADMIN)) {
	return;
}

$link = db_connect();


$roomTypeId = $_REQUEST['room_type_id'];
$startDate = $_REQUEST['start_date'];
$endDate = $_REQUEST['end_date'];

$startDateSlash = str_replace('-', '/', $startDate);
$endDateSlash = str_replace('-', '/', $endDate);

list($startYear, $startMonth, $startDay) = explode('-', $startDate);
list($endYear, $endMonth, $endDay) = explode('-', $endDate);

$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

$roomTypes = loadRoomTypes($link);

$sql = <<<EOT
SELECT * FROM prices_for_date_history pfdh WHERE room_type_id=$roomTypeId AND date>='$startDateSlash' AND date<='$endDateSlash'
UNION ALL
SELECT *, '' as price_unset_date FROM prices_for_date pfd WHERE room_type_id=$roomTypeId AND date>='$startDateSlash' AND date<='$endDateSlash'
EOT;

echo "<data>\n";

$prices = array();
$result = mysql_query($sql, $link);
while($row = mysql_fetch_assoc($result)) {
	$start = $row['price_set_date'];
	$end = (strlen($row['price_unset_date']) < 1 ? date('Y-m-d') : $row['price_unset_date']);
	$roomPrice = $priceData['price_per_room'];
	if(is_null($priceData['price_per_room'])) {
		$roomPrice = $priceData['price_per_bed'] * $roomTypes[$roomTypeId]['num_of_bed'];
	}
	echo "\t<event start=\"$start\" end=\"$end\" isDuration=\"true\" title=\"$roomPrice\">Room price: $roomPrice</event>\n";
}

mysql_close($link);

$bookings = getBookings($roomTypeId, $rooms, $startDate, $endDate);
foreach($bookings as $booking) {
	$ct = $booking['creation_time'];
	$numOfPerson = $booking['num_of_person'];
	$numOfNights = $booking['num_of_nights'];
	$roomPayment = $booking['room_payment'];
	echo "\t<event start=\"$ct\" title=\"$roomPayment\">Num of nights: $numOfNights, num of person: $numOfPerson</event>\n";
}
	

echo <<<EOT

</table>
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
   Timeline.loadXML("get_timeline_xml.php?room_type_id=$roomTypeId&start_date=$startDate&end_date=$endDate", function(xml, url) { eventSource.loadXML(xml, url); });
</script>



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
