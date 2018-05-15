<!-- BEGIN BCR -->

Booking arrived (<a href="{editBookingUrl}">edit</a>)<br>

<table>	
<tr><td>Name: </td><td>{booker_name}</td></tr>
<tr><td>Emai: </td><td>{booker_email}</td></tr>
<tr><td>Phone: </td><td>{booker_phone}</td></tr>
<tr><td>Nationality: </td><td>{booker_nationality}</td></tr>
<tr><td>Address: </td><td>{booker_address}</td></tr>
<tr><td>Arrival: </td><td>{booker_arrival_date}</td></tr>
<tr><td>Departure: </td><td>{booker_departure_date}</td></tr>
<tr><td>Num of nights: </td><td>{booker_number_of_nights}</td></tr>
<tr><td>Comment: </td><td>{booker_comment}</td></tr>
</table>

<!-- BEGIN item_block -->
{item_title}:<br>
<table cellpadding="10" cellspacing="5">
<tr><th>Name</th><th>Type</th><th>Price</th></tr>
<!-- BEGIN item -->
<tr><td>{item_name}</td><td>{item_price}</td></tr>
<!-- END item -->
</table>
<!-- END item_block -->

<!-- BEGIN total_payment -->
Total: {booker_totalprice}<br>
<!-- END total_payment -->


<!-- END BCR -->