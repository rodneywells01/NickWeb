<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php $admin = logged_in(); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Nicholas Nasibyan</title>
	<link rel="stylesheet" type="text/css" href="stylesheets/footer.css">
	<link rel="stylesheet" type="text/css" href="stylesheets/header.css">
	<link rel="stylesheet" type="text/css" href="stylesheets/public.css">
	<link href='http://fonts.googleapis.com/css?family=Yellowtail|Parisienne|Mr+De+Haviland|Pinyon+Script' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="Hover-master/css/hover.css">
	<script type="text/javascript" src="jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="includes/jquery-ui-1.11.4/jquery-ui.js"></script>
	<script type="text/javascript" src="includes/timepicker.js"></script>
	<script type="text/javascript" src="nick.js"></script>
	<script type="text/javascript" src="scripts/popup.js"></script>
	<script type="text/javascript" src="scripts/events.js"></script>
	<script>
		var url ="scripts/events.js";
		$.getScript(url);
	</script>
	<link href='http://fonts.googleapis.com/css?family=Questrial|Noto+Sans|Arvo|Oxygen' rel='stylesheet' type='text/css'>
</head>
<body>

<?php 
if (isset($_GET["redirect"])) {
	// This is a redirect after page update. 
	// Insert hidden div to load new page. 
	echo "<div id=\"pagereturndiv\">{$_GET["redirect"]}</div>"; 
} ?>

<div id="background"></div>
<div id="screen"> <!-- Wrapper -->
	<div id="outerwebsitecontent">
		<div id="swagtest">

	<div id="websitecontent">

	<div id="header"> <!-- Content to be displayed at top of page.  -->
		<div id="portrait"> 
			<img src="edited.png">
		</div>
		<div id="banner"> <!-- Contains Name and Tagline -->			
			<div id="name"><i>Nicholas Nasibyan</i></div>								
			<div><img style="width: 50%;" src="divideredit.png"></div>		<!-- http://docs.creiden.com/circleflip/wp-content/uploads/2014/04/divider.jpg						 -->
			<div id="tagline">Pianist - Composer - Organist</div>
		</div>
		<div id="tabs"> <!-- Cotains Navigation Tabs -->
		<!-- hvr-fade-selected -->
			<a href="main"><div class="hvr-border-fade hvr-fade-selected tabselected">Home</div></a>
			<a href="about"><div class="hvr-border-fade">About</div></a>			
			<a href="events"><div class="hvr-border-fade">Events</div></a>
			<a href="songs"><div class="hvr-border-fade">Songs</div></a>
			<a href="videos"><div class="hvr-border-fade">Videos</div></a>
			<a href="contact"><div class="hvr-border-fade">Contact</div></a>
			<?php if ($admin) { ?>
			<a href="controlpanel"><div class="hvr-border-fade">Control Panel</div></a>
			<?php } ?>
		</div>
	</div>
	<div id="content"> <!-- Table Row -->
	<div id="contentwrap"> <!-- Table Cell -->
	<div id="contenttable"> <!-- Table -->
	<div id="innercontentwrap"> <!-- Table Cell -->

<?php include_once("footer.php"); ?>
