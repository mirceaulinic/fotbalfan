<?php

class Grafic{

	public function __construct($sume,$zile){
	
		if(count($sume) > 10){
		
			$return = self::prelucr($sume,$zile);
			$sume = $return[0];
			$zile = $return[0];
		
		}
		
		self::deseneaza($sume,$zile);
		
	
	}
	
	private function prelucr($sume,$zile){
	
		$count = count($sume);
		$impart = ceil($count / 9);
		for($i = 1;$i < $nr;$i+=2){
			$sum[] = ( $sume[$i] + $sume[$i-1] ) / 2;
			$zil[] = $zile[$i] + (self::getNrZileDintre($zile[$i],$zile[$i+1])) / 2;
		}
		return array($sum,$zil);
	
	}
	
	
	private function getMax($arr){
	
		$arr2 = array();
		$arr2 = $arr; rsort($arr2);
		$arrMax = $arr2[0]; unset($arr2);
		return $arrMax;
	
	}
	
	
	private function deseneaza($sume,$zile){
	
		
		$sumaMax = self::getMax($sume);
		
		$nr = count($sume);

		for($i = 1;$i < $nr;$i++)
			$difZile[$i] = (abs($zile[$i] - $zile[$i-1])) / (24*3600);
			
		$difZileMax = self::getMax($difZile);


		$img = imagecreatefrompng("images/grafic.png");
		$size = getimagesize("images/grafic.png");
		
		$width = $size[0]; $height = $size[1];
		$margine = 45;
		$maxWidth = $width - $margine; $maxHeight = $height - $margine;
		(float)$unitateY = $maxWidth / ( 15/10 * $sumaMax);
		
		(float)$unitateX = $maxHeight / ($nr + 1);

		for($i = 1;$i < $nr;$i++)
			$unitateXX[$i] =  ($unitateX / $difZileMax) * $difZile[$i];
	
		$startX = $margine; $startY = $maxHeight;
		(int)$sfarsitX = $unitateX; (int)$sfarsitY = $maxWidth;
		$culoareVerde = imagecolorallocate($img,91,132,5);
		$culoareRosu = imagecolorallocate($img,248,68,69);
		$culoareAlbastru = imagecolorallocate($img,127,187,235);
		
		$difs = array();
		
		for($i = 1;$i < $nr;$i++){
			(int)$sgn = 1;
			(float)$dif = $sume[$i] - $sume[$i-1];
			if($dif < 0){
				$culoare = $culoareRosu;
				$sgn = -1;
			}
			elseif($dif == 0){
				$culoare = $culoareAlbastru;
			}
			else{
				$sgn = 1;
				$culoare = $culoareVerde;
			}
			$difs[] = $dif;
			$sfarsitX += $unitateXX[$i];
			$sfarsitY += -($unitateY * $dif);
			imageline($img,$startX,$startY,$sfarsitX,$sfarsitY,$culoare); 
			//echo "(".$startX.",".$startY.") -> (".$sfarsitX.",".$sfarsitY.")<br />";
			imagettftext($img,15,40,$sfarsitX-15,$sfarsitY,$culoareAlbastru,"fonts/trebucbd.ttf",$sume[$i]);
			$startX = $sfarsitX;
			$startY = $sfarsitY;
			$elipses['x'][$i] = $startX;
			$elipses['y'][$i] = $startY;

		}



	for($i = 1;$i < $nr;$i++){
	
		imageline($img,$elipses['x'][$i],$elipses['y'][$i],$elipses['x'][$i],$height-$margine-5,$culoareAlbastru);
		imagefilledellipse($img,$elipses['x'][$i],$elipses['y'][$i],10,10,$culoareAlbastru);
		imagettftext($img,15,90,$elipses['x'][$i],$height-$margine-5,$culoareAlbastru,"fonts/trebucbd.ttf",date('d m Y',$zile[$i]));
	}

	(int)$sum = 0;
	(int)$nr = count($difs);
	foreach($difs as $dif)
		$sum += $dif;
	$average = $sum/$nr;
	imageline($img,$startX,$startY,$sfarsitX+$unitateX,$startY-($unitateY * $average),$culoareAlbastru);
	imagedashedline($img,$sfarsitX+$unitateX,$startY-($unitateY * $average),$sfarsitX+$unitateX,$height-$margine-5,$culoareAlbastru);
	imagettftext($img,15,90,$sfarsitX+$unitateX,$height-$margine-5,$culoareAlbastru,"fonts/myriadpro.otf","Suma posibila");
	imagefilledellipse($img,$sfarsitX+$unitateX,$startY-($unitateY * $average),10,10,$culoareAlbastru);
	imagettftext($img,15,40,$sfarsitX+$unitateX-15,$startY-($unitateY * $average),$culoareAlbastru,"fonts/trebucbd.ttf",substr($sume[$nr]+$average,0,6));

	imagepng($img);
	imagedestroy($img);
	
	}


}

?>