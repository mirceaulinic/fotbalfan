<?php
#nr rez pe pagina!
define('RezPePagina',5,false);
#######


function deTitle(){

	if(isset($_GET['id']))
		 echo " &bull; " . Stiri::showNewsTitle($_GET['id']);
	elseif(isset($_GET['care'])){
		if($_GET['care'] == 'cmc')
			echo " &bull; Cele mai citite";
		elseif($_GET['care'] == 'cmr')
			echo " &bull; Cele mai recente";
		elseif($_GET['care'] == 'cma')
			echo " &bull; Cele mai apreciate";
	}

}
			

function aprox($numar){
	(string)$numar;
	$part = explode(".",$numar);
	$part1 = $part[1];
	if((int)$part1[0] < 5)
		return (int)$part[0];
	else
		return ((int)$part[0] + 1);
}

function formatBody($body,$max){
	
	if(strlen($body) <= $max)
		return $body;
		
	if($body[$max] != " "){
		for($i = $max - 1;$i >= $max - 20; $i--)
			if($body[$i] == " "){
				$newMax = $i;
				break;
			}
		$max = $newMax; unset($i,$newMax);
	}
	return substr($body,0,$max+1);
}

function getGet(){
	if(isset($_GET['tag']))
		$return =  '&amp;tag=' . $_GET['tag'];
	elseif(isset($_GET['data']))
		$return =  '&amp;data=' . $_GET['data'];
	
	if( (isset($_GET['sort'])) && ( (@$_GET['sort'] == "asc") || (@$_GET['sort'] == "desc") ) )
		$return .= "&amp;sort=" . $_GET['sort'];
	
	return $return;
}

function afiseazaStiri(){
	if( (isset($_GET['pg'])) && (is_numeric($_GET['pg'])) )
		$limitaInf = ($_GET['pg'] - 1) * RezPePagina;
	else
		$limitaInf = 0;
	
	if(@$_GET['pg'] == 1)
		$limitaInf = 0;
		
	if( (isset($_GET['sort'])) && ( (@$_GET['sort'] == "asc") || (@$_GET['sort'] == "desc") ) ){
		$sort = $_GET['sort'];
		$sort = strtoupper($sort);
	}
	else
		$sort = "ASC"; 
	
	$cauta = NULL;
	if(isset($_GET['cauta']))
		$cauta = addslashes($_GET['cauta']);
	
	
	if(isset($_GET['tag']))
		Stiri::showNewsByTag(addslashes(@$_GET['tag']),$sort,$limitaInf,false,$cauta);
	elseif(isset($_GET['data']))
		Stiri::showNewsByID('data',addslashes(@$_GET['data']),$sort,$limitaInf,false,$cauta);
	elseif(isset($_GET['id'])){
		Stiri::showNewsByID('id',addslashes(@$_GET['id']),$sort,0,true,$cauta);
		Stiri::addViews(addslashes(@$_GET['id']));
		
		include_once "classes/comentarii.php";
		echo '<div id="titluComm">Comentarii</div>';
		echo '<div id="comentarii">';
		Comentarii::afiseazaComentarii("stiri",addslashes(@$_GET['id']),1,$sort = "DESC");
		echo '</div>';
	}
	else{
	(int)$pg = 1; 
	if(isset($_GET['pg']) && is_numeric($_GET['pg']))
		$pg = $_GET['pg'];
	
		if(isset($_GET['care'])){
			if($_GET['care'] == 'cmc' || $_GET['care'] == 'views')
				Stiri::showLastNNews('views',$pg);
			elseif($_GET['care'] == 'cmr' || $_GET['care'] == 'data')
				Stiri::showLastNNews('data',$pg);
			elseif($_GET['care'] == 'cma' || $_GET['media'] == 'views')
				Stiri::showLastNNews('media',$pg);
			elseif($_GET['care'] == 'data')
				Stiri::showLastNNews('data',$pg);
			else
				echo 'Hacking attempt...';
		}
		else
			Stiri::showLastNNews();
	}
	
}

if( (RezPePagina == 0) || (!is_int(RezPePagina)) || (RezPePagina < 0))
	die('Esti prost?');

