<?php


class ForumFcts{

	function adaugaPost($titlu,$body,$cine,$topic){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		if(!self::poatePosta($topic))
			die('Nu poti posta aici!!! Acest topic este inchis!');
		
		$time = time();
		$query = "INSERT INTO `" . $TABLES['forumPosts'] . "` (`" . $FIELDS['forumPosts']['id'] . "`,`" . $FIELDS['forumPosts']['id_topic'] . "`,`" . $FIELDS['forumPosts']['autor'] . "`,`" . $FIELDS['forumPosts']['titlu'] . "`,`" . $FIELDS['forumPosts']['body'] . "`,`" . $FIELDS['forumPosts']['data'] . "`) VALUES (NULL,'" . $topic . "','" . $cine . "','" . $titlu . "','" . $body . "','" . $time . "');";
		$conn->query($query);
		unset($query);
		
		$query = "SELECT `" . $FIELDS['forumPosts']['id'] . "` FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['data'] . "` = '" . $time . "' AND `" . $FIELDS['forumPosts']['id_topic'] . "` = '" . $topic . "' LIMIT 1;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$id_post = $row[0];
		$result->free; unset($result,$query,$row);
		
		$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['lastPost'] . "` = '" . $id_post . "',`" . $FIELDS['forumTopics']['replici'] . "` = `" . $FIELDS['forumTopics']['replici'] . "`+1 WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $topic . "' LIMIT 1;";
		$conn->query($query);
		unset($query);
		
		$query = "SELECT `" . $FIELDS['forumTopics']['id_forums'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $topic . "' LIMIT 1;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$id_thread = $row[0];
		$result->free; unset($result,$query,$row);
		
		$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['lastPost'] . "` = '" . $id_post . "',`" . $FIELDS['forumForums']['nrPosts'] . "` = `" . $FIELDS['forumForums']['nrPosts'] . "`+1 WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $id_thread . "' LIMIT 1;";
		$conn->query($query);
		unset($query,$time,$id_post,$id_thread);
		
		$query = "UPDATE `" . $TABLES['users'] . "` SET `" . $FIELDS['users']['posturi'] . "` = `" . $FIELDS['users']['posturi'] . "`+1,`" . $FIELDS['users']['actiuni'] . "` = `" . $FIELDS['users']['actiuni'] . "`+1 WHERE `" . $FIELDS['users']['user'] . "` = '" . $cine . "' LIMIT 1;";
		$conn->query($query);
		unset($query);
		
	}
	
	function addView($topic){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		if(!self::aVazut('forum',$topic)){
		
			$query = "UPDATE `" . $TABLES['forumTopics'] . "` SET `" . $FIELDS['forumTopics']['vizualizari'] . "` = `" . $FIELDS['forumTopics']['vizualizari'] . "`+1 WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $topic . "' LIMIT 1;";
			$conn->query($query);
			unset($query);
			
			self::ilVede('forum',$topic);
		
		}

	}
	
	function aVazut($unde,$care){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		(bool)$return = false;
		
		$query = "SELECT `" . $FIELDS['views']['care'] . "` FROM `" . $TABLES['views'] . "` WHERE `" . $FIELDS['views']['care'] . "` = '" . $care . "' AND `" . $FIELDS['views']['unde'] . "` =  '" . $unde . "' AND `" . $FIELDS['views']['ip'] . "` = '" . ip2long($_SERVER['REMOTE_ADDR']) . "' LIMIT 1;";
		$nr = $conn->query($query)->num_rows;
		if($nr)
			$return = true;
		unset($query,$nr);
		
		return $return;
	
	}
	
	
	function ilVede($unde,$care){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "INSERT INTO `" . $TABLES['views'] . "` (`" . $FIELDS['views']['id'] . "`,`" . $FIELDS['views']['unde'] . "`,`" . $FIELDS['views']['care'] . "`,`" . $FIELDS['views']['ip'] . "`) VALUES (NULL,'" . $unde . "','" . $care . "','" . ip2long($_SERVER['REMOTE_ADDR']) . "');";
	
		$conn->query($query);
		unset($query);
	
	}
	
	function poatePosta($topic){
		
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT `" . $FIELDS['forumTopics']['stare'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $topic . "' LIMIT 1;";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		if(strtolower($row[0]) == "deschis")
			$return = true;
		else
			$return = false;
		$result->free; unset($query,$result,$row);
		
		return $return;
	}
	
	function adaugaTopic($titlu,$starter,$thread){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "INSERT INTO `" . $TABLES['forumTopics'] . "` (`" . $FIELDS['forumTopics']['id'] . "`,`" . $FIELDS['forumTopics']['titlu'] . "`,`" . $FIELDS['forumTopics']['starter'] . "`,`" . $FIELDS['forumTopics']['id_forums'] . "`,`" . $FIELDS['forumTopics']['data'] . "`,`" . $FIELDS['forumTopics']['stare'] . "`) VALUES (NULL,'" . $titlu . "','" . $starter . "','" . $thread . "','" . time() . "','deschis');";
		$conn->query($query);
		unset($query);
		
		$query = "UPDATE `" . $TABLES['forumForums'] . "` SET `" . $FIELDS['forumForums']['nrTopics'] . "` = `" . $FIELDS['forumForums']['nrTopics'] . "`+1 WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $thread . "' LIMIT 1;";
		$conn->query($query);
		unset($query);
	}
	
	function getIdTopic($cine,$unde,$cand){
	
		include_once "classes/config.php";
		global $TABLES,$FIELDS,$conn;
		
		$query = "SELECT `" . $FIELDS['forumTopics']['id'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['starter'] . "` = '" . $cine . "' AND `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $unde . "' AND `" . $FIELDS['forumTopics']['data'] . "` >= " . $cand . " LIMIT 1;";
		$row = $conn->query($query)->fetch_row();
		$id = $row[0];
		unset($query,$row);
		
		return $id;
	
	}
	
}

?>