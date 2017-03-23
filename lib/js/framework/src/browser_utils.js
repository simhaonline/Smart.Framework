
// [LIB - SmartFramework / JS / Browser Utils]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.7 r.2017.02.22 / smart.framework.v.2.3

// DEPENDS: jQuery, jQuery.Gritter, SmartJS_CoreUtils, SmartJS_CryptoHash
// OPTIONAL-DEPENDS: SmartJS_ModalBox, SmartSimpleDialog, SmartJS_BrowserUIUtils

//==================================================================
//==================================================================

//-- setup
var SmartJS_BrowserUtils_NotifyLoadError = true;
var SmartJS_BrowserUtils_Use_iFModalBox_Active = 0;
var SmartJS_BrowserUtils_Use_iFModalBox_Protection = 0;
var SmartJS_BrowserUtils_PopUp_ShowToolBar = 0;
var SmartJS_BrowserUtils_FullScreen_Img = 'lib/js/framework/img/fullscreen.png';
var SmartJS_BrowserUtils_Cloner_Img_Add = 'lib/js/framework/img/clone-insert.png';
var SmartJS_BrowserUtils_Cloner_Img_Remove = 'lib/js/framework/img/clone-remove.png';
var SmartJS_BrowserUtils_LoaderImg = 'lib/js/framework/img/loading_imodal.gif';
var SmartJS_BrowserUtils_LoaderHtml = 'lib/js/framework/loading.html';
var SmartJS_BrowserUtils_ImgOK = 'lib/core/img/sign_info.png';
var SmartJS_BrowserUtils_ImgWarn = 'lib/core/img/sign_warn.png';
//-- privates
var SmartJS_BrowserUtils_PageAway = false;		// by default the PageAway confirm is set to false
var SmartJS_BrowserUtils_PopUpWindow = null; 	// this holds the pop-up window reference, we don't want to open new popups each time, so we use it if exists and just focus it (identified by window.name / target.name)
var SmartJS_BrowserUtils_RefreshState = 0; 		// if=1, will refresh parent
var SmartJS_BrowserUtils_RefreshURL = ''; 		// ^=1 -> if != '' will redirect parent
var SmartJS_BrowserUtils_CurrentForm = null;	// this holds the current form to submit reference
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


/**
 * Detect if a Browser Window is iFrame
 *
 * @method WindowIsiFrame
 * @static
 * @return 	{Boolean} 							TRUE if iFrame, FALSE if not
 */
this.WindowIsiFrame = function() {
	//--
	if(window.self !== window.top) {
		return true; // is iframe
	} else {
		return false; // not an iframe
	} //end if else
	//--
} //END FUNCTION


/**
 * Detect if a Browser Window is PopUp
 *
 * @method WindowIsPopup
 * @static
 * @return 	{Boolean} 							TRUE if PopUp, FALSE if not
 */
this.WindowIsPopup = function() {
	//--
	if(window.opener) {
		return true; // is popup
	} else {
		return false; // not an popup
	} //end if else
	//--
} //END FUNCTION


/**
 * Print current Browser Page
 *
 * @method PrintPage
 * @static
 */
this.PrintPage = function() {
	//--
	try {
		self.print();
	} catch(err){
		alert('WARNING: Printing may not be available in your browser: ' + err);
	} //end try catch
	//--
} //END FUNCTION


/**
 * Count Down handler that bind to a HTML Element
 *
 * @method CountDown
 * @static
 * @param 	{Integer} 	counter 	The countdown counter
 * @param 	{String} 	elID 		The HTML Element ID to bind to
 * @param 	{JS-Code} 	evcode 		*Optional* the JS Code to execute on countdown complete (to zero)
 */
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
					jQuery('#' + elID).text(counter);
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


/**
 * Page Away control handler
 *
 * @method PageAwayControl
 * @static
 * @param 	{String} 	the_question 	The Question to confirm for navigate away this page
 */
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
	if(_class.WindowIsiFrame() === true) { // try to set only if iframe
		try {
			if(typeof parent.SmartJS_ModalBox != 'undefined') {
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
		} catch (err) {
			console.log('NOTICE: BrowserUtils Failed to Set BeforeUnload PageAway on ModalBox: ' + err);
		} //end try catch
	} //end if
	//--
} //END FUNCTION


/**
 * Redirect to URL current browser window
 *
 * @method RedirectToURL
 * @static
 * @param 	{String} 	yURL 		The URL (relative or absolute) to redirect to
 */
this.RedirectToURL = function(yURL) {
	//--
	if((typeof yURL != 'undefined') && (yURL != '') && (yURL !== null)) {
		self.location = '' + yURL;
	} else {
		console.log('NOTICE: Invalid URL to Redirect ... (Browser Utils)');
	} //end if else
	//--
} //END FUNCTION


/**
 * Delayed Redirect to URL current browser window
 *
 * @method RedirectDelayedToURL
 * @static
 * @param 	{String} 	yURL 		The URL (relative or absolute) to redirect to
 * @param 	{Integer} 	ytime 		The time delay in milliseconds
 */
this.RedirectDelayedToURL = function(yURL, ytime) {
	//--
	setTimeout(function(){ SmartJS_BrowserUtils_PageAway = true; SmartJS_BrowserUtils.RedirectToURL(yURL); }, ytime);
	//--
} //END FUNCTION


/**
 * Redirect to URL parent browser window
 *
 * @method RedirectParent
 * @static
 * @param 	{String} 	yURL 			The URL (relative or absolute) to redirect to
 */
this.RedirectParent = function(yURL) {
	//--
	if((typeof yURL == 'undefined') || (yURL == null) || (yURL == '')) {
		alert('WARNING: Parent Redirection to Empty URL is not allowed !');
		return;
	} //end if
	//--
	if(_class.WindowIsPopup() === true) { // when called from PopUp
		try {
			window.opener.location = yURL;
		} catch(err){
			console.log('NOTICE: BrowserUtils Failed to Redirect Parent from PopUp: ' + err);
		} //end try catch
	} else if(_class.WindowIsiFrame() === true) { // when called from iFrame
		try {
			parent.location = yURL;
		} catch(err){
			console.log('NOTICE: BrowserUtils Failed to Redirect Parent from ModalBox: ' + err);
		} //end try catch
	} //end if else
	//--
} //END FUNCTION


/**
 * Lazy Refresh parent browser window or Lazy redirect parent window to another URL.
 * This method is different than RedirectParent() because will be executed just after the child (modal iFrame / PopUp) is closed.
 * It will just trigger a lazy refresh on parent that will be executed later, after closing current child window.
 *
 * @method RefreshParent
 * @static
 * @param 	{String} 	yURL 			*Optional* The URL (relative or absolute) to redirect to ; if this parameter is not specified will just reload the parent window with the same URL
 */
this.RefreshParent = function(yURL) {
	//--
	if(_class.WindowIsPopup() === true) { // when called from PopUp
		Refresh_SET_Popup_Parent(yURL); // catched errors indide
	} else if(_class.WindowIsiFrame() === true) { // when called from iFrame
		try {
			if(self.name) {
				if(typeof parent.SmartJS_ModalBox != 'undefined') {
					if(self.name === parent.SmartJS_ModalBox.getName()) {
						//console.log('ModalBox Set Refresh Parent: ' + self.name);
						parent.SmartJS_ModalBox.Refresh_SET_iFrame_Parent(yURL);
					} //end if
				} //end if
			} //end if
		} catch(err){
			console.log('NOTICE: BrowserUtils Failed to Refresh Parent from ModalBox: ' + err);
		} //end try catch
	} //end if else
	//--
} //END FUNCTION


/**
 * Close a Modal / PopUp child browser window.
 * If a parent refresh is pending will execute it after.
 * This is the prefered way to close a child window (modal iFrame / PopUp) using a button or just executing this code in a child page.
 *
 * @method CloseModalPopUp
 * @static
 */
this.CloseModalPopUp = function() {
	//--
	if(_class.WindowIsPopup() === true) { // when called from PopUp
		//if(SmartJS_BrowserUtils_PageAway !== true) { // this was not functioning as expected because this is changed after firing the close button
		Refresh_EXEC_Popup_Parent(); // this is no more necessary as it is fired directly by close monitor {{{SYNC-POPUP-Refresh-Parent-By-EXEC}}} ; err is catched inside ; this is required for the situation that popup is created manually with window.open NOT by using the init_PopUp() in this class
		try {
			self.close(); // this may fail if window reference with parent is lost, needs try/catch
		} catch(err){}
		//} //end if
	} else if(_class.WindowIsiFrame() === true) { // when called from iFrame
		try {
			if(self.name) {
				if(typeof parent.SmartJS_ModalBox != 'undefined') {
					if(self.name === parent.SmartJS_ModalBox.getName()) {
						parent.SmartJS_ModalBox.go_UnLoad(); // {{{SYNC-MODAL-Refresh-Parent-By-EXEC}}}
					} //end if
				} //end if
			} //end if
		} catch(err){
			console.log('NOTICE: BrowserUtils Failed to Close/Unload ModalBox: ' + err);
		} //end try catch
	} else { // if a popup lost parent reference or a manual opened link
		try {
			//console.log('Orphan Window ...');
			self.close(); // this may fail if window reference with parent is lost, needs try/catch
		} catch(err){}
	} //end if else
	//--
} //END FUNCTION


