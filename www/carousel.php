<?php

require('includes.php');

echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
		<link rel="stylesheet" href="/css/slider.min.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="/css/slider-theme.min.css" type="text/css" media="screen" />
		<script type="text/javascript" src="/js/jquery.easing.min.js"></script>
		<script type="text/javascript" src="/js/slider.min.js"></script>
		<script type="text/javascript" src="/js/slider-theme.min.js"></script>
		<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript">
			
			jQuery(function($){
				
				$.supersized({
				
					// Functionality
					slideshow               :   1,			// Slideshow on/off
					autoplay				:	1,			// Slideshow starts playing automatically
					start_slide             :   1,			// Start slide (0 is random)
					stop_loop				:	0,			// Pauses slideshow on last slide
					random					: 	0,			// Randomize slide order (Ignores start slide)
					slide_interval          :   3000,		// Length between transitions
					transition              :   6, 			// 0-None, 1-Fade, 2-Slide Top, 3-Slide Right, 4-Slide Bottom, 5-Slide Left, 6-Carousel Right, 7-Carousel Left
					transition_speed		:	1000,		// Speed of transition
					new_window				:	1,			// Image links open in new window/tab
					pause_hover             :   0,			// Pause slideshow on hover
					keyboard_nav            :   1,			// Keyboard navigation on/off
					performance				:	1,			// 0-Normal, 1-Hybrid speed/quality, 2-Optimizes image quality, 3-Optimizes transition speed // (Only works for Firefox/IE, not Webkit)
					image_protect			:	1,			// Disables image dragging and right click with Javascript
															   
					// Size & Position						   
					min_width		        :   0,			// Min width allowed (in pixels)
					min_height		        :   0,			// Min height allowed (in pixels)
					vertical_center         :   1,			// Vertically center background
					horizontal_center       :   1,			// Horizontally center background
					fit_always				:	0,			// Image will never exceed browser width or height (Ignores min. dimensions)
					fit_portrait         	:   1,			// Portrait images will not exceed browser height
					fit_landscape			:   0,			// Landscape images will not exceed browser width
															   
					// Components							
					slide_links				:	false,	// Individual links for each slide (Options: false, 'num', 'name', 'blank')
					thumb_links				:	4,			// Individual thumb links for each slide
					thumbnail_navigation    :   0,			// Thumbnail navigation
					slides 					:  	[			// Slideshow Images

EOT;

$location = $_REQUEST['page'];
$lang = getCurrentLanguage();

$carouselIdx = 1;
while(defined("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_TITLE")) {
	if($carouselIdx > 1) {
		echo ",\n";
	}
	$carouselTitle = constant("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_TITLE");
	$carouselTitleBig = constant("CAROUSEL_" . strtoupper($location) . '_' . $carouselIdx . "_TITLE_BIG");
	$bgImage = "img/carousel-$location-$carouselIdx.jpg";
	if(file_exists(BASE_DIR . "img/carousel-$location-$carouselIdx-" . $lang . ".jpg")) {
		$bgImage = "img/carousel-$location-$carouselIdx-" . $lang . ".jpg";
	}
	$thumbImage = "img/thumb_carousel-$location-$carouselIdx.jpg";
	if(file_exists(BASE_DIR . "img/thumb_carousel-$location-$carouselIdx-" . $lang . ".jpg")) {
		$thumbImage = "img/thumb_carousel-$location-$carouselIdx-" . $lang . ".jpg";
	}

	$bgImage = BASE_URL . $bgImage;
	$thumbImage = BASE_URL . $thumbImage;

	echo "\t\t\t\t\t\t{image : '$bgImage', title : '$carouselTitle', titleBig : '$carouselTitleBig', thumb : '$thumbImage', url : ''}";
	$carouselIdx += 1;
}

echo <<<EOT
												],
												
					// Theme Options			   
					progress_bar			:	1,			// Timer for each slide							
					mouse_scrub				:	0
					
				});
		    });
		    
		</script>
		
	</head>
	


<body>

	
	<!--End of styles-->

	<!--Thumbnail Navigation-->
	<div id="prevthumb"></div>
	<div id="nextthumb"></div>
	
	<!--Arrow Navigation-->
	<a id="prevslide" class="load-item"></a>
	<a id="nextslide" class="load-item"></a>
	
	<div id="titleSmall"></div>
	<div id="titleBig"></div>
	
	<div id="thumb-back"></div>
	<div id="thumb-forward"></div>
	
	<div id="thumb-tray" class="load-item">
		
	</div>
	
	<!--Time Bar-->
	<div id="progress-back" class="load-item">
		<div id="progress-bar"></div>
	</div>
	
	
	<script>
	$(document).ready(function() {
	
		
		
	
	});
	</script>

</body>
</html>

EOT;




?>
