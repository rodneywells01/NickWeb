$(document).ready(function() {
	// Retrieve necessary info. 
	// var hiddendivs = $(".hideinfo");
	// var hiddencontent = [];
	// hiddendivs.forEach(function(item) {
	// 	hiddencontent.push(item);
	// });

	// Handle addition of TIMEPICKER UI. 
	// TIMEPICKER curtesy of http://trentrichardson.com/examples/timepicker/
	$('.datetimeselector').datetimepicker({
		dateFormat: "y-mm-dd",
		timeFormat: "HH:mm:ss"
	})
});

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

	//TODO: Load email from DB? Maybe?
	var desiredevent = $("#eventdisplayrow").find("#eventname").html();	
	var message = "Hello Nicholas! I'm visiting your site, and I just wanted to let you know that i'll be attending the event '" + desiredevent; 
	message += "'. I'm sure it'll be a big hit. Best of luck out there!";
	// Set message in email form. 
	$("#emailbody").html(message);
	$("#emailbody").removeClass("default");
	validBody = true;
}

/*
"Validation error: {"message":{"to":[{"email":"Sorry, this field can't be left blank."}]}}"
*/