/**
 * Delayed Close a Modal / PopUp child browser window.
 * If a parent refresh is pending will execute it after.
 * This is the prefered way to close a child window (modal iFrame / PopUp) using a button or just executing this code in a child page.
 *
 * @method CloseDelayedModalPopUp
 * @static
 * @param 	{Integer} 	timeout 		The time delay in milliseconds
 */
this.CloseDelayedModalPopUp = function(timeout) {
	//--
	timeout = parseInt(timeout);
	if(timeout < 1) {
		timeout = 1; // min
	} //end if
	if(timeout > 300000) {
		timeout = 300000; // max
	} //end if
	//--
	setTimeout(function(){ SmartJS_BrowserUtils_PageAway = true; SmartJS_BrowserUtils.CloseModalPopUp(); }, timeout);
	//--
} //END FUNCTION


/*
 * Prevent Modal Cascading overload in browser.
 * If set will use a Popup instead opening a modal iFrame child from another modal iFrame.
 * This will prevent some browser limitations with nested modal iFrames.
 *
 * @private : internal development only
 * @method Control_ModalCascading
 * @static
 */
this.Control_ModalCascading = function() {
	//--
	try {
		//--
		if(typeof SmartJS_ModalBox != 'undefined') {
			if(self.name) {
				if(self.name == SmartJS_ModalBox.getName()) {
					SmartJS_BrowserUtils_Use_iFModalBox_Active = 0; // disable modal in modal, if so will force popup
				} //end if else
			} //end if
		} //end if
		//--
	} catch(err){
		console.log('NOTICE: BrowserUtils Failed to control ModalCascading / Self: ' + err);
	} //end try catch
	//--
	try {
		//--
		if(typeof parent.SmartJS_ModalBox != 'undefined') {
			if(parent.name) {
				if(parent.name == parent.SmartJS_ModalBox.getName()) {
					SmartJS_BrowserUtils_Use_iFModalBox_Active = 0; // disable modal in modal, if so will force popup
				} //end if else
			} //end if
		} //end if
		//--
	} catch(err){
		console.log('NOTICE: BrowserUtils Failed to control ModalCascading / Parent: ' + err);
	} //end try catch
	//--
} //END FUNCTION


/**
 * Focus a browser window by reference
 *
 * @method windowFocus
 * @static
 * @param 	{Object} 	wnd 		The window (reference) object
 */
this.windowFocus = function(wnd) {
	//--
	try {
		wnd.focus(); // focus the window (this may fail if window reference with parent is lost, needs try/catch)
	} catch(err){} // older browsers have some bugs, ex: IE8 on IETester
	//--
} //END FUNCTION


/**
 * Scroll down a browser window by reference.
 * It will focus the referenced browser window first.
 *
 * @method windwScrollDown
 * @static
 * @param 	{Object} 	wnd 		The window (reference) object
 * @param 	{Integer} 	offset 		The offset in pixels to scroll down
 */
this.windwScrollDown = function(wnd, offset) {
	//--
	try {
		wnd.scrollBy(0, parseInt(offset)); // if offset is -1 will go to end
	} catch(err){} // just in case
	//--
} //END FUNCTION


/**
 * Get the highest available Z-Index from a browser page taking in account all visible layers (div).
 * It will ignore non-visible layers for speed-up, as they have anyway no Z-Index assigned.
 *
 * @method getHighestZIndex
 * @static
 * @return 	{Integer} 							The highest available Z-Index from current page
 */
this.getHighestZIndex = function() {
	//-- inits
	var index_highest = 1;
	var index_current = 1;
	//--
	// var position;
	//--
	jQuery('div').each(function(){ // this will scan just divs to be efficient
		//position = jQuery(this).css("position");
		//if(position === "absolute" || position === "relative" || position === "fixed") {
		if((jQuery(this).css('display') == 'none') || (jQuery(this).css('visibility') == 'hidden') || (jQuery(this).attr('id') == 'SmartFramework___Debug_InfoBar')) {
			// skip
		} else {
			index_current = parseInt(jQuery(this).css("z-index"), 10);
			if((!isNaN(index_current)) && (index_current > 0)) {
				if(index_current > index_highest) {
					index_highest = index_current;
				} //end if
			} //end if
		} //end if else
		//} //end if
	});
	index_highest += 1;
	//console.log('Using Highest Z-INDEX: ' + index_highest);
	//--
	return index_highest;
	//--
} //END FUNCTION


/**
 * Display a page overlay in the current browser window
 * The ZIndex of the overlay is: 2147400000
 * All page elements that must be visible over this overlay must have zIndex in range: 2147401000 - 214740999
 *
 * @method Overlay_Show
 * @static
 * @param 	{String} 	text 			*Optional* a text to display in overlay as notification
 * @param 	{String} 	title 			*Optional* a title to display for overlay as notification
 * @param 	{String} 	class_name 		*Optional* a CSS class name for the notification (if any)
 * @return 	{Object} 					The overlay as HTML object
 */
this.Overlay_Show = function(text, title, class_name) {
	//--
	if((typeof text == 'undefined') || (text == null)) {
		text = '';
	} //end if
	if((typeof title == 'undefined') || (title == null)) {
		title = '';
	} //end if
	if((typeof class_name == 'undefined') || (class_name == null)) {
		class_name = 'gritter-neutral';
	} //end if
	//--
	var the_style = 'style="display: none; background-color:#777777; opacity: 0.85; position: fixed; top: -50px; left: -50px; width: 1px; height: 1px;"';
	if(typeof SmartJS_BrowserUIUtils != 'undefined') {
		if((typeof SmartJS_BrowserUIUtils.overlayCssClass != 'undefined') && (SmartJS_BrowserUIUtils.overlayCssClass != null) && (SmartJS_BrowserUIUtils.overlayCssClass != '')) {
			the_style = 'class="' + SmartJS_CoreUtils.escape_html(SmartJS_BrowserUIUtils.overlayCssClass) + '"'; // integrate with UI's Overlay
		} //end if
	} //end if
	//--
	var have_gritter = true;
	if(typeof jQuery.gritter == 'undefined') {
		have_gritter = false;
	} //end if
	//--
	var inner_html = '<img src="' + SmartJS_BrowserUtils_LoaderImg + '" alt="... loading ..." style="background:rgba(255,255,255,1)!important;opacity:1!important;">';
	if(have_gritter !== true) {
		inner_html = inner_html + '<div>' + '<h1>' + title + '</h1>' + '<div>' + text + '</div>' + '</div>';
	} //end if else
	jQuery('#smart_framework_overlay').remove(); // remove any instance if previous exist
	var overlay = jQuery('<div id="smart_framework_overlay" ' + the_style + '></div>').css({ 'z-index': 2147400000, 'position': 'fixed', 'top': '0px', 'left': '0px', 'width': '100%', 'height': '100%' }).hide().appendTo('body');
	jQuery('#smart_framework_overlay').html('<div style="width:100%; position:fixed; top:25px; left:0px;"><div align="center">' + inner_html + '</div></div>');
	//--
	try {
		overlay.fadeIn();
	} catch(err){
		//console.log('Overlay Failed to FadeIn: ' + err);
		overlay.show();
	} //end try catch
	//--
	if((text != '') || (title != '')) {
		if(have_gritter === true) {
			GrowlNotificationDoAdd(title, text, '', 500, false, class_name);
		} //end if else
	} //end if else
	//--
	return overlay;
	//--
} //END FUNCTION


/**
 * Clear all notifications from the page overlay in the current browser window
 *
 * @method Overlay_Clear
 * @static
 */
this.Overlay_Clear = function() {
	//--
	jQuery('#smart_framework_overlay').html('');
	//--
} //END FUNCTION


/**
 * Hide (destroy) the page overlay in the current browser window
 *
 * @method Overlay_Hide
 * @static
 */
this.Overlay_Hide = function() {
	//--
	var overlay = jQuery('#smart_framework_overlay');
	//--
	try {
		overlay.fadeOut();
	} catch(err){
		//console.log('Overlay Failed to FadeOut: ' + err);
		overlay.hide();
	} //end try catch
	//--
	overlay.css({ 'z-index': 1, 'position': 'fixed', 'top': '-50px', 'left': '-50px', 'width': 1, 'height': 1 }).remove(); // remove the instance
	//--
} //END FUNCTION


