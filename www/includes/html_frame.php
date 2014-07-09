<?php


$MENUS = array(
	'HOME' => 'index.php',
	'LOCATION_NAME_LODGE_MENU' => 'maverick_city_lodge.php',
	'LOCATION_NAME_HOSTEL_MENU' => 'maverick_hostel_ensuites.php',
	'GROUPS' => 'groups.php',
	'RESTAURANT' => '',
	'CONTACT' => 'contact.php');


function html_start($menuTitle, $extraHeader = '', $onloadScript='') {
	global $MENUS;

	$lang = getCurrentLanguage();
	$currency = getCurrency();
	$title = $menuTitle;


	echo <<<EOT
<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>$title</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <link rel="stylesheet" href="/css/normalize.css">
  <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,600,700|Oswald:300,700&subset=latin,latin-ext">
  <link rel="stylesheet" href="/css/main.css">
  <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
$extraHeader
</head>
<body onload="$onloadScript">
  <div id="zoom-helper">
  <div class="site-wrapper">
    <header id="header">
      <h1 class="logo">
        <a class="ir" href="index.php">Budapest Maverick Lodges</a>
      </h1>
      
      <nav class="navigation">
        <ul>

EOT;
	foreach($MENUS as $menuId => $file) {
		if(strlen($file) < 1) {
			echo "            <li>" . constant($menuId) . "</li>\n";
		} else {
			echo "            <li><a href=\"$file\">" . constant($menuId) . "</a></li>\n";
		}
	}

	$currLang = getCurrentLanguage();
	echo <<<EOT
        </ul>
      </nav>
      
      <p class="language fake-select">
        <span class="value" style="font-size: 130%;"></span>
        <span class="open-select icon-down"></span>
        <select id="language" data-current-language="$currLang">

EOT;
	foreach(getLanguages() as $code => $lang) {
		echo "          <option value=\"$code\"" . ($code == $currLang ? ' selected="selected"' : '') . ">$lang</option>\n";
	}
	echo <<<EOT
        </select>
      </p>
      
      <p class="currency fake-select">
        <span class="value" style="font-size: 130%;"></span>
        <span class="open-select icon-down"></span>
        <select id="currency">

EOT;
	foreach(getCurrencies() as $oneCurr) {
		echo "          <option value=\"$oneCurr\"" . ($oneCurr == $currency ? ' selected="selected"' : '') . ">$oneCurr</option>\n";
	}

	echo <<<EOT
        </select>
      </p>
    </header>
    
    <div class="content-wrapper">
      <section id="mobile-header">
        <span class="open-header icon-menu"></span>
        <span class="close-header"></span>
        
        <p>Budapest Maverick Lodges</p>
      </section>
      


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
		echo "\t\t\t<!-- DEBUG $msg -->\n";
	}
	clear_debugs();

}

function html_end() {
	$policy = POLICY;
	$awards = AWARDS;
	$findUs = FIND_US_ON_FACEBOOK;
	$copyight = COPYRIGHT;
echo <<<EOT

        
      <footer id="footer">
        <div>
          <p>
            <a class="open-overlay" href="" data-overlay-title="$policy" data-overlay-content-url="policy.php">$policy</a>
          </p>
          <p>
            <a class="open-overlay" href="" data-overlay-title="$awards" data-overlay-content-url="awards.php">$awards</a>
          </p>
          <p>
            <a href="http://www.facebook.com/mavericklodges">$findUs</a>
          </p>
		  <p>
             <a href="http://google.com/+MaverickCityLodgeBudapest">City Lodge on Google+</a><br>
             <a href="http://google.com/+MaverickHostel">Hostel on Google+</a>
          </p>
          <p class="copyright">$copyight</p>
          <a href="http://www.famoushostels.com" style="float:right;position:relative;right:120px;top:-80px;width:78px;height:78px;"><img src="/img/europes_famous_hostels.jpg"></a>
        </div>
      </footer>
    </div>
  </div>
  </div>
  
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="js/vendor/jquery-ui-1.10.3.custom.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBuvc6MHvAJnJKVetcAg8xDF83w1_ycqpk&sensor=false"></script>
  <script src="/js/plugins.js"></script>
  <script src="/js/main.js"></script>
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-38022880-1', 'mavericklodges.com');
    ga('send', 'pageview');
  </script>

</body>
</html>
EOT;
}
?>
