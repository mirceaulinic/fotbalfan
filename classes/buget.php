<?php

include_once "classes/users.php";
include_once "classes/bilete.php";


$esteLogat = $showLogin = false;
		
$user = determinaStatus();
		
if($user->esteLogat())
	$esteLogat = true;
else
	header('Location: index.php');


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Fotbalfan &bull; Evolutia bugetului meu</title>
	<link rel="stylesheet" href="style/style.css" media="all" type="text/css" />
  	 <link rel="stylesheet" href="style/bilete.css" media="all" type="text/css" />
   	<script type="text/javascript" src="javascripts/ftbf.js"></script>
   	<script type="text/javascript" src="javascripts/jquery.js"></script>
   	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="robots" content="index, follow" />
	<meta name="description" content="comunitate a pasionatilor de fotbal din Romania" />
	<meta name="keywords" content="fotbalfan, fotbal din romania, pariuri, comunitate pasionati de fotbal" />
	<link rel="shortcut icon" type="image/ico" href="images/favicon.png" />
	<script type="text/javascript" src="javascript/functions.js"></script>
</head>
<body>
    <?php
	
		
			
	?>
<div id="mainDiv">

		<div id="headerDiv">
			
			<div id="header">
				
				<a href="index.php"><img src="images/logo.jpg" alt="Fotbalfan" id="logo" /></a>
			
				<ul id="menu">
					<li><a href="index.php">Acas&#259;</a></li>
					<li><a href="stiri.php">&#350;tiri</a></li>
                	<?php
					if($showLogin){
					
					?>
		<li><a href="meciuri.php">Meciuri</a></li>
					<?php
					}
					else{
					?>
        <li><a href="meciuri.php">Pariuri</a></li>
            	    <?php
					}
					?><li><a href="foto.php">Foto</a></li>
					<li><a href="forum.php">Forum</a></li>
				</ul>
			</div><!-- end #header -->
		</div><!-- end #headerDiv -->
				
		

		
		<div id="abureliMic">

			<p>Evolutie buget</p><br />
				
		</div>
				
		<div id="contentAllDiv">	
		
			<div id="content">
            
				<img src="grafic.php" />
            	
			</div>
		</div>
				
		<div id="contentFooter">&nbsp;</div>
	</div>

<div id="pageFooter">
	
		Copyright &copy; Fotbalfan 2008 | Contact: <a href="mailto:fotbal@istic.ro" class="contact">fotbal@istic.ro</a>

	</div>
</body>
</html>