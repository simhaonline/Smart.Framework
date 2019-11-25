
// [LIB - Smart.Framework / JS / DateUtils]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

// DEPENDS: SmartJS_CoreUtils

//==================================================================
// This code is released under the BSD License. Copyright (c) unix-world.org
// This file contains portions of code from:
//	- https://github.com/joshduck/simple-day # A simple library for working with calendar days (YYYY-MM-DD) as plain old JavaScript objects.
//	- https://www.npmjs.com/package/date-offset @ http://howardhinnant.github.io/date_algorithms.html # A simple library for converting Gregorian dates to an integer offset.
//==================================================================

//================== [NO:evcode]

/**
 * CLASS :: Date Utils
 *
 * @package Sf.Javascript:Core
 *
 * @requires		SmartJS_CoreUtils
 *
 * @desc Date Utils class for Javascript
 * @author unix-world.org
 * @license BSD
 * @file date_utils.js
 * @version 20191123
 * @class SmartJS_DateUtils
 * @static
 *
 * @example
 * var d = new Date();
 * console.log(JSON.stringify(d, null, 2));
 *
 * var dz  = SmartJS_DateUtils.standardizeDate(d);
 * console.log(JSON.stringify(dz, null, 2));
 *
 * var ds  = SmartJS_DateUtils.standardizeDate({ year: d.getFullYear(), month: d.getMonth()+1, day: d.getDate() });
 * console.log(JSON.stringify(ds, null, 2));
 *
 * var iso = SmartJS_DateUtils.getIsoDate(ds);
 * console.log(iso);
 *
 * var d1 = SmartJS_DateUtils.createSafeDate(d.getFullYear(), d.getMonth()+1, d.getDate());
 * console.log(JSON.stringify(d1, null, 2));
 *
 * var d2 = SmartJS_DateUtils.createSafeDate(d.getFullYear(), (d.getMonth()+1)+3, d.getDate());
 * console.log(JSON.stringify(d2, null, 2));
 *
 * var o = SmartJS_DateUtils.calculateDaysOffset(d1, d2);
 * console.log(o);
 *
 * var o = SmartJS_DateUtils.calculateDaysOffset(d2, d1);
 * console.log(o);
 *
 * var m = SmartJS_DateUtils.calculateMonthsOffset(d1, d2);
 * console.log(m);
 *
 * var m = SmartJS_DateUtils.calculateMonthsOffset(d2, d1);
 * console.log(m);
 *
 * var a1 = SmartJS_DateUtils.addYears(ds, 1);
 * console.log(JSON.stringify(a1, null, 2));
 *
 * var a2 = SmartJS_DateUtils.addMonths(ds, 12);
 * console.log(JSON.stringify(a2, null, 2));
 *
 * var a3 = SmartJS_DateUtils.addDays(ds, 365);
 * console.log(JSON.stringify(a3, null, 2));
 */
