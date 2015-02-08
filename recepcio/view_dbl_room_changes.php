<?php

require("includes.php");

$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->


EOT;

html_start("Maverick Reception - Multiple room changes", $extraHeader);

echo <<<EOT

<table>
	<tr><th colspan="5">Multiple room changes for a day</th></tr>
	<tr><th>Name</th><th>First night</th><th>Last night</th><th>Original room</th><th>Source</th></tr>
EOT;

$link = db_connect();

$sql = "select distinct bd.id,bd.name,bd.first_night,bd.last_night,bd.source,r.name as room_name from booking_room_changes brc1 inner join booking_room_changes brc2 on brc1.date_of_room_change=brc2.date_of_room_change and brc1.booking_id=brc2.booking_id and brc1.id<>brc2.id inner join bookings b on brc1.booking_id=b.id inner join booking_descriptions bd on bd.id=b.description_id inner join rooms r on b.room_id=r.id where bd.cancelled<>1";

$result = mysql_query($sql, $link);

while($row = mysql_fetch_assoc($result)) {
	$id = $row['id'];
	$name = $row['name'];
	$roomName = $row['room_name'];
	$fnight = $row['first_night'];
	$lnight = $row['last_night'];
	$rname = $row['room_name'];
	$source = $row['source'];
	echo <<<EOT
	<tr>
		<td><a href="edit_booking.php?description_id=$id">$name</a></td>
		<td>$fnight</td>
		<td>$lnight</td>
		<td>$rname</td>
		<td>$source</td>
	</tr>

EOT;
}

echo <<<EOT
</table>


EOT;

mysql_close($link);

html_end();


?>
