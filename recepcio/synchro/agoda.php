<?php


/**
 *
 * We have created your credential for Agoda Connectivity Centre 
 * https://connectivity.agoda.com/  where you can download our specifications.
 * Agoda Username: Maverick@xml.agoda.com
 * Password: agoda630*
 * 
 * 
 * Sandbox
 * The Agoda YCS4 API Sandbox is designed to allow full testing of all live YCS4 API requests and functionality.
 * Please contact the Agoda Connectivity team to get access.
 *
 * Agoda Sandbox URL
 * http://sandbox.xml.ycs4.agoda.com/
 *
 * Agoda Sandbox Endpoint
 * http://sandbox.xml.ycs4.agoda.com/XMLService.aspx
 *
 * Agoda Sandbox YCS Extranet
 * http://sandbox.ycs4.agoda.com/en-us/Login
 *
 * API Key: b857beab-c695-437a-b08a-f5e494010839
 * Test Hotel ID: 71656
 * Hotel ID: 168687
 *
 *
 * <?xml version="1.0" encoding="utf-8" ?>
<GetHotelRoomTypesRequest xmlns="http://xml.ycs.agoda.com">
	<Authentication APIKey="$apiKey" HotelID="$hotelId"/>
	<RequestedLanguage>en</RequestedLanguage>
</GetHotelRoomTypesRequest>

<?xml version="1.0"?>
<GetHotelRoomTypesResponse xmlns="http://xml.ycs.agoda.com">
	<StatusResponse status="200" AffectedRows="8" />
	<HotelRoomTypesList>
		<RoomType RoomTypeID="1366322" language="en"><![CDATA[1 Bed in 10-Bed Dormitory (Mixed)]]></RoomType>
		<RoomType RoomTypeID="1366324" language="en"><![CDATA[1 Bed in 5-Bed Dormitory (Mixed)]]></RoomType>
		<RoomType RoomTypeID="1366323" language="en"><![CDATA[1 Bed in 6-Bed Dormitory (Mixed)]]></RoomType>
		<RoomType RoomTypeID="914387" language="en"><![CDATA[4 Bedroom with private bathroom]]></RoomType>
		<RoomType RoomTypeID="909414" language="en"><![CDATA[5 Bedroom with Private Bathroom]]></RoomType>
		<RoomType RoomTypeID="909412" language="en"><![CDATA[Double Roomwith Private Bathroom]]></RoomType>
		<RoomType RoomTypeID="909415" language="en"><![CDATA[Double room with shared bathroom]]></RoomType>
		<RoomType RoomTypeID="909413" language="en"><![CDATA[Triple Room with Private Bathroom]]></RoomType>
	</HotelRoomTypesList>
</GetHotelRoomTypesResponse>


<?xml version="1.0" encoding="UTF-8"?>
<GetHotelRatePlansRequest xmlns="http://xml.ycs.agoda.com">
	<Authentication APIKey="$apiKey" HotelID="$hotelId"/>
	<RequestedLanguage>en</RequestedLanguage>
</GetHotelRatePlansRequest>

<?xml version="1.0"?>
<GetHotelRatePlansResponse xmlns="http://xml.ycs.agoda.com">
	<StatusResponse status="200" AffectedRows="1" />
	<RatePlanList>
		<RatePlan>
			<ID>1</ID><Name language="en"><![CDATA[Retail]]></Name>
			<RateType>Sell Inclusive</RateType><Currency>EUR</Currency>
			<MinRateAdj>100.00</MinRateAdj><MaxRateAdj>100.00</MaxRateAdj>
			<MinLOS>0</MinLOS><MaxLOS>0</MaxLOS>
		</RatePlan>
	</RatePlanList>
	<OccupancyModel ID="1">Full Rate</OccupancyModel>
</GetHotelRatePlansResponse>

<?xml version="1.0" encoding="utf-8" ?>
<GetHotelInventoryRequest xmlns="http://xml.ycs.agoda.com">
	<Authentication APIKey="$apiKey" HotelID="$hotelId"/>
	<RoomType RoomTypeID="909412" RatePlanID="1"/>
	<DateRange Type="Stay" Start="2013-06-20" End="2013-06-27"/>
	<RequestedLanguage>en</RequestedLanguage>
</GetHotelInventoryRequest>

<?xml version="1.0"?>
<GetHotelInventoryResponse xmlns="http://xml.ycs.agoda.com">
	<StatusResponse status="200" AffectedRows="8" />
	<HotelInventoryList>
		<HotelInventory>
			<RoomType RoomTypeID="909412" RatePlanID="1" /><DateRange Type="Stay" Start="2013-06-20" End="2013-06-20" />
			<InventoryRate Currency="EUR">
				<SingleRate>60.00</SingleRate><DoubleRate>60.00</DoubleRate><FullRate>0.00</FullRate>
				<ExtraPerson>0.00</ExtraPerson><ExtraAdult>0.00</ExtraAdult><ExtraChild>0.00</ExtraChild><ExtraBed>30.00</ExtraBed>
			</InventoryRate>
			<InventoryAllotment>
				<RegularAllotment>6</RegularAllotment><RegularAllotmentUsed>1</RegularAllotmentUsed><GuaranteedAllotment>2</GuaranteedAllotment><GuaranteedAllotmentUsed>0</GuaranteedAllotmentUsed>
				<CutOffDayNormal>0</CutOffDayNormal><CutOffDayGuaranteed>0</CutOffDayGuaranteed>
				<CloseOutRegularAllotment>False</CloseOutRegularAllotment><ClosedToArrival>False</ClosedToArrival><ClosedToDeparture>False</ClosedToDeparture>
				<BreakfastIncluded>False</BreakfastIncluded><PromotionBlackout>False</PromotionBlackout><MinLOS>1</MinLOS><MaxLOS>0</MaxLOS>
			</InventoryAllotment>
		</HotelInventory>
		<HotelInventory>
			<RoomType RoomTypeID="909412" RatePlanID="1" /><DateRange Type="Stay" Start="2013-06-21" End="2013-06-21" />
			<InventoryRate Currency="EUR">
				<SingleRate>64.00</SingleRate><DoubleRate>64.00</DoubleRate><FullRate>0.00</FullRate>
				<ExtraPerson>0.00</ExtraPerson><ExtraAdult>0.00</ExtraAdult><ExtraChild>0.00</ExtraChild><ExtraBed>32.00</ExtraBed>
			</InventoryRate>
			<InventoryAllotment>
				<RegularAllotment>1</RegularAllotment><RegularAllotmentUsed>0</RegularAllotmentUsed>
				<GuaranteedAllotment>2</GuaranteedAllotment><GuaranteedAllotmentUsed>0</GuaranteedAllotmentUsed>
				<CutOffDayNormal>0</CutOffDayNormal><CutOffDayGuaranteed>0</CutOffDayGuaranteed><CloseOutRegularAllotment>False</CloseOutRegularAllotment><ClosedToArrival>False</ClosedToArrival><ClosedToDeparture>False</ClosedToDeparture>
				<BreakfastIncluded>False</BreakfastIncluded><PromotionBlackout>False</PromotionBlackout><MinLOS>1</MinLOS><MaxLOS>0</MaxLOS>
			</InventoryAllotment>
		</HotelInventory>
		<HotelInventory>
			<RoomType RoomTypeID="909412" RatePlanID="1" /><DateRange Type="Stay" Start="2013-06-22" End="2013-06-22" />
			...
		</HotelInventory>
		
	</HotelInventoryList>
</GetHotelInventoryResponse><br>

 *
 */

