<?php

require("includes.php");

$link = db_connect();

$sql = "SELECT * FROM receptionists ORDER BY name";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get receptionists in admin interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
$receptionists = array();
$recOptions = '';
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		$receptionists[] = $row;
		$recOptions .= '<option value="' . $row['login'] . '">' . $row['name'] . '</option>';
	}
}

mysql_close($link);

$monthOptions = '';
for($i = 1; $i <= 12; $i++) {
	$monthOptions .= '<option value="' . ($i < 10 ? '0' : '') . "$i\">$i</option>";
}


html_start("Maverick Mgmt - Receptionists");


echo <<<EOT

<table><tr>

<td valign="top">

<form id="create_btn">
<input type="button" onclick="document.getElementById('rec_form').reset();document.getElementById('rec_form').style.display='block'; document.getElementById('create_btn').style.display='none'; document.getElementById('login').disabled=false; return false;" value="Create new receptionist">
</form>
<br>

<form action="save_receptionist.php" id="rec_form" accept-charset="utf-8" method="POST" style="display: none;">
<fieldset>
<input type="hidden" name="id" id="id" value="">
<label>Login</label><input name="login" id="login" style="width: 240px;"><br>
<label>Name</label><input name="name" id="name" style="width: 240px;"><br>
<label>Email</label><input name="email" id="email" style="width: 240px;"><br>
<label>Telephone</label><input name="telephone" id="telephone" style="width: 240px;"><br>
</fieldset>
<fieldset>
<input type="submit" value="Save receptionist">
<input type="button" value="Cancel" onclick="document.getElementById('rec_form').reset();document.getElementById('rec_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;">
</fieldset>
</form>

</td>

<td valign="top">

View hours worked by a receptionist
<form action="view_receptionist_hours.php" method="GET">
<fieldset>
<label>Receptionsit</label><select name="login">$recOptions</select><br>
<label>Year-Month</label><input size="4" name="year"> - <select style="float:none; display: inline;" name="month">$monthOptions</select><br>
<input type="submit" value="Get hours">
</fieldset>
</form>

</td>

</tr></table>

<h2>Existing Receptionists</h2>
<table border="1">

EOT;

if(count($receptionists) > 0)
	echo "	<tr><th>Name</th><th>Login</th><th>Email</th><th>Telephone</th><th>Enabled</th><th></th></tr>\n";
else
	echo "	<tr><td><i>No record found.</i></td></tr>\n";

foreach($receptionists as $row) {
	$id = $row['id'];
	$login = $row['login'];
	echo "<script language=\"JavaScript\" type=\"text/javascript\">\n";
	echo "	function edit" . $id . "() {\n";
	echo "		document.getElementById('rec_form').reset();\n";
	echo "		document.getElementById('rec_form').style.display='block';\n";
	echo "		document.getElementById('create_btn').style.display='none';\n";
	echo "		document.getElementById('id').value='$id';\n";
	echo "		document.getElementById('login').value='$login';\n";
	echo "		document.getElementById('login').disabled=true;\n";
	echo "		document.getElementById('name').value='" . $row['name'] . "';\n";
	echo "		document.getElementById('email').value='" . $row['email'] . "';\n";
	echo "		document.getElementById('telephone').value='" . $row['telephone'] . "';\n";
	echo "	}\n";
	echo "</script>\n";
	echo "	<tr>\n";
	echo "		<td>" . $row['name'] . "</td><td>$login</td><td>" . $row['email'] . "</td><td>" . $row['telephone'] . "</td><td align=\"center\">" . ($row['enabled'] == 1 ? 'X' : '') . "</td>\n";
	echo "		<td>\n";
	echo "			<a href=\"delete_receptionist.php?id=$id&login=$login\">Delete</a><br>\n";
	if($row['enabled']) {
		echo "			<a href=\"disable_receptionist.php?id=$id&login=$login\">Disable</a><br>\n";
	} else {
			echo <<<EOT
		<a href="#" onclick="document.getElementById('enable_form_$id').style.display='block'; return false;">Enable</a><br>
		<form action="enable_receptionist.php" method="POST" id="enable_form_$id" accept-charset="utf-8" style="display:none;">
			<input type="hidden" name="id" value="$id">
			<input type="hidden" name="login" value="$login">
			<label>Password</label><input type="password" name="password1"><br>
			<label>Confirm password</label><input type="password" name="password2"><br>
			<input type="submit" value="Enable receptionist">
			<input type="button" value="Cancel" onclick="document.getElementById('enable_form_$id').style.display='none';">
		</form>

EOT;
	}
	echo "		</td>\n";
	echo "	</tr>\n";
}



echo <<<EOT
</table>

EOT;


html_end();



?>
