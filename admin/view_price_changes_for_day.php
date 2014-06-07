<?php

require("includes.php");
require("../recepcio/room_booking.php");
require("common_booking.php");

$link = db_connect();


$priceChangeDate = $_REQUEST['price_change_date'];
$priceChangeDateSlash = str_replace('-','/', $priceChangeDate);
html_start("Maverick Admin - Price changes for $priceChangeDate");

$sql =<<<EOT
SELECT pfd.price_per_room, pfd.price_per_bed, pfd.date, rt.name, rt.type FROM prices_for_date pfd INNER JOIN room_types rt ON pfd.room_type_id=rt.id WHERE pfd.price_set_date='$priceChangeDateSlash'
UNION ALL
SELECT pfdh.price_per_room, pfdh.price_per_bed, pfdh.date, rt.name, rt.type FROM prices_for_date_history pfdh INNER JOIN room_types rt ON pfdh.room_type_id=rt.id WHERE pfdh.price_set_date='$priceChangeDateSlash'
EOT;

$result = mysql_query($sql, $link);
$priceChanges = array();
while($row = mysql_fetch_assoc($result)) {
	$priceChanges[] = $row;
}
usort($priceChanges, 'sortByDate');

echo "<table>\n";
echo "	<tr><th>Date</th><th>Room name</th><th>Room type</th><th>Price</th></tr>\n";
foreach($priceChanges as $onePc) {
	echo "	<tr><td>" . $onePc['date'] . "</td><td>" . $onePc['name'] . "</td><td>"  . $onePc['type'] . "</td><td>" . ($onePc['type'] == 'DORM' ? $onePc['price_per_bed'] : $onePc['price_per_room']) . "</td></tr>\n";
}
echo "</table>\n";

mysql_close($link);

function sortByDate($pc1, $pc2) {
	if($pc1['date'] < $pc2['date']) {
		return -1;
	} elseif($pc2['date'] < $pc1['date']) {
		return 1;
	} else {
		return 0;
	}
}
?>
