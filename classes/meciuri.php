<?php

function ePariat($id){
	
	include_once "classes/users.php";
	$user = determinaStatus();	
	
	if($user->esteLogat()){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;

		
		$query = "SELECT `" . $FIELDS['pariuri']['id_meci'] . "` FROM `" . $TABLES['pariuri'] . "` WHERE `" . $FIELDS['pariuri']['id_meci'] . "` = '" . $id . "' AND `" . $FIELDS['pariuri']['id_user'] . "` = '" . getIdFromCookie() . "' LIMIT 1;";
		$result = $conn->query($query);
		return $result->num_rows;
	}
	else
		return 0;
}

class Meciuri{

	public function __construct($data){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		
		$query = "SELECT `" . $FIELDS['meciuri']['id_meci'] . "`,`" . $FIELDS['meciuri']['areRezultat'] . "`,`" . $FIELDS['meciuri']['dataStart'] . "`,`" . $FIELDS['meciuri']['dataSfarsit'] . "` FROM `" . $TABLES['meciuri'] . "` ORDER BY `" . $FIELDS['meciuri']['areRezultat'] . "`;";
		$result = $conn->query($query);
		
		include_once "classes/users.php";
		$poateParia = false;
		if(poateParia())
			$poateParia = true;
		if($poateParia){
			echo '	<div class="meci">' . "\n";
			echo '		<div class="echipaGazda">Echipa Gazda</div>' . "\n";
			echo '		<div class="cota1">Cota 1</div>' . "\n";
			echo '		<div class="cotaX">Cota X</div>' . "\n";
			echo '		<div class="cota2">Cota 2</div>' . "\n";
			echo '		<div class="echipaOaspete">Echipa Oaspete</div>' . "\n";
			echo '		<div class="data">Data Meciului</div>' . "\n";
			echo '		<div class="parr">Pariaza</div>' . "\n";
			echo '	</div>' . "\n\n";
		}
		
			
		while($row = $result->fetch_row()){
			if( ( (int)$row[1] == 0 ) && ($row[2] <= $data) && ($row[3] >= $data) && ($poateParia) && (!ePariat($row[0])) )
				self::afiseazaMeciDePariu($row[0]);
			elseif($row[1])
				self::afiseazaRezultatMeci($row[0]);
		}
		
		unset($query,$result);
	}
	
	private function afiseazaMeciDePariu($id){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		(string)$par = "";
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot())
			$par = "Admin";
		
		$query = "SELECT `" . $FIELDS['meciuri']['echipaGazda'] . "`,`" . $FIELDS['meciuri']['cota1'] . "`,`" . $FIELDS['meciuri']['cotaX'] . "`,`" . $FIELDS['meciuri']['cota2'] . "`,`" . $FIELDS['meciuri']['echipaOaspete'] . "`,`" . $FIELDS['meciuri']['dataMeci'] . "` FROM `" . $TABLES['meciuri'] . "` WHERE `" . $FIELDS['meciuri']['id_meci'] . "` = '" . $id . "' LIMIT 1;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		echo '	<div class="meci" id="meci' . $id . '">' . "\n";
		echo '		<div class="echipaGazda">' . $row[0] . '</div>' . "\n";
		echo '		<div class="cota1"><input type="radio" name="cota' . $id . '" id="cota' . $id . '" value="1" onClick="Pariuri.selecteazaCota(' . $id . ',\'1\',\'parMeciul' . $id . '\');" />' . $row[1] . '</div>' . "\n";
		echo '		<div class="cotaX"><input type="radio" name="cota' . $id . '" id="cota' . $id . '" value="X" onClick="Pariuri.selecteazaCota(' . $id . ',\'x\',\'parMeciul' . $id . '\');" />' . $row[2] . '</div>' . "\n";
		echo '		<div class="cota2"><input type="radio" name="cota' . $id . '" id="cota' . $id . '" value="2" onClick="Pariuri.selecteazaCota(' . $id . ',\'2\',\'parMeciul' . $id . '\');" />' . $row[3] . '</div>' . "\n";
		echo '		<div class="echipaOaspete">' . $row[4] . '</div>' . "\n";
		echo '		<div class="data">' . date("d-m-Y",$row[5]) . '</div>' . "\n";
		echo '		<div class="parr' . $par . '" id="parMeciul' . $id . '"><a href="javascript:void(0)" onClick="alert(\'Selecteaza cota!\')">Pariaza meciul</a></div>' . "\n";
		if(strlen($par))
			echo '		<div class="sterge"><a href="javascript:void(0)" onClick="Admin.stergeMeci(' . $id . ')"><img src="images/icons/cross.png" /></a><a href="acp.php?ce=adauga-rezultate&care=' . $id . '" title="Adauga rezultate la acest meci"><img src="images/icons/building_add.png" /></a></div>';
		echo '	</div>' . "\n\n";
		
