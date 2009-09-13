<?php


class UCP{

	function modificaProfil($unde,$new){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		
		if($user->esteLogat()){
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			if($unde == 'parola')
				$new = encode(getUserFromCookie(),$new);
			
			$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users'][$unde] . "` = '" . $new . "' WHERE `" . $FIELDS['users']['id'] . "` = '" . getIdFromCookie() . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
		}
	
	}
	
	
	function loggedUsers(){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT `" . $FIELDS['users']['lastLogout'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['lastLogout'] . "` = '0';";
		$nr = $conn->query($query)->num_rows;
		
		unset($query);
		
		$query = "SELECT `" . $FIELDS['facils']['maxLoggedUsers'] . "` FROM `" . $TABLES['facils'] . "` WHERE `" . $FIELDS['facils']['id'] . "` = '1' LIMIT 1;";
		$row = $conn->query($query)->fetch_row(); unset($query);
		if($nr > $row[0]){
			
			$query = "UPDATE `" . $TABLES['facils'] . "` SET `" . $FIELDS['facils']['maxLoggedUsers'] . "` = '" . $nr . "', `" . $FIELDS['facils']['maxLoggedDate'] . "` = '" . time() . "' WHERE `" . $FIELDS['facils']['id'] . "` = '1' LIMIT 1;";
			$conn->query($query);
			unset($query);
				
		}
		
		unset($row);
		
		return $nr;
		
	}
	
	function total(){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$total = array();
		
		$query = "SELECT `" . $FIELDS['users']['id'] . "` FROM `" . $TABLES['users'] . "`;";
		$total[0] = $conn->query($query)->num_rows;
		unset($query);
		
		$query = "SELECT `" . $FIELDS['forumPosts']['id'] . "` FROM `" . $TABLES['forumPosts'] . "`;";
		$total[] = $conn->query($query)->num_rows;
		unset($query);
		
		$query = "SELECT `" . $FIELDS['facils']['maxLoggedUsers'] . "`,`" . $FIELDS['facils']['maxLoggedDate'] . "`,`" . $FIELDS['facils']['maxBet'] . "`,`" . $FIELDS['facils']['maxUser'] . "` FROM `" . $TABLES['facils'] . "` WHERE `" . $FIELDS['facils']['id'] . "` = '1' LIMIT 1;";
		$row = $conn->query($query)->fetch_row();
		
		foreach($row as $ro)
			$total[] = $ro;
		unset($query,$row);	
			
		$query = "SELECT `" . $FIELDS['users']['user'] . "` FROM `" . $TABLES['users'] . "` ORDER BY `" . $FIELDS['users']['actiuni'] . "` DESC;";
		$row = $conn->query($query)->fetch_row();
		$total[] = $row[0];
			
		unset($query,$row);
		
		return $total;
	
	}

}

?>