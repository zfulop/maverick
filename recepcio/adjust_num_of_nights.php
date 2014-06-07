<?php

require 'includes.php';

$link = db_connect();

$sql = "SELECT * FROM booking_descriptions";
$result =  mysql_query($sql, $link);

$sql = array();
while($row = mysql_fetch_assoc($result)) {
	$numOfNights = intval((strtotime(str_replace('/', '-', $row['last_night'])) - strtotime(str_replace('/', '-', $row['first_night']))) / (60*60*24)) + 1;
	if($numOfNights != $row['num_of_nights']) {
		echo $row['first_night'] . ' ' . $row['last_night'] . ' ' . $row['num_of_nights'] . ' => ' . $numOfNights . "\n";
		$sql[] = "UPDATE booking_descriptions SET num_of_nights=$numOfNights WHERE id=" . $row['id'];
	}

}

foreach($sql as $s) {
	echo $s . ";\n";
}

mysql_close($link);


?>
