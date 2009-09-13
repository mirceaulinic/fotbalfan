<?php

class Bilete{

	public function __construct(){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
	
		$cookie = $_COOKIE['user'];
		$ide= explode("|",$cookie);
		$id = $ide[1]; unset($ide,$cookie);
		
		$query = "SELECT `" . $FIELDS['users']['idLastBilet'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['id'] . "` = '" . $id . "' LIMIT 1;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$nrBilete = $row[0];
		unset($row,$result,$query);
		
		include_once "pariuri.php";
		
		echo '<ul class="bilete">' . "\n";
		
		for((int)$i = $nrBilete;$i >= 1;$i--){
			
			Pariuri::afiseazaBilet($i,$id,false,time());
		} 
		
		echo '</ul>' . "\n";
	}
	
}


?>