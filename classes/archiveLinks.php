<?php
/*
function proceseazaGet(){
	
	if(isset($_GET['where'])){
	
		if($_GET['where'] == "stiri"){
			
			if( ( (isset($_GET['time'])) && (!empty($_GET['time'])) ) && ( (isset($_GET['kre'])) && (!empty($_GET['kre'])) ) ){
			
				Arhive::returnRequest($_GET['time'] , $_GET['kre'] , "stiri");
			
			}
			
			else
				echo 'Lipsesc niste parametri!';
			
		}
		
		elseif($_GET['where'] == "meciuri"){
			
			if( ( (isset($_GET['time'])) && (!empty($_GET['time'])) ) && ( (isset($_GET['kre'])) && (!empty($_GET['kre'])) ) ){
			
				Arhive::returnRequest($_GET['time'] , $_GET['kre'] , "meciuri");
			
			}
			
			else
				echo 'Lipsesc niste parametri!';
		
		}
		
		else echo 'Cerere incorecta!';
	
	}
		
}
*/
function numesteLuna($nr){
	switch($nr){
		case 1:
			return "Ianuarie"; break;
		case 2:
			return "Februarie"; break;
		case 3:
			return "Martie"; break;
		case 4:
			return "Aprilie"; break;
		case 5:
			return "Mai"; break;
		case 6:
			return "Iunie"; break;
		case 7:
			return "Iulie"; break;
		case 8:
			return "August"; break;
		case 9:
			return "Septembrie"; break;
		case 10:
			return "Octombrie"; break;
		case 11:
			return "Noiembrie"; break;
		case 12:
			return "Decembrie"; break;
		default:
			return "!?"; break;
	}
}

abstract class Archive{
	
	public function __construct(){
		return true;
	}
	
	public function returnRequest($time,$what){
		return 1;
	}	
	
}

class Arhive extends Archive{

	public function __construct($unde){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
					
		$query = "SELECT MIN(`" . $FIELDS[$unde]['data'] . "`),MAX(`" . $FIELDS[$unde]['data'] . "`) FROM `" . $TABLES[$unde] . "` LIMIT 1;";	//determin cea mai mica, respectiv cea mai mare data
		//echo $queryInf;
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$dataInf = $row[0];
		$dataSup = $row[1];
		 unset($query,$result,$row);
		
		(int)$anulInf = date('Y',$dataInf); (int)$lunaInf = date('n',$dataInf); (int)$ziuaInf = date('d',$dataInf); 
		(int)$houInf = date('H',$dataInf); (int)$minInf = date('i',$dataInf); (int)$secInf = date('s',$dataInf);
		
		(int)$anulSup = date('Y',$dataSup); (int)$lunaSup = date('n',$dataSup); (int)$ziuaSup = date('d',$dataSup); (int)$houSup = date('H',$dataSup); (int)$minSup = date('i',$dataSup); (int)$secSup = date('s',$dataSup);
		
		echo '<ul>' . "\n";
		
		if($anulInf == $anulSup)
			for((int)$i = $lunaInf;$i <= $lunaSup;$i++){
				echo '<li class="luna"><a href="javascript:void(0)" onClick="Calendar.genCalendar(' . mktime($houInf,$minInf,$secInf,$i,$ziuaInf,$anulInf) . ',\'luna\',' . $anulInf . ',0,\'' . $unde . '\')">' . "\n";
				echo numesteLuna($i) . " " . $anulInf . "\n";
				echo '</a></li>' . "\n";
			}
		else{
			for((int)$i = $anulInf;$i < $anulSup; $i++){
				echo '<li class="anul">' ."\n";
				echo '<a href="javascript:void(0)" onClick="Calendar.genCalendar(' . mktime(0,0,0,1,1,$i) . ',\'anul\',' . $i . ',' . $anulInf . ',\'' . $unde . '\'); toggle(\'anul' . $i . '\');">' .  $i . '</a>' . "\n";
				echo '<div id="anul' . $i . '" class="anLuni" style="display:none;"></div>' . "\n";
				echo '</li>' . "\n";
			}
			
			(int)$ultimaLuna = 0;
			if($anulSup == (int)date('Y',time()))
				$ultimaLuna = date('n',time());
			else $ultimaLuna = 12;
			
			for((int)$i = 1;$i <= $ultimaLuna;$i++){
				echo '<li class="luna">' . "\n";
				echo '<a href="javascript:void(0)" onClick="Calendar.genCalendar(' . mktime($houSup,$minSup,$secSup,$i,$ziuaSup,$anulSup) . ',\'luna\',' . $anulSup . ',0,\'' . $unde . '\');">' . numesteLuna($i) . " " . $anulSup . "</a>" . "\n";
				echo '</li>' . "\n";
			}		
		}
		
		echo '</ul>' . "\n";
	}
	
