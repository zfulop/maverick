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


		<h2>Address </h2>
		<p>1051 Budapest, Ferenciek tere 2. </p>


		<h2>Public transport</h2>
		<p>The following vechiles stop in front of, behind or under the building
		<ul>
			<li>Bus 7, 78, 8, 112, 15, 5, City Bus</li>
			<li>Subway blue line</li>
			<li>Tram 2</li>
			<li>Several Night bus lines</li>
		</ul></p>

		<h2>Railway stations</h2>
		<ul>
			<li>Keleti - 5min.
				<ul>
					<li>Take bus 7 till Ferenciek tere. </li>
					<li>Take the subway line 2(red) to De&aacute;k t&eacute;r, then change to the line 3(blue) to Ferenciek tere.</li>
				</ul>
			</li>
			<li>Nyugati - 5min.
				<ul>
					<li>Take the subway line 3(blue) to Ferenciek tere.</li>
				</ul>
			</li>
			<li>Déli - 10min.
				<ul>
					<li>Get on the subway line 2(red) to De&aacute;k t&eacute;r, then change to line 3(blue) to Ferenciek tere.</li>
				</ul>
			</li>
		</ul>

		<h2>Airport</h2>
		<ul>
			<li>Terminal 1 - 40min.
				<ul>
					<li>You can take a regular train to Nyugati, and then the subway 3(blue) to Ferenciek tere.</li>
				</ul>
			</li>
			<li>Terminal 2 - 45min.
				<ul>
					<li>Take bus 200, Rept&eacute;ri busz, to K&ocirc;b&aacute;nya Kispest, then take the subway 3(blue) to Ferenciek tere.</li>
				</ul>
			</li>
			<li>Airport pick up is also available.</li>
		</ul>

		<h2>International Bus station - 10min.</h2>
		<ul>
			<li>Take subway 3(blue) to Ferenciek tere.</li>
		</ul>

		<p>
When you arrive to Ferenciek tere look for the Statue and the Church. Then start walking towards the bridge and the entrance of our buliding, a big green iron gate, will be behind the bus stop.
		</p>

EOT;

html_end('Directions', null);

?>
