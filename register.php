<?php
/*
header("Expires: Thu, 17 June 2008 10:17:17 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
 */     
function compara($string1,$string2){
	return (!strcmp($string1,$string2))?1:0;
}


function checkMail($email){
	return (ereg("^([A-Za-z0-9._]+)@([A-Za-z0-9]{2,})\.([A-Za-z]{2,4})$",$email))?1:0;
}


function checkCaptcha($captcha,$temp = NULL){
	if(!$temp)
		$temp = '<div class="error"><img class="error_img" />%error%</div>';
	if(isset($_SESSION['captcha'])){
		if($_SESSION['captcha'] != $captcha){
			$return = str_replace("%error%","Ati introdus gresit textul din imagine!",$temp);
			return $return;
		}
	}
}

function esteUser($user){
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
		return $ret;
} #end function esteUser

#####################################				
#class Register						#
#	contine:						#
#		->construct(public)			#
#		->register(private)			#
#		->goError(private)			#
#		->showAllErrors(private)	#
#		->isEmailIn(public)			#
#		->recPass(public)			#
#		->recPassConfirm(private)	#
#		->recPassReset($private)	#
#####################################		

class Register{
	protected $lungMin = 0;
	protected $response = "";
	private $arr;
	
	public function __construct($userName,$email,$parola1,$parola2,$lungMinPass = 5,$captcha,$nume,$adresa,$cod){
		include_once "classes/login.php";
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		$captcha = strtoupper($captcha);
		$codeUser = $codeEmail = $codePass = $codeCaptcha = $codeNume = $codeAdresa = $codeCod = 0; // 0 = ok
		if(!isset($userName) || (strlen($userName) < 5) || (strlen($userName) > 25))
			$codeUser = 2; // 2 = va rugam sa completati campul
			
		if(!isset($captcha))
			$codeCaptcha = 2; // nu ati compl;
		if($captcha != $_SESSION['captcha'])
			$codeCaptcha = 1; // incorect
		$this->lungMin = $lungMinPass;
		$userName = addslashes(strtolower($userName));
		$email = addslashes(strtolower($email));
		$parola1 = addslashes(strtolower($parola1));
		$parola2 = addslashes(strtolower($parola2));
		if(Login::esteUser($userName))
			$codeUser = 1; // 1 = este deja user
		if(checkMail($email)){
			if(self::isEmailIn($TABLES['users'],$FIELDS['users']['email'],$email))
				$codeEmail = 1; // 1 = email valid, dar deja existent
		}
		else
			$codeEmail = 2; // 2 = email invalid
		if(compara($parola1,$parola2)){
			if(strlen($parola1) < $lungMinPass)
				$codePass = 1; // 1 = parole identice, dar prea scurte
			elseif(levenshtein($parola1,$userName) < (strlen($userName) / 2))
				$codePass = 3; // 3 = parole prea asemanatoare cu username 
		}
		else
			$codePass = 2; // 2 = parole diferite
		if( ! ( ($codeUser) || ($codeEmail) || ($codePass) || ($codeCaptcha) ) ){
			self::register($userName,$email,$parola1,$nume,$adresa,$cod);  // daca au ramas pe 0 toate cele 3 variabile, pot inscrie userul
			$this->response = $this->arr = 1;	// returnez variabila de tin ne-array
								//response = 1 -> user inscris
										
		}
		else{
			$this->response = array($codeUser,$codeEmail,$codePass,$codeCaptcha); // returnez array cu cele 3 variabile
			self::goError(); // response = array() -> sunt erori
		}
		unset($codeUser,$codeEmail,$codePass);
	}
	
	private function register($userName,$email,$parola,$nume,$adresa,$cod){
		//include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		$query = "INSERT INTO `" . $TABLES['users'] . "` (`id`,`" . $FIELDS['users']['user'] . "`,`" . $FIELDS['users']['email'] . "`,`" . $FIELDS['users']['parola'] . "`,`" . $FIELDS['users']['adresa'] . "`,`" . $FIELDS['users']['nume'] . "`,`" . $FIELDS['users']['cod'] . "`,`" . $FIELDS['users']['dataInscr'] . "`) VALUES (NULL,'" . $userName . "','" . $email . "','" . encode($userName,$parola) . "','" . $adresa . "','" . $nume . "','" . $cod . "','" . time() . "');";
		$conn->query($query);
		ob_clean();
		header("Location: index.php");
		return 1; // inregistrarea propriu-zisa
	}
	
