<?php

require("includes.php");

echo <<<EOT
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<link href="/css/bootstrap.min.css" rel="stylesheet">	
	<link href="/css/bootstrap-theme.min.css" rel="stylesheet">	
</head>

<body>

EOT;

$errors = get_errors();
foreach($errors as $error) {
	echo "	<div class=\"alert alert-danger\" role=\"alert\">ERROR: $error</div>\n";
}
clear_errors();
$warnings = get_warnings();
foreach($warnings as $warning) {
	echo "	<div class=\"alert alert-warning\" role=\"alert\">WARNING: $warning</div>\n";
}
clear_warnings();
$messages = get_messages();
foreach($messages as $msg) {
	echo "	<div class=\"alert alert-info\" role=\"alert\">$msg</div>\n";
}
clear_messages();
$debug = get_debug();
foreach($debug as $msg) {
	echo "	<div class=\"alert alert-info\" role=\"alert\">DEBUG: $msg</div>\n";
}
clear_debug();

echo <<<EOT

	<div class="container-fluid">
	<div class="row">
	<div class="col-md-4 col-md-offset-4">
		<div class="panel panel-default">
			<div class="panel-heading">
			    <h3 class="panel-title">Cleaner Login</h3>
			</div>
			<div class="panel-body">
				<form action="do_login.php" method="POST" class="form-horizontal">
					<div class="form-group">
						<label for="hostel" class="col-sm-4 control-label">Hostel</label>
						<div class="col-sm-8"><input name="hostel" class="form-control" id="hostel"/></div>
					</div>
					<div class="form-group">
						<label for="username" class="col-sm-4 control-label">Username</label>
						<div class="col-sm-8"><input name="username" class="form-control" id="username"/></div>
					</div>
					<div class="form-group">
						<label for="password" class="col-sm-4 control-label">Password</label>
						<div class="col-sm-8"><input name="password" class="form-control" type="password" id="password"/></div>
					</div>
					<button type="submit" class="btn btn-default">Login</button>
				</form>
			</div>
		</div>
	</div>
	</div>
	</div>

EOT;

html_end();

?>