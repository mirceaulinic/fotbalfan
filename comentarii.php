<?php


function formatData($time){

	(int)$taim = time();
	(int)$dif = $taim - $time;
	if($dif < 60)
		$return = "in urma cu " . $dif . " secunde";
	elseif( ($dif >= 60) && ($dif < 3600) )
		$return = "in urma cu " . date('i',$dif) . " minute";
	elseif( ($dif >= 3600) && ($dif < 86400) )
		$return = "in urma cu " . floor( $dif / 3600 ) . " ore";
	elseif( ($dif >= 86400) && ($dif < 2592000) )
		$return = "in urma cu " . floor( $dif / 86400 ) . " zile";
	else
		$return = "pe " . date("d m Y, H:i",$time);
	
	unset($taim,$dif);
	return $return;
	
}


class Comentarii{


	function afiseazaComentarii($unde,$id_obj,$pagina = 1,$sort = "DESC"){
	
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT * FROM `" . $TABLES['comentarii'] . "` WHERE `" . $FIELDS['comentarii']['unde'] . "` = '" . $unde . "' AND `" . $FIELDS['comentarii']['id_obj'] . "` = '" . $id_obj . "' ORDER BY `" . $FIELDS['comentarii']['data'] . "` " . $sort . ";";
		$result = $conn->query($query); $nrRows = $result->num_rows;
		
		define('RezPePaginaComm',10);
		
		(int)$max = RezPePaginaComm; (int)$nrPagini = 0;
		
		if($nrRows > $max){
			
			$start = ( $pagina - 1 ) * $max;
			$nrPagini = ceil( $nrRows / $max );
			 unset($result,$query);
			$query = "SELECT * FROM `" . $TABLES['comentarii'] . "` WHERE `" . $FIELDS['comentarii']['unde'] . "` = '" . $unde . "' AND `" . $FIELDS['comentarii']['id_obj'] . "` = '" . $id_obj . "' ORDER BY `" . $FIELDS['comentarii']['data'] . "` " . $sort . " LIMIT " . $start . "," . $max . ";";
			$result = $conn->query($query);
			
		}
		
		(int)$class = 2;
		(int)$k = ($pagina - 1) * $max;
		
		
		
		while($row = $result->fetch_array()){
		
			if($class == 1)
				$class = 2;
			elseif($class == 2)
				$class = 1;
				
			$bad = $row[$FIELDS['comentarii']['bad']];
			$good = $row[$FIELDS['comentarii']['good']];
			$id = $row[$FIELDS['comentarii']['id']];
			(string)$toggle = (string)$style = "";
			if( ($bad - $good) > 5 ){
				$toggle = '<a href="javascript:void(0);" onclick="toggle(\'body' . $id . '\')" class="afiseaza">Afiseaza</a>';
				$style = 'style="display:none;"';
			}
			
			$k++;
			
			(bool)$poate = false;
			(string)$clickBad = (string)$clickGood = "return false;";
			$poate = self::poateNota($id);
			
			if($poate){
			
				$clickBad = 'User.noteazaComm(' . $id . ',\'bad\'); return false;';
				$clickGood = 'User.noteazaComm(' . $id . ',\'good\'); return false;';
				
			}
			
			include_once "classes/users.php";
			$user = determinaStatus();
		
			(bool)$modifica = (bool)$sterge = false;
			(string)$editabilInput = "";
			if($user->poateModifica()){
				$modifica = true;
				$editabilInput = ' class="editabilComm"';
			}
		
			if($user->poateSterge())
				$sterge = true;
			
			echo '					<div class="comentariu' . $class . '" id="comentariu' . $id . '">' . "\n";
			echo '						<div class="diez">#<span id="diez' . $k . '">' . $k . '</span></div>';
			echo '						<div class="details">' . "\n";
			echo '							<span class="user">' . $row[$FIELDS['comentarii']['user']] . '</span>,' . "\n";
			echo '							<span class="data">' . formatData($row[$FIELDS['comentarii']['data']]) . '</span>' . "\n";
			echo '							' . $toggle . "\n";
			echo '						</div>' . "\n";
			echo '						<div class="body" ' . $style . ' id="body' . $id . '">' . "\n";
			echo '							<div id="id_' . $id . '"' . $editabilInput . '>' . $row[$FIELDS['comentarii']['body']] . "</div>\n";
			echo '							<div class="notaBad"><span id="nota' . $id . 'bad">' . $bad . '</span><span id="notabad' . $id . '"><a href="javascript:void(0);" onclick="' . $clickBad . '"><img src="images/icons/thumb_down.png" /></a></span></div>' . "\n";
			echo '							<div class="notaGood"><span id="nota' . $id . 'good">' . $good . '</span><span id="notagood' . $id . '"><a href="javascript:void(0);" onclick="' . $clickGood . '"><img src="images/icons/thumb_up.png" /></a></span></div>' . "\n";
			
			if($sterge)
				echo '							<div class="sterge"><a href="javascript:void(0);" onclick="if(confirm(\'Il stergi?\')) Admin.stergeComentariu(' . $id . ',' . $k . ');"><img src="images/icons/cross.png" /></a></div>';

			echo '						</div>' . "\n";
			echo '					</div>' . "\n\n";
			unset($good,$bad,$clickBad,$clickGood,$style,$toggle);
		}
		
		include_once "classes/users.php";
		$user = determinaStatus();
		
		if($user->esteLogat()){
			
			$k++;
			
			if($class == 1)
				$class = 2;
			elseif($class == 2)
				$class = 1;
				
			echo '					<div class="comentariu' . $class . '" id="lastComentariu" style="display:none;">' . "\n";
			echo '						<div class="diez">#' . $k . '</div>';
			echo '						<div class="details">' . "\n";
			echo '							<span class="user">' . getUserFromCookie() . '</span>,' . "\n";
			echo '							<span class="data">acum</span>' . "\n";
			echo '						</div>' . "\n";
			echo '						<div class="body" id="bodyC' . $k . '">' . "\n";
			echo '						<input type="text" id="newComm" size="40" /> &nbsp; <input type="button" value="Adaug&#259; comentariul" onclick="User.adaugaComm(\'newComm\',' . $k . ',\'' . $unde . '\',' . $id_obj . ');" />';
			echo '						</div>' . "\n";
			echo '					</div>' . "\n\n";
		
		}
		
		if($sort == "DESC")
			$how = "asc";
		else
			$how = "desc";
		
		//(string)$link = "ce=comentarii&unde=" . $unde . "&id_obj=" . $id_obj . "&pg=";
		if($nrPagini){
			for((int)$i = 1; $i <= $nrPagini; $i++){
				//(string)$link2 = $link . $i;
				if($i != $pagina)
					echo '<a href="javascript:void(0)" onclick="User.afiseazaComentarii(' . $i . ',\'comentarii\',\'' . $unde . '\',' . $id_obj . ',\'' . strtolower($sort) . '\');" class="pagini">[' . $i . ']</a>' . "\n";
				else
					echo '<a href="javascript:void(0)" onclick="User.afiseazaComentarii(' . $i . ',\'comentarii\',\'' . $unde . '\',' . $id_obj . ',\'' . $how . '\');" id="prezent">[' . $i .']</a>' . "\n";
			}
		}
		elseif($k > 2)
			echo '<a href="javascript:void(0)" onclick="User.afiseazaComentarii(1,\'comentarii\',\'' . $unde . '\',' . $id_obj . ',\'' . $how . '\');" id="prezent">[sorteaza invers]</a>' . "\n";
		
		if($user->esteLogat())
			echo '<a href="javascript:void(0)" id="addLink" onclick="toggle(\'lastComentariu\')">Adaug&#259; un comentariu</a>';
		
		 unset($class,$result,$row,$query,$id,$toggle,$style);
		
		echo '<span id="nrDiez" class="none">' . $k . '</span>';
		
	}
	
	
	private function poateNota($id_comm){
		
		include_once "classes/users.php";
		$user = determinaStatus();
	
		if($user->esteLogat()){
				
			include_once "classes/config.php";
			global $FIELDS,$TABLES,$conn;
				
			$cookie = $_COOKIE['user'];
			$ide = explode("|",$cookie);
			$id_user = $ide[1]; unset($cookie,$ide);
			
			$query = "SELECT `" . $FIELDS['noteComentarii']['id_user'] . "` FROM `" . $TABLES['noteComentarii'] . "` WHERE `" . $FIELDS['noteComentarii']['id_comm'] . "` = '" . $id_comm . "'  AND `" . $FIELDS['noteComentarii']['id_user'] . "` = '" . $id_user . "' LIMIT 1;";
			$result = $conn->query($query);
			if($result->num_rows)
				return false;
			else
				return true;
		}
		else
			return false;

	}
	
