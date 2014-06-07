<?php


/**
 * Simulation URL: https://simulator.expediaquickconnect.com/connect/ar
 * authentication username/password: anything/ECLPASS
 * Hotel ID: 411
 *
 *
 */

// Enable user error handling
libxml_use_internal_errors(true);

ini_set('display_errors', 'On');

require("../includes.php");
require('../room_booking.php');
require('booker.php');

define('EQC_URL_AR', 'https://ws.expediaquickconnect.com/connect/ar');
define('EQC_URL_PARR', 'https://ws.expediaquickconnect.com/connect/parr');
define('EQC_USERNAME', 'EQC2835952');
define('EQC_PASSWORD', 'MHD1911');
define('EQC_HOTELID', '2835952');

class ExpediaBooker extends Booker {
	
	var $httpClient = null;

	function init() {
		echo "<b>expedia.com synchronization init</b><br>\n";
	}

	var $expRoomIdToRatePlanId = array(
		'200216203' => '201228011A',
		'200216200' => '201228008A',
		'200216204' => '201228012A',
		'200216205' => '201228013A',
		'200216198' => '201228006A',
		'200216199' => '201228007A',
		'200238297' => '201366408A',
		'200238303' => '201366419A'
	);


	var $privateRooms = array(
		'200216203' => array('39', '40'),                             /* Double room shared bathroom */
		'200216200' => array(46,48,49,50,51,52,53,54,55,56,57,58),    /* Double room private bathroom with NEW rooms */
		'200216204' => array('59'),                                   /* 3 beds private bathroom */
		'200238297' => array('61'),                                   /* 5 beds private bathroom */
		'200216205' => array('60')                                    /* 4 beds private bathroom */
	);

	var $dormRooms = array(
		'200216198' => '42',                         /* Dormitory - 10 Bed */
		'200238303' => '35',                         /* The Blue Brothers - 6 Bed */
		'200216199' => '36'                          /* Mss Peach - 5 Bed */
	);

/*
	<ProductAvailRateRetrievalRS xmlns="http://www.expediaconnect.com/EQC/PAR/2011/06">
		<ProductList>
			<Hotel id="2835952" name="Maverick Hostel" city="Budapest"/>
			<RoomType id="200216198" code="Mixed Dorm" name="Mixed Dorm (up to 10 people)" status="Active">
				<RatePlan id="201228006A" code="RoomOnly" name="Breakfast excl." status="Active" type="Standalone" distributionModel="Agency" rateAcquisitionType="SellRate"/>
			</RoomType>
			<RoomType id="200216199" code="Mixed Dorm" name="Mixed Dorm (up to 5 people)" status="Active">
				<RatePlan id="201228007A" code="RoomOnly" name="Breakfast excl." status="Active" type="Standalone" distributionModel="Agency" rateAcquisitionType="SellRate"/>
			</RoomType>
			<RoomType id="200216200" code="Standard One Double Bed" name="Standard, One Double Bed" status="Active">
				<RatePlan id="201228008A" code="RoomOnly" name="Breakfast Excluded" status="Active" type="Standalone" distributionModel="Agency" rateAcquisitionType="SellRate"/>
			</RoomType>
			<RoomType id="200216203" code="Economy Guest Room" name="Economy (with Shared Bathroom)" status="Active">
				<RatePlan id="201228011A" code="RoomOnly" name="Breakfast Excluded" status="Active" type="Standalone" distributionModel="Agency" rateAcquisitionType="SellRate"/>
			</RoomType>
			<RoomType id="200216204" code="Triple Occupancy" name="Triple Occupancy" status="Active">
				<RatePlan id="201228012A" code="RoomOnly" name="Breakfast Excluded" status="Active" type="Standalone" distributionModel="Agency" rateAcquisitionType="SellRate"/>
			</RoomType>
			<RoomType id="200216205" code="Quadruple Occupancy" name="Quadruple Occupancy" status="Active">
				<RatePlan id="201228013A" code="RoomOnly" name="Breakfast Excluded" status="Active" type="Standalone" distributionModel="Agency" rateAcquisitionType="SellRate"/>
			</RoomType>
			<RoomType id="200238297" code="Five Beds with Private bathroom" name="Five Beds (with Private Bathroom)" status="Active">
				<RatePlan id="" code="RoomOnly" name="Breakfast Excluded" status="Active" type="Standalone" distributionModel="Agency" rateAcquisitionType="SellRate"/>
			</RoomType>
			<RoomType id="200238303" code="Mixed Dorm up to 6 people" name="Mixed Dorm (up to 6 people)" status="Active">
				<RatePlan id="201366419A" code="RoomOnly" name="Breakfast Excluded" status="Active" type="Standalone" distributionModel="Agency" rateAcquisitionType="SellRate"/>
			</RoomType>
		</ProductList>
	</ProductAvailRateRetrievalRS>
 */


