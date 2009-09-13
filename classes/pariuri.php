<?php


class Pariuri{

	function __construct($id_meci,$cota){
		
		$posibilitati = array(1,2,"x","X");
		
		if(!in_array($cota,$posibilitati))
			exit();
		
		include_once "classes/users.php";
		
		if(poateParia()){
		
			if(self::poatePariaMeciul($id_meci)){
				if(!self::existaMeciIn('pariuri',$id_meci,getIdFromCookie())){
					include_once "classes/config.php";
					global $FIELDS,$TABLES,$conn;
				
					(int)$id_bilet = self::getIdLastBilet(); $id_bilet++;
					$id_user = getIdFromCookie();
				
					$query = "INSERT INTO `" . $TABLES['pariuri'] . "` (`" . $FIELDS['pariuri']['id'] . "`,`" . $FIELDS['pariuri']['id_meci'] . "`,`" . $FIELDS['pariuri']['id_bilet'] . "`,`" . $FIELDS['pariuri']['id_user'] . "`,`" . $FIELDS['pariuri']['cota'] . "`,`" . $FIELDS['pariuri']['data'] . "`) VALUES (NULL,'" . $id_meci . "','" . $id_bilet . "','" . $id_user . "','" . $cota . "','" . time() . "');";
					$conn->query($query);
					self::afiseazaBilet($id_bilet,$id_user,time(),true);
				}
				else
					self::afiseazaUltimBiletNepariat();
			}
			
			else
				self::afiseazaUltimBiletNepariat();
			
		}
		else
			echo 'Nu puteti paria!';
	
	}
	
	private function poatePariaMeciul($id_meci){
		$este = self::existaMeciIn('meciuri',$id_meci);
		$are = self::areRezultate($id_meci);
		
		if( ($este) && (!$are) )
			return true;
	}
	
	
	private function areRezultate($id){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['meciuri']['id_meci'] . "` FROM `" . $TABLES['meciuri'] . "` WHERE `" . $FIELDS['meciuri']['id_meci'] . "` = '" . $id . "' AND `" . $FIELDS['meciuri']['areRezultat'] . "` = '1' LIMIT 1;";
		//echo $query;
		$result = $conn->query($query);
		
		unset($query);
		
		if($result->num_rows)
			return true;
		else
			return false;
	
	}
	
	public function existaMeciIn($unde,$id,$id_user = NULL){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		(string)$and = "";
		if($unde == 'pariuri')
			$and = "AND `" . $FIELDS['pariuri']['id_user'] . "` = '" . $id_user . "' ";
		
		$query = "SELECT `" . $FIELDS[$unde]['id_meci'] . "` FROM `" . $TABLES[$unde] . "` WHERE `" . $FIELDS[$unde]['id_meci'] . "` = '" . $id . "' " . $and . "LIMIT 1;";
		//echo $query;
		$result = $conn->query($query);
		return $result->num_rows;
	}
	
	public function getIdLastBilet(){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$cookie = $_COOKIE['user'];
		$ide= explode("|",$cookie);
		$id = $ide[1]; unset($ide,$cookie);
		
		$query = "SELECT `" . $FIELDS['users']['idLastBilet'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['id'] . "` = '" . $id . "' LIMIT 1;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		 unset($query,$result,$id);
		return $row[0];
	}
	
	
	public function afiseazaUltimBiletNepariat(){
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->esteLogat()){
		
			(int)$id_bilet = self::getIdLastBilet(); $id_bilet++;
			self::afiseazaBilet($id_bilet,getIdFromCookie(),time(),true);
			
		}
		
	}
	
	public function stergeMeci($id){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "DELETE FROM `" . $TABLES['pariuri'] . "` WHERE `" . $FIELDS['pariuri']['id'] . "` = '" . $id . "' LIMIT 1;";
		$conn->query($query);
		unset($query);
		
		include_once "classes/meciuri.php";
		new Meciuri(time());
		
	}
	
