var validEmail = false;
var validTitle = false;
var validBody = false;

$(document).ready(function() {
	console.log("document ready!");
	// Listeners for focus exits on forms. 
	$("#emailtitle").blur(function() {
		if($("#emailtitle").val() == "") {
			validTitle = false;
			$("#emailtitle").val("Title...");
			$("#emailtitle").addClass("default")
		}
	});

	$("#emailtitle").blur(function() {
		if($("#emailtitle").val() == "") {
			validTitle = false;
			$("#emailtitle").val("Title...");
			$("#emailtitle").addClass("default")
		}
	});

	$("#emailbody").blur(function() {
		if($("#emailbody").val() === "") {
			validBody = false;
			$("#emailbody").val("Message...");
			$("#emailbody").addClass("default")
		} 
	});

	$("#emailtitle").focus(function() {
		if($("#emailtitle").val() == "Title...") {
			validTitle = true;
			$("#emailtitle").val("");
			$("#emailtitle").removeClass("default")
		}
	});

	$("#emailbody").focus(function() {
		if($("#emailbody").val() == "Message...") {
			validBody = true;
			$("#emailbody").val("");
			$("#emailbody").removeClass("default")
		}
	});

	$("#emailuser").blur(function() {
		if($("#emailuser").val() === "") {
			validEmail = false;
			$("#emailuser").val("Your Email... (Optional)");
			$("#emailuser").addClass("default")
		} 
	});

	$("#emailuser").focus(function() {
		if($("#emailuser").val() == "Your Email... (Optional)") {
			validEmail = true;
			$("#emailuser").val("");
			$("#emailuser").removeClass("default")
		}
	});
});

function sendMessage(recipient, message, sender, title) {
	// Send message to recipient. 
	$.ajax({
		type: "POST",
		url: "https://mandrillapp.com/api/1.0/messages/send.json",
		data: {
		    'key': 'aXJ11QL8BGzb8T1LcXFfYg',
		    'message': {
		      'from_email': sender,
		      'to': [
		          {
		            'email': recipient,
		            'name': 'Nicholas Nasibyan',
		            'type': 'to'
		          },
		        ],
		      'autotext': 'true',
		      'subject': title + ' - An Email from NicholasNasibyan.com!',
		      'html': message
		    }
		  }
		 }).done(function(response) {
		   console.log(response); // if you're into that sorta thing
		   console.log("Email sent!");
		 });
}

function update_prompt_elements() {
	// Update styles of prompt for email
	$('#promptsubmit').attr('style','');
	$('#promptsubmit').text("Send Email");
	$("#promptsubmit").css("pointer-events", "auto");
}

function construct_email() {
	//  Build the email to be sent to Nicholas. 

	// Obtain values. 
	$("#errorpopup").remove();
	var errorsdetail = "";
	var errors = false;
	console.log("construct email function");
	// Check for errors.
	if (!validTitle) { errorsdetail += "<li>A <strong><i>title</i></strong> has not been provided!</li>"; errors = true;}	
	if (!validBody) { errorsdetail += "<li>A <strong><i>body</i></strong> has not been provided!</li>"; errors = true;}	
	if (errors) {
			console.log("errors function");

		// There was an error in user submission. 
		var errorsmessage = "There are <strong><i>errors</i></strong> in your submission. Please fix the following: <div><ul>"; 
		errorsmessage += errorsdetail; 
		errorsmessage += "</div></ul>";

		var errorpopup = "<div id=\"errorpopup\">";
		errorpopup += errorsmessage;
		errorpopup += "</div>";
		$("#header").before(errorpopup);
		$("#errorpopup").fadeIn();
	} else {
		// No errors. Collect values, construct, send.

		// Prohibit user from double clicking. 
		$("#promptsubmit").css("pointer-events", "none");
		
		// Collect values.		
		var title = $("#emailtitle").val();
		var user = $("#emailuser").val();
		if(!validEmail) {user = "NicholasWebsite@nicholasnasibyan.com";}
		var body = $("#emailbody").val();

		// Construct 
		var message = "";
		var userinfo = "This email was sent from NicholasNasibyan.com! <br/><br/>";
		message += userinfo; 
		message += body;

		// Message constructed. Send email. 
		$("#promptsubmit").text("Email Sent!");
		console.log($("#clientemail"));
		var emailaddress = $("#clientemail").text().trim(); 
		var test = $("#testtest").text().trim(); 
		console.log("Sending: ");
		console.log("1" + title);
		console.log("2" + user);
		console.log("3" + body);
		console.log("4" + emailaddress);
		console.log("5" + test);

		sendMessage(emailaddress, message, user, title);
		$("#promptsubmit").css("background-color", "#A68E08");
		$("#promptsubmit").css("color", "black");
		
		setTimeout(function(){
		    remove_prompt_popup("email");
		    update_prompt_elements();
		}, 1200);
	}
}
