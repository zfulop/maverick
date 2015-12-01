<?php

require("includes.php");
require("site_text_common.php");

if(!checkLogin(SITE_MGMT)) {
	return;
}


$langTexts = loadConstants();

html_start("Website Texts");


echo <<<EOT

<table border="1">
	<tr>
		<td>&nbsp;</td>

EOT;

foreach(getLanguages() as $langCode => $langName) {
	echo "		<th>$langName</th>\n";
}
echo "	</tr>\n";

$cntr = 0;
foreach($langTexts as $textKey => $texts) {
	echo "	<form id=\"" . $textKey . "\" action=\"save_site_text.php\" accept-charset=\"utf-8\" method=\"POST\">\n";
	echo "	<tr>\n";
	echo "		<td><b>$textKey</b></td>\n";
	foreach(getLanguages() as $langCode => $langName) {
		$text = '';
		$style = '';
		if(isset($texts[$langCode]) and (strlen($texts[$langCode]) > 0)) {
			$text = getTextAsInputValue($texts[$langCode]);
		} else {
			$style = 'border: red solid 1px;';
		}
		echo "		<td><textarea style=\"$style\" name=\"$langCode" . '-' . $textKey . "\">$text</textarea></td>\n";
	}
	echo "		<td><input type=\"submit\" value=\"Save text\"></td>\n";
	echo "	</tr>\n";
	echo "	</form>\n";
	$cntr += 1;
}

echo <<<EOT
</table><br>

$cntr entries<br>


EOT;


html_end();


?>
