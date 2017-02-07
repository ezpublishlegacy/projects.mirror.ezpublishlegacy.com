/*
addEvent function from http://www.quirksmode.org/blog/archives/2005/10/_and_the_winner_1.html
*/
function addEvent( obj, type, fn )
{
	if (obj.addEventListener)
		obj.addEventListener( type, fn, false );
	else if (obj.attachEvent)
	{
		obj["e"+type+fn] = fn;
		obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
		obj.attachEvent( "on"+type, obj[type+fn] );
	}
}

function removeEvent( obj, type, fn )
{
	if (obj.removeEventListener)
		obj.removeEventListener( type, fn, false );
	else if (obj.detachEvent)
	{
		obj.detachEvent( "on"+type, obj[type+fn] );
		obj[type+fn] = null;
		obj["e"+type+fn] = null;
	}
}

/* Create the new window */
function openInNewWindow(e) {
	var event;
	if (!e) event = window.event;
	else event = e;
	// Abort if a modifier key is pressed
	if (event.shiftKey || event.altKey || event.ctrlKey || event.metaKey) {
		return true;
	}
	else {
		// Change "_blank" to something like "newWindow" to load all links in the same new window
	    var newWindow = window.open(this.getAttribute('href'), '_blank');
		if (newWindow) {
			if (newWindow.focus) {
				newWindow.focus();
			}
			return false;
		}
		return true;
	}
}

/*
Add the openInNewWindow function to the onclick event of links with a class name of "new-window"
*/
function getNewWindowLinks() {
	// Check that the browser is DOM compliant
	if (document.getElementById && document.createElement && document.appendChild) {
		// Change this to the text you want to use to alert the user that a new window will be opened
		var strNewWindowAlert = " (opens in a new window)";
		// Find all links
		var links = document.getElementsByTagName('a');
		var objWarningText;
		var link;
		for (var i = 0; i < links.length; i++) {
			link = links[i];
			// Find all links with a class name of "new-window"
			if (/\bnew\-window\b/.test(link.className)) {
				// Create an em element containing the new window warning text and insert it after the link text
				objWarningText = document.createElement("em");
				objWarningText.className="new-window-text";
				objWarningText.appendChild(document.createTextNode(strNewWindowAlert));
				link.appendChild(objWarningText);
				link.onclick = openInNewWindow;
				// it doesn't work with addEvent, it will still open in the current window
				//link.addEvent( link, 'click', openInNewWindow );
			}
		}
		objWarningText = null;
	}
}

addEvent(window, 'load', getNewWindowLinks);