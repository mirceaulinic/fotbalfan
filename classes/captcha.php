<?php
session_start();
?>
<?php

class Captcha{

	function __construct(){
	
		unset($_SESSION['captcha']);
		
		$minLength = 4;
		$maxLength = 6;
		$caractere = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
		$minFontSize = 20;
		$maxFontSize = 30;
		$maxLiniaritate = 15;
		$maxRotationLeft = 30;
		$maxRotationRight = 30;
		$textPadding = 10;
		$maxTextPadding = 10;
		$minTextPadding = 10;
		$textColor = array('3c5e1c','610303','000000','10297a','808157','1c9089','ff67a7');
		$fonts = array('elektra.ttf','episode1.ttf','lcd2bold.ttf','optimusprinceps.ttf','sams_town.ttf','steinerlight.ttf','turn_table.ttf','myriadpro.otf');
		$fontFolder = "../fonts/";
		$backgrounds = array('bg1.png','bg2.png','bg3.png','bg4.png','bg6.png','bg7.png');
		$backgroundsFolder = "../images/captcha/";
		$imgWidth = 0;
		$imgHeight = 0;

		$minY = 30;
		$maxY = 70;

		$codeLength = rand($minLength, $maxLength);
		
		for($c = 1; $c <= $codeLength; $c++){
        	$char[$c]['char']   = $caractere[rand(0, strlen($caractere)-1)];
        	$char[$c]['color']  = self::hex2decColor($textColor[rand(0, count($textColor)-1 )]);
        	$char[$c]['displacement'] = rand(0, $maxLiniaritate*2) - $maxLiniaritate;
        	$char[$c]['font']   = $fontFolder.$fonts[rand(0, count($fontFiles)-1 )];
        	$char[$c]['size']   = rand($minFontSize, $maxFontSize);
        	$char[$c]['angle']  = rand(0, $maxRotationLeft + $maxRotationRight) - $maxRotationRight;
        	$char[$c]['space']  = rand($minTextPadding, $maxTextPadding);
		
			$properties = self::getCharSize($char[$c]['font'], $char[$c]['char'], $char[$c]['size'], $char[$c]['angle']);
        	$width  = $properties['width'];
        	$height = $properties['height'];
		
			$imgWidth += $width;
        	if($c != $codeLength)
            	    $imgWidth += $char[$c]['space'];
		
			if( ($height + abs($char[$c]['displacement'])) > $imgHeight )
        	        $imgHeight = $height + abs($char[$c]['displacement']);
				
			$_SESSION['captcha'] .= $char[$c]['char'];
		}
		
		$imgWidth  += $textPadding * 2;
		$imgHeight += $textPadding * 2 + $maxLiniaritate;
		
		$img = imagecreatetruecolor($imgWidth, $imgHeight);
		$bg = rand(0, count($backgrounds)-1);
		list($bgWidth, $bgHeight) = getimagesize($backgroundsFolder.$backgrounds[$bg]);
		
		$bgX = rand(0, $bgWidth-$imgWidth);
		$bgY = rand(0, $bgHeight-$imgHeight);
		
		$bgImg = imagecreatefrompng($backgroundsFolder.$backgrounds[$bg]);
		imagecopy($img, $bgImg, 0, 0, $bgX, $bgY, $imgWidth, $imgHeight);
		imagedestroy($bgImg);
		
		$cursor = $textPadding;
		
		for($c = 1; $c <= $codeLength; $c++){
        	$color = imagecolorallocate($img, $char[$c]['color']['red'], $char[$c]['color']['green'], $char[$c]['color']['blue']);
		
			$properties = self::getCharSize($char[$c]['font'], $char[$c]['char'], $char[$c]['size'], $char[$c]['angle']);
        	$width  = $properties['width'];
        	$height = $properties['height'];
        	$bottom = $properties['bottom'];

        	//$y = $char[$c]['displacement'] + $maxVerticalDisplacement + $height - $bottom;
			$y = rand($minY,$maxY);
			imagettftext($img, $char[$c]['size'], $char[$c]['angle'], $cursor, $y, $color, $char[$c]['font'], $char[$c]['char']);
        	$cursor += $width + $char[$c]['space'];
		}
		
		if(function_exists('imagefilter')){
        imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
		}
		
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
		header( 'Content-type: image/png' );
		imagepng($img);
		imagedestroy($img);
	}
	
	private function getCharSize($fontFile, $char, $size, $angle=0){
        if( is_file($fontFile) && strlen($char) == 1 && $size > 0){
                $corners = @imagettfbbox( $size, $angle, $fontFile, $char );
                $left    = ($corners[0]>$corners[6])?$corners[6]:$corners[0];
                $right   = ($corners[2]>$corners[4])?$corners[2]:$corners[4];
                $top     = ($corners[5]>$corners[7])?$corners[7]:$corners[5];
                $bottom  = ($corners[1]>$corners[3])?$corners[1]:$corners[3];
                $width   = $right - $left + 4;
                $height  = abs($top - $bottom) + 4;
                $return  = array(
                                'width'  =>$width,
                                'height' =>$height,
                                'bottom' =>$bottom,
                                'right'  =>$right,
                                'top'    =>$top,
                                'left'   =>$left
                                );
        }
		else{
                $return = false;
        }
        
		return $return;
	}
	
	private function hex2decColor($color){
        if(substr_count($color, ',') == 2){
                list($red, $green, $blue) = array_map('trim', explode(',', $color));
        }
		else{
                $red   = hexdec($color[0].$color[1]);
                $green = hexdec($color[2].$color[3]);
                $blue  = hexdec($color[4].$color[5]);
        }
        return array('red'=>$red, 'green'=>$green, 'blue'=>$blue);
	}
	
}

new Captcha();

?>