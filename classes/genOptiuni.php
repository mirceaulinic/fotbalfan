<?php

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


function afiseazaOptiuni($ce,$case,$nume){

	echo '<select name="' . $nume . '">';
	if($ce == 'ziua'){
		
		for($i=1;$i<=31;$i++){
			(string)$selected = "";
			if(isset($case))
				if($i == $case)
					$selected = " selected";
			echo '<option value="' . $i . '"' . $selected . '>' . $i . '</option>' . "\n";
		}
	}
	elseif($ce == 'luna'){
		for($i=1;$i<=12;$i++){
			(string)$selected = "";
			if(isset($case))
				if($i == $case)
					$selected = " selected";
			if(strlen($i) == 1)
				$w = "0".$i;
			else
				$w = $i;
			echo '<option value="' . $w . '"' . $selected . '>' . numesteLuna($i) . '</option>' . "\n";
		}
	}
	elseif($ce == 'anul'){
		for($i=(int)date('Y');$i<=(int)date('Y')+1;$i++){
			(string)$selected = "";
			if(isset($case))
				if($i == $case)
					$selected = " selected";
			echo '<option value="' . $i . '"' . $selected . '>' . $i . '</option>' . "\n";
		}
	}
	echo '</select>';

}

?>