<?php

require('includes.php');

$location = getLocation();

$publicTransportTitle = PUBLIC_TRANSPORT;
$publicTransportValue = constant('PUBLIC_TRANSPORT_TO_' . strtoupper($location));

$railwayStationsTitle = RAILWAY_STATIONS;
$railwayStationsValue = constant('RAILWAY_STATIONS_TO_' . strtoupper($location));

$airportTitle = AIRPORT;
$airportValue = constant('AIRPORT_TO_' . strtoupper($location));

$internationalBusStationTitle = INTERNATIONAL_BUS_STATION;
$internationalBusStationValue = constant('INTERNATIONAL_BUS_STATION_TO_' . strtoupper($location));

echo <<<EOT
<h2>$publicTransportTitle</h2>
$publicTransportValue

<h2>$railwayStationsTitle</h2>
$railwayStationsValue

<h2>$airportTitle</h2>
$airportValue

<h2>$internationalBusStationTitle</h2>
$internationalBusStationValue

EOT;
?>
