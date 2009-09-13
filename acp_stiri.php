<?php

class ACPStiri{

	public function adaugaStire($titlu,$body,$indexImg,$sursa,$taguri,$data = NULL){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			include_once "classes/image.php";
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT MAX(`" . $FIELDS['stiri']['id'] . "`) FROM `" . $TABLES['stiri'] . "`;";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			(int)$newName = (int)$row[0] + 1;
			$result->free; unset($result,$query,$row);
			
			if(!isset($data))
				$data = time();
			
			$saves = Image::uploadImage($indexImg,"images/stiri/",$newName,156,147,50,50);
			
			$query = "INSERT INTO `" . $TABLES['stiri'] . "` (`" . $FIELDS['stiri']['id'] . "`,`" . $FIELDS['stiri']['titlu'] . "`,`" . $FIELDS['stiri']['body'] . "`,`" . $FIELDS['stiri']['img'] . "`,`" . $FIELDS['stiri']['sursa'] . "`,`" . $FIELDS['stiri']['data'] . "`,`" . $FIELDS['stiri']['dataMare'] . "`) VALUES ('" . $newName . "','" . $titlu . "','" . $body . "','" . $saves[1] . "','" . $sursa . "','" . $data . "','" . $data . "');";

			$conn->query($query);
			unset($query);
			
			if(gettype($taguri) != "Array"){
				
				$taguri = explode(";",$taguri);
					
			}
			
			for($i = 0;$i < count($taguri);$i++){
				$query = "INSERT INTO `" . $TABLES['tags'] . "` (`" . $FIELDS['tags']['id'] . "`,`" . $FIELDS['tags']['obj'] . "`,`" . $FIELDS['tags']['id_obj'] . "`,`" . $FIELDS['tags']['tag'] . "`) VALUES (NULL,'stiri','" . $newName . "','" . $taguri[$i] . "');";
				$conn->query($query);
				unset($query);
			}
			
			
		}
		else
			die('Hacking attempt...');
			
	}#end adauga stire
	
	
	public function modificaInStire($ce,$id,$new){
	
		# $ce = { 'titlu', 'body', 'data', 'sursa', 'img'}
		#daca $ce == 'img', atunci $new trebuie sa fie index de vecor de $_FILES
		if($ce == "stire")
			$ce = "body";
		$posibile = array('titlu','body','data','sursa','img');
		if(!in_array($ce,$posibile))
			die('');
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->poateModifica()){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			if($ce == "img"){
			
				include_once "classes/image.php";
				$saves = Image::uploadImage($new,"images/stiri/",$id,156,147,50,50);
				$new = $saves[1];
				unset($saves);
				
			}
			
			$query = "UPDATE `" . $TABLES['stiri'] . "` SET `" . $FIELDS['stiri'][$ce] . "` = '" . $new . "' WHERE `" . $FIELDS['stiri']['id'] . "` = '" . $id . "' LIMIT 1;";
			$conn->query($query);
			
			unset($query);
			
		
		}
		else
			die('Hacking attempt...');
	
	}
	
	public function stergeStire($id){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
	
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
			$query = "DELETE FROM `" . $TABLES['stiri'] . "` WHERE `" . $FIELDS['stiri']['id'] . "` = '" . $id . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			$query = "DELETE FROM `" . $TABLES['comentarii'] . "` WHERE `" . $FIELDS['stiri']['id_obj'] . "` = '" . $id . "' AND `" . $FIELDS['stiri']['unde'] . "` = 'stiri';";
			$conn->query($query);
			unset($query);
		
		}
		else
			die('Hacking attempt...');
	
	}
	
	public function afiseazaPropuneri(){
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
	
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT * FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['unde'] . "` = 'stiri'";
			$result = $conn->query($query);
			
			echo '<table width="100%" cellpadding="0" cellspacing="0" border="1">' . "\n";
			echo '	<tr>' . "\n";
			echo '		<td>Ce propune</td>' . "\n";
			echo '		<td>Unde</td>' . "\n";
			echo '		<td>Pe cine</td>' . "\n";
			echo '		<td>Cine propune</td>' . "\n";
			echo '		<td>Titlu</td>' . "\n";
			echo '		<td>Continut</td>' . "\n";
			echo '		<td>Imagine</td>' . "\n";
			echo '		<td></td>' . "\n";
			echo '		<td></td>' . "\n";
			echo '	</tr>' . "\n";
			
			while($row = $result->fetch_array()){
				$id = $row[$FIELDS['propuneri']['id']];
			
				echo '	<tr class="propunere" id="prop' . $id . '">' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['type']] . '</td>' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['unde']] . '</td>' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['id_obj']] . '</td>' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['prop']] . '</td>' . "\n";
				echo '		<td>' . substr(stripslashes($row[$FIELDS['propuneri']['titlu']]),0) . '</td>' . "\n";
				echo '		<td class="body">' . substr(stripslashes($row[$FIELDS['propuneri']['continut']]),0) . '</td>' . "\n";			
				$default = 'default.png';
				echo '		<td><a href="javascript:void(0)" onClick="Admin.veziThumbApp(\'thumbBig' . $id . '\')"><img src="' . $row[$FIELDS['propuneri']['thumb']] . '" /></a></td>' . "\n";
				
				if($row[$FIELDS['propuneri']['type']] == 'adauga')
					$wtf = "Adaugare";
				else
					$wtf = "Stergere";
					
				echo '		<td><a href="javascript:void(0)" onClick="if(confirm(\'Chiar vreti sa validati aceasta propunere de ' . strtolower($wtf) . '?\'))Admin.valideazaPropunere' . $wtf . '(' . $id . ')" title="Valideaza Propunere ' . $wtf . '"><img src="images/icons/tick.png" /></a></td>' . "\n";
				echo '		<td><a href="javascript:void(0)" onClick="if(confirm(\'Vreti sa stergeti aceasta propunere?\'))Admin.stergePropunere(' . $id . ')" title="Sterge Propunerea"><img src="images/icons/cross.png" /></a></td>' . "\n";
				echo "	</tr>\n";
			
			}
			
			echo '</table>';
			echo '<div id="overlay" style="display:none" class="overlay"></div>';
			
			unset($result,$row);
			
			$result = $conn->query($query);
			
			while($row = $result->fetch_array())
				echo '<div id="thumbBig' . $row[$FIELDS['propuneri']['id']] . '" style="display:none;" class="thumbImg"><img src="' . $row[$FIELDS['propuneri']['img']] . '" /></div>' . "\n";
		}
		else
			die('Hacking attempt...');
	
	}
	
	public function valideazaPropunereAdaugare($id_prop){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
	
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
		
			$query = "SELECT `" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['titlu'] . "`,`" . $FIELDS['propuneri']['continut'] . "`,`" . $FIELDS['propuneri']['img'] . "`,`" . $FIELDS['propuneri']['data'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['unde'] . "` = 'stiri' AND `" . $FIELDS['propuneri']['type'] . "` = 'adauga' AND `" . $FIELDS['propuneri']['id'] . "` = '" . $id_prop . "' LIMIT 1;";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			$result->free; unset($query,$result);
			
			
			$query = "SELECT MAX(`" . $FIELDS['stiri']['id'] . "`) FROM `" . $TABLES['stiri'] . "`;";
			$result = $conn->query($query);
			$row2 = $result->fetch_row();
			(int)$newId = (int)$row2[0] + 1;
			$result->free; unset($query,$result,$row2);
			
			$query = "INSERT INTO `" . $TABLES['stiri'] . "` (`" . $FIELDS['stiri']['id'] . "`,`" . $FIELDS['stiri']['titlu'] . "`,`" . $FIELDS['stiri']['body'] . "`,`" . $FIELDS['stiri']['img'] . "`,`" . $FIELDS['stiri']['prop'] . "`,`" . $FIELDS['stiri']['data'] . "`,`" . $FIELDS['stiri']['dataMare'] . "`) VALUES ('" . $newId . "','" . $row[1] . "','" . $row[2] . "','" . $row[3] . "','" . $row[0] . "','" . $row[4] . "','" . $row[4] . "');";
			$conn->query($query);
			unset($query);
			
			self::stergePropunere($id_prop);
			
		}
		
		else
			die('Hacking attempt...');
	
	}
	
	public function valideazaPropunereStergere($id_prop){
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
	
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['propuneri']['id_obj'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['unde'] . "` = 'stiri' AND `" . $FIELDS['propuneri']['type'] . "` = 'stergere' AND `" . $FIELDS['propuneri']['id'] . "` = '" . $id_prop . "' LIMIT 1;";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			$id_obj = $row[0];
			$result->free; unset($result,$query,$row);
			
			
			$query = "DELETE FROM `" . $TABLES['stiri'] . "` WHERE `" . $FIELDS['stiri']['id'] . "` = '" . $id_obj . "' LIMIT 1;";
			$conn->query($query); unset($query);
			
			self::stergePropunere($id_prop);
			
		}
		else
			die('Hacking attempt...');
	}
	
	public function stergePropunere($id_prop){
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
	
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "DELETE FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['id'] . "` = '" . $id_prop . "' LIMIT 1;";
			$conn->query($query); unset($query);
			
		}
		else
			die('Hacking attempt...');
	}

}



?>