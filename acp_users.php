<?php

function processGetACP(){
	
	(int)$pagina = 1;
	(string)$sort = "DESC";
	(string)$sortDupa = "user";
	$litera = NULL;
	if(isset($_GET['pg']))
		$pagina = intval($_GET['pg']);
	if(isset($_GET['sortDupa']))
		$sortDupa = addslashes($_GET['sortDupa']);
	if(isset($_GET['cum']))
		$sort = addslashes(strtoupper($_GET['cum']));
	if(isset($_GET['litera']))
		$litera = addslashes($_GET['litera']);
	echo '<div style="float:right; font-size:1.2em;">Vezi utilizatorii care incep cu litera: ' . "\n";
	ACPUsers::arataToateLiterele();
	echo '</div>' . "\n";
	ACPUsers::arataTotiUserii($pagina,$sort,$sortDupa,$litera);
	
	echo '<br /><div style="float:right">Rank:<div> 1 = user </div> <div> 2 = editor </div> <div> 3 = admin </div> </div>';
	
}

function getOrder(){
	if(isset($_GET['cum'])){
		if($_GET['cum'] == 'asc')
			return 'desc';
		else
			return 'asc';
	}
	else
		return 'asc';
}

function getGetACP(){

	(string)$return = "";
	if(isset($_GET['pg']))
		$return .= "&amp;pg=" . intval($_GET['pg']);
	if(isset($_GET['sortDupa']))
		$return .= "&amp;sortDupa=" . addslashes($_GET['sortDupa']);
	if(isset($_GET['cum']))
		$return .= "&amp;cum=" . addslashes(strtolower($_GET['cum']));
	if(isset($_GET['litera']))
		$return .= "&amp;litera=" . addslashes($_GET['litera']);
	
	return $return;
	
}

define(Rezultate,30,false);

class ACPUsers{


