<?php

function processGetFoto(){

	if(isset($_GET['id']) && is_numeric($_GET['id'])){
	
		Foto::showGaleryDetails($_GET['id']);
		
		include_once "classes/comentarii.php";
		echo '<div id="titluComm">Comentarii</div>';
		echo '<div id="comentarii">';
		Comentarii::afiseazaComentarii("foto",addslashes(@$_GET['id']),1,$sort = "DESC");
		echo '</div>';
	}
	elseif(isset($_GET['id_foto']) && is_numeric($_GET['id_foto'])){
	
		Foto::showPicture($_GET['id_foto']);
		
		include_once "classes/comentarii.php";
		echo '<div id="titluComm">Comentarii</div>';
		echo '<div id="comentarii">';
		Comentarii::afiseazaComentarii("foto_foto",addslashes(@$_GET['id_foto']),1,$sort = "DESC");
		echo '</div>';
	}
	elseif(isset($_GET['time']) && is_numeric($_GET['time'])){
		(int)$pg = 1;
		if(isset($_GET['pg']) && is_numeric($_GET['pg']))
			$pg = $_GET['pg'];
		Foto::showGaleryByTime($_GET['time'],$pg);
	}
	
	elseif(isset($_GET['tag'])){
		(int)$pg = 1;
		if(isset($_GET['pg']) && is_numeric($_GET['pg']))
			$pg = $_GET['pg'];
		Foto::showGaleryByTag(addslashes($_GET['tag']),$pg);
		
	}
	
	else{
		(int)$pg = 0; (string)$order = "ASC"; (string)$sortDupa = "data";
		if(isset($_GET['pg']) && is_numeric($_GET['pg']))
			$pg = $_GET['pg'];
		if(isset($_GET['sortDupa']))
			$sortDupa = addslashes($_GET['sortDupa']);
		if(isset($_GET['order']))
			$order = strtoupper(addslashes($_GET['order']));
		new Foto($pg,$sortDupa,$order);
	}
}


define('RezPePagGal',20);

class Foto{


	public function __construct($pg = 1,$sortDupa = 'data',$order = "ASC"){
		$nrPagini = 0;
	
		include_once "config.php";
		global $TABLES,$FIELDS,$conn;
		
		$posibile = array('data','titlu');
		if(!in_array($sortDupa,$posibile))
			$sortDupa = 'data';
		unset($posibile);	
		
		$posibile = array('ASC','DESC');
		if(!in_array($order,$posibile))
			$order = "ASC";
		unset($query);
		
		$max = RezPePagGal;
		
		$query = "SELECT `" . $FIELDS['fotoGalery']['id'] . "`,`" . $FIELDS['fotoGalery']['titlu'] . "` FROM `" . $TABLES['fotoGalery'] . "` ORDER BY `" . $FIELDS['fotoGalery'][$sortDupa] . "` " . $order . ";";
		$result = $conn->query($query);
		$nrRows = $result->num_rows;
		
		if($nrRows > $max){
			
			$result->free; unset($query,$result);
			
			$nrPagini = ceil($nrRows / $max);
			
			$limitInf = ( $pg - 1 ) * $max;
			
			$query =  "SELECT `" . $FIELDS['fotoGalery']['id'] . "`,`" . $FIELDS['fotoGalery']['titlu'] . "` FROM `" . $TABLES['fotoGalery'] . "` ORDER BY `" . $FIELDS['fotoGalery'][$sortDupa] . "` " . $order . " LIMIT " . $limitInf . "," . $max . ";";
			$result = $conn->query($query);
			
		}
		
		include_once "classes/users.php";
			$user = determinaStatus();
		
		while($row = $result->fetch_row()){
			
			(string)$admin = '';
			if($user->areTot())
				$admin = '<a href="javascript:void(0)" class="clean" onClick="if(confirm(\'Stergi aceasta galerie?\'))Admin.stergeGalerie(' . $row[0] . ')" title="Sterge Galeria"><img src="images/icons/cross.png" /></a>';
			elseif($user->poateModifica())
				$admin = '<a href="javascript:void(0)" class="clean" onClick="if(confirm(\'Popuneti stergerea galeriei ' . $row[1] . '?\'))Editor.propuneStergereGalerie(' . $row[0] . ')" title="Propune stergerea"><img src="images/icons/cancel.png" /></a> &nbsp; <a href="propune.php?ce=galerie" class="clean" title="Propune o noua galerie"><img src="images/icons/stireAdd.png" /></a>';
			echo '<div class="galery" id="gal' . $row[0] . '">' . "\n";
			echo '	<a href="foto.php?id=' . $row[0] . '">' . $row[1] . '</a>' . "\n";
			echo $admin;
			echo '</div>' . "\n";
		
		}
		
		(string)$order = '';
		if($nrPagini > 1){
			for((int)$i = 1;$i <= $nrPagini; $i++){
				if($i == @$_GET['pg'])
					$order = "&order=desc";
				echo '<a href="foto.php?pg=' . $i . $order . '" class="pagini">' . $i . '</a>' . "\n";
			}
		}
	
	}
	
