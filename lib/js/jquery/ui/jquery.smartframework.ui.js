
// [LIB - SmartFramework / JS / Browser UI Utils]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3 r.2016.08.30

// DEPENDS: jQuery, jQuery-Growl, jQueryUI, SmartJS_CoreUtils, SmartJS_BrowserUtils

//==================================================================
//==================================================================

var SmartJS_BrowserUIUtils = new function() { // START CLASS

//=======================================

// sync with SmartJS_BrowserUtils.alert_Dialog()
this.DialogAlert = function(y_message_html, evcode, y_title, y_width, y_height) {
	//--
	if((typeof y_title == 'undefined') || (y_title == null) || (y_title == '')) {
		y_title = '!';
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
	var HtmlElement = $('<div></div>').html(y_message_html);
	var TheMsgDialog = HtmlElement.dialog({autoOpen:false});
	//--
	TheMsgDialog.dialog({
		title: y_title,
		resizable: false,
		width: y_width,
		height: y_height,
		position: { my: 'center top+70', at: 'center top', of: window },
		modal: true,
		closeOnEscape: false,
		open: function(event, ui){ $(this).parent().find('.ui-dialog-titlebar-close').hide(); },
		buttons: {
			'Cancel': {
				text: 'Cancel',
				//icons: { primary: 'ui-icon-closethick' },
				icon: 'ui-icon-closethick', // fix for jQueryUI 1.12
				click: function() {
					//--
					$(this).dialog('close');
					$(this).dialog('destroy');
					$(this).remove();
					//--
					if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
						try {
							eval('(function(){ ' + evcode + ' })();'); // sandbox
						} catch(err) {
							alert('ERROR: JS-Eval Error on BrowserUI DialogAlert Function' + '\nDetails: ' + err);
						} //end try catch
					} //end if
					//--
				}
			}
		}
	});
	//--
	TheMsgDialog.dialog('open');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// sync with SmartJS_BrowserUtils.confirm_Dialog()
this.DialogConfirm = function(y_question_html, evcode, y_title, y_width, y_height) {
	//--
	if((typeof y_title == 'undefined') || (y_title == null) || (y_title == '')) {
		y_title = '?';
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
	var HtmlElement = $('<div></div>').html(y_question_html);
	var TheMsgDialog = HtmlElement.dialog({autoOpen:false});
	//--
	TheMsgDialog.dialog({
		title: y_title,
		resizable: false,
		width: y_width,
		height: y_height,
		position: { my: 'center top+70', at: 'center top', of: window },
		modal: true,
		closeOnEscape: false,
		open: function(event, ui){ $(this).parent().find('.ui-dialog-titlebar-close').hide(); },
		buttons: {
			'Cancel': {
				text: 'Cancel',
				//icons: { primary: 'ui-icon-closethick' },
				icon: 'ui-icon-closethick', // fix for jQueryUI 1.12
				click: function() {
					//--
					$(this).dialog('close');
					$(this).dialog('destroy');
					$(this).remove();
					//--
				}
			},
			'OK': {
				text: 'OK',
				//icons: { primary: 'ui-icon-check' },
				icon: 'ui-icon-check', // fix for jQueryUI 1.12
				click: function() {
					//--
					$(this).dialog('close');
					$(this).dialog('destroy');
					$(this).remove();
					//--
					if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
						try {
							eval('(function(){ ' + evcode + ' })();'); // sandbox
						} catch(err) {
							alert('ERROR: JS-Eval Error on BrowserUI DialogConfirm Function' + '\nDetails: ' + err);
						} //end try catch
					} //end if
					//--
				}
			}
		}
	});
	//--
	TheMsgDialog.dialog('open');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	lib/js/jquery/ui/listselect/jquery.multiselect.css
//	lib/js/jquery/ui/listselect/jquery.multiselect.filter.css
//	lib/js/jquery/ui/listselect/jquery.multiselect.js
//	lib/js/jquery/ui/listselect/i18n/jquery.multiselect.{lang}.js
//	lib/js/jquery/ui/listselect/jquery.multiselect.filter.js
//	lib/js/jquery/ui/listselect/i18n/jquery.multiselect.filter.{lang}.js
this.Smart_SelectList = function(elemID, dimW, dimH, allowMulti, useFilter) {
	//--
	var HtmlElement = $('#' + elemID);
	//--
	HtmlElement.multiselect({
		multiple: allowMulti,
		selectedList: 1,
		minWidth: dimW,
		height: dimH,
		position: {
			my: 'left top',
			at: 'left bottom',
			collision: 'flipfit'
		},
		close: function() {
			//--
			var evcode = HtmlElement.attr('onBlur'); // onChange is always triggered, but useless on Multi-Select Lists on which we substitute it with the onBlur which is not triggered here but we catch and execute here
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					eval('(function(){ ' + evcode + ' })();'); // sandbox
				} catch(err) {
					alert('ERROR: JS-Eval Error on Single-SelectList: ' + elemID + '\nDetails: ' + err);
				} //end try catch
			} //end if
			//--
		} //end function
	});
	//--
	if(useFilter === true) {
		HtmlElement.multiselectfilter({
			autoReset: true,
			placeholder: '...',
			label: '?:'
		});
	} //end if
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	lib/js/jquery/ui/i18n/jquery.ui.datepicker-{lang}.js
this.Date_Picker = function(elemID, dateFmt, selDate, calStart, calMinDate, calMaxDate, noOfMonths, evcode) {
	//--
	var the_initial_date = '' + selDate;
	//--
	var the_initial_altdate = '';
	if(the_initial_date != '') {
		$('#date-bttn-' + elemID).attr('title', '' + selDate);
		the_initial_altdate = $.datepicker.formatDate('' + dateFmt, new Date(the_initial_date));
		$('#date-entry-' + elemID).val(the_initial_altdate);
	} //end if
	//--
	var HtmlElement = $('#' + elemID);
	//--
	HtmlElement.datepicker({
		showAnim: null, duration: null,
		numberOfMonths: noOfMonths, stepMonths: 1,
		showButtonPanel: true, showWeek: true, weekHeader: '#',
		prevText: '&lt;&lt;', nextText: '&gt;&gt;',
		changeYear: true, changeMonth: true,
		showOtherMonths: true, selectOtherMonths: false,
		firstDay: calStart,
		dateFormat: 'yy-mm-dd',
		altFormat: '' + dateFmt,
		altField: '#date-entry-' + elemID,
		minDate: calMinDate, maxDate: calMaxDate,
		onSelect: function(date, inst) {
			//--
			$('#date-bttn-' + elemID).attr('title', date);
			var altdate = date;
			try {
				altdate = $.datepicker.formatDate('' + dateFmt, new Date(date));
				if(/Invalid|NaN/.test(altdate)) {
					altdate = date;
				} //end if
			} catch(err) {
				console.log('Date conversion is not supported by the browser. Using ISO Date');
			}
			$('#date-entry-' + elemID).val(altdate);
			//--
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					eval('(function(){ ' + evcode + ' })();'); // sandbox
				} catch(err) {
					alert('ERROR: JS-Eval Error on DatePicker: ' + elemID + '\nDetails: ' + err);
				} //end try catch
			} //end if
			//--
		} //end function
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	lib/js/jquery/ui/timepicker/jquery.ui.timepicker.css
//	lib/js/jquery/ui/timepicker/jquery.ui.timepicker.js
//	lib/js/jquery/ui/timepicker/i18n/jquery.ui.timepicker-{lang}.js
this.Time_Picker = function(elemID, hStart, hEnd, mStart, mEnd, mInterval, tmRows, evcode) {
	//--
	var HtmlElement = $('#' + elemID);
	//--
	HtmlElement.timepicker({
		showCloseButton: true,
		showAnim: null, duration: null,
		timeSeparator: ':',
		showPeriodLabels: false,
		showPeriod: false,
		amPmText:['',''],
		rows: tmRows,
		hours: {
			starts: hStart,
			ends: hEnd
		},
		minutes: {
			starts: mStart,
			ends: mEnd,
			interval: mInterval
		},
		onClose: function(time, inst) {
			//--
			if(time != '') { //emulate on select because onSelect trigger twice (1 select hour + 2 select minutes), so if no time selected even if onClose means no onSelect !
				if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
					try {
						eval('(function(){ ' + evcode + ' })();'); // sandbox
					} catch(err) {
						alert('ERROR: JS-Eval Error on TimePicker: ' + elemID + '\nDetails: ' + err);
					} //end try catch
				} //end if
			} //end if
			//--
		} //end function
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

this.AutoComplete_Single = function(elem_id, data_url, var_term, min_src_term_len, evcode) {
	//--
	var HtmlElement = $('#' + elem_id);
	//--
	min_src_term_len = parseInt(min_src_term_len);
	if(min_src_term_len < 1) {
		min_src_term_len = 1;
	} //end if
	if(min_src_term_len > 255) {
		min_src_term_len = 255;
	} //end if
	//--
	if((typeof var_term == 'undefined') || (var_term == 'undefined') || (var_term == null) || (var_term == '')) {
		var_term = 'undefined_search_term_url_variable';
	} //end if
	//--
	HtmlElement.bind("keydown", function(event) {
		if(event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
			event.preventDefault(); // don't navigate away from the field on tab when selecting an item
		} //end if
	}).autocomplete({
		timeout: 0,
		delay: 250,
		source: function(request, response) {
			var ajax = SmartJS_BrowserUtils.Ajax_XHR_Request_From_URL(
				''+data_url,
				'POST',
				'json',
				'&'+var_term+'='+encodeURIComponent(SmartJS_CoreUtils.arrayGetLast(SmartJS_CoreUtils.stringSplitbyComma(request.term)))
			);
			ajax.done(function(msg) { // {{{JQUERY-AJAX}}}
				response(msg); // this will bind json to the autocomplete
			}).fail(function(msg) {
				alert('AutoComplete_Single: FAILED to fetch data for Element: ' + elem_id);
			});
		},
		search: function() {
			// custom minLength
			var term = SmartJS_CoreUtils.arrayGetLast(SmartJS_CoreUtils.stringSplitbyComma(HtmlElement.val()));
			if(term.length < min_src_term_len) {
				return false;
			}
		},
		focus: function() {
			// prevent value inserted on focus
			return false;
		},
		select: function(event, ui) {
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					eval('(function(){ ' + evcode + ' })();'); // sandbox
				} catch(err) {
					alert('AutoComplete_Single ERROR: JS-Eval Error on Element: ' + elem_id + '\nDetails: ' + err);
				} //end try catch
			} else {
				try {
					HtmlElement.val(''+ui.item.value); // on select replace element value with the selected item
				} catch(err) {
					alert('AutoComplete_Single: ERROR ... could not bind value to Element: ' + elem_id);
				}
			} //end if else
			return false;
		}
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

this.AutoComplete_Multi = function(elem_id, data_url, var_term, min_src_term_len, evcode) {
	//--
	var HtmlElement = $('#' + elem_id);
	//--
	min_src_term_len = parseInt(min_src_term_len);
	if(min_src_term_len < 1) {
		min_src_term_len = 1;
	} //end if
	if(min_src_term_len > 255) {
		min_src_term_len = 255;
	} //end if
	//--
	if((typeof var_term == 'undefined') || (var_term == 'undefined') || (var_term == null) || (var_term == '')) {
		var_term = 'undefined_search_term_url_variable';
	} //end if
	//--
	HtmlElement.bind("keydown", function(event) {
		if(event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
			event.preventDefault(); // don't navigate away from the field on tab when selecting an item
		} //end if
	}).autocomplete({
		timeout: 0,
		delay: 250,
		source: function(request, response) {
			var ajax = SmartJS_BrowserUtils.Ajax_XHR_Request_From_URL(
				''+data_url,
				'POST',
				'json',
				'&'+var_term+'='+encodeURIComponent(SmartJS_CoreUtils.arrayGetLast(SmartJS_CoreUtils.stringSplitbyComma(request.term)))
			);
			ajax.done(function(msg) { // {{{JQUERY-AJAX}}}
				response(msg); // this will bind json to the autocomplete
			}).fail(function(msg) {
				alert('AutoComplete_Multi: FAILED to fetch data for Element: ' + elem_id);
			});
		},
		search: function() {
			// custom minLength
			var term = SmartJS_CoreUtils.arrayGetLast(SmartJS_CoreUtils.stringSplitbyComma(HtmlElement.val()));
			if(term.length < min_src_term_len) {
				return false;
			}
		},
		focus: function() {
			// prevent value inserted on focus
			return false;
		},
		select: function(event, ui) {
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					eval('(function(){ ' + evcode + ' })();'); // sandbox
				} catch(err) {
					alert('AutoComplete_Multi ERROR: JS-Eval Error on Element: ' + elem_id + '\nDetails: ' + err);
				} //end try catch
			} else {
				try {
					var terms = SmartJS_CoreUtils.stringSplitbyComma(HtmlElement.val());
					terms.pop(); // remove the current input
					var found = 0;
					if(terms.length > 0) {
						for(var i=0; i<terms.length; i++) {
							if(terms[i] == ui.item.value) {
								found = 1;
								break;
							} //end if
						} //end for
					} //end if
					if(found == 0) {
						terms.push(ui.item.value); // add the selected item
					} //end if
					terms.push(''); // add placeholder to get the comma-and-space at the end
					HtmlElement.val(''+terms.join(', '));
				} catch(err) {
					alert('AutoComplete_Multi: ERROR ... could not bind value to Element: ' + elem_id);
				}
			} //end if else
			return false;
		}
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

this.Tabs_Init = function(tabs_id, tab_selected, prevent_reload) {
	//--
	tab_selected = parseInt(tab_selected);
	if(tab_selected < 0) {
		tab_selected = 0;
	} //end if
	//--
	var HtmlElement = $('#' + tabs_id);
	//--
	HtmlElement.tabs({
		active: tab_selected,
		select: function(event, ui) {},
		beforeLoad: function(event, ui) {
			if(prevent_reload === true) {
				if(ui.tab.data('loaded')) {
					event.preventDefault();
					return;
				}
				ui.jqXHR.done(function() { // {{{JQUERY-AJAX}}} :: instead of .success is deprecated
					ui.tab.data('loaded', true);
				});
			} //end if
			if(!ui.tab.data('loaded')) {
				$('#smartframeworkcomponents_jquery_tabs_loader').remove();
				$('<div id="smartframeworkcomponents_jquery_tabs_loader" style="width:250px; position:absolute; top:37px; right:0px; text-align:center;"><img src="lib/js/framework/img/loading_imodal.gif" alt="... loading Tab data ..."></div>').appendTo('#' + tabs_id);
				//ui.ajaxSettings.type = 'GET';
				//ui.ajaxSettings.async = true;
				//ui.ajaxSettings.cache = true;
				//ui.ajaxSettings.timeout = 0;
				//ui.jqXHR.error(function() { // .error() is deprecated in the favour of .fail()
				ui.jqXHR.fail(function() {
					SmartJS_BrowserUIUtils.DialogAlert('<h1>WARNING: Asyncronous Load Timeout or URL is broken !</h1>', '$(\'#smartframeworkcomponents_jquery_tabs_loader\').remove();', 'TAB #' + (parseInt($(ui.tab).index()) + 1) + ' :: ' + $(ui.tab).text());
				});
			} //end if
		},
		load: function(event, ui) {
			$('#smartframeworkcomponents_jquery_tabs_loader').remove();
		}
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

this.Tabs_Activate = function(tabs_id, activation) {
	//--
	var HtmlElement = $('#' + tabs_id);
	//--
	if(activation === false) {
		HtmlElement.tabs('disable');
	} else {
		HtmlElement.tabs('enable');
	} //end if else
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

} //END CLASS

//==================================================================
//==================================================================


// #END
