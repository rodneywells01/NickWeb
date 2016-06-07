var currentpage = "";
var navsdisplaying = false;
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
		handleredirect(this, event);
	}); 

	$("#nav-tabs a").click(function(event) {
		handleredirect(this, event);
	});

	function handleredirect(element, event) {
		// User clicked on new tab. 
		event.preventDefault();

		var newpage = $(element).attr('href'); 
		var oldpage = currentpage; 
		currentpage = load_content(newpage, oldpage);

		if (oldpage != currentpage) {
			// New tab selected, apply transition. 
			switch_tab_highlight(newpage, oldpage);
		}
		return false;
	};
});

function gen_eventlink(eventid){
	window.location.href = encodeURI(window.location.origin + window.location.pathname + '?redirect=events&event=' + eventid);
}

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

function navdisplay() {  
	var navbar = $("#nav-tabs");
    if (!navsdisplaying){
    	navbar.addClass('active').show().css({
	        left: -(navbar.outerWidth(true))
	    }).animate({
	        left: 0
	    }, 400);
    } else {
    	navbar.removeClass('active').animate({
            left: -(navbar.outerWidth(true))
        }, 400);
    }
 	navsdisplaying = !navsdisplaying;
}

function handle_transitions(page, contentdiv) {
	exit_main_content(contentdiv); // Disappear div.  

	setTimeout(function() {
		contentdiv.load(page, function(){
			enter_main_content(contentdiv);
		});
	}, 300); 
}

function exit_main_content(contentdiv){
	//Fade div and content out.
	contentdiv.fadeOut(100);

	//Display loader of new content. 
}

function enter_main_content(contentdiv) {
	//Fade div and content in. 
	contentdiv.fadeIn(100);
}

// function exit_main_content(contentdiv){
// 	//Fade div and content out.
// 	var fadeTime = 100;
// 	contentdiv.fadeOut(fadeTime);
// 	setTimeout(function() {
// 		$("#loadspinner").fadeIn(20);
// 	}, fadeTime);

	
// }

// function enter_main_content(contentdiv) {
// 	//Fade div and content in. 
// 	var fadeTime = 20;
// 	$("#loadspinner").fadeOut(fadeTime);
// 	setTimeout(function() {
// 		contentdiv.fadeIn(100);
// 	}, fadeTime);
	
// }

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
	} 
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