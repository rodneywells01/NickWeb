<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php $admin = logged_in(); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Nicholas Nasibyan's Website</title>
	<!-- 
	Menu Icon courtesy of https://www.iconfinder.com/icons/134216/hamburger_lines_menu_icon
 	-->
	<link rel="shortcut icon" href="nickpics/musicnotesclear.png">

	<link rel="stylesheet" type="text/css" href="stylesheets/footer.css">
	<link rel="stylesheet" type="text/css" href="stylesheets/header.css">
	<link rel="stylesheet" type="text/css" href="stylesheets/public.css">
	<link href='http://fonts.googleapis.com/css?family=Yellowtail|Parisienne|Mr+De+Haviland|Pinyon+Script' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="Hover-master/css/hover.css">
	<link rel="stylesheet" type="text/css" href="stylesheets/jquery.spin.css">
	<link href='http://fonts.googleapis.com/css?family=Questrial|Noto+Sans|Arvo|Oxygen' rel='stylesheet' type='text/css'>
	
	<script type="text/javascript" src="scripts/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="scripts/jquery.spin.js"></script>
	<script type="text/javascript" src="scripts/jquery-ui-1.11.4/jquery-ui.js"></script>
	<script type="text/javascript" src="scripts/timepicker.js"></script>
	<script type="text/javascript" src="scripts/nick.js"></script>
	<script type="text/javascript" src="scripts/popup.js"></script>
	<script type="text/javascript" src="scripts/events.js"></script>
</head>
<body>

<?php 
if (isset($_GET["redirect"])) {
	if(isset($_GET["event"])) {
		$_SESSION["event"] = $_GET["event"];
		redirect_to("index.php?redirect=events");
	}

	// This is a redirect after page update. 
	// Insert hidden div to load new page. 
	echo "<div id=\"pagereturndiv\">{$_GET["redirect"]}</div>"; 
} ?>

<div id="background"></div>
<div id="screen">
	<div id="nav-dropdown" onclick="navdisplay();">
		<img style="width:40px; height: 40px;" class="verticaltextcenter2" src="nickpics/menubutton.png">
	</div>
	
	<div id="nav-tabs-container">
		<div id="nav-tabs" onclick="navdisplay();">
			<a href="main"><div class="navbutton hvr-fade-selected tabselected">Home</div></a>
			<div class="divider"></div>
			<a href="about"><div class="navbutton">About</div></a>
			<div class="divider"></div>
			<a href="events"><div class="navbutton">Events</div></a>
			<div class="divider"></div>
			<a href="songs"><div class="navbutton">Compositions</div></a>
			<div class="divider"></div>
			<a href="videos"><div class="navbutton">Videos</div></a>
			<div class="divider"></div>
			<a href="contact"><div class="navbutton">Contact</div></a>
			<?php if ($admin) { ?>
			<div class="divider"></div>
			<a href="controlpanel"><div class="navbutton" class="hvr-border-fade">Control Panel</div></a>
			<?php } ?>
		</div>	
	</div>
	
	<div id="outerwebsitecontent">
	<div id="swagtest">
	<div id="websitecontent">
	<div id="header"> 
		<div id="portrait"> 
			<img src="nickpics/nickcoloreditoptimized4.png">
		</div>
		<div id="bannerwrap">
			<div id="banner"> <!-- Contains Name and Tagline -->			
			<div id="name"><i>Nicholas Nasibyan</i></div>								
			<div id="tagline">Pianist - Composer - Organist</div>
		</div>
		</div>
		<div id="tabs"> <!-- Cotains Navigation Tabs -->
			<a href="main"><div class="hvr-border-fade hvr-fade-selected tabselected">Home</div></a>
			<a href="about"><div class="hvr-border-fade">About</div></a>			
			<a href="events"><div class="hvr-border-fade">Events</div></a>
			<a href="songs"><div class="hvr-border-fade">Compositions</div></a>
			<a href="videos"><div class="hvr-border-fade">Videos</div></a>
			<a href="contact"><div class="hvr-border-fade">Contact</div></a>
		</div>
	</div>
	<div id="content">
	<div id="contentwrap"> 
	<div id="contenttable"> 
	<div id="innercontentwrap"> 
	<?php include_once("footer.php"); ?>
