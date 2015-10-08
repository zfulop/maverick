<?php


require("includes.php");

$link = db_connect();


$extraHeader = <<<EOT

<script src="js/datechooser/date-functions.js" type="text/javascript"></script>
<script src="js/datechooser/datechooser.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datechooser/datechooser.css">
<!--[if lte IE 6.5]>
<link rel="stylesheet" type="text/css" href="js/datechooser/select-free.css"/>
<![endif]-->

<style>
	table tr td {
		border-bottom: 1px solid black;
	}
	
	a.selected {
		font-size: 130%;
	}
</style>

EOT;


html_start("Maverick Reception - Blacklist", $extraHeader);

$today = date('Y-m-d');

$list = array();
$order = 'email';
if(isset($_REQUEST['order'])) {
	$_SESSION['order'] = $_REQUEST['order'];
}
if(isset($_SESSION['order'])) {
	$order = $_SESSION['order'];
}
$sql = "SELECT * FROM blacklist ORDER BY $order";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get blacklist in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
}

$dateOfEntryOrderClass = ($order == 'date_of_entry' ? 'selected' : '');
$nameOrderClass = ($order == 'name' ? 'selected' : '');
$emailOrderClass = ($order == 'email' ? 'selected' : '');
$sourceOrderClass = ($order == 'source' ? 'selected' : '');
$reasonOrderClass = ($order == 'reason' ? 'selected' : '');

echo <<<EOT


<form action="add_blacklist_item.php" method="post" accept-charset="utf-8">
<table style="border: 1px solid black;">
	<tr><th colspan="2">Add new item to blacklist</th></tr>
	<tr>
		<td><strong>Date of entry</strong></td><td>$today</td>
	</tr>
	<tr>
		<td><b>Name</b></td>
		<td><input name="name"></td>
	</tr>
	<tr>
		<td><b>Email</b></td>
		<td><input name="email"></td>
	</tr>
	<tr>
		<td><b>Source</b></td>
		<td><input name="source"></td>
	</tr>
	<tr>
		<td><b>Reason</b></td>
		<td><input name="reason"></td>
	</tr>
	<tr><td colspan="2"><input type="submit" value="Save"></td></tr>
</table>
</form>


<table>
	<tr><th colspan="6">Blacklist</th></tr>
	<tr><th><a class="$dateOfEntryOrderClass" href="?order=date_of_entry">Date of entry</a></th><th><a class="$nameOrderClass" href="?order=name">Name</a></th><th><a class="$emailOrderClass" href="?order=email">Email</a></th><th><a class="$sourceOrderClass" href="?order=source">Source</a></th><th><a class="$reasonOrderClass" href="?order=reason">Reason</a></th><th></th></tr>
EOT;

for($i = 0; $i < count($list); $i++) {
	$row = $list[$i];
	$date = $row['date_of_entry'];
	$id = $row['id'];
	$name = $row['name'];
	$email = $row['email'];
	$source = $row['source'];
	$reason = $row['reason'];
	echo <<<EOT
	<tr>
		<td>$date</td>
		<td>$name</td>
		<td>$email</td>
		<td>$source</td>
		<td>$reason</td>
		<td>
			<a href="delete_blacklist_item.php?id=$id">Delete</a> 
		</td>
	</tr>

EOT;

}

echo <<<EOT
</table>


EOT;

mysql_close($link);


html_end();


?>
