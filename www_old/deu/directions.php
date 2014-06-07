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


		<h2>Öffentliche Verkehrsmittel</h2>
		<p>Die folgenden Fahrzeuge haben eine Haltestelle vor, hinter oder unter der Gebäude 
		<ul>
			<li>Bus 7, 78, 8, 112, 15, 5, City Bus</li>
			<li>U-Bahn Blaue Linie </li>
			<li>Straßenbahn 2</li>
			<li>Mehrere Nacht-Bus-Linien</li>
		</ul></p>

		<h2>Bahnhöfe</h2>
		<ul>
			<li>Keleti - 5min.
				<ul>
					<li>Nehmen Sie den Bus 7 bis Ferenciek tere. </li>
					<li>Nehmen Sie die U-Bahn-Linie 2 (rot) bis Deák tér, dann steigen Sie auf der Linie 3 (blau) bis Ferenciek tere um. </li>
				</ul>
			</li>
			<li>Nyugati - 5min.
				<ul>
					<li>Nehmen Sie die U-Bahn-Linie 3 (blau) bis Ferenciek tere.</li>
				</ul>
			</li>
			<li>Déli - 10min.
				<ul>
					<li>Mit der U-Bahn-Linie 2 (rot) bis Deák tér, dann steigen Sie auf der Linie 3 (blau) zu Ferenciek tere um.</li>
				</ul>
			</li>
		</ul>

		<h2>Flughafen</h2>
		<ul>
			<li>Terminal 1 - 40min.
				<ul>
					<li>Sie können einen regulären Zug nach Nyugati nehmen, und von dort die U-Bahn 3 (blau) bis Ferenciek tere.</li>
				</ul>
			</li>
			<li>Terminal 2 - 45min.
				<ul>
					<li>Nehmen Sie den Bus 200, Reptéri busz, auf Kőbánya Kispest, und von dort die U-Bahn 3 (blau) bis Ferenciek tere.</li>
				</ul>
			</li>
			<li>Abholung von dem Flughafen steht auch zur Verfügung </li>
		</ul>

		<h2>Internationale Busbahnhof - 10min.</h2>
		<ul>
			<li>Nehmen Sie die U-Bahn 3 (blau) bis Ferenciek tere.</li>
		</ul>


		<p>
Wenn Sie Ferenciek tere erreichen, halten sie Ausschau nach der Statue und der Kirche. Von der Kirche spazieren Sie in die Richtung der Brücke und der Eingang der Gebäude, was ein große grüne Eisentor ist,  wird hinter der Bushaltestelle.
		</p>

EOT;

html_end('Directions', null);

?>