	public function arataTotiUserii($pagina = 1,$sort = "DESC",$sortDupa = "user",$litera = NULL){
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
			(string)$like = "";
			if(!is_null($litera))
				$like = "  WHERE `" . $FIELDS['users']['user'] . "` LIKE '" . $litera . "%'";
				
			$query = "SELECT * FROM `" . $TABLES['users'] . "`" . $like . " ORDER BY `" . $FIELDS['users'][$sortDupa] . "` " . strtoupper($sort) . ";";
			$result = $conn->query($query);
			$nrRows = $result->num_rows;
			
			(int)$nrPagini = 0;
			
			if($nrRows > Rezultate){
			
				$result->free; unset($query,$result);
				(int)$nrPagini = ceil( $nrRows / Rezultate );
				$limita = ( $pagina - 1) * Rezultate;
				$query = "SELECT * FROM `" . $TABLES['users'] . "`" . $like . " ORDER BY `" . $FIELDS['users'][$sortDupa] . "` " . strtoupper($sort) . " LIMIT " . $limita . "," . Rezultate . ";";
				$result = $conn->query($query);
				
			}
			
			echo '<table width="100%" cellpadding="0" cellspacing="0" border="1">' . "\n";
			echo "\n";
			echo '				<tr>' . "\n";
			echo '					<td><a href="acp.php?ce=users-management' . getGetACP() . '&amp;sortDupa=user&amp;cum=' . getOrder() . '">User</a></td>' . "\n";
			echo '					<td><a href="acp.php?ce=users-management' . getGetACP() . '&amp;sortDupa=email&amp;cum=' . getOrder() . '">E-mail</a></td>' . "\n";
			echo '					<td><a href="acp.php?ce=users-management' . getGetACP() . '&amp;sortDupa=rank&amp;cum=' . getOrder() . '">Rank</a></td>' . "\n";
			echo '					<td><a href="acp.php?ce=users-management' . getGetACP() . '&amp;sortDupa=nume&amp;cum=' . getOrder() . '">Nume</a></td>' . "\n";
			echo '					<td><a href="acp.php?ce=users-management' . getGetACP() . '&amp;sortDupa=adresa&amp;cum=' . getOrder() . '">Adresa</a></td>' . "\n";
			echo '					<td><a href="acp.php?ce=users-management' . getGetACP() . '&amp;sortDupa=actiuni&amp;cum=' . getOrder() . '">Punctaj</a></td>' . "\n";
			echo '					<td><a href="acp.php?ce=users-management' . getGetACP() . '&amp;sortDupa=cont&amp;cum=' . getOrder() . '">Cont</a></td>' . "\n";
			echo '					<td><a href="acp.php?ce=users-management' . getGetACP() . '&amp;sortDupa=lastLogin&amp;cum=' . getOrder() . '">Ultima aut.</a></td>' . "\n";
			echo '					<td><a href="acp.php?ce=users-management' . getGetACP() . '&amp;sortDupa=posturi&amp;cum=' . getOrder() . '">Replici<br />in forum</a></td>' . "\n";
			echo '					<td></td>';
			echo '				</tr>';
			
			while($row = $result->fetch_array())
				self::afiseazaDetaliiUser($row);
				
			echo '</table>';
			
			unset($query,$result);
			
			if($nrPagini)
				for((int)$i = $nrPagini;$i >= 1;$i--)
					echo '<a href="acp.php?ce=users-management' . getGetACP() . '&amp;pg=' . $i . '" class="pagini">' . $i . '</a>';
					
			unset($i,$nrPagini);
			
			
		}
		else
			die('');
	
	}
	
	private function afiseazaDetaliiUser($row){
	
		include_once "classes/config.php";
		global $FIELDS;
		echo "\n";
		echo '				<tr id="user' . $row[$FIELDS['users']['id']] . '">' . "\n";
		echo '					<td>' . $row[$FIELDS['users']['user']] . '</td>' . "\n";
		echo '					<td>' . $row[$FIELDS['users']['email']] . '</td>' . "\n";
		echo '					<td><span class="edd" id="rank' . $row[$FIELDS['users']['id']] . '">' . $row[$FIELDS['users']['rank']] . '</span></td>' . "\n";
		echo '					<td>' . $row[$FIELDS['users']['nume']] . '</td>' . "\n";
		echo '					<td>' . $row[$FIELDS['users']['adresa']] . '</td>' . "\n";
		echo '					<td>' . $row[$FIELDS['users']['actiuni']] . '</td>' . "\n";
		echo '					<td><span class="edd" id="cont' . $row[$FIELDS['users']['id']] . '">' . $row[$FIELDS['users']['cont']] . '</span></td>' . "\n";
		echo '					<td>' . date("d m Y",$row[$FIELDS['users']['lastLogin']]) . '</td>' . "\n";
		echo '					<td>' . $row[$FIELDS['users']['posturi']] . '</td>' . "\n";
		echo '					<td><a href="javascript:void(0)" onClick="if(confirm(\'Vrei sa stergi acest cont?\'))if(confirm(\'Sigur?\'))Admin.stergeUser(' . $row[$FIELDS['users']['id']] . ')"><img src="images/icons/cross.png" /></a></td>';
		echo '				</tr>';
		
	
	}
	
	public function arataToateLiterele(){
	
		$litere = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		for($i = 0; $i < strlen($litere);$i++)
			echo '<a href="acp.php?ce=users-management&amp;litera=' . $litere[$i] . '" class="litera">' . $litere[$i] . '</a>';
	}
	
	public function stergeUser($id_user){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "DELETE FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['id'] . "` = '" . $id_user . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			
		}
		else
			die('');
	
	
	}
	
	public function updateUser($id_user,$ce,$newCe){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			(string)$what = "";
			if($ce == 'cont')
				$what = ", `" . $FIELDS['users']['castigPosibil'] . "` = '" . $newCe . "'";
			
			$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users'][$ce] . "` = '" . $newCe . "'" . $what . " WHERE `" . $FIELDS['users']['id'] . "` = '" . $id_user . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}
	
	
	public function adaugaUser($userName,$parola,$email,$cod = NULL,$numaReal = NULL,$adresa = NULL){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/register.php";
			$_SESSION['captcha'] = 'aaaaaa';
			$register = new Register($userName,$email,$parola,$parola,5,"aaaaaa",$numeReal,$adresa,$cod);
			$register->goError();
			if($register->amErori())
				$register->showAllErrors();
				
			unset($register);
			
		}
	
	}
	
	public function updateCont($id_user,$newCont){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['cont'] . "` = `" . $FIELDS['users']['cont'] . "`+" . $newCont . " WHERE `" . $FIELDS['users']['id'] . "` = '" . $id_user . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
	
		}
		else
			die('');

	}
	
}

?>