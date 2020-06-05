
// [LIB - Smart.Framework / JS / Browser UI Utils - LightJsUI]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2
// LICENSE: BSD


//==================================================================
//==================================================================

/**
 * CLASS :: Browser UI Utils :: SimpleUI
 * This is a standalone implementation of the UI components (Javascript) in Smart.Framework
 * An alternate implementation 100% compatible is provided using jQueryUI and can be loaded by loading from Smart.Framework.Modules: modules/mod-ui-jqueryui/toolkit/jquery.smartframework.ui.js
 *
 * @package Sf.Javascript:UI
 *
 * @requires		jQuery
 * @requires		SmartJS_CoreUtils
 * @requires		SmartJS_DateUtils
 * @requires		SmartJS_BrowserUtils
 * @requires		jQuery.SimpleDialog
 * @requires		jQuery.ListSelect
 * @requires		jQuery.DatePicker
 * @requires		jQuery.TimePicker
 * @requires		jQuery.Tabs
 * @requires		jQuery.AutoSuggest
 * @requires		jQuery.DataTable
 *
 * @desc This JavaScript class provides methods to simplify implementation of several basic UI components.
 * @author unix-world.org
 * @license BSD
 * @file jquery.smartframework.ui.js
 * @version 20200121
 * @class SmartJS_BrowserUIUtils
 * @static
 *
 */
