<?php


class ACPNewsletter{


	function creazaNewsletter($titlu,$body,$data){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "INSERT INTO `" . $TABLES['newsletters'] . "` (`" . $FIELDS['newsletters']['id'] . "`,`" . $FIELDS['newsletters']['titlu'] . "`,`" . $FIELDS['newsletters']['body'] . "`,`" . $FIELDS['newsletters']['data'] . "`,`" . $FIELDS['newsletters']['dataAdaugare'] . "`) VALUES (NULL,'" . $titlu . "','" . $body . "','" . $data . "','" . time() . "');";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}
	
	function afiseazaNewslettere(){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT * FROM `" . $TABLES['newsletters'] . "` ORDER BY `" . $FIELDS['newsletters']['data'] . "` DESC;";
			$result = $conn->query($query);
			
			echo '<table width="100%" cellpadding="0" cellspacing="0" border="1">' . "\n";
			echo '	<tr>' . "\n";
			echo '		<td>Titlu</td>' . "\n";
			echo '		<td>Continut</td>' . "\n";
			echo '		<td>(Propus de)</td>' . "\n";
			echo '		<td>Data</td>' . "\n";
			echo '		<td>Data adaugarii</td>' . "\n";
			echo '		<td></td>' . "\n";
			echo '	</tr>' . "\n";
			
			while($row = $result->fetch_array()){
				
				$id =  $row[$FIELDS['newsletters']['id']];
				
				echo '	<tr>' . "\n";
				echo '		<td>' . substr(stripslashes($row[$FIELDS['newsletters']['titlu']]),0) . '</td>' . "\n";
				echo '		<td class="body">' . substr(stripslashes($row[$FIELDS['newsletters']['body']]),0) . '</td>' . "\n";
				echo '		<td>' . $row[$FIELDS['newsletters']['prop']] . '</td>' . "\n";
				echo '		<td>' . date("d m Y",$row[$FIELDS['newsletters']['data']]) . '</td>' . "\n";
				echo '		<td>' . date("d m Y",$row[$FIELDS['newsletters']['dataAdaugare']]) . '</td>' . "\n";
				if($row[$FIELDS['newsletters']['trimis']] != "1")
					echo '		<td id="newsl' . $id . '" class="send"><a href="javascript:void(0)" onClick="Admin.trimiteNewsletter(' . $id . ');"><img src="images/icons/arrow_out.png" title="Newsletter ce vrea sa fie trimis" /></a></td>' . "\n";
				else
					echo '		<td class"send"><img src="images/icons/table_go.png" title="Newsletter trimis" /></td>';
				echo '	</tr>' . "\n";
			}
			
			echo '</table>' . "\n";
			$result->free; unset($query,$result);
			
		}
		else
			die('');
	
	}
	
	function modificaInNewsletter($id_newsl,$ce,$new){
		#$ce = {'titlu','body','data' }
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "UPDATE `" . $TABLES['newsletters'] . "` SET `" . $FIELDS['newsletters'][$ce] . "` = '" . $new . "' WHERE `" . $FIELDS['newsletters']['id'] . "` = '" . $id_newsl . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
		
	}
	
	
	function afiseazaPropuneriNewsletter(){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['propuneri']['id'] . "`,`" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['titlu'] . "`,`" . $FIELDS['propuneri']['continut'] . "`,`" . $FIELDS['propuneri']['data'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['unde'] . "` = 'newsletter';";
			$result = $conn->query($query);
			
			echo '<table width="100%" cellpadding="0" cellspacing="0" border="1">' . "\n";
			echo '	<tr>' . "\n";
			echo '		<td>Titlu</td>' . "\n";
			echo '		<td>Continut</td>' . "\n";
			echo '		<td>Propus de</td>' . "\n";
			echo '		<td>Data</td>' . "\n";
			echo '		<td></td>' . "\n";
			echo '		<td></td>' . "\n";
			echo '	</tr>' . "\n";
			
			while($row = $result->fetch_array()){
			
				$id = $row[$FIELDS['propuneri']['id']];
			
				echo '	<tr id="prop' . $id . '">' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['titlu']] . '</td>' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['continut']] . '</td>' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['prop']] . '</td>' . "\n";
				echo '		<td>' . date("d m Y",$row[$FIELDS['propuneri']['data']]) . '</td>' . "\n";
				echo '		<td><a href="javascript:void(0)" onClick="Admin.vpN(' . $id . ');"><img src="images/icons/tick.png" /></a></td>' . "\n";
				echo '		<td><a href="javascript:void(0)" onClick="Admin.sN(' . $id . ');"><img src="images/icons/cross.png" /></a></td>' . "\n";
				echo '	</tr>';
			}
			
			echo '</table>';
			
			$result->free; unset($query,$result);
			
		}
		else
			die('');
	
	}
	
	function valideazaPropunereNewsletter($aidi){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['propuneri']['titlu'] . "`,`" . $FIELDS['propuneri']['continut'] . "`,`" . $FIELDS['propuneri']['data'] . "`,`" . $FIELDS['propuneri']['prop'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['unde'] . "` = 'newsletter' AND `" . $FIELDS['propuneri']['id'] . "` = '" . $aidi . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row(); echo $query;
			unset($query);
			
			
			$query = "INSERT INTO `" . $TABLES['newsletters'] . "` (`" . $FIELDS['newsletters']['id'] . "`,`" . $FIELDS['newsletters']['titlu'] . "`,`" . $FIELDS['newsletters']['body'] . "`,`" . $FIELDS['newsletters']['prop'] . "`,`" . $FIELDS['newsletters']['data'] . "`,`" . $FIELDS['newsletters']['dataAdaugare'] . "`) VALUES (NULL,'" . $row[0] . "','" . $row[1] . "','" . $row[3] . "','" . $row[2] . "','" . time() . "');"; echo $query;
			$conn->query($query);
			unset($query,$row);
			
			
			self::stergePropunereNewsletter($aidi);
			
		}
		else
			die('');
	
	}
	
	function stergePropunereNewsletter($aidi){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			
			$query = "DELETE FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['id'] . "` = '" . $aidi . "' AND `" . $FIELDS['propuneri']['unde'] . "` = 'newsletter' LIMIT 1;";
			echo $query;
			$conn->query($query);
			unset($query);
				
		}	
		else
			die('');
	
	}

	function trimiteNewsletter($id_newsl){
	
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "UPDATE `" . $TABLES['newsletters'] . "` SET `" . $FIELDS['newsletters']['trimis'] . "` = '1' WHERE `" . $FIELDS['newsletters']['id'] . "` = '" . $id_newsl . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			$query = "SELECT `" . $FIELDS['newsletters']['titlu'] . "`,`" . $FIELDS['newsletters']['body'] . "`,`" . $FIELDS['newsletters']['data'] . "` FROM `" . $TABLES['newsletters'] . "` WHERE `" . $FIELDS['newsletters']['id'] . "` = '" . $id_newsl . "' LIMIT 1;";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			
			$titlu = $row[0]; $body = $row[1]; $data = $row[2];
			
			$result->free; unset($query,$result,$row);
			
			$headere = "From: fotbal@istic.ro\r\n" . "Reply-To: fotbal@istic.ro \r\n" . "X-Mailer: PHP/" . phpversion();
			
			$query = "SELECT `" . $FIELDS['newsUsers']['email'] . "` FROM `" . $TABLES['newsUsers'] . "` ORDER BY `" . $FIELDS['newsUsers']['data'] . "`;"; // le trimit in ordinea in care s-au inscris la newsletter :)
			$result = $conn->query($query);
			while($row = $result->fetch_row()){
			
				
				try{
				
					if(!@mail($row[0],"Newsletter Fotbalfan - $titlu",$body,$headere))
						throw new Exception ("Nu s-a putut trimite mail!");
					
				
				}
				catch(Exception $ex){
					
					echo $ex->getMessage();
				
				}
					
				
			}
			
			$result->free; unset($query,$result);

			
		}
		else
			die('');
		
	}
	
}


?>