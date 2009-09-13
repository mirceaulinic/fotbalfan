<?php


#####
#      Initializari pentru Cautare:
###################
if(!defined('RezPePagina')){
	if(isset($_SESSION['RezPePagina']))
		define('RezPePagina',$_SESSION['RezPePagina'],false);
	else
		define('RezPePagina',20,false);
}
	
(int)$lim = 0;
if((isset($_GET['pg'])) && (is_numeric($_GET['pg'])))
	$lim = ($_GET['pg'] - 1) * RezPePagina;

define('LimitaInf',$lim,false);

###################

function detFileName(){
	$filename = $_SERVER['SCRIPT_NAME'];
	$exploded = explode("/",$filename);
	return $exploded[count($exploded)-1];
}


class Search{
	
	function __construct($ce,$unde){
	
		if(gettype($unde) == "array"){
			$count = count($unde);
			for($i=0;$i<$count;$i++)
				$ret[$i] = self::suici($unde[$i],$ce);
			
			for($i=0;$i<$count;$i++){
				echo '<div class="rezultate">' . "\n";
				echo $ret[$i] . ' rezultate in ' . $unde[$i];
				if($ret[$i])
					echo ' <a href="' . detFileName() . '?ce=cautare&amp;unde=' . $unde[$i] . '&amp;cauta=' . $ce . '">&raquo;Caut&#259;</a><br />' . "\n";
				echo '</div>' . "\n";
			}
		}
		
		else
			self::suiciExec($unde,$ce);
	}
	
	
	private function suici($care,$ce){
		
		$returned = array();
		
		switch($care){
			case 'stiri':
				$returned = self::nrCautaInStiri($ce);
				break;
			case 'meciuri':
				$returned = self::nrCautaInMeciuri($ce);
				break;
			case 'foto':
				$returned = self::nrCautaInFoto($ce);
		}
		
		return $returned[0];
	}
	
	private function suiciExec($care,$ce){
		switch($care){
			case 'stiri':
				self::cautaInStiri($ce);
				break;
			case 'meciuri':
				self::cautaInMeciuri($ce);
				break;
			case 'foto':
				self::cautaInFoto($ce);
				break;
			default:
				exit('Asa ceva nu exista!');
		}
	
	}
	
	public function nrCautaInStiri($ce){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT * FROM `" . $TABLES['stiri'] . "` WHERE `" . $FIELDS['stiri']['titlu'] . "` LIKE '%" . $ce . "%' OR `" . $FIELDS['stiri']['body'] . "` LIKE '%" . $ce . "%'";
		
		$result = $conn->query($query); $nrRows = $result->num_rows;
		
		return array($nrRows,$result);
	}

	private function cautaInStiri($ce){
		
		$return = self::nrCautaInStiri($ce);
		$nrRows = $return[0]; $result = $return[1];
		
		(int)$nrPagini = 0;
		if($nrRows > RezPePagina){
			
			include_once "classes/config.php";
			global $FIELDS,$TABLES,$conn;
			
			unset($result);
			$nrPagini = ceil(  $nrRows  / RezPePagina );
			$query = "SELECT * FROM `" . $TABLES['stiri'] . "` WHERE `" . $FIELDS['stiri']['titlu'] . "` LIKE '%" . $ce . "%' OR `" . $FIELDS['stiri']['body'] . "` LIKE '%" . $ce . "%' LIMIT " . LimitaInf . ", "  . RezPePagina . ";";
			$result = $conn->query($query);
			$nrRows = $result->num_rows;
		}
		
		if(!$nrRows){
			echo 'Aceasta pagina nu exista!';
			if($nrPagini)
				echo 'Incercati sa va folositi de una din: <br />' . "\n";
		}
		$acces = "abcdefghijklmnopqrstuvwxyz";
		(int)$i = 0;
		while($row = $result->fetch_row()){
			include_once "classes/stiri.php";
			$accesKey = ' accesskey="' . $acces[$i++] . '"';
			$body = formatBody($row[2],226);
			Stiri::publica($row[0],$row[1],$body,$row[7],$row[4],false,0,$accesKey,NULL,$ce);
		}
		
		for($i = 1;$i <= $nrPagini;$i++)
			echo '<a href="' . detFileName() . '?ce=cautare&amp;unde=stiri&amp;cauta=' . $ce . '&amp;pg=' . $i . '">' . $i . '</a> ';
		
	}
	
