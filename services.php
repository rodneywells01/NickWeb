<?php $active_page = "services"; ?>
<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<link rel="stylesheet" type="text/css" href="stylesheets/services.css">
<?php $admin = logged_in(); ?>
<?php global $connection; ?>
<?php $errors = errors(); ?>

<?php 
if(isset($_POST["submit"])) {
}
?>

<?php echo message(); ?> 
<?php echo form_errors($errors); ?>

<!-- Main Contacts Page -->
<!-- <div class="intro emphasis">Offering a Variety of Services For Any Event</div> -->
<div id="mycontent">
	<div id="serviceswrap" class="left columncontent">
		<table id="servicestable" class="verticaltextcenter2">
			<tr>
				<td><div class="hvr-grow servicecontainer">Wedding</div></td>
				<td><div class="hvr-grow servicecontainer">Wedding</div></td>
				<td><div class="hvr-grow servicecontainer">Wedding</div></td>
			</tr>
			<tr>
				<td><div class="hvr-grow servicecontainer">Wedding</div></td>
				<td><div class="hvr-grow servicecontainer">Wedding</div></td>
				<td><div class="hvr-grow servicecontainer">Wedding</div></td>
			</tr>
			<tr>
				<td><div class="hvr-grow servicecontainer">Wedding</div></td>
				<td><div class="hvr-grow servicecontainer">Wedding</div></td>
				<td><div class="hvr-grow servicecontainer">Wedding</div></td>
			</tr>
		</table>
	</div>
	<div class="right columncontent" id="servicedescriptionwrap">
		<div class="verticaltextcenter2">Swag Out</div>
	</div>
</div>




