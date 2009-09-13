<?php

class Tag{

	public function addTag($tag,$obj,$id_obj){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		$query = "SELECT `" . $FIELDS['tags']['id'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['tag'] . "` = '" . $tag . "' AND `" . $FIELDS['tags']['id_obj'] . "` = '" . $id_obj . "' AND `" . $FIELDS['tags']['obj'] . "` = '" . $obj . "' LIMIT 1;";
		$result = $conn->query($query);
		if($result->num_rows){
			$row = $result->fetch_row(); $id = $row[0];
			unset($query,$result,$row);
			$query = "UPDATE `" . $TABLES['tags'] . "` SET `" . $FIELDS['tags']['hits'] . "` = `" . $FIELDS['tags']['hits'] . "`+1 WHERE `" . $FIELDS['tags']['id'] . "` = '" . $id . "' LIMIT 1 ;";
			unset($id);
			$conn->query($query);
			unset($query);
			
			$query = "UPDATE `" . $TABLES['allTags'] . "` SET `" . $FIELDS['allTags']['hits'] . "` = `" . $FIELDS['allTags']['hits'] . "`+1 WHERE `" . $FIELDS['allTags']['tag'] . "` = '" . $tag . "' LIMIT 1 ;";
			$conn->query($query);
			unset($query);
		}
		else{
			unset($query,$result);
			$query = "INSERT INTO `" . $TABLES['tags'] . "` (`" . $FIELDS['tags']['id'] . "`,`" . $FIELDS['tags']['tag'] . "`,`" . $FIELDS['tags']['obj'] . "`,`" . $FIELDS['tags']['id_obj'] . "`) VALUES (NULL,'" . $tag . "','" . $obj . "','" . $id_obj . "');";
			$conn->query($query);
			unset($query);
			
			$query = "INSERT INTO `" . $TABLES['allTags'] . "` (`" . $FIELDS['allTags']['tag'] . "`) VALUES ('" . $tag . "');";
			$conn->query($query);
		}
		return 1;
	}
	
	public function addViewTag($tag,$id_user,$obj){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		$query = "SELECT `" . $FIELDS['userTags']['id'] . "` FROM `" . $TABLES['userTags'] . "` WHERE `" . $FIELDS['userTags']['tag'] . "` = '" . $tag . "' AND `" . $FIELDS['userTags']['obj'] . "` = '" . $obj . "' AND `" . $FIELDS['userTags']['user'] . "` = '" . $id_user . "' LIMIT 1;";
		$result = $conn->query($query);
		if($result->num_rows){
			$row = $result->fetch_row(); $id = $row[0];
			unset($query,$result);
			$query = "UPDATE `" . $TABLES['userTags'] . "` SET `" . $FIELDS['userTags']['hits'] . "` = `" . $FIELDS['userTags']['hits'] . "`+1 WHERE `" . $FIELDS['userTags']['id'] . "` = '" . $id . "' LIMIT 1 ;";
		
			$conn->query($query);
		}
		else{
			unset($query,$result);
			$query = "INSERT INTO `" . $TABLES['userTags'] . "` (`" . $FIELDS['userTags']['id'] . "`,`" . $FIELDS['userTags']['tag'] . "`,`" . $FIELDS['userTags']['obj'] . "`,`" . $FIELDS['userTags']['user'] . "`) VALUES (NULL,'" . $tag . "','" . $obj . "','" . $id_user . "');";
			$conn->query($query);
		}
		return 1;
	}
	
	public function addSearchTag($tag){
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['allTags']['hits'] . "` FROM `" . $TABLES['allTags'] . "` WHERE `" . $FIELDS['allTags']['tag'] . "` = '" . $tag . "' LIMIT 1;";
		$result = $conn->query($query);
		if($result->num_rows){
			unset($query);
			$query = "UPDATE `" . $TABLES['allTags'] . "` SET `" . $FIELDS['allTags']['hits'] . "` = `" . $FIELDS['allTags']['hits'] . "`+1 WHERE `" . $FIELDS['allTags']['tag'] . "` = '" . $tag . "' LIMIT 1;";
			$conn->query($query);
		}
		else{
			$query = "INSERT INTO `" . $TABLES['allTags'] . "` (`" . $FIELDS['allTags']['tag'] . "`) VALUES ('" . $tag . "');";
			$conn->query($query);
		}
		return 1;
	}
	
	public function primeleTaguri($obj,$id_obj){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$return = array();
		
		$query = "SELECT `" . $FIELDS['tags']['tag'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['obj'] . "` = '" . $obj . "' AND `" . $FIELDS['tags']['id_obj'] . "` = '" . $id_obj . "' ORDER BY `" . $FIELDS['tags']['hits'] . "` DESC LIMIT 5;";
		$result = $conn->query($query);
		
		if($result->num_rows){
			while($row = $result->fetch_row())
				$return[count($return)] = $row[0];
		}
		
		 unset($query,$result,$row);
		
		return $return;
		
	}
	
}

class SearchByTag{
	
	function __construct($tag,$pg = 1,$unde = NULL){
		if(is_null($unde)){
			$nrStiri = self::searchByTagGetNr($tag,"stiri");
			$nrFoto = self::searchByTagGetNr($tag,"foto");
			if($nrStiri)
				echo '<a href="stiri.php?tag=' . $tag . '">' . $nrStiri . ' rezultate</a> in stiri<br />' . "\n";
			if($nrFoto)
				echo '<a href="foto.php?tag=' . $tag . '">' . $nrFoto . ' rezultate</a> in foto<br />' . "\n";
		}
		else{
			if($unde == "stiri"){
				include_once "classes/stiri.php";
				$limitaInf = ( $pg - 1 ) * 5;
				Stiri::showNewsByTag($tag,"ASC",$limitaInf,false);
			}
			elseif($unde == "foto"){
				include_once "classes/foto.php";
				$limitaInf = ( $pg - 1 ) * 30;
				Foto::showFotosByTag($tag,$limitaInf,false);
			}
			else
				echo 'Nu exista pagina pe care o cautati!';
		}
	}
	
	public function searchByTagGetNr($tag,$unde){
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['tags']['id'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['tag'] . "` = '" . $tag . "' AND `" . $FIELDS['tags']['obj'] . "` = '" . $unde . "'";
		$result = $conn->query($query);
		return $result->num_rows;
	}
}

class TagCloud{
	
	function __construct($where = NULL,$nr = NULL){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		(string)$unde = "";
		
		if(isset($where)){
			if($where == "stiri")
				$unde = "stiri.php?";
			elseif($where == "foto")
				$unde = "foto.php?";
			else
				$unde = "facilities.php?ce=cauta-dupa-tag&amp;";
		}
		else
			$unde = "facilities.php?ce=cauta-dupa-tag&amp;";
			
		(string)$limit = " LIMIT 15";
		if(!is_null($nr))
			$limit = "";
		
		$query = "SELECT * FROM `" . $TABLES['allTags'] . "` ORDER BY `" . $FIELDS['allTags']['hits'] . "` DESC" . $limit . ";";
		$result = $conn->query($query);
		(int)$i = (int)$best = 0; (int)$max = 30;
		$class = 0;
		while($row = $result->fetch_row()){
			if($i == 0)
				$best = $row[1];
			$class = self::aprox( $row[1] / $best * $max);
			if($class < 7)
				$class = 7;
			echo '<a href="' . $unde . 'tag=' . str_replace(" ","+",$row[0]) . '" class="tagSize' . $class . '">' . $row[0] . '</a>' . "\n";	
			
			$i++;
		}	
	}
	
	private function aprox($numar){
		(string)$numar; $part = array();
		$part = explode(".",$numar);
		$part1 = @$part[1];
		if((int)$part1[0] < 5)
			return (int)$part[0];
		else
			return ((int)$part[0] + 1);
	}
	
	public function afiseazaTaguri($id_obj,$obj,$limit){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		(string)$unde = "";
		if($obj == "stiri")
			$unde = "stiri.php?";
		elseif($obj == "foto")
			$unde = "foto.php?";
		else
			$unde = "facilities.php?ce=cauta-dupa-tag&amp;";
		

		$query = "SELECT `" . $FIELDS['tags']['tag'] . "`,`" . $FIELDS['tags']['hits'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['id_obj'] . "` = '" . $id_obj . "' AND `" . $FIELDS['tags']['obj'] . "` = '" . $obj . "' ORDER BY `" . $FIELDS['tags']['hits'] . "` DESC LIMIT " . $limit . ";";
		$result = $conn->query($query);
		
		(int)$i = 0;
		$max = 30;
		while($row = $result->fetch_row()){
			if($i == 0)
				$best = $row[1];
			$class = self::aprox( $row[1] / $best * $max);
			if($class < 7)
				$class = 7;
			echo '<a href="' . $unde . 'tag=' . str_replace(" ","+",$row[0]) . '&amp;unde=' . $obj . '" class="tagSize' . $class . '">' . $row[0] . '</a>' . "\n";	$i++;

		}
	
	}
}

?>