<?php

require('includes.php');

$ip = $_SERVER["REMOTE_ADDR"];
$co = getCountryCodeForIp($ip);
$coName = getCountryNameForIp($ip);
echo "For ip: $ip the country code is: |$co|, name is: |$coName| client country name: " . getClientCountryName() . "\n";

?>
