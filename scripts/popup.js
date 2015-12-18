function prompt_popup(name, remove) {
	// Launch prompt form, resetting values. 
	var target = $("#errorpopup"); 
	target.remove();
	var identifier = "";
	if(typeof name !== undefined) {
		identifier = name;
	}
	var target = $("#messagepromptcontainer" + identifier);
	target.fadeIn();

	if(remove) {
		// Optional auto removal.
		setTimeout(function(){remove_prompt_popup(name)}, 1800);
	}
}

function remove_prompt_popup(name) {
	// Close email submit form. 	
	var target = $("#messagepromptcontainer" + name);
	target.fadeOut();
	target = $("#errorpopup"); 
	target.fadeOut();
}
