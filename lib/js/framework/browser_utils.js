
// [LIB - SmartFramework / JS / Browser Utils]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.3.1 r.2016.06.17 / smart.framework.v.2.3

// DEPENDS: SmartJS_CoreUtils, SmartJS_CryptoHash, jQuery
// OPTIONAL-DEPENDS: SmartJS_ModalBox, jQuery.Gritter (Growl), SmartSimpleDialog, SmartJS_BrowserUIUtils

//==================================================================
//==================================================================

//-- setup
var SmartJS_BrowserUtils_PageAway = false;
var SmartJS_BrowserUtils_NotifyLoadError = true;
var SmartJS_BrowserUtils_Use_iFModalBox_Active = 0;
var SmartJS_BrowserUtils_Use_iFModalBox_Protection = 0;
var SmartJS_BrowserUtils_PopUp_ShowToolBar = 0;
var SmartJS_BrowserUtils_FullScreen_Img = 'lib/js/framework/img/fullscreen.png';
var SmartJS_BrowserUtils_Cloner_Img_Add = 'lib/js/framework/img/clone-insert.png';
var SmartJS_BrowserUtils_Cloner_Img_Remove = 'lib/js/framework/img/clone-remove.png';
var SmartJS_BrowserUtils_LoaderImg = 'lib/js/framework/img/loading_imodal.gif';
var SmartJS_BrowserUtils_LoaderHtml = 'lib/js/framework/loading.html';
//-- privates
var SmartJS_BrowserUtils_PopUpWindow = null; 	// this holds the pop-up window reference, we don't want to open new popups each time, so we use it if exists and just focus it (identified by window.name / target.name)
var SmartJS_BrowserUtils_RefreshState = 0; 		// if=1, will refresh parent
var SmartJS_BrowserUtils_RefreshURL = ''; 		// ^=1 -> if != '' will redirect parent
var SmartJS_BrowserUtils_CurrentForm = null;	// this holds the current form to submit reference
var SmartJS_BrowserUtils_LoadedJScripts = [];	// array of loaded Js Scripts
var SmartJS_BrowserUtils_LoadedCsStylesheets = [];	// array of loaded Css Stylesheets
//--

