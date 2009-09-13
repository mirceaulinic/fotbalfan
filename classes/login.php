<?php

function seteazaPrajitura($name,$value,$time){
	ob_clean();
	setcookie($name,$value,$time,"/");
}

class Login{

	private static $id = 0;
	private static $rank = 0;
	
	function __construct($user = NULL , $parola = NULL){
		if(!isset($user))
			exit(); //daca nu-i setat user-ul, n-are rost sa trec mai departe
		if(!isset($parola)){ //daca nu e setata parola, adica a ramas pe NULL,
							 //inseamna ca a fost apelata clasa doar pentru a verifica daca un
							 //user este inregistrat
			if(self::esteUser($user))
				echo '1'; //este user
			else
				echo '0'; // nu este user
		}
		else{
			$returned = self::login($user,$parola);
			switch($returned){ //vad ce returneza functia login
				case '1':
					self::trueLogin($user);
					break; //ma loghez
				case '2':
					echo 'Ne pare rau, dar acest cont nu exista!';
					break; //acest user nu exista
				case '3':
					echo 'ai ban! PA!';
					break; //acest ip arata ca si cum ar fi primit ban
				case '4':
					echo 'Ati gresit parola!';
					break;
			}
			unset($returned);
		}
	} #end function __constructor
	
	public static function esteUser($user){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$user = strtolower($user);
		$query = "SELECT `" . $FIELDS['users']['user'] . "` FROM `" . $TABLES['users'] . "` 
				  WHERE `" . $FIELDS['users']['user'] . "` = '" . $user . "' LIMIT 1;";
		$result = $conn->query($query);
		if($result->num_rows)
			$ret = 1;
		else
			$ret = 0;
		$result->free();
		return $ret;
	} #end function esteUser
	
	private function login($user,$parola){
		#va returna 1->logat
		#			2->user necunoscut
		#			3->ban pe ip
		#			4->parola gresita ---> nu poate fi altceva deoarece, verificarea de veridicitate a userului
		#								   s-a facut in prealabil
		if(self::esteUser($user)){ //vad daca exista acest cont
				include_once "classes/config.php";
				global $TABLES,$FIELDS,$conn;
				
				$user = strtolower($user);
				
				$query = "SELECT `" . $FIELDS['users']['id'] . "`,`" . $FIELDS['users']['rank'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['user'] . "` = '" . $user . "' AND `" . $FIELDS['users']['parola'] . "` = '" . encode($user,$parola) . "' LIMIT 1;";
				$result = $conn->query($query);
				if($result->num_rows){
					$row = $result->fetch_row();
					$aidi = $row[0]; $ranc = $row[1];
					self::$id = $aidi; 
					self::$rank = $ranc;
					unset($aidi,$row,$ranc);
					return 1; //daca ma pot loga fara probleme, returnez 1
				}
				else
					return 4;
			}
		else
			return 2; //daca nu este user, returnez 2
	} #end function login

	private function trueLogin($user){
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$random = substr(md5(rand()),0,5);
		$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['rand'] . "` = '" . $random . "',`" . $FIELDS['users']['lastLogin'] . "` = '" . time() . "',`" . $FIELDS['users']['lastLogout'] . "`= '0' WHERE `" . $FIELDS['users']['user'] . "` = '" . $user . "' LIMIT 1;";
		$conn->query($query); //cu acest query, schimb valoarea din fieldul random
							  //cu o noua valoare care se va gasi si in cookie
		$string = $user.$random."|".self::$id."|".self::$rank;
		$timp = time() + 172800;//un cookie poate fi valabil doua zile (2 * 24 * 60 * 60 = 172800)
		seteazaPrajitura('user',$string,$timp);//setez cookie
		//seteazaPrajitura('id',self::$id,$timp);
		//seteazaPrajitura('rank',self::$rank,$timp);
		unset($random,$query,$string,$timp);
		header("Location: index.php");
	} #end function trueLogin

	public function logout(){
			
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->esteLogat()){	
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['lastLogout'] . "` = '" . time() . "' WHERE `" . $FIELDS['users']['id'] . "` = '" . getIdFromCookie() . "' LIMIT 1;";
			$conn->query($query);
			
			$timp = time() - 172800;
			seteazaPrajitura('user',"",$timp);//setez cookie
			//seteazaPrajitura('id',0,$timp);
			//seteazaPrajitura('rank',0,$timp);
			unset($timp);
			header("Location: index.php");
		}
	}
		
	public function esteLogat(){
	
		if(isset($_COOKIE['user'])){
			$cookie = $_COOKIE['user'];
			$cookie2 = explode("|",$cookie);
			$cookie = $cookie2[0];
			$user = substr($cookie,0,strlen($cookie)-5);
			$rand = substr($cookie,strlen($cookie)-5,6);
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['users']['id'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['user'] . "` = '" . $user . "' AND `" . $FIELDS['users']['rand'] . "` = '" . $rand . "' LIMIT 1;";
			$result = $conn->query($query);
			
			if($result->num_rows)
				return true;
			else
				return false;			
		}
		else
			return false;
			
	}
	
} #end Class Login

//Login::esteLogat();
//new Login("buddha","budeishan");
//Login::esteLogat();
?>