
// [LIB - SmartFramework / JS / Smart Modal iFrame]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.7.2 r.2016.09.27 / smart.framework.v.2.3

// DEPENDS: jQuery

//==================================================================
//==================================================================

//=======================================
// CLASS :: Smart Modal iFrame
//=======================================

//-- setup
var SmartJS_ModalBox_LoaderBlank = 'lib/js/framework/blank.html';			// loader html blank page
var SmartJS_ModalBox_LoaderImg = 'lib/js/framework/img/loading_imodal.gif';	// loader image
var SmartJS_ModalBox_CloseImg = 'lib/js/framework/img/closex.png';			// close image
var SmartJS_ModalBox_CloseCode = ''; 										// html code for the modal closer ...
var SmartJS_ModalBox_CloseAlign = 'right'; 									// close align: 'left' or 'right'
var SmartJS_ModalBox_vAlign = 'top'; 										// vAlign: 'top' or 'middle' / 'center'
var SmartJS_ModalBox_DelayOpen = 850;										// modal open delay (500 ... 1000) ; default is 750
var SmartJS_ModalBox_DelayClose = 500;										// modal close delay (250 ... 750) ; default is 500
//-- privates, access only as readonly or use get/set functions
var SmartJS_ModalBox_Status = '';											// hold the status
var SmartJS_ModalBox_RefreshBy_State = 0; 									// if=1, will refresh parent
var SmartJS_ModalBox_RefreshBy_URL = ''; 									// ^=1 -> if != '' will redirect parent
//--

//===========================================

