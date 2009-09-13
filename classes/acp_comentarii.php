<?php

class ACPComentarii{

	public function stergeComentariu($id_comm){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		

			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
			$query = "SELECT `" . $FIELDS['comentarii']['user'] . "` FROM `" . $TABLES['comentarii'] . "` WHERE `" . $FIELDS['comentarii']['id'] . "` = '" . $id_comm . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();		
			unset($query);
			
			$query = "DELETE FROM `" . $TABLES['comentarii'] . "` WHERE `" . $FIELDS['comentarii']['id'] . "` = '" . $id_comm . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
		
			$query = "DELETE FROM `" . $TABLES['noteComentarii'] . "` WHERE `" . $FIELDS['noteComentarii']['id_comm'] . "` = '" . $id_comm . "';";
			$conn->query($query);
			unset($query);
			
			$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['actiuni'] . "` = `" . $FIELDS['users']['actiuni'] . "`-1 WHERE `" . $FIELDS['users']['user'] . "` = '" . $row[0] . "' LIMIT 1;";
			$conn->query($query);
			unset($query,$row);
		}
		die('');
	
	}
	
	public function modificaComentariu($id_comm,$newBody){
		
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->poateModifica()){
		

			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
			$query = "UPDATE `" . $TABLES['comentarii'] . "` SET `" . $FIELDS['comentarii']['body'] . "` = '" . $newBody . "' WHERE `" . $FIELDS['comentarii']['id'] . "` = '" . $id_comm . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	}

}

?>