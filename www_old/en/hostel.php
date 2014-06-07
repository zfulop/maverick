<?php

require('../includes.php');


html_start('TheMaverick', 'Hostel');

echo <<<EOT

<p>To see photos of the rooms, click <a href="#photos">here</a></p>

<p>You can find a wide range of rooms in our Hostel from unique dormitory to intimate double room. Don&#226;&#8364;&#8482;t be afraid of bunk beds either, because we have banished them from the Maverick. Please, look around!</p>


<div style="float: right; margin-right: 20px; width: 455px; height: 743px; background-color: #9DB1D6">
<img src="/images/hostel_plan.jpg">
</div>

<br>
<p><strong style="font-size: 12px;">Mr Green- dormitory</strong><br>
We have 4 beds surrounded with green plants in the dormitory, from which 4 are on the loft. If you are in the mood for reading, do not hesitate to use our free library. </p>

<p><strong style="font-size: 12px;">Mss Peach- 4 beds</strong><br>
When you wake up in the room that has 4 beds, you will be greeted by a wounderful fireplace and a soft light shining through the unique mosaic window. Would you like to enjoy this experience?</p>

<p><strong style="font-size: 12px;">Mr and Mss Yellow- double room</strong><br>
Our intimate double bed room with TV is designed for couples primarily. It has a warm and friendly atmosphere. An extra bed can be added upon request.</p>

<p><strong style="font-size: 12px;">Ms Lemon- double room</strong><br>
Our intimate double bed room with TV is designed for couples primarily. It has a warm and friendly atmosphere. An extra bed can be added upon request.</p>

<p><strong style="font-size: 12px;">The  Blue Brothers- 4 beds</strong><br>
This is our other loft room with 2 beds on the ground and 2 more upstairs. Due to its setup it is perfect for a group of friends that would enjoy the vibe of a cozy room with an ancient fireplace.</p>

<p>
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


<div style="clear:both;">
<br><br>

<h2><a name="photos">Photos</a></h2>

EOT;

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME, $link);

$currLang = getCurrentLanguage();

$sql = "SELECT images.*, lang_text.value AS description FROM images LEFT OUTER JOIN lang_text ON (lang_text.table_name='images' AND lang_text.column_name='description' AND row_id=images.id AND lang_text.lang='$currLang') WHERE type='HOSTEL'";
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

		if(strpos($descr, '10') > 0)
			continue;
		if(strpos($descr, '6') > 0)
			continue;
		if(strpos($descr, '5') > 0)
			continue;


		echo "<div class=\"photo\"><img src=\"" . PHOTOS_URL . "/$file\" onmouseover=\"Tip('<img src=\'" . PHOTOS_URL . "/$bigFile\'>', TITLE, '$descr', BORDERCOLOR, '#ffffff', BORDERWIDTH, 7, PADDING, 0, SHADOW, true, SHADOWWIDTH, 7, SHADOWCOLOR, '#555555', CENTERMOUSE, true, OFFSETX, 0, CLOSEBTN, true, FIX, [CalcFixX(), CalcFixY()], CLICKCLOSE, true, STICKY, true, DURATION, 5000);\" onmouseout=\"UnTip();\"/><div class=\"title\">$descr</div></div>\n";

	}
	closedir($dh);
	echo "<div style=\"clear: both;\"></div><div style=\"display: none;\">$hidden</div>\n";
}

mysql_close($link);

html_end('TheMaverick', 'Hostel');

?>
