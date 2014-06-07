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


		<h2>Addresse </h2>
		<p>1051 Budapest, Ferenciek tere 2. </p>


		<h2>Transports publics</h2>
		<p>Les transports suivants s’arrêtent devant, à côté ou en face de l’immeuble:
		<ul>
			<li>Bus 7, 78, 8, 112, 15, 5, City Bus</li>
			<li>Ligne de métro bleue</li>
			<li>Tram 2</li>
			<li>Plusieurs bus de nuit</li>
		</ul></p>

		<h2>Gares</h2>
		<ul>
			<li>Keleti - 5min.
				<ul>
					<li>Prendre le bus 7 jusqu’à Ferenciek tere. </li>
					<li>Prendre le métro ligne rouge (N° 2) jusqu’a Deák tér puis prendre le métro ligne bleue (N° 3) jusqu’à Ferenciek Tere</li>
				</ul>
			</li>
			<li>Nyugati - 5min.
				<ul>
					<li>Prendre le métro ligne bleue (N° 3) jusqu’à Ferenciek tere.</li>
				</ul>
			</li>
			<li>Déli - 10min.
				<ul>
					<li>Prendre le métro ligne rouge(N° 3) jusqu’à Deák tér puis prendre le métro ligne bleue (N° 3) jusqu’à Ferenciek Tere</li>
				</ul>
			</li>
		</ul>

		<h2>Aéroport</h2>
		<ul>
			<li>Terminal 1 - 40min.
				<ul>
					<li>Prendre le train jusqu’à Nyugati, puis prendre le métro ligne bleue (No 3) jusqu’à Ferenciek Tere</li>
				</ul>
			</li>
			<li>Terminal 2 - 45min.
				<ul>
					<li>Prendre le bus 200, "Reptéri busz", jusqu’à Kőbánya Kispest, puis prendre le métro ligne bleue (N° 3) jusqu’à Ferenciek Tere</li>
				</ul>
			</li>
			<li>Transfer available.</li>
		</ul>

		<h2>Station de bus internationale - 10min.</h2>
		<ul>
			<li>Prenez le métro ligne bleue (N° 3) jusqu’à Ferenciek Tere</li>
		</ul>

		<p>
Quand vous arrivez sur la Place Ferenciek restez sur le côté de l'église et descendez la grande rue vers le Danube. Nous sommes dans le premier bâtiment. Notre entrance est un grand port vert ,qui est derriére d'arrét d'autobus.
		</p>

EOT;

html_end('Directions', null);

?>
