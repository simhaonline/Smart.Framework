
// [LIB - SmartFramework / JS / jQuery Simple Dialog]
// (c) 2006-2015 unix-world.org - all rights reserved
// v.2015.05.27 r.160901

// DEPENDS: jQuery
// REQUIRES-CSS: lib/js/jquery/dialog/simple-dialog.css

//==================================================================
//==================================================================


var SmartSimpleDialog = new function() { // START CLASS

// :: static

var _class = this; // self referencing

//=======================================

// sync with SmartJS_BrowserUtils.alert_Dialog()
this.Dialog_Alert = function(y_message_html, evcode, y_title, y_width, y_height) {
	//--
	if((typeof y_title == 'undefined') || (y_title == null) || (y_title == '')) {
		y_title = ' ';
	} //end if
	//--
	if((typeof y_width == 'undefined') || (y_width == null) || (y_width == '')) {
		y_width = 550;
	} //end if
	y_width = parseInt(y_width);
	if(isNaN(y_width) || (y_width < 100) || (y_width > 920)) {
		y_width = 550;
	} //end if
	//--
	if((typeof y_height == 'undefined') || (y_height == null) || (y_height == '')) {
		y_height = 225;
	} //end if
	y_height = parseInt(y_height);
	if(isNaN(y_height) || (y_height < 50) || (y_height > 700)) {
		y_height = 225;
	} //end if
	//--
	$('#simpledialog-overlay').css({
		'top': '0px',
		'left': '0px',
		'width': '100%',
		'height': '100%',
		'z-index': 2147482001
	}).show();
	//--
	$('#simpledialog-area-head').html('' + y_title);
	$('#simpledialog-area-msg').html('' + y_message_html);
	$('#simpledialog-bttn-no').show().text('').hide();
	$('#simpledialog-bttn-yes').show().html('<i class="fa fa-check"></i> OK').click(function () {
		_class.CloseWidget();
		if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
			try {
				eval('(function(){ ' + evcode + ' })();'); // sandbox
			} catch(err) {
				alert('ERROR: JS-Eval Error on BrowserLightUI DialogAlert Function' + '\nDetails: ' + err);
			} //end try catch
		} //end if
	});
	$('#simpledialog-area-msg').css({
		'height': parseInt(y_height - 130) + 'px'
	});
	var HtmlElement = $('#simpledialog-container');
	HtmlElement.css({
		'top': '70px',
		'left': parseInt(($(window).width()/2)-(y_width/2)) + 'px',
		'width': parseInt(y_width) + 'px',
		'height': parseInt(y_height) + 'px',
		'z-index': 2147482002
	}).show();
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// sync with SmartJS_BrowserUtils.confirm_Dialog()
this.Dialog_Confirm = function(y_question_html, evcode, y_title, y_width, y_height) {
	//--
	if((typeof y_title == 'undefined') || (y_title == null) || (y_title == '')) {
		y_title = ' ';
	} //end if
	//--
	if((typeof y_width == 'undefined') || (y_width == null) || (y_width == '')) {
		y_width = 550;
	} //end if
	y_width = parseInt(y_width);
	if(isNaN(y_width) || (y_width < 100) || (y_width > 920)) {
		y_width = 550;
	} //end if
	//--
	if((typeof y_height == 'undefined') || (y_height == null) || (y_height == '')) {
		y_height = 225;
	} //end if
	y_height = parseInt(y_height);
	if(isNaN(y_height) || (y_height < 50) || (y_height > 700)) {
		y_height = 225;
	} //end if
	//--
	$('#simpledialog-overlay').css({
		'top': '0px',
		'left': '0px',
		'width': '100%',
		'height': '100%',
		'z-index': 2147482001
	}).show();
	//--
	$('#simpledialog-area-head').html('' + y_title);
	$('#simpledialog-area-msg').html('' + y_question_html);
	$('#simpledialog-bttn-no').show().html('<i class="fa fa-remove"></i> Cancel').click(function () {
		_class.CloseWidget();
	});
	$('#simpledialog-bttn-yes').show().html('<i class="fa fa-check"></i> OK').click(function () {
		_class.CloseWidget();
		if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
			try {
				eval('(function(){ ' + evcode + ' })();'); // sandbox
			} catch(err) {
				alert('ERROR: JS-Eval Error on BrowserLightUI DialogAlert Function' + '\nDetails: ' + err);
			} //end try catch
		} //end if
	});
	$('#simpledialog-area-msg').css({
		'height': parseInt(y_height - 130) + 'px'
	});
	var HtmlElement = $('#simpledialog-container');
	HtmlElement.css({
		'top': '70px',
		'left': parseInt(($(window).width()/2)-(y_width/2)) + 'px',
		'width': parseInt(y_width) + 'px',
		'height': parseInt(y_height) + 'px',
		'z-index': 2147482002
	}).show();
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

this.CloseWidget = function() {
	//--
	$('#simpledialog-area-head').text('[T]');
	$('#simpledialog-area-msg').text('[M]');
	$('#simpledialog-bttn-no').text('[N]').unbind('click').hide();
	$('#simpledialog-bttn-yes').text('[Y]').unbind('click').hide();
	$('#simpledialog-area-msg').css({
		'height': '40%'
	});
	var HtmlElement = $('#simpledialog-container');
	HtmlElement.css({
		'top':'-50px',
		'left':'-50px',
		'width':'1px',
		'height':'1px',
		'z-index':1
	}).hide();
	$('#simpledialog-overlay').css({
		'top':'-50px',
		'left':'-50px',
		'width':'1px',
		'height':'1px',
		'z-index':1
	}).hide();
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

$(function() {
	//-- requires: <link rel="stylesheet" type="text/css" href="lib/js/jquery/dialog/simple-dialog.css">
	$('body').append('<!-- SmartJS.Modal.Dialog :: r.160901 --><div id="simpledialog-overlay"></div><div id="simpledialog-container"><div id="simpledialog-area-head" class="header">[T]</div><div id="simpledialog-area-msg" class="message">[M]</div><hr><div class="buttons"><div id="simpledialog-bttn-yes" class="yes">[Y]</div><div id="simpledialog-bttn-no" class="no simpledialog-close">[N]</div></div></div><!-- END: SmartJS.Modal.Loader -->');
	//--
}); //END DOCUMENT READY


} //END CLASS

//==================================================================
//==================================================================


// #END