class Stiri{
	public function showNewsByID($ID,$valueID,$sort/*(sortez dupa data,dar $sort poate fi asc sau desc)*/,$limitaInf,$showAll = false,$subliniaza= NULL){
	    #$IDT = {'data','tag','id'}
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		if($ID == "data"){
			$ziua = date('d',$valueID);
			$luna = date('m',$valueID);
			$anul = date('Y',$valueID);
			$timp1 = abs(mktime(0,0,0,$luna,$ziua,$anul));
			$timp2 = abs(mktime(23,59,59,$luna,$ziua,$anul));
			(string)$like = "`" . $FIELDS['stiri']['dataMare'] . "` BETWEEN " . $timp1 . " AND " .$timp2;
		}
		else
			(string)$like = "`" . $FIELDS['stiri']['id'] . "` = '" . $valueID . "'";
		$nrPagini = 1; $results = 0;
		$query = "SELECT * FROM `" . $TABLES['stiri'] . "` WHERE " . $like . " ORDER BY `" . $FIELDS['stiri']['data'] . "` " . $sort . ";";
		
		$result = $conn->query($query); $results = $result->num_rows;
		
		if($results == 0){
			die('<div class="error">Cautarea nu a intors niciun rezultat!</div>');
		}
		
		$nrPagini = ceil($results / RezPePagina);
		if($results > RezPePagina){
			
			unset($query,$result);
			$query = "SELECT * FROM `" . $TABLES['stiri'] . "` WHERE `" . $FIELDS['stiri'][$ID] . "`" . $like . " ORDER BY `" . $FIELDS['stiri']['data'] . "` " . $sort . " LIMIT " . $limitaInf . "," . RezPePagina . ";";
			$result = $conn->query($query);
		
		}
		
		(int)$i = 0;
		$acces = "abcdefghijklmnopqrstuvwxyz";
		while($row = $result->fetch_row()){
		/*
		$row[0] = id
		$row[1] = titlu
		$row[2] = continut
		$row[3] = data
		$row[4] = img
		$row[6] = media
		$row[9] = proposer
		*/
			if(!$showAll){
			if(strlen($row[2]) > 226)
				$row[2] = formatBody($row[2],226);
			}
			//$tags = self::getTags($row[0]);
			$accesKey = ' accesskey="' . $acces[$i] . '"';
			$prop = NULL;
			if(strlen($row[9]))
				$prop = $row[9];
			self::publica($row[0],$row[1],$row[2],$row[3],$row[4],$showAll,$row[6],$accesKey,$prop,$subliniaza);
			$titlu = $row[1];
			$i++;
				
		}
		unset($acces,$accesKey,$i);
		
		
		if($nrPagini > 1)
				for($i=1;$i<=$nrPagini;$i++){
					echo '<a href="stiri.php?pg=' . $i . getGet() . '" class="';
					if($i == $_GET['pg'])
						echo 'current_';
					echo 'page">' . $i . '</a>' . "\n";
				}
	
	}
	
	public function showNewsByTag($tag,$sort,$limitaInf,$showAll = false){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT * FROM " . $TABLES['tags'] . "," . $TABLES['stiri'] . " WHERE " . $TABLES['tags'] . "." . $FIELDS['tags']['id_obj'] . " = " . $TABLES['stiri'] . "." . $FIELDS['stiri']['id'] . " AND " . $TABLES['tags'] . "." . $FIELDS['tags']['tag'] . " = '" . $tag . "' ORDER BY " . $TABLES['stiri'] . "." . $FIELDS['stiri']['data'] . " " . $sort . ";";
	
		$result = $conn->query($query); $results = $result->num_rows;
		
		if($results == 0){
			echo '<div class="error">Cautarea nu a intors niciun rezultat!</div>';
			//exit();
		}
		
		$nrPagini = ceil($results / RezPePagina);
		
		if($results > RezPePagina){
			
			unset($query,$result);
			$query = "SELECT * FROM " . $TABLES['tags'] . "," . $TABLES['stiri'] . " WHERE " . $TABLES['tags'] . "." . $FIELDS['tags']['id_obj'] . " = " . $TABLES['stiri'] . "." . $FIELDS['stiri']['id'] . " AND " . $TABLES['tags'] . "." . $FIELDS['tags']['tag'] . " = '" . $tag . "' ORDER BY " . $TABLES['stiri'] . "." . $FIELDS['stiri']['data'] . " " . $sort . " LIMIT " . $limitaInf . ", " . RezPePagina . ";";
			$result = $conn->query($query);
		}

		(int)$i = 0;
		$acces = "abcdefghijklmnopqrstuvwxyz";
		while($row = $result->fetch_row()){
		/*
		$row[3] = id
		$row[6] = titlu
		$row[7] = continut
		$row[8] = data
		$row[9] = img
		$row[11] = media
		*/
		//print_r($row);
			if(!$showAll){
				if(strlen($row[7]) > 226){
					$row[7] = formatBody($row[7],226);
				}
			}
			$prop = NULL;
			if(strlen($row[14]))
				$prop = $row[14];
			$accesKey = ' accesskey="' . $acces[$i] . '"';
			(string)$subliniaza = "";
			self::publica($row[3],$row[6],$row[7],$row[8],$row[9],$showAll,$row[11],$accesKey,$prop,$subliniaza);
			$i++;
		}
		
			
		if($nrPagini > 1)
				for($i=1;$i<=$nrPagini;$i++){
					echo '<a href="stiri.php?pg=' . $i . getGet() . '" class="';
					if($i == $_GET['pg'])
						echo 'current_';
					echo 'page">' . $i . '</a>' . "\n";
				}
	
		
	}
	
