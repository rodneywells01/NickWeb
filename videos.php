<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php $active_page = "videos"; ?>
<link rel="stylesheet" type="text/css" href="stylesheets/videos.css">
<?php $admin = logged_in(); ?>
<?php global $connection; ?>
<?php $errors = errors() ?>

<?php 
if(isset($_POST["submit"]) && $admin) {
	// User adding a song. 
	$requiredfields = array("newvideo");
	validate_presences($requiredfields);

	if(empty($errors)) {
		$newsong = mysql_prep($_POST["newvideo"]);

		$query = "INSERT INTO videos (";
		$query .= " videocode";
		$query .= ") VALUES (";
		$query .= " '{$newsong}'";
		$query .= ")";

		$result = mysqli_query($connection, $query); 
		if ($result) {
			// Success
			$_SESSION["message"] = "Video successfully added!";
		} else {
			// Failure
			$_SESSION["message"] = "Video failed to add!";
		}	
		redirect_to("index.php?redirect=videos");
	} else {
		$_SESSION["message"] = "Ruh-roh! There was an error";
	}
} else if (isset($_GET["deleteid"]) && $admin) {
	// User is deleting a song. 
	$deleteid = $_GET["deleteid"];
	remove_from_table($deleteid, $active_page, $active_page);
} else if(isset($_GET["movedirection"]) && $admin) {
	// We're moving a songs position instead. 
	$moveid = $_GET["moveid"];
	$directiontomove = $_GET["movedirection"];
	$destinationid = $moveid + $directiontomove;


	// Check to see if move is performable. 
	$query = "SELECT * FROM videos"; 
	$result = mysqli_query($connection, $query); 
	$numentries = mysqli_num_rows($result);

	$redirectlink = $active_page . ".php";
	if ($destinationid < 1) {
		// ID is too low. 	
		$_SESSION["message"] = "Cannot move this video up!"; 
		redirect_to("index.php?redirect=videos");
	} else if ($destinationid > $numentries) {
		$_SESSION["message"] = "Cannot move this video down!";
		redirect_to("index.php?redirect=videos");
	}

	/*
	INSTRUCTIONS: 
	1. Store destination in temp var. 
	2. Delete destination. 
	3. Update position. 
	4. Reinsert updated destination. 
	*/

	// 1 - Selecting
	$query = "SELECT * FROM {$active_page} WHERE id = {$destinationid} LIMIT 1;"; 
	$result = mysqli_query($connection, $query); 
	confirm_query($result);
	$deleteditem = mysqli_fetch_assoc($result);
	
	// 2 - Deleteing
	$query = "DELETE FROM {$active_page} WHERE id = {$destinationid} LIMIT 1;";
	$result = mysqli_query($connection, $query); 
	confirm_query($result);
	
	// 3 - Move Item 
	$query = "UPDATE {$active_page} SET id = {$destinationid} WHERE id = {$moveid} LIMIT 1;";
	$result = mysqli_query($connection, $query);
	confirm_query($result);
	
	// 4 - Inserting deleted item  
	$query = "INSERT INTO {$active_page} (";
	$query .= " id, videocode";
	$query .= ") VALUES (";
	$query .= " {$moveid}, '{$deleteditem['videocode']}'";
	$query .= ")";
	$result = mysqli_query($connection, $query); 
	confirm_query($result);

	// Finished. 
	$_SESSION["message"] = "Video positions updated!"; 
	redirect_to("index.php?redirect=videos");
}
?>

<?php compress_table($active_page); ?>
<?php echo form_errors($errors); ?>

<?php 
if (isset($_SESSION["message"])) {
	generate_prompt("Notification", message(), 'videos');
	echo "<script type=\"text/javascript\">prompt_popup('videos', true);</script>";
}
?>

<?php if($admin) { ?>
<form action="videos.php" method="post">
<?php } ?>
<table class="maincontenttable">
	<?php 
	// For every 2 movies, make a row. 
	$query = "SELECT * FROM videos";
	$result = mysqli_query($connection, $query); 
	$maxvids = mysqli_num_rows($result);
	display_videos($result, $maxvids, $admin);
	?>

	<?php if($admin) { ?>
	<tr>
		<td id="addvideo" class="shortrow" style="text-align:center" colspan="2">
			<div>
				Copy and paste the embed code from Youtube to add the video to your collection!
				<br />
				<br />
				<input type="text" name="newvideo" style="width:200px"/>
				<br />
				<br />
				<input class="mybutton" type="submit" name="submit" value="Add Video" />
			</div>
		</td>
	</tr>
	</form>
	<?php } ?>	
</table>





