var currentpage = "";
$(document).ready(function(){
	// Check for admin page redirect
	var currentpage = "main";
	var hiddendivcheck = document.getElementById("pagereturndiv");
	if (hiddendivcheck != null) {
		currentpage = hiddendivcheck.innerHTML;
		hiddendivcheck.remove();
		switch_tab_highlight(currentpage, "main")
		currentpage = load_content(currentpage);
	} else {
		currentpage = load_content(currentpage);
	}
	// Load initial content. 
	
	$("#tabs a").click(function(event) {
		// User clicked on new tab. 
		event.preventDefault();

		var newpage = $(this).attr('href'); 
		var oldpage = currentpage; 
		currentpage = load_content(newpage, oldpage);

		if (oldpage != currentpage) {
			// New tab selected, apply transition. 
			switch_tab_highlight(newpage, oldpage);
		}
		return false;
	})
});

function switch_tab_highlight(newpage, oldpage) {
	// Remove effect on old tab. 
	var oldtab = $('a[href="' + oldpage + '"] div');

	oldtab.removeClass("hvr-fade-selected");
	oldtab.removeClass("tabselected");

	// Add effect on new tab.
	var newtab = $('a[href="' + newpage + '"] div');

	newtab.addClass("hvr-fade-selected");
	newtab.addClass("tabselected");
}

function handle_transitions(page, contentdiv) {
	exit_main_content(contentdiv); // Disappear div.  
	setTimeout(function() {
		contentdiv.load(page, function(){
			enter_main_content(contentdiv);
		});
	}, 150); 
}

function exit_main_content(contentdiv){
	//Fade div and content out.
	contentdiv.fadeOut(100);
}

function enter_main_content(contentdiv) {
	//Fade div and content in. 
	contentdiv.fadeIn(100);
}

function load_content(newpage, currentpage) {
	// Load content of new page and prevent default action.	
	// Returns the current page. 
	var new_currentpage = currentpage;
	if (newpage != currentpage) {
		// Load page and set currentpage. 
		var contentdiv = $("#innercontentwrap");
		handle_transitions(newpage + ".php", contentdiv);
		new_currentpage = newpage;
	}
	return new_currentpage;
}

function detectWidth () {
	// Solution courtesy of http://davidwalsh.name/detect-scrollbar-width

	// Create the measurement node
	var scrollDiv = document.createElement("div");
	scrollDiv.className = "scrollbar-measure";
	document.body.appendChild(scrollDiv);

	// Get the scrollbar width
	var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;

	// Delete the DIV 
	document.body.removeChild(scrollDiv);

	return scrollbarWidth;
}

function fixScreenWidth(scrollBarWidth) {
	// Adjust the screen width for the scroll bar.
	if ($(window).hasScrollBar()) {
		var sidewidth = parseInt($("#screen").css("margin-left"));
		$('#screen').css("margin-left", sidewidth + scrollBarWidth / 2);
	} else {
	}
	
	// $('#screen').css("margin-left", "300px");

	
}

function admin_load() {
	load_content("logon", currentpage);
}

function admin_logout() {
	var response = window.confirm("Are you sure you wish to logout, Nick?"); 
	if (response) {
		window.location.href="logout.php";
	}
}

(function($) {
    $.fn.hasScrollBar = function() {
        return window.innerWidth > document.documentElement.clientWidth;
    }
})(jQuery);