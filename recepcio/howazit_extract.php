<?php

require("includes.php");

$location = LOCATION;

$locationName = constant('LOCATION_NAME_' . strtoupper($location));

$link = db_connect();
$today = date('Y/m/d');
$yesterday = date('Y/m/d', strtotime(date('Y-m-d') . ' -1 day'));
$file = getcwd() . "/maverick_" . $location . "." . date('Ymd') . ".csv";
$sql = "SELECT name,telephone,email,nationality,source,first_night as arrival_date, '$today' as departure_date FROM booking_descriptions WHERE last_night='$yesterday' and cancelled<>1";

echo "Getting guests who are leaving $today, last night is $yesterday\n";

$result = mysql_query($sql, $link);
if(!$result) {
	echo "ERROR: " . mysql_error($link) . " (SQL: $sql)\n";
	mysql_close($link);
	return;
}

$fields = mysql_num_fields($result);
for ($i = 0; $i < $fields; $i++) {
	$header .= mysql_field_name($result, $i) . ",";
}

$count = 0;
$data = $header . "\n";
$msg = '';
while($row = mysql_fetch_row($result)) {
	$line = '';
	foreach($row as $value) {
		if((!isset($value)) || ($value == "")) {
			$value = ",";
		} else {
			$value = str_replace( '"' , '""' , $value );
			$value = '"' . $value . '"' . ",";
		}
		$line .= $value;
	}
	$data .= trim($line) . "\n";
	$msg .= sprintf("[%-20s] %s %s\n", $row[0], $row[5], $row[6]);
	$count += 1;
}
$data = str_replace( "\r" , "" , $data );

echo "There are $count such guests\n";
echo $msg;

mysql_close($link);

$fh = fopen($file, "w");
fwrite($fh, $data);
fclose($fh);

echo "Sending mail with file: $file\n";
sendMail(CONTACT_EMAIL, $locationName, HOWAZIT_EMAIL, 'Howazit', "howazit departure report", "Please find attached the list of guests leaving today", array(), array($file));

?>
