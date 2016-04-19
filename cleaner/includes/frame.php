<?php

function html_start($title = null, $extraHeader = '', $onloadScript = '') {
	$title = $_SESSION['login_hotel_name'] . ' - Cleaner - ' . $title;
	$loginName = $_SESSION['login_user'];

	$logout = ROOT_URL . 'logout.php';
	$changePassword = ROOT_URL . 'change_password.php';

	echo <<<EOT
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>$title</title>
	<link href="/css/bootstrap.min.css" rel="stylesheet">	
	<link href="/css/bootstrap-theme.min.css" rel="stylesheet">	

	$extraHeader
</head>

<body style="padding-top: 70px;" onload="$onloadScript">

<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container-fluid">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">RC</a>
		</div>

		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">$loginName</a>
					<ul class="dropdown-menu">
						<li><a href="$logout">Logout</a></li>
						<li><a href="$changePassword">Change Password</a></li>
					</ul>
				</li>
			</ul>
		</div><!-- /.navbar-collapse -->		
	</div> <!-- .container-fluid -->
</nav>


EOT;

	echo <<<EOT

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

	echo "	<div class=\"container-fluid\">\n";

}

function html_end() {
	echo <<<EOT
	
	</div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
EOT;
}

?>