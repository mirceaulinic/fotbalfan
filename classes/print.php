<?php

function printVersion($id){

		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		(string)$like = "`" . $FIELDS['stiri']['id'] . "` = '" . $id . "'";
		$query = "SELECT * FROM `" . $TABLES['stiri'] . "` WHERE " . $like . " ORDER BY `" . $FIELDS['stiri']['data'] . "` ASC;";
		
		$result = $conn->query($query); $results = $result->num_rows;
		
		if($results == 0){
			echo '<div class="error">Cautarea nu a intors niciun rezultat!</div>';
		}
		
		else{
		
			$row = $result->fetch_row();
			echo '				<div class="stireMare">'."\n";
			(string)$img = "";
			if(trim($row[4]) == "")
				$img = "images/stiri/default.png";
			echo '					<div class="hreview">' . "\n";
			echo '						<img src="' . $row[4] . '" alt="' . htmlspecialchars(stripslashes($row[1])) . '" class="photo" />'."\n";
			echo '						<div class="item"><h3><a id="titlu' . $row[0] . '" href="stiri.php?id=' . $row[0] . '" class="fn url">' . stripslashes($row[1]) . '</a></h3></div>'."\n";
			echo '						<p id="stire' . $row[0] . '">' . "\n" . wordwrap(stripslashes($row[2]),100) ."\n";
			echo "						</p><br />\n";
			echo '						<span class="rating" style="display:none;">' . ceil( $row[6] / 16 ) . '</span>' . "\n";
			echo "					</div>";
			if(strlen($row[9]))
				echo '					<div class="proposer">Propusa de: <a href="users.php?unde=profil&user=' . $row[9] . '">' . $row[9] . '</a></div>';
			echo "				</div>\n";
		}
		
}


function pdfVersion($id){

	include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		(string)$like = "`" . $FIELDS['stiri']['id'] . "` = '" . $id . "'";
		$query = "SELECT * FROM `" . $TABLES['stiri'] . "` WHERE " . $like . " ORDER BY `" . $FIELDS['stiri']['data'] . "` " . $sort . ";";
		
		$result = $conn->query($query); $results = $result->num_rows;
		
		if($results == 0){
			echo '<div class="error">Cautarea nu a intors niciun rezultat!</div>';
		}
		
		else{
		
		$row = $result->fetch_row();
		
			include_once "classes/fpdf/fpdf.php";
			ob_clean();
			$pdf = new FPDF('P', 'in', 'Letter');
			$pdf->AddPage(  );
			$pdf->SetFont('Arial','B',24);
			$pdf->Cell(0,0,$row[1],0,0,'C');
			$pdf->SetFont('Arial','B',14);
			$max = 80;
			$randuri = ceil(strlen($row[2]) / $max);
			$rand = 0.3;
			for($i = 1; $i <= $randuri;$i++){
				$pdf->ln($rand);
				$pdf->Cell(0,0,formatBody(substr(stripslashes($row[2]),($i-1)*($max-1)),$max),0,0,'L');
			}
			$pdf->Output();
		}

}
