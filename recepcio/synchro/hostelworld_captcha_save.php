<?php

ini_set('display_errors', 'On');


require('booker.php');


$captcha = $_REQUEST['captcha'];
file_put_contents('captcha/hostelworld.txt', $captcha);

echo "OK\n";

?>