	public function nrCautaInMeciuri($ce){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
	
		$query = "SELECT `" . $FIELDS['meciuri']['echipaGazda'] . "`,`" . $FIELDS['meciuri']['echipaOaspete'] . "`,`" . $FIELDS['meciuri']['Rezultat1'] . "`,`" . $FIELDS['meciuri']['Rezultat2'] . "`,`" . $FIELDS['meciuri']['data'] . "`,`" . $FIELDS['meciuri']['id_meci'] . "` FROM `" . $TABLES['meciuri'] . "` WHERE (`" . $FIELDS['meciuri']['echipaGazda'] . "` LIKE '%" . $ce . "%' OR `" . $FIELDS['meciuri']['echipaOaspete'] . "` LIKE '%" . $ce . "%') AND `" . $FIELDS['meciuri']['areRezultat'] . "` = '1';";
		
		$result = $conn->query($query); $nrRows = $result->num_rows;
		
		return array($nrRows,$result);
	
	}
	
	private function cautaInMeciuri($ce){
		
		$return = self::nrCautaInMeciuri($ce);
		$nrRows = $return[0]; $result = $return[1];
		
		(int)$nrPagini = 0;
		if($nrRows > RezPePagina){
			include_once "classes/config.php";
			global $FIELDS,$TABLES,$conn;
	
			unset($result);
			$nrPagini = ceil( $nrRows  / RezPePagina );
			$query = "SELECT `" . $FIELDS['meciuri']['echipaGazda'] . "`,`" . $FIELDS['meciuri']['echipaOaspete'] . "`,`" . $FIELDS['meciuri']['Rezultat1'] . "`,`" . $FIELDS['meciuri']['Rezultat2'] . "`,`" . $FIELDS['meciuri']['data'] . "`,`" . $FIELDS['meciuri']['id_meci'] . "` FROM `" . $TABLES['meciuri'] . "` WHERE (`" . $FIELDS['meciuri']['echipaGazda'] . "` LIKE '%" . $ce . "%' OR `" . $FIELDS['meciuri']['echipaOaspete'] . "` LIKE '%" . $ce . "%') AND `" . $FIELDS['meciuri']['areRezultat'] . "` = '1' LIMIT " . LimitaInf . ", "  . RezPePagina . ";";
			$result = $conn->query($query);
		    $nrRows = $result->num_rows;
		}
		
		if(!$nrRows){
			echo 'Aceasta pagina nu exista!';
			if($nrPagini)
				echo 'Incercati sa va folositi de una din: <br />' . "\n";
		}
		
		echo "\n";
		while($row = $result->fetch_row()){
			
			(int)$diferenta = (int)$row[2] - (int)$row[3];
		
			(string)$echipa1 = (string)$echipa2 = "";
			
			if($diferenta > 0){
				$echipa1 = "echipaCastigatoare";
				$echipa2 = "echipaPierzatoare";
			}
			elseif($diferenta == 0){
				$echipa1 = "remiza";
				$echipa2 = "remiza";
			}
			elseif($diferenta < 0){
				$echipa1 = "echipaPierzatoare";
				$echipa2 = "echipaCastigatoare";
			}
				
			echo '				<div class="meci">' . "\n";
			echo '					<span class="' . $echipa1 . '">' . str_replace(array(strtolower($ce),strtoupper($ce),ucfirst($ce),ucwords($ce)),array('<div class="bolder">'.strtolower($ce).'</div>','<span class="bolder">'.strtoupper($ce).'</span>','<span class="bolder">'.ucfirst($ce).'</span>','<span class="bolder">'.ucwords($ce).'</span>'),$row[0]) . '</span>' . "\n";
			echo '					<span class="goluri1">' . $row[2] . '</span>' . "\n";
			echo '					<span class="despartitor"> - </span>' . "\n";
			echo '					<span class="goluri2">' . $row[3] . '</span>' . "\n";
			echo '					<span class="' . $echipa2 . '">' . str_replace(array(strtolower($ce),strtoupper($ce),ucfirst($ce),ucwords($ce)),array('<div class="bolder">'.strtolower($ce).'</div>','<span class="bolder">'.strtoupper($ce).'</span>','<span class="bolder">'.ucfirst($ce).'</span>','<span class="bolder">'.ucwords($ce).'</span>'),$row[1]) . '</span>' . "\n";
			echo '					<span class="dataT">( ' . date("d m Y H:i",$row[4]) . ' )</span>' . "\n";
			echo '					<span class="detaliiLink"><a href="javascript:void(0)" onClick="Pariuri.detaliiMeci(' . $row[5] . '); return false;">Vezi statistica</a></span>' . "\n";
			echo '					<div class="detalii" id="meciul' . $row[5] . '" style="display:none;"><img src="ajax.php?ce=detalii-meci&amp;care=' . $row[5] . '" /></div>';
			echo '				</div>' . "\n";
		}
		
		for($i = 1;$i <= $nrPagini;$i++)
			echo '<a href="' . detFileName() . '?ce=cautare&amp;unde=meciuri&amp;cauta=' . $ce . '&amp;pg=' . $i . '">' . $i . '</a> ';
		
	}
	
