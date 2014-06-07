<?php

$TRANSLATIONS = array(
	'eng' => array(
		'IandI' => 'About Us',
		'SpecialOffers' => 'Special Offers',
		'TheMaverick' => 'The Maverick',
		'Hostel' => 'Hostel',
		'Ensuites' => 'Ensuites',
		'EnsuitesTop' => 'Ensuites on the Top',
		'Videos' => 'Videos',
		'Policy' => 'Policy',
		'BookingAndPrices' =>  'Booking and Prices',
		'Directions' => 'Directions',
		'HaveFun' => 'Have Fun',
		'BonApetit' =>  'Bon Apetit',
		'ContactUs' => 'Contact Us',
		'Links' => 'Links'),
	'fra' => array(
		'IandI' => 'Á notre Sujet',
		'SpecialOffers' => 'Offres spéciales',
		'TheMaverick' => 'Le Maverick',
		'Hostel' => 'Hostel',
		'Ensuites' => 'Ensuites',
		'EnsuitesTop' => 'Ensuites en haut',
		'Videos' => 'Vidéos',
		'Policy' => 'Règlement interne',
		'BookingAndPrices' =>  'Réservation et prix',
		'Directions' => 'Directions',
		'HaveFun' => 'Have Fun',
		'BonApetit' =>  'Bon Apetit',
		'ContactUs' => 'Contactez-nous',
		'Links' => 'Liens'),
	'esp' => array(
		'IandI' => '¿Quiénes somos?',
		'SpecialOffers' => 'Ofertas especiales',
		'TheMaverick' => 'El Maverick',
		'Hostel' => 'Hostel',
		'Ensuites' => 'Ensuites',
		'EnsuitesTop' => 'Ensuites en la parte superior',
		'Videos' => 'Videos',
		'Policy' => 'El reglamento',
		'BookingAndPrices' =>  'Reservaciones y tarifas',
		'Directions' => 'Localización',
		'HaveFun' => 'Actividades',
		'BonApetit' =>  'Bon Apetit',
		'ContactUs' => 'Contact',
		'Links' => 'Enlaces'),
	'deu' => array(
		'IandI' => 'über uns',
		'SpecialOffers' => 'Sonderangebote',
		'TheMaverick' => 'Maverick',
		'Hostel' => 'Hostel',
		'Ensuites' => 'Ensuites',
		'EnsuitesTop' => 'Ensuites auf der Oberseite',
		'Videos' => 'Videos',
		'Policy' => 'Politik',
		'BookingAndPrices' =>  'Buchung-Preise',
		'Directions' => 'Direktion',
		'HaveFun' => 'Viel Spaß',
		'BonApetit' =>  'Bon Apetit',
		'ContactUs' => 'Kontakt',
		'Links' => 'Links'),
	'hun' => array(
		'IandI' => '| és |', 
		'SpecialOffers' => 'Különleges ajánlatok',
		'TheMaverick' => 'A Maverick',
		'Hostel' => 'Hostel',
		'Ensuites' => 'Ensuites',
		'EnsuitesTop' => 'Ensuites fent',
		'Videos' => 'Videók',
		'Policy' => 'Szabályzat',
		'BookingAndPrices' =>  'Szoba foglalás, árak',
		'Directions' => 'Hol vagyunk',
		'HaveFun' => 'Have Fun',
		'BonApetit' =>  'Bon Apetit!',
		'ContactUs' => 'Elérhetõségek',
		'Links' => 'Linkek')
);

$MENUS = array(
	'IandI' => 'index.php',
	'TheMaverick' => array(
		'index' => 'hostel.php',
		'Hostel' => 'hostel.php',
		'Ensuites' => 'ensuites.php',
		'EnsuitesTop' => 'ensuites_top.php',
		'Videos' => 'videos.php',
		'Policy' => 'policy.php'),
	'BookingAndPrices' => 'booking_prices_and_special_offers.php', 
	'Directions' => 'directions.php',
	'HaveFun' => 'have_fun.php',
	'BonApetit' =>  'bon_apetit.php',
	'ContactUs' => 'contact_us.php',
	'Links' => 'links.php'
);


$HEADER_IMAGES = array(
	'IandI' => BASE_URL . 'images/Top_iandi.jpg',
	'TheMaverick' => BASE_URL . 'images/Top_themaverick.jpg',
	'BookingAndPrices' => BASE_URL . 'images/Top_booking.jpg',
	'Directions' => BASE_URL . 'images/Top_directions.jpg',
	'HaveFun' => BASE_URL . 'images/Top_havefun.jpg',
	'BonApetit' => BASE_URL . 'images/Top_bonapetit.jpg',
	'ContactUs' => BASE_URL . 'images/Top_contactus.jpg',
	'Links' => BASE_URL . 'images/Top_links.jpg'
);




