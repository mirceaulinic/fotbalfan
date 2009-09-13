<?php

function callFunction($nameOfFunction,$param){
	$nameOfFunction($param);
}

function returnFunction($nameOfFunction,$param){
	return $nameOfFunction($param);
}

function callFunction2Param($nameOfFunction,$param1,$param2){
	$nameOfFunction($param1,$param2);
}

function colorFromHexToRgb($color){
	switch(strlen($color)){
	case 7:	$color = substr($color,1,6); break; //$color a fost trimis de forma #blabla -> il facem de forma blabla
	case 6: $color = $color; break; //$color a fost trimis de forma blabla -> il las asa cum e
	case 4: $color = substr($color,1,3); $color .= $color; break; //$color a fost trimis de forma #fff -> il fac de forma ffffff
	case 3: $color = $color.$color; break; //$color a fost trimis de forma fff -> il fac de forma ffffff
	default: for($i=strlen($color);$i<=6;$i++) //$color a fost trimis de forma c sau aa -> il fac de forma cFFFFF sau aaFFFF
				$color .= "F";				   //in functie de numarul de caractere care lipsesc
	}
	/*determinarea culorii:
	-> $color este primit ca #blabla (care este reprezentarea
	hexazecimala a culorii dorite din rgb
	-> luam doua cate doua caractere din $color si le transform in baza 10
	-> numerege formate formeaza, in ordine, culoarea de baza, in format rgb();
	*/
	$R = base_convert(substr($color,0,2),16,10); //red		|
	$G = base_convert(substr($color,2,2),16,10); //green	|=> RGB
	$B = base_convert(substr($color,4,2),16,10); //blue		|
	$RGB = array($R,$G,$B);
	unset($R,$G,$B);
	return $RGB;
}

#########################################
# Image class							#
#	contine:							#
#		->getType(private)				#
#		->createImageFrom(public)		#
#		->createImageForText(public)	#
#		->getSizeForThumb(private)		#
#		->createThumb(public)			#
#		->getExtensionFromMime(private)	#
#		->uploadImage(public)			#
#########################################
class Image{
#####################################
	private function getType($name){
		$name = trim(strtolower($name));
		$get = explode(".",$name);
		$type = "";
		$available = array("gif","png","jpg","jpeg");
		if(count($get))
			$extension = $get[count($get)-1];//iau extensia
		$fType = $extension; //fType = function type
		if($extension == "jpg")
			$fType = "jpeg";
		if(!in_array($extension,$available))
			return 0; //daca nu e recunoscut formatul imaginii, returnez 0 (nu este de tip array) si functia se opreste aici
		$ctype = getimagesize($name); //pt det mimetype-ului
		$mimeType = $ctype['mime']; unset($ctype);
		$array = array($extension,$mimeType,$fType);
		return $array;
	}#end getType
	
	public final function createImageFrom($source,$text,$textX,$textY,$textSize,$color,$sourceFont,$save = NULL){
		$source = trim(strtolower($source));
		$types = $this->getType($source); 
		if(!strcmp(gettype($types),"Array")){ //getType() returneaza array daca imaginea este intr-un format recunoscut
			print('Fisierul sursa are format nerecunoscut!');
			exit(); //daca nu este de tip array, imaginea nu e recunoscuta, deci nu mai are rost ceea ce urmeaza
		}
		/*
		$types[0] => extensia
		$types[1] => mimeType
		$types[2] =? tipul imaginii
		*/
		$type = $types[2]; $mimeType = $types[1]; unset($types);
		$nameOfFunction = "imagecreatefrom".$type;//in functie de tipul imaginii functia poate fi
												  // ->imagecreatefromjpeg
												  // ->imagecreatefromgif
												  // ->imagecretatefrompng
		if(extension_loaded('gd')){
			$image = returnFunction($nameOfFunction,$source); //apelez functia dorita, in fct de tipul imaginii
			$RGB = colorFromHexToRgb($color); //prelucrez culoarea
			unset($color,$nameOfFunction); 
			$color = imagecolorallocate($image,$RGB[0],$RGB[1],$RGB[2]); //aloc culoarea
			imagettftext($image,$textSize,0,$textX,$textY,$color,$sourceFont,$text); //asez textul pe imagine
			$nameOfFunction = "image".$type;
			callFunction($nameOfFunction,$image); //generez imaginea, tot in fct de tipul imaginii (jpeg,gif,png)
			if($save){
				$saveAs = $save.".".$type;
				callFunction2Param($nameOfFunction,$image,$saveAs);
			}
			ob_clean();
			header("Content-type: $mimeType"); 
			imagedestroy($image);
		}
		else{
			echo ("Nu e incarcata biblioteca GD!");
		}
	}#end createImageFrom
	
