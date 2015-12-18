<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php $active_page = "controlpanel"; ?>
<link rel="stylesheet" type="text/css" href="stylesheets/controlpanel.css">
<?php $admin = logged_in();  ?>
<?php global $connection; ?>
<?php $errors = errors(); ?>


<?php echo message(); ?> 
<?php echo form_errors($errors); ?>
<?php compress_table("events"); ?>

<div id="tabstogglepanel">
	<div style="text-align: center;" class="shortrow">Tab Control</div>
	<div class="tabcontrolwrap">
		<div class="tabname">Home</div>
		<div class="checkboxwrap">
			<div class="checkbox" onclick="flipStatus()" ></div>
		</div>
	</div>
	<div class="tabcontrolwrap">
		<div class="tabname">About</div>
		<div class="checkboxwrap">
			<div class="checkbox" onclick="flipStatus()" ></div>
		</div>
	</div>
	<!-- <div class="tabcontrolwrap">
		<div class="tabname">Services</div>
		<div class="checkboxwrap">
			<div class="checkbox" onclick="flipStatus()" ></div>
		</div>
	</div> -->
	<div class="tabcontrolwrap">
		<div class="tabname">Events</div>
		<div class="checkboxwrap">
			<div class="checkbox" onclick="flipStatus()" ></div>
		</div>
	</div>
	<div class="tabcontrolwrap">
		<div class="tabname">Songs</div>
		<div class="checkboxwrap">
			<div class="checkbox" onclick="flipStatus()" ></div>
		</div>
	</div>
	<div class="tabcontrolwrap">
		<div class="tabname">Videos</div>
		<div class="checkboxwrap">
			<div class="checkbox" onclick="flipStatus()" ></div>
		</div>
	</div>
	<div class="tabcontrolwrap">
		<div class="tabname">Contact</div>
		<div class="checkboxwrap">
			<div class="checkbox" onclick="flipStatus()" ></div>
		</div>
	</div>
</div>