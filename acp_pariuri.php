<?php

class ACPPariuri{

	public function distribuieBani(){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			$rezultate = array();
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['users']['id'] . "`,`" . $FIELDS['users']['user'] . "` FROM `" . $TABLES['users'] . "` ORDER BY `" . $FIELDS['users']['dataInscr'] . "` DESC;"; //iau fiecare user, in ordinea inscriereii :)
			$result = $conn->query($query);
			
			while($row = $result->fetch_row()){
				
				$query2 = "SELECT `" . $TABLES['users'] . "`.`" . $FIELDS['users']['idLastBilet'] . "` FROM `" . $TABLES['users'] . "`,`" . $TABLES['pariuri'] . "` WHERE `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['id_bilet'] . "` = `" . $TABLES['users'] . "`.`" . $FIELDS['users']['idLastBilet'] . "` AND `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['id_user'] . "` = `" . $TABLES['users'] . "`.`" . $FIELDS['users']['id'] . "` AND `" . $TABLES['users'] . "`.`" . $FIELDS['users']['id'] . "` = '" . $row[0] . "' AND `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['verificat'] . "` = '0' LIMIT 1;";
				//echo $query2."<br /><br />";
				//vad daca ultimul sau bilet este verificat
				$result2 = $conn->query($query2);
				
				if($result2->num_rows){
			
					$row2 = $result2->fetch_row();
					$rezultate[] = self::verificaBilet($row[0],$row2[0],$row[1]); //trec la verificare abiletului
					unset($row2);
				}
				
				$result2->freee; unset($result2,$query2);
					
			}
			
			$result->free; unset($query,$result);
			
			$nume = pariuriPdf($rezultate);
			echo '<a href="' . $nume . '">Vezi statistica pariurilor</a>';
			unset($rezultate,$nume);
			
		}
		else
			die('');
	
	}
	
	private function verificaBilet($id_user,$id_bilet,$user_user){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		(int)$nrMeciuri = (int)$nrGasite = (int)$nrMeciuriBune = 0;
		(float)$suma = (float)$sumaPosibila = 1;
		
		$query = "SELECT `" . $FIELDS['pariuri']['id'] . "` FROM `" . $TABLES['pariuri'] . "` WHERE `" . $FIELDS['pariuri']['id_user'] . "` = '" . $id_user . "' AND `" . $FIELDS['pariuri']['id_bilet'] . "` = '" . $id_bilet . "';"; // gasesc numarul de meciuri pe care le-a pariat
		//echo $query."<br /><br />";
		(int)$nrMeciuri = $conn->query($query)->num_rows; //echo $nrMeciuri;
		
		unset($query);
		if($nrMeciuri >=3){
			$query = "SELECT `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['cota'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['Rezultat1'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['Rezultat2'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['cota1'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['cotaX'] . "`,`" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['cota2'] . "` FROM `" . $TABLES['pariuri'] . "`,`" . $TABLES['meciuri'] . "` WHERE `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['id_meci'] . "` = `" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['id_meci'] . "` AND `" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['dataSfarsit'] . "` <= '" . time() . "' AND `" . $TABLES['meciuri'] . "`.`" . $FIELDS['meciuri']['dataMeci'] . "` <= '" . time() . "' AND  `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['verificat'] . "` = '0' AND `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['id_bilet'] . "` = '" . $id_bilet . "' AND `" . $TABLES['pariuri'] . "`.`" . $FIELDS['pariuri']['id_user'] . "` = '" . $id_user . "';"; //determin numarul de meciuri la care s-a trecut de data meciului (atat de desfasurare cat si de pariere) dar verifica iarasi daca nu a fost verificat biletul(pt siguranta)
		//echo $query."<br /><br />";
			$result = $conn->query($query);
			(int)$nrGasite = $result->num_rows; //echo $nrGasite;
		
		
			if($nrGasite == $nrMeciuri){ //trec mai departe numai daca toate meciurile din bilet corespund cerintelor de mai sus 
			
				while($row = $result->fetch_row()){
			
					(int)$dif = $row[1] - $row[2];
					if($dif > 0){
						$cotaBuna = '1';
						$valCota = $row[3];
					}
					elseif($dif == 0){
						$cotaBuna = 'x';
						$valCota = $row[4];
					}
						else{
						$cotaBuna = '2'; // determin cota care ar fi fost bine sa fie aleasa, pentru ca rez meciului sa fi fost ghicit
						$valCota = $row[5];
					}
				 
					$sumaPosibila *= $valCota;
					if(strtolower($row[0]) == (string)$cotaBuna){ // vad daca cota aleasa de user este cea buna
						$nrMeciuriBune++;
						$suma *= $valCota;
					}
				
				}
			
				$result->free; unset($query,$result,$row);
			
				if($nrMeciuriBune == $nrMeciuri){ //numai daca toate meciurile sunt alese cum trebuie, biletul este declarat castigator
					$query = "SELECT `" . $FIELDS['users']['biletulMare'] . "`,`" . $FIELDS['users']['cont'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['id'] . "` = '" . $id_user . "' LIMIT 1;";
					$row = $conn->query($query)->fetch_row();
					unset($query);
					
					(float)$cont = $row[1]; 
					
					if($row[0] < $suma){
						$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['biletulMare'] . "` = '" . substr($suma,0,10) . "' WHERE `" . $FIELDS['users']['id'] . "` = '" . $id_user . "' LIMIT 1;";
						$conn->query($query);
						unset($query);
					}
					unset($row);
			
					$query = "UPDATE `" . $TABLES['pariuri'] . "` SET `" . $FIELDS['pariuri']['castigator'] . "` = '1' WHERE `" . $FIELDS['pariuri']['id_user'] . "` = '" . $id_user . "' AND `" . $FIELDS['pariuri']['id_bilet'] . "` = '" . $id_bilet . "';";
					$conn->query($query);
					unset($query);
				
					$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['cont'] . "` = `" . $FIELDS['users']['cont'] . "`+" . $suma . "  WHERE `" . $FIELDS['users']['id'] . "` = '" . $id_user . "' LIMIT 1;";
					$conn->query($query);
					unset($query);
					
					$contNew = $cont+$suma;
					
					$query = "INSERT INTO `" . $TABLES['bugete'] . "` (`" . $FIELDS['bugete']['id'] . "`,`" . $FIELDS['bugete']['idUser'] . "`,`" . $FIELDS['bugete']['cont'] . "`,`" . $FIELDS['bugete']['data'] . "`) VALUES (NULL,'" . $id_user . "','" . $contNew . "','" . time() . "');";
					$conn->query($query);
					unset($query);
					
					$query = "SELECT `" . $FIELDS['facils']['maxBet'] . "` FROM `" . $TABLES['facils'] . "` WHERE `" . $FIELDS['facils']['id'] . "` = '1' LIMIT 1;";
					$row = $conn->query($query)->fetch_row();
					$max = $row[0];
					unset($row,$query);
					if($suma > $max){
							
							$query = "UPDATE `" . $TABLES['facils'] . "` SET `" . $FIELDS['facils']['maxBet'] . "` = '" . $suma . "',`" . $FIELDS['facils']['maxUser'] . "` = '" . $user_user . "' WHERE `" . $FIELDS['facils']['id'] . "` = '1' LIMIT 1;";
							$conn->query($query);
							unset($query);
					
					}
					unset($max);
					
				}
				
				else{
					$suma = 0;
				}
				
				$query = "UPDATE `" . $TABLES['pariuri'] . "` SET `" . $FIELDS['pariuri']['verificat'] . "` = '1' WHERE `" . $FIELDS['pariuri']['id_user'] . "` = '" . $id_user . "' AND `" . $FIELDS['pariuri']['id_bilet'] . "` = '" . $id_bilet . "';";
				$conn->query($query);
				unset($query);
				
				$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['castigPosibil'] . "` = `" . $FIELDS['users']['castigPosibil'] . "`+" . $sumaPosibila . " WHERE `" . $FIELDS['users']['id'] . "` = '" . $id_user . "' LIMIT 1;";
				$conn->query($query);
				unset($query);
				
				return array($user_user,$nrMeciuri,substr($suma,0,10));
				
			}
			
		}
		
	}

}

include_once "classes/fpdf/fpdf.php";

class PDF extends FPDF{

