
// [LIB - SmartFramework / JS / Smart Modal Scanner]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.7.1 r.2016.09.21 / smart.framework.v.2.3

// DEPENDS: jQuery, SmartJS_ModalBox, SmartJS_BrowserUtils

//==================================================================
//==================================================================

//=======================================
// CLASS :: Smart Modal iFrame Scanner
//=======================================

//--
var SmartJS_ClickHandlerModalPopupBox = function(el) {
	//--
	if((typeof el == 'undefined') || (!el)) {
		return;
	} //end if
	//--
	var winWidth = parseInt(jQuery(window).width());
	var winHeight = parseInt(jQuery(window).height());
	//--
	var dataSmart = el.getAttribute('data-smart');
	//--
	var isModal = RegExp(/^open.modal/i).test(dataSmart);
	var isPopup = RegExp(/^open.popup/i).test(dataSmart);
	var aDim = dataSmart.match(/[0-9]+(\.[0-9][0-9]?)?/g); // dataSmart.match(/[0-9]+/g);
	var w = winWidth; // (aDim && (aDim[0] > 0)) ? aDim[0] : winWidth;
	var h = winHeight; // (aDim && (aDim[1] > 0)) ? aDim[1] : winHeight;
	var u = (aDim && (aDim[2] > 0)) ? aDim[2] : 0;
	//--
	if(aDim) {
		if(aDim[0] > 0) {
			if(aDim[0] < 1) {
				w = aDim[0] * winWidth;
			} else {
				w = aDim[0];
			} //end if else
		} //end if
		if(aDim[1] > 0) {
			if(aDim[1] < 1) {
				h = aDim[1] * winHeight;
			} else {
				h = aDim[1];
			} //end if else
		} //end if
	} //end if
	//--
	w = parseInt(w);
	h = parseInt(h);
	u = parseInt(u);
	//--
	if(w > winWidth) {
		w = parseInt(winWidth * 0.9);
	} //end if
	if(w < 200) {
		w = 200;
	} //end if
	if(h > winHeight) {
		h = parseInt(winHeight * 0.9);
	} //end if
	if(h < 100) {
		h = 100;
	} //end if
	//--
	var display_mode = 0; // 1 = popup, 0 = modal if not in modal, -1 = modal
	switch(u) {
		case 1:
			display_mode = -1; // force modal
			break;
		default:
			display_mode = 0; // default
	} //end switch
	//--
	if(isModal === true) {
		SmartJS_BrowserUtils.PopUpLink(el.href, el.target, w, h, display_mode, 1);
	} else if(isPopup === true) {
		SmartJS_BrowserUtils.PopUpLink(el.href, el.target, w, h, 1, 1);
	} //end if else
	//--
} //END FUNCTION
//--
var SmartJS_ScannerModalPopupBox = function() {
	//--
	var aa = document.getElementsByTagName('a');
	//--
	for(var i=0; i<aa.length; i++) {
		//--
		if(RegExp(/^open.modal/i).test(aa[i].getAttribute('data-smart'))) {
			//--
			aa[i].onclick = function() {
				//--
				SmartJS_ClickHandlerModalPopupBox(this);
				//--
				return false;
				//--
			} //END FUNCTION
			//--
		} else if(RegExp(/^open.popup/i).test(aa[i].getAttribute('data-smart'))) {
			//--
			aa[i].onclick = function() {
				//--
				SmartJS_ClickHandlerModalPopupBox(this);
				//--
				return false;
				//--
			} //END FUNCTION
			//--
		} //end if
		//--
	} //end for
	//--
} //END FUNCTION
//--

//===========================================

jQuery(function() { // ON END DOCUMENT READY
	//--
	SmartJS_ScannerModalPopupBox();
	//--
}); //END FUNCTION

jQuery(document).ajaxComplete(function(event, xhr, settings) { // DETECT CONTENT CHANGES BY AJAX CALLS
	//-- if((typeof settings.dataType == 'undefined') || (settings.dataType == 'html') || (settings.dataType == 'text')) {
		setTimeout(function(){ SmartJS_ScannerModalPopupBox(); }, 500); // this have to be async
	//-- } //end if
}); //END FUNCTION

//==================================================================
//==================================================================

// #END