	public function nrCautaInFoto($ce){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
	
		$query = "SELECT `" . $FIELDS['fotoGalery']['id'] . "`,`" . $FIELDS['fotoGalery']['titlu'] . "` FROM `" . $TABLES['fotoGalery'] . "` WHERE `" . $FIELDS['fotoGalery']['titlu'] . "` LIKE '%" . $ce . "%';";
		
		$result = $conn->query($query); $nrRows = $result->num_rows;
		
		return array($nrRows,$result);
	
	}
	
	private function cautaInFoto($ce){
		
		$return = self::nrCautaInFoto($ce);
		$nrRows = $return[0]; $result = $return[1];
		
		(int)$nrPagini = 0;
		if($nrRows > RezPePagina){
			include_once "config.php";
			global $FIELDS,$TABLES,$conn;
	
			unset($result);
			$nrPagini = ceil( $nrRows  / RezPePagina );
			$query = "SELECT `" . $FIELDS['fotoGalery']['id'] . "`,`" . $FIELDS['fotoGalery']['titlu'] . "` FROM `" . $TABLES['fotoGalery'] . "` WHERE `" . $FIELDS['fotoGalery']['titlu'] . "` LIKE '%" . $ce . "%' LIMIT " . LimitaInf . ", "  . RezPePagina . ";";
			$result = $conn->query($query);
			$nrRows = $result->num_rows;
		}
		
		if(!$nrRows){
			echo 'Aceasta pagina nu exista!';
			if($nrPagini)
				echo 'Incercati sa va folositi de una din: <br />' . "\n";
		}
		
		while($row = $result->fetch_row()){
			echo '<div class="galery">' . "\n";
			echo '	<a href="foto.php?id=' . $row[0] . '">' . $row[1] . '</a>' . "\n";
			echo '</div>' . "\n";
		}
		
		for($i = 1;$i <= $nrPagini;$i++)
			echo '<a href="' . detFileName() . '?ce=cautare&amp;unde=foto&amp;cauta=' . $ce . '&amp;pg=' . $i . '">' . $i . '</a> ';
		
	} 

}


class Abonare{
	
