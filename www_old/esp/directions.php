<?php

require('../includes.php');


html_start('Directions', null);

echo <<<EOT
		<div style="float: right; padding: 10px;">
			<img src="/images/Map2_small.jpg" style="border: 4px solid rgb(0, 67, 125); margin-bottom: 30px;" onmouseover="Tip('<img src=\'/images/Map2.jpg\'>', BORDERCOLOR, '#00437d', BORDERWIDTH, 4, PADDING, 0, SHADOW, true, SHADOWWIDTH, 4, SHADOWCOLOR, '#555555', STICKY, true, DURATION, 5000, CENTERMOUSE, true, OFFSETX, 0, CLICKCLOSE, true);" onmouseout="UnTip();"><br>
			<img src="/images/Map_small.jpg" style="border: 4px solid rgb(0, 67, 125);" onmouseover="Tip('<img src=\'/images/Map.jpg\'>', BORDERCOLOR, '#00437d', BORDERWIDTH, 4, PADDING, 0, SHADOW, true, SHADOWWIDTH, 4, SHADOWCOLOR, '#555555', STICKY, true, DURATION, 5000, CENTERMOUSE, true, OFFSETX, 0, CLICKCLOSE, true);" onmouseout="UnTip();"><br>
			<img src="/images/Map2.jpg" style="display: none;">
			<img src="/images/Map.jpg" style="display: none;">
		</div>


		<h2>La dirección del Hostel Maverick</h2>
		<p>1051 Budapest, Ferenciek tere 2. </p>


		<h2>El transporte público</h2>
		<p>Estos vehículos se paran enfrente, detrás o debajo del edificio del hostal:
		<ul>
			<li>Autobuses 7, 78, 8, 112, 15, 5 y el City Bus</li>
			<li>El metro (línea 3, azul)</li>
			<li>El tranvía número 2</li>
			<li>Varias líneas de autobuses nocturnos </li>
		</ul></p>

		<h2>¿Cómo llegas desde las estaciones de ferrocarril?</h2>
		<ul>
			<li>La estación Este – 5 minutos
				<ul>
					<li>Toma el autobús número 7 hasta la Plaza Ferenciek.</li>
					<li>Toma el metro (línea 2, rojo) hasta la Plaza Deák, y despúes cambia para el metro (línea 3, azul) hasta la Plaza Ferenciek.</li>
				</ul>
			</li>
			<li>La estación Oeste – 10 minutos
				<ul>
					<li>Toma el metro (línea 3, azul) hasta la Plaza Ferenciek.</li>
				</ul>
			</li>
			<li>La estación Sur – 10 minutos.
				<ul>
					<li>Toma el metro (línea 2,  rojo) hasta la Plaza Deák, y despúes cambia para el metro (línea 3, azul) hasta la Plaza Ferenciek.</li>
				</ul>
			</li>
		</ul>

		<h2¿Cómo llegas desde los aeropuertos?</h2>
		<ul>
			<li>Terminal 1 - 40 minutos.
				<ul>
					<li>Puedes tomar un tren regular hasta la esatción de ferrocarril Oeste, y después toma el metro (línea 3, azul) hasta la Plaza Ferenciek.</li>
				</ul>
			</li>
			<li>Terminal 2 - 45 minutos.
				<ul>
					<li>Toma el autobus número 200 (le llamamos el autobús del aeropuerto, “Reptéri busz” en húngaro) hasta la estación Kőbánya-Kispest, y después toma el metro (línea 3, azul) hasta la Plaza Ferenciek.</li>
				</ul>
			</li>
			<li>Servicio de taxi disponible en el hostel (aeropuerto ↔ hostel.</li>
		</ul>

		<h2>¿Cómo llegas desde la estación internacional de autobuses?</h2>
		<ul>
			<li>Toma el metro (línea 3, azul) hasta la Plaza Ferenciek.</li>
		</ul>

		<p>
Cuando llegas a la Plaza Ferenciek, busques la estatua y la iglesia. Después comiences a caminar hacia la puente y la entrada del edificio del Hostal Maverick está detrás de la parada de autobús.
		</p>

EOT;

html_end('Directions', null);

?>
