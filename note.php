<?php

class Noteaza{

	public function __construct($nota,$unde,$id_obj){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT `" . $FIELDS[$unde]['votanti'] . "`,`" . $FIELDS[$unde]['media'] . "` FROM `" . $TABLES[$unde] . "` WHERE `" . $FIELDS[$unde]['id'] . "` = '" . $id_obj  . "' LIMIT 1;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$nr = $row[0]; $media = $row[1];
		
		if(!self::aVotat($unde,$id_obj)){
			$media2 = ( $nr * $media + $nota ) / ( $nr + 1 );
			$nr++;
			$query = "UPDATE `" . $TABLES[$unde] . "` SET `" . $FIELDS[$unde]['votanti'] . "` = '" . $nr . "',`" . $FIELDS[$unde]['media'] . "` = '" . $media2 . "' WHERE `" . $FIELDS[$unde]['id'] . "` = '" . $id_obj . "' LIMIT 1 ;";
			$conn->query($query);

		
			echo '	<ul class="star">
    					<li class="curr" title="' . $media2 . '" style="width: ' . $media2 . 'px;"></li>
    				</ul>';	
		
			unset($nr,$media,$media2,$result,$query);
		}
		else
			echo '	<ul class="star">
    					<li class="curr" title="' . $media . '" style="width: ' . $media . 'px;"></li>
    				</ul>';	
	}

	public function aVotat($unde,$id_obj){
	
		if(isset($_COOKIE['user'])){
			$cookie = $_COOKIE['user'];
			$ide = explode("|",$cookie);
			$id = $ide[1];
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `obj` FROM `ftbf_voturi` WHERE `obj` = '" . $unde . "' AND `id_obj` = '" . $id_obj . "' AND `id_user` = '" . $id . "' LIMIT 1;";
			$result = $conn->query($query);
			
			if($result->num_rows)
				return true;
			else{
				 unset($query,$result);
				$query = "INSERT INTO `ftbf_voturi` (`obj`,`id_obj`,`id_user`) VALUES ('" . $unde . "','" . $id_obj . "','" . $id . "');";			$conn->query($query);
				unset($query);
				
				return false;
			}
		}
		
		else
			return true;
	
	}
	
}


?>