// Enable user error handling
libxml_use_internal_errors(true);

ini_set('display_errors', 'On');

require("../includes.php");
require('../room_booking.php');
require('booker.php');

define('AGODA_URL', 'https://xml.ycs4.agoda.com/XMLService.aspx');
define('AGODA_API_KEY', 'b857beab-c695-437a-b08a-f5e494010839');
define('AGODA_HOTELID', '168687');

class AgodaBooker extends Booker {
	
	var $httpClient = null;

	function init() {
		echo "<b>ycs.agoda.com synchronization init</b><br>\n";
	}


	var $privateRooms = array(
		'909415' => array('39', '40'),                             /* Double room shared bathroom */
		'909412' => array(46,48,49,50,51,52,53,54,55,56,57,58),    /* Double room private bathroom with NEW rooms */
		'909413' => array('59'),                                   /* 3 beds private bathroom */
		'909414' => array('61'),                                   /* 5 beds private bathroom */
		'914387' => array('60')                                    /* 4 beds private bathroom */
	);

	var $dormRooms = array(
		'1366322' => '42',                                         /* Dormitory - 10 Bed */
		'1366323' => '35',                                         /* The Blue Brothers - 6 Bed */
		'1366324' => '36'                                          /* Mss Peach - 5 Bed */
	);


	/////////////////////////////////////////////////////////////
	// IMPORTANT!!!!!!!!!
	// Here the availability is by the rooms (not by beds)!!!! 
	/////////////////////////////////////////////////////////////
	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "<b>ycs.agoda.com synchronization update</b><br>\n";

