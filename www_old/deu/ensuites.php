<?php

require('../includes.php');


html_start('TheMaverick', 'Ensuites', 'Maverick ensuites - Doppelzimmer mit eigenem Bad');

echo <<<EOT


<div style="float: right; margin-right: 20px; width: 500px; height: 430px; background-color: #9DB1D6">
<img src="/images/Plan_ensuite.jpg">
</div>

<a href="#fotos">Fotos</a><br><br><br>

<p>
Wir haben ein grosses Gemeinschaftsraum und die Rezeption in unserem neuen Gebäudeteil. Unsere Computer und wifi stehen Ihnen kostenlos zur Verfügung.<br>
Jedes unserer Privatzimmer in diesem Stock ist mit eigenem Badezimmer und Kabel-TV ausgestattet. Alle Zimmer, die Lounge und die Küche sind geräumig und haben ein schönes, eigenes Design.
</p>

<p>
<h2>Dienstleistungen</h2>

<ul>
	<li>24 Stunden Rezeption</li>
	<li>Flexibles check in / out</li>
	<li>Freies Internet und Wi-Fi Nutzung</li>
	<li>Bettwäsche inbehalten</li>
	<li>Wäscherei</li>
	<li>Kaffe und Tee kostenlos den ganzen Tag</li>
	<li>Handtücher sind in den Preis eingerechnet</li>
	<li>Lebensmittelgeschäft an der Ecke an unseren Gebäude, der auch nachts geöffnet hatt</li>
	<li>Kostenlose Tour den ganzen Tag</li>
	<li>Schließfächer</li>
	<li>Fön und Bügeleisen verfügbar</li>
	<li>Voll ausgestattete Küche </li>
	<li>Lift</li>
	<li>Rauchfrei Umwelt</li>
	<li>Transport vom Flughafen auf Anfrage</li>
	<li>Gratis-Bibliothek</li>
	<li>Sicherheits- Spinde</li>
	<li>Fahrradverleih</li>
	<li>Organisierte Touren, Führungen auf Anfrage.</li>
	<li>Öffentliche Verkehrsmittel beizuziehen.</li>
</ul>

<div style="clear:both;">
<br><br>

<h2><a name="fotos">Fotos</a></h2>

EOT;


$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);

$currLang = getCurrentLanguage();

$sql = "SELECT images.*, lang_text.value AS description FROM images LEFT OUTER JOIN lang_text ON (lang_text.table_name='images' AND lang_text.column_name='description' AND row_id=images.id AND lang_text.lang='$currLang')";
$result = mysql_query($sql, $link);
$images = array();
while($row = mysql_fetch_assoc($result)) {
	$images[$row['filename']] = $row;
}

if ($dh = opendir(PHOTOS_DIR)) {
	$hidden = "";
	while ($file = readdir($dh)) {
		if(is_dir(PHOTOS_DIR . "/" . $file))
			continue;
		if(substr($file, 0, 7) != '_thumb_')
			continue;

		$hidden .= "<img src=\"" . PHOTOS_URL . "/$bigFile\">";
		$bigFile = substr($file, 7);
		$descr = '';
		if(isset($images[$bigFile])) {
			$descr = $images[$bigFile]['description'];
			if($images[$bigFile]['type'] != 'ENSUITE') {
				continue;
			}
		} else {
			continue;
		}

		echo "<div class=\"photo\"><img src=\"" . PHOTOS_URL . "/$file\" onmouseover=\"Tip('<img src=\'" . PHOTOS_URL . "/$bigFile\'>', TITLE, '$descr', BORDERCOLOR, '#ffffff', BORDERWIDTH, 7, PADDING, 0, SHADOW, true, SHADOWWIDTH, 7, SHADOWCOLOR, '#555555', CENTERMOUSE, true, OFFSETX, 0, CLOSEBTN, true, FIX, [CalcFixX(), CalcFixY()], CLICKCLOSE, true, STICKY, true, DURATION, 5000);\" onmouseout=\"UnTip();\"/><div class=\"title\">$descr</div></div>\n";

	}
	closedir($dh);
	echo "<div style=\"clear: both;\"></div><div style=\"display: none;\">$hidden</div>\n";
}

mysql_close($link);



html_end('TheMaverick', 'Ensuites');

?>
