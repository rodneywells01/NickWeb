<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php $active_page = "songs"; ?>
<link rel="stylesheet" type="text/css" href="stylesheets/songs.css">
<?php $admin = logged_in(); ?>
<?php global $connection; ?>

<?php 
// Add a song.
if (isset($_POST["submit"])) {
	echo "Yes submit";
	// Is there a song to be added to the database? 
	if(!empty($_POST["newsong"])) {
		echo "Yes new song";
		// User wants to add a song.
		$songcode = $_POST["newsong"];

		// Confirm song is from soundcloud, to the best of my abilities. 
		$terms = array("soundcloud", "iframe", "height");
		verify_str_contains($terms, $songcode); 
		
		if (empty($errors)) { 
			// Relatively certain correct link was posted 
			// Need to adjust height of embeded value. 
			$editedsongcode = fix_song_size($songcode);
			
			// Save song to database. 
			$query = "INSERT INTO songs ("; 
			$query .= " songcode";
			$query .= ") VALUES (";
			$query .= " '{$editedsongcode}'";
			$query .= ")"; 
			$result = mysqli_query($connection, $query); 

			if ($result) {
				// Success
				$_SESSION["message"] = "Songs has created!";
				compress_table($active_page);	
			} else {
				// Failure
				$_SESSION["message"] = "Songs has failed to be added!";
			}
			

		} else {			
			$_SESSION["errors"] = "<ul>";

			foreach ($errors as $error) {
				echo "$error" . "<br />";
				$_SESSION["errors"] .= "<li>" . $error . "</li>";
			}

			$_SESSION["errors"] .= "</ul>";
		}
	}  else {
		$_SESSION["message"] = "No song was provided!";
	} 
	redirect_to("index.php?redirect=songs");
}

// Move a song.
if (isset($_GET["movedirection"]) && $admin) {
	// We're moving a songs position instead. 
	$movesongid = $_GET["moveid"];
	$directiontomove = $_GET["movedirection"];
	$destinationid = $movesongid + $directiontomove;

	// Check to see if move is performable. 
	$query = "SELECT * FROM songs"; 
	$result = mysqli_query($connection, $query); 
	$numsongs = mysqli_num_rows($result);

	if ($destinationid < 1) {
		// ID is too low. 
		$_SESSION["message"] = "Cannot move this song up!"; 
		redirect_to("index.php?redirect=songs");
	} else if ($destinationid > $numsongs) {
		$_SESSION["message"] = "Cannot move this song down!";
		redirect_to("index.php?redirect=songs");
	}


	/*
	INSTRUCTIONS: 
	1. Store destination in temp var. 
	2. Delete destination. 
	3. Update position. 
	4. Reinsert updated destination. 
	*/

	// 1 - Selecting
	$query = "SELECT * FROM songs WHERE id = {$destinationid} LIMIT 1;"; 
	$result = mysqli_query($connection, $query); 
	confirm_query($result);
	$deletedsong = mysqli_fetch_assoc($result);
	
	// 2 - Deleteing
	$query = "DELETE FROM songs WHERE id = {$destinationid} LIMIT 1;";
	$result = mysqli_query($connection, $query); 
	confirm_query($result);
	
	// 3 - Move Song 
	$query = "UPDATE songs SET id = {$destinationid} WHERE id = {$movesongid} LIMIT 1;";
	$result = mysqli_query($connection, $query);
	confirm_query($result);
	

	// 4 - Inserting deleted song  
	$query = "INSERT INTO songs (";
	$query .= " id, songcode";
	$query .= ") VALUES (";
	$query .= " {$movesongid}, '{$deletedsong['songcode']}'";
	$query .= ")";
	$result = mysqli_query($connection, $query); 
	confirm_query($result);

	// Finished. 
	$_SESSION["message"] = "Song positions updated!"; 
	redirect_to("index.php?redirect=songs");
} 

// Delete a song.
if (isset($_GET["deleteid"]) && $admin) {
	remove_from_table($_GET["deleteid"], $active_page, $active_page);

	$_SESSION["message"] = "Song removed!"; 
}
?>


<?php 
if (isset($_SESSION["message"])) {
	generate_prompt("Notification", message(), 'songs');
	echo "<script type=\"text/javascript\">prompt_popup('songs');</script>";
} else if (isset($_SESSION["errors"])) {
	generate_prompt("Notification", errors(), 'songs');
	echo "<script type=\"text/javascript\">prompt_popup('songs');</script>";
}
?>

<table id="maincontent">
	<?php 
		// Obtain all songs. 
		$query = "SELECT * FROM songs"; 
		$result = mysqli_query($connection, $query); 

		// Display all songs. 
		while($song = mysqli_fetch_assoc($result)) {
			display_song($song, $admin);
		}
	?>				
	<?php if ($admin) { ?>
		<tr>
			<td  colspan="5">
				<form action="songs.php" method="post">
					<div id="addsong">
						<br /> <br />
						Paste the url of your song from SoundCloud into this box and press "Add Song" to add the song to the song list. 
						<br /> <br />
						<input type="text" name="newsong"/> <br /> <br />
						<input class="mybutton" type="submit" name="submit" value="Add Song"> <br /> <br />	
					</div>
				</form>
			</td>
		</tr>
	<?php } ?>
</table>