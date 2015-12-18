<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php $admin = logged_in(); ?>

<?php 

	// v1: simple logout
	if($admin) {
		$_SESSION["admin_id"] = null;
		$_SESSION["username"] = null;

		// $_SESSION["message"] = "You have been logged out.";
		redirect_to("index.php");
	} else {
		redirect_to("index.php");
	}
	

?>

