<?php $active_page = "contact"; ?>
<?php require_once("includes/functions.php"); ?>
<?php require("includes/connection.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<!-- <link rel="stylesheet" type="text/css" href="stylesheets/contact.css"> -->
<link rel="stylesheet" type="text/css" href="stylesheets/test.css">
<script type="text/javascript" src="scripts/email.js"></script>
<?php $admin = logged_in(); ?>
<?php global $connection; ?>
<?php $errors = errors(); ?>

<?php 
if(isset($_POST["submit"])) {
	// Acquire data. 
	$requiredfields = array("contactmessage", "email", 
		"location1", "location2", "facebook", "twitter", "linkedin");

	validate_presences($requiredfields); 

	if(empty($errors)) {
		// All data available. 
		$contactmessage = mysql_prep($_POST["contactmessage"]);
		$email = mysql_prep($_POST["email"]);
		$location1 = mysql_prep($_POST["location1"]);
		$location2 = mysql_prep($_POST["location2"]);
		$facebook = mysql_prep($_POST["facebook"]);
		$twitter = mysql_prep($_POST["twitter"]);
		$linkedin = mysql_prep($_POST["linkedin"]);

		// Generate Google Maps URL.
		// Thanks http://asnsblues.blogspot.com/2011/11/google-maps-query-string-parameters.html
		$location1nospace = str_replace(" ", "+", $location1);
		$location2nospace = str_replace(" ", "+", $location2);
		$gmapslocationquery = "?q=" . $location1nospace . "+" . $location2nospace; 
		// $gmapszoomlevel = "zoom=6";
		$gmapszoomlevel = "";
		$gmapsrequest =  "http://maps.google.com/" . $gmapslocationquery . "&" . $gmapszoomlevel;

		$query = "UPDATE contact SET ";
		$query .= "contactmessage = '{$contactmessage}', ";
		$query .= "email = '{$email}', ";
		$query .= "location1 = '{$location1}', ";
		$query .= "location2 = '{$location2}', ";
		$query .= "gmaps = '{$gmapsrequest}', "; 
		$query .= "facebook = '{$facebook}', ";
		$query .= "twitter = '{$twitter}', ";
		$query .= "linkedin = '{$linkedin}' ";
		
		$result = mysqli_query($connection, $query); 

		if ($result) {
			$_SESSION["message"] = "Contact info succesfully updated!";
		} else {
			$_SESSION["message"] = "Contact info failed to update!";
		}
	} 
}
?>

<?php 
	// Acquire necessary data. 
	$query = "SELECT * FROM contact ";
	$query .= "WHERE id = 1 "; 
	$query .= "LIMIT 1";

	$result = mysqli_query($connection, $query);
	$contactinfo = mysqli_fetch_assoc($result);
?>

<?php echo message(); ?> 
<?php echo form_errors($errors); ?>
<?php 
// Generate form. 
$formbody = "<div class=\"emailinputwrap\"><input id=\"emailtitle\" class=\"default noborderfocus\" type=\"text\" value=\"Title...\" /></div>
						<div class=\"emailinputwrap\"><input id=\"emailuser\" class=\"default noborderfocus\"  type=\"text\" value=\"Your Email... (Optional)\" /></div>
						<div class=\"emailinputwrap\"><textarea id=\"emailbody\" class=\"default noborderfocus\">Message...</textarea></div>
						<div onclick=\"construct_email();\" id=\"promptsubmit\" class=\"emailinputwrap hvr-sweep-to-right\">Send Email!</div>";

generate_prompt("Send Nick a Message!", $formbody, "email");						
?>

<div id="contactverticalwrap">
	<?php if ($admin) { ?>
	<form action="contact.php" method="post"> 
	<?php } ?>
	
	<div class="title emphasis" style="margin-bottom: 20px;">Connect with Nicholas</div>

	<div class="contactrow">
		<div class="sidecolumn">
			<div onclick="prompt_popup('email');" class="iconwrap ">
				<a><img class="hvr-grow" src="icons/email.png"></a>
			</div>
		</div>
		<div id="clientemail" class="contactinfo botline">	
			<?php 
				// Display email. 
				$output = ""; 
				if($admin) { $output.= "<input class=\"fullwidth\" type=\"text\" name=\"email\" value=\""; }
				$output.= htmlentities($contactinfo["email"]); 
				if($admin) {$output.= "\" />"; } 
				echo $output; 
			?>
		</div>
		<div class="sidecolumn"></div>
	</div>

	<div class="contactrow">
		<div class="sidecolumn"><div class="iconwrap hvr-grow"><a href="<?php echo $contactinfo["gmaps"]; ?>"><img src="icons/home.png"></a></div></div>
		<div class="contactinfo botline ">
			<?php 
				// Display Location 
				$output = ""; 
				if($admin) { $output .= "<input class=\"fullwidth\" type=\"text\" name=\"location1\" value=\""; } 
				$output .= htmlentities(trim($contactinfo["location1"])); 
				if($admin) {$output .= "\" />"; } 
				else {$output .= "<br /><br />"; }							
				if($admin) { $output .= "<input class=\"fullwidth\" type=\"text\" name=\"location2\" value=\""; } 
				$output .= htmlentities(trim($contactinfo["location2"])); 
				if($admin) {$output .= "\" />"; } 
				echo $output; 
			?>
		</div>
		<div class="sidecolumn"></div>
	</div>

<?php 
	// Display Location 
	// $output = "<div class=\"addressrow\">"; 
	// if($admin) { $output .= "<input class=\"fullwidth\" type=\"text\" name=\"location1\" value=\""; } 
	// $output .= htmlentities(trim($contactinfo["location1"])); 
	// if($admin) {$output .= "\" />"; } 
	// $output .= "</div><div class=\"addressrow\">";						
	// if($admin) { $output .= "<input class=\"fullwidth\" type=\"text\" name=\"location2\" value=\""; } 
	// $output .= htmlentities(trim($contactinfo["location2"])); 
	// if($admin) {$output .= "\" />"; } 
	// $output .= "</div>";
	// echo $output; 
?>

	<div class="contactrow contacticon" style="border:none; text-align: center;">
		<div id="socialmedia">
		<?php if($admin) { ?>
		<table id="socialmediaadmin">
		<?php } ?>
		<?php display_contact_icon($admin, $contactinfo, "facebook") ?>
		<?php display_contact_icon($admin, $contactinfo, "twitter") ?>
		<?php display_contact_icon($admin, $contactinfo, "linkedin") ?>
		<?php if($admin) { ?>
		</table>
		<?php } ?>
		</div>
	</div>
	<?php if($admin) { ?>
	<div>
		<div class="shortrow savebutton">
			<input type="submit" name="submit" value="Save Changes">
			</form>
		</div>
	</div>
	<?php }	?>
</div>






