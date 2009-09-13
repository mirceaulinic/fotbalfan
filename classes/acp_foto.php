<?php

class ACPFoto{


	public function addGalerie($titlu,$time = NULL){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			
			if($time == NULL)
				$time = time();
			
			
			$query = "INSERT INTO `" . $TABLES['fotoGalery'] . "` (`" . $FIELDS['fotoGalery']['id'] . "`,`" . $FIELDS['fotoGalery']['titlu'] . "`,`" . $FIELDS['fotoGalery']['data'] . "`) VALUES (NULL,'" . $titlu . "','" . $time . "');";
			$conn->query($query);
			unset($query);
			
			
			$query = "SELECT `" . $FIELDS['fotoGalery']['id'] . "` FROM `" . $TABLES['fotoGalery'] . "` WHERE `" . $FIELDS['fotoGalery']['titlu'] . "` = '" . $titlu . "' AND `" . $FIELDS['fotoGalery']['data'] . "` = '" . $time . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			$id = $row[0];
			unset($query,$row);
			
			return $id;
			
		}
		else
			die('');
			
	
	}
	
	
	public function addFotosInGalerie($id_gal,$index){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/image.php";
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			(int)$i = 1;
			
			$query = "SELECT `" . $FIELDS['fotos']['id'] . "` FROM `" . $TABLES['fotos'] . "`;";
			(int)$nr = $conn->query($query)->num_rows;
			unset($query);
			
			$saves = Image::uploadImage($index,"images/foto/",$nr,540,377,157,147);
				
			$query = "INSERT INTO `" . $TABLES['fotos'] . "` (`" . $FIELDS['fotos']['id'] . "`,`" . $FIELDS['fotos']['idGalery'] . "`,`" . $FIELDS['fotos']['src'] . "`,`" . $FIELDS['fotos']['src_thumb'] . "`) VALUES (NULL,'" . $id_gal . "','" . $saves[1] . "','" . $saves[2] . "');";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	
	}
	
	
	public function afiseazaPropuneriGalerii(){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT * FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['unde'] . "` = 'galerie';";
			$result = $conn->query($query);
			
			echo '<table width="100%" cellpadding="0" cellspacing="0" border="1">' . "\n";
			echo '	<tr>' . "\n";
			echo '		<td>Ce propune</td>' . "\n";
			echo '		<td>Pe cine / Unde</td>' . "\n";
			echo '		<td>Cine propune</td>' . "\n";
			echo '		<td>Titlu</td>' . "\n";
			echo '		<td>Imagine</td>' . "\n";
			echo '		<td></td>' . "\n";
			echo '		<td></td>' . "\n";
			echo '	</tr>' . "\n";
			unset($query);
			
			while($row = $result->fetch_array()){
				
				$id = $row[$FIELDS['propuneri']['id']];
			
				echo '	<tr id="prop' . $id . '">' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['type']] . '</td>' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['id_obj']] . '</td>' . "\n";
				echo '		<td><a href="users.php?unde=profil&amp;user=' . $row[$FIELDS['propuneri']['prop']] . '">' . $row[$FIELDS['propuneri']['prop']] . '</a></td>' . "\n";
				echo '		<td>' . $row[$FIELDS['propuneri']['titlu']] . '</td>' . "\n";
				echo '		<td></td>' . "\n";
				echo '		<td><a href="javascript:void(0)" onClick="Admin.valideazaPropunere' . ucfirst($row[$FIELDS['propuneri']['type']]) . 'Galerie(' . $id . ')" title="Valideaza propunere ' . $row[$FIELDS['propuneri']['type']] . ' galerie"><img src="images/icons/tick.png" /></a></td>' . "\n";
				echo '		<td><a href="javascript:void(0)" onClick="Admin.stergePropunere(' . $id . ')" title="Sterge propunere ' . $row[$FIELDS['propuneri']['type']] . ' galerie"><img src="images/icons/cross.png" /></a></td>' . "\n";
				echo '	</tr>';
				if($row[$FIELDS['propuneri']['type']] == 'adauga'){
					$query2 = "SELECT * FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['id_obj'] . "` = '" . $id . "' AND `" . $FIELDS['propuneri']['unde'] . "` = 'foto' AND `" . $FIELDS['propuneri']['type'] . "` = 'adaugare';";
	
					$result2 = $conn->query($query2);
					while($row2 = $result2->fetch_array()){
					
						echo '	<tr id="prop' . $id . '">' . "\n";
						echo '		<td></td>' . "\n";
						echo '		<td></td>' . "\n";
						echo '		<td></td>' . "\n";
						echo '		<td></td>' . "\n";
						echo '		<td><img src="' . $row2[$FIELDS['propuneri']['thumb']] . '" /></td>' . "\n";
						echo '		<td></td>' . "\n";
						echo '		<td></td>' . "\n";
						echo '	</tr>';
						
					
					}
					
					unset($query2,$row2,$result2);
				}
				
			}
			echo '</table>';
			
		}
	
	}
	
	
	public function valideazaPropunereAdaugareGalerie($id_prop){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['propuneri']['titlu'] . "`,`" . $FIELDS['propuneri']['data'] . "`,`" . $FIELDS['propuneri']['prop'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['unde'] . "` = 'galerie' AND `" . $FIELDS['propuneri']['id'] . "` = '" . $id_prop . "' AND `" . $FIELDS['propuneri']['type'] . "` = 'adauga' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			
			$id = self::addGalerie($row[0],$row[1]);
			
			unset($query,$row);
			
			$query = "SELECT * FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['id_obj'] . "` = '" . $id_prop . "' AND `" . $FIELDS['propuneri']['unde'] . "` = 'foto' AND `" . $FIELDS['propuneri']['type'] . "` = 'adaugare';";
			$result = $conn->query($query);
			
			while($row = $result->fetch_array()){
			
				$query2 = "INSERT INTO `" . $TABLES['fotos'] . "` (`" . $FIELDS['fotos']['id'] . "`,`" . $FIELDS['fotos']['idGalery'] . "`,`" . $FIELDS['fotos']['src'] . "`,`" . $FIELDS['fotos']['src_thumb'] . "`,`" . $FIELDS['fotos']['urcataDe'] . "`) VALUES (NULL,'" . $id . "','" . $row[$FIELDS['propuneri']['img']] . "','" . $row[$FIELDS['propuneri']['thumb']] . "','" . $row[$FIELDS['propuneri']['prop']] . "');";
				$conn->query($query2);
				unset($query2);
				
				self::stergePropunere($row[$FIELDS['propuneri']['id']]);
				
			}
			
			unset($row,$query);
			
			self::stergePropunere($id_prop);
			
			$query = "DELETE FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['id_obj'] . "` = '" . $id . "' AND `" . $FIELDS['propuneri']['unde'] . "` = 'foto' AND `" . $FIELDS['propuneri']['type'] . "` = 'adaugare';";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}
	
	public function valideazaPropunereStergereGalerie($id_prop){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['propuneri']['id_obj'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['unde'] . "` = 'galerie' AND `" . $FIELDS['propuneri']['id'] . "` = '" . $id_prop . "' AND `" . $FIELDS['propuneri']['type'] . "` = 'stergere' LIMIT 1;";
			echo $query;
			$row = $conn->query($query)->fetch_row();
			(int)$id_gal = $row[0];
			unset($row,$query);
			
			self::stergeGalerie($id_gal);
			
			self::stergePropunere($id_prop);
			
		}
		else
			die('');
	
	}

	public function stergeGalerie($id_gal){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
	
			$query = "DELETE FROM `" . $TABLES['fotoGalery'] . "` WHERE `" . $FIELDS['fotoGalery']['id'] . "` = '" . $id_gal . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			$query = "DELETE FROM `" . $TABLES['fotos'] . "` WHERE `" . $FIELDS['fotos']['idGalery'] . "` = '" . $id_gal . "';";
			$conn->query($query);
			unset($query);
			
		
		}
		else
			die('');
	
	}


	public function modificaInGalerie($unde,$ce,$new){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->poateModifica()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			
			$query = "UPDATE `" . $TABLES['fotoGalery'] . "` SET `" . $FIELDS['fotoGalery'][$ce] . "` = '" . $new . "' WHERE `" . $FIELDS['fotoGalery']['id'] . "` = '" . $unde . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}
	
	public function afiseazaPropuneriFotos($id = 0){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['propuneri']['id'] . "`,`" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['thumb'] . "`,`" . $FIELDS['propuneri']['id_obj'] . "`,`" . $FIELDS['propuneri']['type'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['unde'] . "` = 'foto';";
			$result = $conn->query($query);
			
			echo '<table width="100%" cellpadding="0" cellspacing="0" border="1">' . "\n";
			echo '	<tr>' . "\n";
			echo '		<td>Ce propune</td>' . "\n";
			echo '		<td>Pe cine / Unde</td>' . "\n";
			echo '		<td>Cine propune</td>' . "\n";
			echo '		<td>Imagine</td>' . "\n";
			echo '		<td></td>' . "\n";
			echo '		<td></td>' . "\n";
			echo '	</tr>' . "\n";
			unset($query);
			
			while($row = $result->fetch_row()){
				
				$id = $row[0];
			
				echo '	<tr id="prop' . $id . '">' . "\n";
				echo '		<td>' . $row[4] . '</td>' . "\n";
				echo '		<td>' . $row[3] . '</td>' . "\n";
				echo '		<td>' . $row[1] . '</td>' . "\n";
				echo '		<td><img src="' . $row[2] . '" /></td>' . "\n";
				echo '		<td><a href="javascript:void(0)" onClick="Admin.valideazaPropunere' . ucfirst($row[4]) . 'Foto(' . $id . ')"><img src="images/icons/tick.png" /></a></td>' . "\n";
				echo '		<td><a href="javascript:void(0)" onClick="Admin.stergePropunere(' . $id . ')"><img src="images/icons/cross.png" /></a></td>' . "\n";
				echo '	</tr>' . "\n";
			
			}
			
			echo '</table>';
			unset($query,$result,$row);
			
		}
		else
			die('');
	
	}
	
	public function valideazaPropunereAdaugareFoto($id_prop){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['propuneri']['prop'] . "`,`" . $FIELDS['propuneri']['img'] . "`,`" . $FIELDS['propuneri']['thumb'] . "`,`" . $FIELDS['propuneri']['id_obj'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['id'] . "` = '" . $id_prop . "' AND `" . $FIELDS['propuneri']['unde'] . "` = 'foto' AND `" . $FIELDS['propuneri']['type'] . "` = 'adaugare' LIMIT 1;";
		  	$row = $conn->query($query)->fetch_row();
			unset($query);
			
			$query = "INSERT INTO `" . $TABLES['fotos'] . "` (`" . $FIELDS['fotos']['id'] . "`,`" . $FIELDS['fotos']['idGalery'] . "`,`" . $FIELDS['fotos']['src'] . "`,`" . $FIELDS['fotos']['src_thumb'] . "`,`" . $FIELDS['fotos']['urcataDe'] . "`) VALUES (NULL,'" . $row[3] . "','" . $row[1] . "','" . $row[2] . "','" . $row[0] . "');";
			$conn->query($query);
			unset($query,$row);
			
			self::stergePropunere($id_prop);
				
		}
		else
			die('');
	
	}
	
	public function valideazaPropunereStergereFoto($id_prop){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['propuneri']['id_obj'] . "` FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['id'] . "` = '" . $id_prop . "' AND `" . $FIELDS['propuneri']['unde'] . "` = 'foto' AND `" . $FIELDS['propuneri']['type'] . "` = 'stergere' LIMIT 1;";
		  	$result = $conn->query($query);
			$row = $result->fetch_row();
			self::stergeFoto($row[0]);
			
			unset($row,$query);
			
			self::stergePropunere($id_prop);
			
		}
		else
			die('');
	
	}
	
	public function stergeFoto($id_foto){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "DELETE FROM `" . $TABLES['fotos'] . "` WHERE `" . $FIELDS['fotos']['id'] . "` = '" . $id_foto . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}
	
	public function stergePropunere($id_prop){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "DELETE FROM `" . $TABLES['propuneri'] . "` WHERE `" . $FIELDS['propuneri']['id'] . "` = '" . $id_prop . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}
	
	public function adaugaFotoLa($id_gal,$index = NULL){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/images.php";
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['fotos']['id'] . "` FROM `" . $TABLES['fotos'] . "`;";
			(int)$nr = $conn->query($query)->num_rows;
			unset($query);
			$nr++;
			$saves = Image::uploadImage($index,"images/foto/",$nr,500,500,100,100);
			
			$query = "INSERT INTO `" . $TABLES['fotos'] . "` (`" . $FIELDS['fotos']['id'] . "`,`" . $FIELDS['fotos']['idGalery'] . "`,`" . $FIELDS['fotos']['src'] . "`,`" . $FIELDS['fotos']['src_thumb'] . "`) VALUES (NULL,'" . $id_gal . "','" . $saves[1] . "','" . $saves[2] . "');";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	
	}
	
}


?>