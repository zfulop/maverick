<?php

	function saveUploadedImage($formName, $dir, $maxWidth, $maxHeight) {
		if(!isset($_FILES[$formName]))
			return false;

		$fname = $_FILES[$formName]["name"];
		$img_file = getDestinationFile($dir, $fname);
		$tmp_name = $_FILES[$formName]["tmp_name"];
		if(move_uploaded_file($tmp_name, $img_file)) {
			fitImageIntoBox($img_file, $maxWidth, $maxHeight);
			return $img_file;
		} else {
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
		$fname = utf8_decode($fname);
		$fname = strtr($fname,	
			utf8_decode("()!$'?: ,&+-/.ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØŐŰÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöőøùúûüűýÿ"),
			            "______________SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOOUUUUUYsaaaaaaaceeeeiiiionooooooouuuuuyy");

		$fname = utf8_encode($fname);
		$dir .= ( substr($dir,-1) != "/") ? "/" : "";
		if(!file_exists($dir)) {
			mkdir($dir, 0777);
		}
		$counter = 0;
		$file = $dir . '/' . $imgPrefix . $fname . '.' . $ext;
		while(file_exists($file)) {
			$file = $dir . '/' . $imgPrefix . $fname . '_' . $counter . '.' . $ext;
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
/*
			$cmd = IM_CONVERT_APP . " -antialias -sample " . $convWidth . "X" . $convHeight ." " . $img_file . " " . $img_file;
			$output = array();
			$retVal = 0;
			exec($cmd, $output, $retVal);
			if($retVal) {
				trigger_error("Cannot call ImageMagick's convert application: " . implode("\n", $output) . ". Command: $cmd (retval: $retVal)", E_USER_ERROR);
			}
*/
		}
	}

	function createThumbnail($img_file, $maxWidth, $maxHeight) {
		$thumb_file = dirname($img_file) . '/_thumb_' . basename($img_file);
		copy($img_file, $thumb_file);
		fitImageIntoBox($thumb_file, $maxWidth, $maxHeight);
		return $thumb_file;
	}

?>
