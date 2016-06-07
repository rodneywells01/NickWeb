function generate_time_picker() {
	// Handle addition of TIMEPICKER UI. 
	// TIMEPICKER curtesy of http://trentrichardson.com/examples/timepicker/
	$('.datetimeselector').datetimepicker({
		dateFormat: "y-mm-dd",
		timeFormat: "HH:mm:ss"
	});
}

function display_event_description(event_id) {
	/*
		Generate body contents for event information popup.
	*/
	var updatelocation;

	var eventrowtodisplay = $("#eventrow" + event_id).clone().css("min-width", "400px");
	updatelocation = $("#eventdisplayrow");
	updatelocation.html(eventrowtodisplay);

	var eventdetailstodisplay = $("#hideevent" + event_id).html(); 
	updatelocation = $("#eventdisplayinfo");
	updatelocation.html(eventdetailstodisplay);

	prompt_popup("eventinfo");
}

function prep_email() {
	/*
		Generate default message to notify Nick.
	*/
	var desiredevent = $("#eventdisplayrow").find("#eventname").html();	
	var message = "Hello Nicholas! I'm visiting your site, and I just wanted to let you know that I'll be attending the event '" + desiredevent + "'."; 
	var title = "Attending " + desiredevent;
	
	// Set message in email form. 
	$("#emailbody").html(message);
	$("#emailtitle").val(title);
	$("#emailbody").removeClass("default");
	$("#emailtitle").removeClass("default");
	validTitle = true;
	validBody = true;
}