/**
* CLASS :: Browser Utils
*
* @class SmartJS_BrowserUtils
* @static
*
* @module Smart.Framework/JS/Browser
*/
var SmartJS_BrowserUtils = new function() { // START CLASS

// :: static

var _class = this; // self referencing

//=======================================

this.MaximizeElement = function(id, maximized) {
	//--
	var el = $('#' + id);
	//--
	if((el.data('fullscreen') == 'default') || (el.data('fullscreen') == 'fullscreen')) {
		return; // avoid apply twice
	} //end if
	//--
	el.data('fullscreen', 'default').append('<div style="position:absolute; top:-4px; left:-4px; width:20px; height: 20px; overflow:hidden; text-align:center; cursor:pointer; opacity:0.5;" title="Toggle Element Full Screen"><img src="' + SmartJS_CoreUtils.escape_html(SmartJS_BrowserUtils_FullScreen_Img) + '"></div>').css({ 'position': 'relative', 'border': '1px solid #DDDDDD', 'z-index': 1 }).click(function() {
		var the_el = $(this);
		if(the_el.data('fullscreen') == 'fullscreen') {
			_class.Overlay_Hide();
			the_el.data('fullscreen', 'default');
			the_el.css({
				'position': 'relative',
				'top': 0,
				'left': 0,
				'width': Math.max(parseInt(the_el.data('width')), 100),
				'height': Math.max(parseInt(the_el.data('height')), 100),
				'z-index': 1
			});
		} else {
			_class.Overlay_Show();
			_class.Overlay_Clear();
			the_el.data('width', '' + SmartJS_CoreUtils.escape_html('' + the_el.width()));
			the_el.data('height', '' + SmartJS_CoreUtils.escape_html('' + the_el.height()));
			the_el.data('fullscreen', 'fullscreen');
			the_el.css({
				'position': 'fixed',
				'top': '7px',
				'left': '7px',
				'width': '99%',
				'height': '98%',
				'z-index': 2147403000
			});
		} //end if else
	});
	//--
	if(maximized === true) {
		el.trigger('click');
	} //end if
	//--
} //END FUNCTION

//=======================================

this.PageAwayControl = function(the_question) {
	//--
	if((typeof the_question == 'undefined') || (the_question == null) || (the_question == '')) {
		the_question = 'Confirm leaving this page ... ?';
	} //end if
	//--
	window.onbeforeunload = function(e) {
		e = e || window.event;
		if(SmartJS_BrowserUtils_PageAway != true) {
			e.preventDefault();
			return '' + the_question;
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
					var is_exit = confirm('' + the_question); // true or false
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
} //END FUNCTION

//=======================================

this.PrintPage = function() {
	//--
	self.print();
	//--
} //END FUNCTION

//=======================================

this.CountDown = function(counter, elID, evcode) {
	//--
	if((typeof counter != 'undefined') && (counter != '') && (counter !== '') && (counter != null)) {
		//--
		counter = parseInt(counter);
		if(isNaN(counter)) {
			counter = 1;
		} //end if
		//--
		var cdwn = setInterval(function() {
			//--
			if(counter > 0) {
				//--
				counter = counter - 1;
				//--
				if((typeof elID != 'undefined') && (elID != '') && (elID !== '') && (elID != null)) {
					$('#' + elID).text(counter);
				} //end if
				//--
			} else {
				//--
				clearInterval(cdwn);
				//--
				if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
					try {
						eval('(function(){ ' + evcode + ' })();'); // sandbox
					} catch(err) {
						alert('ERROR: JS-Eval Error on Browser CountDown Function' + '\nDetails: ' + err);
					} //end try catch
				} //end if
				//--
			} //end if
			//--
		}, 1000);
		//--
	} //end if
	//--
} //END FUNCTION

//=======================================

this.RedirectToURL = function(yredirect) {
	//--
	if((typeof yredirect != 'undefined') && (yredirect != '') && (yredirect !== null)) {
		self.location = yredirect;
	} else {
		alert('NOTICE: Invalid URL to Redirect ... (Browser Utils)');
	} //end if else
	//--
} //END FUNCTION

//=======================================

this.RedirectDelayedToURL = function(yredirect, ytime) {
	//--
	setTimeout(function(){ SmartJS_BrowserUtils_PageAway = true; SmartJS_BrowserUtils.RedirectToURL(yredirect); }, ytime);
	//--
} //END FUNCTION

//=======================================

this.RedirectParent = function(yURL) {
	//--
	if((typeof yURL == 'undefined') || (yURL == null) || (yURL == '')) {
		alert('WARNING: Parent Redirection to Empty URL is not allowed !');
		return;
	} //end if
	//--
	if(window.opener) {
		window.opener.location = yURL;
	} else {
		parent.location = yURL;
	} //end if else
	//--
} //END FUNCTION

//=======================================

this.RefreshParent = function(yURL) {
	//--
	if(window.opener) {
		if(self.name != 'smart__iFModalBox__iFrame') { // don't do if modal because will loose the content on refresh
			try {
				_class.RefreshBySET_Parent(yURL);
			} catch(err){}
		} //end if
	} else {
		try {
			parent.SmartJS_ModalBox.RefreshBySET_Parent(yURL);
		} catch(err){}
	} //end if else
	//--
} //END FUNCTION

//=======================================

this.RefreshBySET_Parent = function(yURL) {
	//--
	if(window.opener) {
		//--
		window.opener.SmartJS_BrowserUtils_RefreshState = 1;
		//--
		if((typeof yURL != 'undefined') && (yURL != null) && (yURL != '')) {
			window.opener.SmartJS_BrowserUtils_RefreshURL = '' + yURL;
		} //end if
		//--
	} //end if
	//--
} //END FUNCTION

//=======================================

this.RefreshByEXEC_Parent = function(call_mode) {
	//--
	if(window.opener) { // when called from PopUp
		//--
		if(window.opener.SmartJS_BrowserUtils_RefreshState) {
			//--
			if((typeof window.opener.SmartJS_BrowserUtils_RefreshURL == 'undefined') || (window.opener.SmartJS_BrowserUtils_RefreshURL == null) || (window.opener.SmartJS_BrowserUtils_RefreshURL == '')) {
				//window . opener . location . reload(false); // false is to reload from cache
				window.opener.location = window.opener.location; // FIX: avoid reload to resend POST vars !!
			} else {
				window.opener.location = '' + window.opener.SmartJS_BrowserUtils_RefreshURL;
			} //end if else
			//--
			window.opener.SmartJS_BrowserUtils_RefreshState = 0;
			window.opener.SmartJS_BrowserUtils_RefreshURL = '';
			//--
		} //end if
		//--
	} else {
		//--
		if(call_mode === 'self') { // when called from parent
			//--
			if(window.SmartJS_BrowserUtils_RefreshState) {
				//--
				if((typeof window.SmartJS_BrowserUtils_RefreshURL == 'undefined') || (window.SmartJS_BrowserUtils_RefreshURL == null) || (window.SmartJS_BrowserUtils_RefreshURL == '')) {
					//window . location . reload(false); // false is to reload from cache
					window.location = window.location; // FIX: avoid reload to resend POST vars !!
				} else {
					window.location = '' + window.SmartJS_BrowserUtils_RefreshURL;
				} //end if else
				//--
				window.SmartJS_BrowserUtils_RefreshState = 0;
				window.SmartJS_BrowserUtils_RefreshURL = '';
				//--
			} //end if
			//--
		} //end if
		//--
	} //end if
	//--
} //END FUNCTION

//======================================= Focus Window

this.windowFocus = function(wnd) {
	//--
	try {
		wnd.focus(); // focus the window
	} catch(err){} // older browsers have some bugs, ex: IE8 on IETester
	//--
} //END FUNCTION

//======================================= Focus Scroll Down

this.windwScrollDown = function(wnd, offset) {
	//--
	try {
		wnd.scrollBy(0, parseInt(offset)); // if offset is -1 will go to end
	} catch(err){} // just in case
	//--
} //END FUNCTION

//======================================= get the highest z-index from all visible divs (will ignore the non-visible ones)

this.getHighestZIndex = function() {
	//-- inits
	var index_highest = 1;
	var index_current = 1;
	//--
	$('div').each(function(){
		if(($(this).css('display') == 'none') || ($(this).css('visibility') == 'hidden') || ($(this).attr('id') == 'SmartFramework___Debug_InfoBar')) {
			// skip
		} else {
			index_current = parseInt($(this).css("z-index"), 10);
			if(index_current > index_highest) {
				index_highest = index_current;
			} //end if
		} //end if else
	});
	index_highest += 1;
	//console.log('Using Highest Z-INDEX: ' + index_highest);
	//--
	return index_highest;
	//--
} //END FUNCTION

//=======================================

this.Overlay_Show = function(text) {
	//--
	var the_style = 'style="background-color:#333333; position: fixed; top: 0px; left: 0px; width: 100%; height: 100%; z-index:' + _class.getHighestZIndex() + ';"';
	if(typeof SmartJS_BrowserUIUtils != 'undefined') {
		the_style = 'class="ui-widget-overlay"'; // integrate with jQueryUI's Overlay
	} //end if
	//--
	var inner_html = '<img src="' + SmartJS_BrowserUtils_LoaderImg + '" alt="... loading ...">';
	$('#smart_framework_overlay').remove(); // remove any instance if previous exist
	var overlay = $('<div id="smart_framework_overlay" ' + the_style + '></div>').hide().appendTo('body').css({ width: $(document).width(), height: $(document).height(), opacity: 0.85 });
	$('#smart_framework_overlay').html('<div style="width:100%; position:fixed; top:25px; left:0px;"><div align="center">' + inner_html + '</div></div>');
	//--
	overlay.fadeIn();
	//--
	if((typeof text != 'undefined') && (text != null) && (text != '')) {
		_class.GrowlNotificationAdd('', text, '', 750, false, '');
	} //end if else
	//--
	return overlay;
	//--
} //END FUNCTION

//=======================================

this.Overlay_Clear = function() {
	//--
	$('#smart_framework_overlay').html('');
	//--
} //END FUNCTION

//=======================================

this.Overlay_Hide = function(overlay) {
	//--
	try {
		overlay.fadeOut();
	} catch(err) {
	} //end try catch
	//--
	$('#smart_framework_overlay').remove(); // remove the instance
	//--
} //END FUNCTION

//======================================= Add Growl Notification

this.GrowlNotificationAdd = function(title, text, image, time, sticky, class_name) {
	//--
	if((typeof text == 'undefined') || (text == null) || (text == '')) {
		text = ' '; // fix
	} //end if
	//--
	return GrowlNotificationDoAdd(title, text, image, time, sticky, class_name);
	//--
} //END FUNCTION

//======================================= Remove Growl Notification

this.GrowlNotificationRemove = function(id) {
	//--
	GrowlNotificationDoRemove(id);
	//--
} //END FUNCTION

//======================================= Alert Dialog

this.alert_Dialog = function(y_message, evcode, y_title, y_width, y_height) {
	//--
	if(typeof SmartJS_BrowserUIUtils != 'undefined') { // use jQueryUI Dialog (the best choice)
		//--
		SmartJS_BrowserUIUtils.DialogAlert(y_message, evcode, y_title, y_width, y_height);
		//--
	} else if(typeof SmartSimpleDialog != 'undefined') { // use simple dialog
		//--
		SmartSimpleDialog.Dialog_Alert(y_message, evcode, y_title, y_width, y_height); // aaa
		//--
	} else { // fallback to native browser alert
		//--
		y_message = $(y_message).text(); // strip tags
		alert(y_message);
		//--
		if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
			try {
				eval('(function(){ ' + evcode + ' })();'); // sandbox
			} catch(err) {
				alert('ERROR: JS-Eval Error on Browser DialogAlert Function' + '\nDetails: ' + err);
			} //end try catch
		} //end if
		//--
	} //end if else
	//--
} //END FUNCTION

//======================================= Confirm Dialog

this.confirm_Dialog = function(y_question, evcode, y_title, y_width, y_height) {
	//--
	if(typeof SmartJS_BrowserUIUtils != 'undefined') { // use jQueryUI Dialog (the best choice)
		//--
		SmartJS_BrowserUIUtils.DialogConfirm(y_question, evcode, y_title, y_width, y_height);
		//--
	} else if(typeof SmartSimpleDialog != 'undefined') { // use simple dialog
		//--
		SmartSimpleDialog.Dialog_Confirm(y_question, evcode, y_title, y_width, y_height); // aaa
		//--
	} else { // fallback to native browser confirm dialog
		//--
		y_question = $(y_question).text(); // strip tags
		var the_confirmation = confirm(y_question);
		//--
		if(the_confirmation) {
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					eval('(function(){ ' + evcode + ' })();'); // sandbox
				} catch(err) {
					alert('ERROR: JS-Eval Error on Browser DialogConfirm Function' + '\nDetails: ' + err);
				} //end try catch
			} //end if
		} //end if
		//--
	} //end if else
	//--
} //END FUNCTION

