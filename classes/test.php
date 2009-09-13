<?php
include "config.php";
$query = "SELECT `" . $FIELDS['facils']['maxBet'] . "` FROM `" . $TABLES['facils'] . "` WHERE `" . $FIELDS['facils']['id'] . "` = '1' LIMIT 1;";
					$row = $conn->query($query)->fetch_row();
					$max = $row[0];
	echo $max;

?>