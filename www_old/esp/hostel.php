<?php

require('../includes.php');


html_start('TheMaverick', 'Hostel');

echo <<<EOT

<a href="#photos">Photos</a><br><br><br>

<p>
El Hostel Maverick cuenta con cinco habitaciones de las cuales una es para diez personas, una de seis personas, una de cinco personas y dos habitaciones dobles.
</p>


<div style="float: right; margin-right: 20px; width: 455px; height: 743px; background-color: #9DB1D6">
<img src="/images/Plan.jpg">
</div>

<br>
<p><strong style="font-size: 12px;">Mr Green (El Sr. Verde)</strong><br>
La habitación Mr Green tiene capacidad para diez personas, cuatro de ellas en una planta superior. Ornamentada con plantas y flores también brinda una estantería con libros para los más autodidactas.</p>

<p><strong style="font-size: 12px;">Mss Peach (La Sra. Melocotón)</strong><br>
Habitación dotada de chimenea antigua y vitral en la ventana, piezas originales de la arquitectura de la época. Cuenta con cinco camas personales.</p>

<p><strong style="font-size: 12px;">Mr & Mss Yellow (El Sr. y la Sra. Amarilla) </strong><br>
Habitación doble cuenta con una cama doble de matrimonio y diseñada fundamentalmente para parejas gracias a su ambiente acogedor. Cuenta también con televisor y la posibilidad de cama suplementaria.</p>

<p><strong style="font-size: 12px;">Ms Lemon (La Sra. Limón)</strong><br>
Habitación doble cuenta con una cama doble de matrimonio y diseñada fundamentalmente para parejas gracias a su ambiente acogedor. Cuenta también con televisor y la posibilidad de cama suplementaria.</p>

<p><strong style="font-size: 12px;">The  Blue Brothers (Los hermanos Azul)</strong><br>
Cuenta con seis camas personales, tres de ellas en planta superior. Ideal para grupos de amigos que deseen disfrutar de un ambiente acogedor garantizado por una chimenea de época.</p>

<p>
<h2>Servicios</h2>

<ul>
	<li>Recepción durante 24 horas</li>
	<li>Horas de entrada y salida flexibles</li>
	<li>Conexión a internet y wifi gratuita</li>
	<li>Ropa de cama incluida</li>
	<li>Toalla incluida</li>
	<li>Servicio de lavandería</li>
	<li>Secador de pelo y plancha disponible</li>
	<li>Cocina completamente equipada</li>
	<li>Ascensor</li>
	<li>Toda el área del hostel calificada para no fumadores</li>
	<li>Servicio de taxis desde y hasta el aeropuerto</li>
	<li>Servicio de biblioteca</li>
	<li>Caja fuerte</li>
	<li>Renta de bicicletas</li>
	<li>Recorridos turísticos organizados con guías (A consultar en recepción)</li>
	<li>Informaciones en recepción</li>
	<li>Café y té gratuitos</li>
	<li>Refrescas disponibles en máquina expendedora</li>
</ul>

<div style="clear:both;">
<br><br>

<h2><a name="photos">Photos</a></h2>

EOT;

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
			if($images[$bigFile]['type'] != 'HOSTEL') {
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


html_end('TheMaverick', 'Hostel');

?>