//======================================= Confirm Form Submit

this.confirmSubmitForm = function(y_confirm, y_form, y_target, windowWidth, windowHeight, forcePopUp, forceDims) {
	//--
	if((typeof y_form == 'undefined') || (y_form == null) || (y_form == '')) {
		//--
		alert('ERROR: Form Object is Undefined ...');
		//--
	} else {
		//--
		SmartJS_BrowserUtils_CurrentForm = y_form; // export this var because we can't reference this object in eval
		//--
		var submit_code = 'SmartJS_BrowserUtils_CurrentForm.submit();'; // by default we do just submit
		if((typeof y_target != 'undefined') && (y_target != null) && (y_target != '')) {
			//--
			if((typeof windowWidth == 'undefined') || (windowWidth == null) || (windowWidth == '')) {
				windowWidth = '0';
			} //end if
			if((typeof windowHeight == 'undefined') || (windowHeight == null) || (windowHeight == '')) {
				windowHeight = '0';
			} //end if
			if((typeof forcePopUp == 'undefined') || (forcePopUp == null) || (forcePopUp == '')) {
				forcePopUp = '0';
			} //end if
			if((typeof forceDims == 'undefined') || (forceDims == null) || (forceDims == '')) {
				forceDims = '0';
			} //end if
			//--
			submit_code = 'SmartJS_BrowserUtils.PopUpSendForm(SmartJS_BrowserUtils_CurrentForm, \'' + y_target + '\', \'' + windowWidth + '\', \'' + windowHeight + '\', \'' + forcePopUp + '\', \'' + forceDims + '\');'; // in this situation we do both: popup/modal + submit
			//--
		} //end if
		//-- execute the above code only if confirmed
		_class.confirm_Dialog(y_confirm, submit_code);
		//--
	} //end if
	//--
} //END FUNCTION

//======================================= Prevent Modal Cascading, use Popup if a modal try to open another modal inside

this.Control_ModalCascading = function() {
	//--
	if((self.name == 'smart__iFModalBox__iFrame') || (parent.name == 'smart__iFModalBox__iFrame')) {
		SmartJS_BrowserUtils_Use_iFModalBox_Active = 0; // disable modal in modal, if so will force popup
	} //end if else
	//--
} //END FUNCTION

//======================================= Close a Modal / PopUp Window

this.CloseModalPopUp = function() {
	//--
	if(window.opener) {
		try {
			SmartJS_BrowserUtils.RefreshByEXEC_Parent(); // {{{SYNC-POPUP-Refresh-Parent-By-EXEC}}}
		} catch(err){}
		self.close();
	} else {
		try {
			parent.SmartJS_ModalBox.go_UnLoad();
		} catch(err){}
	} //end if else
	//--
} //END FUNCTION

//======================================= Delayed Close a Modal / PopUp Window

this.CloseDelayedModalPopUp = function(timeout) {
	//--
	setTimeout(function(){ SmartJS_BrowserUtils_PageAway = true; SmartJS_BrowserUtils.CloseModalPopUp(); }, parseInt(timeout));
	//--
} //END FUNCTION

//======================================= forms :: GET/POST (this function must be called by a form button, followed by 'return false;' to avoid fire send twice because this will also send the form after opening the popup/modal)

