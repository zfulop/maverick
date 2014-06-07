<?php

require('../includes.php');


html_start('TheMaverick', 'EnsuitesTop', 'Maverick ensuites - habitación doble con baño privado');

echo <<<EOT


<div style="float: right; margin-right: 20px; width: 503px; height: 734px; background-color: #9DB1D6">
<img src="/images/Plan_ensuite_top.jpg">
</div>

<a href="#photos">Photos</a><br><br><br>

<p>
Cada una de nuestras cinco habitaciones dobles en el último piso cuenta con baño privado y TV por cable. Todas las habitaciones y el salón compartido con la cocina tiene una decoración innovadora y un menaje muy confortable. Una cama suplementaria es disponible mediante demanda.
</p>


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
	<li>Desayuno contenido en el precio en el Restaurante Repeta ubicado en la planta baja del edificio donde se encuentra el hostel.</li>
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
			if($images[$bigFile]['type'] != 'ENSUITE_TOP') {
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


html_end('TheMaverick', 'EnsuitesTop');

?>
