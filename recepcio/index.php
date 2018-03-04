<?php

require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}


if(isset($_REQUEST['day_to_show'])) {
	$_SESSION['day_to_show'] = $_REQUEST['day_to_show'];
} else {
	$_SESSION['day_to_show'] = date('Y-m-d');
}
$dayToShow = $_SESSION['day_to_show'];

// Before 5am show as if it was yesterday
if(date('G') < 5 and ($dayToShow == date('Y-m-d'))) {
	$dayToShow = date('Y-m-d', strtotime($dayToShow . ' -1 day'));
	$_SESSION['day_to_show'] = $dayToShow;
}


$extraHeader =<<<EOT

	<script type="text/javascript">

		function cancelBooking(id) {
			if(confirm('Are you sure to cancel the booking?')) { 
				new Ajax.Request('cancel_booking.php', {
					method: 'post',
					parameters: {description_id: id, type: 'reception'},
					onSuccess: function(transport) {
						alert('The booking is cancelled.');
						$('bcr_' + id).hide();
					}
				});
			}
		}
		
		function loadPanes() {
			new Ajax.Updater({ success: 'guests_arriving_today', failure: 'notice' }, 'index_guests_arriving_today.php',
				{parameters: { today: '$dayToShow' } });
			new Ajax.Updater({ success: 'guests_leaving_today', failure: 'notice' }, 'index_guests_leaving_today.php',
				{parameters: { today: '$dayToShow' } });
			new Ajax.Updater({ success: 'room_changes_today', failure: 'notice' }, 'index_room_changes_today.php',
				{parameters: { today: '$dayToShow' } });
			new Ajax.Updater({ success: 'current_guests', failure: 'notice' }, 'index_current_guests.php',
				{parameters: { today: '$dayToShow' } });
		}


	</script>

EOT;


html_start("Maverick Reception - Activities for today ($today)", $extraHeader, true, 'loadPanes()');

	echo <<<EOT

<form action="index.php">
Current date is set to: $dayToShow.<br>
<input name="day_to_show" value="$dayToShow"><input type="submit" value="Set ative day">
</form><br><br>

<table style="border-collapse: collapse;">
<tr><td style="vertical-align: top; border: 1px solid black; padding: 10px;">

<div id="guests_arriving_today"><img src="loading.gif"></div>

</td><td style="vertical-align: top; border: 1px solid black; padding: 10px;">

<div id="guests_leaving_today"><img src="loading.gif"></div>

</td><td style="vertical-align: top; border: 1px solid black; padding: 10px;">

<div id="room_changes_today"><img src="loading.gif"></div>

</td></tr></table>
<br>

<!-- Current guests data here -->
<div id="current_guests"><img src="loading.gif"></div>


EOT;


html_end();


?>
