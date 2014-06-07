<?php

/*
$initScript = '';
$displayHrs = isset($_REQUEST['hrs']) ? 'block' : 'none';
$initScript .= isset($_REQUEST['hrs']) ? 'reloadImageHRS();' : '';
$displayHostelworld = isset($_REQUEST['hostelworld']) ? 'block' : 'none';
$initScript .= isset($_REQUEST['hostelworld']) ? 'reloadImageHostelworld();' : '';
 */

$initScript = 'reloadImageHRS();';

echo <<<EOT

<html>

<head>
<title>Captcha</title>
<script type="text/javascript" src="/js/prototype.js"></script>
<script language="JavaScript">



function reloadImageHRS() {
	var now = new Date();
	document.getElementById('captcha_hrs').src = 'captcha/hrs.png?' + now.getTime();
	setTimeout('reloadImageHRS()',1000);
}

function reloadImageHostelworld() {
	var now = new Date();
	document.getElementById('captcha_hostelworld').src = 'captcha/hostelworld.gif?' + now.getTime();
	setTimeout('reloadImageHostelworld()',1000);
}

	

function checkClose() {
	if(document.getElementById('hostelworld_form').style.display == 'none' && document.getElementById('hrs_form').style.display == 'none') {
		window.close();
	}
}

function submitHrs() {
	new Ajax.Request('hrs_captcha_save.php', {
		method: 'post',
		parameters: $('hrs_form').serialize(true),
		onSuccess: function(transport) {
			document.getElementById('hrs_form').style.display='none';
			checkClose();
		}
	});
}

function submitHostelworld() {
	new Ajax.Request('hostelworld_captcha_save.php', {
		method: 'post',
		parameters: $('hostelworld_form').serialize(true),
		onSuccess: function(transport) {
			document.getElementById('hostelworld_form').style.display='none';
			checkClose();
		}
	});

}


</script>
</head>

<body onload="$initScript">

<!--
<form id="hostelworld_form" onsubmit="submitHostelworld(); return false;" style="display: $displayHostelworld;">
<fieldset>
Hostelworld captcha image saved: <img id="captcha_hostelworld" src="captcha/hostelworld.gif"><br>
Please enter the text shown on the image: 
<input name="captcha">
<input type="button" value="Send text" onclick="submitHostelworld(); return false;">
</fieldset>
</form>
-->

<form id="hrs_form" onsubmit="submitHrs(); return false;" style="display: $displayHrs;">
<fieldset>
HRS captcha image saved: <img id="captcha_hrs" src="captcha/hrs.png"><br>
Please enter the text shown on the image: 
<input name="captcha">
<input type="button" value="Send text" onclick="submitHrs(); return false;">
</fieldset>
</form>

</body>

</html>

EOT;

?>
