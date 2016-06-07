<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php $active_page = "main"; ?>
<link rel="stylesheet" type="text/css" href="stylesheets/main.css">
<?php $admin = logged_in(); ?>
<?php global $connection; ?>


<?php  
// Detects if changes have been made. 
if(isset($_POST["submit"])) {
	
	$required_fields = array("title", "content");
	validate_presences($required_fields);

	if(empty($errors)) {
		$title = mysql_prep($_POST["title"]);
		$content = mysql_prep($_POST["content"]);

		// Update content in database. 
		$query = "UPDATE main SET "; 
		$query .= "title = '{$title}', ";
		$query .= "content = '{$content}' ";
		$query .= "WHERE id = 1 ";
		$query .= "LIMIT 1";
		$result = mysqli_query($connection, $query);

		if ($result && mysqli_affected_rows($connection) == 1) {
			// Success. 
			$_SESSION["message"] = "Content successfully updated!";			
		} else {
			// Failure. 
			$_SESSION["message"] = "Content failed to update!";
		}
	}
	redirect_to("index.php?redirect=main");
}
?>

<?php $errors = errors(); ?>
<?php echo form_errors($errors); ?>

<?php 
if (isset($_SESSION["message"])) {
	generate_prompt("Notification", message(), 'main');
	echo "<script type=\"text/javascript\">prompt_popup('main',true);</script>";
}
?>

<?php 
// Collect content. 
$query = "SELECT * FROM main ";
$query .= "WHERE id = 1 ";
$query .= "LIMIT 1";
$result = mysqli_query($connection, $query); 
$mainpagedata = mysqli_fetch_assoc($result);

?>

<div id="mobileportrait" class="mobileshowme"> 
	<img src="nickpics/nickcoloreditoptimized4.png">
</div>

<?php if ($admin) { ?>
<form action="main.php" method="post">
<?php } ?>
<div class="left columncontent">
	<div style="position:relative;"class="title emphasis">
		<?php $output = ""; ?>
		<?php if($admin) { $output .= "<input class=\"fullwidth\" type=\"text\" name =\"title\" value=\""; }
		else {$output .= "<div class=\"overflowcenter\">"; } ?>
		<?php $contentstring = htmlspecialchars($mainpagedata["title"]); ?>
		<?php if (!$admin) { $contentstring = nl2br($contentstring); } ?>
		<?php $output .= $contentstring; ?>
		<?php if($admin) { $output .= "\" />"; }
		else {$output .= "</div>"; } ?>
		<?php echo $output; ?>
	</div>
	<div class="maincontent">
		<?php $output = ""; ?>
		<?php if($admin) { $output .= "<textarea name=\"content\">"; } ?>
		<?php $contentstring = htmlentities($mainpagedata["content"]); ?>
		<?php if (!$admin) { $contentstring = nl2br($contentstring); } ?>
		<?php $output .= $contentstring; ?>
		<?php if($admin) {$output .= "</textarea>";} ?>
		<?php echo $output; ?>
	</div>
</div>
<div class="right columncontent">
	<div class="title emphasis">
		Upcoming Events
	</div>
	<div id="eventscontainer">
			<?php 
			// Acquire a list of all events. 
			date_default_timezone_set("America/New_York");
			$query = "SELECT * FROM events ORDER BY datetime ASC"; 
			global $connection;
			$eventlistdata = mysqli_query($connection, $query); 
			$currentdate = date('m/d/Y h:i:s a', time());
			
			$finished = false; 
			$count = 0;
			$anEvent = false;
			while(($event = mysqli_fetch_assoc($eventlistdata)) && $finished == false) { ?>
				<div onclick="gen_eventlink(<?php echo $event["id"]?>)" class="eventcontainer hvr-border-fade hvr-custom" style="width: 100%; height:33%;">
					<div class="eventdatewrap">
						<div class="eventdisplay verticalnudge"  style="margin: 0;">
							<div class="verticaltextcenter2">
								<?php echo strtoupper(date("M", strtotime($event["datetime"]))); ?>
								<br />
								<?php echo date("j", strtotime($event["datetime"])); ?>
							</div>
						</div>	
					</div>
					<div class="eventdescriptionwrap">
						<div class="eventdescription centercontent">
							<?php echo htmlentities($event["name"]); ?>	
							<br />							
							<?php echo htmlentities($event["location"]); ?>							
							<?php $count +=1; ?>
						</div>	
					</div>						
				</div>
				<?php
				$anEvent = true;
				if ($count >= 3) {
					$finished = true;
				}
			}

			if (!$anEvent) {
				echo "Nick currently has no events! Check back soon!";
			}

			?>
	</div>
</div>
<!-- </div> -->
<?php if ($admin) { ?>
<div id="adminbar">
	<div class="bottomcontainer"><input class="mybutton" type="submit" name="submit" value="Save Changes"/></div>
</div>
</form>
<div style="clear: both;"></div>
<?php } ?>