	public function getTags($id){
		//include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		$query = "SELECT `" . $FIELDS['tags']['tag'] . "`,`" . $FIELDS['tags']['hits'] . "`,`" . $FIELDS['tags']['id'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['obj'] . "` = 'stiri' AND `" . $FIELDS['tags']['id_obj'] . "` = '" . $id . "' ORDER BY `" . $FIELDS['tags']['hits'] . "` DESC LIMIT 5;";
		
		$result = $conn->query($query);
		(int)$i = 0;
		while($row = $result->fetch_row()){
			$i++;
			$tags[$i]['tag'] = $row[0];
			$tags[$i]['hits'] = $row[1];
			$tags[$i]['id'] = $row[2];
		}
		return $tags;
	}
	
	public function showNewsTitle($id){
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		$query = "SELECT `" . $FIELDS['stiri']['titlu'] . "` FROM `" . $TABLES['stiri'] . "` WHERE `" . $FIELDS['stiri']['id'] . "` = '" . $id . "' LIMIT 1;";
		$result = $conn->query($query);
		if($result->num_rows){
			$row = $result->fetch_row();
			$return = stripslashes($row[0]);
		}
		else
			$return = "Nu exista aceasta stire!";
		
		unset($result,$query,$row);
		return $return;
	}
	
	public function publica($id,$titlu,$body,$data,$img,$showTags = false,$media = 0,$accesKey = ' accesskey="a"',$propusa = NULL,$subliniaza = NULL){
		
		$carcatere = array("'",'"',"&#259;","&#351;");
		$caractere_replace = array("","","a","s");
		(string)$cauta = "";
		if(!is_null($subliniaza)){
			$body = str_replace(array($subliniaza,ucfirst($subliniaza),ucwords($subliniaza),strtoupper($subliniaza),strtolower($subliniaza)),'<span class="subliniaza">'.$subliniaza.'</span>',$body);
			$cauta = "&amp;cauta=".$subliniaza;
		}
		
		$mare = "";
		if($showTags)
			$mare = "Mare";
		
		include_once "classes/users.php";
		$user = determinaStatus();
		
		(bool)$modifica = (bool)$sterge = false;
		(string)$editabil = (string)$editabilInput = "";
		if($user->poateModifica() && $showTags){
			$modifica = true;
			$editabil = ' class="editabil"';
			$editabilInput = ' class="editabilInput"';
		}
		else{
			$editabilInput = ' class="fn url"';
		}
		if($user->poateSterge() && $showTags)
			$sterge = true;
		(string)$aHref = "";
		if(!$showTags)
			$aHref = ' href="stiri.php?id=' . $id . '"';	
		echo '				<div class="stire' . $mare .'">'."\n";
		if(trim($img) == "")
			$img = "default.png";
		echo '					<div class="hreview">' . "\n";
		echo '						<img src="' . $img . '" alt="' . htmlspecialchars(stripslashes($titlu)) . '" class="photo" />'."\n";
		echo '						<div class="item"><h3><a' . $aHref . $editabilInput . ' id="titlu' . $id . '"' . $accesKey . '>' . stripslashes($titlu) . '</a></h3></div>'."\n";
		echo '						<p id="stire' . $id . '"' . $editabil . '>' . "\n" . wordwrap(stripslashes($body),100) ."\n";
		if(!$showTags)
			echo  '<a href="stiri.php?id=' . $id . $cauta .'">[Citeste tot]</a>';
		echo "						</p><br />\n";
		if($showTags)
			echo '						<span class="rating" style="display:none;">' . ceil( $media / 16 ) . '</span>' . "\n";
		echo "					</div>";
		
		
		if($showTags){
		
			if(!is_null($propusa))
			echo '					<div class="proposer">Propusa de: <a href="users.php?unde=profil&amp;user=' . $propusa . '">' . $propusa . '</a></div>';
			
			echo '<div style="float:right;">' . "\n";
			
			if($sterge)
				echo '<a href="javascript:void(0);" onclick="if(confirm(\'Chiar vreti sa stergeti stirea?\')){Admin.stergeStire(' . $id . '); alert(\'Stirea a fost stearsa!\');}" title="Sterge stirea"><img src="images/icons/cross.png" /></a>';
			
			elseif(!$sterge && $modifica){
				echo '<a href="javascript:void(0);" onclick="if(confirm(\'Propuneti stergerea?\'))Editor.propuneStergere(' . $id . ');" title="Propune stergere"><img src="images/icons/cancel.png" /></a>';
				echo '<a href="propune.php?ce=stire" title="Propune o stire"><img src="images/icons/stireAdd.png" /></a>';
			}
			echo "</div>\n";
			
			echo '<p>D&#259; o not&#259; &#351;tirii:</p>
				 <div id="star">
					<ul id="star0" class="star" onclick="star.update(event,this,' . $id . ');" onmousemove="star.cur(event,this)" title="Voteaza!">
    					<li id="starCur0" class="curr" title="' . $media . '" style="width: ' . $media . 'px;"></li>
    				</ul>
  					<div id="starUser0" class="user">' . $media . '</div>

				</div> 
';	
		echo '<div style="float:right;"><a href="javascript:void(0)" onclick="Text.plus_text(' . $id . ')" title="Mareste dimensiunea literelor"><img src="images/icons/font_add.png" /></a>' . "\n";
		echo '<a href="javascript:void(0)" onclick="Text.minus_text(' . $id . ')" title="Micsoreaz&#259; dimensiunea literelor"><img src="images/icons/font_delete.png" /></a>' . "\n";
		echo '<a href="javascript:void(0)" onclick="Text.initial(' . $id . ')" title="Dimensiunea initial&#259;"><img src="images/icons/font.png" /></a>' . "\n";
		echo '<a href="print.php?unde=stiri-pdf&amp;id=' . $id . '" title="Vezi versiunea portabil&#259;"><img src="images/icons/pdf.png" /></a>' . "\n";
		echo '<a href="javascript:void(0)" onclick="User.trimiteStireApp()" title="Trimite cuiva stirea"><img src="images/icons/email.png" /></a>' . "\n";
		echo '<a href="print.php?unde=stiri&amp;id=' . $id . '" title="Printeaza"><img src="images/icons/printer.png" /></a>' . "\n";
		
		echo "</div>\n";
		
		//my "thickbox"
		
		echo '<div id="overlay" class="overlay" style="display:none;"></div>
			  <div id="sender" class="sender" style="display:none;">
			  	<div class="header">
					<div class="titlu">Trimite stirea</div>
   			   	 	<div class="inchizator"><a href="javascript:void(0)" onclick="User.trimiteStireApp()">Inchide</a></div>
				</div>
				<div class="div">
					<div class="title">Expeditor:</div>
					<div class="new">
						<label for="numeTau">Nume:</label><input type="text" name="numeTau" id="numeTau" onblur="User.verificaLung(\'numeTau\',\'numeTauErr\',\'numeLui\',11)" /><span id="numeTauErr"></span>
					</div>
			 		<div class="new">
						<label for="emailTau">E-mail:</label><input type="text" name="emailTau" id="emailTau" onblur="User.verificaEmail(\'emailTau\',\'emailTauErr\',\'numeLui\')" /><span id="emailTauErr"></span>
					</div>
				</div>
				<div class="div">
					<div class="title">Destinatar:</div>
					<div class="new">
						<label for="numeLui">Nume:</label><input type="text" name="numeLui" id="numeLui" onblur="User.verificaLung(\'numeLui\',\'numeLuiErr\',\'numeTau\',11)" /><span id="numeLuiErr"></span>
					</div>
			 		<div class="new">
						<label for="emailLui">E-mail:</label><input type="text" name="emailLui" id="emailLui" onblur="User.verificaEmail(\'emailLui\',\'emailLuiErr\',\'numeTau\')" /><span id="emailLuiErr"></span>
					</div>
				</div>
				<input type="button" onclick="User.trimiteStire(\'stiri\',' . $id . ')" value="Trimite" />
			 </div>';
		//
			
		}
		echo "				</div>\n\n";
		
	}
	
