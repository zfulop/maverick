<?php 

require('includes.php');

$file = $_REQUEST['file'];
$type = $_REQUEST['type'];
$destWidth = $_REQUEST['width'];
$destHeight = $_REQUEST['height'];

if($type == 'ROOM') {
	$file = ROOMS_IMG_DIR . $file;
}

//echo "checking if file exists: $file<br>\n";

if(!file_exists($file)) {
	return;
}

list($width, $height, $type, $attr) = getimagesize($file);
//echo "width: $width, height: $height<br>\n";
if($width/$height > $destWidth/$destHeight) {
	$convHeight = $destHeight;
	$convWidth = round($destHeight * $width / $height);
} else {
	$convWidth = $destWidth;
	$convHeight = round($destWidth * $height / $width);
}
switch($type) {
	case "1": $imorig = imagecreatefromgif($file); break;
	case "2": $imorig = imagecreatefromjpeg($file);break;
	case "3": $imorig = imagecreatefrompng($file); break;
	default:  $imorig = imagecreatefromjpeg($file);
}
$im = imagecreatetruecolor($convWidth, $convHeight);


if(imagecopyresampled($im,$imorig, 0, 0, 0, 0, $convWidth, $convHeight, $width, $height)) {
	imageinterlace($im, true);
	$x = round(($convWidth - $destWidth) / 2);
	$y = round(($convHeight - $destHeight) / 2);
	$destIm = imagecreatetruecolor($destWidth, $destHeight);
	imagecopy($destIm, $im, 0, 0, $x, $y, $destWidth, $destHeight);
	header('Content-type: image/jpg');
	imagejpeg($destIm);
}
imagedestroy($imorig);
imagedestroy($im);
imagedestroy($destIm);


?>
