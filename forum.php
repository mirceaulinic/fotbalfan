<?php


function veziDetalii($user){

	include_once "classes/config.php";
	global $FIELDS,$TABLES,$conn;

	$query = "SELECT `" . $FIELDS['users']['posturi'] . "`,`" . $FIELDS['users']['semnatura'] . "`,`" . $FIELDS['users']['lastLogout'] . "` FROM `" . $TABLES['users'] . "` WHERE `" . $FIELDS['users']['user'] . "` = '" . $user . "' LIMIT 1;";
	$row = $conn->query($query)->fetch_row();
	
	unset($query);
	return $row;

}

function formatData($time){

	(int)$taim = time();
	(int)$dif = $taim - $time;
	if($dif < 60)
		$return = "in urma cu " . $dif . " secunde";
	elseif( ($dif >= 60) && ($dif < 3600) )
		$return = "in urma cu " . date('i',$dif) . " minute";
	elseif( ($dif >= 3600) && ($dif < 86400) )
		$return = "in urma cu " . floor( $dif / 3600 ) . " ore";
	elseif( ($dif >= 86400) && ($dif < 2592000) )
		$return = "in urma cu " . floor( $dif / 86400 ) . " zile";
	else
		$return = "pe " . date("d m Y, H:i",$time);
	
	unset($taim,$dif);
	return $return;
}

function returnSort(){
	
	(string)$return = "";
	if(isset($_GET['pg']))
		$return = "&amp;pg=" . $_GET['pg'];
	if(isset($_GET['cum'])){
		if(strtolower($_GET['cum']) == "asc")
			$return .= "&amp;cum=desc";
		else
			$return .= "&amp;cum=asc";
	}
	else
		$return .= "&amp;cum=asc";
	
	return $return;
	
}


function getGetForum(){

	if(isset($_GET['pg']))
		$return = "&amp;pg=" . $_GET['pg'];
	if(isset($_GET['sortDupa']))
		$return .= "&amp;sortDupa=" . $_GET['sortDupa'];
	if(isset($_GET['cum']))
		$return .= "&amp;cum=" . strtolower($_GET['cum']);
	
	return $return;
}

