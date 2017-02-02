
// [LIB - SmartFramework / JS / Browser UI Utils - LightJsUI]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.6 r.2017.02.02 / smart.framework.v.2.3

// DEPENDS: jQuery, SmartJS_CoreUtils, SmartJS_BrowserUtils, jQuery.SimpleDialog, jQuery.ListSelect, jQuery.DatePicker, jQuery.TimePicker, jQuery.Tabs, jQuery.AutoSuggest

// v.160927

//==================================================================
//==================================================================

var SmartJS_BrowserUIUtils = new function() { // START CLASS

this.overlayCssClass = 'simpledialog-overlay'; // optional: overlay integration

//=======================================

// SYNC WITH: SmartJS_BrowserUtils.alert_Dialog()
// Dependencies:
//	jQuery
//	lib/js/jquery/dialog/simple-dialog.js
//	lib/js/jquery/dialog/simple-dialog.css
this.DialogAlert = function(y_message_html, evcode, y_title, y_width, y_height) {
	//--
	return SmartSimpleDialog.Dialog_Alert(y_message_html, evcode, y_title, y_width, y_height);
	//--
} //END FUNCTION

//=======================================

// SYNC WITH: SmartJS_BrowserUtils.confirm_Dialog()
// Dependencies:
//	jQuery
//	lib/js/jquery/dialog/simple-dialog.js
//	lib/js/jquery/dialog/simple-dialog.css
this.DialogConfirm = function(y_question_html, evcode, y_title, y_width, y_height) {
	//--
	return SmartSimpleDialog.Dialog_Confirm(y_question_html, evcode, y_title, y_width, y_height);
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
// 	lib/js/jquery/listselect/css/chosen.css
// 	lib/js/jquery/listselect/chosen.jquery.js
this.Smart_SelectList = function(elemID, dimW, dimH, allowMulti, useFilter) {
	//--
	var HtmlElement = $('#' + elemID);
	//--
	var disable_search = ! useFilter;
	HtmlElement.chosen({
		allow_single_deselect: true,
		disable_search_threshold: 10,
		enable_split_word_search: false,
		search_contains: true,
		no_results_text: 'Nothing found!',
		disable_search: disable_search,
		width: dimW
		// unused: dimH
	}).on('change', function(evt, params) {
		evt.preventDefault();
		var evcode = HtmlElement.attr('onBlur'); // onChange is always triggered, but useless on Multi-Select Lists on which we substitute it with the onBlur which is not triggered here but we catch and execute here
		if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
			try {
				eval('(function(){ ' + evcode + ' })();'); // sandbox
			} catch(err) {
				alert('ERROR: JS-Eval Error on Single-SelectList: ' + elemID + '\nDetails: ' + err);
			} //end try catch
		} //end if
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/datepicker/css/{theme}.css
//	lib/js/jquery/datepicker/jquery-zdatepicker.js
this.Date_Picker_Init = function(elemID, dateFmt, selDate, calStart, calMinDate, calMaxDate, noOfMonths, evcode) {
	//--
	// TODO: if possible show multiple months: noOfMonths
	//--
	var the_initial_date = '' + selDate;
	var the_initial_altdate = '';
	if(the_initial_date != '') {
		$('#date-bttn-' + elemID).attr('title', '' + selDate);
		the_initial_altdate = SmartJS_CoreUtils.formatDate('' + dateFmt, new Date(the_initial_date));
		$('#date-entry-' + elemID).val(the_initial_altdate);
	} //end if
	//--
	if((typeof calMinDate != 'undefined') || (calMinDate == 'undefined') || (calMinDate = '') || (calMinDate == null)) {
		calMinDate = SmartJS_CoreUtils.determineDate(calMinDate);
		if(calMinDate == null) {
			calMinDate = false;
		} else {
			calMinDate = SmartJS_CoreUtils.formatDate('yy-mm-dd', calMinDate);
		} //end if
	} else {
		calMinDate = false;
	} //end if else
	if((typeof calMaxDate != 'undefined') || (calMaxDate == 'undefined') || (calMaxDate = '') || (calMaxDate == null)) {
		calMaxDate = SmartJS_CoreUtils.determineDate(calMaxDate);
		if(calMaxDate == null) {
			calMaxDate = false;
		} else {
			calMaxDate = SmartJS_CoreUtils.formatDate('yy-mm-dd', calMaxDate);
		} //end if
	} else {
		calMaxDate = false;
	} //end if else
	//--
	var HtmlElement = $('#' + elemID);
	var AltElement = 'date-entry-' + elemID;
	//--
	HtmlElement.val(the_initial_date).Zebra_DatePicker({
		strict: true,
		readonly_element: true,
		default_position: 'below',
		first_day_of_week: calStart,
		format: 'Y-m-d',
		show_clear_date: false,
		show_icon: false,
		inside: false,
		show_other_months: true,
		show_week_number: '#',
		alternate_container: AltElement,
		direction: [calMinDate, calMaxDate],
		//start_date: the_initial_date, // no need will get value from the field
		onSelect: function(inst, date) {
			//--
			$('#date-bttn-' + elemID).attr('title', date);
			var altdate = date;
			try {
				altdate = SmartJS_CoreUtils.formatDate('' + dateFmt, new Date(date));
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
		}
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/datepicker/css/{theme}.css
//	lib/js/jquery/datepicker/jquery-zdatepicker.js
this.Date_Picker_Display = function(datepicker_id) {
	//--
	var HtmlElement = $('#' + datepicker_id);
	//--
	HtmlElement.data('Zebra_DatePicker').show();
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/ui/timepicker/jquery.ui.timepicker.css
//	lib/js/jquery/ui/timepicker/jquery.ui.timepicker.js
//	lib/js/jquery/ui/timepicker/i18n/jquery.ui.timepicker-{lang}.js
this.Time_Picker_Init = function(elemID, hStart, hEnd, mStart, mEnd, mInterval, tmRows, evcode) {
	//--
	var HtmlElement = $('#' + elemID);
	//--
	HtmlElement.timepicker({
		defaultTime: '', // this must superset the default now() when now() is not in allowed h/m
		showOn: 'button',
		showCloseButton: false,
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
		onSelect: function(time, inst) {
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

// Dependencies:
//	jQuery
//	lib/js/jquery/ui/timepicker/jquery.ui.timepicker.css
//	lib/js/jquery/ui/timepicker/jquery.ui.timepicker.js
//	lib/js/jquery/ui/timepicker/i18n/jquery.ui.timepicker-{lang}.js
this.Time_Picker_Display = function(timepicker_id) {
	//--
	var HtmlElement = $('#' + timepicker_id);
	//--
	HtmlElement.timepicker('show');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/tabs/jquery.tabs.css
//	lib/js/jquery/tabs/jquery.tabs.js
this.Tabs_Init = function(tabs_id, tab_selected, prevent_reload) {
	//--
	tab_selected = parseInt(tab_selected);
	if(tab_selected < 0) {
		tab_selected = 0;
	} //end if
	//--
	return SmartSimpleTabs.initTabs(tabs_id, prevent_reload, tab_selected);
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/tabs/jquery.tabs.css
//	lib/js/jquery/tabs/jquery.tabs.js
this.Tabs_Activate = function(tabs_id, activation) {
	//--
	if(activation !== false) {
		activation = true;
	} //end if
	//--
	return SmartSimpleTabs.activateTabs(tabs_id, activation)
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/autosuggest/smart-suggest.css
//	lib/js/jquery/autosuggest/smart-suggest.js
this.AutoCompleteField = function(single_or_multi, elem_id, data_url, var_term, min_term_len, evcode) {
	//--
	return SmartAutoSuggest.bindToInput(single_or_multi, elem_id, '', data_url+'&'+var_term+'=', false, null, min_term_len, evcode);
	//--
} //END FUNCTION

//=======================================

} //END CLASS

//==================================================================
//==================================================================


// #END
