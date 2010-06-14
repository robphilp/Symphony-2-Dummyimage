<?php

	Class Extension_Dummyimage extends Extension{
	
		public function about() {
			return array('name' => 'Dummy Image Generator',
						 'version' => '0.1',
						 'release-date' => '2010-01-26',
						 'author' => array('name' => 'Robert Philp',
										   'website' => 'http://robertphilp.com',
										   'email' => ''),
							'description'   => 'Generates dummy images for placeholding, prototyping etc'
				 		);
		}

		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendPrePageResolve',
					'callback'	=> 'frontendPrePageResolve'
				)			
			);
		}

		public function frontendPrePageResolve($context) {

			$slices = explode("/", $context['page']);

			if(strtolower($slices[1]) == "dummyimage") {
				// Dynamic Dummy Image Generator - DummyImage.com
				$x = $slices[2]; //GET the query string from the URL. x would = 600x400 if the url was http://dummyimage.com/600x400
				if (strlen($x) > 9) { //Limit the size of the image by limiting the length of the query string
					die("Too big of an image!"); //If it is too big we kill the script.
				}
				list($width, $height) = split('x', $x); // Split X up at the 'x' character so we have our image width and height.

				$angle = 0; //I don't use this but if you wanted to angle your text you would change it here.
				$fontsize = $width/16; //I came up with 16 to scale the text size based on the width.
				if($fontsize<= 5) { //I do set a minimum font size so it is still sort of legible at very small image sizes.
					$fontsize = 5;
				}

				$font = DOCROOT . '/extensions/dummyimage/assets/arial.ttf';// you want to use a different font simply upload the true type font (.ttf) file to the same directory as this PHP file and set the $font variable to the font file name. 
				
				// Background Colour
				$backgroundRGB = array('r' => 204, 'g' => 204, 'b' => 204);
				// get the background color from url if exists
				if(isset($slices[3]) && $slices[3] != "") {
					if ( $slices[3] == "random" || $slices[3] == "Random" || $slices[3] == "RANDOM" ||$slices[3] == "r" ||$slices[3] == "R" ) 
						$backgroundRGB =  array('r' => rand(0, 255), 'g' => rand(0, 255), 'b' => rand(0, 255));
					else
						$backgroundRGB = $this->rgb2hex2rgb($slices[3]);
				}
				
				// Text Colour
				$textRGB = array('r' => 0, 'g' => 0, 'b' => 0);
				// get the background color from url if exists
				if(isset($slices[4]) && $slices[4] != "") {
					if ( $slices[4] == "random" || $slices[4] == "Random" || $slices[4] == "RANDOM" ||$slices[4] == "r" ||$slices[4] == "R" ) 
						$textRGB =  array('r' => rand(0, 255), 'g' => rand(0, 255), 'b' => rand(0, 255));
					else
						$textRGB = $this->rgb2hex2rgb($slices[4]);
				}  

				$im = imageCreate($width,$height); //Create an image.
				$backgroundColor = imageColorAllocate($im, $backgroundRGB['r'], $backgroundRGB['g'], $backgroundRGB['b']); //Set the color gray for the background color. Hex value = #CCCCCC
				$textColor = imageColorAllocate($im, $textRGB['r'], $textRGB['g'], $textRGB['b']); //Set the black color for the text

				$text = $width." X ".$height; //This is the text string that will go right in the middle of the gray rectangle.

				$textBox = $this->imagettfbbox_t($fontsize, $angle, $font, $text); //Pass these variable to a function that calculates the position of the bounding box.
				$textWidth = $textBox[4] - $textBox[1]; //Calculates the width of the text box by subtracting the Upper Right X position with the Lower Left X position.
				$textHeight = abs($textBox[7])+abs($textBox[1]); //Calculates the height of the text box by adding the absolute value of the Upper Left Y position with the Lower Left Y position.

				$textX = ($width - $textWidth)/2; //Determines where to set the X position of the text box so it is centered.
				$textY = ($height - $textHeight)/2 + $textHeight; //Determines where to set the Y position of the text box so it is centered.

				imageFilledRectangle($im, 0, 0, $width, $height, $backgroundColor); //Creates the gray rectangle http://us2.php.net/manual/en/function.imagefilledrectangle.php
				imagettftext($im, $fontsize, $angle, $textX, $textY, $textColor, $font, $text);	 //Create and positions the text http://us2.php.net/manual/en/function.imagettftext.php
				header('Content-type: image/gif'); //Set the header so the browser can interpret it as an image and not a bunch of weird text.
	
				imagegif($im); //Create the final GIF image
				imageDestroy($im);//Destroy the image to free memory.
				exit;
	
			}
		}

		function imagettfbbox_t($size, $angle, $fontfile, $text){ //Ruquay K Calloway http://ruquay.com/sandbox/imagettf/ made a better function to find the coordinates of the text bounding box so I used it.
    		// compute size with a zero angle
    		$coords = imagettfbbox($size, 0, $fontfile, $text);
    		// convert angle to radians
    		$a = deg2rad($angle);
    		// compute some usefull values
    		$ca = cos($a);
    		$sa = sin($a);
    		$ret = array();
    		// perform transformations
    		for($i = 0; $i < 7; $i += 2){
        		$ret[$i] = round($coords[$i] * $ca + $coords[$i+1] * $sa);
        		$ret[$i+1] = round($coords[$i+1] * $ca - $coords[$i] * $sa);
    		}
    		return $ret;
		}
		
		
		
		// Convert HEX to RGB Array http://www.php.net/manual/en/function.hexdec.php#93835
		function rgb2hex2rgb($c){ 
		   if(!$c) return false; 
		   $c = trim($c); 
		   $out = false; 
		  if(preg_match("/^[0-9ABCDEFabcdef\#]+$/i", $c)){ 
		      $c = str_replace('#','', $c); 
		      $l = strlen($c) == 3 ? 1 : (strlen($c) == 6 ? 2 : false); 
		
		      if($l){ 
		         unset($out); 
		         $out[0] = $out['r'] = $out['red'] = hexdec(substr($c, 0,1*$l)); 
		         $out[1] = $out['g'] = $out['green'] = hexdec(substr($c, 1*$l,1*$l)); 
		         $out[2] = $out['b'] = $out['blue'] = hexdec(substr($c, 2*$l,1*$l)); 
		      }else $out = false; 
		              
		   }elseif (preg_match("/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $c)){ 
		      $spr = str_replace(array(',',' ','.'), ':', $c); 
		      $e = explode(":", $spr); 
		      if(count($e) != 3) return false; 
		         $out = '#'; 
		         for($i = 0; $i<3; $i++) 
		            $e[$i] = dechex(($e[$i] <= 0)?0:(($e[$i] >= 255)?255:$e[$i])); 
		              
		         for($i = 0; $i<3; $i++) 
		            $out .= ((strlen($e[$i]) < 2)?'0':'').$e[$i]; 
		                  
		         $out = strtoupper($out); 
		   }else $out = false; 
		          
		   return $out; 
		} 
			
	}

?>
