<?php

require('../includes.php');


html_start('TheMaverick', 'Rooms');

echo <<<EOT

<a href="#photos">Photos</a><br>

<p>
Il Maverick Hostel dispone di un'ampia gamma di camere che va da un unico dormitorio a intime camere doppie. Dai un'occhiata in giro!
</p>


<div style="float: right; margin-right: 20px; width: 455px; height: 743px; background-color: #9DB1D6">
<img src="/images/Plan.jpg">
</div>

<br>
<p><strong style="font-size: 12px;">Dormitorio Mr Green</strong><br>
10 letti circondati da piante verdi sul livello inferiore del dormitorio e 4 su quello superiore. Se vuoi leggere qualcosa, la nostra biblioteca gratuita è a tua disposizione.</p>

<p><strong style="font-size: 12px;">Camera Mss Peach a 5 letti</strong><br>
In questa camera a 5 letti, potrai svegliarti al fuoco di uno splendido camino e alla soffice luce che penetra attraverso l'eccezionale vetrata a mosaico della finestra. Perché non provare?</p>

<p><strong style="font-size: 12px;">Camera doppia Mr and Mss Yellow</strong><br>
Questa intima camera doppia con TV è adatta soprattutto alle coppie grazie alla calda ed amichevole atmosfera che la caratterizza.</p>

<p><strong style="font-size: 12px;">Camera doppia Ms Lemon</strong><br>
Questa intima camera doppia con TV è adatta soprattutto alle coppie grazie alla calda ed amichevole atmosfera che la caratterizza.</p>

<p><strong style="font-size: 12px;">Camera The Blue Brothers a 6 letti</strong><br>
In questa seconda camera loft vi sono 3 letti sul livello inferiore e altri 3 su quello superiore. Questa particolare sistemazione è perfetta per un gruppo di amici che desidera condividere l'atmosfera di una camera accogliente con caminetto d'epoca.</p>

<p>
<h2>Servizi</h2>

<ul>
	<li>Reception aperta 24 ore su 24</li>
	<li>Ceck-in/check-out flessibile</li>
	<li>Accesso Wi-Fi a Internet GRATUITO</li>
	<li>Tè e caffè GRATUITI tutto il giorno</li>
	<li>Asciugamani INCLUSI</li>
	<li>Biancheria INCLUSA</li>
	<li>Lavanderia</li>
	<li>Asciugacapelli e ferro da stiro disponibili</li>
	<li>Cucina completamente attrezzata</li>
	<li>Ascensore</li>
	<li>Struttura con divieto di fumo</li>
	<li>Prelievo all'aeroporto su richiesta</li>
	<li>Biblioteca gratuita</li>
	<li>Armadietti di sicurezza</li>
	<li>Noleggio biciclette</li>
	<li>Tour organizzati e guide su richiesta</li>
	<li>Informazioni sul trasporto pubblico</li>
</ul>


<div style="clear: both;"></div>
<br><br>


<h2><a name="photos">Photos</a></h2>

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
		if(substr($file, 0, 10) == '_thumb_5yY')
			continue;

		$hidden .= "<img src=\"" . PHOTOS_URL . "/$bigFile\">";
		$bigFile = substr($file, 7);
		$descr = '';
		if(isset($images[$bigFile])) {
			$descr = $images[$bigFile]['description'];
			if($images[$bigFile]['type'] == 'ENSUITE') {
				continue;
			}
		}

		echo "<div class=\"photo\"><img src=\"" . PHOTOS_URL . "/$file\" onmouseover=\"Tip('<img src=\'" . PHOTOS_URL . "/$bigFile\'>', TITLE, '$descr', BORDERCOLOR, '#ffffff', BORDERWIDTH, 7, PADDING, 0, SHADOW, true, SHADOWWIDTH, 7, SHADOWCOLOR, '#555555', CENTERMOUSE, true, OFFSETX, 0, CLOSEBTN, true, FIX, [CalcFixX(), CalcFixY()], CLICKCLOSE, true, STICKY, true, DURATION, 5000);\" onmouseout=\"UnTip();\"/><div class=\"title\">$descr</div></div>\n";

	}
	closedir($dh);
	echo "<div style=\"clear: both;\"></div><div style=\"display: none;\">$hidden</div>\n";
}

mysql_close($link);



html_end('TheMaverick', 'Rooms');

?>