	public function adaugaComentariu($cine,$body,$unde,$id_obj){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$time = time();
		
		$query = "INSERT INTO `" . $TABLES['comentarii'] . "` (`" . $FIELDS['comentarii']['id'] . "`,`" . $FIELDS['comentarii']['id_obj'] . "`,`" . $FIELDS['comentarii']['unde'] . "`,`" . $FIELDS['comentarii']['body'] . "`,`" . $FIELDS['comentarii']['user'] . "`,`" . $FIELDS['comentarii']['data'] . "`) VALUES (NULL,'" . $id_obj . "','" . $unde . "','" . $body . "','" . $cine . "','" . $time . "');";
		$conn->query($query);
		unset($query);
		
		$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['actiuni'] . "` = `" . $FIELDS['users']['actiuni'] . "`+1 WHERE `" . $FIELDS['users']['user'] . "` = '" . $cine . "' LIMIT 1;";
		$conn->query($query);
		unset($query);
		
		$cookie = $_COOKIE['user'];
		$id = explode("|",$cookie);
		
		
		$query = "SELECT `" . $FIELDS['comentarii']['id'] . "` FROM `" . $TABLES['comentarii'] . "` WHERE `" . $FIELDS['comentarii']['id_obj'] . "` = '" . $id_obj . "' AND `" . $FIELDS['comentarii']['data'] . "` = '" . $time . "' LIMIT 1;";
		$id_comm = $conn->query($query)->fetch_row();
		unset($query);
		
		$query = "INSERT INTO `" . $TABLES['noteComentarii'] . "` (`" . $FIELDS['noteComentarii']['id_comm'] . "`,`" . $FIELDS['noteComentarii']['id_user'] . "`) VALUES ('" . $id_comm[0] . "','" . $id[1] . "');";
		$conn->query($query);
		unset($query,$time,$id_comm,$id);
	
	}
	
	public function noteazaComm($id_comm,$badOrGood,$id_user){
		
		if(self::poateNota($id_comm)){
		
			include_once "classes/config.php";
			global $FIELDS,$TABLES,$conn;
			
			$query = "INSERT INTO `" . $TABLES['noteComentarii'] . "` (`" . $FIELDS['noteComentarii']['id_comm'] . "`,`" . $FIELDS['noteComentarii']['id_user'] . "`) VALUES ('" . $id_comm . "','" . $id_user . "');";
			$conn->query($query);
			unset($query);
			
			$query = "UPDATE `" . $TABLES['comentarii'] . "` SET `" . $FIELDS['comentarii'][$badOrGood] . "` = `" . $FIELDS['comentarii'][$badOrGood] . "`+1 WHERE `" . $FIELDS['comentarii']['id'] . "` = '" . $id_comm . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
		}
	
	} 

}

?>