<?php

function processGetMesaje(){

	include_once "classes/users.php";
	
	if(isset($_GET['care'])){
	
		if($_GET['care'] == 'necitite'){
			PM::citesteMesajePrimite('necitite',getUserFromCookie());
		}
		
		elseif($_GET['care'] == 'citite'){
			PM::citesteMesajePrimite('citite',getUserFromCookie());
		}
		
		elseif($_GET['care'] == 'trimise'){
			PM::citesteMesajeTrimise('toate',getUserFromCookie());
		}
		elseif($_GET['care'] == 'salvate'){
			PM::citesteMesajeTrimise('salvate',getUserFromCookie());
		}
		else
			header("Location: mesaje.php");
	
	}
	elseif(isset($_GET['id'])){
		PM::citeste(addslashes($_GET['id']));
	}
	elseif(isset($_GET['trimiteLa'])){
	?>
    <form action="mesaje.php?trimiteLa=<?php echo $_GET['trimiteLa']; ?>" method="post">
    <?php
		if(isset($_POST['submit'])){
			?>
            <h2>Aveti erori</h2>
            <div class="registerForm">
            <?php
			(int)$err = 0;
			
			if( (strlen($_POST['user']) < 4) || ($_POST['user'] == getUserFromCookie()) ){
				echo '<div class="error">Nu puteti trimite mesaj acestui utilizator!</div>';
				$err++;
			}
			else{
				
				include_once "classes/register.php";
				if(!esteUser(addslashes($_POST['user']))){
					echo '<div class="error">Nu exista acest utilizator!</div>';
					$err++;
				}
					
			}
			
			
			if(strlen($_POST['titlu']) < 10){
				echo '<div class="error">Titlul este prea scurt!</div>';
				$err++;
			}
			
			if(strlen($_POST['body']) < 20){
				echo '<div class="error">Mesajul este prea scurt!</div>';
				$err++;
			}
			
			if(!$err){
			
				PM::trimite(addslashes(htmlspecialchars($_POST['body'])),getUserFromCookie(),addslashes($_POST['user']),addslashes(htmlspecialchars($_POST['titlu'])));
				
			}
			
			?>
            </div>
            <?php
		}
		?>
        <h2>Trimite mesaj lui <?php echo $_GET['trimiteLa']; ?></h2>
        <div class="registerForm">

			<div class="item">
				<label for="user" class="column">Utilizator:</label><br />
				<input name="user" id="user" value="<?php if(isset($_POST['user']))echo stripslashes($_POST['user']); else echo $_GET['trimiteLa'] ?>" class="field" type="text" />
         	</div>
            <div class="item">
				<label for="titlu" class="column">Titlu:</label><br />
				<input name="titlu" id="titlu" value="<?php if(isset($_POST['titlu']))echo stripslashes($_POST['titlu']); ?>" class="field" type="text" />
         	</div>
			<div class="item">
				<label for="user" class="column">Mesaj:</label><br />
				<textarea name="body" id="body" class="textarea"><?php if(isset($_POST['body']))echo stripslashes($_POST['body']); ?></textarea>
         	</div>
            <div>
				<input value="Trimite" class="submit" type="submit" name="submit" />
			</div>
         </div>
        <?php
	?>
    </form>
    <?php
	}
	elseif(isset($_GET['lista-ignore'])){
		PM::afiseazaMyIgnore(getUserFromCookie());
	}
	else
		PM::citesteMesajePrimite('toate',getUserFromCookie());
	


}