	/////////////////////////////////////////////////////////////
	// 
	/////////////////////////////////////////////////////////////
	function update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, &$rooms) {
		echo "<b>expedia.com synchronization update</b><br>\n";

		$endTS = strtotime("$endYear-$endMonth-$endDay");
		$currDate = "$startYear-$startMonth-$startDay";
		echo "Date: $currDate<br>\n";
		do {
			$currTS = strtotime($currDate);
			$currYear = date('Y', $currTS);
			$currMonth = date('m', $currTS);
			$currDay = date('d', $currTS);
			$username = EQC_USERNAME;
			$pwd = EQC_PASSWORD;
			$hotelId = EQC_HOTELID;
			$xmlRequest = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<AvailRateUpdateRQ xmlns="http://www.expediaconnect.com/EQC/AR/2011/06">
	<Authentication username="$username" password="$pwd"/>
	<Hotel id="$hotelId"/>
	<AvailRateUpdate>
		<DateRange from="$currDate" to="$currDate"/>

EOT;
			foreach($this->privateRooms as $expRoomId => $localRoomIds) {
				$numOfAvailRooms = 0;
				$occupancy = $rooms[$localRoomIds[0]]['num_of_beds'];
				foreach($localRoomIds as $roomId) {
					if(!isset($rooms[$roomId])) {
						echo "<b>ERROR:</b> room id: $roomId is not found in the loaded rooms!<br>\n";
						continue;
					}
					$numOfAvailBeds = getNumOfAvailBeds($rooms[$roomId], $currDate);
					if($numOfAvailBeds == $rooms[$roomId]['num_of_beds']) {
						$numOfAvailRooms += 1;
					}
				}

				$price = getRoomPrice($currYear, $currMonth, $currDay, $rooms[$localRoomIds[0]]);
				echo "[$expRoomId] avail rooms: $numOfAvailRooms, price: $price \n";
				$ratePlanId = $this->expRoomIdToRatePlanId[$expRoomId];
				$xmlRequest .= $this->_getRoomTypeTag($numOfAvailRooms, $expRoomId, $ratePlanId, $price);
			}
			echo "<br>\n";

			foreach($this->dormRooms as $expRoomId => $localRoomId) {
				$occupancy = 1;
				$numOfAvailBeds = getNumOfAvailBeds($rooms[$localRoomId], $currDate);
				if(!isset($rooms[$localRoomId])) {
					echo "<b>ERROR:</b> room id: $roomId is not found in the loaded rooms!<br>\n";
					continue;
				}

				$price = getBedPrice($currYear, $currMonth, $currDay, $rooms[$localRoomId]);
				echo "[$expRoomId], avail beds: $numOfAvailRooms, price: $price \n";
				$ratePlanId = $this->expRoomIdToRatePlanId[$expRoomId];
				$xmlRequest .= $this->_getRoomTypeTag($numOfAvailBeds, $expRoomId, $ratePlanId, $price);
			}
			echo "<br>\n";

			$xmlRequest .= <<<EOT
	</AvailRateUpdate>
</AvailRateUpdateRQ>

EOT;
			$this->_sendInXmlRequest($xmlRequest, EQC_URL_AR, 'AvailRateUpdateRQ.xsd', $currDate);
			$currDate = date('Y-m-d', strtotime("$currDate +1 day"));
		} while($currTS < $endTS);



		echo "done.<br>\n";
		echo "<b>expedia.com synchronization update finished</b><br><br><br>\n";
	}

	function _getRoomTypeTag($numOfAvailRooms, $expRoomId, $ratePlanId, $price) {
		$msg = <<<EOT
		<RoomType id="$expRoomId">
			<Inventory totalInventoryAvailable="$numOfAvailRooms"/>
			<RatePlan id="$ratePlanId" closed="false">
				<Rate currency="EUR">
					<PerDay rate="$price"/>
				</Rate>
			</RatePlan>
		</RoomType>

EOT;
		return $msg;
	}



	function sendInTestXmlRequest() {
		$username = EQC_USERNAME;
		$pwd = EQC_PASSWORD;
		$hotelId = EQC_HOTELID;

		/*
		$xmlRequest = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<ProductAvailRateRetrievalRQ xmlns="http://www.expediaconnect.com/EQC/PAR/2011/06">
	<Authentication username="$username" password="$pwd"/>
	<Hotel id="$hotelId"/>
	<ParamSet>
		<ProductRetrieval/>
	</ParamSet>
</ProductAvailRateRetrievalRQ>

EOT;

		$response = $this->_sendInXmlRequest($xmlRequest, EQC_URL_PARR, 'ProductAvailRateRetrievalRQ.xsd', '2012-11-20');
		echo "\n\nRequest:\n $xmlRequest\n";
		echo "\n\nResponse:\n $response\n";
		 */


		$xmlRequest = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<AvailRateUpdateRQ xmlns="http://www.expediaconnect.com/EQC/AR/2011/06">
	<Authentication username="$username" password="$pwd"/>
	<Hotel id="$hotelId"/>
	<AvailRateUpdate>
		<DateRange from="2012-12-01" to="2012-12-01"/>
		<RoomType id="200216200">
			<Inventory totalInventoryAvailable="11"/>
			<RatePlan id="201228008A" closed="false">
				<Rate currency="EUR">
					<PerDay rate="46"/>
				</Rate>
				<Restrictions closedToArrival="false" closedToDeparture="false" minLOS="1" maxLOS="14"/>
			</RatePlan>
		</RoomType>
	</AvailRateUpdate>
</AvailRateUpdateRQ>

EOT;

		$response = $this->_sendInXmlRequest($xmlRequest, EQC_URL_AR, 'AvailRateUpdateRQ.xsd', 'testing');

		echo "\n\nRequest:\n $xmlRequest\n";
		echo "\n\nResponse:\n $response\n";
	}

	function _getLibxmlDisplayError($error) {
		$return = "<br/>\n";
		switch ($error->level) {
			case LIBXML_ERR_WARNING:
			$return .= "<b>Warning $error->code</b>: ";
			break;
		case LIBXML_ERR_ERROR:
			$return .= "<b>Error $error->code</b>: ";
			break;
		case LIBXML_ERR_FATAL:
			$return .= "<b>Fatal Error $error->code</b>: ";
			break;
		}
		$return .= trim($error->message);
		if ($error->file) {
			$return .=    " in <b>$error->file</b>";
		}
		$return .= " on line <b>$error->line</b>\n";
		return $return;
	}

	function _sendInXmlRequest($xmlRequest, $targetUrl, $xsdFile, $currDate) {
		$this->addResponse('xml_request_' . $currDate, $xmlRequest, '');
		echo "Validating xml message against schema...<br>\n";
		$xml = new DOMDocument();
		$xml->loadXml($xmlRequest);

		if (!$xml->schemaValidate($xsdFile)) {
			echo "<b>Validation error(s)</b><br>\n";
		    $errors = libxml_get_errors();
		    foreach ($errors as $error) {
		        echo $this->_getLibxmlDisplayError($error);
		    }
			libxml_clear_errors();
			echo "<br>Not sending message message to server.<br>\n";
			return;
		} else { 
			echo "Validated<br>\n";
		}  

		echo "Sending message to Expedia QuickConnect service...<br>\n";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_URL, $targetUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: text/xml"));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);
		$response = trim(curl_exec($curl));
		$this->addResponse('xml_response_' . $currDate, $response, '');
		if(curl_errno($curl)) {
			echo "<b>ERROR: </b>" . curl_error($curl) . "<br>\n";
		}
		curl_close($curl);

		$respXml = new DOMDocument();
		$respXml->loadXml($response);
		$nodeList = $respXml->documentElement->getElementsByTagName('Error'); 
		if($nodeList->length > 0) {
			$errorElement = $nodeList->item(0);
			$errorCode = $errorElement->getAttribute('code');
			$errorText = $errorElement->textContent;
			echo "<b>ERROR: </b>[$errorCode] $errorText<br>\n";
		}
		$nodeList = $respXml->documentElement->getElementsByTagName('Warning'); 
		if($nodeList->length > 0) {
			$errorElement = $nodeList->item(0);
			$errorCode = $errorElement->getAttribute('code');
			$errorText = $errorElement->textContent;
			echo "<b>WARNING: </b>[$errorCode] $errorText<br>\n";
		}
		$nodeList = $respXml->documentElement->getElementsByTagName('Success'); 
		if($nodeList->length > 0) {
			echo "OK.<br>";
		}
		return $response;
	}

	function shutdown() {
		echo "<b>expedia.com synchronization shutdown</b><br>";
	}

}

/*
$booker = new ExpediaBooker();
$booker->init();
$booker->sendInTestXmlRequest();
$booker->shutdown();
*/


$startYear = $_REQUEST['start_year'];
$startMonth = $_REQUEST['start_month'];
$startDay = $_REQUEST['start_day'];
$endYear = $_REQUEST['end_year'];
$endMonth = $_REQUEST['end_month'];
$endDay = $_REQUEST['end_day'];

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

echo "Period begining: $startYear - $startMonth - $startDay<br>\n";
echo "Period ending: $endYear - $endMonth - $endDay<br>\n";


$booker = new ExpediaBooker();
$booker->init();
$booker->update($startYear, $startMonth, $startDay, $endYear, $endMonth, $endDay, $rooms);
$booker->shutdown();
$responseFiles = $booker->saveResponsesInfoFile('expedia');
echo "Saved responses: <br>\n";
echo "<ul>\n";
foreach($responseFiles as $key => $filename) {
	echo "	<li><a target=\"_blank\" href=\"$filename\">$key</a></li>\n";
}
echo "</ul>\n";

mysql_close($link);

return;




?>
