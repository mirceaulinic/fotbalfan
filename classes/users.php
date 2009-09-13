<?php

function showUserPanel(){
	echo 'Bun Venit, <a href="users.php?unde=profil&amp;user=' . getUserFromCookie(). '">' .getUserFromCookie() . "</a> !<br />" . "\n";
	echo '<a href="users.php?unde=profilul-meu">Schimb&#259; date personale</a><br />' . "\n";
	echo '<a href="users.php?unde=profil&amp;user=' . getUserFromCookie(). '">Profilul meu</a><br />' . "\n";
	include_once "classes/pms.php";
	echo '<a href="mesaje.php?care=necitite">Aveti ' . PM::nrMesajeNecitite(getUserFromCookie()) . ' mesaje necitite</a><br />';
	echo '<a href="bilete.php">Statistic&#259; pariuri</a><br />' . "\n";
	if(getRankFromCookie() == 2){
		
		echo '' . "\n";
		echo '<a href="propune.php?ce=stire">Propune o stire</a><br />' . "\n";
		echo '<a href="propune.php?ce=galerie">Propune o galerie</a><br />' . "\n";
		echo '<a href="propune.php?ce=newsletter">Propune un newsletter</a><br />' . "\n";
	
	}
	elseif(getRankFromCookie() == 3)
		echo '<a href="acp.php">Panoul de control al administratorului</a><br />';
	echo '<a href="users.php?unde=logout">Ie&#351;ire</a>';
}
				

function getRankFromCookie(){
	if(isset($_COOKIE['user'])){
		$cookie = $_COOKIE['user'];
		$rank = explode("|",$cookie);
		return $rank[2];
	}
	else
		return 0;
}

function getIdFromCookie(){
	if(isset($_COOKIE['user'])){
		$cookie = $_COOKIE['user'];
		$id = array();
		$id = explode("|",$cookie);
		return $id[1];
	}
	else
		return 0;
}

function getUserFromCookie(){
	if(isset($_COOKIE['user'])){
		$cookie = $_COOKIE['user'];
		$cookie2 = explode("|",$cookie);
		$cookie = $cookie2[0];
		$user = substr($cookie,0,strlen($cookie)-5);
		unset($cookie);
		return $user;
		
	}
	else
		return "Vizitator";
}

function sumaDinCont(){
	
	include_once "classes/config.php";
	global $TABLES,$FIELDS,$conn;
	
	$id = getIdFromCookie();
	$query = "SELECT `" . $FIELDS['users']['cont'] . "` FROM `" . $TABLES['users'] . "`  WHERE `" . $FIELDS['users']['id'] . "` = '" . $id . "' LIMIT 1;";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	$cont = $row[0];
	unset($query,$result,$row);
	return $cont;	
}

function profil($user){

	include_once "classes/config.php";
	global $TABLES,$FIELDS,$conn;
	
	$query = "SELECT `" . $FIELDS['users']['email'] . "`,`" . $FIELDS['users']['rank'] . "`,`" . $FIELDS['users']['lastLogin'] . "`,`" . $FIELDS['users']['dataInscr'] . "`,`" . $FIELDS['users']['posturi'] . "`,`" . $FIELDS['users']['semnatura'] . "`,`" . $FIELDS['users']['idLastBilet'] . "`,`" . $FIELDS['users']['actiuni'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['user'] . "` = '" . $user . "' LIMIT 1;";
	if($conn->query($query)->num_rows){
		$row = $conn->query($query)->fetch_row();
		return $row;
	}
	else
		return 0;
	
}

function profilMare($user){

	include_once "classes/config.php";
	global $TABLES,$FIELDS,$conn;
	
	$query = "SELECT `" . $FIELDS['users']['email'] . "`,`" . $FIELDS['users']['semnatura'] . "`,`" . $FIELDS['users']['adresa'] . "`,`" . $FIELDS['users']['cod'] . "`,`" . $FIELDS['users']['nume'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['user'] . "` = '" . $user . "' LIMIT 1;";
	return $conn->query($query)->fetch_row();

}

function sumeleMele(){

	include_once "classes/config.php";
	global $TABLES,$FIELDS,$conn;
	
	$id = getIdFromCookie();
	$query = "SELECT `" . $FIELDS['users']['cont'] . "`,`" . $FIELDS['users']['castigPosibil'] . "`,`" . $FIELDS['users']['biletulMare'] . "` FROM `" . $TABLES['users'] . "`  WHERE `" . $FIELDS['users']['id'] . "` = '" . $id . "' LIMIT 1;";

	$result = $conn->query($query);
	$row = $result->fetch_row();
	 unset($query,$result);
	return $row;

}


function poateParia(){
		
		$poateParia = false;

		$user = determinaStatus();
		if($user->poateParia())
			$poateParia = true;
		(int)$cont = sumaDinCont();
		if($cont ==  0)
			$poateParia = false;
		unset($cont);
		return $poateParia;
}

abstract class User{
	public function showStatus(){
		return "Vizitator";
	}
	public function esteLogat(){
		return false;
	}
	
	public function poateParia(){
		return false;
	}
	
	public function poateModifica(){
		return false;
	}
	
	public function poateSterge(){
		return false;
	}
	
	public function areTot(){
		return false;
	}	
}

class Vizitator extends User{
}

class userLogat extends User{
	
	public function esteLogat(){
		return true;
	}
	
	public function poateParia(){
		return true;
	}
	
	public function showStatus(){
		return getUserFromCookie();
	}
	
}

class Editor extends userLogat{
	public function poateModifica(){
		return true;
	}
	
	public function poateSterge(){
		return false;
	}
}

class Admin extends userLogat{
	public function poateModifica(){
		return true;
	}
	
	public function poateSterge(){
		return true;
	}
	
	public function areTot(){
		return true;
	}
}

function determinaStatus(){
	include_once "classes/login.php";
	if(Login::esteLogat()){
		$cuchi = getRankFromCookie();
		switch($cuchi){
		case 1:
			$obj = new userLogat();
			break;
		case 2:
			$obj = new Editor();
			break;
		case 3:
			$obj = new Admin();
			break;
		default:
			$obj = new Vizitator();
			break;
		}
	}
	else
		$obj = new Vizitator();
	
	return $obj;
}

?>