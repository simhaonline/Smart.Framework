
// NetVision JS - Slimbox Scanner
// (c) 2006-2015 unix-world.org
// v.2015.02.15

// DEPENDS: jQuery, Slimbox

//===========================================

//--
var NetVision_Scan_SlimBox = function() {
	if(!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
		$("a[rel^='slimbox']").slimbox({}, null, function(el) {
			return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
		});
	} //end if
} //END FUNCTION
//--

//===========================================

$(function() { // ON END DOCUMENT READY
	//--
	NetVision_Scan_SlimBox();
	//--
}); //END FUNCTION

$(document).ajaxComplete(function(event, xhr, settings) { // DETECT CONTENT CHANGES BY AJAX CALLS
	//-- if((typeof settings.dataType == 'undefined') || (settings.dataType == 'html') || (settings.dataType == 'text')) {
		setTimeout(function(){ NetVision_Scan_SlimBox(); }, 500); // this have to be async
	//-- } //end if
}); //END FUNCTION

//===========================================

// #END