	function showGalerybyTag($tag,$pg = 1){
	
		include_once "config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['tags']['id_obj'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['tag'] . "` = '" . $tag . "' AND `" . $FIELDS['tags']['obj'] . "` = 'foto' ORDER BY `" . $FIELDS['tags']['hits'] . "` DESC;";
		$result = $conn->query($query);
		$nr = $result->num_rows;
		(int)$nrPagini = 0;
		if($nr > RezPePagGal){
		
			unset($query,$result);
			$nrPagini = ceil($nr / RezPePagGal);
			$start = ($pg - 1) * RezPePagGal;
			$query = "SELECT `" . $FIELDS['tags']['id_obj'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['tag'] . "` = '" . $tag . "' AND `" . $FIELDS['tags']['obj'] . "` = 'foto' ORDER BY `" . $FIELDS['tags']['hits'] . "` DESC LIMIT " . $start . "," . RezPePagGal . ";";
			$result = $conn->query($query);
		
		}
	
		while($row = $result->fetch_row())
			self::showGalery($row[0]);
			
			
		if($nrPagini > 1)
			for($i=1;$i<=$nrPagini;$i++)
				echo '<a href="foto.php?tag=' .  $tag . '&amp;pg=' . $i . '" class="pagini">[ ' . $i .' ]</a>' . "\n" ;
			
	
	}
	
	
	public function showGaleryByTime($time,$pg = 1){
	
		include_once "config.php";
		global $FIELDS,$TABLES,$conn;
		
		$ziua = date('d',$time);
		$luna = date('m',$time);
		$anul = date('Y',$time);
		$timpInf = abs(mktime(0,0,0,$luna,$ziua,$anul));
		$timpSup = abs(mktime(23,59,59,$luna,$ziua,$anul));
		
		$query = "SELECT `" . $FIELDS['fotoGalery']['id'] . "` FROM `" . $TABLES['fotoGalery'] . "` WHERE `" . $FIELDS['fotoGalery']['data'] . "` BETWEEN " . $timpInf . " AND " . $timpSup . ";";
		$result = $conn->query($query);
		$nrs = $result->num_rows;
		(int)$nrPagini = 0;
		if($nrs > RezPePagGal){
		
			$nrPagini = ceil($nrs / RezPePagGal);
			unset($query,$result);
			$limita = ($pg - 1) * RezPePagGal;
			$query = "SELECT `" . $FIELDS['fotoGalery']['id'] . "` FROM `" . $TABLES['fotoGalery'] . "` WHERE `" . $FIELDS['fotoGalery']['data'] . "` BETWEEN " . $timpInf . " AND " . $timpSup . " LIMIT " . $limita . "," . RezPePagGal . ";";
			$result = $conn->query($query);
		
		}
		
		while($row = $result->fetch_row())
			self::showGalery($row[0]);
		
		if($nrPagini > 1)
			for($i=1;$i<=$nrPagini;$i++)
				echo '<a href="foto.php?time=' .  $time . '&amp;pg=' . $i . '" class="pagini">[ ' . $i .' ]</a>' . "\n" ;
			
		unset($query,$result,$row,$nrs,$limita);
		
	}
	
	public function showGaleryDetails($id){
	
		include_once "config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['fotoGalery']['titlu'] . "` FROM `" . $TABLES['fotoGalery'] . "` WHERE `" . $FIELDS['fotoGalery']['id'] . "` = '" . $id . "' LIMIT 1;";
		$row = $conn->query($query)->fetch_row();
		$titlu = $row[0];
		
		$query = "SELECT `" . $FIELDS['fotos']['id'] . "`,`" . $FIELDS['fotos']['src_thumb'] . "` FROM `" . $TABLES['fotos'] . "` WHERE `" . $FIELDS['fotos']['idGalery'] . "` = '" . $id . "';";
		$result = $conn->query($query);
		if($result->num_rows){
			echo '<div class="titlu">' . $titlu . '</div>';
			echo '<div class="galery">' . "\n";
			(int)$i = 0;
			
			include_once "classes/users.php";
			$user = determinaStatus();
			
			while($row = $result->fetch_row()){
		
				(string)$admin = '';
				if($user->areTot())
					$admin = '<a href="javascript:void(0)" onClick="if(confirm(\'Stergi aceasta fotografie?\'))Admin.stergeImg(' . $row[0] . ')" class="delete" title="Sterge fotografia"><img src="images/icons/cross.png" /></a><a href="acp.php?ce=adauga-foto&amp;care=' . $id . '" class="delete_sus" title="Adauga o imagina"><img src="images/icons/add.png" /></a>';
				elseif($user->poateModifica())
					$admin = '<a href="javascript:void(0)" onClick="if(confirm(\'Propuneti stergerea acestei imagini?\'))Editor.propuneStergereImg(' . $row[0] . ')" class="delete" title="Propune stergerea"><img src="images/icons/cancel.png" /></a><a href="propune.php?ce=foto&amp;care=' . $id . '" class="delete_sus" title="Propune imagine"><img src="images/icons/imgAdd.png" /></a>';
					
				if($i == 0)
					echo "		<div class=\"rand\">";
					
				echo '			<div class="coloana" id="foto' . $row[0] . '"><a href="foto.php?id_foto=' . $row[0] . '" class="fotoLink"><img src="' . $row[1] . '" /></a>' . $admin . '</div>' . "\n";
				
				
								
				$i++;
				
				if($i == 3){
					echo "			</div>";
					$i = 0;
				}
					
			}
			
			if($i > 0 && $i < 3)
				echo "			</div>";
			
			echo '	</div>';

			unset($query,$result,$row);
		}
		else
			echo 'Aceasta galerie nu exista!';
		
	}
	