this.PopUpSendForm = function(objForm, strTarget, windowWidth, windowHeight, forcePopUp, forceDims, evcode) {
	//--
	var strUrl = '' + objForm.action; // ensure string and get form action
	//-- if cross domain calls between http:// and https:// will be made will try to force pop-up to avoid XSS Error
	var crr_protocol = '' + document.location.protocol;
	var crr_arr_url = strUrl.split(':');
	var crr_url = crr_arr_url[0] + ':';
	//--
	if(((crr_protocol === 'http:') || (crr_protocol === 'https:')) && ((crr_url === 'http:') || (crr_url === 'https:')) && (crr_url !== crr_protocol)) {
		forcePopUp = 1;
	} //end if
	//--
	if(((SmartJS_BrowserUtils_Use_iFModalBox_Active) && (forcePopUp != 1)) || (forcePopUp == -1)) {
		objForm.target = 'smart__iFModalBox__iFrame'; // use smart modal box
	} else {
		objForm.target = strTarget; // normal popUp use
	} //end if else
	//--
	init_PopUp(SmartJS_BrowserUtils_LoaderHtml, objForm.target, windowWidth, windowHeight, forcePopUp, forceDims);
	//--
	if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
		setTimeout(function(){ objForm.submit(); try { eval(evcode); } catch(err) { alert('After-Form JS Code Error: ' + err); } }, 500); // delay submit for buggy browsers
	} else {
		setTimeout(function(){ objForm.submit(); }, 500); // delay submit for buggy browsers
	} //end if else
	//--
	return false;
	//--
} //END FUNCTION

//======================================= links :: GET (this function must be called by a form button)

this.PopUpLink = function(strUrl, strTarget, windowWidth, windowHeight, forcePopUp, forceDims) {
	//--
	strUrl = '' + strUrl; // ensure string
	//-- if cross domain calls between http:// and https:// will be made will try to force pop-up to avoid XSS Error
	var crr_protocol = '' + document.location.protocol;
	var crr_arr_url = strUrl.split(':');
	var crr_url = crr_arr_url[0] + ':';
	//--
	if(((crr_protocol === 'http:') || (crr_protocol === 'https:')) && ((crr_url === 'http:') || (crr_url === 'https:')) && (crr_url !== crr_protocol)) {
		forcePopUp = 1;
	} //end if
	//--
	init_PopUp(strUrl, strTarget, windowWidth, windowHeight, forcePopUp, forceDims);
	//--
	return false;
	//--
} //END FUNCTION

//======================================= returns get a Cookie

this.getCookie = function(name) {
	//--
	var c = document.cookie.match(new RegExp('(^|;)\\s*' + name + '=([^;\\s]*)'));
	//--
	if(c && c.length >= 3) {
		return '' + decodeURIComponent(c[2]);
	} else {
		return ''; // fix to avoid working with null !!
	} //end if
	//--
} //END FUNCTION

//======================================= set a Cookie

this.setCookie = function(name, value, days, path, domain, secure) {
	//--
	if((typeof value == 'undefined') || (value == undefined) || (value == null) || (value == 'null')) {
		return; // bug fix (avoid to set null cookie)
	} //end if
	//--
	var d = new Date();
	//--
	if(days) {
		d.setTime(d.getTime() + (days * 8.64e7)); // now + days in milliseconds
	} //end if
	//--
	document.cookie = name + '=' + SmartJS_CoreUtils.escape_url(value) + (days ? ('; expires=' + d.toGMTString()) : '') + '; path=' + (path || '/') + (domain ? ('; domain=' + domain) : '') + (secure ? '; secure' : '');
	//--
} //END FUNCTION

//======================================= delete a Cookie

this.deleteCookie = function(name, path, domain) {
	//--
	_class.setCookie(name, '', -1, path, domain); // sets expiry to now - 1 day
	//--
} //END FUNCTION

//======================================= Resize iFrames Dinamically on Height

this.resize_iFrame = function(f) {
	//--
	f.style.height = "1px";
	f.style.height = f.contentWindow.document.body.scrollHeight + "px";
	//--
} //END FUNCTION

//======================================= Limit TextArea v.1.2

this.textArea_Limit = function(y_field, y_countfield, y_maxlimit) {
	//--
	var maxlimit = parseInt(y_maxlimit);
	if((maxlimit < 1) || isNaN(maxlimit)) {
		alert('TextArea (with Counter) :: Invalid Text Limit');
		maxlimit = 1;
	} //end if
	//--
	var field = document.getElementById(y_field);
	var countfield = document.getElementById(y_countfield);
	//--
	if(field.value.length > maxlimit) { // if too long then trim it!
		field.value = field.value.substring(0, maxlimit);
	} //end if
	//-- update the counter
	countfield.value = maxlimit - field.value.length;
	//--
} //END FUNCTION

//======================================= Catch TAB Key in TextArea v.1.0

// Example: <textarea id="txt" onKeyDown="SmartJS_BrowserUtils.catch_TABKey(event);">
this.catch_TABKey = function(evt) {
	//--
	var tab = "\t";
	var t = evt.target;
	var ss = t.selectionStart;
	var se = t.selectionEnd;
	var scrollTop = t.scrollTop;
	var scrollLeft = t.scrollLeft;
	//--
	if(evt.keyCode == 9) {
		//-- Tab key - insert tab expansion
		evt.preventDefault();
		//-- Special case of multi line selection
		if(ss != se && t.value.slice(ss,se).indexOf("\n") != -1) {
			//-- In case selection was not of entire lines (e.g. selection begins in the middle of a line) we have to tab at the beginning as well as at the start of every following line.
			var pre = t.value.slice(0,ss);
			var sel = t.value.slice(ss,se).replace(/\n/g,"\n"+tab);
			var post = t.value.slice(se,t.value.length);
			//--
			t.value = pre.concat(tab).concat(sel).concat(post);
			t.selectionStart = ss + tab.length;
			t.selectionEnd = se + tab.length;
		} else {
			//-- The Normal Case (no selection or selection on one line only)
			t.value = t.value.slice(0,ss).concat(tab).concat(t.value.slice(ss,t.value.length));
			if (ss == se) {
				t.selectionStart = t.selectionEnd = ss + tab.length;
			} else {
				t.selectionStart = ss + tab.length;
				t.selectionEnd = se + tab.length;
			} //end if
		} //end if else
		//--
		t.scrollTop = scrollTop;
		t.scrollLeft = scrollLeft;
		//--
	} //end if
	//--
} //END FUNCTION

//======================================= Check All Checkbox

this.checkAll_CkBoxes = function(y_form_name, y_element_id, y_element_checked) {
	//--
	var i;
	//--
	for(i=0; i<document.forms[y_form_name].elements.length; i++) {
		//--
		if(document.forms[y_form_name].elements[i].type == "checkbox") {
			//--
			if(typeof y_element_id == 'undefined') { // default
				//--
				document.forms[y_form_name].elements[i].checked = !document.forms[y_form_name].elements[i].checked;
				//--
			} else {
				//--
				if(y_element_id == document.forms[y_form_name].elements[i].id) {
					document.forms[y_form_name].elements[i].checked = y_element_checked;
				} //end if
				//--
			} //end if
			//--
		} //end if
		//--
	} //end for
	//--
} //END FUNCTION

