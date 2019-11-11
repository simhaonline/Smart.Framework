
// [LIB - Smart.Framework / JS / Smart Modal Scanner]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

// DEPENDS: jQuery, SmartJS_ModalBox, SmartJS_BrowserUtils

//==================================================================
//==================================================================

//================== [NO:evcode]

/*
* jQuery Plugin class :: Smart Modal iFrame Scanner
*
* @package Smart.Framework/JS/Browser
*
* @requires		jQuery
* @requires		SmartJS_ModalBox
* @requires		SmartJS_CoreUtils
* @requires		SmartJS_BrowserUtils
*
* @desc on document.ready will use jQuery to scan all a[data-smart] links from a page to implement Modal iFrame / iPopUp based on SmartJS_BrowserUtils
* @author unix-world.org
* @license BSD
* @file ifmodalbox_scanner.js
* @version 20191102
* @class jQuery.Plugin::SmartJS_ModalBox::Scanner
* @static
* @hideconstructor
*
*/
jQuery(function() { // ON DOCUMENT READY
	//--
	//jQuery('body').delegate('a[data-smart]', 'click', function(el) { // delegate() does the job also with new dom inserted links
	jQuery('body').on('click', 'a[data-smart]', function(el) { // jQuery 3+ : this is equivalent with delegate() which was deprecated
		//--
		var version = 'r.20191102';
		//--
		var dataSmart = jQuery(this).attr('data-smart');
		if(!dataSmart) {
			return true; // let click function as default
		} //end if
		//alert(dataSmart);
		//--
		var isModal = RegExp(/^open.modal/i).test(dataSmart);
		var isPopup = RegExp(/^open.popup/i).test(dataSmart);
		if((isModal !== true) && (isPopup !== true)) { // does not have proper syntax
			return true; // let click function as default
		} //end if
		//--
		var attrHref = jQuery(this).attr('href');
		if(!attrHref) {
			console.error('iFrmBox Scanner (' + version + '): The Clicked Data-Smart [' + dataSmart + '] Link has no Href Attribute: ' + jQuery(this).text());
			return false;
		} //end if
		//--
		var attrTarget = jQuery(this).attr('target');
		if(!attrTarget) {
			attrTarget = '_blank';
		} //end if
		//--
		var winWidth = parseInt(jQuery(window).width());
		if(!SmartJS_CoreUtils.isFiniteNumber(winWidth)) {
			winWidth = 920;
		} //end if
		var winHeight = parseInt(jQuery(window).height());
		if(!SmartJS_CoreUtils.isFiniteNumber(winHeight)) {
			winHeight = 700;
		} //end if
		//--
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
		var mode = 0; // 1 = popup, 0 = modal if not in modal, -1 = modal
		switch(u) {
			case 1:
				mode = -1; // force modal
				break;
			default:
				mode = 0; // default
		} //end switch
		//--
		if(isModal === true) {
			SmartJS_BrowserUtils.PopUpLink(attrHref, attrTarget, w, h, mode, 1);
		} else if(isPopup === true) {
			SmartJS_BrowserUtils.PopUpLink(attrHref, attrTarget, w, h, 1, 1);
		} //end if else
		//--
		return false;
		//--
	});
	//--
}); //END ON DOCUMENT READY FUNCTION

//jQuery('body').on('DOMSubtreeModified', function() { // this is deprecated
//jQuery(document).ajaxComplete(function(event, xhr, settings) { // DETECT CONTENT CHANGES BY AJAX CALLS
//	//-- if((typeof settings.dataType == 'undefined') || (settings.dataType == 'html') || (settings.dataType == 'text')) {
//		setTimeout(function(){ ... re-scan page links ... }, 500); // this have to be async
//	//-- } //end if
//}); //END FUNCTION

//==================================================================
//==================================================================

// #END
