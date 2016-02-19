
// [LIB - SmartFramework / JS / Page Away Control]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3 r.2016.02.19

// DEPENDS: SmartJS_BrowserUtils

//==================================================================
//==================================================================

//=======================================
// CODE :: Page Away Control
//=======================================

//--
var NetVision_JS_pageMessageAway = '[####QUESTION####]';
//--
window.onbeforeunload = function(e) {
	e = e || window.event;
	if(SmartJS_BrowserUtils_PageAway != true) {
		e.preventDefault();
		return NetVision_JS_pageMessageAway + ' '; // add space to avoid being empty
	} //end if
} //END FUNCTION
//--
window.onunload = function () {
	SmartJS_BrowserUtils_PageAway = true;
} //END FUNCTION
//--
try {
	if(window.self !== window.top) { // try to set only if iframe
		parent.SmartJS_ModalBox.on_Before_Unload = function() {
			if(SmartJS_BrowserUtils_PageAway != true) {
				var is_exit = confirm(NetVision_JS_pageMessageAway + ' '); // true or false
				if(is_exit) {
					SmartJS_BrowserUtils_PageAway = true;
				} //end if
				return is_exit;
			} else {
				return true;
			} //end if else
		} //end function
	} //end if
} catch (err) { }
//--

//==================================================================
//==================================================================

// #END