var SmartJS_ModalBox = new function() { // START CLASS

// :: static

var _class = this; // self referencing

// [PUBLIC]
this.getName = function() {
	//--
	return 'smart__iFModalBox__iFrame';
	//--
} //END FUNCTION

// [PUBLIC]
this.getStatus = function() {
	//--
	return '' + SmartJS_ModalBox_Status;
	//--
} //END FUNCTION

// [PUBLIC]
this.setRefreshParent = function(state, yURL) {
	//--
	if((typeof yURL == 'undefined') || (yURL == 'undefined') || (yURL == null)) {
		yURL = '';
	} //end if
	//--
	SmartJS_ModalBox_RefreshBy_State = parseInt(state);
	SmartJS_ModalBox_RefreshBy_URL = yURL;
	//--
} //END FUNCTION

// [PUBLIC]
this.Refresh_SET_iFrame_Parent = function(yURL) {
	//--
	if((typeof yURL == 'undefined') || (yURL == 'undefined') || (yURL == null)) {
		yURL = '';
	} //end if
	//--
	SmartJS_ModalBox_RefreshBy_State = 1;
	SmartJS_ModalBox_RefreshBy_URL = '' + yURL;
	//--
} //END FUNCTION

// [PUBLIC]
this.make_Visible = function() {
	//--
	jQuery('#smart__iFModalBox__X').css({
		'visibility': 'visible'
	});
	//--
	jQuery('#smart__iFModalBox__iFrame').css({
		'background-color': '#FFFFFF',
		'visibility': 'visible' // BugFix: we use opacity to hide/show iFrame because some bug in browsers if the iframe is hidden while loading
	});
	jQuery('#smart__iFModalBox__Ldr').empty().html('');
	//--
	return false;
	//--
} //END FUNCTION

// [PUBLIC]
this.go_Load = function(yURL, yProtect, windowWidth, windowHeight) {
	//--
	do_load(yURL, yProtect, windowWidth, windowHeight);
	//--
	return false;
	//--
} //END FUNCTION

// [PUBLIC]
this.go_UnLoad = function() {
	//--
	var test_unload = true;
	try {
		test_unload = _class.on_Before_Unload();
	} catch(e) {}
	if(!test_unload) {
		return false; // this is like onbeforeunload
	} //end if
	//--
	do_unload();
	//--
	var closeDelay = parseInt(SmartJS_ModalBox_DelayClose);
	if(isNaN(closeDelay)) {
		closeDelay = 500; // default
	} //end if
	if(closeDelay < 250) {
		closeDelay = 250;
	} //end if
	if(closeDelay > 750) {
		closeDelay = 750;
	} //end if
	//--
	setTimeout(function(){ SmartJS_ModalBox.fx_Unload(); }, closeDelay); // delayed close
	//--
	return false;
	//--
} //END FUNCTION

// [PUBLIC]
this.fx_Unload = function() {
	//--
	fx_out();
	//--
	return false;
	//--
} //END FUNCTION

//================================== # [PRIVATES]

var RefreshEXEC_Self = function() {
	//--
	if(SmartJS_ModalBox_RefreshBy_State) {
		//--
		if((typeof SmartJS_ModalBox_RefreshBy_URL == 'undefined') || (SmartJS_ModalBox_RefreshBy_URL === undefined) || (SmartJS_ModalBox_RefreshBy_URL == '')) {
			//--
			//self . location . reload(false); // false is to reload from cache
			self.location = self.location; // FIX: avoid reload to resend POST vars !!
			//--
		} else {
			//--
			self.location = '' + SmartJS_ModalBox_RefreshBy_URL;
			//--
		} //end if else
		//--
		SmartJS_ModalBox_RefreshBy_State = 0;
		SmartJS_ModalBox_RefreshBy_URL = '';
		//--
	} //end if
	//--
} //END FUNCTION

var get_Window_Width = function(windowWidth) {
	//--
	windowWidth = parseInt(windowWidth);
	//--
	if((windowWidth <= 0) || isNaN(windowWidth)) {
		//--
		windowWidth = parseInt(parseInt(jQuery(window).width()) - 40);
		//--
		if(isNaN(windowWidth)) {
			windowWidth = 920; // just in case
		} //end if
		//--
	} //end if
	//--
	if(windowWidth < 200) {
		windowWidth = 200;
	} //end if
	//--
	return windowWidth;
	//--
} //END FUNCTION

var get_Window_Height = function(windowHeight) {
	//--
	windowHeight = parseInt(windowHeight);
	//--
	if((windowHeight <= 0) || isNaN(windowHeight)) {
		//--
		windowHeight = parseInt(parseInt(jQuery(window).height()) - 20);
		//--
		if(isNaN(windowHeight)) {
			windowHeight = 700; // just in case
		} //end if
		//--
	} //end if
	//--
	if(windowHeight < 100) {
		windowHeight = 100;
	} //end if
	//--
	return windowHeight;
	//--
} //END FUNCTION

var do_load = function(yURL, yProtect, windowWidth, windowHeight) {
	//--
	SmartJS_ModalBox_Status = 'visible';
	//-- disable parent scrolling
	jQuery('body').css({
		'overflow': 'hidden' // need to be hidden
	});
	//--
	var iFBoxWidth = windowWidth;
	var iFBoxHeight = windowHeight;
	//--
	jQuery('#smart__iFModalBox__Ldr').empty().html('<br><br><img src="' + SmartJS_ModalBox_LoaderImg + '" alt="..." title="...">');
	//--
	fx_in(yProtect, iFBoxWidth, iFBoxHeight);
	//-- force no-cache and fix a bug if same URL as parent
	var UrlTime = new Date().getTime();
	if(yURL.indexOf('?') != -1) {
		yURL += '&smart__iFModalBox__iFrame=' + encodeURIComponent(UrlTime);
	} else {
		yURL += '?smart__iFModalBox__iFrame=' + encodeURIComponent(UrlTime);
	} //end if else
	//--
	jQuery('#smart__iFModalBox__iFrame').show().css({
		'width': '100%',
		'height': '100%',
		'visibility': 'hidden' // BugFix: we use opacity to hide/show iFrame because some bug in browsers if the iframe is hidden while loading
	}).attr('src', '' + yURL);
	//--
	var the_closeimg = '';
	if(SmartJS_ModalBox_CloseCode === '') {
		the_closeimg = '<img src="' + SmartJS_ModalBox_CloseImg + '" alt="[X]" title="[X]">';
	} else {
		the_closeimg = '' + SmartJS_ModalBox_CloseCode;
	} //end if else
	//--
	var the_align_left = 'auto';
	var the_align_right = 'auto';
	if(SmartJS_ModalBox_CloseAlign == 'left') {
		the_align_left = '-20px'; // left
	} else { // right
		the_align_right = '-20px'; // right
	} //end if else
	//--
	jQuery('#smart__iFModalBox__X').show().css({
		'position': 'absolute',
		'z-index': 2111111099, //9999999,
		'cursor': 'pointer',
		'top': '-12px',
		'left': the_align_left,
		'right': the_align_right,
		'min-width': '32px',
		'max-width': '64px',
		'min-height': '32px',
		'max-height': '64px',
		'visibility': 'hidden'
	}).empty().html(the_closeimg).click(function(){
		_class.go_UnLoad();
	});
	//--
	if(yProtect != 1) {
		jQuery('#smart__iFModalBox__Bg').click(function(){
			_class.go_UnLoad();
		});
	} else {
		jQuery('#smart__iFModalBox__Bg').unbind('click');
	} //end if
	//-- show delayed
	var openDelay = parseInt(SmartJS_ModalBox_DelayOpen);
	if(isNaN(openDelay)) {
		openDelay = 750; // default
	} //end if
	if(openDelay < 500) {
		openDelay = 500;
	} //end if
	if(openDelay > 1000) {
		openDelay = 1000;
	} //end if
	setTimeout(function(){ SmartJS_ModalBox.make_Visible(); }, openDelay); // delay a bit to avoid show a blank area
	//--
	return false;
	//--
} //END FUNCTION

var do_unload = function() {
	//--
	jQuery('#smart__iFModalBox__Bg').unbind('click');
	jQuery('#smart__iFModalBox__X').unbind('click');
	jQuery('#smart__iFModalBox__Ldr').empty().html('');
	//--
	var the_align_left = 'auto';
	var the_align_right = 'auto';
	if(SmartJS_ModalBox_CloseAlign == 'left') {
		the_align_left = '0px'; // left
	} else { // right
		the_align_right = '0px'; // right
	} //end if else
	//--
	jQuery('#smart__iFModalBox__X').css({
		'position': 'absolute',
		'width': '1px',
		'height': '1px',
		'left': the_align_left,
		'right': the_align_right,
		'top': '0px',
	}).empty().html('').hide();
	//--
	jQuery('#smart__iFModalBox__iFrame').css({
		'width': '1px',
		'height': '1px'
	}).attr('src', SmartJS_ModalBox_LoaderBlank); // force unload
	jQuery('#smart__iFModalBox__iFrame').attr('src', '').hide();
	//--
	jQuery('#smart__iFModalBox__Div').css({
		'position': 'absolute',
		'width': '1px',
		'height': '1px',
		'left': '0px',
		'top': '0px'
	}).hide();
	//-- restore parent scrolling
	jQuery('body').css({
		'overflow': 'auto' // need to be 'auto' instead 'visible' to work with IE
	});
	//--
	SmartJS_ModalBox_Status = '';
	//--
	return false;
	//--
} //END FUNCTION

var fx_in = function(yProtect, windowWidth, windowHeight) {
	//--
	var the_wWidth = get_Window_Width(windowWidth);
	var the_wHeight = get_Window_Height(windowHeight);
	//--
	var the_wRealWidth = get_Window_Width(0);
	if(the_wRealWidth < windowWidth) {
		the_wWidth = the_wRealWidth;
	} //end if
	var the_wRealHeight = get_Window_Height(0);
	if(the_wRealHeight < windowHeight) {
		the_wHeight = the_wRealHeight;
	} //end if
	//--
	var the_style_cursor = 'auto';
	if(yProtect != 1) {
		the_style_cursor = 'pointer';
	} //end if
	jQuery('#smart__iFModalBox__Bg').css({
		'position': 'fixed',
		'z-index': 2111111097, //9999997,
		'cursor': the_style_cursor,
		'text-align': 'center',
		'left': '0px',
		'top': '0px',
		'width': '100%',
		'height': '100%',
	}).show();
	//--
	fx_position(the_wWidth, the_wHeight);
	//--
	window.onresize = function () { // called when the window is resized
		//--
		fx_in(yProtect, windowWidth, windowHeight);
		//--
	} //end function
	//--
	return false;
	//--
} //END FUNCTION

var fx_out = function() {
	//--
	window.onresize = function () {
		// clear
	} //end function
	//--
	jQuery('#smart__iFModalBox__Div').css({
		'position': 'absolute',
		'width': '1px',
		'height': '1px',
		'left': '0px',
		'top': '0px'
	}).hide();
	//--
	jQuery('#smart__iFModalBox__Bg').css({
		'position': 'absolute',
		'width': '1px',
		'height': '1px',
		'left': '0px',
		'top': '0px'
	}).hide();
	//--
	RefreshEXEC_Self(); // {{{SYNC-MODAL-Refresh-Parent-By-EXEC}}}
	//--
	return false;
	//--
} //END FUNCTION

var fx_position = function(windowWidth, windowHeight) {
	//--
	var the_h_align = parseInt(parseInt(jQuery(window).scrollLeft()) + ((parseInt(jQuery(window).width()) - windowWidth) / 2)) + 'px';
	var the_v_align = parseInt(parseInt(jQuery(window).scrollTop()) + 10) + 'px';
	if((SmartJS_ModalBox_vAlign == 'center') || (SmartJS_ModalBox_vAlign == 'middle')) {
		the_v_align = parseInt((parseInt(jQuery(window).scrollTop()) + ((parseInt(jQuery(window).height()) - windowHeight) / 2))) + 'px';
	} //end if else
	//--
	jQuery('#smart__iFModalBox__Div').css({
		'position': 'absolute',
		'z-index': 2111111098, //9999998,
		'text-align': 'center',
		'left': the_h_align,
		'top': the_v_align,
		'width': windowWidth + 'px',
		'height': windowHeight + 'px'
	}).show();
	//--
} //END FUNCTION

//== ALL BELOW ARE EXTERNAL HANDLERS THAT CAN BE REDEFINED PER INSTANCE

//--
this.on_Before_Unload = function() {
	//--
	return true; // execute code onUnload and must return true if do unload or false to prevent unload
	//--
} //END FUNCTION
//--

//==

} //END CLASS

//===========================================

jQuery(function() {
	//--
	jQuery('body').append('<!-- SmartJS.Modal.Loader :: r.150706 --><div id="smart__iFModalBox__Bg" style="background-color:#333333; position:absolute; top:0px; left:0px; width:1px; height:1px; opacity: 0.85; filter:alpha(opacity=80);"></div><div id="smart__iFModalBox__Div" style="position:absolute; top:0px; left:0px; width:1px; height:1px;"><div id="smart__iFModalBox__Ldr" align="center"></div><div id="smart__iFModalBox__X" title="[X]"></div><iframe name="smart__iFModalBox__iFrame" id="smart__iFModalBox__iFrame" width="1" height="1" scrolling="auto" src="" marginwidth="5" marginheight="5" hspace="0" vspace="0" frameborder="0"></iframe></div><!-- END: SmartJS.Modal.Loader -->'); // src="' + SmartJS_ModalBox_LoaderBlank + '"
	//--
	SmartJS_ModalBox.fx_Unload();
	//--
}); //END DOCUMENT READY

//==================================================================
//==================================================================

// #END
