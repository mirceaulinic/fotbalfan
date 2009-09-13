<?php

class Propuneri{
   ########
   ##adaugare stiri
   ########
   
	
	public function adaugaPropunereStire($proposer,$titlu,$continut,$img,$thumb){
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "INSERT INTO `" . $TABLES['propuneri'] . "` (`" . $FIELDS['propuneri']['id'] . "`,`" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['titlu'] . "`,`" . $FIELDS['propuneri']['continut'] . "`,`" . $FIELDS['propuneri']['img'] . "`,`" . $FIELDS['propuneri']['thumb'] . "`,`" . $FIELDS['propuneri']['type'] . "`,`" . $FIELDS['propuneri']['unde'] . "`,`" . $FIELDS['propuneri']['data'] . "`) VALUES (NULL,'" . $proposer . "','" . $titlu . "','" . $continut . "','" . $img . "','" . $thumb . "','adauga','stiri','" . time() . "');";
		$conn->query($query);
		unset($query);
	}
	
	private function afiseazaErori($codeTitlu,$codeContinut){
		if($codeTitlu)
			echo '<div class="error">Completati corect campul pentru titlu!</div>';
		if($codeContinut)
			echo '<div class="error">Completati corect campul pentru continut!</div>';
	}
	
	public function amErori(){
		return self::$am;
	}
	
	########
   	##stergere stiri
   	########
   
   	public function hasAlreadyProposedIt($id_obj,$user){
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['propuneri']['id'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['id_obj'] . "` = '" . $id_obj . "' AND `" . $FIELDS['propuneri']['prop'] . "` = '" . $user . "' AND `" . $FIELDS['propuneri']['type'] . "` = 'stergere' AND `" . $FIELDS['propuneri']['unde'] . "` = 'stiri' LIMIT 1;";
		$result = $conn->query($query);
		if($result->num_rows)
			return 1;
		else
			return 0;
	}
	
	public function propuneStergereStire($user,$id_obj){
		if( !(self::hasAlreadyProposedIt($id_obj,$user)) ){
			include_once "classes/config.php";
			global $FIELDS,$TABLES,$conn;
			
			$query = "INSERT INTO `" . $TABLES['propuneri'] . "` (`" . $FIELDS['propuneri']['id'] . "`,`" . $FIELDS['propuneri']['id_obj'] . "`,`" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['type'] . "`,`" . $FIELDS['propuneri']['unde'] . "`,`" . $FIELDS['propuneri']['data'] . "`) VALUES (NULL,'" . $id_obj . "','" . $user . "','stergere','stiri','" . time() . "');";
			$conn->query($query);
			
		}
		else
			return 0;
	
	}
	
	
	public function propuneNewsletter($user,$titlu,$body){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "INSERT INTO `" . $TABLES['propuneri'] . "` (`" . $FIELDS['propuneri']['id'] . "`,`" . $FIELDS['propuneri']['titlu'] . "`,`" . $FIELDS['propuneri']['continut'] . "`,`" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['unde'] . "`,`" . $FIELDS['propuneri']['data'] . "`) VALUES (NULL,'" . $titlu . "','" . $body . "','" . $user . "','newsletter','" . time() . "');";
		$conn->query($query);
	
	}
	
	
	public function propuneAdaugareGalerie($titlu,$cine){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$time = time();
		
		$query = "INSERT INTO `" . $TABLES['propuneri'] . "` (`" . $FIELDS['propuneri']['id'] . "`,`" . $FIELDS['propuneri']['titlu'] . "`,`" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['type'] . "`,`" . $FIELDS['propuneri']['unde'] . "`,`" . $FIELDS['propuneri']['data'] . "`) VALUES (NULL,'" . $titlu . "','" . $cine . "','adauga','galerie','" . $time . "');";
		$conn->query($query);
		unset($query);
		
		$query = "SELECT `" . $FIELDS['propuneri']['id'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['prop'] . "` = '" . $cine . "' AND `" . $FIELDS['propuneri']['data'] . "` = '" . $time . "' LIMIT 1;";
		$row = $conn->query($query)->fetch_row();
		$id = $row[0];
		unset($query,$row);
		return $id;
		
	}
	
	public function propuneStergereGalerie($id,$cine){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
	
		$query = "INSERT INTO `" . $TABLES['propuneri'] . "` (`" . $FIELDS['propuneri']['id'] . "`,`" . $FIELDS['propuneri']['id_obj'] . "`,`" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['type'] . "`,`" . $FIELDS['propuneri']['unde'] . "`,`" . $FIELDS['propuneri']['data'] . "`) VALUES (NULL,'" . $id . "','" . $cine . "','stergere','galerie','" . time() . "');";
		$conn->query($query);
		unset($query);
	
	}
	
	public function propuneAdaugareFoto($unde,$cine,$index){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		include_once "classes/image.php";
		$saves = Image::uploadImage($index,"images/foto/propuneri/",substr(md5(rand()),0,10),540,377,156,147);
		
		$query = "INSERT INTO `" . $TABLES['propuneri'] . "` (`" . $FIELDS['propuneri']['id'] . "`,`" . $FIELDS['propuneri']['id_obj'] . "`,`" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['type'] . "`,`" . $FIELDS['propuneri']['unde'] . "`,`" . $FIELDS['propuneri']['data'] . "`,`" . $FIELDS['propuneri']['img'] . "`,`" . $FIELDS['propuneri']['thumb'] . "`) VALUES (NULL,'" . $unde . "','" . $cine . "','adaugare','foto','" . time() . "','" . $saves[1] . "','" . $saves[2] . "');";
		$conn->query($query);
		unset($query);
		
	}
	
	public function propuneStergereFoto($id,$cine){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "INSERT INTO `" . $TABLES['propuneri'] . "` (`" . $FIELDS['propuneri']['id'] . "`,`" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['type'] . "`,`" . $FIELDS['propuneri']['unde'] . "`,`" . $FIELDS['propuneri']['id_obj'] . "`,`" . $FIELDS['propuneri']['data'] . "`) VALUES (NULL,'" . $cine . "','stergere','foto_foto','" . $id . "','" . time() . "');";
		$conn->query($query);
		unset($query);
	
	}
	
}

?>