		unset($query,$result);
	}
	
	private function afiseazaRezultatMeci($id){
	
		include_once "config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['meciuri']['echipaGazda'] . "`,`" . $FIELDS['meciuri']['Rezultat1'] . "`,`" . $FIELDS['meciuri']['Rezultat2'] . "`,`" . $FIELDS['meciuri']['echipaOaspete'] . "`,`" . $FIELDS['meciuri']['dataMeci'] . "` FROM `" . $TABLES['meciuri'] . "` WHERE `" . $FIELDS['meciuri']['id_meci'] . "` = '" . $id . "' LIMIT 1;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		
		(int)$diferenta = (int)$row[1] - (int)$row[2];
		
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
			
		echo '	<div class="meci" id="meci' . $id . '">' . "\n";
		echo '		<span class="' . $echipa1 . '">' . $row[0] . '</span>' . "\n";
		echo '		<span class="goluri1">' . $row[1] . '</span>' . "\n";
		echo '		<span class="despartitor">-</span>' . "\n";
		echo '		<span class="goluri2">' . $row[2] . '</span>' . "\n";
		echo '		<span class="' . $echipa2 . '">' . $row[3] . '</span>' . "\n";
		echo '		<span class="detaliiLink"><a href="javascript:void(0)" onClick="Pariuri.detaliiMeci(' . $id . '); return false;">Vezi statistica</a></span>' . "\n";
		echo '		<div class="detalii" id="meciul' . $id . '" style="display:none;"><img src="ajax.php?ce=detalii-meci&amp;care=' . $id . '" /></div>';
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot())
			echo '		<div class="sterge"><a href="javascript:void(0)" onClick="Admin.stergeMeci(' . $id . ')"><img src="images/icons/cross.png" /></a></div>';
		echo '	</div>' . "\n\n";
		
		unset($result,$query);
	}
	
	public function afiseazaDetalii($id){
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT * FROM `" . $TABLES['meciStats'] . "` WHERE `" . $FIELDS['meciStats']['id_meci'] . "` = '" . $id . "' LIMIT 1;";		$result = $conn->query($query);
		$row = $result->fetch_row();
		$goluri[1] = $row[2]; $goluri[2] = $row[3];
		$suturi[1] = $row[4]; $suturi[2] = $row[5];
		$suturiPePoarta[1] = $row[6]; $suturiPePoarta[2] = $row[7];
		$faulturi[1] = $row[8]; $faulturi[2] = $row[9];
		$cartonaseGalbene[1] = $row[10]; $cartonaseGalbene[2] = $row[11]; 
		$cartonaseRosii[1] = $row[12];	$cartonaseRosii[2] = $row[13];
		$cornere[1] = $row[14]; $cornere[2] = $row[15];
		$offsides[1] = $row[16]; $offsides[2] = $row[17];
		$posesie[1] = $row[18]; $posesie[2] = $row[19];

		self::genereazaImagine($id,$goluri,$suturi,$suturiPePoarta,$faulturi,$cartonaseGalbene,$cartonaseRosii,$cornere,$offsides,$posesie);
		
		$result->free(); unset($query,$result,$goluri,$suturi,$suturiPePoarta,$faulturi,$cartonaseGalbene,$cartonaseRosii,$cornere,$offsides,$posesie);
	}
	
	private function genereazaImagine($id_meci,$goluri,$suturi,$suturiPePoarta,$faulturi,$cartonaseGalbene,$cartonaseRosii,$cornere,$offsides,$posesie){
	
	$fisier = "images/meciuri/detalii/" . $id_meci . ".png";
	$template = "images/meciuri/detalii/template.png";
	$font = "fonts/times.ttf";
	if(!file_exists($fisier)){
		
		$image = imagecreatefrompng($template);
		$bgColor = imagecolorallocate($image,049,006,005);
		imagerectangle($image,0,0,$width,$height,$bgColor);
		$color = imagecolorallocate($image,255,255,255);
		
		imagettftext($image,20,0,50,35,$color,$font,$goluri[1]);
		imagettftext($image,20,0,492,35,$color,$font,$goluri[2]);
		
		imagettftext($image,20,0,50,60,$color,$font,$suturi[1]);
		imagettftext($image,20,0,492,60,$color,$font,$suturi[2]);
		
		imagettftext($image,20,0,50,85,$color,$font,$suturiPePoarta[1]);
		imagettftext($image,20,0,492,85,$color,$font,$suturiPePoarta[2]);
		
		imagettftext($image,20,0,50,110,$color,$font,$faulturi[1]);
		imagettftext($image,20,0,492,110,$color,$font,$faulturi[2]);
		
		imagettftext($image,20,0,50,135,$color,$font,$cartonaseGalbene[1]);
		imagettftext($image,20,0,492,135,$color,$font,$cartonaseGalbene[2]);
		
		imagettftext($image,20,0,50,160,$color,$font,$cartonaseRosii[1]);
		imagettftext($image,20,0,492,160,$color,$font,$cartonaseRosii[2]);
		
		imagettftext($image,20,0,50,185,$color,$font,$cornere[1]);
		imagettftext($image,20,0,492,185,$color,$font,$cornere[2]);
		
		imagettftext($image,20,0,50,210,$color,$font,$offsides[1]);
		imagettftext($image,20,0,492,210,$color,$font,$offsides[2]);
		
		imagettftext($image,20,0,40,235,$color,$font,$posesie[1]."%");
		imagettftext($image,20,0,482,235,$color,$font,$posesie[2]."%");
		
		//imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
		header( 'Content-type: image/png' );
		imagepng($image);
		imagepng($image,$fisier);
		imagedestroy($image);
		}
		else{
			$image = imagecreatefrompng($fisier);
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			header( 'Cache-Control: post-check=0, pre-check=0', false );
			header( 'Pragma: no-cache' );
			header( 'Content-type: image/png' );
			imagepng($image);
			imagedestroy($image);
		}
	}
}

?>