class PM{

	
	public function trimite($mesaj,$from,$to,$titlu){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['users']['id'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['user'] . "` = '" . $to . "' LIMIT 1;";
		$result = $conn->query($query);
		$nr = $result->num_rows;
		
		if($nr){
			
			$row = $result->fetch_row();
			$id = $row[0]; $result->free; unset($query,$result,$row,$nr);
			
			$query = "SELECT `" . $FIELDS['ignore']['id'] . "` FROM `" . $TABLES['ignore'] . "` WHERE `" . $FIELDS['ignore']['from'] . "` = '" . $from . "' AND `" . $FIELDS['ignore']['to'] . "` = '" . $to . "' LIMIT 1;";
			//$result = $conn->query($query); $nr = $result->num_rows;
			$nr2 = $conn->query($query)->num_rows;
			if(!$nr2){
				$result->free; unset($query,$result);
				
				$query = "INSERT INTO `" . $TABLES['pms'] . "` (`" . $FIELDS['pms']['id'] . "`,`" . $FIELDS['pms']['from'] . "`,`" . $FIELDS['pms']['to'] . "`,`" . $FIELDS['pms']['mesaj'] . "`,`" .  $FIELDS['pms']['titlu'] . "`,`" . $FIELDS['pms']['data'] . "`) VALUES (NULL,'" . $from . "','" . $to . "','" . $mesaj . "','" . $titlu . "','" . time() . "');";

				$conn->query($query);
				unset($query);
				
				ob_clean();
				header("Location: mesaje.php?care=trimise");
				/*$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['pmsNec'] . "` = `" . $FIELDS['users']['pmsNec'] . "`+1 WHERE `" . $FIELDS['users']['id'] . "` = '" . $id . "' LIMIT 1;";
				$conn->query($query);
				unset($query);*/
			}
			else
				echo '<div class="error">Acest utilizator a decis sa nu mai primeasca mesaje din partea dvs!</div>';
		}
		else
			return 0;
	
	}
	
	public function citeste($id_pm){

		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		
		$query = "SELECT * FROM `" . $TABLES['pms'] . "` WHERE `" . $FIELDS['pms']['id'] . "` = '" . $id_pm . "' LIMIT 1;";		
		$result = $conn->query($query);
		$row = $result->fetch_array();
		
		echo '<div id="mesaje">' . "\n";
		echo '	<div id="mesajTot">' . "\n";
		echo '		<div class="titlu">Titlu: ' . $row[$FIELDS['pms']['titlu']] . '</div>' . "\n";
		echo '		<div class="from">Primit de la <a href="users.php?unde=profil&amp;user=' . $row[$FIELDS['pms']['from']] . '">' . $row[$FIELDS['pms']['from']] . '</a></div>' . "\n";
		echo '		<div class="data">&nbsp;pe ' . date("d m Y",$row[$FIELDS['pms']['data']]) . ':</div>' . "\n";
		echo '		<div class="body">' . $row[$FIELDS['pms']['mesaj']] . '</div>' . "\n";
		echo '		<div class="linkRight"><a href="mesaje.php?trimiteLa=' . $row[$FIELDS['pms']['from']] . '">Trimite-i raspuns</a></div><br /><div class="linkRight"><a href="javascript:void(0)" onClick="Pms.addIgnore(\'' .  $row[$FIELDS['pms']['from']]. '\')">Nu vreau sa mai primesc mesaje de la ' . $row[$FIELDS['pms']['from']] . '</a></div>';
		echo '	</div>';
		echo '</div>';
		
		$to = $row[$FIELDS['pms']['to']];
		$citit = $row[$FIELDS['pms']['citit']];
		$result->free; unset($query,$result,$row);
		if(!$citit){
			/*$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['pmsNec'] . "` = `" . $FIELDS['users']['pmsNec'] . "`-1 WHERE `" . $FIELDS['users']['user'] . "` = '" . $to . "' LIMIT 1;";
			$conn->query($query);
			unset($query);*/
			
			$query = "UPDATE `" . $TABLES['pms'] . "` SET `" . $FIELDS['pms']['citit'] . "` = '1' WHERE `" . $FIELDS['pms']['id'] . "` = '" . $id_pm . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
		}
	}
	
	private function afiseaza($id){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT `" . $FIELDS['pms']['from'] . "`,`" . $FIELDS['pms']['citit'] . "`,`" . $FIELDS['pms']['data'] . "` FROM `" . $TABLES['pms'] . "`,`" . $FIELDS['pms']['stare'] . "` WHERE `" . $FIELDS['pms']['id'] . "` = '" . $id . "' LIMIT 1;";
		$row = $conn->query($query)->fetch_row();
		
		
		
		unset($query,$row);
		
	}
	
	public function citesteMesajePrimite($care,$to){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT * FROM `" . $TABLES['pms'] . "` WHERE `" . $FIELDS['pms']['to'] . "` = '" . $to . "' AND `" . $FIELDS['pms']['stare'] . "` != 'netrimis'";
		
		if($care == "necitite")
			$query .= " AND `" . $FIELDS['pms']['citit'] . "` = '0'"; 
		elseif($care == "citite")
			$query .= " AND `" . $FIELDS['pms']['citit'] . "` = '1'";
		
		$query .= ";";

		$result = $conn->query($query);
		echo '<div id="mesaje">';
		
		echo '	<div class="mesaj">' . "\n";
		echo '		<div class="titlu">Titlu</div>' . "\n";
		echo '		<div class="from">Primit de la</div>' . "\n";
		echo '		<div class="data">Data</div>' . "\n";
		echo '		<div class="citit">Citit/Necitit</div>' . "\n";
		echo '		<div class="sterge">Sterge</div>' . "\n";
		echo "	</div>\n";
		
		while($row = $result->fetch_array()){
			
			if($row[$FIELDS['pms']['citit']])
				$citit = "citit";
			else
				$citit = "necitit";
			
			echo '	<div class="mesaj" id="mesaj' . $row[$FIELDS['pms']['id']] . '">' . "\n";
			echo '		<div class="titlu"><a href="mesaje.php?id=' . $row[$FIELDS['pms']['id']] . '">' . $row[$FIELDS['pms']['titlu']] . "</a></div>\n";
			echo '		<div class="from">' . $row[$FIELDS['pms']['from']] . "</div>\n";
			echo '		<div class="data">' . date("d m Y",$row[$FIELDS['pms']['data']]) . "</div>\n";
			echo '		<div class="citit">' . $citit . "</div>\n";
			echo '		<div class="sterge"><a href="javascript:void(0)" onClick="Pms.stergeMesaj(' . $row[$FIELDS['pms']['id']] . ')" title="Sterge Mesaj"><img src="images/icons/cross.png" /></a><a href="javascript:void(0)" onClick="Pms.marcheazaCaCitit(' . $row[$FIELDS['pms']['id']] . ')" title="Marcheaza ca fiind citit"><img src="images/icons/tick.png" /></a><a href="javascript:void(0)" onClick="Pms.addIgnore(\'' . $row[$FIELDS['pms']['from']] . '\')" title="Nu vreau sa mai primesc mesaje de la ' . $row[$FIELDS['pms']['from']] . '"><img src="images/icons/cancel.png" /></a></div>' ."\n";

			echo "	</div>\n";
			
		
		}
		
		echo "</div>\n";
		
		unset($result,$query);
	
	}
	
	public function citesteMesajeTrimise($care,$from){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT * FROM `" . $TABLES['pms'] . "` WHERE `" . $FIELDS['pms']['from'] . "` = '" . $from . "'";
		
		if($care == "salvate")
			$query .= " AND `" . $FIELDS['pms']['stare'] . "` = 'netrimis'";
		else
			$query .= " AND `" . $FIELDS['pms']['stare'] . "` != 'netrimis'";
			
		$query .= ";";
		
		$result = $conn->query($query);
		
		echo '<div id="mesaje">';
		
		echo '	<div class="mesaj">' . "\n";
		echo '		<div class="titlu">Titlu</div>' . "\n";
		echo '		<div class="from">Trimis lui</div>' . "\n";
		echo '		<div class="data">Data</div>' . "\n";
		echo '		<div class="citit">Citit/Necitit</div>' . "\n";
		echo '		<div class="sterge">Optiuni</div>' . "\n";
		echo "	</div>\n";
		
		while($row = $result->fetch_array()){
			
			if($row[$FIELDS['pms']['citit']])
				$citit = "citit";
			else
				$citit = "necitit";
				
			if($row[$FIELDS['pms']['stare']] == 'netrimis')
				$trimis = '<a href="javascript:void(0)" onClick="Pms.trimiteMesaj(' . $row[$FIELDS['pms']['id']] . ')">Trimite</a>';
			else
				$trimis = "";
			
			echo '	<div class="mesaj" id="mesaj' . $row[$FIELDS['pms']['id']] . '">' . "\n";
			echo '		<div class="titlu"><a href="mesaje.php?id=' . $row[$FIELDS['pms']['id']] . '">' . $row[$FIELDS['pms']['titlu']] . "</a></div>\n";
			echo '		<div class="from">' . $row[$FIELDS['pms']['to']] . "</div>\n";
			echo '		<div class="data">' . date("d m Y",$row[$FIELDS['pms']['data']]) . "</div>\n";
			echo '		<div class="citit">' . $citit . "</div>\n";
			echo '		<div class="sterge">' . $trimis . '</div>';
			echo "	</div>\n";
			
		}
		
		echo "</div>\n";
		
		$result->free;
		unset($result,$query);
	
	}
	
	public function stergeMsg($id_pm){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "DELETE FROM `" . $TABLES['pms'] . "` WHERE `" . $FIELDS['pms']['id'] . "` = '" . $id_pm . "' LIMIT 1;";
		$conn->query($query);	
		unset($query);
		
	}
	
	public function stergeMesajePrimite($care,$to){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "DELETE FROM `" . $TABLES['pms'] . "` WHERE `" . $FIELDS['pms']['to'] . "` = '" . $to . "'";
		
		if($care == "citite")
			$query .= " AND `" . $FIELDS['pms']['citit'] . "` = '1'";
		elseif($care == "necitite")
			$query .= " AND `" . $FIELDS['pms']['citit'] . "` = '0'";
		$query .= ";";
		
		$conn->query($query);
		unset($query);
	
	}
	
	public function stergeMesajeTrimise($care,$from){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "DELETE FROM `" . $TABLES['pms'] . "` WHERE `" . $FIELDS['pms']['from'] . "` = '" . $from . "'";
		if($care == "salvate")
			$query .= " AND `" . $FIELDS['pms']['stare'] . "` = 'netrimis'";
		
		$query .= ";";
		
		$conn->query($query);
		unset($query);
	
	}
	
	public function salveaza($mesaj,$from,$to){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "INSERT INTO `" . $TABLES['pms'] . "` (`" . $FIELDS['pms']['id'] . "`,`" . $FIELDS['pms']['from'] . "`,`" . $FIELDS['pms']['to'] . "`,`" . $FIELDS['pms']['mesaj'] . "`,`" . $FIELDS['pms']['data'] . "`,`" . $FIELDS['pms']['stare'] . "`) VALUES (NULL,'" . $from . "','" . $to . "','" . $mesaj . "','" . time() . "','netrimis');";
		$conn->query($query);
		unset($query);
	
	}
	
	public function trimiteMesajSalvat($id_pm){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['pms']['to'] . "` FROM `" . $TABLES['pms'] . "` WHERE `" . $FIELDS['pms']['id'] . "` = '" . $id_pm . "' AND `" . $FIELDS['pms']['stare'] . "` = 'netrimis' LIMIT 1;";
		$result = $conn->query($query);
		
		if($result->num_rows){
			$row = $result->fetch_row();
			$to = $row[0];
			$result->free; unset($query,$result,$row);
			
			$query = "UPDATE `" . $TABLES['pms'] . "` SET `" . $FIELDS['pms']['stare'] . "` = 'trimis' WHERE `" . $FIELDS['pms']['id'] . "` = '" . $id_pm . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			/*$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['pmsTotal'] . "` = `" . $FIELDS['users']['pmsTotal'] . "`+1,`" . $FIELDS['users']['pmsNec'] . "` = `" . $FIELDS['users']['pmsNec'] . "`+1 WHERE `" . $FIELDS['users']['user'] . "` = '" . $to . "' LIMIT 1;";
			$conn->query($query);
			unset($query);*/
		}
		else
			return 0;
		
	}
	
	public function nrMesajeNecitite($to){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT `" . $FIELDS['pms']['id'] . "` FROM `" . $TABLES['pms'] . "` WHERE `" . $FIELDS['pms']['citit'] . "` = '0' AND `" . $FIELDS['pms']['to'] . "` = '" . $to . "' AND `" . $FIELDS['pms']['stare'] . "` != 'netrimis';";
		$result = $conn->query($query);
		$return = $result->num_rows;
		
		unset($result,$query);
		return $return;
	}
	
	public function afiseazaMyIgnore($to){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT `" . $FIELDS['ignore']['id'] . "`,`" . $FIELDS['ignore']['from'] . "` FROM `" . $TABLES['ignore'] . "` WHERE `" . $FIELDS['ignore']['to'] . "` = '" . $to . "';";
		$result = $conn->query($query);
		
		while($row = $result->fetch_row()){
			
			echo '<div id="ignore' . $row[0] . '">' . "\n";
			echo $row[1] . '<a href="javascript:void(0)" onClick="Pms.removeIgnore(' . $row[0] . ')"><img src="images/icons/cross.png" /></a>' . "\n";
			echo '</div>' . "\n";
		
		}
		
		$result->free; unset($result,$query,$row);
		
	}
	
	public function seteazaCaCitit($id_msg){
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->esteLogat()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "UPDATE `" . $TABLES['pms'] . "` SET `" . $FIELDS['pms']['citit'] . "` = '1' WHERE `" . $FIELDS['pms']['id'] . "` = '" . $id_msq . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		
	}
	
	public function adaugaLaIgnore($peCine){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->esteLogat()){
		
			include_once "classes/register.php";
			if(esteUser($peCine)){
				
				include_once "classes/config.php";
				global $TABLES,$FIELDS,$conn;
				
				$query = "INSERT INTO `" . $TABLES['ignore'] . "` (`" . $FIELDS['ignore']['id'] . "`,`" . $FIELDS['ignore']['from'] . "`,`" .  $FIELDS['ignore']['to'] . "`) VALUES (NULL,'" . $peCine . "','" . getUserFromCookie() . "');";
				$conn->query($query);
				unset($query);
				
			}
		
		}

	}
	
	public function stergeIgonre($id_ignore){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "DELETE FROM `" . $TABLES['ignore'] . "` WHERE `" . $FIELDS['ignore']['id'] . "` = '" . $id_ignore . "' LIMIT 1;";
		$conn->query($query);
		unset($query);
		
	}

}

?>