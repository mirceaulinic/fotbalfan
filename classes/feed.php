<?php

function showStiriRSS(){

	include_once "config.php";
	global $FIELDS,$TABLES,$conn;
	
	$query = "SELECT `" . $FIELDS['stiri']['titlu'] . "`,`" . $FIELDS['stiri']['id'] . "`,`" . $FIELDS['stiri']['body'] . "`,`" . $FIELDS['stiri']['dataMare'] . "` FROM `" . $TABLES['stiri'] . "` ORDER BY `" . $FIELDS['stiri']['data'] . "` LIMIT 10;";
	$result = $conn->query($query);
	
	while($row = $result->fetch_row()){
	
		echo '		<item>' . "\n";
		echo '			<title><![CDATA[' . $row[0] . ']]></title>' . "\n";
		echo '			<link>http://' . $_SERVER['HTTP_HOST'] . '/stiri.php?id=' . $row[1] . '</link>' . "\n";
		echo '			<description>' . substr($row[2],0,250) . '...</description>' . "\n";
		echo '			<pubDate>' . gmdate('D, d M Y H:i:s \G\M\T',$row[3]) . '</pubDate>' . "\n";
		echo '		</item>' . "\n";
	}
	
	$result->free; unset($query,$result);
	
}

ob_start();
header('Content-Type: application/rss+xml; charset=ISO-8859-1');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="0.92" xml:lang="en-US">' . "\n";
echo '	<channel>' . "\n";
echo '		<title>Fotbalfan</title>' . "\n";
echo '		<link>http://' . $_SERVER['HTTP_HOST'] . '/index.php</link>' . "\n";
echo '		<description><![CDATA[Ultimele stiri pe fotbalfan]]></description>' . "\n";
showStiriRSS();
echo '	</channel>' . "\n";
echo '</rss>';

?>