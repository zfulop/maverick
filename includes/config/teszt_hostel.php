<?php

define('EXCHANGE_TABLE_FILE', '/home/zolika/dev/includes/config/exchange_table_test_hostel.php');
define('COUNTRIES_FILE', '/home/zolika/dev/includes/countries.txt');
define('LOCATION', 'test_hostel');

define('CONTACT_EMAIL', 'zolika@zolilla.com');



define('MYALLOCATOR_CUSTOMER_ID','');
define('MYALLOCATOR_CUSTOMER_PASSWORD','');
define('MYALLOCATOR_VENDOR_ID','');
define('MYALLOCATOR_VENDOR_PASSWORD','');

// Use the hostel config here because the teszt_hostel rooms was copied over from there
$myallocatorRoomMap = array(
	'-1000' => array(
		array(
			'roomName' => 'The_Blue_Brothers_6_Bed',
			'roomTypeId' => 35,
			'remoteRoomId' => '9131'
			),
		array(
			'roomName' => 'Double_room_shared_bathroom',
			'roomTypeId' => 39,
			'remoteRoomId' => '9133'
			),
		array(
			'roomName' => 'Mr Green',
			'roomTypeId' => 42,
			'remoteRoomId' => '9132'
		),
		array(
			'roomName' => 'Double_room_private_bathroom_ensuites_with_NEW_rooms',
			'roomTypeId' => 46,
			'remoteRoomId' => '9134'
			),
		array(
			'roomName' => 'Studio Apartment',
			'roomTypeId' => array(70,72),
			'remoteRoomId' => '29812'
		),
		array(
			'roomName' => 'Deluxe Studio Apartment',
			'roomTypeId' => 69,
			'remoteRoomId' => '29813'
		)
	)
);




?>
