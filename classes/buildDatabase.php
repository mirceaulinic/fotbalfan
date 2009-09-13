<?php

include_once "config.php";

$fisier = fopen("aaa.sql","r+");
$sql = fread($fisier, 1048576);

$query = explode(";",$sql);

for($i = 0;$i < count($query);$i++)
	$conn->query($query[$i]);

?>