/**
 * Add a Growl notification in the current browser window (page)
 *
 * @method GrowlNotificationAdd
 * @static
 * @param 	{String} 	title 			a title for the notification (HTML) ; it can be empty string
 * @param 	{String} 	text 			the main notification message (HTML) ; this is mandatory
 * @param 	{String} 	image 			the URL link to a notification icon image (gif/png/jpg) or null
 * @param 	{Integer} 	time 			the notification display time in milliseconds
 * @param 	{Boolean} 	sticky 			*Optional* FALSE by default (will auto-close after the display time expire) ; TRUE to set sticky (require manual close, will not auto-close)
 * @param 	{String} 	class_name 		*Optional* a CSS class name for the notification or empty string to use default one
 * @param 	{Object} 	options 		*Optional* can map handlers for notification: before_open, after_open, before_close, after_close
 * @return 	{Object} 					The growl notification as HTML object
 */
this.GrowlNotificationAdd = function(title, text, image, time, sticky, class_name, options) {
	//--
	if((typeof text == 'undefined') || (text == null) || (text == '')) {
		text = ' '; // fix
	} //end if
	//--
	return GrowlNotificationDoAdd(title, text, image, time, sticky, class_name, options);
	//--
} //END FUNCTION


/**
 * Remove a Growl Notification by ID from the current browser window (page)
 *
 * @method GrowlNotificationRemove
 * @static
 * @param 	{String} 	id 		The HTML id of the Growl Notification to remove
 */
this.GrowlNotificationRemove = function(id) {
	//--
	GrowlNotificationDoRemove(id);
	//--
} //END FUNCTION


/**
 * Create a Maximize HTML Element wrapper arround a layer (div, iframe, ...).
 * This will add a maximize / un-maximize button and the handler to be able to maximize the element.
 * When the selected element will be maximized the page will be protected by an overlay.
 *
 * @method MaximizeElement
 * @static
 * @param 	{String} 	id 			The HTML id of the HTML element to bind to
 * @param 	{Boolean} 	maximized 	if (default) FALSE the element will not be maximized when bind to this ; if TRUE, element will be auto-maximized when bind to this function
 */