	public function afiseazaBilet($id_bilet,$id_user,$showUL = true,$data = 0){
		
		//include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['echipaGazda'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['echipaOaspete'] . "`,`" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['cota'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['cota1'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['cotaX'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['cota2'] . "`,`" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['verificat'] . "`,`" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['castigator'] . "`,`" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['data'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['Rezultat1'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['Rezultat2'] . "`,`" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['id'] . "` FROM `" . $TABLES['meciuri'] . "`,`" . $TABLES['pariuri'] . "` WHERE `" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['id_meci'] . "` = `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['id_meci'] . "` AND `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['id_user'] . "` = '" . $id_user . "' AND `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['id_bilet'] . "` = '" . $id_bilet . "' ;";


		$result = $conn->query($query);
		(int)$castig = 1;
		
		//$showUL = true => afisat,dar neverificat ; afisez <ui>
		//		  false => neafisat daca e neverificat si restul; nu afisez <ul>
		
		(int)$eVerificat = (int)$castigator = 0;
		if($result->num_rows){
			while($row = $result->fetch_row()){
				unset($eVerificat,$castigator);
				if($row[2] == "1")
					$cota = $row[3];
				elseif($row[2] == "x")
					$cota = $row[4];
				else
					$cota = $row[5];
				$castig *= $cota;
				
				$eVerificat = $row[6];
				$castigator = $row[7];
			}
			
			if($showUL == true){
				echo '<ul class="bilete">' . "\n";
				$class = " neverificat";
				$data = time();
			}
			
			else{
				if($eVerificat == 1){
					if($castigator == 1){
						$class = "";
					}
					else{
						$class = " necastigator";
					}
				}
				else{
					if($showUL == false){
						$class = " neafisat";
					}
					else{
						$class = " neverificat";
					}
				}
			}
				
			$castig = substr($castig,0,5);
			
			 unset($row,$result);
			$result = $conn->query($query);
	
			echo '	<li id="bilet' . $id_bilet . '" class="bilet' . $class . '">' . "\n";
			echo '		<div class="dataBilet">' . "\n";
			echo '			<h4 onclick="toggle(\'divBilet' . $id_bilet . '\');">' . date("d m Y",$data) . '</h4>' . "\n";
			echo '		</div>' . "\n";
			echo '		<div class="meciuriBilet" id="divBilet' . $id_bilet . '">' . "\n";
			echo '			<div class="suma"><div class="divSuma">' . $castig . ' RON</div></div>' . "\n";
			 
			(int)$k = 0;
			 
			while($row = $result->fetch_row()){
				
				$k++;
				
				if($row[2] == "1")
					$cota = $row[3];
				elseif($row[2] == "x")
					$cota = $row[4];
				else
					$cota = $row[5];
				
				(string)$echipa1 = (string)$echipa2 = "";
				
				if(!$showUL){
				
					(int)$diferenta = (int)$row[9] - (int)$row[10];
					
					if($diferenta > 0){
						$echipa1 = "echipaCastigatoare";
						$echipa2 = "echipaPierzatoare";
						$cotaCareArFiTb = '1';
					}
					elseif($diferenta == 0){
						$echipa1 = "remiza";
						$echipa2 = "remiza";
						$cotaCareArFiTb = 'x';
					}
					elseif($diferenta < 0){
						$echipa1 = "echipaPierzatoare";
						$echipa2 = "echipaCastigatoare";
						$cotaCareArFiTb = '2';
					}
					
				}
				
				if($cotaCareArFiTb == $row[2])
					$icon = "tick";
				else
					$icon = "cross";
									
				echo '			<div class="meci" id="mc' . $row[count($row)-1] . '">';
				if(!$showUL)
					echo '<a href="javascript:void(0)" onClick="Pariuri.rezultateMeci(' . $row[count($row)-1] . ',' . $id_bilet . ')">';
				echo '<span class="' . $echipa1 . '">' . $row[0] . '</span> - <span class="' . $echipa2 . '">' . $row[1] . "</span>"; 
				if(!$showUL)
					echo '</a>';
				echo " cota aleasa " . $row[2] . " ( " . $cota . " ) \n";
				if($showUL){
					echo '<a href="javascript:void(0)" onClick="Pariuri.stergeMeciDinBilet(' . $row[count($row)-1] . ')">Sterge</a>' . "\n";
				}
				else
					echo '<img src="images/icons/' . $icon . '.png" />';
				echo "</div>\n";
				
			}
			if(!$showUL)
				echo '		<div id="meciuriRezultate' . $id_bilet . '" class="rezultate"></div>' . "\n";
			if( ($showUL) && ($k >= 3) )
				echo '	<input type="button" onClick="Pariuri.pariazaUltimBilet()" value="Pariaza" />';
			echo '		</div>' . "\n";
			echo '	</li>' . "\n";
			if($showUL){
				echo '</ul>' . "\n";
			}
		}
		 unset($result,$query,$row,$cota,$castig);
		unset($echipa1,$echipa2,$diferenta);
		
	}
	
	public function pariazaUltimBilet(){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		include_once "classes/users.php";
		$id =  getIdFromCookie();
		
		if(sumaDinCont() >= 1){
		
			$query = "SELECT MAX(`" . $FIELDS['pariuri']['id_bilet'] . "`) FROM `" . $TABLES['pariuri'] . "` WHERE `" . $FIELDS['pariuri']['id_user'] . "` = '" . $id . "' LIMIT 1;";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			
			$id_biletPariuri = $row[0];
			
			 unset($query,$result,$row);
			
			$query = "SELECT `" . $FIELDS['pariuri']['id'] . "` FROM `" . $TABLES['pariuri'] . "` WHERE `" . $FIELDS['pariuri']['id_user'] . "` = '" . $id . "' AND `" . $FIELDS['pariuri']['id_bilet'] . "` = '" . $id_biletPariuri . "'";
			$result = $conn->query($query);
			$nrMeciuri = $result->num_rows;
			 unset($query,$result);
			
			$id_biletUser = self::getIdLastBilet();
			
			
			if( ($id_biletUser + 1 == $id_biletPariuri) && ($nrMeciuri >= 3) ){
				
				$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['idLastBilet'] . "` = `" . $FIELDS['users']['idLastBilet'] . "`+1,`" . $FIELDS['users']['cont'] . "` = `" . $FIELDS['users']['cont'] . "`-1,`" . $FIELDS['users']['actiuni'] . "` = `" . $FIELDS['users']['actiuni'] . "`+5 WHERE `" . $FIELDS['users']['id'] . "` = '" . $id . "' LIMIT 1;";
				$conn->query($query);
				unset($query);
				
				$query = "SELECT `" . $FIELDS['users']['cont'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['id'] . "` = '" . $id . "' LIMIT 1;";
				$row = $conn->query($query)->fetch_row();
				$cont = $row[0]; unset($query,$row);
				
				$query = "INSERT INTO `" . $TABLES['bugete'] . "` (`" . $FIELDS['bugete']['id'] . "`,`" . $FIELDS['bugete']['idUser'] . "`,`" . $FIELDS['bugete']['cont'] . "`,`" . $FIELDS['bugete']['data'] . "`) VALUES (NULL,'" . $id . "','" . $cont . "','" . time() . "');";
				$conn->query($query);
				unset($query);
			}
			else
				die('');
		}

	}
	
	
	public function sumaMaxima(){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT SUM(`" . $FIELDS['users']['cont'] . "`) FROM `" . $TABLES['users'] . "`;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		return $row[0];
	}
	
}

?>