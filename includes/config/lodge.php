<?php

define('EXCHANGE_TABLE_FILE', '/home/zolika/includes/config/exchange_table_lodge.php');
define('COUNTRIES_FILE', '/home/zolika/includes/countries.txt');
define('LOCATION', 'lodge');

define('CONTACT_EMAIL', 'reservation@mavericklodges.com');

define('MYALLOCATOR_CUSTOMER_ID','maverick');
define('MYALLOCATOR_CUSTOMER_PASSWORD','Palesz22');
define('MYALLOCATOR_VENDOR_ID','maverickhostel');
define('MYALLOCATOR_VENDOR_PASSWORD','kPnFxw85RS');

$myallocatorRoomMap = array(
	'1748' => array(
		array(
			'roomName' => 'Double Private Room',
			'roomIds' => array(89,90,106,107),
			'remoteRoomId' => '10035'
		),
		array(
			'roomName' => 'Double Private Ensuite Room',
			'roomIds' => array(65,78,79,80,81,82,83,84,85,86,87,88,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,108),
			'remoteRoomId' => '10036'
		),
		array(
			'roomName' => 'Triple Private Ensuite Room',
			'roomIds' => array(109,110,111,115),
			'remoteRoomId' => '10037'
		),
		array(
			'roomName' => 'Quadruple Private Ensuite Room',
			'roomIds' => array(112,113,114,116),
			'remoteRoomId' => '10038'
		),
		array(
			'roomName' => '4 bed mixed dorm',
			'roomIds' => array(74,75,76),
			'remoteRoomId' => '10066'
		),
		array(
			'roomName' => '6 bed mixed dorm',
			'roomIds' => array(67,68,69,70,71,72),
			'remoteRoomId' => '10067'
		),
		array(
			'roomName' => '8 bed mixed dorm',
			'roomIds' => array(77),
			'remoteRoomId' => '10068'
		),
		array(
			'roomName' => 'Female 6 bed dorm',
			'roomIds' => array(117,118),
			'remoteRoomId' => '16475'
		)	
	)
);


?>
