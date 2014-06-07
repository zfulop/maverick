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


		<h2>Indirizzo</h2>
		<p>1051 Budapest, Ferenciek tere 2. </p>


		<h2>Trasporto pubblico</h2>
		<p>I seguenti mezzi pubblici si fermano di fronte o sotto l'ostello:
		<ul>
			<li>Autobus 7, 78, 8, 112, 15, 5, City Bus</li>
			<li>Metropolitana: linea blu</li>
			<li>Tram 2</li>
			<li>autobus notturni</li>
		</ul></p>

		<h2>Stazioni ferroviarie</h2>
		<ul>
			<li>Keleti Pályaudvar (stazione est): 5minuti
				<ul>
					<li>Prendere l'autobus 7 fino a Ferenciek tere. </li>
					<li>Prendere la linea 2 (rossa) della metropolitana fino a Deak ter, quindi cambiare per la linea 3 (blu) fino a Ferenciek tere.</li>
				</ul>
			</li>
			<li>Nyugati Pályaudvar (stazione ovest): 5 minuti
				<ul>
					<li>Prendere la linea 3 (blu) della metropolitana fino a Ferenciek tere.</li>
				</ul>
			</li>
			<li>Déli Pályaudvar (stazione sud): 10 minuti
				<ul>
					<li>Prendere la linea 2 (rossa) della metropolitana fino a Deak ter, quindi cambiare per la linea 3 (blu) fino a Ferenciek tere.</li>
				</ul>
			</li>
		</ul>

		<h2>Aeroporto</h2>
		<ul>
			<li>Terminal 1: 40 minuti
				<ul>
					<li>È possibile prendere un treno normale fino a Nyugati Pályaudvar e quindi la linea 3 (blu) della metropolitana fino a Ferenciek tere</li>
				</ul>
			</li>
			<li>Terminal 2: 45 minuti
				<ul>
					<li>Prendere l'autobus 200, Repteri busz, fino a Kobanya Kispest e quindi la linea 3 (blu) della metropolitana fino a Ferenciek tere.</li>
				</ul>
			</li>
			<li>È anche disponibile il prelievo dall'aeroporto.</li>
		</ul>

		<h2>Stazione internazionale degli autobus: 10 minuti</h2>
		<ul>
			<li>Prendere la linea 3 (blu) della metropolitana fino a Ferenciek tere.</li>
		</ul>

		<h2>Parcheggio: 2 minuti a piedi</h2>
		<ul>
			<li>anche un grande parcheggio a pagamento sul lato di Pest del Ponte Erzsébet.</li>
		</ul>

		<p>
Una volta arrivati a Ferenciek tere, cercare la statua e la chiesa. Quindi incamminarsi verso il ponte. L'entrata del nostro edificio si trova dietro la fermata dell'autobus, proprio accanto al negozio di fiori.	
		</p>

EOT;

html_end('Directions', null);

?>
