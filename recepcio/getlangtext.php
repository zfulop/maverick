<?php


$text = <<<EOT
ehLang'from' = 'From';
ehLang'bpn' = 'Day / Bed';
ehLang'rpn' = 'Day / Room';
ehLang'beds' = 'Bed(s)';
ehLang'rooms' = 'Room(s)';
ehLang'rooms2' = 'Rooms';
ehLang'checkoutourrooms' = 'View All Rooms';
ehLang'roomnotfoundtitle' = 'Maverick Lodges - Room not found';
ehLang'roomnotfound' = 'Room not found';
ehLang'chooseLoc' = 'Choose Location';
ehLang'adate' = 'Arrival date';
ehLang'ddate' = 'Departure  date';
ehLang'adults' = 'Guest(s)';
ehLang'children' = 'Children';
ehLang'checkav' = 'Check availability';
ehLang'price' = 'Price';
ehLang'otherRooms' = 'Other rooms';
ehLang'yourres' = 'Your Resevation';
ehLang'yourbooking' = 'Your Booking';
ehLang'nobookingyet' =' No booking yet...';
ehLang'roomdesc' = 'Room description';
ehLang'roomamenities' = 'Room Amenities';
ehLang'searchresults' = 'Search Results';
ehLang'capacitybeds' = 'Total beds / room';
ehLang'capacityrooms' = 'Total rooms';
ehLang'freebeds' = 'Available beds';
ehLang'freerooms' = 'Available rooms';
ehLang'novacancy' = 'Unavailable. Try different dates, please!';
ehLang'quantity' = 'Quantity';
ehLang'selectroom' = 'Add';
ehLang'selectservice' = 'Add';
ehLang'selectatleastone' = 'Select at least 1 item, please!';
ehLang'finalizebooking' = 'Finalize';
ehLang'feeinfo' = "No booking fees.<br>Credit card not needed.<br>All taxes included.";
ehLang'roomtype' = 'Room type';
ehLang'nights' = 'Night(s)';
ehLang'total' = 'Total';
ehLang'bookingsummary' = 'Booking Summary';
ehLang'grandtotal' = 'Grand Total';
ehLang'customerdets' = 'Customer Details';
ehLang'firstname' = 'First name';
ehLang'lastname' = 'Last name';
ehLang'phone' = 'Phone number';
ehLang'nationality' = 'Nationality';
ehLang'country' = 'Country';
ehLang'street' = 'Street, House number';
ehLang'city' = 'City';
ehLang'zip' = 'ZIP';
ehLang'comment' = 'Comment';
ehLang'submitbooking' = 'Submit booking';
ehLang'viewoccupancy' = 'Room occupancy';
ehLang'closeoccupancy' = 'Hide occupancy';
ehLang'roomdetails' = 'Room details';
ehLang'closeroomdetails' = 'Hide details';
ehLang'services' = 'Services';
ehLang'service' = 'Service';
ehLang'srvdetails' = 'Service details';
ehLang'closesrvdetails' = 'Hide details';
ehLang'reservationSuccess' = 'Reservation successfull! We will send you an e-mail confirmation shortly!';
ehLang'comment' = 'Comment';
ehLang'optextras' = 'Optional extras';
ehLang'FOR_SELECTED_DATE_MIN_STAY_2' = 'Minimum stay for the selected period is 2 days';
ehLang'FOR_SELECTED_DATE_MIN_STAY_3' = 'Minimum stay for the selected period is 3 days';
ehLang'FOR_SELECTED_DATE_MIN_STAY_4' = 'Minimum stay for the selected period is 4 days';
ehLang'FOR_SELECTED_DATE_MAX_STAY_2' = 'Maximum stay for the selected period is 2 days';
ehLang'FOR_SELECTED_DATE_MAX_STAY_3' = 'Maximum stay for the selected period is 2 days';
ehLang'FOR_SELECTED_DATE_MAX_STAY_4' = 'Maximum stay for the selected period is 4 days';
ehLang'BOOKING_DATE_MUST_BE_IN_THE_FUTURE' = 'Booking date must be in the future...';
ehLang'CHECKOUT_DATE_MUST_BE_AFTER_CHECKIN_DATE ' = 'Checkout date must be after checkin date...';
ehLang'DB_ERROR' = 'An error occured. Please try again!';

EOT;

foreach(explode("\n", $text) as $line) {
	$quoteIdx1 = strpos($line, "'", 0);
	$quoteIdx2 = strpos($line, "'", $quoteIdx1+1);
	$quoteIdx3 = strpos($line, "'", $quoteIdx2+1);
	$quoteIdx4 = strpos($line, "'", $quoteIdx3+1);	
	$key = substr($line, $quoteIdx1+1, $quoteIdx2 - $quoteIdx1 -1);
	$value = substr($line, $quoteIdx3+1, $quoteIdx4 - $quoteIdx3-1);
	echo "INSERT INTO lang_text (table_name, column_name, value, lang) VALUES ('website', '$key', '$value', '$lang');\r\n";
}


?>