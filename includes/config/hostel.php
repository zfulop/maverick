<?php

define('EXCHANGE_TABLE_FILE', '/home/zolika/includes/config/exchange_table_lodge.php');
define('COUNTRIES_FILE', '/home/zolika/includes/countries.txt');
define('LOCATION', 'hostel');

define('CONTACT_EMAIL', 'reservation@maverickhostel.com');

define('MYALLOCATOR_CUSTOMER_ID','maverick');
define('MYALLOCATOR_CUSTOMER_PASSWORD','Palesz22');
define('MYALLOCATOR_VENDOR_ID','maverickhostel');
define('MYALLOCATOR_VENDOR_PASSWORD','kPnFxw85RS');

$myallocatorRoomMap = array(
	'1650' => array(
		array(
			'roomName' => 'The_Blue_Brothers_6_Bed',
			'roomIds' => array(35),
			'remoteRoomId' => '9131'
			),
		array(
			'roomName' => 'Mss_Peach_5_Bed',
			'roomIds' => array(36),
			'remoteRoomId' => '9130'
			),
		array(
			'roomName' => 'Double_room_shared_bathroom',
			'roomIds' => array(39, 40),
			'remoteRoomId' => '9133'
			),
		array(
			'roomName' => 'Double_room_private_bathroom_ensuites_with_NEW_rooms',
			'roomIds' => array(46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 66, 67, 68, 69, 75),
			'remoteRoomId' => '9134'
			),
		array(
			'roomName' => 'NEW_Maverick_ensuite_3_Bed',
			'roomIds' => array(59, 62, 73),
			'remoteRoomId' => '9135'
			),
		array(
			'roomName' => 'NEW_Maverick_ensuite_4_Bed',
			'roomIds' => array(60, 71, 72),
			'remoteRoomId' => '9136'
			),
		array(
			'roomName' => 'NEW_Maverick_ensuite_5_Bed',
			'roomIds' => array(61, 74),
			'remoteRoomId' => '9137'
		),
		array(
			'roomName' => 'Mr Green',
			'roomIds' => array(42),
			'remoteRoomId' => '9132'
		),
		array(
			'roomName' => 'HW 4 bedded extra private ensuite',
			'roomIds' => array(63),
			'remoteRoomId' => '10032'
		),
		array(
			'roomName' => '5 bed Dorm with private bathroom',
			'roomIds' => array(64),
			'remoteRoomId' => '9431'
		),
		array(
			'roomName' => 'Single room ensuite',
			'roomIds' => array(65),
			'remoteRoomId' => '24369'
		)
	),
	'5637' => array(
		array(
			'roomName' => 'Studio Apartment',
			'roomIds' => array(82),
			'remoteRoomId' => '29812'
		),
		array(
			'roomName' => 'Deluxe Studio Apartment',
			'roomIds' => array(80,81),
			'remoteRoomId' => '29813'
		),
		array(
			'roomName' => 'One-bedroom apartment, Ferenciek',
			'roomIds' => array(78,79),
			'remoteRoomId' => '29814'
		),
		array(
			'roomName' => 'One-bedroom apartment, Belgrád',
			'roomIds' => array(77),
			'remoteRoomId' => '29815'
		),
		array(
			'roomName' => 'Two-bedroom apartment, Deák',
			'roomIds' => array(76),
			'remoteRoomId' => '29816'
		)	
	)
);


?>
