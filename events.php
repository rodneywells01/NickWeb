<?php require("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php $active_page = "events"; ?>
<link rel="stylesheet" type="text/css" href="stylesheets/events.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script type="text/javascript" src="scripts/email.js"></script>
<script type="text/javascript">generate_time_picker();</script>
<?php $admin = logged_in();  ?>
<?php if ($admin) { compress_table($active_page); } ?>
<?php global $connection; ?>
<?php 
check_event_delete(); 
check_event_add(); 
if (isset($_POST['submit']) && $admin) {
	// I have to filter all of the values.
	$query = "SELECT * FROM events";
	$response = mysqli_query($connection, $query);
	$numevents = mysqli_num_rows($response);

	$required_fields = array("event_name", "event_location", "event_description", "event_datetime");
	for ($i = 1; $i <= (int)$numevents; $i++) {
		foreach($required_fields as $requirement) {
			// Perform all necessary checks in here. 
			$validationvalue = $requirement . $i;
			validate_presence($validationvalue);
			$_POST[$validationvalue] = mysql_prep($_POST[$validationvalue]);
		}
	}

	// If no errors, update page. 
	if (empty($errors)) {
		for ($i = 1; $i <= (int)$numevents; $i++) {
			$query = "UPDATE events SET ";
			$query .= "name = '{$_POST["event_name{$i}"]}', ";
			$query .= "location = '{$_POST["event_location{$i}"]}', ";
			$query .= "description = '{$_POST["event_description{$i}"]}', ";
			$query .= "datetime = '{$_POST["event_datetime{$i}"]}' ";
			$query .= "WHERE id = {$i} "; 
			$query .= "LIMIT 1";
			$result = mysqli_query($connection, $query);
			confirm_query($result);
		}
		$_SESSION["message"] = "Events Updated!";
	}	else {
		$_SESSION["errors"] = $errors;
		echo $errors;

	}
	redirect_to("index.php?redirect=events");
} 
?>

<?php compress_table("events"); ?>
<?php $errors = errors(); ?>
<?php echo form_errors($errors); ?>

<?php 
if (isset($_SESSION["message"])) {
	generate_prompt("Notification", message(), 'event');
	echo "<script type=\"text/javascript\">prompt_popup('event', true);</script>";
} else if (isset($_SESSION["errors"])) {
	generate_prompt("Error:", errors(), 'event');
	echo "<script type=\"text/javascript\">prompt_popup('event', true);</script>";
}
?>

<?php 
if(isset($_SESSION["event"])) {
	echo "<script>display_event_description(" . $_SESSION["event"] . ");</script>";
	$_SESSION["event"] = null;
}
?>

<?php 
	$query = "SELECT * FROM contact WHERE id = 1 LIMIT 1";
	$result = mysqli_query($connection, $query);
	confirm_query($result);
	$emailaddress = mysqli_fetch_assoc($result)["email"];
?>

<div style="display:none;" id="nicksemail"><?php echo $emailaddress; ?></div>
<?php 
$eventdisplaybody = "<div id=\"eventdisplayrow\"></div>" . 
					"<div id=\"eventdisplayinfo\"></div>" . 
					"<div class=\"eventdisplaybutton hvr-fade\" onclick=\"prep_email();remove_prompt_popup('eventinfo');prompt_popup('email')\">Let Nick Know You Are Coming!</div>"; 
					generate_prompt("Event Information", $eventdisplaybody, "eventinfo");					

$formbody = "<div class=\"emailinputwrap\"><input id=\"emailtitle\" class=\"default noborderfocus\" type=\"text\" value=\"Title...\" /></div>
						<div class=\"emailinputwrap\"><input id=\"emailuser\" class=\"default noborderfocus\"  type=\"text\" value=\"Your Email... (Optional)\" /></div>
						<div class=\"emailinputwrap\"><textarea id=\"emailbody\" class=\"default noborderfocus\">Message...</textarea></div>
						<div onclick=\"construct_email();\" id=\"promptsubmit\" class=\"emailinputwrap hvr-sweep-to-right\">Send Email!</div> <div id='#clientemail' style='display: none'><?php echo $emailaddress ?></div>";
					generate_prompt("Send Nick a Message!", $formbody, "email");			
?>

<div id="eventheader" class="eventrow">
	<?php if ($admin) { ?>
	<div class="eventcell shortrow emphasis">Options</div>
	<?php } ?>
	<div class="eventcell shortrow emphasis">Date</div>
	<div class="eventcell shortrow emphasis">Event</div>
	<div class="eventcell shortrow emphasis">Location</div>
	<div class="eventcell shortrow emphasis mobiledeleteme">Time</div>
	<?php if($admin) { ?>
	<div class="eventcell shortrow emphasis">Details</div>
	<?php } ?>
</div>
<?php 
// Acquire list of events. 
$query = "SELECT * FROM events ORDER BY datetime ASC"; 
$eventlistdata = mysqli_query($connection, $query); 

if ($admin) { ?>
	<form action="events.php" method="post">
<?php 
} 
while($event = mysqli_fetch_assoc($eventlistdata)) {
	new_display_event_row($event, $admin); // Display all information about event, editable if Admin. 
	store_description_content($event);
}

if ($admin) {
	// Display last empty row for additional Event. 
	?>
	<div class="eventrow">
		<?php $rownum = mysqli_num_rows($eventlistdata) + 1; ?>
		<div class="eventcell"><input class="mybutton" type="submit" name="add" value="Add Event" /></div>
		<div class="eventcell"><input class="datetimeselector" type="text" style="width: 110px;" name="event_datetime<?php echo $rownum; ?>" /></div>
		<div class="eventcell"><input type="text" style="width: 100px;" name="event_name<?php echo $rownum; ?>"/></div>
		<div class="eventcell"><input type="text" style="width: 100px;" name="event_location<?php echo $rownum; ?>"/></div>
		<div class="eventcell"></div>
		<div class="eventcell"><textarea class="customtextarea" name="event_description<?php echo $rownum; ?>"></textarea></div>
	</div>
<?php	} 	?>
<?php $cols = 4; if ($admin) {$cols = 6;} ?>
<div class="eventrow">
	<div class="eventcell">
		<?php if ($admin) { ?>
			<input class="mybutton" type="submit" name="submit" value="Save Changes" />
			</form>
		<?php } else { ?>
			Want to reserve Nicholas Nasibyan for your event? <br /> 
			Click on the "Contact" tab! <br />
		<?php } ?>
	</div>
</div>