//======================================= Clone Elements

this.CloneElement = function(elem, destination, elType, maxLimit) {
	//--
	maxLimit = parseInt(maxLimit);
	if(isNaN(maxLimit) || (maxLimit < 0) || (maxLimit > 255)) {
		maxLimit = 255; // hard code limit
	} //end if
	//-- init
	var control_num = parseInt($('body').find('[id^=' + 'clone_control__' + SmartJS_CoreUtils.escape_js(elem) + ']').length);
	if((control_num <= 0) || isNaN(control_num)) {
		$('#' + elem).before('<img id="' + 'clone_control__' + SmartJS_CoreUtils.escape_html(elem) + '" alt="Add New" title="Add New" src="' + SmartJS_CoreUtils.escape_html(SmartJS_BrowserUtils_Cloner_Img_Add) + '" style="cursor:pointer; vertical-align:middle;" onClick="SmartJS_BrowserUtils.CloneElement(\'' + SmartJS_CoreUtils.escape_js(elem) + '\', \'' + SmartJS_CoreUtils.escape_js(destination) + '\', \'' + SmartJS_CoreUtils.escape_js(elType) + '\', ' + parseInt(maxLimit) + ');' + '">&nbsp;&nbsp;</span>');
		return;
	} //end if
	//-- do clone
	var cloned_num = parseInt($('body').find('[id^=' + 'clone_of__' + SmartJS_CoreUtils.escape_js(elem) + '_' + ']').length);
	if((cloned_num <= 0) || isNaN(cloned_num)) {
		cloned_num = 0;
	} //end if
	if(cloned_num >= (maxLimit - 1)) {
		return;
	} //end if
	//alert(cloned_num);
	//--
	var date = new Date();
	var seconds = date.getTime();
	var milliseconds = date.getMilliseconds();
	var randNum = Math.random().toString(36);
	var uuID = SmartJS_CryptoHash.sha1('This is a UUID for #' + cloned_num + ' @ ' + randNum + ' :: ' + seconds + '.' + milliseconds);
	//--
	var clone_data = $('#' + elem).clone().attr('id', 'clone_of__' + SmartJS_CoreUtils.escape_js(elem) + '_' + SmartJS_CoreUtils.escape_js(uuID));
	//--
	$('#' + destination).append('<span id="' + 'clone_container__' + SmartJS_CoreUtils.escape_html(elem) + '_' + SmartJS_CoreUtils.escape_html(uuID) + '"><br><img alt="Remove" title="Remove" src="' + SmartJS_CoreUtils.escape_html(SmartJS_BrowserUtils_Cloner_Img_Remove) + '" style="cursor:pointer; vertical-align:middle;" onClick="$(this).parent().remove();">&nbsp;&nbsp;</span>');
	//--
	switch(elType) {
		case 'text-input':
		case 'text-area':
		case 'file-input':
			clone_data.val('').appendTo('#' + 'clone_container__' + SmartJS_CoreUtils.escape_js(elem) + '_' + SmartJS_CoreUtils.escape_js(uuID));
			break;
		case 'html-element': // regular html element
		default: // other cases
			clone_data.appendTo('#' + 'clone_container__' + SmartJS_CoreUtils.escape_js(elem) + '_' + SmartJS_CoreUtils.escape_js(uuID));
	} //end switch
	//--
} //END FUNCTION

//======================================= AJAX Send Form

this.Submit_Form_By_Ajax = function(the_form_id, url, growl) {
	//--
	var ajax = _class.Ajax_XHR_GetByForm(the_form_id, url, 'json');
	if(ajax === null) {
		alert('ERROR: Submit Form by Ajax / Null Object !');
		return;
	} //end if
	//--
	var page_overlay = _class.Overlay_Show();
	//--
	ajax.done(function(msg) { // {{{JQUERY-AJAX}}}
		//--
		_class.Overlay_Clear();
		//--
		var doReplaceDiv = 'no';
		//--
		if(msg != null) {
			//--
			if((msg.hasOwnProperty('completed')) && (msg.completed == 'DONE') && (msg.hasOwnProperty('status')) && ((msg.status == 'OK') || (msg.status == 'ERROR')) && (msg.hasOwnProperty('action')) && (msg.action != null) && (msg.hasOwnProperty('title')) && (msg.title != null) && (msg.hasOwnProperty('message')) && (msg.message != null) && (msg.hasOwnProperty('redirect')) && (msg.hasOwnProperty('replace_div')) && (msg.hasOwnProperty('replace_html'))) {
				//--
				if(msg.status == 'OK') { // OK
					//--
					if((msg.replace_div != null) && (msg.replace_div != '') && (msg.replace_html != null) && (msg.replace_html != '')) {
						doReplaceDiv = 'yes';
					} //end if
					//--
					if((msg.redirect != null) && (msg.redirect != '') && (msg.message == '')) {
						_class.RedirectDelayedToURL(msg.redirect, 250);
					} else {
						if(doReplaceDiv == 'yes') {
							$('#'+msg.replace_div).html(SmartJS_Base64.decode(msg.replace_html));
						} //end if
						if((doReplaceDiv != 'yes') || (msg.message != '')) {
							if(growl === 'yes') {
								Message_AjaxForm_Notification(page_overlay, '' + SmartJS_CoreUtils.escape_html(msg.title), '<img src="lib/core/img/sign_info.png" align="right">' + SmartJS_Base64.decode(msg.message), msg.redirect, 'yes', 'gritter-green', 2000);
							} else {
								Message_AjaxForm_Notification(page_overlay, '' + SmartJS_CoreUtils.escape_html(msg.action) + ' / ' + SmartJS_CoreUtils.escape_html(msg.title), '<img src="lib/core/img/sign_info.png" align="right">' + SmartJS_Base64.decode(msg.message), msg.redirect, 'no', '', 2000);
							} //end if else
						} //end if else
						if(growl !== 'yes') {
							_class.Overlay_Hide(page_overlay);
						} //end if
					} //end if
					//--
				} else { // ERROR
					if(growl === 'yes') {
						Message_AjaxForm_Notification(page_overlay, '* ' + SmartJS_CoreUtils.escape_html(msg.title), '<img src="lib/core/img/sign_warn.png" align="right">' + SmartJS_Base64.decode(msg.message), msg.redirect, 'yes', 'gritter-red', 3000);
					} else {
						Message_AjaxForm_Notification(page_overlay, '* ' + SmartJS_CoreUtils.escape_html(msg.action) + ' / ' + SmartJS_CoreUtils.escape_html(msg.title), '<img src="lib/core/img/sign_warn.png" align="right">' + SmartJS_Base64.decode(msg.message), msg.redirect, 'no', '', 3000);
					} //end if else
					if(growl !== 'yes') {
						_class.Overlay_Hide(page_overlay);
					} //end if
				} //end if else
				//--
			} else {
				//--
				alert('ERROR (2): Invalid DataObject Format !' + '\n' + SmartJS_CoreUtils.print_Object(msg)); // this must be alert because errors may prevent dialog
				_class.Overlay_Hide(page_overlay);
				//--
			} //end if else
			//--
		} else {
			//--
			alert('ERROR (3): DataObject is NULL !'); // this must be alert because errors may prevent dialog
			_class.Overlay_Hide(page_overlay);
			//--
		} //end if else
		//--
	}).fail(function(msg) {
		//--
		_class.alert_Dialog('ERROR (1): Invalid Server Response !' + '\n' + 'Status Code: ' + msg.status + '\n' + msg.responseText, '', 'ERROR', 750, 425);
		_class.Overlay_Hide(page_overlay);
		//--
	});
	//--
} //END FUNCTION