	public function showPicture($id){
	
		include_once "config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['fotos']['idGalery'] . "`,`" . $FIELDS['fotos']['src'] . "` FROM `" . $TABLES['fotos'] . "` WHERE `" . $FIELDS['fotos']['id'] . "` = '" . $id . "' LIMIT 1;";
		$result = $conn->query($query);
		if($result->num_rows){
			
			$row = $result->fetch_row();
			echo '<div class="fotografie">' . "\n";
			echo '	<img src="' . $row[1] . '" />' . "\n";
			echo '</div>' . "\n";
			$id_gal = $row[0];
			unset($query,$row);
			$query = "SELECT `" . $FIELDS['fotoGalery']['titlu'] . "` FROM `" . $TABLES['fotoGalery'] . "` WHERE `" . $FIELDS['fotoGalery']['id'] . "` = '" . $id_gal . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			$titlu = $row[0]; unset($query,$row);
			echo '<a href="foto.php?id=' . $id_gal . '" id="goBack">Du-te inapoi la ' . $titlu . '</a>' . "\n";
			
			unset($result,$query);
		
		}
		else
			echo 'Aceasta fotografie nu exista!';
		
	}
	
	private function showGalery($id){
			
		include_once "classes/config.php";	
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['fotoGalery']['titlu'] . "` FROM `" . $TABLES['fotoGalery'] . "` WHERE `" . $FIELDS['fotoGalery']['id'] . "` = '" . $id . "';";
		$result = $conn->query($query);
		$row = $result->fetch_row();
			
		echo '<div class="galery">' . "\n";
		echo '	<a href="foto.php?id=' . $id . '">' . $row[0] . '</a>' . "\n";
		echo '</div>' . "\n";
			
		$result->free; unset($query,$result,$row);
			
	}
	
	public function showFotosByTag($tag,$limitaInf,$showComments = false){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
			
		$query = "SELECT `" . $FIELDS['tags']['id_obj'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['tag'] . "` = '" . $tag . "' AND `" . $FIELDS['tags']['obj'] . "` = 'foto';";
		$result = $conn->query($query);
		(int)$nr = $result->num_rows;
		(int)$nrPagini = 0;
		
		if($nr){
		
			if($nr > RezPePagGal){
			
				$result->free; unset($query,$result);
				$nrPagini = ceil( $nr / RezPePagGal );
				$query = "SELECT `" . $FIELDS['tags']['id_obj'] . "` FROM `" . $TABLES['tags'] . "` WHERE `" . $FIELDS['tags']['tag'] . "` = '" . $tag . "' AND `" . $FIELDS['tags']['obj'] . "` = 'foto' LIMIT " . $limitaInf . "," . RezPePagGal . ";";
				$result = $conn->query($query);
			}
		
			while($row = $result->fetch_row())
				self::showGalery($row[0]);
		
			
			for((int)$i = 1; $i <= $nrPagini; $i++)
				echo "pagina $i";
			
			$result->free; unset($query,$result,$row,$nrPagini,$i,$nr);
		}
		
		else
			echo 'Nu am gasit rezultate!';
	}
	
}


?>