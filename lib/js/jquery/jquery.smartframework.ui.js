
// [LIB - SmartFramework / JS / Browser UI Utils - LightJsUI]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7
// LICENSE: BSD

// DEPENDS: jQuery, SmartJS_CoreUtils, SmartJS_BrowserUtils, jQuery.SimpleDialog, jQuery.ListSelect, jQuery.DatePicker, jQuery.TimePicker, jQuery.Tabs, jQuery.AutoSuggest, jQuery.DataTable

//==================================================================
//==================================================================

var SmartJS_BrowserUIUtils = new function() { // START CLASS :: v.181206

this.overlayCssClass = 'simpledialog-overlay'; // optional: overlay integration

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/jquery.tiptop.css
//	lib/js/jquery/jquery.tiptop.js
this.ToolTip = function(selector) {
	//--
	var HtmlElement = jQuery(selector);
	var dataTooltipOk = 'tooltip-ok';
	//--
	HtmlElement.tipTop().data(dataTooltipOk, '1');
	//--
	jQuery('body').on('mousemove', selector, function(el) {
		jQuery(selector).each(function(index) {
			if(jQuery(this).data(dataTooltipOk)) {
				return;
			} //end if
			jQuery(this).tipTop().data(dataTooltipOk, '1');
		});
	});
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// SYNC WITH: SmartJS_BrowserUtils.alert_Dialog()
// Dependencies:
//	jQuery
//	lib/js/jquery/dialog/simple-dialog.js
//	lib/js/jquery/dialog/simple-dialog.css
this.DialogAlert = function(y_message_html, evcode, y_title, y_width, y_height) {
	//--
	// evcode params: -
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
	// evcode params: -
	//--
	return SmartSimpleDialog.Dialog_Confirm(y_question_html, evcode, y_title, y_width, y_height);
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery, SmartJS_CoreUtils
// 	lib/js/jquery/listselect/css/chosen.css
// 	lib/js/jquery/listselect/chosen.jquery.js
this.Smart_SelectList = function(elemID, dimW, dimH, allowMulti, useFilter) {
	//--
	// evcode is taken from onBlur ; evcode params: elemID
	//--
	var HtmlElement = jQuery('#' + elemID);
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
//	}).on('change', function(evt, params) {
	}).on('chosen:hiding_dropdown', function(evt, params) {
		evt.preventDefault();
		var evcode = HtmlElement.attr('onBlur'); // onChange is always triggered, but useless on Multi-Select Lists on which we substitute it with the onBlur which is not triggered here but we catch and execute here
		if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
			try {
				if(typeof evcode === 'function') {
					evcode(elemID); // call :: sync params ui-selectlist
				} else { // sync :: eliminate javascript:
					evcode = SmartJS_CoreUtils.stringTrim(evcode);
					evcode = evcode.replace('javascript:', '');
					evcode = SmartJS_CoreUtils.stringTrim(evcode);
					if((evcode != null) && (evcode != '')) {
						eval('(function(){ ' + evcode + ' })();'); // sandbox
					} //end if
				} //end if else
			} catch(err) {
				console.error('ERROR: JS-Eval Error on Smart-SelectList: ' + elemID + '\nDetails: ' + err);
			} //end try catch
		} //end if
	});
	HtmlElement.data('smart-ui-elem-type', 'chosen');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery, SmartJS_CoreUtils
//	lib/js/jquery/datepicker/css/{theme}.css
//	lib/js/jquery/datepicker/jquery-zdatepicker.js
// TODO: if possible show multiple months: noOfMonths
this.Date_Picker_Init = function(elemID, dateFmt, selDate, calStart, calMinDate, calMaxDate, noOfMonths, evcode) {
	//--
	// evcode params: date, altdate, inst, elemID
	//--
	var the_initial_date = String(selDate);
	var the_initial_altdate = '';
	if(the_initial_date != '') {
		jQuery('#date-bttn-' + elemID).attr('title', String(selDate));
		the_initial_altdate = SmartJS_CoreUtils.formatDate(String(dateFmt), new Date(the_initial_date));
		jQuery('#date-entry-' + elemID).val(the_initial_altdate);
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
	var HtmlElement = jQuery('#' + elemID);
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
			jQuery('#date-bttn-' + elemID).attr('title', date);
			var altdate = date;
			try {
				altdate = SmartJS_CoreUtils.formatDate(String(dateFmt), new Date(date));
				if(/Invalid|NaN/.test(altdate)) {
					altdate = date;
				} //end if
			} catch(err) {
				console.log('Date conversion is not supported by the browser. Using ISO Date');
			} //end try catch
			jQuery('#date-entry-' + elemID).val(altdate);
			//--
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					if(typeof evcode === 'function') {
						evcode(date, altdate, inst, elemID); // call :: sync params ui-datepicker
					} else {
						eval('(function(){ ' + evcode + ' })();'); // sandbox
					} //end if else
				} catch(err) {
					console.error('ERROR: JS-Eval Error on DatePicker: ' + elemID + '\nDetails: ' + err);
				} //end try catch
			} //end if
			//--
		}
	});
	HtmlElement.data('smart-ui-elem-type', 'Zebra_DatePicker');
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
	var HtmlElement = jQuery('#' + datepicker_id);
	//--
	if(HtmlElement.data('smart-ui-elem-type') !== 'Zebra_DatePicker') {
		return null;
	} //end if
	//--
	HtmlElement.data('Zebra_DatePicker').show();
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/timepicker/css/jquery.timepicker.css
//	lib/js/jquery/timepicker/jquery.timepicker.js
this.Time_Picker_Init = function(elemID, hStart, hEnd, mStart, mEnd, mInterval, tmRows, evcode) {
	//--
	// evcode params: time, inst, elemID
	//--
	var HtmlElement = jQuery('#' + elemID);
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
						if(typeof evcode === 'function') {
							evcode(time, inst, elemID); // call :: sync params ui-timepicker
						} else {
							eval('(function(){ ' + evcode + ' })();'); // sandbox
						} //end if else
					} catch(err) {
						console.error('ERROR: JS-Eval Error on TimePicker: ' + elemID + '\nDetails: ' + err);
					} //end try catch
				} //end if
			} //end if
			//--
		} //end function
	});
	HtmlElement.data('smart-ui-elem-type', 'timepicker');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/timepicker/css/jquery.timepicker.css