//======================================= Background Post a Form (it does not catch the result, just send it to ensure updates in some cases ...)

// this will not work with forms that must upload because will do just serialize() on that
this.Background_Send_a_Form = function(other_form_id, evcode) {
	//--
	var ajax = _class.Ajax_XHR_GetByForm(other_form_id, '', 'text'); // since the answer is not evaluated because can vary, will use text
	if(ajax === null) {
		alert('ERROR: Submit Form by Ajax / Null Object !');
		return;
	} //end if
	//--
	var page_overlay = _class.Overlay_Show();
	//--
	ajax.done(function(msg) { // {{{JQUERY-AJAX}}}
		//--
		_class.Overlay_Clear();
		//--
		if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
			try {
				eval('(function(){ ' + evcode + ' })();'); // sandbox
			} catch(err) {
				alert('ERROR: JS-Eval Error on Background Send Form' + '\nDetails: ' + err);
			} //end try catch
		} //end if
		//--
		_class.Overlay_Hide(page_overlay);
		//--
	}).fail(function(msg) {
		//--
		_class.alert_Dialog('ERROR (1): Invalid Background Form Update !' + '\n' + 'Status Code: ' + msg.status + '\n' + msg.responseText, '', 'ERROR', 750, 425);
		//--
		_class.Overlay_Hide(page_overlay);
		//--
	});
	//--
} //END FUNCTION

//======================================= AJAX XHR Form

this.Ajax_XHR_GetByForm = function(the_form_id, url, data_type) {
	//--
	var ajax = null;
	var data = '';
	//--
	if((typeof url == 'undefined') || (url == null) || (url == '')) {
		url = $('#' + the_form_id).attr('action'); // try to get form action if URL is empty
	} //end if
	//--
	if((typeof url == 'undefined') || (url == null) || (url == '')) {
		alert('Empty URL for Ajax_XHR_GetByForm ...');
		return null;
	} //end if
	//--
	if((the_form_id == null) || (the_form_id == '')) {
		//--
		ajax = _class.Ajax_XHR_Request_From_URL(url, 'GET', data_type, '');
		//console.log('Form.XHR.Ajax: using No Data ... (empty formID)');
		//--
	} else {
		//--
		var found_files = false;
		if(($('#' + the_form_id).attr('method') == 'post') && ($('#' + the_form_id).attr('enctype') == 'multipart/form-data')) {
			var have_files = $('#' + the_form_id).find('input:file');
			if(typeof have_files != 'undefined') {
				if(typeof have_files[0] != 'undefined') {
					found_files = true;
				} //end if
			} //end if
			//console.log('The Form Have Files and is Multi-Part');
		} //end if
		//--
		if(found_files !== true) {
			//--
			data = $('#' + the_form_id).serialize(); // no files detected use serialize
			ajax = _class.Ajax_XHR_Request_From_URL(url, 'POST', data_type, data);
			//console.log('Form.XHR.Ajax: using Serialized Form Data ... ' + the_form_id);
			//--
		} else {
			//--
			try {
				var theFormObj = document.getElementById(the_form_id);
			} catch(err) {
				alert('ERROR: Invalid FormID for Ajax_XHR_GetByForm !');
				return null;
			} //end try catch
			//--
			try {
				data = new FormData(theFormObj);
				data.append('ie__fix', '...dummy-variable...'); // workarround for IE10/11 bugfix with array variables, after array of vars a non-array var must be to avoid corruption: http://blog.yorkxin.org/posts/2014/02/06/ajax-with-formdata-is-broken-on-ie10-ie11/
				ajax = _class.Ajax_XHR_PostMultiPart_To_URL(url, data_type, data);
				//console.log('Form.XHR.Ajax: using MultiPart Form Data ... ' + the_form_id);
			} catch(err) {
				alert('ERROR: Ajax_XHR_GetByForm / FormData Object Failed. File Attachments NOT sent ! Try to upgrade / change your browser. Your browser does not support HTML5 File Uploads.');
				data = $('#' + the_form_id).serialize(); // no files detected use serialize
				ajax = _class.Ajax_XHR_Request_From_URL(url, 'POST', data_type, data);
				//console.log('Form.XHR.Ajax: using MultiPart Form Data (FallBack without File Attachments) ... ' + the_form_id);
			} //end try catch
			//--
		} //end if else
		//--
	} //end if
	//--
	return ajax;
	//--
} //END FUNCTION

//======================================= AJAX Post MultiPart Form (Data)