	public function getNLastNews(){
	
		//include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT `" . $FIELDS['facils']['lastNNews'] . "` FROM `" . $TABLES['facils'] . "` WHERE `id` = '1'";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		//echo $row[0]; 
		unset($query,$result);
		return $row[0];	
	}
	
	public function showLastNNews($dupa = 'data',$pagina = 1){
	
		#dupa = { data,views,media }
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT * FROM `" . $TABLES['stiri'] . "` ORDER BY `" . $FIELDS['stiri'][$dupa] . "` DESC;";
		$result = $conn->query($query);
		(int)$nr = $result->num_rows;
		(int)$nrPagini = 0;
		(int)$N = 1;
		$N = self::getNLastNews();
		
		if($nr > $N){
		
			unset($query,$result);
			$nrPagini = ceil($nr / $N);
			$from = ($pagina - 1) * $N; 
			$query = "SELECT * FROM `" . $TABLES['stiri'] . "` ORDER BY `" . $FIELDS['stiri'][$dupa] . "` DESC LIMIT " . $from . "," . $N;		
			$result = $conn->query($query);
		}
		
		(int)$i = 0;
		$acces = "abcdefghijklmnopqrstuvwxyz";
		while($row = $result->fetch_row()){
		
		/*
		$row[0] = id
		$row[1] = titlu
		$row[2] = continut
		$row[4] = data
		$row[5] = img
		$row[6] = media
		*/
		
			if(strlen($row[2]) > 226)
				$row[2] = formatBody($row[2],226);
			$accesKey = ' accesskey="' . $acces[$i] . '"';
			self::publica($row[0],$row[1],$row[2],$row[3],$row[4],false,$row[6],$accesKey);
			$i++;
		}
		if($i == 0)
			echo 'Niciun rezultat!';
			
		if($nrPagini){
			for((int)$i = $nrPagini; $i >= 1; $i--){
				(string)$class = 'class="pagini"';
				if($i == $pagina)
					$class = 'id="presentPg"';
				echo '<a ' . $class . ' href="stiri.php?care=' . $dupa . '&amp;pg=' . $i . '">' . $i . '</a>';
			}
		}	
				 
	}
	