		$apiKey = AGODA_API_KEY;
		$hotelId = AGODA_HOTELID;
		$xmlRequest = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<SetHotelInventoryRequest xmlns="http://xml.ycs.agoda.com">
	<Authentication APIKey="$apiKey" HotelID="$hotelId"/>
	<HotelInventoryList>

EOT;


		$endTS = strtotime("$endYear-$endMonth-$endDay");
		$currDate = "$startYear-$startMonth-$startDay";
		do {
			$currTS = strtotime($currDate);
			$currYear = date('Y', $currTS);
			$currMonth = date('m', $currTS);
			$currDay = date('d', $currTS);
			foreach($this->privateRooms as $agodaRoomId => $localRoomIds) {
				$numOfAvailRooms = 0;
				foreach($localRoomIds as $roomId) {
					if(!isset($rooms[$roomId])) {
						echo "<b>ERROR:</b> room id: $roomId is not found in the loaded rooms!<br>\n";
						continue;
					}
					$numOfAvailBeds = getNumOfAvailBeds($rooms[$roomId], $currDate);
					//echo "For roomId: $roomId, for date: $currDate, the avail beds: $numOfAvailBeds<br>\n";
					if($numOfAvailBeds == $rooms[$roomId]['num_of_beds']) {
						$numOfAvailRooms += 1;
					}
				}

				$roomData = $rooms[$localRoomIds[0]];
				$roomPrice = getRoomPrice($currYear, $currMonth, $currDay, $roomData);
				$bedPrice = getBedPrice($currYear, $currMonth, $currDay, $roomData);
				if($roomData['num_of_beds'] == 2) {
					$xmlRequest .= $this->_getHotelInventoryElementForDbl($numOfAvailRooms, $agodaRoomId, $roomPrice, $bedPrice, $currDate);
				} else {
					$xmlRequest .= $this->_getHotelInventoryElement($numOfAvailRooms, $agodaRoomId, $roomPrice, $bedPrice, $currDate);
				}
			}

			foreach($this->dormRooms as $agodaRoomId => $localRoomId) {
				$numOfAvailBeds = getNumOfAvailBeds($rooms[$localRoomId], $currDate);
				$roomData = $rooms[$localRoomId];
				$roomPrice = getRoomPrice($currYear, $currMonth, $currDay, $roomData);
				$bedPrice = getBedPrice($currYear, $currMonth, $currDay, $roomData);
				$xmlRequest .= $this->_getHotelInventoryElementForDorm($numOfAvailBeds, $agodaRoomId, $roomPrice, $bedPrice, $currDate);
			}

			$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
		} while($currTS < $endTS);
		$xmlRequest .= <<<EOT
	</HotelInventoryList>
</SetHotelInventoryRequest>

EOT;

		$this->_sendInXmlRequest($xmlRequest, AGODA_URL, $startYear . '-' . $startMonth . '-' . $startDay . '_' .  $endYear . '-' . $endMonth . '-' . $endDay);