function html_start($mainMenu, $subMenu, $title = null, $extraHeader = '') {
	global $MENUS;
	global $TRANSLATIONS;
	global $HEADER_IMAGES;

	$lang = getCurrentLanguage();
	$thisFileNameMinusLang = substr($_SERVER['SCRIPT_NAME'], strlen(BASE_URL) + 1 + strlen($lang));
	if(strlen($_SERVER['QUERY_STRING']) > 0) {
		$thisFileNameMinusLang .= '?' . $_SERVER['QUERY_STRING'];
	}
	if(is_null($title)) {
		if(is_null($subMenu))
			$title = $TRANSLATIONS[$lang][$mainMenu];
		else
			$title = $TRANSLATIONS[$lang][$subMenu];
	}

	$headerImg = $HEADER_IMAGES[$mainMenu];

	echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Maverick Hostel - $title</title>
	<link href="../style/maverick.css" rel="stylesheet" type="text/css"/>
	<link href="../style/menu.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="../js/maverick.js"></script>
	<script type="text/javascript" src="../js/prototype-1.6.0.2.js"></script>
	$extraHeader
</head>

<body>
	<script type="text/javascript" src="../js/wz_tooltip.js"></script>

	<div class="content" align="center">
		<div class="header"><img src="$headerImg" alt="Maverick Hostel Budapest"></div>
		<div class="main_menu_container">
			<div class="main_menu">
				<div class="menu_right_side"></div>
			</div>

EOT;
	foreach($MENUS as $mainMenuId => $menu) {
		$menuTitle = $TRANSLATIONS[$lang][$mainMenuId];
		if(is_array($menu)) {
			$menuUrl = $menu['index'];
		} else {
			$menuUrl = $menu;
		}
		if($mainMenuId == $mainMenu) {
			$menuClass = 'main_menu_in';
			$divParams = "";
		} else {
			$menuClass = 'main_menu';
			$divParams = ' onmouseover="this.className=\'main_menu_over\';" onmouseout="this.className=\'main_menu\';"';
		}
		echo <<<EOT
			<div class="$menuClass"$divParams>
				<div class="menu_left_side"></div>
				<a href="$menuUrl">$menuTitle</a>
				<div class="menu_right_side"></div>
			</div>

EOT;
	}

	echo <<<EOT
			<div class="main_menu">
				<div class="menu_left_side"></div>
			</div>
		</div>

		<div class="sub_menu_container">
			<div class="sub_menu_items">

EOT;

	if(is_array($MENUS[$mainMenu])) {
			foreach($MENUS[$mainMenu] as $subMenuId => $subMenuUrl) {
				if($subMenuId == 'index')
					continue;

				$subMenuTitle = $TRANSLATIONS[$lang][$subMenuId];
				$menuClass = ($subMenuId == $subMenu) ? 'sub_menu_in' : 'sub_menu';
				echo <<<EOT
				<div class="$menuClass">
					<a href="$subMenuUrl">$subMenuTitle</a>
				</div>

EOT;
			}
	}

	$espLangMenuClass = 'sub_menu_language';
	$engLangMenuClass = 'sub_menu_language';
	$fraLangMenuClass = 'sub_menu_language';
	$deuLangMenuClass = 'sub_menu_language';
	if($lang == 'esp') {
		$espLangMenuClass = 'sub_menu_language_in';
	} elseif($lang == 'eng') {
		$engLangMenuClass = 'sub_menu_language_in';
	} elseif($lang == 'fra') {
		$fraLangMenuClass = 'sub_menu_language_in';
	} elseif($lang == 'deu') {
		$deuLangMenuClass = 'sub_menu_language_in';
	}	

	echo <<<EOT
			</div>
			<div class="sub_menu_languages">

EOT;

	if(isClientFromHU() and substr($_SERVER['REQUEST_URI'], 0, 4) == '/en/') {
	echo <<<EOT
				<div class="$engLangMenuClass">
					<a href="../en/$thisFileNameMinusLang">eng</a>
				</div>
			</div>

EOT;

	} else {
	echo <<<EOT
				<div class="$espLangMenuClass">
					<a href="../esp/$thisFileNameMinusLang">esp</a>
				</div>
				<div class="$engLangMenuClass">
					<a href="../eng/$thisFileNameMinusLang">eng</a>
				</div>
				<div class="$fraLangMenuClass">
					<a href="../fra/$thisFileNameMinusLang">fra</a>
				</div>
				<div class="$deuLangMenuClass">
					<a href="../deu/$thisFileNameMinusLang">deu</a>
				</div>
			</div>

EOT;
	}

	echo <<<EOT
		</div>

		<div class="body_content">
		<!-- page content starts here!!! -->

			<h1>$title</h1>

EOT;

	foreach(get_errors() as $error) {
		echo "\t\t\t<div class=\"error\">$error</div>\n";
	}
	clear_errors();
	foreach(get_messages() as $msg) {
		echo "\t\t\t<div class=\"message\">$msg</div>\n";
	}
	clear_messages();
	foreach(get_debugs() as $msg) {
		echo "\t\t\t<div class=\"debug\">$msg</div>\n";
	}
	clear_debugs();

}

function html_end($mainMenu, $subMenu) {
echo <<<EOT


		<!-- page content ends here!!! -->
		</div>
	</div>

	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-38022880-1']);
	  _gaq.push(['_trackPageview']);
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>


</body>
</html>

EOT;
}
?>
