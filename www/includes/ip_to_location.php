<?php

require("geoip.inc");

function getCountryCodeForIp($idAddress) {
	$gi = geoip_open(GEO_IP_DAT_FILE, GEOIP_STANDARD);
	$code = geoip_country_code_by_addr($gi, $idAddress);
	geoip_close($gi);
	return $code;
}

function getCountryNameForIp($idAddress) {
	$gi = geoip_open(GEO_IP_DAT_FILE, GEOIP_STANDARD);
	$code = geoip_country_name_by_addr($gi, $idAddress);
	geoip_close($gi);
	return $code;
}


function isClientFromHU() {
	$ip = $_SERVER["REMOTE_ADDR"];
	$co = getCountryCodeForIp($ip);
	$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	return (($co == 'HU') and ($lang == 'hu'));
}

function getClientCountryName() {
	$ip = $_SERVER["REMOTE_ADDR"];
	$co = getCountryNameForIp($ip);
	return $co;
}

?>
