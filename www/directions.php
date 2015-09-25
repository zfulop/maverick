<?php

require('includes.php');
require('includes/common_booking.php');

$location = getLocation();

$addressTitle = ADDRESS_TITLE;
$phone = PHONE;
$email = EMAIL;
$fax = FAX;

$addressValue = constant('ADDRESS_VALUE_' . strtoupper($location));
// constants are defined in config.php
$contactPhone = constant('CONTACT_PHONE_' . strtoupper($location));
$contactEmail = constant('CONTACT_EMAIL_' . strtoupper($location));
$contactFax = constant('CONTACT_FAX_' . strtoupper($location));


$publicTransportTitle = PUBLIC_TRANSPORT;
$publicTransportValue = constant('PUBLIC_TRANSPORT_TO_' . strtoupper($location));

$railwayStationsTitle = RAILWAY_STATIONS;
$railwayStationsValue = constant('RAILWAY_STATIONS_TO_' . strtoupper($location));

$airportTitle = AIRPORT;
$airportValue = constant('AIRPORT_TO_' . strtoupper($location));

$internationalBusStationTitle = INTERNATIONAL_BUS_STATION;
$internationalBusStationValue = constant('INTERNATIONAL_BUS_STATION_TO_' . strtoupper($location));

echo <<<EOT
<h2>$addressTitle</h2>
$addressValue<br>
<strong>$phone</strong>
$contactPhone<br>
<strong>$email</strong>
<a href="mailto:$contactEmail">$contactEmail</a><br>
<strong>$fax</strong>
$contactFax

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