var SmartJS_DateUtils = new function() { // START CLASS

	// :: static


	/**
	 * Create a Safe Date Object
	 *
	 * @memberof SmartJS_DateUtils
	 * @method createSafeDate
	 * @static
	 *
	 * @param 	{Integer} 	year 	The Raw Year: YYYY
	 * @param 	{Integer} 	month 	The Raw Month: 1..12 ; if wrong will fix ahead or behind
	 * @param 	{Integer} 	day 	The Raw Day: 1..31 ; if wrong will fix ahead or behind
	 * @return 	{Object} 			Normalized Date Object as: { year: YYYY, month: 1..12, day: 1..31 }
	 */
	this.createSafeDate = function(year, month, day) {
		//--
		return normalizeAndClone({
			year: year,
			month: month,
			day: day
		});
		//--
	} //END FUNCTION


	/**
	 * Normalize a Date Object
	 *
	 * @memberof SmartJS_DateUtils
	 * @method standardizeDate
	 * @static
	 *
	 * @param 	{Object} 	date 	The instanceof Date() or the Raw Date Object that need to be safe fixed as { year: YYYY, month: 1..12, day: 1..31 }
	 * @return 	{Object} 			Normalized Date Object as: { year: YYYY, month: 1..12, day: 1..31 }
	 */
	this.standardizeDate = function(date) {
		//--
		if(date instanceof Date) {
			var d = date;
			date = null;
			date = {
				year:  d.getFullYear(),
				month: d.getMonth()+1,
				day:   d.getDate()
			};
			d = null;
		} //end if
		//--
		return normalizeAndClone(date);
		//--
	} //END FUNCTION


	/**
	 * Get a Date Object as ISO
	 *
	 * @memberof SmartJS_DateUtils
	 * @method getIsoDate
	 * @static
	 *
	 * @param 	{Object} 	date 	The Raw Date Object as { year: YYYY, month: 1..12, day: 1..31 }
	 * @return 	{String} 			Normalized Date String as: YYYY-MM-DD
	 */
	this.getIsoDate = function(date) {
		//--
		if(date instanceof Date) {
			var d = date;
			date = null;
			date = {
				year:  d.getFullYear(),
				month: d.getMonth()+1,
				day:   d.getDate()
			};
			d = null;
		} //end if
		//--
		date = normalizeAndClone(date);
		//--
		var y = String(date.year);
		var m = String(addLeadingZero(date.month));
		var d = String(addLeadingZero(date.day));
		//--
		return String(y + '-' + m + '-' + d);
		//--
	} //END FUNCTION


	/**
	 * Calculate Days Offset between two dates
	 *
	 * @memberof SmartJS_DateUtils
	 * @method calculateDaysOffset
	 * @static
	 *
	 * @param 	{Object} 	sdate1 	Normalized Date #1 Object as: { year: YYYY, month: MM, day: DD }
	 * @param 	{Object} 	sdate2 	Normalized Date #2 Object as: { year: YYYY, month: MM, day: DD }
	 * @return 	{Integer} 			The Date Offset in seconds between sdate1 and sdate2 as: sdate2 - sdate1
	 */
	this.calculateDaysOffset = function(sdate1, sdate2) {
		//--
		var ofs1 = toOffset(sdate1.year, sdate1.month, sdate1.day);
		var ofs2 = toOffset(sdate2.year, sdate2.month, sdate2.day);
		//--
		return ofs2 - ofs1;
		//--
	} //END FUNCTION


	/**
	 * Calculate Months Offset between two dates
	 *
	 * @memberof SmartJS_DateUtils
	 * @method calculateMonthsOffset
	 * @static
	 *
	 * @param 	{Object} 	sdate1 	Normalized Date #1 Object as: { year: YYYY, month: MM, day: DD }
	 * @param 	{Object} 	sdate2 	Normalized Date #2 Object as: { year: YYYY, month: MM, day: DD }
	 * @return 	{Integer} 			The Date Offset in seconds between sdate1 and sdate2 as: sdate2 - sdate1
	 */
	this.calculateMonthsOffset = function(sdate1, sdate2) {
		//--
		var ofs = ((sdate2.year - sdate1.year) * 12) + (sdate2.month - sdate1.month);
		//--
		return ofs;
		//--
	} //END FUNCTION


	/**
	 * Add Years to a Date Object
	 *
	 * @memberof SmartJS_DateUtils
	 * @method addYears
	 * @static
	 *
	 * @param 	{Object} 	date 	The Raw Date Object as { year: YYYY, month: 1..12, day: 1..31 }
	 * @param 	{Integer} 	years 	The number of Years to add or substract
	 * @return 	{Object} 			Normalized Date Object as: { year: YYYY, month: MM, day: DD }
	 */
	this.addYears = function(date, years) {
		//--
		var sd = normalizeAndClone(date);
		sd.year += years;
		sd = clipDay(sd);
		//--
		return sd;
		//--
	} //END FUNCTION


	/**
	 * Add Months to a Date Object
	 *
	 * @memberof SmartJS_DateUtils
	 * @method addMonths
	 * @static
	 *
	 * @param 	{Object} 	date 	The Raw Date Object as { year: YYYY, month: 1..12, day: 1..31 }
	 * @param 	{Integer} 	months 	The number Months to add or substract
	 * @return 	{Object} 			Normalized Date Object as: { year: YYYY, month: MM, day: DD }
	 */
	this.addMonths = function(date, months) {
		//--
		var sd = normalizeAndClone(date);
		sd.month += months;
		sd = wrapMonth(sd);
		sd = clipDay(sd);
		//--
		return sd;
		//--
	} //END FUNCTION


	/**
	 * Add Days to a Date Object
	 *
	 * @memberof SmartJS_DateUtils
	 * @method addDays
	 * @static
	 *
	 * @param 	{Object} 	date 	The Raw Date Object as { year: YYYY, month: 1..12, day: 1..31 }
	 * @param 	{Integer} 	days 	The number Days to add or substract
	 * @return 	{Object} 			Normalized Date Object as: { year: YYYY, month: MM, day: DD }
	 */
	this.addDays = function(date, days) {
		//--
		var normalized = normalizeAndClone(date);
		var offset = toOffset(normalized.year, normalized.month, normalized.day);
		//--
		return toDate(offset + days);
		//--
	} //END FUNCTION


	/**
	 * Get the Number Of Days in a specific Month of the given Year
	 *
	 * @memberof SmartJS_DateUtils
	 * @method daysInMonth
	 * @static
	 *
	 * @param 	{Integer} 	year 	The Year to be tested
	 * @param 	{Integer} 	month 	The Month to be tested
	 * @return 	{Integer} 			the Number of Days in the tested month as: 28, 29, 30 or 31
	 */
	this.daysInMonth = function(year, month) {
		//--
		return inMonthDays(year, month);
		//--
	} //END FUNCTION


	/**
	 * Test if the given Year is a Leap Year or not
	 *
	 * @memberof SmartJS_DateUtils
	 * @method isLeapYear
	 * @static
	 *
	 * @param 	{Integer} 	year 	The Year to be tested
	 * @return 	{Boolean} 			TRUE if the Year is Leap or FALSE if is Not Leap
	 */
	this.isLeapYear = function(year) {
		//--
		return isYearLeap(year);
		//--
	} //END FUNCTION


	/**
	 * Format a date object into a string value.
	 * The format can be combinations of the following:
	 * d  - day of month (no leading zero) ;
	 * dd - day of month (two digit) ;
	 * o  - day of year (no leading zeros) ;
	 * oo - day of year (three digit) ;
	 * D  - day name short ;
	 * DD - day name long ;
	 * m  - month of year (no leading zero) ;
	 * mm - month of year (two digit) ;
	 * M  - month name short ;
	 * MM - month name long ;
	 * y  - year (two digit) ;
	 * yy - year (four digit) ;
	 * @ - Unix timestamp (ms since 01/01/1970) ;
	 * ! - Windows ticks (100ns since 01/01/0001) ;
	 * "..." - literal text ;
	 * '' - single quote ;
	 * @hint This is similar with jQueryUI formatDate
	 *
	 * @memberof SmartJS_DateUtils
	 * @method formatDate
	 * @static
	 *
	 * @param 	{String} 	format 			The desired format of the date
	 * @param 	{Date} 		date 			The date value to format, from Date() object
	 * @param 	{Object} 	settings 		Attributes include: dayNamesShort string[7] - abbreviated names of the days from Sunday (optional) ; dayNames string[7] - names of the days from Sunday (optional) ; monthNamesShort string[12] - abbreviated names of the months (optional) ; monthNames string[12] - names of the months (optional)
	 * @return 	{String} 					The date in the above format
	 */
	this.formatDate = function(format, date, settings) { // This function was taken from (c) jQueryUI/v1.12.0/2016-07-30
		//--
		if(!date) {
			return '';
		} //end if
		//--
		var defaultSettings = {
			monthNames: 		['January','February','March','April','May','June', 'July','August','September','October','November','December' ], // Names of months for drop-down and formatting
			monthNamesShort: 	['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ], // For formatting
			dayNames: 			['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ], // For formatting
			dayNamesShort: 		['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ] // For formatting
		};
		//--
		var iFormat,
			//--
			dayNamesShort = (settings ? settings.dayNamesShort : null) || defaultSettings.dayNamesShort,
			dayNames = (settings ? settings.dayNames : null) || defaultSettings.dayNames,
			monthNamesShort = (settings ? settings.monthNamesShort : null) || defaultSettings.monthNamesShort,
			monthNames = (settings ? settings.monthNames : null) || defaultSettings.monthNames,
			//-- ticks to 1970
			_ticksTo1970 = (((1970 - 1) * 365 + Math.floor(1970 / 4) - Math.floor(1970 / 100) + Math.floor(1970 / 400)) * 24 * 60 * 60 * 10000000),
			//-- Check whether a format character is doubled
			lookAhead = function(match) {
				var matches = (iFormat + 1 < format.length && format.charAt( iFormat + 1 ) === match);
				if(matches) {
					iFormat++;
				} //end if
				return matches;
			}, //end function
			//-- Format a number, with leading zero if necessary
			formatNumber = function(match, value, len) {
				var num = String(value);
				if(lookAhead(match)) {
					while(num.length < len) {
						num = '0' + num;
					} //end while
				} //end if
				return num;
			}, //end function
			//-- Format a name, short or long as requested
			formatName = function(match, value, shortNames, longNames) {
				return (lookAhead( match ) ? longNames[ value ] : shortNames[ value ]);
			}, //end function
			//--
			output = '',
			literal = false;
			//--
		//--
		if(date) {
			//--
			for(iFormat=0; iFormat<format.length; iFormat++) {
				//--
				if(literal) {
					//--
					if(format.charAt(iFormat) === "'" && !lookAhead("'")) {
						literal = false;
					} else {
						output += format.charAt(iFormat);
					} //end if else
					//--
				} else {
					//--
					switch(format.charAt(iFormat)) {
						case 'd':
							output += formatNumber('d', date.getDate(), 2);
							break;
						case 'D':
							output += formatName('D', date.getDay(), dayNamesShort, dayNames);
							break;
						case 'o':
							output += formatNumber('o', Math.round((new Date(date.getFullYear(), date.getMonth(), date.getDate()).getTime() - new Date(date.getFullYear(), 0, 0 ).getTime()) / 86400000), 3);
							break;
						case 'm':
							output += formatNumber('m', date.getMonth()+1, 2);
							break;
						case 'M':
							output += formatName('M', date.getMonth(), monthNamesShort, monthNames);
							break;
						case 'y':
							output += (lookAhead( 'y' ) ? date.getFullYear() : (date.getFullYear() % 100 < 10 ? '0' : '') + date.getFullYear() % 100);
							break;
						case '@':
							output += date.getTime();
							break;
						case '!':
							output += date.getTime() * 10000 + _ticksTo1970;
							break;
						case "'":
							if(lookAhead("'")) {
								output += "'";
							} else {
								literal = true;
							} //end if else
							break;
						default:
							output += format.charAt(iFormat);
					} //end switch
					//--
				} //end if else
				//--
			} //end for
			//--
		} //end if
		//--
		return String(output);
		//--
	} //END FUNCTION


	/**
	 * Determine a date by a Date object or Expression
	 * Valid date objects or expressions:
	 * new Date(1937, 1 - 1, 1) 	:: a date in the past, as object ;
	 * '-1y -1m -1d' 				:: a date in the past as relative expression to the defaultDate ;
	 * new Date(2037, 12 - 1, 31) 	:: a date in the future as object ;
	 * '1y 1m 1d' 					:: a date in the future as relative expression to the defaultDate ;
	 * @hint This is similar with jQueryUI determineDate
	 *
	 * @memberof SmartJS_DateUtils
	 * @method determineDate
	 * @static
	 *
	 * @param 	{Mixed} 	date 				The Date object or date relative expression to the defaultDate
	 * @param 	{Mixed} 	defaultDate 		*Optional* null (for today) or a Date object / timestamp as default (selected) date to be used for relative expressions
	 * @return 	{Mixed} 						A Date object or null if fails to validate expression
	 */
	this.determineDate = function(date, defaultDate) { // This function was taken from (c) jQueryUI/v1.12.0/2016-07-30
		//--
		if((typeof defaultDate == 'undefined') || (defaultDate == 'undefined') || (defaultDate == '') || (defaultDate == null)) {
			defaultDate = null; // fix by unixman
		} //end if
		//--
		var _daylightSavingAdjust = function(date) {
			if(!date) {
				return null;
			} //end if
			date.setHours(date.getHours() > 12 ? date.getHours() + 2 : 0);
			return date;
		} //end function
		//--
		var offsetNumeric = function(offset) {
			var date = new Date();
			date.setDate(date.getDate() + offset);
			return date;
		};
		//--
		var offsetString = function(offset) {
			var date = null;
			//if(offset.toLowerCase().match(/^c/)) {
			if(offset) { // fix by unixman
				date = defaultDate;
			} //end if
			if(date == null) {
				date = new Date();
			} //end if
			var year = date.getFullYear(),
				month = date.getMonth(),
				day = date.getDate();
			var pattern = /([+\-]?[0-9]+)\s*(d|D|w|W|m|M|y|Y)?/g,
				matches = pattern.exec(offset);
			while(matches) {
				switch(matches[2] || "d") {
					case "d":
					case "D":
						day += parseInt(matches[1], 10);
						break;
					case "w":
					case "W":
						day += parseInt(matches[1], 10) * 7;
						break;
					case "m":
					case "M":
						month += parseInt(matches[1], 10);
						day = Math.min(day, new Date(year, month+1, 0).getDate()); // 2nd param is get days in month
						break;
					case "y":
					case "Y":
						year += parseInt(matches[1], 10);
						day = Math.min(day, new Date(year, month+1, 0).getDate()); // 2nd param is get days in month
						break;
				} //end switch
				matches = pattern.exec(offset);
			} //end while
			//--
			return new Date(year, month, day);
			//--
		};
		//--
		var newDate = (date == null || date === '' ? defaultDate : (typeof date === 'string' ? offsetString(date) : (typeof date === 'number' ? (!SmartJS_CoreUtils.isFiniteNumber(date) ? defaultDate : offsetNumeric(date)) : new Date(date.getTime()))));
		newDate = (newDate && newDate.toString() === 'Invalid Date' ? defaultDate : newDate);
		if(newDate) {
			newDate.setHours(0);
			newDate.setMinutes(0);
			newDate.setSeconds(0);
			newDate.setMilliseconds(0);
		} //end if
		//--
		return _daylightSavingAdjust(newDate);
		//--
	} //END FUNCTION


	//===== PRIVATES


	// add leading zero: d_or_m is Integer
	var addLeadingZero = function(d_or_m) {
		//--
		if(d_or_m < 1) {
			d_or_m = 1;
		} //end if
		//--
		if(d_or_m < 10) {
			d_or_m = '0' + String(d_or_m);
		} //end if
		//--
		return String(d_or_m);
		//--
	} //END FUNCTION

	// normalize a date
	var normalizeAndClone = function(date) {
		//--
		var yearOffset = yearOffsetForMonth(date.month);
		var year = date.year + yearOffset;
		var month = date.month - yearOffset * 12;
		//--
		return toDate(toOffset(year, month, date.day));
		//--
	} //END FUNCTION


	// wraps a month
	var wrapMonth = function(date) {
		//--
		var yearOffset = yearOffsetForMonth(date.month);
		date.year += yearOffset;
		date.month -= yearOffset * 12;
		//--
		return date;
		//--
	} //END FUNCTION


	// clips a day
	var clipDay = function(date) {
		//--
		date.day = Math.min(date.day, inMonthDays(date.year, date.month));
		//--
		return date;
		//--
	} //END FUNCTION


	// get the Year offset for a specific Month
	var yearOffsetForMonth = function(month) {
		//--
		var ofs = 0;
		if(month > 12) {
			ofs = Math.ceil(month / 12) - 1;
		} else if(month < 1) {
			ofs = Math.floor((month - 1) / 12);
		} //end if else
		//--
		return ofs;
		//--
	} //END FUNCTION


	// Get the Number Of Days in a specific Month of the given Year
	var inMonthDays = function(year, month) {
		//--
		var d = DAYS_IN_MONTH[month - 1];
		if(month === 2 && isYearLeap(year)) {
			d = 29;
		} //end if
		//--
		return d;
		//--
	} //END FUNCTION
	var DAYS_IN_MONTH = [
		31,
		28,
		31,
		30,
		31,
		30,
		31,
		31,
		30,
		31,
		30,
		31,
	];


	// Test if the given Year is a Leap Year or not
	var isYearLeap = function(year) {
		//--
		var leap = true;
		if(year % 4 !== 0) {
			leap = false;
		} else if(year % 400 == 0) {
			leap = true;
		} else if(year % 100 == 0) {
			leap = false;
		} //end if else
		//--
		return leap;
		//--
	} //END FUNCTION


	// date-offset: calculate Y,M,D to Date
	var toDate = function(z) {
		//--
		z += 719468;
		//--
		var era = ((z >= 0 ? z : z - 146096) / 146097) | 0;
		var doe = z - era * 146097;                                                // [0, 146096]
		var yoe = Math.floor((doe - Math.floor(doe / 1460) + Math.floor(doe / 36524) - Math.floor(doe / 146096)) / 365);   // [0, 399]
		var y = yoe + era * 400;
		var doy = doe - (365 * yoe + Math.floor(yoe / 4) - Math.floor(yoe / 100)); // [0, 365]
		var mp = Math.floor((5 * doy + 2) / 153);                                  // [0, 11]
		var d = doy - Math.floor((153 * mp + 2) / 5) + 1;                          // [1, 31]
		var m = mp + (mp < 10 ? 3 : -9);                                           // [1, 12]
		//--
		return {
			year: y + (m <= 2),
			month: m,
			day: d
		};
		//--
	} //END FUNCTION


	// date-offset: calculate Y,M,D to Offset
	var toOffset = function(y, m, d) {
		//--
		y -= m <= 2;
		//--
		var era = ((y >= 0 ? y : y - 399) / 400) | 0;
		var yoe = y - era * 400;                                                   // [0, 399]
		var doy = Math.floor((153 * (m + (m > 2 ? -3 : 9)) + 2) / 5) + d - 1;      // [0, 365]
		var doe = yoe * 365 + Math.floor(yoe / 4) - Math.floor(yoe / 100) + doy;   // [0, 146096]
		//--
		return era * 146097 + doe - 719468;
		//--
	} //END FUNCTION


} //END CLASS


//==================================================================
//==================================================================

// #END
