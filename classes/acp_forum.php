<?php

class ACPForum{
	
	public function adaugaThread($categ,$titlu,$descriere){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
			$query = "INSERT INTO `" . $TABLES['forumForums'] . "` (`" . $FIELDS['forumForums']['id'] . "`,`" . $FIELDS['forumForums']['titlu'] . "`,`" . $FIELDS['forumForums']['descriere'] . "`,`" . $FIELDS['forumForums']['id_categ'] . "`) VALUES (NULL,'" . $titlu . "','" . $descriere . "','" . $categ . "');";
			$conn->query($query);
			unset($query);
		}
		else
			die('');
			
	}
	
	public function adaugaCategorie($titlu){
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT MAX(`" . $FIELDS['forumCateg']['order'] . "`) FROM `" . $TABLES['forumCateg'] . "` LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			(int)$order = $row[0]; unset($query,$row);
			$order++;
			
			$query = "INSERT INTO `" . $TABLES['forumCateg'] . "` (`" . $FIELDS['forumCateg']['id'] . "`,`" . $FIELDS['forumCateg']['titlu'] . "`,`" . $FIELDS['forumCateg']['order'] . "`) VALUES (NULL,'" . $titlu . "','" . $order . "');";
			$conn->query($query);
			unset($query);
			
		}
		
	}
	
	function stergePost($id_post){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['forumTopics']['id'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['lastPost'] . "` = '" . $id_post . "' LIMIT 1;"; //vad daca este ultimul post din topicul sau
			$result = $conn->query($query);
			(int)$nrRows = $result->num_rows;
			$row = $result->fetch_row();
			(int)$id_topic = $row[0];
			$result->free; unset($query,$result,$row);
			
			if($nrRows){//daca este ultimul post in topicul sau,
				
				$query = "SELECT `" . $FIELDS['forumPosts']['id'] . "` FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id_topic'] . "` = '" . $id_topic . "' AND `" . $FIELDS['forumPosts']['id'] . "` != '" . $id_post . "' ORDER BY `" . $FIELDS['forumPosts']['data'] . "` DESC LIMIT 1;"; //vad care este cel mai recent, in afara de el
				$row = $conn->query($query)->fetch_row();
				(int)$idLastPost = $row[0];
				unset($row,$query);
				
				$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['lastPost'] . "` = '" . $idLastPost . "' WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;"; //in tabela cu topicuri updatez id-ul ultimului post
				$conn->query($query);
				unset($query,$idLastPost);
				
				$query = "SELECT `" . $FIELDS['forumForums']['id'] . "` FROM `" . $TABLES['forumForums'] . "` WHERE `" . $FIELDS['forumForums']['lastPost'] . "` = '" . $id_post . "' LIMIT 1;"; //vad daca este ultimul post in thread-ul sau
				$result = $conn->query($query);
				$row = $result->fetch_row();
				(int)$nr = $result->num_rows; (int)$id_thread = $row[0];
				$result->free; unset($query,$result,$row);
				
				if($nr){//daca este ultimul post in thread-ul sau,
				
					$query = "SELECT `" . $FIELDS['forumTopics']['lastPost'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $id_thread . "' AND `" . $FIELDS['forumTopics']['lastPost'] . "` != '" . $id_post . "' ORDER BY `" . $FIELDS['forumTopics']['lastPost'] . "` DESC LIMIT 1;"; //iau lastPost-ul din topicul cu cel mai mare lastPost -> acesta va fi ultimul post al thread-ului
					$row = $conn->query($query)->fetch_row();
					(int)$idLastPost = $row[0];
					unset($query,$row);
					
					$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['lastPost'] . "` = '" . $idLastPost . "' WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $id_thread . "' LIMIT 1;";
					$conn->query($query);
					unset($query,$idLastPost);
					
				
				}
				
				unset($id_thread,$nr);
			
			}
			
			
			$query = "SELECT `" . $FIELDS['forumPosts']['id_topic'] . "`,`" . $FIELDS['forumPosts']['autor'] . "`	FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id'] . "` = '" . $id_post . "' LIMIT 1;"; //vad in ce topic era
			$row = $conn->query($query)->fetch_row();
			(int)$id_topic = $row[0]; (string)$cine = $row[1];
			unset($row,$query);
			
			$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['replici'] . "` = `" . $FIELDS['forumTopics']['replici'] . "`-1 WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;"; //scad numarul de replici
			$conn->query($query);
			unset($query);
			
			$query = "SELECT `" . $FIELDS['forumTopics']['id_forums'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			(int)$id_thread = $row[0]; unset($query,$row);
			
			$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['nrPosts'] . "` = `" . $FIELDS['forumForums']['nrPosts'] . "`-1 WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $id_thread . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			$query = "DELETE FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id'] . "` = '" . $id_post . "' LIMIT 1;";//si in final il sterg...
			$conn->query($query);
			unset($query,$id_topic,$nrRows);
			
			$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['posturi'] . "` = `" . $FIELDS['users']['posturi'] . "`-1,`" . $FIELDS['users']['actiuni'] . "` = `" . $FIELDS['users']['actiuni'] . "` -1 WHERE `" . $FIELDS['users']['user'] . "` = '" . $cine . "' LIMIT 1;";//scad numarul de posturi si de puncte al userului care a postat postul
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
		
	}
	
	function stergeTopic($id_topic){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
			$query = "SELECT `" . $TABLES['forumForums'] . "`.`" . $FIELDS['forumForums']['id'] . "`,`" . $TABLES['forumTopics'] . "`.`" . $FIELDS['forumTopics']['lastPost'] . "` FROM `" . $TABLES['forumForums'] . "`,`" . $TABLES['forumTopics'] . "` WHERE `" . $TABLES['forumTopics'] . "`.`" . $FIELDS['forumTopics']['id_forums'] . "` = `" . $TABLES['forumForums'] . "`.`" . $FIELDS['forumForums']['id'] . "` AND `" . $TABLES['forumTopics'] . "`.`" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;"; //vad daca cel mai recent post al thread-ului apartine acestui topic
			$result =  $conn->query($query);
			(int)$nrRows = $result->num_rows;
			unset($query);
			
			if($nrRows){//daca da,
				
				$row = $result->fetch_row();
				(int)$id_thread = $row[0]; (int)$idLastPostTopic = $row[1];
				unset($row);
				
				$query = "SELECT `" . $FIELDS['forumTopics']['lastPost'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $id_thread . "' AND `" . $FIELDS['forumTopics']['lastPost'] . "` != '" . $idLastPostTopic . "' ORDER BY `" . $FIELDS['forumTopics']['lastPost'] . "` DESC LIMIT 1;"; //gasesc cel mai recent post al threadu-ului in care e topicul
				$row = $conn->query($query)->fetch_row();
				
				(int)$idLastPost = $row[0]; unset($query,$row);
				
				$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['lastPost'] . "` = '" . $idLastPost . "' WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $id_thread . "' LIMIT 1;";//setez postul cel mai recent din thread
				$conn->query($query);
				unset($query);
				
			}
			
			unset($result,$nrRows,$query);
			
			$query = "SELECT `" . $FIELDS['forumTopics']['replici'] . "`,`" . $FIELDS['forumTopics']['id_forums'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "';"; //gasesc numarul de replici din acest topic
			$result = $conn->query($query);
			$row = $result->fetch_row();
			unset($query,$result);
			
			$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['nrPosts'] . "` = `" . $FIELDS['forumForums']['nrPosts'] . "`-" . $row[0] . ",`" . $FIELDS['forumForums']['nrTopics'] . "` = `" . $FIELDS['forumForums']['nrTopics'] . "`-1 WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $row[1] . "' LIMIT 1;";//scad numarul de replici si numarul de topicuri ale thread-ului
			$conn->query($query);
			unset($query,$row);
			
			$query = "DELETE FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;";//si in final sterg topicul...
			$conn->query($query);
			unset($query);
			
			$query = "SELECT `" . $FIELDS['forumPosts']['id'] . "` FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id_topic'] . "` = '" . $id_topic . "';";//selecez posturiele care trebuiescx sterse
			$result = $conn->query($query);
			
			while($row = $result->fetch_row())
				self::stergePost($row[0]);
			
			$result->free; unset($query,$row,$result);
		
		}
		else
			die('');
		
	}
	
	function stergeThread($id_thread){
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
			$query = "SELECT `" . $FIELDS['forumTopics']['id'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $id_thread . "';";//selectez toate topicurile thread-ului
			$result = $conn->query($query); unset($query);
		
			while($row = $result->fetch_row())
				self::stergeTopic($row[0]);
		
			$result->free; unset($result,$row,$query);

		
			$query = "DELETE FROM `" . $TABLES['forumForums'] . "` WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $id_thread . "' LIMIT 1;";//sterg thread-ul
			$conn->query($query);
			unset($query);
	
		}
		else
			die('');
	
	}
	
	function stergeCategorie($id_categ){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
		
			$query = "SELECT `" . $FIELDS['forumForums']['id'] . "` FROM `" . $TABLES['forumForums'] . "` WHERE `" . $FIELDS['forumForums']['id_categ'] . "` = '" . $id_categ . "';"; //selectez toate thread-urile categoriei
			$result = $conn->query($query); unset($query);
		
			while($row = $result->fetch_row())
				self::stergeThread($row[0]); //sterge thread-ul i;
		
			$result->free; unset($row,$result);
		
			$query = "DELETE FROM `" . $TABLES['forumCateg'] . "` WHERE `" . $FIELDS['forumCateg']['id'] . "` = '" . $id_categ . "' LIMIT 1;";//sterg categoria
			$conn->query($query);
			unset($query);
	
		}
		else
			die('');
			
	}
	
	function afiseazaOptiuniPostToTopics(){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		echo '<select name="posttotopic">' . "\n";
		
		$q1 = "SELECT `" . $FIELDS['forumCateg']['id'] . "`,`" . $FIELDS['forumCateg']['titlu'] . "` FROM `" . $TABLES['forumCateg'] . "`;";
		$r1 = $conn->query($q1);
		while($row1 = $r1->fetch_row()){
			$q2 = "SELECT `" . $FIELDS['forumForums']['id'] . "`,`" . $FIELDS['forumForums']['titlu'] . "` FROM `" . $TABLES['forumForums'] . "` WHERE `" . $FIELDS['forumForums']['id_categ'] . "` = '" . $row1[0] . "' ORDER BY `" . $FIELDS['forumForums']['lastPost'] . "`;";	
			$r2 = $conn->query($q2);
			while($row2 = $r2->fetch_row()){
				$q3 = "SELECT `" . $FIELDS['forumTopics']['id'] . "`,`" . $FIELDS['forumTopics']['titlu'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $row2[0] . "' ORDER BY `" . $FIELDS['forumTopics']['data'] . "`;";
				$r3 = $conn->query($q3);
				while($row3 = $r3->fetch_row()){
					echo '<option value="' . $row3[0] . '">' . $row1[1] . " ---> " . $row2[1] . " ---> " . $row3[1] . "</option>\n";
				}
			}
		}
		
		echo "</select>\n";
		
		unset($result,$query,$row,$row2,$result2);
			
	
	}
	
	function afiseazaOptiuniTopicsToThreads(){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		echo '<select name="topictothread">' . "\n";
		
		$query = "SELECT `" . $FIELDS['forumForums']['id'] . "`,`" . $FIELDS['forumForums']['titlu'] . "` FROM `" . $TABLES['forumForums'] . "` ORDER BY `" . $FIELDS['forumForums']['lastPost'] . "`;";	
		$result = $conn->query($query);
		while($row = $result->fetch_row())
			echo '<option value="' . $row[0] . '">' . $row[1] . "</option>\n";
		
		echo "</select>\n";
		
		$result->free; unset($result,$query,$row);
		
	}
	
	function afiseazaOptiuniThreadToCateg(){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		echo '<select name="threadtocateg">' . "\n";
		
		$query = "SELECT `" . $FIELDS['forumCateg']['id'] . "`,`" . $FIELDS['forumCateg']['titlu'] . "` FROM `" . $TABLES['forumCateg'] . "` ORDER BY `" . $FIELDS['forumCateg']['order'] . "`;";
		$result = $conn->query($query);
		while($row = $result->fetch_row())
			echo '<option value="' . $row[0] . '">' . $row[1] . "</option>\n";
		
		echo "</select>\n";
		
		$result->free; unset($query,$result);
		
		
	}
	
	function mutaPost($id_post,$toTopic){
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['forumTopics']['id'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['lastPost'] . "` = '" . $id_post . "' LIMIT 1;"; //vad daca este ultimul post din topicul sau
			$result = $conn->query($query);
			(int)$nrRows = $result->num_rows;
			$row = $result->fetch_row();
			(int)$id_topic = $row[0];
			unset($query,$result,$row);
			
			if($nrRows){//daca este ultimul post in topicul sau,
				
				$query = "SELECT `" . $FIELDS['forumPosts']['id'] . "` FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id_topic'] . "` = '" . $id_topic . "' AND `" . $FIELDS['forumPosts']['id'] . "` != '" . $id_post . "' ORDER BY `" . $FIELDS['forumPosts']['data'] . "` DESC LIMIT 1;"; //vad care este cel mai recent, in afara de el
				$row = $conn->query($query)->fetch_row();
				(int)$idLastPost = $row[0];
				unset($row,$query);
				
				$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['lastPost'] . "` = '" . $idLastPost . "' WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;"; //in tabela cu topicuri updatez id-ul ultimului post
				$conn->query($query);
				unset($query,$idLastPost);
			
			}
			
			
			
			$query = "SELECT `" . $FIELDS['forumPosts']['id_topic'] . "`,`" . $FIELDS['forumPosts']['data'] . "` FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id'] . "` = '" . $id_post . "' LIMIT 1;"; //vad in ce topic era
			$row = $conn->query($query)->fetch_row();
			(int)$id_topic = $row[0]; (int)$dataSa = $row[1];
			unset($row,$query);
			
			$query = "SELECT MAX(`" . $FIELDS['forumPosts']['data'] . "`) FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id_topic'] . "` = '" . $toTopic . "' LIMIT 1;";//vad data ultimului post din noul topic
			$row = $conn->query($query)->fetch_row();
			(int)$celMaiRecent = $row[0]; unset($query,$row);
			
			if($celMaiRecent < $dataSa){//daca data ultimului post din noul topic este mai veche decat cea a postului care trebuie mutat,
				
				$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['lastPost'] . "` = '" . $id_post . "' WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $toTopic . "' LIMIT 1;";//setez pe acesta ca ultimul post al noului topic
				$conn->query($query);
				unset($query);
				
			}
			
			$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['replici'] . "` = `" . $FIELDS['forumTopics']['replici'] . "`-1 WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;"; //scad numarul de replici din fostul topic
			$conn->query($query);
			unset($query);
			
			$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['replici'] . "` = `" . $FIELDS['forumTopics']['replici'] . "`+1 WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $toTopic . "' LIMIT 1;"; //maresc numarul de replici din acutualul topic
			$conn->query($query);
			unset($query);
			
			$query = "UPDATE `" . $TABLES['forumPosts'] . "` SET `" . $FIELDS['forumPosts']['id_topic'] . "` = '" . $toTopic . "' WHERE `" . $FIELDS['forumPosts']['id'] . "` = '" . $id_post . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			$query = "SELECT `" . $FIELDS['forumTopics']['id_forums'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;";//vad in ce thread este topicul de unde iau postul
			$row = $conn->query($query)->fetch_row();
			$idThreadFrom = $row[0]; unset($query,$row);
			
			$query = "SELECT `" . $FIELDS['forumTopics']['id_forums'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $toTopic . "' LIMIT 1;";//vad in ce thread este topicul unde duc postul
			$row = $conn->query($query)->fetch_row();
			$idThreadTo = $row[0]; unset($query,$row);
			if($idThreadFrom != $idThreadTo){//daca thread este topicul de unde iau postul difera de thread este topicul unde duc postul (topicurile fac parte din thread-uri diferite)
			
				$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['nrPosts'] . "` = `" . $FIELDS['forumForums']['nrPosts'] . "`-1 WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $idThreadFrom . "' LIMIT 1;";//scad numarul de replici de unde il iau
				$conn->query($query);
				unset($query);
				
				$query = $query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['nrPosts'] . "` = `" . $FIELDS['forumForums']['nrPosts'] . "`+1 WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $idThreadTo . "' LIMIT 1;";//cresc numarul de replici acolo unde il duc
				$conn->query($query);
				unset($query);
				
				$query = "SELECT MAX(`" . $FIELDS['forumForums']['lastPost'] . "`) FROM `" . $TABLES['forumForums'] . "` WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $idThreadTo . "' LIMIT 1;";//vad care este cel ma recent post din noul thread

				$row = $conn->query($query)->fetch_row();
				$idLastPostNewThread = $row[0]; unset($query,$row);
				
				if($idLastPostNewThread < $id_post){//daca cel mai recent post din noul thread este mai vechi decat acesta,
				
					$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['lastPost'] . "` = '" . $id_post . "' WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $idThreadTo . "' LIMIT 1;";//setez pe acesta ca cel mai recent post al threadului in care bag
					$conn->query($query);
					unset($query);
				
					$query = "SELECT `" . $FIELDS['forumTopics']['lastPost'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $idThreadFrom . "' AND `" . $FIELDS['forumTopics']['lastPost'] . "` != '" . $id_post . "' ORDER BY `" . $FIELDS['forumTopics']['lastPost'] . "` DESC LIMIT 1;";//vad care este cel ma recent post din thread-ul din care iau postul
					$row = $conn->query($query)->fetch_row();
					(int)$idLastPostTh = $row[0]; unset($query,$row);
					
					$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['lastPost'] . "` = '" . $idLastPostTh . "' WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $idThreadFrom . "' LIMIT 1;";//setez pe acela ca fiind cel mai recent al thread-ului
					$conn->query($query);
					unset($query);
					
				}
				
			}

		}
		else
			die('');
	}
	
	function mutaTopic($id_topic,$toThread){
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $TABLES['forumForums'] . "`.`" . $FIELDS['forumForums']['id'] . "`,`" . $TABLES['forumTopics'] . "`.`" . $FIELDS['forumTopics']['lastPost'] . "` FROM `" . $TABLES['forumForums'] . "`,`" . $TABLES['forumTopics'] . "` WHERE `" . $TABLES['forumTopics'] . "`.`" . $FIELDS['forumTopics']['id_forums'] . "` = `" . $TABLES['forumForums'] . "`.`" . $FIELDS['forumForums']['id'] . "` AND `" . $TABLES['forumTopics'] . "`.`" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;"; //vad daca cel mai recent post al thread-ului apartine acestui topic
			$result =  $conn->query($query);
			(int)$nrRows = $result->num_rows;
			unset($query);
			
			if($nrRows){//daca da,
				
				$row = $result->fetch_row();
				(int)$id_thread = $row[0]; (int)$idLastPostTopic = $row[1];
				unset($row);
				
				$query = "SELECT `" . $FIELDS['forumTopics']['lastPost'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $id_thread . "' AND `" . $FIELDS['forumTopics']['lastPost'] . "` != '" . $idLastPostTopic . "' ORDER BY `" . $FIELDS['forumTopics']['lastPost'] . "` DESC LIMIT 1;"; //gasesc cel mai recent post al threadu-ului in care e topicul
				$row = $conn->query($query)->fetch_row();
				
				(int)$idLastPost = $row[0]; unset($query,$row);
				
				$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['lastPost'] . "` = '" . $idLastPost . "' WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $id_thread . "' LIMIT 1;";//setez postul cel mai recent din thread
				$conn->query($query);
				unset($query);
				
			}
			
			unset($result,$nrRows,$query);
			
			$query = "SELECT `" . $FIELDS['forumTopics']['replici'] . "`,`" . $FIELDS['forumTopics']['id_forums'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "';"; //gasesc numarul de replici din acest topic
			$result = $conn->query($query);
			$row = $result->fetch_row();
			unset($query,$result);
			
			//$row[0]--;
			$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['nrPosts'] . "` = `" . $FIELDS['forumForums']['nrPosts'] . "`-" . $row[0] . ",`" . $FIELDS['forumForums']['nrTopics'] . "` = `" . $FIELDS['forumForums']['nrTopics'] . "`-1 WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $row[1] . "' LIMIT 1;";//scad numarul de replici si numarul de topicuri ale thread-ului din care iau
			$conn->query($query);
			unset($query);
			
			$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['nrPosts'] . "` = `" . $FIELDS['forumForums']['nrPosts'] . "`+" . $row[0] . ",`" . $FIELDS['forumForums']['nrTopics'] . "` = `" . $FIELDS['forumForums']['nrTopics'] . "`+1 WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $toThread . "' LIMIT 1;";//maresc numarul de replici si numarul de topicuri ale thread-ului in care pun topicul
			$conn->query($query);
			unset($query,$row);
			
			$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $toThread . "' WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;";//il mut
			$conn->query($query);
			unset($query);
			
			$query = "SELECT MAX(`" . $FIELDS['forumTopics']['lastPost'] . "`) FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $toThread . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			(int)$idLestPost = $row[0]; unset($query,$row);
			
			$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['lastPost'] . "` = '" . $idLestPost . "' WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $toThread . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	}
	
	function mutaThread($id_thread,$toCateg){
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['id_categ'] . "` = '" . $toCateg . "' WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $id_thread . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	}
	
	function modificaInPost($ce,$care,$cat){
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			
			$query = "UPDATE `" . $TABLES['forumPosts'] . "` SET `" . $FIELDS['forumPosts'][$ce] . "` = '" . $cat . "' WHERE `" . $FIELDS['forumPosts']['id'] . "` = '" . $care . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	}
	
	function modificaInTopic($ce,$care,$new){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			
			$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics'][$ce] . "` = '" . $new . "' WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $care . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	}
	
	function modificaInThread($ce,$care,$new){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			
			$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums'][$ce] . "` = '" . $new . "' WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $care . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	}
	
	function modificaInCateg($ce,$care,$new){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			
			$query = "UPDATE `" . $TABLES['forumCateg'] . "` SET `" . $FIELDS['forumCateg'][$ce] . "` = '" . $new . "' WHERE `" . $FIELDS['forumCateg']['id'] . "` = '" . $care . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
		else
			die('');
	}
	
	function seteazaOrdine($cum,$cine){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT MAX(`" . $FIELDS['forumCateg']['order'] . "`) FROM `" . $TABLES['forumCateg'] . "` LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			$max = $row[0]; unset($row,$query);
			
			if($cum == 'down'){
				if($cine == $max){
				
					$inter1 = 1;
					$inter2 = $max;
					
				}
				else{
					$inter1 = $cine+1;
					$inter2 = $cine;
				}
			}
			else{
				if($cine == 1){
					$inter1 = $max;
					$inter2 = 1;
				}
				else{
					$inter1 = $cine-1;
					$inter2 = $cine;
				}
			}
			
			
			$query = "SELECT `" . $FIELDS['forumCateg']['id'] . "` FROM `" . $TABLES['forumCateg'] . "` WHERE `" . $FIELDS['forumCateg']['order'] . "` = '" . $inter1 . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			$id1 = $row[0];
			unset($query,$row);
					
			$query = "SELECT `" . $FIELDS['forumCateg']['id'] . "` FROM `" . $TABLES['forumCateg'] . "` WHERE `" . $FIELDS['forumCateg']['order'] . "` = '" . $inter2 . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			$id2 = $row[0];
			unset($query,$row);
					
			$query = "UPDATE `" . $TABLES['forumCateg'] . "` SET `" . $FIELDS['forumCateg']['order'] . "` = '" . $inter2 . "' WHERE `" . $FIELDS['forumCateg']['id'] . "` = '" . $id1 . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
					
			$query = "UPDATE `" . $TABLES['forumCateg'] . "` SET `" . $FIELDS['forumCateg']['order'] . "` = '" . $inter1 . "' WHERE `" . $FIELDS['forumCateg']['id'] . "` = '" . $id2 . "' LIMIT 1;";
			$conn->query($query);
			unset($query);

			
			
		}
		else
			die('');
		
	}
	
	function schimbaStareTopic($id_topic){
	
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
		
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['forumTopics']['stare'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			$stare = $row[0]; unset($query,$row);
			
			if($stare == 'inchis')
				$newStare = "deschis";
			else
				$newStare = "inchis";
			
			$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['stare'] . "` = '" . $newStare . "' WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_topic . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
		}
	
	}

}

?>