
// jQuery Simple Dialog (SmartLightUI)
// (c) 2015-2017 unix-world.org
// License: BSD
// v.170406

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
	y_width = getMaxWidth(y_width);
	y_height = getMaxHeight(y_height);
	//--
	var the_top = 25; // 50 / 2
	var the_left = parseInt((jQuery(window).width()/2) - (y_width/2) - 4); // -4 for borders
	if(isNaN(the_left) || (the_left < 0)) {
		the_left = 0;
	} //end if
	//--
	jQuery('#simpledialog-overlay').css({
		'top': '0px',
		'left': '0px',
		'width': '100%',
		'height': '100%',
		'z-index': 2147482001
	}).show();
	//--
	jQuery('#simpledialog-area-head').html('' + y_title);
	jQuery('#simpledialog-area-msg').html('' + y_message_html);
	jQuery('#simpledialog-bttn-no').show().text('').hide();
	jQuery('#simpledialog-bttn-yes').show().html('<i class="fa fa-check"></i> OK').click(function () {
		_class.CloseWidget();
		if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
			try {
				eval('(function(){ ' + evcode + ' })();'); // sandbox
			} catch(err) {
				alert('ERROR: JS-Eval Error on BrowserLightUI DialogAlert Function' + '\nDetails: ' + err);
			} //end try catch
		} //end if
	});
	jQuery('#simpledialog-area-msg').css({
		'height': (y_height - 130) + 'px'
	});
	var HtmlElement = jQuery('#simpledialog-container');
	HtmlElement.css({
		'top': the_top + 'px',
		'left': the_left + 'px',
		'width': y_width + 'px',
		'height': y_height + 'px',
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
	y_width = getMaxWidth(y_width);
	y_height = getMaxHeight(y_height);
	//--
	var the_top = 25; // 50 / 2
	var the_left = parseInt((jQuery(window).width()/2) - (y_width/2) - 4); // -4 for borders
	if(isNaN(the_left) || (the_left < 0)) {
		the_left = 0;
	} //end if
	//--
	jQuery('#simpledialog-overlay').css({
		'top': '0px',
		'left': '0px',
		'width': '100%',
		'height': '100%',
		'z-index': 2147482001
	}).show();
	//--
	jQuery('#simpledialog-area-head').html('' + y_title);
	jQuery('#simpledialog-area-msg').html('' + y_question_html);
	jQuery('#simpledialog-bttn-no').show().html('<i class="fa fa-remove"></i> Cancel').click(function () {
		_class.CloseWidget();
	});
	jQuery('#simpledialog-bttn-yes').show().html('<i class="fa fa-check"></i> OK').click(function () {
		_class.CloseWidget();
		if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
			try {
				eval('(function(){ ' + evcode + ' })();'); // sandbox
			} catch(err) {
				alert('ERROR: JS-Eval Error on BrowserLightUI DialogAlert Function' + '\nDetails: ' + err);
			} //end try catch
		} //end if
	});
	jQuery('#simpledialog-area-msg').css({
		'height': (y_height - 130) + 'px'
	});
	var HtmlElement = jQuery('#simpledialog-container');
	HtmlElement.css({
		'top': the_top + 'px',
		'left': the_left + 'px',
		'width': y_width + 'px',
		'height': y_height + 'px',
		'z-index': 2147482002
	}).show();
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

this.CloseWidget = function() {
	//--
	jQuery('#simpledialog-area-head').text('[T]');
	jQuery('#simpledialog-area-msg').text('[M]');
	jQuery('#simpledialog-bttn-no').text('[N]').unbind('click').hide();
	jQuery('#simpledialog-bttn-yes').text('[Y]').unbind('click').hide();
	jQuery('#simpledialog-area-msg').css({
		'height': '40%'
	});
	var HtmlElement = jQuery('#simpledialog-container');
	HtmlElement.css({
		'top':'-50px',
		'left':'-50px',
		'width':'1px',
		'height':'1px',
		'z-index':1
	}).hide();
	jQuery('#simpledialog-overlay').css({
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

// #PRIVATES#

//=======================================

var getMaxWidth = function(y_width) {
	//--
	if((typeof y_width == 'undefined') || (y_width == null) || (y_width == '')) {
		y_width = 550;
	} //end if
	y_width = parseInt(y_width);
	if(isNaN(y_width) || (y_width < 100) || (y_width > 920)) {
		y_width = 550;
	} //end if
	var max_width = parseInt(jQuery(window).width() - 50);
	if(isNaN(max_width) || (max_width < 270)) {
		max_width = 270;
	} //end if
	if(y_width > max_width) {
		y_width = max_width; // responsive fix width: min 320
	} //end if
	//--
	return y_width;
	//--
} //END FUNCTION

//=======================================

var getMaxHeight = function(y_height) {
	//--
	if((typeof y_height == 'undefined') || (y_height == null) || (y_height == '')) {
		y_height = 225;
	} //end if
	y_height = parseInt(y_height);
	if(isNaN(y_height) || (y_height < 50) || (y_height > 700)) {
		y_height = 225;
	} //end if
	var max_height = parseInt(jQuery(window).height() - 50);
	if(isNaN(max_height) || (max_height < 270)) {
		max_height = 270;
	} //end if
	if(y_height > max_height) {
		y_height = max_height; // responsive fix height: min 320
	} //end if
	//--
	return y_height;
	//--
} //END FUNCTION

//=======================================

} //END CLASS


//=======================================
//=======================================

jQuery(function() {
	//-- requires: <link rel="stylesheet" type="text/css" href="lib/js/jquery/dialog/simple-dialog.css">
	jQuery('body').append('<!-- SmartJS.Modal.Dialog :: r.160919 --><div id="simpledialog-overlay"></div><div id="simpledialog-container"><div id="simpledialog-area-head" class="header">[T]</div><div id="simpledialog-area-msg" class="message">[M]</div><hr><div class="buttons"><div id="simpledialog-bttn-yes">[Y]</div><div id="simpledialog-bttn-no">[N]</div></div></div><!-- END: SmartJS.Modal.Loader -->');
	//--
	SmartSimpleDialog.CloseWidget();
	//--
}); //END DOCUMENT READY


//==================================================================
//==================================================================


// #END