	function BuildTable($header,$data){
		

   	 	$this->SetFillColor(103,139,66);
   		$this->SetTextColor(255,255,255);
   	 	$this->SetDrawColor(63,87,38);
   		$this->SetLineWidth(.3);
    	$this->SetFont('','B');

    	$w = array(85,40,40);

    	for($i = 0;$i < count($header);$i++)
    	    $this->Cell($w[$i],7,$header[$i],1,0,'C',1);
   	 	$this->Ln();

    	$this->SetFillColor(255,253,207);
    	$this->SetTextColor(0);
    	$this->SetFont('');

    	$fill = 0;  
    	foreach($data as $row){
       		$this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
			
        	$this->SetTextColor(0,0,0);
        	$this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);

         	$this->SetTextColor(4);
         	$this->SetFont('');
        	$this->Cell($w[2],6,$row[2],'LR',0,'C',$fill);

        	$this->Ln();
        	$fill =! $fill;
    	}
    	$this->Cell(array_sum($w),0,'','T');
	}
}

function pariuriPdf($data){
	

	$pdf = new PDF(  );


	$header=array('Utilizator','Meciuri Pariate','Suma castigata');

	$pdf->SetFont('Arial','',14);
	$pdf->AddPage(  );

	$pdf->BuildTable($header,$data);
	$nume = time() . ".pdf";
	$pdf->Output(time().".pdf");
	
	return $nume;
}

//greu,greu

?>