	public function returnRequest($time,$what,$unde){
		if(!$time){
			exit('Hacking attempt...');
		}
		if($what == "luna"){
			//afiseaza calendar
			(int)$luna = date('n',$time);
			(int)$anul = date('Y',$time);
			(int)$azi = date("d",mktime(0, 0, 0, $luna,1,$anul));
			(int)$ziuaDeAzi = date("w",mktime(0, 0, 0, $luna,1,$anul));
			(int)$nrZileDinLuna = date("t", $time);//nr-ul de zile din luna...
			(int)$coloana = $ziuaDeAzi; //initializarile...
										//au nume suficient de explicite
										//nu necesita comantariu
			if($ziuaDeAzi == 0)
				$coloana = 7; //coloana in care va fi plasata prima zi a lunii
			
			(int)$i = (int)$j = 1;	
			
			for($i = 1;$i <= 6;$i++)
				for($j = 1;$j <= 7;$j++)
					(int)$a[$i][$j] = 0; //initializez matricea in care se va stoca calendarul
			
			(int)$aux = 0;
			
			for($i = 1;$i <=6 ;$i++){
        		for($j = $coloana;$j <= 7;$j++){ //prima data pronesc de la coloana de unde s-a gasit prima zi a lunii
          			if($aux < $nrZileDinLuna){
        				$a[$i][$j] = $aux + 1;
        				$aux++;
                    } 
                }
             $coloana = 1; // dupa accea pornim de la prima coloana, firesc
			 }   //generez calendarul
			 
			$aux = 0;
			
			for($i = 1;$i <= 7;$i++)
				if((int)$a[6][$i] == 0)
					$aux++;			 
            if($aux == 7)
				$nrLinii = 5;
			else
				$nrLinii=6;	//gasesc numarul de linii (vad daca am 7 de 0 pe ultima linie)
			
			echo '<ul>';
			 
			(string)$mata = $unde;
			 
			if(strtolower($unde) == "stiri"){
				$ce = "Stiri";
				$dat = "dataMare";
				//$timp = abs(substr(mktime(0,0,0,$luna,$a[$i][$j],$anul),0,5));
			}
			elseif(strtolower($unde) == "meciuri"){
				//$timp = abs(mktime(0,0,0,$luna,$a[$i][$j],$anul));
				$dat = "data";
				$ce = "Meciuri";
			}
			else{
				$dat = "data";
				$ce = "Foto";
			}
			(int)$hou = date('H',$time);
			(int)$min = date('i',$time);
			(int)$sec = date('s',$time);
			(int)$ziua = date('d',$time);
			
			 $timp = mktime($hou,$min,$sec,$luna,@$a[$i][$j],$anul);
			 
			for($i = 1;$i <= $nrLinii;$i++){
				echo '<li class="saptamana">' . "\n";
				echo '<ul>' . "\n";
				for($j = 1;$j <= 7;$j++){
					
					if($a[$i][$j]){
						include_once "classes/config.php";
						global $TABLES,$FIELDS,$conn;
						$query = "SELECT `" . $FIELDS[$unde]['id'] . "` FROM `" . $TABLES[$unde] . "` WHERE `" . $FIELDS[$unde][$dat] . "` BETWEEN " . abs(mktime(0,0,0,$luna,$a[$i][$j],$anul)) . " AND " . abs(mktime(23,59,59,$luna,$a[$i][$j],$anul)) . ";";
						if($conn->query($query)->num_rows)
							echo '<li class="ziua">' . "\n" . '<a href="javascript:void(0)" onClick="Calendar.afiseaza' . $ce . '(' . mktime($hou,$min,$sec,$luna,$a[$i][$j],$anul) . ')">' . $a[$i][$j] . '</a>' . "\n";					
					else
						echo '<li class="ziuaFara"><a href="javascript:void(0)">' . $a[$i][$j] . '</a>';
			
					}
					else echo '<li class="gol">' . "\n" . '<a href="#">&nbsp;</a>' . "\n";
					
					echo '</li>' . "\n";
				}
				echo '</ul>' . "\n";
				echo '</li>' . "\n";
			} //afisez matricea
			
			echo '</ul>';
		
			unset($i,$j,$a,$nrLinii,$aux,$coloana,$nrZileDinLuna,$luna,$anul,$azi,$ziuaDeAzi);
		}
		
		elseif($what == "anul"){
			$ultimaLuna = 12;
			
			(int)$anul = date('Y',$time);
			(int)$hou = date('H',$time);
			(int)$min = date('i',$time);
			(int)$sec = date('s',$time);
			(int)$ziua = date('d',$time);
			
			if((int)$anul == (int)date('Y',time()))
				$ultimaLuna = date('n',time());
			
			echo '<ul>' . "\n";
			
			for((int)$i = 1;$i <= $ultimaLuna;$i++){
				echo '<li class="luna">' . "\n";
				echo '<a href="javascript:void(0)" onClick="Calendar.genCalendar(' . mktime($hou,$min,$sec,$i,$ziua,$anul) . ',\'luna\',' . $anul . ',0,\'' . $unde . '\')">' . numesteLuna($i) . " " . $anul . "</a>" . "\n";
				echo '</li>' . "\n";
			}
			
			echo '</ul>' . "\n";
			
		}
		
		elseif($what == "ziua"){
			
			if(strtolower($unde) == "stiri"){
				include_once "classes/stiri.php";
				Stiri::showNewsByID('data',$time,"ASC",0);
			}
			elseif(strtolower($unde) == "meciuri"){
				include_once "classes/meciuri.php";
				new Meciuri($time);
			}
			else{
				include_once "classes/foto.php";
				new Foto();
			}
			
		}
		
		else
			echo 'Hacking attempt...';
	
	}
}


//proceseazaGet();

?>