this.Ajax_XHR_PostMultiPart_To_URL = function(y_url, y_data_type, y_data_formData) {
	//--
	if((typeof y_url == 'undefined') || (y_url == null) || (y_url == '')) {
		y_url = '#';
		console.log('Empty URL for Ajax_XHR_Request_From_URL ...');
	} else {
		y_url = '' + y_url;
	} //end if
	//--
	if((typeof y_data_type == 'undefined') || (y_data_type == null)) {
		y_data_type = '';
	} else {
		y_data_type = '' + y_data_type;
	} //end if
	switch(y_data_type) {
		case 'json':
			y_data_type = 'json'; // Evaluates the response as JSON and returns a JavaScript object. The JSON data is parsed in a strict manner; any malformed JSON is rejected and a parse error is thrown.
			break;
		case 'html':
			y_data_type = 'html'; // Returns HTML as plain text; included script tags are evaluated when inserted in the DOM.
			break;
		case 'text':
		default:
			y_data_type = 'text'; // A plain text string.
	} //end switch
	//--
	return $.ajax({
		async: true,
		cache: false,
		timeout: 0,
		type: 'POST',
		url: y_url,
		//--
		contentType: false,
		processData: false,
		data: y_data_formData,
		dataType: y_data_type
		//--
		/* the below functions must be assigned later to avoid execution here
		success: function(msg) {},
		error: function(msg) {}
		*/
	});
	//--
} //END FUNCTION

//======================================= AJAX Request (with *optional* Basic Auth)

this.Ajax_XHR_Request_From_URL = function(y_url, y_method, y_data_type, y_data_arr_or_serialized, y_AuthUser, y_AuthPass, y_Headers) {
	//--
	if((typeof y_url == 'undefined') || (y_url == null) || (y_url == '')) {
		y_url = '#';
		console.log('Empty URL for Ajax_XHR_Request_From_URL ...');
	} else {
		y_url = '' + y_url;
	} //end if
	if((typeof y_method == 'undefined') || (y_method == null) || (y_method == '')) {
		y_method = 'GET';
	} //end if
	if((y_method != 'GET') && (y_method != 'POST')) {
		y_method = 'GET';
	} //end if
	//--
	if((typeof y_data_type == 'undefined') || (y_data_type == null)) {
		y_data_type = '';
	} else {
		y_data_type = '' + y_data_type;
	} //end if
	switch(y_data_type) {
		case 'json':
			y_data_type = 'json'; // Evaluates the response as JSON and returns a JavaScript object. The JSON data is parsed in a strict manner; any malformed JSON is rejected and a parse error is thrown.
			break;
		case 'html':
			y_data_type = 'html'; // Expects valid HTML ; included javascripts are evaluated when inserted in the DOM
			break;
		case 'text':
		default:
			y_data_type = 'text'; // Expects Text or HTML ; If HTML, includded javascripts are not evaluated when inserted in the DOM
	} //end switch
	//--
	if((typeof y_data_arr_or_serialized == 'undefined') || (y_data_arr_or_serialized == null)) {
		y_data_arr_or_serialized = '';
	} //end if
	//--
	var the_headers = {}; // default
	if((typeof y_Headers != 'undefined') && (y_Headers != null)) {
		the_headers = y_Headers;
	} //end if
	var the_user = '';
	var the_pass = '';
	if(((typeof y_AuthUser != 'undefined') && (y_AuthUser != null)) && ((typeof y_AuthPass != 'undefined') && (y_AuthPass != null))) {
		the_user = '' + y_AuthUser;
		the_pass = '' + y_AuthPass;
	} //end if
	//--
	return $.ajax({
		//--
		async: true,
		cache: false,
		timeout: 0,
		type: y_method,
		url: y_url,
		//--
		headers: the_headers, // extra headers
		username: the_user, // auth user name
		password: the_pass, // auth user pass
		//--
		data: y_data_arr_or_serialized, // this can be a serialized string as: '&var1=value1&var2=value2' or array: { var1: "value1", var2: "value2" }
		dataType: y_data_type // json, html or text
		//--
	});
	/* [Sample Implementation:]
	var ajax = SmartJS_BrowserUtils.Ajax_XHR_Request_From_URL(...);
	// {{{JQUERY-AJAX}}} :: the below functions: success() / error() must be assigned on execution because they are actually executing the ajax request and the Ajax_XHR_Request_From_URL() just creates the request object !
	ajax.done(function(msg) { // instead of .success (which is deprecated)
		...
	}).fail(function(msg) { // instead of .error (which is deprecated)
		...
	}).always(function(msg) { // instead of .complete (which is deprecated)
	});
	*/
	//--
} //END FUNCTION

//======================================= Load DIV content by Ajax

this.Load_Div_Content_By_Ajax = function(y_div, y_img_loader, y_url, y_method, y_data_type, y_data_serialized) {
	//--
	if((typeof y_div == 'undefined') || (y_div == null) || (y_div == '')) {
		_class.alert_Dialog('ERROR (1): Invalid DivID in Ajax LoadDivContent From URL', '', 'ERROR', 750, 425);
		return -1;
	} //end if
	//--
	if((typeof y_img_loader != 'undefined') && (y_img_loader != null) && (y_img_loader != '')) {
		if($('#' + y_div + '__Load_Div_Content_By_Ajax').length == 0) {
			$('#' + y_div).prepend('<span id="' + y_div + '__Load_Div_Content_By_Ajax' + '"><img src="' + y_img_loader + '" title="Loading ..." alt="Loading ..."></span><br>');
		} //end if
	} //end if
	//--
	var ajax = _class.Ajax_XHR_Request_From_URL(y_url, y_method, y_data_type, y_data_serialized);
	//--
	ajax.done(function(msg) { // {{{JQUERY-AJAX}}}
		$('#' + y_div).html(msg); // this will also evaluate the js scripts
	}).fail(function(msg) {
		if(SmartJS_BrowserUtils_NotifyLoadError === false) {
			console.log('ERROR (2): Invalid Server Response for LoadDivContent !' + '\n' + 'Status Code: ' + msg.status + '\n' + msg.responseText);
		} else {
			_class.alert_Dialog('ERROR (2): Invalid Server Response for LoadDivContent !' + '\n' + 'Status Code: ' + msg.status + '\n' + msg.responseText, '', 'ERROR', 750, 425);
		} //end if else
		$('#' + y_div).html(''); // clear
	});
	//--
} //END FUNCTION

//======================================= Add Bookmark to Favorites