//	lib/js/jquery/timepicker/jquery.timepicker.js
this.Time_Picker_Display = function(timepicker_id) {
	//--
	var HtmlElement = jQuery('#' + timepicker_id);
	//--
	if(HtmlElement.data('smart-ui-elem-type') !== 'timepicker') {
		return null;
	} //end if
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
	// evcode params: id, value, label, data
	//--
	return SmartAutoSuggest.bindToInput(single_or_multi, elem_id, '', data_url+'&'+var_term+'=', false, null, min_term_len, evcode);
	//--
} //END FUNCTION


//=======================================

// Dependencies:
//	jQuery
//	lib/js/jquery/datatables/datatables-responsive.css
//	lib/js/jquery/datatables/datatables-responsive.js
this.Smart_DataTable_Init = function(elem_id, options) {
	//--
	if(!options || typeof options !== 'object') {
		options = {};
	} //end if
	//--
	if(!options.hasOwnProperty('responsive')) {
		options['responsive'] = false; // default not responsive (here responsive is something else ... will collapse rows under header with a + sign)
	} else {
		options['responsive'] = !(!options['responsive']); // force boolean
	} //end if
	//--
	if(!options.hasOwnProperty('filter')) {
		options['filter'] = true;
	} else {
		options['filter'] = !(!options['filter']); // force boolean
	} //end if
	//--
	if(!options.hasOwnProperty('sort')) {
		options['sort'] = true;
	} else {
		options['sort'] = !(!options['sort']); // force boolean
	} //end if
	//--
	if(!options.hasOwnProperty('paginate')) {
		options['paginate'] = true;
	} else {
		options['paginate'] = !(!options['paginate']); // force boolean
	} //end if
	//--
	if(!options.hasOwnProperty('pagesize')) {
		options['pagesize'] = 10;
	} else {
		options['pagesize'] = parseInt(options['pagesize']); // force integer
		if(options['pagesize'] < 1) {
			options['pagesize'] = 1;
		} //end if
	} //end if
	//--
	var defPageSizes = [ 10, 25, 50, 100 ]; // default array
	if(!options.hasOwnProperty('pagesizes')) {
		options['pagesizes'] = defPageSizes;
	} else if(!Array.isArray(options['pagesizes'])) {
		options['pagesizes'] = defPageSizes;
	} //end if else
	//--
	if(!(!!options.paginate)) {
		options['pagesize'] = Number.MAX_SAFE_INTEGER;
		options['pagesizes'] = [ Number.MAX_SAFE_INTEGER ];
	} //end if
	//--
	if(!options.hasOwnProperty('classField')) {
		options['classField'] = 'ux-field'; // default class
	} //end if
	//--
	if(!options.hasOwnProperty('classButton')) {
		options['classButton'] = 'ux-button ux-button-small'; // default class
	} //end if
	//--
	if(!options.hasOwnProperty('classActiveButton')) {
		options['classActiveButton'] = 'ux-button-primary'; // default class
	} //end if
	//--
	var ordCols = []; // default array
	if(!options.hasOwnProperty('colorder')) {
		options['colorder'] = ordCols;
	} else if(!Array.isArray(options['colorder'])) {
		options['colorder'] = ordCols;
	} //end if else
	//--
	var defCols = [{}]; // default array
	if(!options.hasOwnProperty('coldefs')) {
		options['coldefs'] = defCols;
	} else if(!Array.isArray(options['coldefs'])) {
		options['coldefs'] = defCols;
	} //end if else
	//--
	var opts = {
		responsive: 	!!options.responsive,
		bFilter: 		!!options.filter,
		bSort: 			!!options.sort,
		bSortMulti: 	!!options.sort,
		order: 			Array.from(options.colorder),
		bPaginate: 		!!options.paginate,
		iDisplayLength: parseInt(options.pagesize),
		aLengthMenu: 	Array.from(options.pagesizes, x => parseInt(x)),
		uxmHidePagingIfNoMultiPages: 	true,
		uxmCssClassLengthField: 		String(options.classField),
		uxmCssClassFilterField: 		String(options.classField),
		classes: {
			sPageButton: 		String(options.classButton),
			sPageButtonActive: 	String(options.classActiveButton)
		},
		columnDefs: 	Array.from(options.coldefs)
	};
	//--
	var HtmlElement = jQuery('table#' + elem_id);
	//--
	HtmlElement.DataTable(opts);
	HtmlElement.data('smart-ui-elem-type', 'DataTable');
	//--
	return HtmlElement;
	//--
} //END FUNCTION

//=======================================

// Dependencies:
//	jQuery, SmartJS_CoreUtils
//	lib/js/jquery/datatables/datatables-responsive.css
//	lib/js/jquery/datatables/datatables-responsive.js
this.Smart_DataTable_FilterColumns = function(elem_id, filterColNumber, regexStr) {
	//--
	var HtmlElement = jQuery('table#' + elem_id);
	//--
	if(HtmlElement.data('smart-ui-elem-type') !== 'DataTable') {
		return null;
	} //end if
	//--
	var obj = HtmlElement.DataTable();
	//--
	var col = parseInt(filterColNumber);
	if((col < 0) || !SmartJS_CoreUtils.isFiniteNumber(col)) {
		col = 0;
	} //end if
	if(regexStr) { // ex: '^(val1|val\-2)$'
		obj.columns(col).search(String(regexStr), true, false, true).draw();
	} else {
		obj.columns(col).search('').draw();
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