var SmartJS_BrowserUIUtils = new function() { // START CLASS

	/**
	 * Overlay CSS class
	 * @default 'simpledialog-overlay'
	 * @var {String} overlayCssClass
	 * @static
	 * @memberof SmartJS_BrowserUIUtils
	 */
	this.overlayCssClass = 'simpledialog-overlay';

	//=======================================

	/**
	 * Display a Tooltip ; UI Component
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/jquery.tiptop.css
	 * @requires lib/js/jquery/jquery.tiptop.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method ToolTip
	 * @static
	 *
	 * @param 	{String} 	selector 	:: The jQuery element selector ; Ex: '.class' or '#id'
	 * @return 	{Object} 				:: The jQuery HTML Element
	 */
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

	/**
	 * Display an Alert Dialog, with 1 button: OK ; UI Component
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/dialog/simple-dialog.css
	 * @requires lib/js/jquery/dialog/simple-dialog.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method DialogAlert
	 * @static
	 *
	 * @param 	{String} 		y_message_html 		:: Message to Display, HTML
	 * @param 	{Function} 		evcode 				:: The code to execute on press OK: function(){} or null
	 * @param 	{String} 		y_title 			:: The Title of Dialog, TEXT
	 * @param 	{Integer+} 		y_width 			:: The Dialog Width, *Optional*, Default: 550 (px)
	 * @param 	{Integer+} 		y_height 			:: The Dialog Height, *Optional*, Default: 225 (px)
	 * @return 	{Object} 							:: The jQuery HTML Element
	 */
	this.DialogAlert = function(y_message_html, evcode, y_title, y_width, y_height) {
		//--
		// KEEP SYNC WITH SmartJS_BrowserUtils.alert_Dialog()
		//--
		// evcode params: -
		//--
		return SmartSimpleDialog.Dialog_Alert(y_message_html, evcode, y_title, y_width, y_height);
		//--
	} //END FUNCTION

	//=======================================

	/**
	 * Display a Confirm Dialog, with 2 buttons: OK and Cancel ; UI Component
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/dialog/simple-dialog.css
	 * @requires lib/js/jquery/dialog/simple-dialog.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method DialogConfirm
	 * @static
	 *
	 * @param 	{String} 		y_question_html 	:: Message (Question) to Display, HTML
	 * @param 	{Function} 		evcode 				:: The code to execute on press OK: function(){} or null
	 * @param 	{String} 		y_title 			:: The Title of Dialog, TEXT
	 * @param 	{Integer+} 		y_width 			:: The Dialog Width, *Optional*, Default: 550 (px)
	 * @param 	{Integer+} 		y_height 			:: The Dialog Height, *Optional*, Default: 225 (px)
	 * @return 	{Object} 							:: The jQuery HTML Element
	 */
	this.DialogConfirm = function(y_question_html, evcode, y_title, y_width, y_height) {
		//--
		// KEEP SYNC WITH SmartJS_BrowserUtils.confirm_Dialog()
		//--
		// evcode params: -
		//--
		return SmartSimpleDialog.Dialog_Confirm(y_question_html, evcode, y_title, y_width, y_height);
		//--
	} //END FUNCTION

	//=======================================

	/**
	 * Display a Single or Multi Select List ; UI Component
	 *
	 * @hint onChange handler is taken from onBlur html attribute of the element this component binds to ; can be: function(elemID){} or null
	 *
	 * @requires jQuery
	 * @requires SmartJS_CoreUtils
	 * @requires lib/js/jquery/listselect/css/chosen.css
	 * @requires lib/js/jquery/listselect/chosen.jquery.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method Smart_SelectList
	 * @static
	 *
	 * @param 	{String} 		elemID 				:: The HTML Element ID to bind to (ussualy a real list single or multi)
	 * @param 	{Integer+} 		dimW 				:: The element Width (can be overriden with a CSS style !important)
	 * @param 	{Integer+} 		dimH 				:: The element Height (can be overriden with a CSS style !important)
	 * @param 	{Boolean} 		isMulti 			:: If the list is multi (TRUE) or single (FALSE)
	 * @param 	{Boolean} 		useFilter 			:: If TRUE will display a search filter list
	 * @return 	{Object} 							:: The jQuery HTML Element
	 */
	this.Smart_SelectList = function(elemID, dimW, dimH, isMulti, useFilter) {
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

	/**
	 * Initialize a Date Picker ; UI Component
	 * The selected date will be in ISO format as yyyy-mm-dd
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/datepicker/css/datepicker.css
	 * @requires lib/js/jquery/datepicker/datepicker.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method Date_Picker_Init
	 * @static
	 *
	 * @param 	{String} 		elemID 				:: The HTML Element ID to bind to (ussualy a text input)
	 * @param 	{String} 		dateFmt 			:: Calendar Date Format, used to display only ; Ex: 'dd.mm.yy'
	 * @param 	{String} 		selDate 			:: Calendar selected date, ISO format as yyyy-mm-dd or empty string
	 * @param 	{Integer+} 		calStart 			:: Calendar first day of week (0..6) ; 0 = Sunday, 1 = Monday ...
	 * @param 	{Mixed} 		calMinDate 			:: Calendar min date to display and allow selection ; Ex Object: new Date(1937, 1 - 1, 1) ; Ex String: '-1y -1m -1d'
	 * @param 	{Mixed} 		calMaxDate 			:: Calendar max date to display and allow selection ; Ex Object: new Date(2037, 12 - 1, 31) ; Ex String: '1y 1m 1d'
	 * @param 	{Integer+} 		noOfMonths 			:: Calendar number of months to display ; Default is 1
	 * @param 	{Function} 		evcode 				:: The code to execute on select: function(date, altdate, inst, elemID){} or null
	 * @return 	{Object} 							:: The jQuery HTML Date Picker
	 */
	this.Date_Picker_Init = function(elemID, dateFmt, selDate, calStart, calMinDate, calMaxDate, noOfMonths, evcode) {
		//--
		// TODO: if possible show multiple months: noOfMonths
		//--
		// evcode params: date, altdate, inst, elemID
		//--
		var the_initial_date = String(selDate);
		var the_initial_altdate = '';
		if(the_initial_date != '') {
			the_initial_altdate = SmartJS_DateUtils.formatDate(String(dateFmt), new Date(the_initial_date));
			jQuery('#date-entry-' + elemID).val(the_initial_altdate);
		} //end if
		//--
		if((typeof calMinDate != 'undefined') || (calMinDate == 'undefined') || (calMinDate = '') || (calMinDate == null)) {
			calMinDate = SmartJS_DateUtils.determineDate(calMinDate);
			if(calMinDate == null) {
				calMinDate = '';
			} else {
				calMinDate = SmartJS_DateUtils.formatDate('yy-mm-dd', calMinDate);
			} //end if
		} else {
			calMinDate = '';
		} //end if else
		if((typeof calMaxDate != 'undefined') || (calMaxDate == 'undefined') || (calMaxDate = '') || (calMaxDate == null)) {
			calMaxDate = SmartJS_DateUtils.determineDate(calMaxDate);
			if(calMaxDate == null) {
				calMaxDate = '';
			} else {
				calMaxDate = SmartJS_DateUtils.formatDate('yy-mm-dd', calMaxDate);
			} //end if
		} else {
			calMaxDate = '';
		} //end if else
		//--
		if(calMinDate) {
			calMinDate = new Date(calMinDate);
		} else {
			calMinDate = '';
		} //end if
		if(calMaxDate) {
			calMaxDate = new Date(calMaxDate);
		} else {
			calMaxDate = '';
		} //end if
		//--
		var HtmlElement = jQuery('#' + elemID);
		//--
		HtmlElement.val(the_initial_date).datepicker({
			//--
			keyboardNav: false,
			toggleSelected: false, // avoid unselect the selected date if clicking on it to avoid double hit for onSelect method with double click
			timepicker: false,
			inline: false,
			position: 'bottom left',
			offset: 12,
			todayButton: true,
			clearButton: true,
			showEvent: 'focus',
			autoClose: true,
			//--
			showOtherMonths: true,
			selectOtherMonths: true,
			moveToOtherMonthsOnSelect: false,
			showOtherYears: true,
			selectOtherYears: true,
			moveToOtherYearsOnSelect: false,
			//--
			weekends: [6, 0],
			firstDay: calStart,
			dateFormat: 'yyyy-mm-dd',
			altField: '#date-entry-' + elemID,
			altFieldDateFormat: 'yyyy-mm-dd',
			minDate: calMinDate,
			maxDate: calMaxDate,
			//--
			disableNavWhenOutOfRange: true,
			multipleDates: false,
			multipleDatesSeparator: ';',
			range: false,
			//--
			onSelect: function(date, altdate, inst) {
				//--
				altdate = date; // altdate is rewritten and re-converted below because format is different than standard if used
				try {
					altdate = SmartJS_DateUtils.formatDate(String(dateFmt), new Date(date));
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
		//--
		HtmlElement.data('smart-ui-elem-type', 'Air_DatePicker');
		//--
		return HtmlElement;
		//--
	} //END FUNCTION

	//=======================================

	/**
	 * Handle Display for an already Initialized Date Picker ; UI Component
	 * The selected date will be in ISO format as yyyy-mm-dd
	 *
	 * @example
	 * var selectedDate = ''; // or can be yyyy-mm-dd
	 * var elemID = 'myDatePicker';
	 * var html = '
	 * <span id="date-area-' + myDatePicker + '">
	 *   <input type="hidden" id="' + myDatePicker + '" name="date" value="' + selectedDate + '"><!-- holds the ISO formatted date -->
	 *   <input type="text" id="date-entry-' + myDatePicker + '" name="dtfmt__date" maxlength="13" value="' + selectedDate + '" readonly="readonly" class="datetime_Field_DatePicker" autocomplete="off"><!-- holds the custom formatted date -->
	 * </span>
	 * ';
	 * jQuery('body').append(html);
	 * SmartJS_BrowserUIUtils.Date_Picker_Init(elemID, 'dd.mm.yy', '', 1, '-1y -1m -1d', '1y 1m 1d', 1); // initialize the date picker
	 * jQuery('#date-entry-' + elemID).on('click', function(e){ SmartJS_BrowserUIUtils.Date_Picker_Display(elemID); })}); // show date picker on click over text input
	 * jQuery('#date-entry-' + elemID).on('dblclick doubletap', function(e){ jQuery('#' + elemID).val(''); jQuery(this).val(''); }); // reset on double click the text input
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/datepicker/css/datepicker.css
	 * @requires lib/js/jquery/datepicker/datepicker.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method Date_Picker_Display
	 * @static
	 *
	 * @param 	{String} 		datepicker_id 		:: The HTML Element ID to bind to (ussualy a text input) ; must be previous initialized with SmartJS_BrowserUIUtils.Date_Picker_Init()
	 * @return 	{Object} 							:: The jQuery HTML Date Picker
	 */
	this.Date_Picker_Display = function(datepicker_id) {
		//--
		var HtmlElement = jQuery('#' + datepicker_id);
		//--
		if(HtmlElement.data('smart-ui-elem-type') !== 'Air_DatePicker') {
			return null;
		} //end if
		//--
		HtmlElement.data('datepicker').show();
		//--
		return HtmlElement;
		//--
	} //END FUNCTION

	//=======================================

	/**
	 * Initialize a Time Picker ; UI Component
	 * The selected time will be in ISO format as hh:ii
	 * The selected value will be get directly from the `value` attribute of the html element is binded to <input id="MyTimePicker" type="text" value="12:30"> element
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/timepicker/css/jquery.timepicker.css
	 * @requires lib/js/jquery/timepicker/jquery.timepicker.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method Time_Picker_Init
	 * @static
	 *
	 * @param 	{String} 		elemID 				:: The HTML Element ID to bind to (ussualy a text input)
	 * @param 	{Integer+} 		hStart 				:: Time Start Hour ; 0..22
	 * @param 	{Integer+} 		hEnd 				:: Time End Hour ; 1..23
	 * @param 	{Integer+} 		mStart 				:: Time Start Minute ; 0..58
	 * @param 	{Integer+} 		mEnd 				:: Time End Minute ; 1..59
	 * @param 	{Integer+} 		mInterval 			:: Time Interval in Minutes ; 1..30
	 * @param 	{Integer+} 		tmRows 				:: Time Table Rows ; 1..5 ; Default is 2
	 * @param 	{Function} 		evcode 				:: The code to execute on select: function(time, inst, elemID){} or null
	 * @return 	{Object} 							:: The jQuery HTML Time Picker
	 */
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
			showDeselectButton: true,
			showAnim: null,
			duration: null,
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

	/**
	 * Handle Display for an already Initialized Time Picker ; UI Component
	 * The selected time will be in ISO format as hh:ii
	 *
	 * @example
	 * var selectedTime = ''; // or can be hh:ii
	 * var elemID = 'myTimePicker';
	 * var html = '
	 * <span id="time-area-' + elemID + '" title="[###TEXT-SELECT|html###] [HH:ii]">
	 *   <input type="text" name="time" id="' + elemID + '" maxlength="5" value="' + selectedTime + '" readonly="readonly" class="datetime_Field_TimePicker" autocomplete="off">
	 * </span>
	 * ';
	 * jQuery('body').append(html);
	 * SmartJS_BrowserUIUtils.Time_Picker_Init(elemID, 0, 23, 0, 59, 5, 2); // initialize the time picker
	 * jQuery('#' + elemID).on('click', function(e){ SmartJS_BrowserUIUtils.Time_Picker_Display(elemID); })}); // show time picker on click over text input
	 * jQuery('#' + elemID).on('dblclick doubletap', function(e){ jQuery(this).val(''); }); // reset on double click the text input
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/timepicker/css/jquery.timepicker.css
	 * @requires lib/js/jquery/timepicker/jquery.timepicker.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method Time_Picker_Display
	 * @static
	 *
	 * @param 	{String} 		timepicker_id 		:: The HTML Element ID to bind to (ussualy a text input) ; must be previous initialized with SmartJS_BrowserUIUtils.Time_Picker_Init()
	 * @return 	{Object} 							:: The jQuery HTML Time Picker
	 */
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

	/**
	 * Initialize Tabs Component ; UI Component
	 * The content of each tab (from the tabs component) can be loaded async by Ajax when the Tab is selected for display
	 *
	 * @example
	 * var tabs = '
	 * <div id="tabs_draw">
	 *   <div id="tabs_nav">
	 *     <li><a href="#tab-in-page">Tab with Content Preset</a></li>
	 *     <li><a href="?content=external-tab-content-load-by-ajax">Tab which loads contents by Ajax</a></li>
	 *   </div>
	 *   <div id="tab-in-page">
	 *     <h1>This is the content of the first tab ...</h1>
	 *   </div>
	 *   <!-- second tab does not to be set in HTML, will be created on the fly by the tabs component and populated with the HTML contents that comes by Ajax from a GET request on (example): ?content=external-tab-content-load-by-ajax -->
	 * </div>
	 * ';
	 * jQuery('body').append(html);
	 * SmartJS_BrowserUIUtils.Tabs_Init('tabs_draw');
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/tabs/jquery.tabs.css
	 * @requires lib/js/jquery/tabs/jquery.tabs.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method Tabs_Init
	 * @static
	 *
	 * @param 	{String} 	tabs_id 			:: The HTML Element ID to bind to
	 * @param 	{Integer+} 	tab_selected 		:: The selected tab number ; Default is zero
	 * @param 	{Boolean} 	prevent_reload		:: *Optional* ; Default is FALSE ; If TRUE the tab content will not be reloaded after the first load
	 * @return 	{Object} 						:: The jQuery HTML Element
	 */
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

	/**
	 * Activate or Deactivate the Tabs Component ; UI Component
	 * By default all the Tabs are active ; Use this function to deactivate and perhaps activate again
	 * When deactivated, only the current selected tab can be used
	 * This can be useful for using by example with edit operations to prevent switch tabs before saving the current form from the current active Tab
	 *
	 * @example
	 * SmartJS_BrowserUIUtils.Tabs_Activate(false); // deactivate tabs
	 * SmartJS_BrowserUIUtils.Tabs_Activate(true); // re-activate tabs
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/tabs/jquery.tabs.css
	 * @requires lib/js/jquery/tabs/jquery.tabs.js
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method Tabs_Activate
	 * @static
	 *
	 * @param 	{String} 	tabs_id 			:: The HTML Element ID to bind to ; must be previous initialized with SmartJS_BrowserUIUtils.Tabs_Init()
	 * @param 	{Boolean} 	activation			:: If FALSE the Tabs component will become inactive, except the current selected Tab ; when set back to TRUE will re-activate all tabs
	 * @return 	{Object} 						:: The jQuery HTML Element
	 */
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

	/**
	 * Creates a Single-Value or Multi-Value AutoComplete (AutoSuggest) Field ; UI Component
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/autosuggest/smart-suggest.css
	 * @requires lib/js/jquery/autosuggest/smart-suggest.js
	 *
	 * @example
	 * // the expected JSON structure that have to be served via the DataURL + VarTerm
	 * [
	 * 		{ "id":"id1", "value":"Value1","label":"Label1" },
	 * 		{ "id":"id2", "value":"Value2","label":"Label2" },
	 * 		// ...
	 * 		{ "id":"idN", "value":"ValueN","label":"LabelN" },
	 * ]
	 * // the DataURL + VarTerm controller must take care to return the values filetered by the value sent from the field via VarTerm via HTTP GET
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method AutoCompleteField
	 * @static
	 *
	 * @param 	{Enum} 		selector 			:: Type: can be: 'single' or 'multilist'
	 * @param 	{String} 	elem_id 			:: The HTML Element ID to bind to ; must be a text input or a text area
	 * @param 	{String} 	data_url 			:: The Data URL Prefix ; Ex: '?op=list&type=autosuggest'
	 * @param 	{String} 	var_term 			:: The Data URL Variable to be appended as suffix to the above Data URL ; Ex: 'searchTerm' (will use: '?op=list&type=autosuggest&searchTerm=')
	 * @param 	{Integer+} 	min_term_len 		:: The minimum term search length ; expects a value between 0..255 ; will start searching only after the typed term length matches this value
	 * @param 	{Function} 	evcode 				:: The code to execute on select: function(id, value, label, data){} or null
	 * @return 	{Object} 						:: The jQuery HTML Element
	 */
	this.AutoCompleteField = function(single_or_multi, elem_id, data_url, var_term, min_term_len, evcode) {
		//--
		// evcode params: id, value, label, data
		//--
		if(!data_url) {
			console.error('SmartJS_BrowserUIUtils.AutoCompleteField requires a non-empty dataUrl');
			return;
		} //end if
		//--
		var url = '';
		//--
		data_url = String(data_url);
		if(data_url.indexOf('?') != -1) {
			url = String(data_url) + '&';
		} else {
			url = String(data_url) + '?';
		} //end if else
		//--
		if(var_term) {
			url += String(var_term) + '=';
		} //end if
		//--
		return SmartAutoSuggest.bindToInput(single_or_multi, elem_id, '', url, false, null, min_term_len, evcode);
		//--
	} //END FUNCTION


	//=======================================

	/**
	 * Creates a DataTable from a regular HTML Table ; UI Component
	 * DataTables is a table enhancing plug-in for the jQuery Javascript library,
	 * adding sorting, paging and filtering abilities to plain HTML tables.
	 *
	 * @hint Add advanced interaction controls to HTML tables
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/datatables/datatables-responsive.css
	 * @requires lib/js/jquery/datatables/datatables-responsive.js
	 *
	 * @example
	 * // <!-- transform the following table into a DataTable with filtering, pagination, column ordering and many other features -->
	 * //<table id="myTable">
	 * // <thead>
	 * // 	<tr>
	 * // 		<th>Col1</th>
	 * // 		<th>Col2</th>
	 * // 	</tr>
	 * // </thead>
	 * // <tbody>
	 * // 	<tr>
	 * // 		<td>Col1</td>
	 * // 		<td>Col2</td>
	 * // 	</tr>
	 * // </tbody>
	 * // <tfoot>
	 * // 	<tr>
	 * // 		<th>Col1</th>
	 * // 		<th>Col2</th>
	 * // 	</tr>
	 * // </tfoot>
	 * //</table>
	 * //--
	 * SmartJS_BrowserUIUtils.Smart_DataTable_Init('myTable', {
	 * 		responsive: false, // if TRUE on responsive mode columns may become fluid on small screens
	 *		filter: true,
	 *		sort: true,
	 *		paginate: true,
	 * 		pagesize: 10,
	 * 		pagesizes: [ 10, 25, 50, 100 ],
	 * 		classField: 'ux-field', // css classes to display input fields (ex: filter)
	 * 		classButton: 'ux-button ux-button-small', // css classes to display the buttons
	 * 		classActiveButton: 'ux-button-primary', // css classes to display the active buttons
	 *		colorder: [
	 *			[ 0, 'asc' ], // [ 1, 'desc' ]
	 *		],
	 *		coldefs: [
	 *			{ // column one
	 *				targets: 0,
	 *				width: '25px',
	 * 				render: function(data, type, row) {
	 * 					if(type === 'type' || type === 'sort' || type === 'filter') { // preserve special objects from column render
	 * 						return data;
	 * 					} else { // customize the appearance of the 1st column
	 * 						return '<span style="color:#CCCCCC;">' + SmartJS_CoreUtils.escape_html(data) + '</span>';
	 * 					}
	 * 				}
	 * 				// for more options see: examples at https://github.com/DataTables/DataTables
	 *			},
	 *			{ // column two
	 *				targets: 1,
	 *				width: '275px'
	 *			}
	 *		]
	 * });
	 * //--
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method Smart_DataTable_Init
	 * @static
	 *
	 * @param 	{String} 	elem_id 			:: The HTML Element ID to bind to ; must be a HTML <table></table>
	 * @param 	{Object} 	options 			:: The Options for DataTable
	 * @return 	{Object} 						:: The jQuery HTML Element
	 */
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
			aLengthMenu: 	Array.from(options.pagesizes), // , x => parseInt(x)
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

	/**
	 * Programatically Filters a DataTable using a regular expression ; UI Component
	 *
	 * @requires jQuery
	 * @requires lib/js/jquery/datatables/datatables-responsive.css
	 * @requires lib/js/jquery/datatables/datatables-responsive.js
	 *
	 * @example
	 * // filter a DataTable by column no.1 (2nd column, starting from zero) and display only lines where column no.1 have the value: 'warning' or 'error'
	 * SmartJS_BrowserUIUtils.Smart_DataTable_FilterColumns('myTable', 1, '^(warning|error)$');
	 *
	 * @memberof SmartJS_BrowserUIUtils
	 * @method Smart_DataTable_FilterColumns
	 * @static
	 *
	 * @param 	{String} 	elem_id 			:: The HTML Element ID to bind to ; must be a DataTable already previous initiated with SmartJS_BrowserUIUtils.Smart_DataTable_Init()
	 * @param 	{Integer+} 	filterColNumber 	:: The DataTable column number 0..n
	 * @param 	{Regex} 	regexStr 			:: A valid Regex Partial Expression String (without enclosing slashes /../, as string) to filter the column values
	 * @return 	{Object} 						:: The jQuery HTML Element
	 */
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
