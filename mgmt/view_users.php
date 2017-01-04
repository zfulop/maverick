<?php

require("includes.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$link = db_connect();

$sql = "SELECT * FROM users ORDER BY name";
$result = mysql_query($sql, $link);
if(!$result) {
	trigger_error("Cannot get users in mgmt interface: " . mysql_error($link) . " (SQL: $sql)", E_USER_ERROR);
}
$users = array();
$recOptions = '';
$clOptions = '';
if($result) {
	while($row = mysql_fetch_assoc($result)) {
		$users[] = $row;
		if($row['role'] == 'RECEPTION') {
			$recOptions .= '<option value="' . $row['username'] . '">' . $row['name'] . '</option>';
		}
		if($row['role'] == 'CLEANER') {
			$clOptions .= '<option value="' . $row['username'] . '">' . $row['name'] . '</option>';
		}
	}
}

mysql_close($link);

$monthOptions = '';
for($i = 1; $i <= 12; $i++) {
	$monthOptions .= '<option value="' . ($i < 10 ? '0' : '') . "$i\">$i</option>";
}


html_start("Users");


echo <<<EOT

<form id="create_btn">
<input type="button" onclick="document.getElementById('rec_form').reset();document.getElementById('rec_form').style.display='block'; document.getElementById('create_btn').style.display='none'; document.getElementById('login').disabled=false; return false;" value="Create new user">
</form>
<br>

<form action="save_user.php" id="rec_form" accept-charset="utf-8" method="POST" style="display: none;">
<fieldset>
<input type="hidden" name="id" id="id" value="">
<div style="clear:both;"><label>Login</label><input name="username" id="username" style="width: 240px;"></div>
<div style="clear:both;"><label>Name</label><input name="name" id="name" style="width: 240px;"></div>
<div style="clear:both;"><label>Email</label><input name="email" id="email" style="width: 240px;"></div>
<div style="clear:both;"><label>Telephone</label><input name="telephone" id="telephone" style="width: 240px;"></div>
<div style="clear:both;"><label>Role</label><select name="role" id="role" style="width: 240px;">
	<option id="ADMIN" value="ADMIN">ADMIN</option>
	<option id="MANAGER" value="MANAGER">MANAGER</option>
	<option id="RECEPTION" value="RECEPTION">RECEPTION</option>
	<option id="CLEANER" value="CLEANER">CLEANER</option>
	<option id="CLEANER_SUPERVISOR" value="CLEANER_SUPERVISOR">CLEANER SUPERVISOR</option>
</select></div>
</fieldset>
<fieldset>
<input type="submit" value="Save user">
<input type="button" value="Cancel" onclick="document.getElementById('rec_form').reset();document.getElementById('rec_form').style.display='none'; document.getElementById('create_btn').style.display='block'; return false;">
</fieldset>
</form>

<div>
View hours worked by a receptionist
<form action="view_receptionist_hours.php" method="GET">
<fieldset>
<label>Receptionsit</label><select name="login">$recOptions</select><br>
<label>Year-Month</label><input size="4" name="year"> - <select style="float:none; display: inline;" name="month">$monthOptions</select><br>
<input type="submit" value="Get hours">
</fieldset>
</form>
</div>


<div>
View hours worked by a cleaner
<form action="view_cleaner_hours.php" method="GET">
<fieldset>
<label>Cleaner</label><select name="login">$clOptions</select><br>
<label>Year-Month</label><input size="4" name="year"> - <select style="float:none; display: inline;" name="month">$monthOptions</select><br>
<input type="submit" value="Get hours">
</fieldset>
</form>
</div>

<h2>Existing Users</h2>
<table border="1">

EOT;

if(count($users) > 0)
	echo "	<tr><th>Name</th><th>Login</th><th>Role</th><th>Email</th><th>Telephone</th><th></th></tr>\n";
else
	echo "	<tr><td><i>No record found.</i></td></tr>\n";

foreach($users as $row) {
	$id = $row['id'];
	$login = $row['username'];
	echo "<script language=\"JavaScript\" type=\"text/javascript\">\n";
	echo "	function edit" . $id . "() {\n";
	echo "		document.getElementById('rec_form').reset();\n";
	echo "		document.getElementById('rec_form').style.display='block';\n";
	echo "		document.getElementById('create_btn').style.display='none';\n";
	echo "		document.getElementById('id').value='$id';\n";
	echo "		document.getElementById('username').value='$login';\n";
	echo "		document.getElementById('name').value='" . $row['name'] . "';\n";
	echo "		document.getElementById('email').value='" . $row['email'] . "';\n";
	echo "		document.getElementById('telephone').value='" . $row['telephone'] . "';\n";
	echo "		document.getElementById('" . $row['role'] . "').selected=true;\n";
	echo "	}\n";
	echo "</script>\n";
	echo "	<tr>\n";
	echo "		<td>" . $row['name'] . "</td><td>$login</td><td>" . $row['role'] . "</td><td>" . $row['email'] . "</td><td>" . $row['telephone'] . "</td>\n";
	echo "		<td>\n";
	echo "			<a href=\"delete_user.php?id=$id&login=$login\">Delete</a><br>\n";
	echo "			<a href=\"reset_password.php?id=$id\">Reset password</a><br>\n";
	echo "			<a href=\"#\" onclick=\"edit" . $id . "();return false;\">Edit</a><br>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
}



echo <<<EOT
</table>

EOT;


html_end();



?>
