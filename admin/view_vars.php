<?

require("includes.php");

if(!checkLogin(SITE_ADMIN)) {
	return;
}

print_r($_SERVER);

?>
