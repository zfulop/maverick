<?php

require('../includes.php');


html_start('TheMaverick', 'Photos');

if ($dh = opendir(PHOTOS_DIR)) {
	$cntr = 0;
	$hidden = "";
	while ($file = readdir($dh)) {
		if(is_dir(PHOTOS_DIR . "/" . $file))
			continue;
		if(substr($file, 0, 7) != '_thumb_')
			continue;
		if(substr($file, 0, 10) == '_thumb_5yY')
			continue;

		$bigFile = substr($file, 7);
		$cntr += 1;
		echo "<div class=\"photo\"><img src=\"" . PHOTOS_URL . "/$file\" onmouseover=\"Tip('<img src=\'" . PHOTOS_URL . "/$bigFile\'>', BORDERCOLOR, '#ffffff', BORDERWIDTH, 7, PADDING, 0, SHADOW, true, SHADOWWIDTH, 7, SHADOWCOLOR, '#555555', CENTERMOUSE, true, OFFSETX, 0, CLOSEBTN, true, FIX, [CalcFixX(), CalcFixY()], CLICKCLOSE, true, STICKY, true, DURATION, 5000);\" onmouseout=\"UnTip();\"/></div>\n";

		$hidden .= "<img src=\"" . PHOTOS_URL . "/$bigFile\">";
	}
	closedir($dh);
	echo "<div style=\"clear: both;\"></div>\n";
	echo "<div style=\"display: none;\">$hidden</div>\n";
}


html_end('TheMaverick', 'Photos');

?>
