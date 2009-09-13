<?php

class ACPFacils{


	function seteazaLastNNews($newNr){
		
		if($newNr <= 1)
			die('Hacking attempt...');
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "UPDATE `" . $TABLES['facils'] . "` SET `" . $FIELDS['facils']['lastNNews'] . "` = '" . $newNr . "' WHERE `" . $FIELDS['facils']['id'] . "` = '1' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	
	}

}


?>