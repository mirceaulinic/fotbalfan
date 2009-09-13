<?php

class ACPMeciuri{


	function adaugaMeci($dtStart,$dtSfarsit,$echipaGazda,$echipaOaspete,$cote,$data){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
			$query = "INSERT INTO `" . $TABLES['meciuri'] . "` (`" . $FIELDS['meciuri']['id_meci'] . "`,`" . $FIELDS['meciuri']['dataStart'] . "`,`" . $FIELDS['meciuri']['dataSfarsit'] . "`,`" . $FIELDS['meciuri']['echipaGazda'] . "`,`" . $FIELDS['meciuri']['echipaOaspete'] . "`,`" . $FIELDS['meciuri']['cota1'] . "`,`" . $FIELDS['meciuri']['cotaX'] . "`,`" . $FIELDS['meciuri']['cota2'] . "`,`" . $FIELDS['meciuri']['data'] . "`,`" . $FIELDS['meciuri']['dataMeci'] . "`) VALUES (NULL,'" . $dtStart . "','" . $dtSfarsit . "','" . $echipaGazda . "','" . $echipaOaspete . "','" . $cote['1'] . "','" . $cote['X'] . "','" . $cote['2'] . "','" . time() . "','" . $data . "');";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}
	
	function introduRezultate($id_meci,$statistica){
	
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "UPDATE `" . $TABLES['meciuri'] . "` SET `" . $FIELDS['meciuri']['areRezultat'] . "` = '1',`" . $FIELDS['meciuri']['Rezultat1'] . "` = '" . $statistica['goluri'][1] . "',`" . $FIELDS['meciuri']['Rezultat2'] . "` = '" . $statistica['goluri'][2] . "' WHERE `" . $FIELDS['meciuri']['id_meci'] . "` = '" . $id_meci . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			$query = "INSERT INTO `" . $TABLES['meciStats'] . "` VALUES (NULL,'" . $id_meci . "','" . $statistica['goluri'][1] . "','" . $statistica['goluri'][2] . "','" . $statistica['suturi'][1] . "','" . $statistica['suturi'][2] . "','" . $statistica['spp'][1] . "','" . $statistica['spp'][2] . "','" . $statistica['faulturi'][1] . "','" . $statistica['faulturi'][2] . "','" . $statistica['cgalbene'][1] . "','" . $statistica['cgalbene'][2] . "','" . $statistica['crosii'][1] . "','" . $statistica['crosii'][2] . "','" . $statistica['cornere'][1] . "','" . $statistica['cornere'][2] . "','" . $statistica['offsides'][1] . "','" . $statistica['offsides'][2] . "','" . $statistica['posesie'][1] . "','" . $statistica['posesie'][2] . "');";
			#(`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`,`" .  . "`) 
			$conn->query($query); 
			
		}
		else
			die('');
	
	}
	
	function stergeMeci($aidi){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query  = "DELETE FROM `" . $TABLES['meciuri'] . "` WHERE `" . $FIELDS['meciuri']['id_meci'] . "` = '" . $aidi . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			$query = "DELETE FROM `" . $TABLES['meciStats'] . "` WHERE `" . $FIELDS['meciStats']['id_meci'] . "` = '" . $aidi . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}

}

?>