this.MaximizeElement = function(id, maximized) {
	//--
	var el = jQuery('#' + id);
	//--
	if((el.attr('data-fullscreen') == 'default') || (el.attr('data-fullscreen') == 'fullscreen')) {
		return; // avoid apply twice
	} //end if
	//--
	el.attr('data-fullscreen', 'default').append('<div style="position:absolute; top:-4px; left:-4px; width:20px; height: 20px; overflow:hidden; text-align:center; cursor:pointer; opacity:0.5;" title="Toggle Element Full Screen"><img src="' + SmartJS_CoreUtils.escape_html(SmartJS_BrowserUtils_FullScreen_Img) + '"></div>').css({ 'position': 'relative', 'border': '1px solid #DDDDDD', 'z-index': 1 }).click(function() {
		var the_el = jQuery(this);
		if(the_el.attr('data-fullscreen') == 'fullscreen') {
			_class.Overlay_Hide();
			the_el.attr('data-fullscreen', 'default');
			the_el.css({
				'position': 'relative',
				'top': 0,
				'left': 0,
				'width': Math.max(parseInt(the_el.attr('data-width')), 100),
				'height': Math.max(parseInt(the_el.attr('data-height')), 100),
				'z-index': 1
			});
		} else {
			_class.Overlay_Show();
			_class.Overlay_Clear();
			the_el.attr('data-width', '' + SmartJS_CoreUtils.escape_html('' + the_el.width()));
			the_el.attr('data-height', '' + SmartJS_CoreUtils.escape_html('' + the_el.height()));
			the_el.attr('data-fullscreen', 'fullscreen');
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


/**
 * Create a Browser Alert Dialog that will have just the OK button.
 * This will detect if the UIDialog is available and will prefer to use it if set.
 * Otherwise will try to detect if SimpleDislog is available and will use it if UIDialog is not available - fallback.
 * If none of the above are available (not available) will display a simple alert() - fallback.
 *
 * @method alert_Dialog
 * @static
 * @param 	{String} 	y_message 		The message to display (HTML)
 * @param 	{JS-Code} 	evcode 			*Optional* the JS Code to execute on closing this alert / Dialog by pressing the OK button (the alert / Dialog can be closed only if OK button is clicked)
 * @param 	{String} 	y_title 		*Optional* a title for this alert / Dialog (HTML)
 * @param 	{Integer} 	y_width 		*Optional* the width of the Dialog (will be used just if UIDialog / SimpleDialog are detected) ; if not set the default width will be used
 * @param 	{Integer} 	y_height 		*Optional* the height of the Dialog (will be used just if UIDialog / SimpleDialog are detected) ; if not set the default height will be used
 */
this.alert_Dialog = function(y_message, evcode, y_title, y_width, y_height) {
	//--
	if(typeof SmartJS_BrowserUIUtils != 'undefined') { // use UI Dialog (the best choice)
		//--
		SmartJS_BrowserUIUtils.DialogAlert(y_message, evcode, y_title, y_width, y_height);
		//--
	} else if(typeof SmartSimpleDialog != 'undefined') { // use simple dialog
		//--
		SmartSimpleDialog.Dialog_Alert(y_message, evcode, y_title, y_width, y_height);
		//--
	} else { // fallback to native browser alert
		//--
		y_title = jQuery('<div>' + y_title + '</div>').text(); // strip tags
		y_message = jQuery('<div>' + y_message + '</div>').text(); // strip tags
		alert(y_title + '\n' + y_message);
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


/**
 * Create a Browser Confirm Dialog that will have 2 buttons: OK / Cancel.
 * This will detect if the UIDialog is available and will prefer to use it if set.
 * Otherwise will try to detect if SimpleDislog is available and will use it if UIDialog is not available - fallback.
 * If none of the above are available (not available) will display a simple confirm() - fallback.
 *
 * @method confirm_Dialog
 * @static
 * @param 	{String} 	y_question 		The question / message to display (HTML)
 * @param 	{JS-Code} 	evcode 			*Optional* the JS Code to execute on closing this confirm / Dialog by pressing the OK button (the confirm / Dialog can be closed by either OK button clicked or Cancel button clicked ; this code will be not executed on Cancel button click / close)
 * @param 	{String} 	y_title 		*Optional* a title for this confirm / Dialog (HTML)
 * @param 	{Integer} 	y_width 		*Optional* the width of the Dialog (will be used just if UIDialog / SimpleDialog are detected) ; if not set the default width will be used
 * @param 	{Integer} 	y_height 		*Optional* the height of the Dialog (will be used just if UIDialog / SimpleDialog are detected) ; if not set the default height will be used
 */
this.confirm_Dialog = function(y_question, evcode, y_title, y_width, y_height) {
	//--
	if(typeof SmartJS_BrowserUIUtils != 'undefined') { // use UI Dialog (the best choice)
		//--
		SmartJS_BrowserUIUtils.DialogConfirm(y_question, evcode, y_title, y_width, y_height);
		//--
	} else if(typeof SmartSimpleDialog != 'undefined') { // use simple dialog
		//--
		SmartSimpleDialog.Dialog_Confirm(y_question, evcode, y_title, y_width, y_height);
		//--
	} else { // fallback to native browser confirm dialog
		//--
		y_title = jQuery('<div>' + y_title + '</div>').text(); // strip tags
		y_question = jQuery('<div>' + y_question + '</div>').text(); // strip tags
		var the_confirmation = confirm(y_title + '\n' + y_question);
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


/**
 * Confirm a Form Submit by a confirm / Dialog, with OK and Cancel buttons.
 * The form will be submitted just if OK button is clicked.
 * This function is using confirm_Dialog() and will detect and prefer in this order if UIDialog / SimpleDialog or just confirm() are available in Browser.
 *
 * @method confirmSubmitForm
 * @static
 * @param 	{String} 	y_confirm 		The question / message to display for confirmation (HTML)
 * @param 	{JS-Code} 	y_form 			The HTML Form Object to bind to
 * @param 	{String} 	y_target 		The window target to send this form to
 * @param 	{Integer} 	windowWidth 	*Optional* the width of the new Modal/PopUp child window if new window target is used ; if not set the default width will be used
 * @param 	{Integer} 	windowHeight 	*Optional* the height of the new Modal/PopUp child window if new window target is used ; if not set the default height will be used
 * @param 	{Enum} 		forcePopUp 		*Optional* if a new target window is required, 0 = default, use modal/iFrame if set by SmartJS_BrowserUtils_Use_iFModalBox_Active, 1 = force PopUp ; -1 force modal/iFrame
 * @param	{Enum} 		forceDims 		*Optional* if set to 1 will try force uwing the width/height set for the new modal/iFrame or PopUp
 */
this.confirmSubmitForm = function(y_confirm, y_form, y_target, windowWidth, windowHeight, forcePopUp, forceDims) {
	//--
	if((typeof y_form == 'undefined') || (y_form == null) || (y_form == '')) {
		//--
		alert('ERROR: Form Object is Undefined in confirmSubmitForm()');
		//--
	} else {
		//--
		SmartJS_BrowserUtils_CurrentForm = y_form; // export this var because we can't reference this object in eval
		//--
		var submit_code = 'SmartJS_BrowserUtils_CurrentForm.submit();'; // by default we do just submit
		//--
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
			submit_code = 'SmartJS_BrowserUtils.PopUpSendForm(SmartJS_BrowserUtils_CurrentForm, \'' + SmartJS_CoreUtils.escape_js(y_target) + '\', \'' + SmartJS_CoreUtils.escape_js(windowWidth) + '\', \'' + SmartJS_CoreUtils.escape_js(windowHeight) + '\', \'' + SmartJS_CoreUtils.escape_js(forcePopUp) + '\', \'' + SmartJS_CoreUtils.escape_js(forceDims) + '\');'; // in this situation we do both: popup/modal + submit
			//--
		} //end if
		//-- execute the above code only if confirmed
		_class.confirm_Dialog(y_confirm, submit_code);
		//--
	} //end if
	//--
} //END FUNCTION


/**
 * Raise a Modal/iFrame or PopUp child window with a new target to post a form within.
 * It will get the form URL and form method GET/POST directly from the objForm.
 * This function must be called by a form button onClick followed by 'return false;' not by classic submit to avoid fire the form send twice 1st before (in a _blank window) and 2nd after opening the child popup/modal.
 * This function if used in a button with 'return false;' will catch the form send behaviour and will trigger it just after the child modal/iFrame or PopUp child window (new) target is opened and available.
 *
 * @method PopUpSendForm
 * @static
 * @param 	{Object} 	objForm 		The HTML Form Object reference
 * @param 	{String} 	strTarget 		The child window target to post this form to
 * @param 	{Integer} 	windowWidth 	*Optional* the width of the new Modal/PopUp child window if new window target is used ; if not set the default width will be used
 * @param 	{Integer} 	windowHeight 	*Optional* the height of the new Modal/PopUp child window if new window target is used ; if not set the default height will be used
 * @param 	{Enum} 		forcePopUp 		*Optional* if a new target window is required, 0 = default, use modal/iFrame if set by SmartJS_BrowserUtils_Use_iFModalBox_Active, 1 = force PopUp ; -1 force modal/iFrame
 * @param	{Enum} 		forceDims 		*Optional* if set to 1 will try force uwing the width/height set for the new modal/iFrame or PopUp
 * @param 	{JS-Code} 	evcode 			*Optional* the JS Code to execute after submit this form
 */
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
	objForm.target = strTarget; // normal popUp use
	if(((SmartJS_BrowserUtils_Use_iFModalBox_Active) && (forcePopUp != 1)) || (forcePopUp == -1)) {
		if(typeof SmartJS_ModalBox != 'undefined') {
			objForm.target = SmartJS_ModalBox.getName(); // use smart modal box
		} //end if else
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


/**
 * Raise a Modal/iFrame or PopUp child window with a new target to open a URL link within.
 * This function can be called by a button, a link or other HTML elements at onClick (if 'a' element onClick is used must be followed by 'return false;' to avoid fire page refresh.
 *
 * @method PopUpLink
 * @static
 * @param 	{String} 	strUrl 			The URL link to be opened in a new browser child window target
 * @param 	{String} 	strTarget 		The child window target to post this form to
 * @param 	{Integer} 	windowWidth 	*Optional* the width of the new Modal/PopUp child window if new window target is used ; if not set the default width will be used
 * @param 	{Integer} 	windowHeight 	*Optional* the height of the new Modal/PopUp child window if new window target is used ; if not set the default height will be used
 * @param 	{Enum} 		forcePopUp 		*Optional* if a new target window is required, 0 = default, use modal/iFrame if set by SmartJS_BrowserUtils_Use_iFModalBox_Active, 1 = force PopUp ; -1 force modal/iFrame
 * @param	{Enum} 		forceDims 		*Optional* if set to 1 will try force uwing the width/height set for the new modal/iFrame or PopUp
 */
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


/**
 * Get a Cookie from Browser by Name and return it's Value
 *
 * @method getCookie
 * @static
 * @param 	{String} 	name 			The cookie Name
 * @return 	{String} 					The cookie Value
 */
this.getCookie = function(name) {
	//--
	var c;
	try {
		c = document.cookie.match(new RegExp('(^|;)\\s*' + name + '=([^;\\s]*)'));
	} catch(err){
		console.log('NOTICE: BrowserUtils Failed to getCookie: ' + err);
	} //end try catch
	//--
	if(c && c.length >= 3) {
		return '' + decodeURIComponent(c[2]);
	} else {
		return ''; // fix to avoid working with null !!
	} //end if
	//--
} //END FUNCTION


/**
 * Set a Cookie in Browser by Name and Value
 *
 * @method setCookie
 * @static
 * @param 	{String} 	name 			The cookie Name
 * @param 	{String} 	value 			The cookie Value
 * @param	{Numeric} 	days 			*Optional* The cookie expiration in days (default expire by session)
 * @param 	{String} 	path 			*Optional* The cookie path (default is /)
 * @param 	{String} 	domain 			*Optional* The cookie domain (default is NULL)
 * @param 	{Boolean} 	secure 			*Optional* Force Cookie Secure Mode (default is FALSE)
 */
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
	try {
		document.cookie = name + '=' + SmartJS_CoreUtils.escape_url(value) + (days ? ('; expires=' + d.toGMTString()) : '') + '; path=' + (path || '/') + (domain ? ('; domain=' + domain) : '') + (secure ? '; secure' : '');
	} catch(err){
		console.log('NOTICE: BrowserUtils Failed to setCookie: ' + err);
	} //end try catch
	//--
} //END FUNCTION


/**
 * Delete a Cookie from Browser by Name
 *
 * @method deleteCookie
 * @static
 * @param 	{String} 	name 			The cookie Name
 * @param 	{String} 	path 			*Optional* The cookie path (default is /)
 * @param 	{String} 	domain 			*Optional* The cookie domain (default is NULL)
 */
this.deleteCookie = function(name, path, domain) {
	//--
	_class.setCookie(name, '', -1, path, domain); // sets expiry to now - 1 day
	//--
} //END FUNCTION


/*
 * Resize iFrames Dinamically on Height and Optional on Width
 *
 * @private : internal development only
 * @method resize_iFrame
 * @static
 * @param 	{String} 	f 				The reference iFrame
 * @param 	{Boolean} 	w 				*Optional* if TRUE will resize also on Width
 */
this.resize_iFrame = function(f, w) {
	//--
	f.style.height = '1px';
	f.style.height = f.contentWindow.document.body.scrollHeight + 'px';
	//--
	if(w === true) {
		f.style.width = '1px';
		f.style.width = f.contentWindow.document.body.scrollWidth + 'px';
	} //end if
	//--
} //END FUNCTION


/*
 * Do limit the text on TextArea and update the attached CounterField
 * This should be bind on the following events of TextArea: change, click, blur, keydown, keyup
 *
 * @private : internal use only
 * @method textArea_Limit
 * @static
 * @param 	{String} 	y_field 		The TextArea field name
 * @param 	{String} 	y_countfield 	The text counter field name
 * @param 	{Integer} 	y_maxlimit 		The max limit of characters to accept in the TextArea
 */
this.textArea_Limit = function(y_field, y_countfield, y_maxlimit) {
	//--
	y_maxlimit = parseInt(y_maxlimit);
	if((y_maxlimit < 1) || isNaN(y_maxlimit)) {
		console.log('TextArea Limit :: Invalid Text Limit, will reset to 1');
		y_maxlimit = 1;
	} //end if
	//--
	var field = jQuery('#' + y_field);
	if(field.val().length > y_maxlimit) { // if too long then trim it!
		field.val(field.val().substring(0, y_maxlimit));
	} //end if
	//--
	jQuery('#' + y_countfield).val(y_maxlimit - field.val().length); // update the counter
	//--
} //END FUNCTION


/**
 * Force a text limit on a TextArea (will also attach a CounterField)
 *
 * @method textArea_addLimit
 * @static
 * @param 	{String} 	y_field 		The TextArea field name
 * @param 	{Integer} 	y_maxlimit 		The max limit of characters to accept in the TextArea
 */
this.textArea_addLimit = function(y_field, y_maxlimit) {
	//--
	y_maxlimit = parseInt(y_maxlimit);
	if((y_maxlimit < 1) || isNaN(y_maxlimit)) {
		console.log('TextArea Add Limit :: Invalid Text Limit, will reset to 1');
		y_maxlimit = 1;
	} //end if
	//--
	y_countfield = '__LimitedTextArea__' + y_field + '__Fld_COUNTER__';
	//--
	jQuery('#' + y_field).wrap(
		'<div style="position:relative!important;"></div>'
	).after(
		'<input title="TextArea Limit: ' + SmartJS_CoreUtils.escape_html(y_maxlimit) + '" type="text" readonly disabled id="' + SmartJS_CoreUtils.escape_html(y_countfield) + '" size="6" maxlength="5" value="' + SmartJS_CoreUtils.escape_html(y_maxlimit) + '" style="cursor:help!important; padding:0px!important; font-size:10px!important; text-align:center!important; position:absolute!important; right:3px!important; bottom:3px!important; opacity:0.25;">'
	).change(function(){
		_class.textArea_Limit(y_field, y_countfield, y_maxlimit);
	}).click(function(){
		_class.textArea_Limit(y_field, y_countfield, y_maxlimit);
	}).blur(function(){
		_class.textArea_Limit(y_field, y_countfield, y_maxlimit);
	}).keydown(function(){
		_class.textArea_Limit(y_field, y_countfield, y_maxlimit);
	}).keyup(function(){
		_class.textArea_Limit(y_field, y_countfield, y_maxlimit);
	}).attr('maxlength', y_maxlimit);
	//--
	jQuery('#' + y_countfield).hover(function(){
		jQuery(this).css('opacity', '1');
	}, function(){
		jQuery(this).css('opacity', '0.25');
	});
	//--
} //END FUNCTION


/**
 * Catch TAB Key in a TextArea or other compatible field
 * Example: onKeyDown = SmartJS_BrowserUtils.catch_TABKey(event);
 *
 * @method catch_TABKey
 * @static
 * @param 	{Event} 	evt 			The EVENT Object Reference
 */
this.catch_TABKey = function(evt) {
	//-- Example: <textarea id="txt" onKeyDown="SmartJS_BrowserUtils.catch_TABKey(event);">
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


/**
 * Check or Uncheck all checkboxes in a form element
 *
 * @method checkAll_CkBoxes
 * @static
 * @param 	{String} 	y_form_name 			The form name (if empty string will operate on all page otherwise just inside the form)
 * @param 	{String} 	y_element_id 			The checkboxes element ID
 * @param 	{Boolean} 	y_element_checked 		If TRUE will do check ; If FALSE will do uncheck ; otherwise will just inverse check
 */
this.checkAll_CkBoxes = function(y_form_name, y_element_id, y_element_checked) {
	//--
	var i;
	//--
	for(i=0; i<document.forms[y_form_name].elements.length; i++) {
		//--
		if(document.forms[y_form_name].elements[i].type == "checkbox") {
			//--
			if((typeof y_element_id == 'undefined') || (y_element_id === '')) { // default
				//--
				if((y_element_checked === true) || (y_element_checked === false)) {
					document.forms[y_form_name].elements[i].checked = y_element_checked;
				} else {
					document.forms[y_form_name].elements[i].checked = !document.forms[y_form_name].elements[i].checked;
				} //end if else
				//--
			} else {
				//--
				if(y_element_id == document.forms[y_form_name].elements[i].id) {
					if((y_element_checked === true) || (y_element_checked === false)) {
						document.forms[y_form_name].elements[i].checked = y_element_checked;
					} else {
						document.forms[y_form_name].elements[i].checked = !document.forms[y_form_name].elements[i].checked;
					} //end if else
				} //end if
				//--
			} //end if
			//--
		} //end if
		//--
	} //end for
	//--
} //END FUNCTION


/**
 * Clone a HTML Element
 *
 * @method CloneElement
 * @static
 * @param 	{String} 	elem 					The element ID to be cloned
 * @param 	{String} 	destination 			The destination container ID
 * @param 	{Enum} 		elType 					The type of the element to be cloned: text-input ; text-area ; file-input ; html-element
 * @param 	{Integer} 	maxLimit 				The max limit number of cloned elements
 */
this.CloneElement = function(elem, destination, elType, maxLimit) {
	//--
	maxLimit = parseInt(maxLimit);
	if(isNaN(maxLimit) || (maxLimit < 0) || (maxLimit > 255)) {
		maxLimit = 255; // hard code limit
	} //end if
	//-- init
	var control_num = parseInt(jQuery('body').find('[id^=' + 'clone_control__' + SmartJS_CoreUtils.escape_js(elem) + ']').length);
	if((control_num <= 0) || isNaN(control_num)) {
		jQuery('#' + elem).before('<img id="' + 'clone_control__' + SmartJS_CoreUtils.escape_html(elem) + '" alt="Add New" title="Add New" src="' + SmartJS_CoreUtils.escape_html(SmartJS_BrowserUtils_Cloner_Img_Add) + '" style="cursor:pointer; vertical-align:middle;" onClick="SmartJS_BrowserUtils.CloneElement(\'' + SmartJS_CoreUtils.escape_js(elem) + '\', \'' + SmartJS_CoreUtils.escape_js(destination) + '\', \'' + SmartJS_CoreUtils.escape_js(elType) + '\', ' + parseInt(maxLimit) + ');' + '">&nbsp;&nbsp;</span>');
		return;
	} //end if
	//-- do clone
	var cloned_num = parseInt(jQuery('body').find('[id^=' + 'clone_of__' + SmartJS_CoreUtils.escape_js(elem) + '_' + ']').length);
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
	var clone_data = jQuery('#' + elem).clone().attr('id', 'clone_of__' + SmartJS_CoreUtils.escape_js(elem) + '_' + SmartJS_CoreUtils.escape_js(uuID));
	//--
	jQuery('#' + destination).append('<span id="' + 'clone_container__' + SmartJS_CoreUtils.escape_html(elem) + '_' + SmartJS_CoreUtils.escape_html(uuID) + '"><br><img alt="Remove" title="Remove" src="' + SmartJS_CoreUtils.escape_html(SmartJS_BrowserUtils_Cloner_Img_Remove) + '" style="cursor:pointer; vertical-align:middle;" onClick="jQuery(this).parent().remove();">&nbsp;&nbsp;</span>');
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


/*
 * Background Send (post) a Form (it does not catch the result, just send it to ensure updates in some cases ...).
 * This should be used for very particular situations by example posting a form to post another form before !!
 * It will NOT work with forms that must upload because will do just serialize() on that.
 *
 * @private : internal development only
 * @method Background_Send_a_Form
 * @static
 * @param 	{String} 	other_form_id 			The element ID of the form to be sent / posted
 * @param 	{String} 	evcode 					*Optional* the JS Code to execute on SUCCESS answer
 */
this.Background_Send_a_Form = function(other_form_id, evcode) {
	//--
	var ajax = _class.Ajax_XHR_GetByForm(other_form_id, '', 'text'); // since the answer is not evaluated because can vary, will use text
	if(ajax === null) {
		alert('ERROR: Submit Form by Ajax / Null Object !');
		return;
	} //end if
	//--
	_class.Overlay_Show();
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
		_class.Overlay_Hide();
		//--
	}).fail(function(msg) {
		//--
		_class.alert_Dialog('ERROR (1): Invalid Background Form Update !' + '\n' + 'Status Code: ' + msg.status + '\n' + msg.responseText, '', 'ERROR', 750, 425);
		//--
		_class.Overlay_Hide();
		//--
	});
	//--
} //END FUNCTION


/**
 * Submit a Form by Ajax via POST and Handle the Answer
 * It supports simple forms or complex forms with multipart/form-data and file attachments.
 * This function expects a json answer with the following structure: see more in framework PHP lib SmartComponents::post_answer_by_ajax()
 * 		{ // Json Structure for Answer
 * 			'completed': 	'DONE',
 * 			'status': 		'OK|ERROR',
 * 			'action': 		'Notification Button Text: Ok/Cancel',
 * 			'title': 		'Notification Title',
 * 			'message': 		'Notification Message HTML Content',
 * 			'js_evcode': 	'If non-empty, the JS Code to execute on either SUCCESS or ERROR (before redirect or Div Replace)'
 * 			'redirect': 	'If non-empty, a redirect URL on either SUCCESS or ERROR ; on SUCCESS if message is Empty will redirect without confirmation: Growl / Dialog',
 * 			'replace_div': 	'If non-empty, an ID for a div to be replaced with content from [replace_html] on Success',
 * 			'replace_html': 'If non-empty, a HTML code to populate / replace the current Content for the [replace_div] on Success'
 * 		} // #END Json Structure
 *
 * @method Submit_Form_By_Ajax
 * @static
 * @param 	{String} 	the_form_id 			The form ID
 * @param 	{String} 	url 					The form destination URL
 * @param 	{Yes/No} 	growl 					*Optional* If 'yes' will use the Growl notifications otherwise (default: if 'no') will use Dialog notifications
 * @param 	{String} 	evcode 					*Optional* the JS Code to execute on SUCCESS answer (before anything else)
 */
this.Submit_Form_By_Ajax = function(the_form_id, url, growl, evcode) {
	//--
	if(growl !== 'no') {
		if(typeof jQuery.gritter != 'undefined') {
			growl = 'yes';
		} else {
			growl = 'no';
		} //end if
	} //end if
	//--
	var ajax = _class.Ajax_XHR_GetByForm(the_form_id, url, 'json');
	if(ajax === null) {
		alert('ERROR: Submit Form by Ajax / Null Object !');
		return;
	} //end if
	//--
	_class.Overlay_Show();
	//--
	ajax.done(function(msg) { // {{{JQUERY-AJAX}}}
		//--
		_class.Overlay_Clear();
		//--
		var doReplaceDiv = 'no';
		//--
		if(msg != null) {
			//--
			if((msg.hasOwnProperty('completed')) && (msg.completed == 'DONE') && (msg.hasOwnProperty('status')) && ((msg.status == 'OK') || (msg.status == 'ERROR')) && (msg.hasOwnProperty('action')) && (msg.action != null) && (msg.hasOwnProperty('title')) && (msg.title != null) && (msg.hasOwnProperty('message')) && (msg.message != null) && (msg.hasOwnProperty('js_evcode')) && (msg.hasOwnProperty('redirect')) && (msg.hasOwnProperty('replace_div')) && (msg.hasOwnProperty('replace_html'))) {
				//--
				if(msg.status == 'OK') { // OK
					//--
					if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
						try {
							eval('(function(){ ' + evcode + ' })();'); // sandbox
						} catch(err) {
							alert('ERROR: JS-Eval Error on Submit Form By Ajax (1)' + '\nDetails: ' + err);
						} //end try catch
					} //end if
					//--
					if((msg.js_evcode != null) && (msg.js_evcode != '')) {
						try {
							eval('(function(){ ' + msg.js_evcode + ' })();'); // sandbox
						} catch(err) {
							alert('ERROR: JS-Eval Error on Submit Form By Ajax (2)' + '\nDetails: ' + err);
						} //end try catch
					} //end if
					//--
					if((msg.replace_div != null) && (msg.replace_div != '') && (msg.replace_html != null) && (msg.replace_html != '')) {
						doReplaceDiv = 'yes';
					} //end if
					//--
					if((msg.redirect != null) && (msg.redirect != '') && (msg.message == '')) {
						_class.RedirectDelayedToURL(msg.redirect, 250);
					} else {
						if(doReplaceDiv == 'yes') {
							jQuery('#'+msg.replace_div).html('' + msg.replace_html);
						} //end if
						if((doReplaceDiv != 'yes') || (msg.message != '')) {
							if(growl === 'yes') {
								Message_AjaxForm_Notification('' + SmartJS_CoreUtils.escape_html(msg.title), '<img src="' + SmartJS_BrowserUtils_ImgOK + '" align="right">' + msg.message, msg.redirect, 'yes', 'gritter-green', 2000);
							} else {
								Message_AjaxForm_Notification('' + SmartJS_CoreUtils.escape_html(msg.action) + ' / ' + SmartJS_CoreUtils.escape_html(msg.title), '<img src="' + SmartJS_BrowserUtils_ImgOK + '" align="right">' + msg.message, msg.redirect, 'no', '', 2000);
							} //end if else
						} //end if else
						if(growl !== 'yes') {
							_class.Overlay_Hide();
						} //end if
					} //end if
					//--
				} else { // ERROR
					//--
					if((msg.js_evcode != null) && (msg.js_evcode != '')) {
						try {
							eval('(function(){ ' + msg.js_evcode + ' })();'); // sandbox
						} catch(err) {
							alert('ERROR: JS-Eval Error on Submit Form By Ajax (3)' + '\nDetails: ' + err);
						} //end try catch
					} //end if
					//--
					if(growl === 'yes') {
						Message_AjaxForm_Notification('* ' + SmartJS_CoreUtils.escape_html(msg.title), '<img src="' + SmartJS_BrowserUtils_ImgWarn + '" align="right">' + msg.message, msg.redirect, 'yes', 'gritter-red', 3000);
					} else {
						Message_AjaxForm_Notification('* ' + SmartJS_CoreUtils.escape_html(msg.action) + ' / ' + SmartJS_CoreUtils.escape_html(msg.title), '<img src="' + SmartJS_BrowserUtils_ImgWarn + '" align="right">' + msg.message, msg.redirect, 'no', '', 3000);
					} //end if else
					if(growl !== 'yes') {
						_class.Overlay_Hide();
					} //end if
					//--
				} //end if else
				//--
			} else {
				//--
				alert('ERROR (2): Invalid DataObject Format !'); // this must be alert because errors may prevent dialog
				//console.log(msg); // dump object
				_class.Overlay_Hide();
				//--
			} //end if else
			//--
		} else {
			//--
			alert('ERROR (3): DataObject is NULL !'); // this must be alert because errors may prevent dialog
			_class.Overlay_Hide();
			//--
		} //end if else
		//--
	}).fail(function(msg) {
		//--
		_class.alert_Dialog('ERROR (1): Invalid Server Response !' + '\n' + 'Status Code: ' + msg.status + '\n' + msg.responseText, '', 'ERROR', 750, 425);
		_class.Overlay_Hide();
		//--
	});
	//--
} //END FUNCTION


/**
 * Create an Ajax XHR Request (POST) by Form
 * It supports simple forms or complex forms with multipart/form-data and file attachments.
 *
 * @method Ajax_XHR_GetByForm
 * @static
 * @param 	{String} 	the_form_id 			The element ID of the form to be create the Ajax XHR Request for
 * @param 	{String} 	url 					The URL to send form to via POST
 * @param 	{Enum} 		data_type 				The type of Data served back by the Request: json | html | text
 * @return 	{Object} 							The Ajax XHR Request Object ; The following methods must be bind to this object and redefined:
 * 		.done: 		function(msg) {}
 * 		.fail: 		function(msg) {}
 * 		.always: 	function(msg) {}
 */
this.Ajax_XHR_GetByForm = function(the_form_id, url, data_type) {
	//--
	var ajax = null;
	var data = '';
	//--
	if((typeof url == 'undefined') || (url == null) || (url == '')) {
		url = jQuery('#' + the_form_id).attr('action'); // try to get form action if URL is empty
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
		if((jQuery('#' + the_form_id).attr('method') == 'post') && (jQuery('#' + the_form_id).attr('enctype') == 'multipart/form-data')) {
			var have_files = jQuery('#' + the_form_id).find('input:file');
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
			data = jQuery('#' + the_form_id).serialize(); // no files detected use serialize
			ajax = _class.Ajax_XHR_Request_From_URL(url, 'POST', data_type, data);
			//console.log('Form.XHR.Ajax: using Serialized Form Data ... ' + the_form_id);
			//--
		} else {
			//--
			try {
				var theFormObj = document.getElementById(the_form_id);
			} catch(err) {
				alert('ERROR: Ajax_XHR_GetByForm / Invalid FormID !');
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
				data = jQuery('#' + the_form_id).serialize(); // no files detected use serialize
				ajax = _class.Ajax_XHR_Request_From_URL(url, 'POST', data_type, data);
				//console.log('Form.XHR.Ajax: using MultiPart Form Data (FallBack without File Attachments) ... ' + the_form_id);
			} //end try catch
			//--
		} //end if else
		//--
	} //end if
	//--
	return ajax;
	/* the below functions must be assigned later to avoid execution here {{{SYNC-JQUERY-AJAX-EVENTS}}}
	.done: function(msg) {}, // instead of .success() (which is deprecated or removed from newest jQuery)
	.fail: function(msg) {}, // instead of .error() (which is deprecated or removed from newest jQuery)
	.always: function(msg) {} // *optional* instead of .complete() (which is deprecated or removed from newest jQuery)
	*/
	//--
} //END FUNCTION


/*
 * Create an Ajax XHR Request (POST) using multipart/form-data type to bse used with file attachments.
 * Instead using this directly is better to use:
 * 		SmartJS_BrowserUtils.Ajax_XHR_GetByForm(); 		// basic, will detect the form type if must use multipart/form-data + attachments (if any)
 * or even much better use this:
 * 		SmartJS_BrowserUtils.Submit_Form_By_Ajax(); 	// advanced, will handle the XHR form request and the answer
 *
 * @private : internal development only
 * @method Ajax_XHR_PostMultiPart_To_URL
 * @static
 * @param 	{String} 	y_url 					The URL to send form to via POST
 * @param 	{Enum} 		y_data_type 			The type of Data served back by the Request: json | html | text
 * @param 	{String} 	y_data_formData 		The *special* serialized form data using: new FormData(document.getElementById(the_form_id)) to support attachments
 * @return 	{Object} 							The Ajax XHR Request Object ; The following methods must be bind to this object and redefined:
 * 		.done: 		function(msg) {}
 * 		.fail: 		function(msg) {}
 * 		.always: 	function(msg) {}
 */
this.Ajax_XHR_PostMultiPart_To_URL = function(y_url, y_data_type, y_data_formData) {
	//--
	if((typeof y_url == 'undefined') || (y_url == null) || (y_url == '')) {
		y_url = '#';
		console.log('Empty URL for Ajax_XHR_PostMultiPart_To_URL ...');
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
	return jQuery.ajax({
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
		/* the below functions must be assigned later to avoid execution here {{{SYNC-JQUERY-AJAX-EVENTS}}}
		.done: function(msg) {}, // instead of .success() (which is deprecated or removed from newest jQuery)
		.fail: function(msg) {}, // instead of .error() (which is deprecated or removed from newest jQuery)
		.always: function(msg) {} // *optional* instead of .complete() (which is deprecated or removed from newest jQuery)
		*/
	});
	//--
} //END FUNCTION


/**
 * Create a general purpose Ajax XHR Request (GET/POST) with Optional support for Authentication and Extra Headers
 * It is NOT intended to be used with HTML forms that may contain multipart/form-data and file attachments or not.
 * For creating an Ajax XHR Request to be used with HTML Forms use:
 * 		SmartJS_BrowserUtils.Ajax_XHR_GetByForm(); 		// basic, will detect the form type if must use multipart/form-data + attachments (if any)
 * or even much better use this:
 * 		SmartJS_BrowserUtils.Submit_Form_By_Ajax(); 	// advanced, will handle the XHR form request and the answer
 *
 * @method Ajax_XHR_Request_From_URL
 * @static
 * @param 	{String} 	y_url 						The URL to send the Request to
 * @param 	{Enum} 		y_method 					The Request Method: GET / POST
 * @param 	{Enum} 		y_data_type 				The type of Data served back by the Request: json | html | text
 * @param 	{Mixed} 	y_data_arr_or_serialized 	The Data to be sent: a serialized string via serialize() such as: '&var1=value1&var2=value2' or an associative array as: { var1: "value1", var2: "value2" }
 * @param 	{String} 	y_AuthUser 					*Optional* The Authentication UserName (if custom Authentication need to be used)
 * @param 	{String} 	y_AuthPass 					*Optional* The Authentication Password (if custom Authentication need to be used)
 * @param 	{String} 	y_Headers 					*Optional* Extra Headers to be sent with this Request
 * @return 	{Object} 								The Ajax XHR Request Object ; The following methods must be bind to this object and redefined:
 * 		.done: 		function(msg) {}
 * 		.fail: 		function(msg) {}
 * 		.always: 	function(msg) {}
 */
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
	return jQuery.ajax({
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
	/* [Sample Implementation:] {{{SYNC-JQUERY-AJAX-EVENTS}}}
	var ajax = SmartJS_BrowserUtils.Ajax_XHR_Request_From_URL(...);
	// {{{JQUERY-AJAX}}} :: the below functions: done() / fail() / always() must be assigned on execution because they are actually executing the ajax request and the Ajax_XHR_Request_From_URL() just creates the request object !
	ajax.done(function(msg) { // instead of .success() (which is deprecated or removed from newest jQuery)
		// code for done
	}).fail(function(msg) { // instead of .error() (which is deprecated or removed from newest jQuery)
		// code for fail
	}).always(function(msg) { // *optional* instead of .complete() (which is deprecated or removed from newest jQuery)
		// code for always
	});
	*/
	//--
} //END FUNCTION


/**
 * Loads the contents for a Div (or other compatible) HTML Element(s) by Ajax using a GET / POST Ajax Request
 * This is intended to simplify populating a Div (or other compatible) HTML Element(s) with content(s) by Ajax Requests.
 *
 * @method Load_Div_Content_By_Ajax
 * @static
 * @param 	{String} 	y_div 						The ID of the Div (or other compatible) HTML Element(s) to bind to
 * @param 	{String} 	y_img_loader 				If non-empty, a pre-loader image that will be displayed while loading ...
 * @param 	{String} 	y_url 						The URL to send the Request to
 * @param 	{Enum} 		y_method 					The Request Method: GET / POST
 * @param 	{Enum} 		y_data_type 				The type of Data served back by the Request: html | text
 * @param 	{Mixed} 	y_data_arr_or_serialized 	The Data to be sent: a serialized string via serialize() such as: '&var1=value1&var2=value2' or an associative array as: { var1: "value1", var2: "value2" }
 */
this.Load_Div_Content_By_Ajax = function(y_div, y_img_loader, y_url, y_method, y_data_type, y_data_arr_or_serialized) {
	//--
	if((typeof y_div == 'undefined') || (y_div == null) || (y_div == '')) {
		_class.alert_Dialog('ERROR (1): Invalid DivID in Ajax LoadDivContent From URL', '', 'ERROR', 750, 425);
		return -1;
	} //end if
	//--
	if((typeof y_img_loader != 'undefined') && (y_img_loader != null) && (y_img_loader != '')) {
		if(jQuery('#' + y_div + '__Load_Div_Content_By_Ajax').length == 0) {
			jQuery('#' + y_div).prepend('<span id="' + y_div + '__Load_Div_Content_By_Ajax' + '"><img src="' + y_img_loader + '" title="Loading ..." alt="Loading ..."></span><br>');
		} //end if
	} //end if
	//--
	var ajax = _class.Ajax_XHR_Request_From_URL(y_url, y_method, y_data_type, y_data_arr_or_serialized);
	//--
	ajax.done(function(msg) { // {{{JQUERY-AJAX}}}
		jQuery('#' + y_div).html(msg); // this will also evaluate the js scripts
	}).fail(function(msg) {
		if(SmartJS_BrowserUtils_NotifyLoadError === false) {
			console.log('ERROR (2): Invalid Server Response for LoadDivContent !' + '\n' + 'Status Code: ' + msg.status + '\n' + msg.responseText);
		} else {
			_class.alert_Dialog('ERROR (2): Invalid Server Response for LoadDivContent !' + '\n' + 'Status Code: ' + msg.status + '\n' + msg.responseText, '', 'ERROR', 750, 425);
		} //end if else
		jQuery('#' + y_div).html(''); // clear
	});
	//--
} //END FUNCTION


/**
 * Add (Create) a Bookmark to Favorites in Browser
 * This may not be supported by all browsers thus it have a TRY/CATCH fallback to avoid errors.
 *
 * @method bookmark_url
 * @static
 * @param 	{String} 	title 						The Title of the Bookmark
 * @param 	{String} 	url 						The URL of the Bookmark
 */
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


/*
 * Set Refresh on PopUp Parent
 *
 * @private : non public
 * @method Refresh_SET_Popup_Parent
 * @static
 * @param 	{String} 	yURL 						The URL to be used for Refresh
 */
var Refresh_SET_Popup_Parent = function(yURL) {
	//--
	if((typeof yURL == 'undefined') || (yURL == 'undefined') || (yURL == null)) {
		yURL = '';
	} //end if
	//--
	if(_class.WindowIsPopup() === true) { // when called from PopUp
		//--
		//console.log('Executing: Refresh_SET_Popup_Parent ...');
		//--
		try {
			//-- {{{SYNC-TRANSFER-MODAL-POPUP-REFRESH}}}
			if(window.opener.SmartJS_ModalBox) {
				if(window.opener.SmartJS_ModalBox.getStatus() === 'visible') {
					window.opener.SmartJS_ModalBox.setRefreshParent(1, ''+yURL);
					return;
				} //end if
			} //end if
			//--
			window.opener.SmartJS_BrowserUtils_RefreshState = 1;
			window.opener.SmartJS_BrowserUtils_RefreshURL = '' + yURL;
			//--
		} catch(err){
			console.log('NOTICE: BrowserUtils Failed to Set Refresh on PopUp Parent: ' + err);
		} //end try catch
		//--
	} //end if
	//--
} //END FUNCTION


/*
 * Exec Refresh PopUp Parent
 *
 * @private : non public
 * @method Refresh_EXEC_Popup_Parent
 * @static
 */
var Refresh_EXEC_Popup_Parent = function() {
	//--
	if(_class.WindowIsPopup() === true) { // when called from PopUp
		//--
		try {
			//--
			if((window.opener.SmartJS_BrowserUtils_PopUpWindow) && (window.opener.SmartJS_BrowserUtils_PopUpWindow === self)) {
				//console.log('This will be handled by initPopUp timer, no needed to use directly');
				return;
			} //end if
			//--
			if(window.opener.SmartJS_BrowserUtils_RefreshState) {
				//--
				//console.log('Executing: Refresh_EXEC_Popup_Parent ...');
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
		} catch(err){
			console.log('NOTICE: BrowserUtils Failed to Exec Refresh on PopUp Parent: ' + err);
		} //end try catch
		//--
	} //end if
	//--
} //END FUNCTION


/*
 * Exec Refresh on Self
 *
 * @private : non public
 * @method RefreshEXEC_Self
 * @static
 */
var RefreshEXEC_Self = function() {
	//--
	try {
		if(self.SmartJS_BrowserUtils_RefreshState) {
			//--
			//console.log('Executing: RefreshEXEC_Self ...');
			//--
			if((typeof SmartJS_BrowserUtils_RefreshURL == 'undefined') || (SmartJS_BrowserUtils_RefreshURL == null) || (SmartJS_BrowserUtils_RefreshURL == '')) {
				//self . location . reload(false); // false is to reload from cache
				self.location = self.location; // FIX: avoid reload to resend POST vars !!
			} else {
				self.location = '' + SmartJS_BrowserUtils_RefreshURL;
			} //end if else
			//--
			SmartJS_BrowserUtils_RefreshState = 0;
			SmartJS_BrowserUtils_RefreshURL = '';
			//--
		} //end if
	} catch(err){
		console.log('NOTICE: BrowserUtils Failed to Set Refresh on Self: ' + err);
	} //end try catch
	//--
} //END FUNCTION


/*
 * Create a Message (Dialog or Growl) Notification
 *
 * @private : non public
 * @method Message_AjaxForm_Notification
 * @static
 * @param 	{String} 	ytitle 						The Title
 * @param 	{String} 	ymessage 					The Message (HTML code)
 * @param 	{String} 	yredirect 					*Optional* The URL to redirect
 * @param 	{Yes/No} 	growl 						*Optional* If 'yes' will use the Growl notifications otherwise (default: if 'no') will use Dialog notification
 * @param 	{Enum} 		class_growl 				*Optional* If Growl is used, a CSS class for Growl is required: gritter-neutral ; gritter-dark ; gritter-light ... see jquery.gritter.css (or create a custom css class for Growl)
 * @param 	{Integer} 	timeout 					*Optional* If Growl is used, the Growl timeout in milliseconds
 */
var Message_AjaxForm_Notification = function(ytitle, ymessage, yredirect, growl, class_growl, timeout) {
	//--
	if(growl === 'yes') {
		//--
		var redirectAfterClose = null;
		if((typeof yredirect != 'undefined') && (yredirect != null) && (yredirect != '')) {
			//_class.RedirectDelayedToURL(yredirect, (timeout + 500));
			redirectAfterClose = function(){
				SmartJS_BrowserUtils.RedirectDelayedToURL(''+yredirect, 500);
			};
		} else {
			//setTimeout(function(){ SmartJS_BrowserUtils.Overlay_Hide(); }, (timeout + 500));
		} //end if
		//--
		var growlOptions = {
			before_close: redirectAfterClose,
			after_close: function(){
				SmartJS_BrowserUtils.Overlay_Hide();
			}
		};
		//--
		GrowlNotificationDoAdd(ytitle, ymessage, '', timeout, false, class_growl, growlOptions);
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


/*
 * Create a Message Growl Notification
 *
 * @private : non public
 * @method GrowlNotificationDoAdd
 * @static
 * @param 	{String} 		title 						The Title
 * @param 	{String} 		text 						The Message (HTML code)
 * @param 	{String} 		image 						If non-empty, an image will be displayed inside Growl
 * @param 	{Integer} 		time 						The Growl timeout in milliseconds
 * @param 	{Boolean} 		sticky 						If TRUE the Growl will be sticky (ignore time, will be closed just on user explicit close), otherwise if FALSE (default) is to be non-sticky and close on time-out
 * @param 	{Enum} 			class_name 					*Optional* If Growl is used, a CSS class for Growl is required: gritter-neutral ; gritter-dark ; gritter-light ... see jquery.gritter.css (or create a custom css class for Growl)
 * @param 	{Array-Obj} 	options 					*Optional* Extra Growl Properties:
 * 		{ // example of extra Options
 * 			before_open: 	function(){},
 * 			after_open: 	function(){},
 * 			before_close: 	function(){},
 * 			after_close: 	function(){}
 * 		}
 * @return 	{Object} 									The Growl object
 */
var GrowlNotificationDoAdd = function(title, text, image, time, sticky, class_name, options) {
	//--
	growl_before_open = null;
	growl_after_open = null;
	growl_before_close = null;
	growl_after_close = null;
	if(typeof options != 'undefined') {
		if(options.hasOwnProperty('before_open')) {
			growl_before_open = options.before_open;
		} //end if
		if(options.hasOwnProperty('after_open')) {
			growl_after_open = options.after_open;
		} //end if
		if(options.hasOwnProperty('before_close')) {
			growl_before_close = options.before_close;
		} //end if
		if(options.hasOwnProperty('after_close')) {
			growl_after_close = options.after_close;
		} //end if
	} //end if
	//--
	if(typeof jQuery.gritter == 'undefined') {
		if(jQuery.isFunction(growl_before_open)) {
			growl_before_open();
		} //end if
		if(jQuery.isFunction(growl_after_open)) {
			growl_after_open();
		} //end if
		title = jQuery('<div>' + title + '</div>').text(); // strip tags
		text = jQuery('<div>' + text + '</div>').text(); // strip tags
		alert(title + '\n' + text);
		if(jQuery.isFunction(growl_before_close)) {
			growl_before_close();
		} //end if
		if(jQuery.isFunction(growl_after_close)) {
			growl_after_close();
		} //end if
		return null;
	} //end if
	//--
	if((typeof image != 'undefined') && (image !== '') && (image !== null)) {
		image = '<img src="' + image + '" align="right">';
	} else {
		image = '';
	} //end if
	//--
	if((typeof class_name == 'undefined') || (class_name == 'undefined') || (class_name == undefined)) {
		class_name = '';
	} else {
		class_name = '' + class_name;
	} //end if
	//--
	var growl = jQuery.gritter.add({
		class_name: '' + class_name,
		title: '' + title + image,
		text: '' + text,
		sticky: sticky,
		before_open:  growl_before_open,
		after_open:   growl_after_open,
		before_close: growl_before_close,
		after_close:  growl_after_close,
		time: parseInt(time)
	});
	//--
	return growl;
	//--
} //END FUNCTION


/*
 * Remove a Growl Notification by ID from the current browser window (page)
 *
 * @private : non public
 * @method GrowlNotificationDoRemove
 * @static
 * @param 	{String} 	id 		The HTML id of the Growl Notification to remove
 */
var GrowlNotificationDoRemove = function(id) {
	//--
	if(typeof jQuery.gritter == 'undefined') {
		return;
	} //end if
	//--
	if((typeof id != 'undefined') && (id !== undefined) && (id != '')) {
		try {
			jQuery.gritter.remove(id);
		} catch(e){}
	} else {
		jQuery.gritter.removeAll();
	} //end if else
	//--
} //END FUNCTION

//======================================= Open Req. PopUp


/*
 * Inits and Ppen a PopUp or Modal Window by Form or Link
 *
 * @private : non public
 * @method init_PopUp
 * @static
 * @param 	{String} 	strUrl 			The URL to open
 * @param 	{String} 	strTarget 		The URL target (window name)
 * @param 	{String} 	windowWidth 	*Optional* The Window Width
 * @param 	{String} 	windowHeight 	*Optional* The Window Height
 * @param 	{Enum} 		forcePopUp 		*Optional* Open Mode:
 * 		 0 (default) don't force, if modal Open Modal otherwise open PopUp
 * 		 1 force PopUp
 * 		-1 force Modal
 * @param 	{Enum} 		forceDims 		*Optional* If Modal must be set to 1 to force use the specified Width and Height
 */
var init_PopUp = function(strUrl, strTarget, windowWidth, windowHeight, forcePopUp, forceDims) {
	//--
	if(((typeof SmartJS_ModalBox != 'undefined') && (SmartJS_BrowserUtils_Use_iFModalBox_Active) && (forcePopUp != 1)) || (forcePopUp == -1)) { // use smart modal box
		//-- trasfer current parent refresh settings to this modal {{{SYNC-TRANSFER-MODAL-POPUP-REFRESH}}}
		SmartJS_ModalBox.setRefreshParent(SmartJS_BrowserUtils_RefreshState, SmartJS_BrowserUtils_RefreshURL);
		//-- reset refresh on each modal open else a popup opened previous may refresh the parent on close
		SmartJS_BrowserUtils_RefreshState = 0;
		SmartJS_BrowserUtils_RefreshURL = '';
		//-- open
		if(forceDims != 1) {
			SmartJS_ModalBox.go_Load(strUrl, SmartJS_BrowserUtils_Use_iFModalBox_Protection); // we do not use here custom size
		} else {
			SmartJS_ModalBox.go_Load(strUrl, SmartJS_BrowserUtils_Use_iFModalBox_Protection, windowWidth, windowHeight); // we use here custom size
		} //end if else
		//--
	} else { // use pop up
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
		try {
			SmartJS_BrowserUtils_PopUpWindow = window.open(strUrl, strTarget, "top=" + windowTop + ",left=" + windowLeft + ",width=" + windowWidth + ",height=" + windowHeight + ",toolbar="+SmartJS_BrowserUtils_PopUp_ShowToolBar+",scrollbars=1,resizable=1");
		} catch(err){
			alert('ERROR when trying to raise a new PopUp Window: ' + err);
		} //end try catch
		if(SmartJS_BrowserUtils_PopUpWindow) {
			try { // post-focus
				_class.windowFocus(SmartJS_BrowserUtils_PopUpWindow); // focus
			} catch(err){}
			try { // monitor when popup is closed, every 250ms
				var wnd_popup_timer = setInterval(function() {
					if(SmartJS_BrowserUtils_PageAway !== true) {
						if(SmartJS_BrowserUtils_PopUpWindow.closed) {
							clearInterval(wnd_popup_timer); // first stop
							RefreshEXEC_Self(); // {{{SYNC-POPUP-Refresh-Parent-By-EXEC}}}
							return false;
						} //end if
					} //end if
				}, 250);
			} catch(err){}
		} //end if
		//--
	} //end if else
	//--
} //END FUNCTION


} //END CLASS

//==================================================================
//==================================================================

// #END
