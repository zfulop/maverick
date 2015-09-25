<?php

require('includes.php');

$location = getLocation();
$lang = getCurrentLanguage();

$link = db_connect($location);

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



$roomTypeId = $_REQUEST['room_type_id'];
$sql = "SELECT ri.*, l.value AS description FROM room_images ri LEFT OUTER JOIN lang_text l ON (l.table_name='room_images' AND l.column_name='description' AND l.row_id=ri.id AND l.lang='$lang') WHERE ri.room_type_id=$roomTypeId order by ri._order";
$result = mysql_query($sql, $link);
$carouselIdx = 1;
while($row = mysql_fetch_assoc($result)) {
	if($carouselIdx > 1) {
		echo ",\n";
	}
	$roomImg = constant('ROOMS_IMG_URL_' . strtoupper($location)) . $row['filename'];
	$width = $row['width'];
	$height = $row['height'];
	$descr = str_replace('"', '\"', $row['description']);
	echo "\t\t\t\t\t\t{image : \"$roomImg\", title : \"$descr\", titleBig : \"\", thumb : \"$roomImg\", url : \"\"}";
	$carouselIdx += 1;
}

mysql_close($link);

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