function showTitleRoot(){
	if(isset($_GET['id_topic'])){
		
		(int)$care = $_GET['id_topic'];
		echo Forum::showRoot($care,'topic',3,$care,true);
	
	}
	elseif(isset($_GET['id_thread'])){
	
		(int)$care = $_GET['id_thread'];
		echo Forum::showRoot($care,'thread',2,$care,true);
		
	}
	elseif(isset($_GET['id_categ'])){
	
		(int)$care = $_GET['id_categ'];
		echo Forum::showRoot($care,'categ',1,$care,true);
		
	}
}
function processGet(){

	if(isset($_GET['pg']))
		(int)$pagina = $_GET['pg'];
	else
		(int)$pagina = 1;
		
	if(isset($_GET['sortDupa']))
		(string)$sortDupa = $_GET['sortDupa'];
	else
		(string)$sortDupa = "data";
		
	if(isset($_GET['cum']))
		(string)$cum = strtoupper(addslashes($_GET['cum']));
	else
		(string)$cum = "DESC";
	
	if(isset($_GET['id_topic'])){
		
		(int)$care = $_GET['id_topic'];
		echo '<a href="forum.php" class="root">Forum</a> &gt; ';
		echo '<div class="root">' . Forum::showRoot($care,'topic',3,$care) . '</div>';
		Forum::showPostsInTopic($care,$pagina);
		
		include_once "classes/forumFcts.php";
		ForumFcts::addView($care);
	
	}
	elseif(isset($_GET['id_thread'])){
	
		(int)$care = $_GET['id_thread'];
		echo '<a href="forum.php" class="root">Forum</a> &gt; ';
		echo '<div class="root">' . Forum::showRoot($care,'thread',2,$care) . '</div>';
		Forum::showTopicsInForums($care,$pagina,$sortDupa,$cum);
		
	}
	elseif(isset($_GET['id_categ'])){
		(int)$care = $_GET['id_categ'];
		echo '<a href="forum.php" class="root">Forum</a> &gt; ';
		echo '<div class="root">' . Forum::showRoot($care,'categ',1,$care) . '</div>';
		Forum::showCategory($care);
	}
	elseif(isset($_GET['tml'])){
		echo '<div class="root"><a class="root">Vezi toate mesajele lui ' . $_GET['tml'] . '</a></div>';
		Forum::showPostsInTopic(0,$pagina,addslashes($_GET['tml']));
	}
	elseif(isset($_GET['ce'])){
		if( ($_GET['ce'] == 'adauga-topic') && (isset($_GET['unde'])) && (is_numeric(@$_GET['unde'])) ){
			?>
           <form method="post" action="forum.php?ce=adauga-topic&amp;unde=<?php echo $_GET['unde'] ?>">
            <?php
			if(isset($_POST['submit'])){
			?>
             <h2>Aveti erori</h2>
             <div class="registerForm">
            <?php
			
				if(strlen($_POST['titlu']) >= 10){
					
					include_once "classes/forumFcts.php";
					$time = time();
					ForumFcts::adaugaTopic(stripslashes(htmlspecialchars($_POST['titlu'])),getUserFromCookie(),$_GET['unde']);
					$id = ForumFcts::getIdTopic(getUserFromCookie(),$_GET['unde'],$time);
					ob_clean();
					header("Location: forum.php?ce=adauga-post&unde=$id");
					
				}
				else
					echo '<div class="error">Titlul trebuie sa fie mai lung!</div>';
			?>
            </div>
            <?php
			}
			?>
			 <h2>Adauga un topic</h2>
             <div class="registerForm">
               	 <div class="item">
					<label for="titlu" class="column">Titlu:</label><br />
					<input name="titlu" id="titlu" value="<?php if(isset($_POST['titlu']))echo $_POST['titlu']; ?>" class="field" type="text" />
				</div>
             </div>
             <div>
				<input value="Adauga" class="submit" type="submit" name="submit" />
			</div>
           </form>
            <?php
		
		}
		elseif( ($_GET['ce'] == 'adauga-post') && ( (  (  (isset($_GET['unde'])) && (is_numeric($_GET['unde'])) ) || ( (isset($_GET['quote'])) && (is_numeric($_GET['quote'])) ) ) ) ){
		(int)$topic = 0; (string)$titlu = "";
		if(isset($_GET['unde'])){
		
			$get = "&amp;unde=" . $_GET['unde'];
			$topic = $_GET['unde'];
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['forumTopics']['titlu'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $topic . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			$titlu = $row[0]; unset($query,$row);
			$titlu = "Re: ".$titlu;
			
		}
		else{
			$get = "&amp;quote=" . $_GET['quote'];
			$post = $_GET['quote'];
			
			include_once "classes/config.php";
			global $TABLES,$FIELDS,$conn;
			
			$query = "SELECT `" . $FIELDS['forumPosts']['autor'] . "`,`" . $FIELDS['forumPosts']['id_topic'] . "`,`" . $FIELDS['forumPosts']['titlu'] . "`,`" . $FIELDS['forumPosts']['body'] . "` FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id'] . "` = '" . $post . "' LIMIT 1;";
			$row = $conn->query($query)->fetch_row();
			$autor = $row[0];
			$topic = $row[1];
			$titlu = "Re: " . str_replace("Re: ",'',$row[2]);
			$continut = $row[3];
		}
		?>
         <form method="post" action="forum.php?ce=adauga-post<?php echo $get; ?>">
        <?php
			if(isset($_POST['submit'])){
			?>
             <h2>Aveti erori</h2>
             <div class="registerForm">
            <?php
			
				(int)$err = 0;
				
				if(strlen($_POST['titlu']) < 5){
					$err++;
					echo '<div class="error">Titlul trebuie sa fie mai lung!</div>';
				}	
				
				if(strlen($_POST['body']) < 15){
					$err++;
					echo '<div class="error">Replica trebuie sa fie mai lunga!</div>';
				}
				
				if(!$err){
					include_once "classes/forumFcts.php";
					ForumFcts::adaugaPost(stripslashes(htmlspecialchars($_POST['titlu'])),stripslashes(htmlentities($_POST['body'],ENT_NOQUOTES)),getUserFromCookie(),$topic);
					ob_clean();
					header("Location: forum.php?id_topic=$topic");
				}
				
			?>
            </div>
            <?php
			}
			?>
            <h2>Adauga o replica</h2>
             <div class="registerForm">
               	 <div class="item">
					<label for="titlu" class="column">Titlu:</label><br />
					<input name="titlu" id="titlu" value="<?php if(isset($_POST['titlu']))echo $_POST['titlu']; else echo $titlu; ?>" class="field" type="text" />
				</div>
                 <div class="item">
                 	<div class="formatari">
                    	<a href="javascript:void(0)" onClick="adaugaEdit('b','body')" title="Litere ingrosate"><img src="images/icons/text_bold.png" /></a>
                    	<a href="javascript:void(0)" onClick="adaugaEdit('i','body')" titlke="Litere aplecate"><img src="images/icons/text_italic.png" /></a>
                        <a href="javascript:void(0)" onClick="adaugaEdit('u','body')" title="Text subliniat"><img src="images/icons/text_underline.png" /></a>
                        <a href="javascript:void(0)" onClick="adaugaEdit('s','body')" title="Text taiat"><img src="images/icons/text_strikethrough.png" /></a> &nbsp; &nbsp;
                        <a href="javascript:void(0)" onClick="adaugaEdit('st','body')" title="Text aliniat la stanga"><img src="images/icons/text_align_left.png" /></a>
                    	<a href="javascript:void(0)" onClick="adaugaEdit('ct','body')" title="Text aliniat la centru"><img src="images/icons/text_align_center.png" /></a>
                        <a href="javascript:void(0)" onClick="adaugaEdit('dr','body')" title="Text aliniat la dreapta"><img src="images/icons/text_align_right.png" /></a>
                        <a href="javascript:void(0)" onClick="adaugaEdit('sh','body')" title="Text cu umbra"><img src="images/icons/style.png" /></a>
                    </div>
					<label for="body" class="column">Replica:</label><br />
					<textarea name="body" id="body" class="textarea"><?php 
					if(isset($_POST['body']))
						echo $_POST['body'];
					elseif(isset($_GET['quote'])){
					
							$link = $topic .  "#post" . $post;
							echo '[quote autor="' . $autor . '" link="' . $link . '"]' . $continut . '[/quote]';
							unset($post,$link,$topic,$autor);
						
					}
					?></textarea>
				</div>
             </div>
             <div>
				<input value="Adauga" class="submit" type="submit" name="submit" />
			</div>
           </form>
            <?php
			
		}
	}
	else
		Forum::showCategory();
	
	unset($care,$pagina,$sortDupa,$cum);
}

class Forum{


	public function showCategory($id = NULL){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query1 = "SELECT * FROM `" . $TABLES['forumCateg'] . "`";

		if( (isset($id)) && (is_numeric($id)) ){
			$query1 .= " WHERE `" . $FIELDS['forumCateg']['id'] . "` = '" . $id . "' ";
		}
		
		$query1 .= " ORDER BY `" . $FIELDS['forumCateg']['order'] . "`;";
		
		$result1 = $conn->query($query1); $maxCateg = $result1->num_rows;
		
		echo '<div class="forum">' . "\n\n";
		(int)$i = 0;
		
		include_once "classes/users.php";
		$user = determinaStatus();
		
		while($row1 = $result1->fetch_array()){
			
			$i++;
			(string)$adminT = (string)$adminX = "";
			if($user->areTot()){
				$adminT = '<a href="javascript:void(0)" onClick="Admin.mutaCateg(\'up\',' . $row1[$FIELDS['forumCateg']['order']] . ',' . $maxCateg . ')" style="float:right; margin-top:-15px; z-index:20;" title="Muta in sus"><img src="images/icons/arrow_up.png" /></a><a href="javascript:void(0)" onClick="Admin.mutaCateg(\'down\',' . $row1[$FIELDS['forumCateg']['order']] . ',' . $maxCateg . ')" style="float:right; margin-top:-15px; z-index:20;" title="Muta in jos"><img src="images/icons/arrow_down.png" /></a>';
				$adminX = '<a href="acp.php?ce=modifica-categ&amp;care=' . $row1[$FIELDS['forumCateg']['id']] . '" title="Editeaza categoria" style="margin-left: 5px; margin-top:-15px; z-index:20;"><img src="images/icons/pencil.png" /></a>';
			}
			echo '<div class="categorie">' ."\n\n";
			echo '	<div class="titlu"><div id="tit' . $i . '">' . $row1[$FIELDS['forumCateg']['titlu']] . $adminX . '</div>' . $adminT ."</div>\n";			echo '	<div id="topic' . $i . '">';
			$id1 = $row1[$FIELDS['forumCateg']['id']];
			
			$query2 = "SELECT * FROM `" . $TABLES['forumForums'] . "` WHERE `" . $FIELDS['forumForums']['id_categ'] . "` = '" . $id1 . "' ORDER BY `" . $FIELDS['forumForums']['titlu'] . "`;";
			$result2 = $conn->query($query2);
			
			while($row2 = $result2->fetch_array()){
			
				
				(string)$admin = "";
				if($user->areTot())
					$admin = '<a href="acp.php?ce=modifica-thread&amp;care=' . $row2[$FIELDS['forumForums']['id']] . '" title="Modifica thread"><img src="images/icons/pencil.png" /></a><a href="acp.php?ce=muta-thread&amp;care=' . $row2[$FIELDS['forumForums']['id']] . '" title="Muta thread" ><img src="images/icons/arrow_turn_left.png" /></a><a href="javascript:void(0)" onClick="if(confirm(\'Chiar vrei sa stergi threadu-ul asta?\'))Admin.stergeThread(' . $row2[$FIELDS['forumForums']['id']] . ')" title="Sterge thread"><img src="images/icons/cross.png" /></a>';
				
				echo "\n" . '	<div class="topic" id="thread' . $row2[$FIELDS['forumForums']['id']] . '">' . "\n";
				echo '		<div class="titluTopic">
								<a href="forum.php?id_thread=' . $row2[$FIELDS['forumForums']['id']] . '">' . $row2[$FIELDS['forumForums']['titlu']] . "</a>" . $admin . "\n";
				echo '			<div class="comentariu">' . $row2[$FIELDS['forumForums']['descriere']] . '</div>' . "\n";
				echo '		</div>' . "\n";
				echo '		<div class="numaratori">' . $row2[$FIELDS['forumForums']['nrTopics']] . " Topicuri<br />";
				echo $row2[$FIELDS['forumForums']['nrPosts']] . " Replici </div>\n";		
				echo '		<div class="uReplica">' . "\n";
				self::showDetailsPost($row2[$FIELDS['forumForums']['lastPost']]);
				echo '		</div>'."\n";
				echo "	</div>\n\n";

			}
			
			echo "	</div>\n";
			echo "</div>\n\n\n";
		
		}
		
		echo "</div>\n";
		
		include_once "classes/users.php";
		$user = determinaStatus();
		if($user->areTot()){
			echo '		<div class="adauga"><a href="acp.php?ce=adauga-catergorie">Creaz&#259; o noua categorie</a></div>' . "\n";
			if(!is_null($id))
				echo '		<div class="adauga"><a href="acp.php?ce=adauga-thread&amp;care=' . $id . '">Creaz&#259; un nou thread</a></div>' . "\n";
		}
	}
	
	
	private function showDetailsPost($id,$showLi = false,$showBR = false){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;

		$query = "SELECT `" . $FIELDS['forumPosts']['autor'] . "`,`" . $FIELDS['forumPosts']['data'] . "` FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id'] . "` = '" . $id . "' LIMIT 1;";
		$result = $conn->query($query);
		
		if($showLi)
			$showBR = true;
		
		if($result->num_rows){
			$row = $result->fetch_array();
			if($showLi)
				echo "		<li>\n<h4>";
			echo '			In ' . self::showRoot($id,'post',2) . ",\n";
			if($showLi)
				echo "</h4>";
			echo '			<span class="data"> ' . formatData($row[1]) . "</span>\n";
			//if($showBR)
				echo '<br />';
			echo '			Postat de <span><a href="users.php?unde=profil&amp;user=' . $row[0] . '">' . $row[0]  . "</a></span>\n";
			if($showLi)
				echo "		</li>\n";
		}
	}
	
	
	public function showTopicsInForums($id_forums,$pagina,$sorteazaDupa = "data",$cum = "ASC"){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$posibilDupa = array('titlu','starter','vizionari','replici','data','lastPost');
		$posibilCum = array("ASC","DESC");
		
		if( (!in_array($sorteazaDupa,$posibilDupa)) || (!in_array($cum,$posibilCum)) ){
			$sorteazaDupa = 'data';
			$cum = 'ASC';
		}
		
		
		$query = "SELECT * FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $id_forums . "' ORDER BY `" . $FIELDS['forumTopics'][$sorteazaDupa] . "` " . $cum . ";";
		$result = $conn->query($query);
		$nrRows = $result->num_rows;
		(int)$nrPagini = 0;
		
		(int)$max = 30;
		
		if($nrRows > $max){
			$nrPagini = ceil($nrRows / $max);
			unset($result,$query);
			(int)$limitaInf = ($pagina - 1) * $max;
			$query = "SELECT * FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id_forums'] . "` = '" . $id_forums . "' ORDER BY `" . $FIELDS['forumTopics'][$sorteazaDupa] . "` " . $cum . " LIMIT " . $limitaInf . "," . $max . ";";
			$result = $conn->query($query);
		}

		if($result->num_rows){
		
			//echo self::showRoot($id_forums,'forums',3);
			echo '	<div class="forums">' . "\n";
			echo '		<div class="topic">' . "\n";
			echo '			<div class="stare"></div>' . "\n";
			echo '			<div class="titlu"><a href="forum.php?id_thread=' . $id_forums . returnSort() . '&amp;sortDupa=titlu">Topicuri</a></div>' . "\n";
			echo '			<div class="starter"><a href="forum.php?id_thread=' . $id_forums . returnSort() . '&amp;sortDupa=starter">Inceput de</a></div>' . "\n";
			echo '			<div class="replici"><a href="forum.php?id_thread=' . $id_forums . returnSort() . '&amp;sortDupa=replici">Replici</a></div>' . "\n";
			echo '			<div class="vizualizari"><a href="forum.php?id_thread=' . $id_forums . returnSort() . '&amp;sortDupa=vizionari">Vizualizari</a></div>' . "\n";
			echo '			<div class="uReplica"><div class="headerRep"><a href="forum.php?id_thread=' . $id_forums . returnSort() . '&amp;sortDupa=lastPost">Ultima replica</a></div></div>' . "\n";
			echo '		</div>' . "\n";
			while($row = $result->fetch_array()){
			
				$icon = "images/icons/";
				switch($row[$FIELDS['forumTopics']['stare']]){
				case 'deschis':
					$icon .= "forum_deschis.png";
					break;
				case 'inchis':
					$icon .= "forum_inchis.png";
					break;
				case 'hot':
					$icon .= "forum_hot.png";
					break;
				default:
					$icon .= "forum_deschis.png";
				}
				
				(string)$admin = "";
				include_once "classes/users.php";
				$user = determinaStatus();
				if($user->areTot())
					$admin = '<a href="javascript:void(0)" style="float:right;" onClick="if(confirm(\'Chiar vrei sa stergi topic-ul asta?\'))Admin.stergeTopic(' . $row[$FIELDS['forumTopics']['id']] . ')" title="Sterge Topic"><img src="images/icons/cross.png" /></a><a href="acp.php?ce=muta-topic&amp;care=' . $row[$FIELDS['forumTopics']['id']] . '" title="Muta Topic" ><img src="images/icons/arrow_turn_left.png" style="float:right;" /></a><a href="javascript:void(0)" onClick="Admin.schimbaStareTopic(' . $row[$FIELDS['forumTopics']['id']] . ')" style="float:right;" title="Schimba starea topicului"><img src="images/icons/arrow_refresh.png" /></a><a href="acp.php?ce=modifica-topic&amp;care=' . $row[$FIELDS['forumTopics']['id']] . '" title="Modifica topic" style="float:right;"><img src="images/icons/pencil.png" /></a>';
				
				$id = $row[$FIELDS['forumTopics']['id']];
				echo '		<div class="topic" id="topic' . $id . '">' . "\n";
				//echo self::showRoot(,'forums',2);
				echo '			<div class="stare"><img src="' . $icon . '" title="Topicul este ' . $row[$FIELDS['forumTopics']['stare']] . '" /></div>' . "\n";
				echo '			<div class="titlu"><a href="forum.php?id_topic=' . $id . '">' . $row[$FIELDS['forumTopics']['titlu']] . "</a>" . $admin . "</div>\n";		
				echo '			<div class="starter">' . $row[$FIELDS['forumTopics']['starter']] . "</div>\n";
				echo '			<div class="replici">' . $row[$FIELDS['forumTopics']['replici']] . "</div>\n";
				echo '			<div class="vizualizari">' . $row[$FIELDS['forumTopics']['vizualizari']] . "</div>\n";
				echo '			<div class="uReplica">' . "\n";
				self::showDetailsPost($row[$FIELDS['forumTopics']['lastPost']],false,true);
				echo '			</div>' . "\n";
				echo "		</div>\n";
			}
			include_once "classes/users.php";
			$user = determinaStatus();
			if($user->esteLogat())
				echo '		<div class="adauga"><a href="forum.php?ce=adauga-topic&amp;unde=' . $id_forums . '">Creaz&#259; un nou topic</a></div>' . "\n";
			echo "	</div>\n";
		}
		else{
			include_once "classes/users.php";
			$user = determinaStatus();
			if($user->esteLogat())
				echo '		<div class="adauga"><a href="forum.php?ce=adauga-topic&amp;unde=' . $id_forums . '">Creaz&#259; un nou topic</a></div>' . "\n";
			echo('<div class="die"></div>');
		}
		
		for((int)$i = $nrPagini;$i >= 1;$i--){
			(string)$class = ' class="pagini"';
			if($i == $pagina)
				$class = ' id="presentPg"';
			echo '<a href="forum.php?id_thread=' . $id_forums  . getGetForum(). '&pg=' . $i . '"' . $class . '>' . $i . "</a>\n" ;
		}
		
		echo '<br />';
		
	}
	
	
	public function showPostsInTopic($id_topic,$pagina,$ttl = NULL){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		if(!is_null($ttl))
			$query = "SELECT * FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['autor'] . "` = '" . $ttl . "' ORDER BY `" . $FIELDS['forumPosts']['data'] . "` ASC;";
		else
			$query = "SELECT * FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id_topic'] . "` = '" . $id_topic . "' ORDER BY `" . $FIELDS['forumPosts']['data'] . "` ASC;";
		$result = $conn->query($query);
		$nrRows = $result->num_rows;
		(int)$nrPagini = 0;
		(int)$max = 12;
		if($nrRows > $max){
			unset($result,$query);
			(int)$limitaInf = ($pagina - 1)*$max;
			$nrPagini = ceil($nrRows / $max);
			if(!is_null($ttl))
				$query = "SELECT * FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['autor'] . "` = '" . $ttl . "' ORDER BY `" . $FIELDS['forumPosts']['data'] . "` ASC LIMIT " . $limitaInf . "," . $max . ";";
			else
				$query = "SELECT * FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id_topic'] . "` = '" . $id_topic . "' ORDER BY `" . $FIELDS['forumPosts']['data'] . "` ASC LIMIT " . $limitaInf . "," . $max . ";";
			$result = $conn->query($query);
		}
		
		(bool)$esteLogat = (bool)$admin = false;
		include_once "classes/users.php";
		$user = determinaStatus();
		
		if($result->num_rows){
			
			echo '<div class="topics">' . "\n";
			if($user->esteLogat())
				$esteLogat = true;
			if($user->areTot())
				$admin = true;

			while($row = $result->fetch_array()){
				
				$id = $row[$FIELDS['forumPosts']['id']];
				$detalii = veziDetalii($row[$FIELDS['forumPosts']['autor']]);
				if($detalii[2])
					$status = "offline";
				else
					$status = "online";
				$id_topic = $row[$FIELDS['forumPosts']['id_topic']];
				echo '<div class="hreview">';
				echo '	<div class="post" id="post' . $id . '">' . "\n";
				echo '		<div class="autor"><a href="forum.php?tml=' . $row[$FIELDS['forumPosts']['autor']] . '">' . $row[$FIELDS['forumPosts']['autor']] . 
								'</a><img src="images/icons/status_' . $status . '.png" title="'  . $row[$FIELDS['forumPosts']['autor']] . ' este acum ' . $status . '" /><div class="replici">Replici: ' . $detalii[0] . "</div>\n" .
								'
								<a href="users.php?unde=profil&amp;user=' . $row[$FIELDS['forumPosts']['autor']] . '" title="Vezi profil"><img src="images/icons/profil.png" /></a>
								<a href="mesaje.php?trimiteLa=' . $row[$FIELDS['forumPosts']['autor']] . '" title="Trimite mesaj"><img src="images/icons/trimite_mesaj.png" /></a>' .
							"</div>\n";
				echo '		<div class="postDets">' . "\n" . '<div class="postHeader">' . "\n";
				echo '			<div class="titlu"><a href="forum.php?id_topic=' . $id_topic . '#post' . $id . '" class="fn url">' . $row[$FIELDS['forumPosts']['titlu']] . "</a>";
				if(!is_null($ttl))
					echo ' <a href="forum.php?id_topic=' . $id_topic . '#post' . $id . '">(vezi topic)</a>';
				echo "			</div>\n";
				echo '			<div class="data">&laquo; <span class="pe">pe:</span>' . date("d m Y, H:i:s",$row[$FIELDS['forumPosts']['data']]) . " &raquo;</div>\n";
				echo '<abbr title="' . gmstrftime('%Y-%m-%dT%H:%M:%SZ',$row[$FIELDS['forumPosts']['data']]) . '" class="dtreviewed" style="display:none;">' . date('d m Y',$row[$FIELDS['forumPosts']['data']]) . '</abbr>';
				if($esteLogat)
					echo '			<div class="quote"><a href="forum.php?ce=adauga-post&amp;quote=' . $id . '">D&#259; o replic&#259; cu citat</a></div>' . "\n";
				if($admin)
					echo '			<a href="javascript:void(0)" onClick="if(confirm(\'Chiar vrei sa stergi post-ul asta?\'))Admin.stergePost(' . $id . ')" title="Sterge Post" ><img src="images/icons/cross.png" style="float:right;" /></a><a href="acp.php?ce=muta-post&amp;care=' . $id . '" title="Muta Post" ><img src="images/icons/arrow_turn_left.png" style="float:right;" /></a><a href="acp.php?ce=modifica-post&amp;care=' . $id . '" title="Modifica post" style="float:right;"><img src="images/icons/pencil.png" /></a>' . "\n";
				echo "		</div>\n";
				
				$body = $row[$FIELDS['forumPosts']['body']];
				$body = self::bbDecode($body);
				$quote = self::getQuote($body);
				
				if($quote[0]){
					$body = $quote[2];
					$unquote = $quote[3];
				}
				
				if(strlen($body) > 250)
					$class = "body";
				else
					$class = "bodyPreset";
				echo '		<div class="' . $class . '">' . "\n";
				
				if($quote[0])
					echo '			<div class="quoteTitle">' . $quote[1] . '</div>' . "\n";
				if($quote[0])
					echo '			<div class="quote">' . "\n";
				if($quote[0])
					echo $body;
				if($quote[0])
					echo '			</div>' . "\n";
				if($quote[0])
					echo $quote[3];
				else
					echo $body;
				echo  "		</div>\n\n";
				if($detalii[1])
					echo '		<div class="semnatura">' . $detalii[1] . '</div>' . "\n";
				echo "	  	</div>";
				echo "	</div>\n";
				echo "</div>\n";
				
		
			}
		}
		else
			echo('<div class="die">');
			
		include_once "classes/forumFcts.php";
		if($esteLogat && ForumFcts::poatePosta($id_topic))
				echo '		<div class="adauga"><a href="forum.php?ce=adauga-post&amp;unde=' . $id_topic . '">Adaug&#259; o replic&#259;</a></div>' . "\n";
			echo "</div>\n";
		
		for((int)$i = $nrPagini;$i >= 1;$i--){
			(string)$class = ' class="pagini"';
			if($i == $pagina)
				$class = ' id="presentPg"';
			echo '<a href="forum.php?id_topic=' . $id_topic  . getGetForum(). '&pg=' . $i . '"' . $class . '>' . $i . "</a>\n" ;
		}
			
	
	}
	
	public function showRoot($id_obj,$tip,$deep = 4,$prezent = 0,$titlu = false){
		
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		
		$tipInitial = $tip;
		$titluri = array();
		$iduri = array();
		$deCare = array();
		(bool)$aFost = false; (int)$idPost = 0;
		
		if($tip == "post"){
			
			$aFost = true; $idPost = $id_obj;
			
			$query = "SELECT `" . $FIELDS['forumPosts']['id_topic'] . "` FROM `" . $TABLES['forumPosts'] . "` WHERE `" . $FIELDS['forumPosts']['id'] . "` = '" . $id_obj . "' LIMIT 1;";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			$tip = $deCare[count($deCare)] = "topic";
			$id_obj = $iduri[count($iduri)] = $row[0];
			$deep--;
			
		}
		
		if($tip == "topic"){
			if($deep > 0){
			
				$query = "SELECT `" . $FIELDS['forumTopics']['titlu'] . "`,`" . $FIELDS['forumTopics']['id_forums'] . "` FROM `" . $TABLES['forumTopics'] . "` WHERE `" . $FIELDS['forumTopics']['id'] . "` = '" . $id_obj . "' LIMIT 1;";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				if($tipInitial == $tip)
					$iduri[count($iduri)] = $prezent;
				$titluri[count($titluri)] = $row[0];
				$id_obj = $iduri[count($iduri)] = $row[1];
				$deCare[count($deCare)] = "topic";
				$tip = "thread";
				$deep--;
				
			}
		}
		
		if($tip == "thread"){
			if($deep > 0){
			
				$query = "SELECT `" . $FIELDS['forumForums']['titlu'] . "`,`" . $FIELDS['forumForums']['id_categ'] . "` FROM `" . $TABLES['forumForums'] . "` WHERE `" . $FIELDS['forumForums']['id'] . "` = '" . $id_obj . "' LIMIT 1;";
				$result = $conn->query($query);
				$row = $result->fetch_row();
				if($tipInitial == $tip)
					$iduri[count($iduri)] = $prezent;
				$titluri[count($titluri)] = $row[0];
				$id_obj = $iduri[count($iduri)] = $row[1];
				$deCare[count($deCare)] = "thread";
				$tip = "categ";
				$deep--;
			}
		}
		
		if($tip == "categ"){
			if($deep > 0){
				$query = "SELECT `" . $FIELDS['forumCateg']['titlu'] . "` FROM `" . $TABLES['forumCateg'] . "` WHERE `" . $FIELDS['forumCateg']['id'] . "` = '" . $id_obj . "' LIMIT 1;";
				$result = $conn->query($query);
				if($tipInitial == $tip)
					$iduri[count($iduri)] = $prezent;
				$row = $result->fetch_row();
				$deCare[count($deCare)] = "categ";
				$titluri[count($titluri)] = $row[0];
			}
		}
	
		(string)$return = (string)$diez = "";
		for((int)$i = count($titluri) - 1;$i >= 0;$i--){
			
			if($aFost)
				$diez = "#post" . $idPost;;
				
			
			if(!$titlu)
				$return .= '<a href="forum.php?id_' . $deCare[$i] . '=' . $iduri[$i] . $diez . '" class="root">';
			
			$return .= $titluri[$i];
			
			if(!$titlu)
				$return .= "</a>";
				
			if($i > 0)
				$return .= " &gt; ";
		
		}
		//print_r($iduri);
		
		return $return;
		
	}
	
	public function ultimeleInForum(){
	
		include_once "classes/config.php";
		global $FIELDS,$TABLES,$conn;
		
		$query = "SELECT `" . $FIELDS['forumPosts']['id'] . "` FROM `" . $TABLES['forumPosts'] . "` ORDER BY `" . $FIELDS['forumPosts']['data'] . "` DESC LIMIT 5;";
		$result = $conn->query($query);
		
		while($row = $result->fetch_row())
			self::showDetailsPost($row[0],true);
		
	}
	
	
	public function getQuote($body){
		
		if( (ereg('^\[quote',$body)) || (ereg('^\[/quote\]',$body)) ) {
			
			
			(string)$link = (string)$autor = "";
			
			(bool)$was = false;
			
			$quote = substr($body,strpos($body,"[/quote]")+8);
			$body = substr($body,0,strpos($body,"[/quote]"));
			
			
			if(	ereg('^\[quote autor="',$body) ) {
			
				$was = true;
				$body = str_replace('[quote autor="','',$body);
				$autor = substr($body,0,strpos($body,'"'));
				$body = str_replace($autor.'"','',$body);
				 
			}
			
			if( ereg('link="',$body) ) {
			
				if(!$was){
					
					$body = str_replace('[quote','',$body);
						
				}
			
				$body = str_replace(' link="','',$body);
				$link = substr($body,0,strpos($body,'"'));
				$body = str_replace(array($link.'"]','[/quote]'),'',$body);
			
			}
			
			if( (strlen($link) > 0) && (strlen($autor) > 0) ){
			
				$link = '<a href="forum.php?id_topic=' . $link . '">Citat din postul lui ' . $autor . '</a>';
				
			}
			
			elseif( (strlen($autor) > 0) && (strlen($link) == 0) ){
			
				$link = 'Citat din postul lui ' . $autor;
			
			}
			
			elseif( (strlen($autor) == 0) && (strlen($link) > 0) ){
			
				$link = '<a href="forum.php?id_topic=' . $link . '">Citat</a>';
			
			}
			
			else{
				
				$link = "Citat:";
				$body = str_replace(array('[quote]','[/quote]'),'',$body);
			
			}
			
			return array(true,$link,$body,$quote);
				
		
		}
		else
			return array(false,NULL,$body,"");
	}
	
	public function bbDecode($body){
		
		$ce = array('[b]','[/b]','[u]','[/u]','[i]','[/i]','[s]','[/s]','&','[st]','[/st]','[ct]','[/ct]','[dr]','[/dr]','[sh]','[/sh]');
		$cuCe = array('<span class="bold">','</span>','<span class="underline">','</span>','<span class="italic">','</span>',
'<span class="taiat">','</span>','&amp;','<div class="stanga">','</div>','<div class="centru">','</div>','<div class="dreapta">','</div>','<span class="umbra">','</span>');
		return str_replace($ce,$cuCe,$body);
	
	}
	
}

?>