	private function goError(){
		$response = $this->response;
		if(strcmp(gettype($response),"Array")){
			/*
			daca e de tip array, avem erori:
			*/
			$this->arr = array();
			if($response[0] == 1)
				$this->arr[0] = "Acest utilizator deja exista!";
			elseif($response[0] == 2)
				$this->arr[0] = "Va rugam sa completati campul pentru utilizator!";
			
			if($response[1] == 1)
				$this->arr[count($this->arr)] = "Acest email deja exista in baza noastra de date!";
			elseif($response[1] == 2)
				$this->arr[count($this->arr)] = "Adresa de e-mail invalida!";
				
			if($response[2] == 1)
				$this->arr[count($this->arr)] = "Lungimea parolelor trebuie sa fie de minim ".$this->lungMin." caractere!";
			elseif($response[2] == 2)
				$this->arr[count($this->arr)] = "Parolele nu sunt identice!";
			elseif($response[2] == 3)
				$this->arr[count($this->arr)] = "Parola aleasa este nesigura; se aseamana prea mult cu numele de utilizator!";
		
			if($response[3] == 1)
				$this->arr[count($this->arr)] = "Ati introdus incorect textul!";
			elseif($response[3] == 2)
				$this->arr[count($this->arr)] = "Va rugam sa completati campul cu textul din imagine!";
		}
		else
			$this->arr = 1; //daca nu e de tip array (ceea ce e f. putin probabil sa se intample :) ), userul a fost inscris
	}
	
	
	public function amErori(){
		$errors = $this->arr;
		if(strcmp(gettype($errors),"Array"))
			return count($errors);
		else
			return 0;
	}
	
	
	public function showAllErrors($templ = NULL){
		$errors = $this->arr;
		if(strcmp(gettype($errors),"Array")){
			if(!$templ)
				$templ = '<div class="error">%error%</div>' . "\n";
			$nrErr = count($errors);
			for($i=0;$i<$nrErr;$i++){
				$temp = str_replace("%error%",$errors[$i],$templ);
				print($temp);
			}
		}
	}
	
	public function isEmailIn($where,$field,$email){
		include_once "classes/config.php";
		global $conn;
		$query = "SELECT `" . $field . "` FROM `" . $where . "` WHERE `" . $field . "` = '" . $email . "' LIMIT 1;";
		$result = $conn->query($query);
		if($result->num_rows)
			return 1; 
		else
			return 0;
		$result->free();
	}
	
	
	protected $recPass = "";
	private $email = "";
	
	public function recPass($user,$random = NULL){
		$return = -1;
		$confirm = self::recPassConfirm($user);
		
		switch($confirm){
			case 0:
				$recPassword = "Acest utilizator nu exista!";
				break;
			case 1:
				$return = self::recPassReset($user,$random);
				break;
			case 2:
				$recPassword = "A fost trimis un mail pentru a confirma resetarea parolei!";
				break;
			case 3:
				$recPassword = "Nu a putut fi trimis mailul pentru confirmarea de resetare a parolei. Va rugam incercati mai tarziu!";       break;
		}
		
		switch($return){
			case 0:
				$recPassword = "Nu a putut fi trimis un e-mail cu noua parola!";
				break;
			case 1:
				$recPassword = "Ati primit un mail cu noua parola!";
				break;
			case 2:
				$recPassword = "Cod aleatoriu gresit!";
			default:
				break;
		}
		unset($confirm,$return);
		return $recPassword;
	}
	
	
	private function recPassConfirm($user){
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		if(!esteUser($user)){
			return 0; //nu exista userul acesta
			exit();
		}
		//1 = confirmat
		//0 = neconfirmat
		$query = "SELECT " . $FIELDS['users']['user'] .  "," . $FIELDS['users']['email'] . " FROM " . $TABLES['users'] . " WHERE " . $FIELDS['users']['confirmResetPass'] . ' = "1" AND ' . $FIELDS['users']['user'] . ' = "' . $user . '" LIMIT 1;' ;
		$result = $conn->query($query); unset($query);
		if($result->num_rows == 0){
			return 1; //este confirmat
			exit();
		}
		else{
			$row = $result->fetch_row();
			$email = $row[1]; unset($row);
			$rand = substr(md5(0,24000),0,5);
			$mesaj = 'Pentru a confirma schimbarea parolei apasati <a href="confirm.php?ce=recpass&user=' .$user . '"&rand=' . $rand . '>aici</a>.';
			if(@mail($email,"Confirmare Fotbalfan",$mesaj)){
				$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['confirmResetPass'] . "` = '0',`" . $FIELDS['users']['randConfirmPass'] . "` = '" . $rand . "' WHERE CONVERT( `" . $FIELDS['users']['user'] . "` USING utf8 ) = '" . $user . "' LIMIT 1;";
			$conn->query($query); unset($query);
				return 2; //inca nu a confirmat, dar a fost trimis mailul
			}
			else
				return 3; //mailul nu a putut fi trimis
		}
		$result->free;
	}
	
	private function recPassReset($user,$random){
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		$query = "SELECT `" . $FIELDS['users']['email'] . "`,`" . $FIELDS['users']['randConfirmPass'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['user'] . "`='" . $user . "' AND `" . $FIELDS['users']['randConfirmPass'] . "`='" . $random . "' LIMIT 1;";
		$result = $conn->query($query); 
		if($result->num_rows == 0){
			return 2;
			exit();
		}
		$mail = $result->fetch_row(); $email = $mail[0]; unset($mail);
		$string = substr(md5(rand(0,24000)),0,6);
		$parola = encode($user,$string);
		$mesaj = 'Noua dvs. parola pe Fotbalfan: <br /><b>' . $parola . '</b>';
		if(! ( @mail($email,"Confirmare Fotbalfan",$mesaj) ) ){
			return 0;
			exit();
		}
		$query1 = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['confirmResetPass'] . "` = '1' WHERE CONVERT( `" . $FIELDS['users']['user'] . "` USING utf8 ) = '" . $user . "' LIMIT 1;";

		$query2 = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['parola'] . "` = '" . $parola . "' WHERE CONVERT( `" . $FIELDS['users']['user'] . "` USING utf8 ) = '" . $user . "' LIMIT 1;";
		//multi query
		
		$result = $conn->query($query1);
		$result->free;
		$result = $conn->query($query2); 
		$result->free;
		return 1;
	}
}	

?>