	public function __construct($email,$option = "abonare"){
	
		include_once "classes/register.php";
		
		if(checkMail($email)){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			if($option == "abonare"){
				
					if( !(Register::isEmailIn($TABLES['newsUsers'],$FIELDS['newsUsers']['email'],$email) ) ){
					
						$query = "INSERT INTO `" . $TABLES['newsUsers'] . "` ( `" . $FIELDS['newsUsers']['email'] . "` , `" . $FIELDS['newsUsers']['data'] . "` )
								  VALUES ('" . $email . "', '" . time() . "');";
						$result = $conn->query($query);
						echo 'Ati fost abonat!';
					}	//abonare
					else
						echo ('Sunteti deja abonat!');
						
			}
			
			elseif($option == "dezabonare"){
				
					if(Register::isEmailIn($TABLES['newsUsers'],$FIELDS['newsUsers']['email'],$email) ){
					
						$query = "DELETE FROM `" . $TABLES['newsUsers'] . "` WHERE CONVERT(`" . $FIELDS['newsUsers']['email'] . "` USING utf8) = '" . $email . "' LIMIT 1;";
						$result = $conn->query($query);
						echo 'Bravo!';
					}
					else
						echo ('Dar nu sunteti inca abonat!');
						
			}
			
			else
				exit();
			
		}
		
		else echo 'Adresa invalida!';
	
	}

}


class Contorizeaza{

	function __construct($id,$nr = 1){
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['actiuni'] . "` = `" . $FIELDS['users']['actiuni'] . "`+" . $nr . " WHERE `" . $FIELDS['users']['id'] . "` = '" . $id . "' LIMIT 1 ;";
		$conn->query($query);
		unset($query);
		return 1;
	}

}

class Trimite{

	function __construct($mailTau,$numeTau,$mailPrieten,$captcha,$type,$id_obj,$obj,$mesaj){
		
		include_once "classes/register.php";
		
		$codeNumeTau = $codeMailTau = $codeNumePrieten = $codeCaptcha = 0;
		if(strlen($numeTau) < 5)
			$codeNumeTau = 1; // incorect completat
		
		if($captcha != $_SESSION['captcha'])
			$codeCaptcha = 1;
			
		if(!checkMail($mailTau))
			$codeMailTau = 1;
			
		if(!checkMail($mailPrieten))
			$codeMailPrieten = 1;
			
		if( (!$codeNumeTau) && (!$codeNumePrieten) && (!$codeMailTau) && (!$codeMailPrieten) ){
			self::trimite($mailTau,$numeTau,$mailPrieten,$type,$id_obj,$obj,$mesaj);
		}
		else{
			self::erori($codeNumeTau,$codeEmailTau,$codeEmailPrieten,$codeCaptcha);
		}
				
	}
	public static function trimite($mailTau,$numeTau,$mailPrieten,$id_obj,$obj){
	
		$mesajFinal = "Ati primit un mail de recomandare de la " . $numeTau . " ( " . $mailTau . " ):";
		if($type == "link")
			$mesajFinal .= '<br /><a href="http://www.fotbal.istic.ro/' . $obj . '.php?id=' . $id_obj . '">Urmeaza acest link</a>';
		else{
			include_once "classes/config.php";
			global $FIELDS,$TABLES,$conn;
			$query = "SELECT `" . $FIELDS[$obj]['titlu'] . "`,`" . $FIELDS[$obj]['body'] . "` FROM `" . $TABLES[$obj] . "` WHERE `" . $FIELDS[$obj]['id'] . "` = '" . $id_obj . " LIMIT 1;";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			$mesajFinal .= '<br /><a href="http://www.fotbal.istic.ro/' . $obj . '.php?id=' . $id_obj . '">' . $row[0] . '</a><br />' . $row[1];
			unset($query,$result,$row);
		}
		if(@mail($mailPrieten,"Mail Fotbalfan",$mesajFinal))
			echo 'Va multumim';
		else
			echo '<div class="error">Nu a putut fi trimis un mail cu mesajul dvs.</div>';
	}
	
	public function amErori(){
		return $this->erori;
	}
	
	private function erori($code1,$code2,$code3,$code4){
		if($code1)
			$arr[0] = "Nu ati completat corect campul pentru numele dvs!";
		if($code2)
			$arr[count($arr)] = "Adresa de e-mail a dvs este invalida!";
		if($code3)
			$arr[count($arr)] = "Adresa de e-mail a prietenulu este invalida!";
		if($code4)
			$arr[count($arr)] = "Nu ati completat corect campul cu textul din imagine!";
		$this->erori = count($arr);
		$temp = '<div class="error">%error%</div><br />';
		
		for($i=0;$i<count($arr);$i++)
			echo str_replace("%error%",$arr[$i],$temp);
			
	}
}

?>