	public function addViews($id){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		if(!self::aVazut('stiri',$id)){
		
			$query = "UPDATE `" . $TABLES['stiri'] . "` SET `" . $FIELDS['stiri']['views'] . "` = `" . $FIELDS['stiri']['views'] . "`+1 WHERE `" . $FIELDS['stiri']['id'] . "` = '" . $id . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			self::ilVede('stiri',$id);
			
		}
		
	}
	
	function aVazut($unde,$care){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		(bool)$return = false;
		
		$query = "SELECT `" . $FIELDS['views']['care'] . "` FROM `" . $TABLES['views'] . "` WHERE `" . $FIELDS['views']['care'] . "` = '" . $care . "' AND `" . $FIELDS['views']['unde'] . "` =  '" . $unde . "' AND `" . $FIELDS['views']['ip'] . "` = '" . ip2long($_SERVER['REMOTE_ADDR']) . "' LIMIT 1;";
		$nr = $conn->query($query)->num_rows;
		if($nr)
			$return = true;
		unset($query,$nr);
		
		return $return;
	
	}
	
	
	function ilVede($unde,$care){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "INSERT INTO `" . $TABLES['views'] . "` (`" . $FIELDS['views']['id'] . "`,`" . $FIELDS['views']['unde'] . "`,`" . $FIELDS['views']['care'] . "`,`" . $FIELDS['views']['ip'] . "`) VALUES (NULL,'" . $unde . "','" . $care . "','" . ip2long($_SERVER['REMOTE_ADDR']) . "');";
	
		$conn->query($query);
		unset($query);
	
	}
	
}

?>