this.bookmark_url = function(title, url) {
	//--
	try {
		if(browser.msie) { // ie
			//--
			window.external.AddFavorite(url, title);
			//--
		} else if(browser.mozilla || browser.webkit) { // ffox or webkit
			//--
			alert('Press CTRL+D to save / Bookmark this URL to your Favorites ...');
			//--
		} else if(browser.opera){ // opera
			//--
			var elem = document.createElement('a');
			elem.setAttribute('href',url);
			elem.setAttribute('title',title);
			elem.setAttribute('rel','sidebar');
			elem.click();
			//--
		} else {
			//--
			alert('Your Browser appear not to support Add-To-Favorites / Bookmarks !');
			//--
		} //end if else
	} catch(err) {
		//--
		alert('Your Browser failed to Add-To-Favorites (Bookmark) this URL. Try to do it manually ...');
		//--
	} //end try catch
	//--
	return false;
	//--
} //END FUNCTION

// ###################################### PRIVATES

//======================================= Message (Dialog/Growl) Notification for Ajax Forms

var Message_AjaxForm_Notification = function(page_overlay, ytitle, ymessage, yredirect, growl, class_growl, timeout) {
	//--
	if(growl === 'yes') {
		//--
		var TheGrowlNotification = _class.GrowlNotificationAdd(ytitle, ymessage, '', timeout, false, class_growl);
		//--
		if((typeof yredirect != 'undefined') && (yredirect != null) && (yredirect != '')) {
			_class.RedirectDelayedToURL(yredirect, (timeout + 500));
		} else {
			setTimeout(function(){ SmartJS_BrowserUtils.Overlay_Hide(page_overlay); }, (timeout + 500));
		} //end if
		//--
	} else {
		//--
		var active_code = '';
		if((typeof yredirect != 'undefined') && (yredirect != null) && (yredirect != '')) {
			active_code = 'SmartJS_BrowserUtils.RedirectDelayedToURL(\'' + SmartJS_CoreUtils.escape_js(yredirect) + '\', 100);';
		} //end if
		//--
		_class.alert_Dialog(ymessage, active_code, ytitle, 550, 275);
		//--
	} //end if else
	//--
} //END FUNCTION

//======================================= Add Growl Notification

var GrowlNotificationDoAdd = function(title, text, image, time, sticky, class_name) {
	//--
	if(image !== '') {
		image = '<img src="' + image + '" align="right">';
	} else {
		image = '';
	} //end if
	//--
	if(class_name !== undefined) {
		class_name = '' + class_name;
	} else {
		class_name = '';
	} //end if
	//--
	var growl = $.gritter.add({
		class_name: '' + class_name,
		title: '' + title + image,
		text: '' + text,
		sticky: sticky,
		time: parseInt(time)
	});
	//--
	return growl;
	//--
} //END FUNCTION

//======================================= Remove Growl Notification

var GrowlNotificationDoRemove = function(id) {
	//--
	if((typeof id != 'undefined') && (id !== undefined) && (id != '')) {
		try {
			$.gritter.remove(id);
		} catch(e){}
	} else {
		$.gritter.removeAll();
	} //end if else
	//--
} //END FUNCTION

//======================================= Open Req. PopUp

var init_PopUp = function(strUrl, strTarget, windowWidth, windowHeight, forcePopUp, forceDims) {
	//--
	if(((typeof SmartJS_ModalBox != 'undefined') && (SmartJS_BrowserUtils_Use_iFModalBox_Active) && (forcePopUp != 1)) || (forcePopUp == -1)) {
		//-- use smart modal box
		if(forceDims != 1) {
			SmartJS_ModalBox.go_Load(strUrl, SmartJS_BrowserUtils_Use_iFModalBox_Protection); // we do not use here custom size
		} else {
			SmartJS_ModalBox.go_Load(strUrl, SmartJS_BrowserUtils_Use_iFModalBox_Protection, windowWidth, windowHeight); // we use here custom size
		} //end if else
		//--
	} else {
		//--
		var the_screen_width = 0;
		try { // try to center
			the_screen_width = parseInt(screen.width);
		} catch(e){} //end try catch
		if((the_screen_width <= 0) || (isNaN(the_screen_width))) {
			the_screen_width = 920;
		} //end if
		//--
		var the_screen_height = 0;
		try { // try to center
			the_screen_height = parseInt(screen.height);
		} catch(e){} //end try catch
		if((the_screen_height <= 0) || (isNaN(the_screen_height))) {
			the_screen_height = 700;
		} //end if
		//--
		var maxW = parseInt(the_screen_width * 0.90);
		windowWidth = parseInt(windowWidth);
		if(isNaN(windowWidth) || (windowWidth > maxW)) {
			windowWidth = maxW;
		} //end if
		//--
		var maxH = parseInt(the_screen_height * 0.80); // on height there are menus or others
		windowHeight = parseInt(windowHeight);
		if(isNaN(windowHeight) || (windowHeight > maxH)) {
			windowHeight = maxH;
		} //end if
		//--
		if((windowWidth < 200) || (windowHeight < 100)) {
			windowWidth = maxW;
			windowHeight = maxH;
		} //end if
		//--
		var windowTop = 50;
		windowLeft = parseInt((the_screen_width / 2) - (windowWidth / 2));
		if((windowLeft < 10) || isNaN(windowLeft)) {
			windowLeft = 10;
		} //end if
		//-- normal use :: events (normal use): SmartJS_BrowserUtils_PopUpWindow == null ; SmartJS_BrowserUtils_PopUpWindow.closed
		try { // pre-focus if opened
			if(SmartJS_BrowserUtils_PopUpWindow) {
				_class.windowFocus(SmartJS_BrowserUtils_PopUpWindow);
			} //end if
		} catch(err){}
		SmartJS_BrowserUtils_PopUpWindow = window.open(strUrl, strTarget, "top=" + windowTop + ",left=" + windowLeft + ",width=" + windowWidth + ",height=" + windowHeight + ",toolbar="+SmartJS_BrowserUtils_PopUp_ShowToolBar+",scrollbars=1,resizable=1");
		if(SmartJS_BrowserUtils_PopUpWindow) {
			try { // post-focus
				_class.windowFocus(SmartJS_BrowserUtils_PopUpWindow); // focus
			} catch(err){}
			var timer = setInterval(function() {
				if(SmartJS_BrowserUtils_PopUpWindow.closed) {
					//--
					clearInterval(timer);
					//--
					try {
						SmartJS_BrowserUtils.RefreshByEXEC_Parent('self'); // {{{SYNC-POPUP-Refresh-Parent-By-EXEC}}}
					} catch(err){}
					//--
				} //end if
			}, 250);
		} //end if
		//--
	} //end if else
	//--
} //end function

//=======================================

} //END CLASS

//==================================================================
//==================================================================

// #END