	public final function createImageForText($type,$width,$height,$text,$textX,$textY,$textSize,$sourceFont,$color,$bgColor = NULL,$save = NULL){
		$type = trim(strtolower($type));
		//$type poate fi doar jpeg / gif / png 
		$types = ".".$type;
		$types = $this->getType($types);
		$mimeType = $types[1];
		if(extension_loaded('gd')){
			$image = imagecreate($width,$height);
			if($bgColor){ //daca vreau culoare pe background
				$RBG = colorFromHexToRgb($bgColor); //formatez culoarea de background
				unset($bgColor);
				$bgColor = imagecolorallocate($image,$RGB[0],$RGB[1],$RGB[2]);
				imagerectangle($image,0,0,$width,$height,$bgColor); //desenez backgroundul
				unset($RBG,$bgColor);
			}
			$RGB = colorFromHexToRgb($color);
			unset($color);
			$color = imagecolorallocate($image,$RGB[0],$RGB[1],$RGB[2]);
			imagettftext($image,$textSize,0,$textX,$textY,$color,$sourceFont,$text); //asez textul
			$nameOfFunction = "image".$type;
			callFunction($nameOfFunction,$image);
			header("Content-type: $mimeType");
			ob_clean();
			if($save){ //vad daca salvez
				$saveAs = $save.".".$type;
				callFunction2Param($nameOfFunction,$image,$saveAs);
			}
			imagedestroy($image);
		}
	} #end createImageForText
	
	private function getSizeForThumb($width,$height,$maxWidth,$maxHeight){
		if((!$maxWidth)||(!maxHeight))
			exit();
		if($width > $height){
			if($width > $maxWidth){
				$rW = $width / $maxWidth;
				$height = intval($height / $rW);
				$width = $maxWidth;
				
				if($height > $maxHeight){
					$rH = $height / $maxHeight;
					$width = intval($width / $rH);
					$height = $maxHeight;
				}
			}
			else{
				if($height > $maxHeight){
					$rH = $height / $maxHeight;
					$width = intval($width / $rH);
					$height = $maxHeight;
				}
			}
		}
		else{
			if($height > $maxHeight){
				$rH = $height / $maxHeight;
				$width = intval($width / $rH);
				$height = $maxHeight;
				
				if($width > $maxWidth){
					$rW = $width / $maxWidth;
					$height = intval($height / $rW);
					$width = $maxWidth;
				}
			}
			else{
				if($width > $maxWidth){
					$rW = $width / $maxWidth;
					$height = intval($height / $rW);
					$width = $maxWidth;
				}
			}
		} 
		return array($width,$height);
	}#end getSizeForThumb
	
	public final function createThumb($source,$maxWidth,$maxHeight,$smth = ""){
		$source = trim(strtolower($source));
		$size = getimagesize($source);
		$newSize = self::getSizeForThumb($size[0],$size[1],$maxWidth,$maxHeight);
		$types = self::getType($source);
		if(!strcmp(gettype($types),"Array")){
			print('Fisierul sursa are format nerecunoscut!');
			exit();
		}
		$nameOfFunction = "imagecreatefrom".$types[2];
		$src = returnFunction($nameOfFunction,$source);
		$dest = imagecreatetruecolor($newSize[0],$newSize[1]);
		imagecopyresampled($dest,$src,0,0,0,0,$newSize[0],$newSize[1],$size[0],$size[1]);
		$sv = explode(".",$source); $cSv = count($sv); $save = "";
		for($i=0;$i<$cSv-1;$i++)
			$save .= ".".$sv[$i];
		$save = substr($save,1,strlen($save)-1);
		(string)$samth = "";
		if(strlen($smth))
			$samth =  "_thumb" . $smth;
		$saveAs = $save . $samth . "." . $types[0];
		$nameOfFunction = "image" . $types[2];
		callFunction2Param($nameOfFunction,$dest,$saveAs);
		imagedestroy($src); imagedestroy($dest);
		return $saveAs;
	}#end createThumb
	
	private function getExtensionFromMime($mime){
		$ext = substr($mime,6,strlen($mime)-6); $extension = "";
		echo $ext;
		switch($ext){
			case 'jpg':
			case 'pjpeg':
			case 'jpeg': $extension = "jpeg"; break;
			case 'gif': $extension = "gif"; break;
			case 'png': $extension = "png"; break;
		}
		unset($ext);
		return $extension;
	}
	
	public function uploadImage($index,$folder,$newName,$imageMaxWidth,$imageMaxHeight,$thumbMaxWidth,$thumbMaxHeight,$maxSize = 2000000000){
		/*
		$index -> indexul vectorului bidimensional (matricii) $-FILES. ex: pt $index = "foto"; vom avea $_FILES["foto"}[etc];
		$folder -> folderul in care sa va salva imaginea
		$newName -> numele imaginii salvate  NU contine extensia!
		$imageMaxWidth -> latimea maxima a imaginii care urmeaza a fi salvata
		$imageMaxHeight -> inaltimea maxima a imaginii care urmeaza a fi salvata
		$thumbMaxWidth -> latimea maxima a thumbnail-ului
		$thumbMaxHeight -> inaltimea maxima a thumbnail-ului
		$maxSize -> dimensiumea maxima acceptata; initializata la 2000000000
		*/
		$good = false; $save = array();
		$available = array("image/gif","image/jpg","image/jpeg","image/jpeg","image/pjpeg","image/png");
		if ((in_array($_FILES[$index]["type"],$available)) && ($_FILES[$index]["size"] < $maxSize)){
			if ($_FILES[$index]["error"] == 0){
				$newName .= ".".self::getExtensionFromMime($_FILES[$index]["type"]);
				$finalName = $folder.$newName;
				move_uploaded_file($_FILES[$index]["tmp_name"] , $finalName);
				$save[1] = self::createThumb($finalName,$imageMaxWidth,$imageMaxHeight,"_1");
				$save[2] = self::createThumb($save[1],$thumbMaxWidth,$thumbMaxHeight,"_2");
				$good = true;
			}
		}
		return $save;
	}
}

?>