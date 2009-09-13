<?php

class ACPTags{

	public function stergeTag($tag){
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		

			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "DELETE FROM `" . $TABLES['tags'] . "` WHERE `" . $FILEDS['tags']['tag'] . "` = '" . $tag . "';";
			$conn->query($query);
			unset($query);
			
			$query = "DELETE FROM `" . $TABLES['userTags'] . "` WHERE `" . $FIELDS['userTags']['tag'] . "` = '" . $tag . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}

}

?>