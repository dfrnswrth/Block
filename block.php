<?
	error_reporting(E_ALL);
	ini_set("display_errors","on");

	// Set memory_limit to appropriate size
	ini_set("memory_limit","64M"); // Good for ~ 7.3MP (2700^2)

	$path = array();
	$path = $_SERVER['REQUEST_URI'];
	$path = explode('/', $path);
	$location = $_SERVER["SCRIPT_NAME"];
	$location = explode('/', $location);	
	$size = sizeof($location);
	
	// We only care about the URL after this script
	for ($i=0; $i<$size; $i++) {
		if ($path[$i] == $location[$i]) {
			unset($path[$i]);
		}
	}
	$path = array_values($path);	
	
	//	Convert hex colors to RGB
	//	http://css-tricks.com/snippets/php/convert-hex-to-rgb/
	function hex2rgb( $colour ) {
		if ($colour[0] == '#') {
			$colour = substr($colour,1);
		}
		if (strlen($colour)==6) {
			list($r, $g, $b) = array($colour[0].$colour[1], $colour[2].$colour[3], $colour[4].$colour[5]);
		} elseif ( strlen( $colour ) == 3 ) {
			list($r, $g, $b) = array($colour[0].$colour[0], $colour[1].$colour[1], $colour[2].$colour[2]);
		} else {
			return false;
		}
		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);
		return array('red'=>$r,'green'=>$g,'blue'=>$b);
	}
	
	//	If only one dimension given, make a square
	if (!$path[1]) {
		$path[1] = $path[0];
	}
	//	Set some defaults, overwrite with commands
	$defaultBg = "444444";
	$defaultText = "bbbbbb";
	$defaults = array("50", "50", $defaultBg, $defaultText);
	$path = array_replace($defaults, $path);
	
	$cacheDir = "_cache/";
	
	if ($path[2] != $defaultBg && $path[3] != $defaultBg) {
		$filename = $path[0] . "x" . $path[1] . "_" . $path[2] . "_" . $path[3] . ".png";
	} else {
		$filename = $path[0] . "x" . $path[1] . ".png";
	}
	
	$file = $cacheDir . $filename;
	
	if (!file_exists($file)) {
		//	Get our values from $path
		$width = $path[0];
		$height = $path[1];
		$bgColor = hex2rgb($path[2]);
		$tColor = hex2rgb($path[3]);

		//	Set the text
		$text = $width . " x " . $height;
		$font = "_fonts/Vera.ttf";
		
		//	Make sure the text is inside the image dimensions
		$shortSide = min($width,$height);
		$fontSize = round($shortSide / 9);
	
		//	Get size of rendered text
		$textVolume = imagettfbbox($fontSize,0,$font,$text);
		$posx = $textVolume[0] + ($width / 2) - ($textVolume[4] / 2);
		$posy = $textVolume[1] + ($height / 2) - ($textVolume[5] / 2);
	
		//	Let's make an image
		$image = @imagecreatetruecolor($width, $height)
			or die('Oops!');
		$image_color = imagecolorallocate($image, $bgColor["red"], $bgColor["green"], $bgColor["blue"]);
		imagefill($image, 0, 0, $image_color); 
		$text_color = imagecolorallocate($image, $tColor["red"], $tColor["green"], $tColor["blue"]);
		imagettftext($image, $fontSize, 0, $posx, $posy, $text_color, $font, $text);
		
		//	Write it to file
		imagepng($image, $file);
		imagedestroy($image);
	}
	
	//	get image from file
	$data = file_get_contents($file);

	//	Send it
	header ('Content-Type: image/png');
	die($data);
?>