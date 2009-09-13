<?php

function resort($IDS){
	$count = count($IDS); $Arr = array();
	for($i=$count;$i>=1;$i--)
		$Arr[$count-$i+1] = $IDS[$i];
	unset($count,$i);
	return $Arr;
}		

function eliminaDubluri($array){
	$count = count($array);
	for((int)$i=0;$i<$count;$i++){
		if(isset($array[$i]))
			for((int)$j=$i+1;$j<=$count;$j++)
				if(@$array[$j] == @$array[$i]){
					for($k=$j;$k<$count;$k++)
						$array[$k] = $array[$k+1];
					$i--;
					unset($array[$count]);
					$count--;
				}
	}
	unset($count,$i,$j,$k);
	return $array;
}

function lipesteVectori($Arr1,$Arr2){
	$count1 = count($Arr1);
	$count2 = count($Arr2);
	for($i=1;$i<=$count2;$i++)
		if(isset($Arr2[$i]))
			$Arr1[$count1+$i] = $Arr2[$i];
	return $Arr1;
}

class Recomands{
	public function getRecs($where,$idUser){
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$tags = self::getUserTags($where,$idUser);
		$query = array();
		for($i=0;$i<3;$i++)
			$query[$i] = "SELECT `" . $TABLES['users'] . "`.`" . $FIELDS['users']['user'] . "` FROM `" . $TABLES['userTags'] . "`, `" . $TABLES['users'] . "` WHERE `" . $TABLES['users'] . "`.`" . $FIELDS['users']['id'] . "` = `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['user'] . "` AND `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['user'] . "` = '" . $idUser . "' AND `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['tag'] . "` = '" . $tags[$i] . "' AND `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['obj'] . "` = '" . $where . "' ORDER BY `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['hits'] . "` DESC LIMIT 5;";	
		(int)$total = 0;
		for($i=0;$i<3;$i++){
			$result = $conn->query($query[$i]);
			$nrRows[$i] = $result->num_rows;
			unset($result);
			$total += $nrRows[$i];	
		} unset($query);
		(int)$ramase = 0;
		if($total){
			for($i=0;$i<=3;$i++)
				if(@$nrRows[$i]){
					switch($i){
					case 0:
						$celalalt[1] = $tags[1];
						$celalalt[2] = $tags[2];
						break;
					case 1:
						$celalalt[1] = $tags[0];
						$celalalt[2] = $tags[2];
						break;
					case 2:
						$celalalt[1] = $tags[0];
						$celalalt[2] = $tags[1];
						break;
					} #end switch
					
					$query1 = "SELECT `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['id_obj'] . "` FROM `" . $TABLES['userTags'] . "`,`" . $TABLES['tags'] . "` WHERE `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['tag'] . "` = `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['tag'] . "` AND `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['obj'] . "`= 'stiri' AND `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['tag'] . "` = '" . $tags[$i] . "' ORDER BY `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['hits'] . "` DESC LIMIT 5;";
					$result1 = $conn->query($query1);
					//echo $query1." (1)<br />";
					if($result1->num_rows)
						while($row = $result1->fetch_row()){
							/*$query2 = "SELECT `" . $FIELDS['tags']['id_obj'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['tag'] . "` = '" . $celalalt[1] . "' AND `" . $FIELDS['tags']['obj'] . "` = 'stiri' AND `" . $FIELDS['tags']['id_obj'] . "` = '" . $row[0] . "' LIMIT 5;";*/
							$query2 = "SELECT `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['id_obj'] . "` FROM `" . $TABLES['tags'] . "`,`" . $TABLES['userTags'] . "` WHERE `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['tag'] . "` = `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['tag'] . "` AND `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['tag'] . "` = '" . $celalalt[1] . "' AND `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['obj'] . "` = 'stiri' AND `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['id_obj'] . "` = '" . $row[0] . "' ORDER BY `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['hits'] . "` DESC LIMIT 5;";
							$result2 = $conn->query($query2);
							//echo $query2." (2)<br />";
							if($result2->num_rows)
								while($row2 = $result2->fetch_row()){
									/*$query3 = "SELECT `" . $FIELDS['tags']['id_obj'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['tag'] . "` = '" . $celalalt[2] . "' AND `" . $FIELDS['tags']['obj'] . "` = 'stiri' AND `" . $FIELDS['tags']['id_obj'] . "` = '" . $row2[0] . "' LIMIT 5;";*/
									$query3 = "SELECT `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['id_obj'] . "` FROM `" . $TABLES['tags'] . "`,`" . $TABLES['userTags'] . "` WHERE `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['tag'] . "` = `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['tag'] . "` AND `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['tag'] . "` = '" . $celalalt[2] . "' AND `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['obj'] . "` = 'stiri' AND `" . $TABLES['tags'] . "`.`" . $FIELDS['tags']['id_obj'] . "` = '" . $row2[0] . "' ORDER BY `" . $TABLES['userTags'] . "`.`" . $FIELDS['userTags']['hits'] . "` DESC LIMIT 5;";
									$result3 = $conn->query($query3);
									//echo $query3." (3)<br />";
									if($result3->num_rows)
										while($row3 = $result3->fetch_row()){
											$IDS[++$ramase] = $row3[0];
											//$ramase--;
										}
									//$ramase2 = $ramase;
									//if($ramase){
										$IDS[++$ramase] = $row2[0];
										//$ramase2--;
									//}
									//$ramase = $ramase2;
								}
							//$ramase2 = $ramase;
							//if($ramase){
								$IDS[++$ramase] = $row[0];
								//$ramase2--;
							//}
							//$ramase = $ramase2;
						}
				}
		
				unset($query1,$result1,$row1,$query2,$result2,$row2,$query3,$result3,$row3);
				$IDS = eliminaDubluri($IDS);  //$IDS = resort($IDS);
				$ramase = 5 - count($IDS);
				if($ramase){
					$IDS2 = array();
					$IDS2 = self::getRandom($ramase,$where);
					$IDS = lipesteVectori($IDS,$IDS2);
				}
		}	#end if
		else
			$IDS = self::getRandom(5,$where);
		
		if($where == "stiri")
			self::afiseazaRecsStiri($IDS,$where);
		elseif($where == "foto")
			self::afiseazaRecsFoto($IDS,$where);
		else
			echo 'Hacking attempt...';
	}
	
	private function afiseazaRecsStiri($Arr,$where){
		for((int)$i = 0;$i <= count($Arr);$i++)
			if(isset($Arr[$i]))
				self::afiseaza($Arr[$i],$where);
	}
	
	public function getUserTags($where,$idUser){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$return = array();
		
		$query = "SELECT `" . $FIELDS['userTags']['tag'] . "` FROM `" . $TABLES['userTags'] . "` WHERE `" . $FIELDS['userTags']['user'] . "` =  '" . $idUser . "' AND `" . $FIELDS['userTags']['obj'] . "` = '" . $where . "' ORDER BY `" . $FIELDS['userTags']['hits'] . "` DESC LIMIT 3;";

		$result = $conn->query($query);
		if($result->num_rows){
			while($row = $result->fetch_row())
				$return[count($return)] = $row[0];
		}
		
		unset($query,$result);
		
		return $return;
	}
	
	public function getRandom($nr,$where){
		//include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		$query = "SELECT MAX(`" . $FIELDS[$where]['id'] . "`) FROM `" . $TABLES[$where] . "`";
		$row = $conn->query($query)->fetch_row();
		$maxRandom = $row[0];
		(int)$j=0; $stiri = array();
		unset($query,$row);
		for($i=1;$i<=$nr;$i++){
			$rand = rand(1,$maxRandom);
			$query = "SELECT `" . $FIELDS[$where]['id'] . "` FROM `" . $TABLES[$where] . "` WHERE `" . $FIELDS[$where]['id'] . "` = '" . $rand . "'";
			$result = $conn->query($query);
			if($result->num_rows)
				$stiri[++$j] = $rand;
			else
				$i--;
		}
		unset($i,$j,$query,$result,$rand);
		return $stiri;
	}
	
	private function afiseaza($id,$where){
		//include_once "classes/stiri.php";
		//include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		$query = "SELECT `" . $FIELDS[$where]['titlu'] . "`,`" . $FIELDS[$where]['img'] . "`,`" . $FIELDS[$where]['body'] . "` FROM `" . $TABLES[$where] . "` WHERE `" . $FIELDS[$where]['id'] . "` = '" . $id . "' LIMIT 1;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		echo '<div>' . "\n";
		echo '<a href="' . $where . '.php?id=' . $id . '">' . $row[0] . '</a>' . "\n";
		echo formatBody($row[2],50) . "\n";
		echo '</div><br />' . "\n";
		unset($query,$result,$row);
	}
} 


?>