		echo "done.<br>\n";
		echo "<b>ycs.agoda.com synchronization update finished</b><br><br><br>\n";
	}

	function _getHotelInventoryElement($numOfAvailRooms, $roomId, $roomPrice, $bedPrice, $currDate) {
		$msg = <<<EOT
		<HotelInventory>
			<RoomType RoomTypeID="$roomId" RatePlanID="1"/>
			<DateRange Type="Stay" Start="$currDate" End="$currDate"/>
			<InventoryRate Currency="EUR">
				<SingleRate>$roomPrice</SingleRate>
				<DoubleRate>$roomPrice</DoubleRate>
				<FullRate>$roomPrice</FullRate>
				<ExtraBed>$bedPrice</ExtraBed>
				<BreakfastIncluded>False</BreakfastIncluded>
				<PromotionBlackout>False</PromotionBlackout>
			</InventoryRate>
			<InventoryAllotment>
				<RegularAllotment>$numOfAvailRooms</RegularAllotment>
			</InventoryAllotment>
		</HotelInventory>

EOT;
		return $msg;
	}

	function _getHotelInventoryElementForDbl($numOfAvailRooms, $roomId, $roomPrice, $bedPrice, $currDate) {
		$msg = <<<EOT
		<HotelInventory>
			<RoomType RoomTypeID="$roomId" RatePlanID="1"/>
			<DateRange Type="Stay" Start="$currDate" End="$currDate"/>
			<InventoryRate Currency="EUR">
				<SingleRate>$roomPrice</SingleRate>
				<DoubleRate>$roomPrice</DoubleRate>
				<FullRate>0.00</FullRate>
				<ExtraBed>$bedPrice</ExtraBed>
				<BreakfastIncluded>False</BreakfastIncluded>
				<PromotionBlackout>False</PromotionBlackout>
			</InventoryRate>
			<InventoryAllotment>
				<RegularAllotment>$numOfAvailRooms</RegularAllotment>
			</InventoryAllotment>
		</HotelInventory>

EOT;
		return $msg;
	}

	function _getHotelInventoryElementForDorm($numOfAvailBeds, $roomId, $roomPrice, $bedPrice, $currDate) {
		$msg = <<<EOT
		<HotelInventory>
			<RoomType RoomTypeID="$roomId" RatePlanID="1"/>
			<DateRange Type="Stay" Start="$currDate" End="$currDate"/>
			<InventoryRate Currency="EUR">
				<SingleRate>$bedPrice</SingleRate>
				<BreakfastIncluded>False</BreakfastIncluded>
				<PromotionBlackout>False</PromotionBlackout>
			</InventoryRate>
			<InventoryAllotment>
				<RegularAllotment>$numOfAvailBeds</RegularAllotment>
			</InventoryAllotment>
		</HotelInventory>

EOT;
		return $msg;
	}



	function sendInTestXmlRequest() {
		$apiKey = AGODA_API_KEY;
		$hotelId = AGODA_HOTELID;

		$xmlRequest = <<<EOT

EOT;

		$response = $this->_sendInXmlRequest($xmlRequest, AGODA_URL, 'test');
		echo "Request:" . $xmlRequest . "<br>\n";
		echo "Response:" . $response . "<br>\n";
	}


	function _sendInXmlRequest($xmlRequest, $targetUrl, $identifier) {
		echo "Sending message to AgodaAPI service...<br>\n";
		$this->addResponse('xml_request_' . $identifier, '<pre>' . $xmlRequest . '</pre>', '');

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_URL, $targetUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: text/xml"));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($curl, CURLOPT_PORT, 443);
		$response = trim(curl_exec($curl));
		if(curl_errno($curl)) {
			echo "<b>ERROR: </b>" . curl_error($curl) . "<br>\n";
		}
		curl_close($curl);
		$this->addResponse('xml_response_' . $identifier, '<pre>' . $response . '</pre>', '');

		$xml = new DOMDocument();
		$xml->loadXml($response);
		$nodeList = $xml->documentElement->getElementsByTagName('ErrorItem'); 
		if($nodeList->length > 0) {
			$errorElement = $nodeList->item(0);
			$errorCode = $errorElement->getAttribute('ErrorID');
			$errorText = $errorElement->getAttribute('ErrorText');
			echo "<b>ERROR: </b>[$errorCode] $errorText<br>\n";
		}

		$nodeList = $xml->documentElement->getElementsByTagName('StatusResponse'); 
		$statusEl = $nodeList->item(0);
		if($statusEl->getAttribute('Status') == '200') {
			echo "<b>OK.</b><br>\n";
		}


		return $response;
	}

	function shutdown() {
		echo "<b>ycs.agoda.com synchronization shutdown</b><br>";
	}

}

/*
$booker = new AgodaBooker();
$booker->init();
$booker->sendInTestXmlRequest();
$booker->shutdown();
$startYear = '2013';
$startMonth = '05';
$startDay = '05';
$endYear = '2013';
$endMonth = '05';
$endDay = '06';

*/

$startYear = $_REQUEST['start_year'];
$startMonth = $_REQUEST['start_month'];
$startDay = $_REQUEST['start_day'];
$endYear = $_REQUEST['end_year'];
$endMonth = $_REQUEST['end_month'];
$endDay = $_REQUEST['end_day'];
/*
 */


if(strlen($startDay) == 1)
	$startDay = '0' . $startDay;
if(strlen($startMonth) == 1)
	$startMonth = '0' . $startMonth;
if(strlen($endDay) == 1)
	$endDay = '0' . $endDay;
if(strlen($endMonth) == 1)
	$endMonth = '0' . $endMonth;



$link = db_connect();
$rooms = loadRooms($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $link);

echo "Period begining: $startYear-$startMonth-$startDay<br>\n";
echo "Period ending: $endYear-$endMonth-$endDay<br>\n";


$booker = new AgodaBooker();
$booker->init();
$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();
$responseFiles = $booker->saveResponsesInfoFile('agoda');
echo "Saved responses: <br>\n";
echo "<ul>\n";
foreach($responseFiles as $key => $filename) {
	echo "	<li><a target=\"_blank\" href=\"$filename\">$key</a></li>\n";
}
echo "</ul>\n";

mysql_close($link);


return;




?>
