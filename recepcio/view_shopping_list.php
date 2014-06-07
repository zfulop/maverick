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

html_start("Maverick Reception - Shopping List", $extraHeader);

$today = date('Y-m-d');
echo <<<EOT

<form action="add_shopping_item.php" method="post" accept-charset="utf-8">
<table style="border: 1px solid black;">
	<tr><th colspan="2">Add new item to shopping list</th></tr>
	<tr>
		<td><strong>Date</strong></td><td>$today</td>
	</tr>
	<tr>
		<td>Description</td>
		<td>
			<textarea name="description"></textarea>
		</td>
	<tr>
	<tr><td colspan="2"><input type="submit" value="Save"></td></tr>
</table>
</form>


<table>
	<tr><th colspan="4">Shopping List</th></tr>
	<tr><th>Date</th><th>Description</th><th></th></tr>
EOT;

$link = db_connect();

$sql = "SELECT * FROM shopping_list ORDER BY create_date";
$result = mysql_query($sql, $link);

while($row = mysql_fetch_assoc($result)) {
	$cdate = $row['create_date'];
	$id = $row['id'];
	$descr = $row['description'];
	echo <<<EOT
	<tr id="tr_view_$id"><td>$cdate</td><td>$descr</td><td><a href="delete_shopping_item.php?id=$id">Delete</a> <a href="#" onclick="$('tr_view_$id').hide();$('tr_edit_$id').show();">Edit</a></td></tr>
	<form action="save_shopping_item.php" method="post" accept-charset="utf-8">
	<input type="hidden" name="id" value="$id">
	<tr id="tr_edit_$id" style="display:none;"><td>$cdate</td><td><textarea name="description">$descr</textarea></td><td><input type="submit" value="Save"></td></tr>
	</form>

EOT;
}

echo <<<EOT
</table>


EOT;

mysql_close($link);

html_end();


?>
