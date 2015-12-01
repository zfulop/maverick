<?php

require("includes.php");

logout();
set_message("Successfully logged out");
header("Location: view_login.php");



?>
