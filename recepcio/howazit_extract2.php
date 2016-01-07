<?php

require("includes.php");

$location = LOCATION;

$locationName = constant('LOCATION_NAME_' . strtoupper($location));

$link = db_connect();
$today=date('Y/m/d');
$file = getcwd() . "/maverick_checkedin_" . $location . "." . date('Ymd') . ".csv";
$sql = "SELECT name,email,first_night,last_night FROM booking_descriptions WHERE checked_in=1 AND last_night>='$today'";

echo "Getting guests who are leaving $today, last night is $yesterday\n";

$result = mysql_query($sql, $link);
if(!$result) {
	echo "ERROR: " . mysql_error($link) . " (SQL: $sql)\n";
	mysql_close($link);
	return;
}


$count = 0;
$data = "name,email,checkin_date,checkout_date\n";
while($row = mysql_fetch_assoc($result)) {
	$checkinDate = $row['first_night'];
	$checkoutDate = str_replace('-','/',date('Y-m-d', strtotime(str_replace('/', '-', $row['last_night']) . ' +1 day')));
	$line = str_replace(',', '', $row['name']) . ',' . $row['email'] . ',' . $checkinDate . ',' . $checkoutDate;
	$data .= trim($line) . "\n";
	$count += 1;
}
$data = str_replace( "\r" , "" , $data );

echo "There are $count checked in guests\n";

mysql_close($link);

$fh = fopen($file, "w");
fwrite($fh, $data);
fclose($fh);

echo "Sending mail with file: $file\n";
sendMail(CONTACT_EMAIL, $locationName, 'zfulop@zolilla.com', 'Howazit', "howazit checkedin report", "Please find attached the list of guests checked in today", array(), array($file));

?>
