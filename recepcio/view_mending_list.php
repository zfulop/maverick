<?php


require("includes.php");


if(!checkLogin(SITE_RECEPTION)) {
	return;
}



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
</style>

EOT;

$colors = array("red" => "#ff0000",
				"green" => "#00ff00",
				"yellow" => "#ffff00",
				"orange" => "#ff8800",
				"blue" => "#0000ff",
				"white" => "#ffffff",
				"black" => "#000000");
$colorOptions = '';
foreach($colors as $color => $code) {
	$colorOptions .= "\t\t<option style=\"color: $code;\" value=\"$code\"" . ($color == 'black' ? ' selected' : '') . ">$color</option>\n";
}
$bgcolorOptions = '';
foreach($colors as $color => $code) {
	$bgcolorOptions .= "\t\t<option style=\"background-color: $code;\" value=\"$code\"" . ($color == 'white' ? ' selected' : '') . ">$color</option>\n";
}



html_start("Mending List", $extraHeader);

$today = date('Y-m-d');

$mtypes = array();
$typeOptions = '';
$sql = "SELECT * FROM mending_type ORDER BY type";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get mending type in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
} else {
	while($row = mysql_fetch_assoc($result)) {
		$mtypes[] = $row['type'];
		$typeOptions .= '<option>' . $row['type'] . '</option>';
	}
}




echo <<<EOT


<span style="padding:5px;margin-bottom:10px;background-color:rgb(0,255,0);">folyamatban van</span><br><br>

<span style="padding:5px;margin-bottom:10px;background-color:rgb(255,0,0);">1-3 napon belül</span><br><br>

<span style="padding:5px;margin-bottom:10px;background-color:rgb(255,125,0);">1-2 héten belül</span><br><br>

<span style="padding:5px;margin-bottom:10px;background-color:rgb(255,255,0);">2-3 héten belül</span><br><br>

<span style="padding:5px;margin-bottom:10px;background-color:rgb(0,0,255);">1 hónapon belül</span><br><br>

<span style="padding:5px;margin-bottom:10px;background-color:rgb(0,0,0);color:white;">3. személy átbeszélésre/válaszra/döntésre vár</span><br><br>


<form action="add_mending_item.php" method="post" accept-charset="utf-8">
<table style="border: 1px solid black;">
	<tr><th colspan="2">Add new item to mending list</th></tr>
	<tr>
		<td><strong>Last update</strong></td><td>$today</td>
	</tr>
	<tr>
		<td><strong>Due date</strong></td><td><input name="due_date" value="$today"></td>
	</tr>
	<tr>
		<td><b>Description</b></td>
		<td>
			<textarea name="description"></textarea>
		</td>
	</tr>
	<tr>
		<td><b>Owner</b></td>
		<td>
			<select name="type">$typeOptions</select>
		</td>
	</tr>
	<tr>
		<td><b>Color</b></td>
		<td>
			<select name="color">
$colorOptions
			</select>
		</td>
	</tr>
	<tr>
		<td><b>Background Color</b></td>
		<td>
			<select name="bgcolor">
$bgcolorOptions
			</select>
		</td>
	</tr>
	<tr><td colspan="2"><input type="submit" value="Save"></td></tr>
</table>
</form>


<table>
	<tr><th colspan="4">Mending List</th></tr>
	<tr><th>Priority</th><th>Last update</th><th>Due date</th><th>Owner</th><th>Description</th><th></th></tr>
EOT;

$sql = "SELECT * FROM mending_list ORDER BY type,priority,create_date";
$result = mysql_query($sql, $link);
$mlist = array();
while($row = mysql_fetch_assoc($result)) {
	$mlist[] = $row;
}

for($i = 0; $i < count($mlist); $i++) {
	$row = $mlist[$i];
	$cdate = $row['create_date'];
	$dueDate = $row['due_date'];
	$id = $row['id'];
	$descr = $row['description'];
	$rowcolor = $row['color'];
	$bgcolor = $row['bgcolor'];
	$priority = $row['priority'];
	$type = $row['type'];
	$prevPriority = 0;
	$nextid = '';
	$moveUpStyle = '';
	$moveDownStyle = '';
	if($i == 0) {
		$moveUpStyle = 'display: none;';
	} else {
		$prevPriority = $mlist[$i-1]['priority'];
	}
	if($i == (count($mlist)-1)) {
		$moveDownStyle = 'display: none;';
	} else {
		$nextid = $mlist[$i+1]['id'];
	}
	$colorOptions = '';
	foreach($colors as $color => $code) {
		$colorOptions .= "\t\t<option style=\"color: $code;\" value=\"$code\"" . ($row['color'] == $code ? ' selected' : '') . ">$color</option>\n";
	}
	$bgcolorOptions = '';
	foreach($colors as $color => $code) {
		$bgcolorOptions .= "\t\t<option style=\"background-color: $code;\" value=\"$code\"" . ($row['bgcolor'] == $code ? ' selected' : '') . ">$color</option>\n";
	}
	$typeOptions = '';
	foreach($mtypes as $mtype) {
		$typeOptions .= "\t\t<option value=\"$mtype\"" . ($type == $mtype ? ' selected' : '') . ">$mtype</option>\n";
	}
	echo <<<EOT
	<tr id="tr_view_$id">
		<td style="color: $rowcolor;background-color:$bgcolor;">$priority</td>
		<td style="color: $rowcolor;background-color:$bgcolor;">$cdate</td>
		<td style="color: $rowcolor;background-color:$bgcolor;">$dueDate</td>
		<td style="color: $rowcolor;background-color:$bgcolor;">$type</td>
		<td style="color: $rowcolor;background-color:$bgcolor;">$descr</td>
		<td>
			<a href="delete_mending_item.php?id=$id">Delete</a> 
			<a href="#" onclick="$('tr_view_$id').hide();$('tr_edit_$id').show();">Edit</a> <br> 
			<a style="font-weight:bold;text-decoration:none;$moveUpStyle" href="moveup_mending_item.php?id=$id&prev_priority=$prevPriority">&#8593;</a> 
			<a style="font-weight:bold;text-decoration:none;$moveDownStyle" href="moveup_mending_item.php?id=$nextid&prev_priority=$priority">&#8595;</a>
		</td>
	</tr>
	<form action="save_mending_item.php" method="post" accept-charset="utf-8">
	<input type="hidden" name="id" value="$id">
	<tr id="tr_edit_$id" style="display:none;">
		<td>&nbsp;</td>
		<td>$cdate</td>
		<td><input name="due_date" value="$dueDate"></td>
		<td><select name="type">$typeOptions</select></td>
		<td>
			<table style="width: 100%;">
				<tr><td rowspan="2" style="border:none;"><textarea style="width: 100%;" name="description">$descr</textarea></td><td style="border:none;">Color</td><td style="border:none;"><select name="color">$colorOptions</select></td></tr>
				<tr><td style="border:none;">Background color</td><td style="border:none;"><select name="bgcolor">$bgcolorOptions</select></td></tr>
			</table>
		</td>
		<td>
			<input type="submit" value="Save">
		</td>
	</tr>
	</form>

EOT;
	$prevPriority = $row['priority'];
}

echo <<<EOT
</table>


EOT;

mysql_close($link);


html_end();


?>

