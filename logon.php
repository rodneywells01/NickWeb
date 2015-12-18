<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<link rel="stylesheet" type="text/css" href="stylesheets/logon.css">
<?php if(logged_in()) {
	// User doesn't need to log on! 
	$_SESSION["message"] = "You are already logged in!"; 
	redirect_to("index.php");
} ?>

<?php 
	$username = "";
	if (isset($_POST['submit'])) {
		$required_fields = array("username", "password"); 
		validate_presences($required_fields);

		if(empty($errors)) {
			// Attempt a login. 
			$username = $_POST["username"]; 
			$password = $_POST["password"]; 
			$found_admin = attempt_login($username, $password);

			if ($found_admin) {
				// Success. User is logged in! 
				$_SESSION["admin_id"] = $found_admin["id"]; 
				$_SESSION["username"] = $found_admin["username"]; 
				redirect_to("index.php");
			} else {
				// Failure. Could not log in. 
				$_SESSION["message"] = "Username/password not found.";
				redirect_to("index.php");
			}
		} else {
			// Errors Exist. 
			$_SESSION["errors"] = $errors; 
			redirect_to("index.php");
		}
	}
?>

<?php echo message(); ?>
<?php $errors = errors(); ?> 
<?php form_errors($errors) // What does this do again? ?> 
<form action="logon.php" method="post">
<table id="logon">
	<tr>
		<td class="shortrow emphasis">
			Log In
		</td>		
	</tr>
	
		<tr>
			<td>Username: &nbsp;&nbsp;&nbsp; <input type="text" name="username"> </td>
		</tr>
		<tr>
			<td>Password: &nbsp;&nbsp;&nbsp; <input type="password" name="password"> </td>
		</tr>
		<tr>
			<td class="shortrow emphasis">
				<input class="mybutton" type="submit" name="submit" value="Log In"/>
			</td>
			
		</tr>

</table>
</form>	
