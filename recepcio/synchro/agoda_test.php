<?php

// Enable user error handling
libxml_use_internal_errors(true);

ini_set('display_errors', 'On');

require("../includes.php");
require('../room_booking.php');
require('booker.php');

define('AGODA_URL', 'https://xml.ycs4.agoda.com/XMLService.aspx');
define('AGODA_API_KEY', 'b857beab-c695-437a-b08a-f5e494010839');
define('AGODA_HOTELID', '168687');

echo time() . " " . date() . "<br>\n";

$apiKey = AGODA_API_KEY;
$hotelId = AGODA_HOTELID;
echo time() . " Trying curl<br>\n";
$xmlRequest = <<<EOT
<?xml version="1.0" encoding="utf-8" ?>
<GetHotelInventoryRequest xmlns="http://xml.ycs.agoda.com">
	<Authentication APIKey="$apiKey" HotelID="$hotelId"/>
	<RoomType RoomTypeID="909415" RatePlanID="1"/>
	<DateRange Type="Stay" Start="2013-06-21" End="2013-06-21"/>
	<RequestedLanguage>en</RequestedLanguage>
</GetHotelInventoryRequest>

EOT;
$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl, CURLOPT_URL, AGODA_URL);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($curl, CURLOPT_TIMEOUT, 60);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: text/xml"));
curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);
curl_setopt($curl, CURLOPT_PORT, 443);
$response = trim(curl_exec($curl));
if(curl_errno($curl)) {
	echo "<b>ERROR: </b>" . curl_error($curl) . "<br>\n";
} else {
	echo "Response: $response<br>\n";
}
curl_close($curl);
echo time() . " end of curl<br>\n";



?>
