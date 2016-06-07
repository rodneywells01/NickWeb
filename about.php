<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php $active_page = "about"; ?>
<link rel="stylesheet" type="text/css" href="stylesheets/about.css">
<?php $admin = logged_in(); ?> 
<?php global $connection; ?>
<?php $errors = errors(); ?>

<?php 
if (isset($_POST['submit']) && $admin) {
	// Customization for Nick
	$required_fields = array("aboutnick", "services", "experiences"); 
	validate_presences($required_fields);
	$aboutnick = mysql_prep($_POST["aboutnick"]);
	$services = mysql_prep($_POST["services"]);
	$experiences = mysql_prep($_POST["experiences"]);

	if(empty($errors)) {
		// Update the page. 
		$query = "UPDATE about SET "; 
		$query .= "aboutnick = '{$aboutnick}', ";
		$query .= "services = '{$services}', ";
		$query .= "experiences = '{$experiences}' ";
		$query .= "WHERE id = '1' ";
		$query .= "LIMIT 1";
		$result = mysqli_query($connection, $query); 
		confirm_query($result);
		$_SESSION["message"] = "About Page has been updated!";
	} else {
		$_SESSION["message"] = "About Page has failed to update! Not sure what happened. Try again?";
	}

	redirect_to("index.php?redirect=about");
}
?>

<?php echo form_errors($errors); ?>

<?php 
if (isset($_SESSION["message"])) {
	generate_prompt("Notification", message(), 'about');
	echo "<script type=\"text/javascript\">prompt_popup('about',true);</script>";
}
?>

<?php
$query = "SELECT * FROM about"; 
$result = mysqli_query($connection, $query); 
$data = mysqli_fetch_assoc($result)
?>

<?php if ($admin) { ?>
<form action="about.php" method="post"> 
<?php } ?>

<div class="rowcontent">
	<div style="text-align:center;" class="title shortrow emphasis">Biography</div>
	<div class="aboutcontentcontainer">
		<?php 
		if ($admin) { 
			echo "<textarea name=\"aboutnick\" >";
		}
		// Format string for display type. 
		$contentstring = htmlentities($data["aboutnick"]);
		if(!$admin) { $contentstring = nl2br($contentstring); }
		echo $contentstring;

		if ($admin) {
			echo "</textarea>";
		}
		?>
	</div>	
</div>

<div class="rowcontent nickpic">
	<img style="width:50%;" src="nickpics/076.JPG">
</div>

<div class="rowcontent nickpic" style="border-top: 1px solid;"></div>

<div class="rowcontent">
	<div style="text-align:center;" class="title emphasis">Services</div>
	<div class="aboutcontentcontainer" >
		<?php 
		if ($admin) { 
			echo "<textarea name=\"services\" >";
		}
		// Format string for display type. 						
		$contentstring = htmlentities($data["services"]);
		if(!$admin) { $contentstring = nl2br($contentstring); }
		echo $contentstring;

		if ($admin) { 
			echo "</textarea>";
		} ?>
	</div>
</div>

<div class="rowcontent nickpic">
	<img style="width:80%;" src="nickpics/theband.jpg">
</div>

<div class="rowcontent nickpic" style="border-top: 1px solid;"></div>

<div class="rowcontent">
	<div style="text-align:center;" class="title emphasis">Experience</div>
	<div class="aboutcontentcontainer" >
		
		<?php 
		if ($admin) { 
			echo "<textarea name=\"experiences\" >";
		} 
		// Format string for display type. 						
		$contentstring = htmlentities($data["experiences"]);
		if(!$admin) { $contentstring = nl2br($contentstring); }
		echo $contentstring;

		if ($admin) { 			
			echo "</textarea>";
		} ?>
	</div>
</div>

<div class="rowcontent nickpic">
	<img style="width:80%;" src="nickpics/markrusselnored.jpg">
</div>

<?php if ($admin) { ?>
	<input class="mybutton centercontent" style="margin-top: 10px;"type="submit" name="submit" value="Save Changes"/>
<?php } ?>
</form>
