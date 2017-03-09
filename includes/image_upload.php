<?php

	function saveUploadedImage($formName, $dir, $maxWidth, $maxHeight, $fitIntoBox = true) {
		if(!isset($_FILES[$formName])) {
			set_error("No image was sent in parameter: $formName");
			return false;
		}

		$fname = $_FILES[$formName]["name"];
		$img_file = getDestinationFile($dir, $fname);
		$tmp_name = $_FILES[$formName]["tmp_name"];
		if(move_uploaded_file($tmp_name, $img_file)) {
			chmod($img_file, 0644);
			set_message("Image copied to: $img_file");
			if($fitIntoBox) {
				fitImageIntoBox($img_file, $maxWidth, $maxHeight);
			} else {
				makeImageThisSize($img_file, $maxWidth, $maxHeight);
			}
			return $img_file;
		} else {
			set_error("Cannot move uploaded image to $img_file");
			return false;
		}
	}

	function getDestinationFile($dir, $fname, $imgPrefix = '') {
		$pntPos = strrpos($fname, ".");
		$ext = "";
		if($pntPos > 0) {
			$ext = substr($fname, $pntPos + 1);
			$fname = substr($fname, 0, $pntPos);
		}
		$fname = stripAccents($fname);
		$dir .= ( substr($dir,-1) != "/") ? "/" : "";
		if(!file_exists($dir)) {
			mkdir($dir, 0777);
		}
		$counter = 0;
		$file = $dir . $imgPrefix . $fname . '.' . $ext;
		while(file_exists($file)) {
			$file = $dir . $imgPrefix . $fname . '_' . $counter . '.' . $ext;
			$counter += 1;
		}

		return $file;
	}


	function fitImageIntoBox($img_file, $maxWidth, $maxHeight) {
		list($width, $height, $type, $attr) = getimagesize($img_file);
		if(($width > $maxWidth) or ($height > $maxHeight)) {
			if($width/$height > $maxWidth/$maxHeight) {
				$convWidth = $maxWidth;
				$convHeight = round($maxWidth * $height / $width);
			} else {
				$convHeight = $maxHeight;
				$convWidth = round($maxHeight * $width / $height);
			}
			switch($type) {
				case "1": $imorig = imagecreatefromgif($img_file); break;
				case "2": $imorig = imagecreatefromjpeg($img_file);break;
				case "3": $imorig = imagecreatefrompng($img_file); break;
				default:  $imorig = imagecreatefromjpeg($img_file);
			}
			$im = imagecreatetruecolor($convWidth, $convHeight);
			if(imagecopyresampled($im,$imorig, 0, 0, 0, 0, $convWidth, $convHeight, $width, $height)) {
				imageinterlace($im, true);
				imagejpeg($im, $img_file);
			}
			imagedestroy($im);
		}
	}

	function makeImageThisSize($file, $destWidth, $destHeight) {
		list($width, $height, $type, $attr) = getimagesize($file);
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
			imageinterlace($destIm, true);
			switch($type) {
				case "1": imagegif($destIm, $file); break;
				case "2": imagejpeg($destIm, $file); break;
				case "3": imagepng($destIm, $file); break;
				default:  imagejpeg($destIm, $file);
			}
		}
		imagedestroy($imorig);
		imagedestroy($im);
		imagedestroy($destIm);
	}

	function createThumbnail($prefix, $img_file, $maxWidth, $maxHeight) {
		$thumb_file = dirname($img_file) . '/' . $prefix . basename($img_file);
		copy($img_file, $thumb_file);
		fitImageIntoBox($thumb_file, $maxWidth, $maxHeight);
		return $thumb_file;
	}

?>
