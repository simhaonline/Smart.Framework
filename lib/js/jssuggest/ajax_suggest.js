
// NetVision JS - Ajax Suggest
// (c) 2006-2015 unix-world.org
// v.160919

// DEPENDS: jQuery

//==================

var AJX_Suggest_LoaderImg = 'lib/js/jssuggest/img/ajax_loader.gif';

var AJX_Suggest_Class = new function() {

// :: static

var table_code_start = '<table cellpadding="0" cellspacing="0" id="AJX_suggest_container">';
var table_code_end = '</table>';

//Called from keyup on the search textbox :: Starts the AJAX request.
// y_method = GET / POST ; y_search_results = search.php?search=
this.search_Suggest = function(y_div, y_txt, y_method, y_search_results) {
	//--
	var the_divelem = $('#' + y_div);
	var the_txtfield = $('#' + y_txt);
	//--
	var url = '' + y_search_results + encodeURIComponent(the_txtfield.val());
	var method = 'GET';
	if(y_method !== 'GET') {
		method = 'POST';
	} //end if
	//--
	if(the_divelem.is(':visible')) {
		//--
		AJX_Suggest_Class.resetDIV(y_div);
		//--
	} else {
		//--
		the_divelem.show();
		the_divelem.css({'height':'auto'});
		the_divelem.html('<div class="AJX_suggest_load"><img src="' + AJX_Suggest_LoaderImg + '"></div>');
		//--
		$.ajax({
			async: true,
			cache: false,
			timeout: 0,
			type: method,
			url: url,
			data: '',
			dataType: 'json',
			success: function(answer) {
				//--
				if(answer instanceof Array) {
					// OK
				} else {
					return;
				} //end if
				//--
				var extcols = 1;
				for(var i=0; i<(answer.length); i++) {
					if(answer[i].label instanceof Array) {
						extcols = Math.max(extcols, answer[i].label.length);
					} //end if
				} //end for
				var cols = 0;
				//--
				var el = 0;
				var suggest = table_code_start;
				//--
				for(var i=0; i<(answer.length); i++) {
					//--
					suggest += '<tr valign="top" data-id="' + SmartJS_CoreUtils.escape_html(el) + '" data-value="' + SmartJS_CoreUtils.escape_html(answer[i].value) + '" onclick="AJX_Suggest_Class.set_Search(\'' + SmartJS_CoreUtils.escape_html(y_div) + '\', \'' + SmartJS_CoreUtils.escape_html(y_txt) + '\', $(this).data(\'value\'), $(this).html());" title="' + SmartJS_CoreUtils.escape_html(el+1) + '">';
					//--
					suggest += '<td style="font-weight:bold !important;">';
					suggest += SmartJS_CoreUtils.escape_html(answer[i].value);
					suggest += '</td>';
					//--
					if(answer[i].label instanceof Array) {
						cols = answer[i].label.length;
						for(var j=0; j<cols; j++) {
							if(answer[i].label[j].hasOwnProperty('color')) {
								suggest += '<td style="color:' + SmartJS_CoreUtils.escape_html(answer[i].label[j].color) + '!important;">' + SmartJS_CoreUtils.escape_html(answer[i].label[j].text) + '</td>';
							} else {
								suggest += '<td>' + SmartJS_CoreUtils.escape_html(answer[i].label[j]) + '</td>';
							} //end if else
						} //end for
					} else {
						cols = 1;
						suggest += '<td>' + SmartJS_CoreUtils.escape_html(answer[i].label) + '</td>';
					} //end if else
					//--
					if(cols < extcols) { // fix
						for(var k=0; k<(extcols - cols); k++) {
							suggest += '<td>&nbsp;</td>';
						} //end for
					} //end if
					//--
					suggest += '</tr>';
					//--
					el += 1;
					//--
				} //end for
				//--
				suggest += table_code_end;
				//-- clear the loading img
				AJX_Suggest_Class.resetDIV(y_div);
				the_divelem.show();
				the_divelem.css({'height':'auto'});
				//-- set div content
				if(el > 0) {
					the_divelem.html(suggest);
				} else {
					the_divelem.html(table_code_start + '<tr valign="top" onclick="AJX_Suggest_Class.resetDIV(\'' + y_div + '\');">' + '<td align="center" title="[No Matching Results]">(' + el + ')</td>' + '</tr>' + table_code_end);
				} //end if
				//-- cleanup
				el = 0;
				suggest = '';
				//--
			}, //END FUNCTION
			error: function(answer) {
				//--
				alert('ERROR (JS-Suggest): Invalid Server Response !', '' + answer.responseText);
				//--
				AJX_Suggest_Class.resetDIV(y_div);
				//--
			} //END FUNCTION
		});
		//--
	} //end if else
	//--
} //END FUNCTION

this.resetDIV = function(y_div) {
	//--
	var the_divelem = $('#' + y_div);
	var the_left = parseInt( -1 * parseInt(the_divelem.css('width')) / 4);
	//--
	the_divelem.html('');
	the_divelem.css({'height':'1px', 'left':the_left+'px'});
	var the_offset = parseInt(the_divelem.offset().left);
	if(the_offset < 0) { // constraint to avoid be outside page on left
		the_divelem.css({'height':'1px', 'left':the_left-the_offset+10+'px'});
	} //end if
	the_divelem.hide();
	//--
} //END FUNCTION

//Click function :: a function that sets the text of the search textbox when one of our suggested items are clicked.
// values are separed by :: in a cell and \n in rows
this.set_Search = function(y_div, y_txt, value, lineval) {
	//--
	var the_txtfield = $('#' + y_txt);
	var the_divelem = $('#' + y_div);
	//--
	value = '' + value; // force string
	//--
	the_divelem.html('');
	the_divelem.css({'height':'auto'});
	//--
	the_txtfield.val('' + value); // it must not apply htmlspecialchars because it comes from jQuery.data() and is set by jQuery.val()
	the_txtfield.dblclick(function() {
		$(this).val('');
	});
	//--
	the_divelem.html(table_code_start + '<tr valign="top" onclick="AJX_Suggest_Class.resetDIV(\'' + y_div + '\');">' + lineval + '</tr>' + table_code_end);
	//--
	setTimeout(function(){ AJX_Suggest_Class.resetDIV(y_div); }, 850);
	//--
} //END FUNCTION

// disable ENTER Key to submit form
this.disableEnterKey = function(e, y_div, y_txt, y_method, y_search_results) {
	//--
	var key;
	//--
	if(window.event) {
		key = window.event.keyCode; //IE
	} else {
		key = e.which; //firefox
	} //end if else
	//--
	if(key == 13) {
		AJX_Suggest_Class.search_Suggest(y_div, y_txt, y_method, y_search_results);
	} //end if
	//--
	return (key != 13);
	//--
} //END FUNCTION

} //END CLASS

//==================

// #END
