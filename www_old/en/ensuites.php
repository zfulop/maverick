<?php

require('../includes.php');


html_start('TheMaverick', 'Ensuites', 'Maverick ensuites - double rooms with private bathrooms');

echo <<<EOT


<div style="float: right; margin-right: 20px; width: 500px; height: 430px; background-color: #9DB1D6">
<img src="/images/Plan_ensuite.jpg">
</div>

<br>
To see photos click <a href="#photos">here</a><br><br><br>

<p>
We have a large common area and the reception in our new part. You can also use the kitchen and our computers free. Each of our private rooms  on this floor has a private bathroom and cable TV. All the rooms and the shared lounge with a kitchen have unique innovative decoration and a very comfortable setup. An extra bed can be added upon request.
</p>
 

<h2>Services</h2>
<ul>
	<li>24 hour reception</li>
	<li>Flexible check in/out</li>
	<li>FREE internet, wifi</li>
	<li>FREE coffee and tea all day long</li>
	<li>Towel included</li>
	<li>Linen included</li>
	<li>Laundry</li>
	<li>Hair drier, Iron available</li>
	<li>Fully equiped kitchen</li>
	<li>Elevator</li>
	<li>Smoke free environment</li>
	<li>Airport pick up on request</li>
	<li>Free Library</li>
	<li>Security lockers</li>
	<li>Bicycle hire</li>
	<li>Organized tours, guides on request.</li>
	<li>Public transport counsel.</li>
</ul>


<div style="clear: both;"></div>
<br><br>

<h2><a name="photos">Photos</a></h2>

EOT;


$link = db_connect();

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
