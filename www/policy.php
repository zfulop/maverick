<?php

require('includes.php');

echo "<h2 style=\"width:100%;text-align:center\">";
foreach(getLocations() as $location) {
	$name = constant('LOCATION_NAME_' . strtoupper($location));
	echo "<a href=\"#$location\" style=\"padding:5px 10px;\">$name</a>  ";
}
echo "</h2><br>";

foreach(getLocations() as $location) {
	$name = constant('LOCATION_NAME_' . strtoupper($location));
	echo "<h2><a name=\"$location\">$name</a></h2>\n<ul>\n";
	$idx = 1;
	while(defined('POLICY_' . strtoupper($location) . '_' . $idx)) {
		$policy = constant('POLICY_' . strtoupper($location) . '_' . $idx);
		echo "	<li>$policy</li>\n";
		$idx += 1;
	}
	echo "</ul>\n";
}

?>
