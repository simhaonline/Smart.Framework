
// [LIB - SmartFramework / JS / Core Utils]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

// DEPENDS: -

//==================================================================
//==================================================================

//================== [NO:evcode]

/**
* CLASS :: Core Utils
*
* @class SmartJS_CoreUtils
* @static
*
* @module Smart.Framework/JS/Core
*/
var SmartJS_CoreUtils = new function() { // START CLASS :: v.170922

// :: static

var _class = this; // self referencing


/**
 * Check if a number is valid: Finite and !NaN
 *
 * @method isFiniteNumber
 * @static
 * @param 	{Number} 	num 	The number to be tested
 * @return 	{Boolean} 			TRUE is number is Finite and !NaN ; FALSE otherwise
 */
this.isFiniteNumber = function(num) { // http://stackoverflow.com/questions/5690071/why-check-for-isnan-after-isfinite
	//--
	return Boolean(isFinite(num) && !isNaN(num));
	//--
} //END FUNCTION


/**
 * Trim a string (at begining or end by any whitespace: space \ n \ r \ t)
 *
 * @method stringTrim
 * @static
 * @param 	{String} 	str 	The string to be trimmed
 * @return 	{String} 			The trimmed string
 */
this.stringTrim = function(str) {
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//--
	return String(str.replace(/^\s\s*/, '').replace(/\s\s*$/, '')); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Split string by comma with trimming pre/post
 *
 * @method stringSplitbyComma
 * @static
 * @param 	{String} 	str 	The string to be splitted by , (comma)
 * @return 	{Array} 			The array with string parts splitted
 */
this.stringSplitbyComma = function(str) {
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	str = _class.stringTrim(str);
	//--
	return str.split(/,\s*/); // Array
	//--
} //END FUNCTION


/**
 * Split string by semicolon with trimming pre/post
 *
 * @method stringSplitbySemicolon
 * @static
 * @param 	{String} 	str 		The string to be splitted by ; (semicolon)
 * @return 	{Array} 				The array with string parts splitted
 */
this.stringSplitbySemicolon = function(str) {
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	str = _class.stringTrim(str);
	//--
	return str.split(/;\s*/); // Array
	//--
} //END FUNCTION


/**
 * Replace all occurences in a string - Case Sensitive
 *
 * @method stringReplaceAll
 * @static
 * @param 	{String} 	token 		The string part to be replaced
 * @param 	{String} 	newToken 	The string part replacement
 * @param 	{String} 	str 		The string where to do the replacements
 * @return 	{String} 				The processed string
 */
this.stringReplaceAll = function(token, newToken, str) {
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//--
	return String(str.split(token).join(newToken)); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Replace all occurences in a string - Case Insensitive
 *
 * @method stringIReplaceAll
 * @static
 * @param 	{String} 	token 		The string part to be replaced
 * @param 	{String} 	newToken 	The string part replacement
 * @param 	{String} 	str 		The string where to do the replacements
 * @return 	{String} 				The processed string
 */
this.stringIReplaceAll = function(token, newToken, str) {
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//--
	var i = -1;
	//--
	if((str != '') && (typeof token === 'string') && (typeof newToken === 'string')) {
		//--
		token = token.toLowerCase();
		//--
		while((i = str.toLowerCase().indexOf(token, i >= 0 ? i + newToken.length : 0)) !== -1) {
			str = String(str.substring(0, i) + newToken + str.substring(i + token.length));
		} //end while
		//--
	} //end if
	//--
	return String(str); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Get the first element from an Array
 *
 * @method arrayGetFirst
 * @static
 * @param 	{Array} 	arr 		The array to be used
 * @return 	{Mixed} 				The first element from the array
 */
this.arrayGetFirst = function(arr) {
	//--
	if(arr instanceof Array) {
		return arr.shift(); // Mixed
	} else {
		return '';
	} //end if
	//--
} //END FUNCTION


/**
 * Get the last element from an Array
 *
 * @method arrayGetLast
 * @static
 * @param 	{Array} 	arr 		The array to be used
 * @return 	{Mixed} 				The last element from the array
 */
this.arrayGetLast = function(arr) {
	//--
	if(arr instanceof Array) {
		return arr.pop(); // Mixed
	} else {
		return '';
	} //end if
	//--
} //END FUNCTION


/**
 * Format a number as INTEGER
 *
 * @method format_number_int
 * @static
 * @param 	{Numeric} 	y_number 				A numeric value
 * @param 	{Boolean} 	y_allow_negatives 		If TRUE will allow negative values else will return just positive (unsigned) values
 * @return 	{Integer} 							An integer number
 */
this.format_number_int = function(y_number, y_allow_negatives) {
	//--
	if((typeof y_number == 'undefined') || (y_number == null) || (y_number == '') || (!_class.isFiniteNumber(y_number))) {
		y_number = 0;
	} //end if
	//--
	if(y_allow_negatives !== true) {
		y_allow_negatives = false;
	} //end if
	//--
	y_number = parseInt(String(y_number));
	if(!_class.isFiniteNumber(y_number)) {
		y_number = 0;
	} //end if
	//--
	if(y_allow_negatives !== true) { // force as positive
		if(y_number < 0) {
			y_number = parseInt(-1 * y_number);
		} //end if
		if(!_class.isFiniteNumber(y_number)) {
			y_number = 0;
		} //end if
		if(y_number < 0) {
			y_number = 0;
		} //end if
	} //end if
	//--
	return y_number; // Integer
	//--
} //END FUNCTION


/**
 * Format a number as DECIMAL
 *
 * @method format_number_dec
 * @static
 * @param 	{Numeric} 	y_number 					A numeric value
 * @param 	{Integer} 	y_decimals 					The number of decimal to use (between 1 and 4)
 * @param 	{Boolean} 	y_allow_negatives 			*Optional* If TRUE will allow negative values else will return just positive (unsigned) values
 * @param 	{Boolean} 	y_keep_trailing_zeroes 		*Optional* If set to TRUE will keep trailing zeroes, otherwise will discard them
 * @return 	{Integer} 								A decimal number
 */
this.format_number_dec = function(y_number, y_decimals, y_allow_negatives, y_keep_trailing_zeroes) {
	//--
	if((typeof y_number == 'undefined') || (y_number == null) || (y_number == '') || (!_class.isFiniteNumber(y_number))) {
		y_number = 0;
	} //end if
	//--
	if((typeof y_decimals == 'undefined') || (y_decimals == null) || (y_decimals == '')) {
		y_decimals = 2; // default;
	} //end if
	y_decimals = parseInt(y_decimals);
	if(!_class.isFiniteNumber(y_decimals)) {
		y_decimals = 2;
	} //end if
	if((y_decimals < 1) || (y_decimals > 4)) {
		y_decimals = 2;
	} //end if
	//--
	if(y_allow_negatives !== true) {
		y_allow_negatives = false;
	} //end if
	//--
	y_number = parseFloat(String(y_number)).toFixed(y_decimals);
	if(!_class.isFiniteNumber(y_number)) {
		y_number = parseFloat(0).toFixed(y_decimals);
	} //end if
	//--
	if(y_allow_negatives !== true) { // force as positive
		if(y_number < 0) {
			y_number = parseFloat(-1 * y_number).toFixed(y_decimals);
		} //end if
		if(!_class.isFiniteNumber(y_number)) {
			y_number = parseFloat(0).toFixed(y_decimals);
		} //end if
		if(y_number < 0) {
			y_number = parseFloat(0).toFixed(y_decimals);
		} //end if
	} //end if
	//-- remove trailing zeroes if not set to keep them
	if(y_keep_trailing_zeroes !== false) {
		y_number = parseFloat(y_number);
	} //end if
	//--
	return y_number; // Integer
	//--
} //END FUNCTION


/**
 * Add the Thousands Separator (comma ,) to a number
 *
 * @method add_number_ThousandsSeparator
 * @static
 * @param 	{Numeric} 	num 		The number to be formatted
 * @return 	{String} 				The formatted number as string with comma as thousands separator if apply (will keep the . dot as decimal separator if apply)
 */
this.add_number_ThousandsSeparator = function(num) {
	//--
	num = String(num); // this is a special case
	//--
	var parts = num.split('.');
	parts[0] = parts[0].replace(/(\d)(?=(\d{3})+$)/g, '$1,'); // add thousands separator
	//--
	return String(parts.join('.')); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Safe escape URL Variable (using RFC3986 standards to be full Unicode compliant).
 * This is a shortcut to the encodeURIComponent() to provide a standard into Smart.Framework/JS.
 *
 * @method escape_url
 * @static
 * @param 	{String} 	str 		The URL variable value to be escaped
 * @return 	{String} 				The escaped URL variable
 */
this.escape_url = function(str) {
	//-- format sting
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//--
	str = String(encodeURIComponent(str));
	//-- fixes to make it more compliant with it RFC 3986
	str = str.replace('!', '%21');
	str = str.replace("'", '%27');
	str = str.replace('(', '%28');
	str = str.replace(')', '%29');
	str = str.replace('*', '%2A');
	//--
	return String(str); // fix to return empty string instead of null
	//--
} //END FUNCTION


/*
 * Convert special characters to HTML entities.
 * This is like the Smart::escape_html() from the PHP Smart.Framework.
 * Depending on the flag parameter, the following values will be converted to safe HTML entities:
 * 		ENT_COMPAT: 	< > & "
 * 		ENT_QUOTES: 	< > & " '
 * 		ENT_NOQUOTES: 	< > &
 *
 * @method htmlspecialchars
 * @static
 * @param 	{String} 	str 		The string to be escaped
 * @param 	{Enum} 		flag 		*Optional* A bitmask of one or more of the following flags: ENT_COMPAT (default) ; ENT_QUOTES ; ENT_NOQUOTES
 * @return 	{String} 				The safe escaped string to be injected in HTML code
 */
this.htmlspecialchars = function(str, flag) { // v.170308
	//-- format sting
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//-- format flag
	if((typeof flag == 'undefined') || (flag == undefined) || (flag == null) || (flag == '')) {
		flag = 'ENT_COMPAT';
	} //end if
	//-- replace basics
	str = str.replace(/&/g, '&amp;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');
	//-- replace quotes, depending on flag
	if(flag == 'ENT_QUOTES') { // ENT_QUOTES
		//-- replace all quotes: ENT_QUOTES
		str = str.replace(/"/g, '&quot;');
		str = str.replace(/'/g, '&#039;');
		//--
	} else if (flag != 'ENT_NOQUOTES') { // ENT_COMPAT
		//-- default, replace just double quotes
		str = str.replace(/"/g, '&quot;');
		//--
	} //end if else
	//--
	return String(str); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Convert special characters to HTML entities.
 * This is like the Smart::escape_html() from the PHP Smart.Framework.
 * These values will be converted to safe HTML entities: < > & "
 *
 * @method escape_html
 * @static
 * @param 	{String} 	str 		The string to be escaped
 * @return 	{String} 				The safe escaped string to be injected in HTML code
 */
this.escape_html = function(str) { // v.170308
	//--
	return String(_class.htmlspecialchars(str, 'ENT_COMPAT'));
	//--
} //END FUNCTION


/**
 * Convert special characters to escaped entities for safe use with Javascript Strings.
 * This is like the Smart::escape_js() from the PHP Smart.Framework.
 *
 * @method escape_js
 * @static
 * @param 	{String} 	str 		The string to be escaped
 * @return 	{String} 				The escaped string using the json encode standard to be injected between single quotes '' or double quotes ""
 */
this.escape_js = function(str) { // v.170831
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//-- sub-function to escape a string as unicode
	var escape_unicode = function(str) {
		str = String(str);
		return String('\\u' + ('0000' + str.charCodeAt(0).toString(16)).slice(-4).toLowerCase());
	} //END FUNCTION
	//-- table of character substitutions: get from json2.js but excludding the " which is done later to preserve compatibility with PHP
	var meta = {
		'\b': '\\b',
		'\t': '\\t',
		'\n': '\\n',
		'\f': '\\f',
		'\r': '\\r',
		'\\': '\\\\'
	};
	//-- replace meta
	var encoded = str.replace(/[\x00-\x1f\x7f-\x9f\\]/g, function(a){ var c = meta[a]; return typeof c === 'string' ? c: escape_unicode(a); });
	//-- replace unicode characters
	encoded = encoded.replace(/[\u007F-\uFFFF]/g, function(c){ return escape_unicode(c); });
	//-- replace special characters (use uppercase unicode escapes as in PHP ; example: u003C / u003E )
	encoded = encoded.replace(/[\u0026]/g, '\\u0026');	// & 	JSON_HEX_AMP
	encoded = encoded.replace(/[\u0022]/g, '\\u0022');	// " 	JSON_HEX_QUOT
	encoded = encoded.replace(/[\u0027]/g, '\\u0027');	// ' 	JSON_HEX_APOS
	encoded = encoded.replace(/[\u003C]/g, '\\u003C'); 	// < 	JSON_HEX_TAG
	encoded = encoded.replace(/[\u003E]/g, '\\u003E'); 	// > 	JSON_HEX_TAG
	encoded = encoded.replace(/[\/]/g,     '\\/');	    // / 	JSON_UNESCAPED_SLASHES
	//-- return string
	return String(encoded); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Replace new lines \ r \ n ; \ n with the <br> html tag.
 * This is compatible with the PHP nl2br() function.
 *
 * @method nl2br
 * @static
 * @param {String} str The string to be processed
 * @return {String} The processed string with <br> html tags if new lines were detected
 */
this.nl2br = function(str) {
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//--
	return String(str.replace(/\r\n/g, /\n/).replace(/\r/g, /\n/).replace(/\n/g, '<br>')); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Capitalize the first letter of a string.
 * This is compatible with the PHP ucfirst() function.
 *
 * @method ucfirst
 * @static
 * @param {String} str The string to be processed
 * @return {String} The processed string with 1st letter capitalized
 */
this.ucfirst = function(str) {
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//--
	return String(str.charAt(0).toUpperCase() + str.slice(1));
	//--
} //END FUNCTION


/**
 * Un-quotes a quoted string.
 * This is compatible with PHP stripslashes() function.
 *
 * @method stripslashes
 * @static
 * @param {String} str The string to be processed
 * @return {String} The processed string
 */
this.stripslashes = function(str) {
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//-- original written by: Kevin van Zonneveld (http://kevin.vanzonneveld.net) ; improved by: Ates Goral, marrtins, rezna ; fixed / bugfixed by: Mick@el, Onno Marsman, Brett Zamir, Rick Waldron, Brant Messenger
	return str.replace(/\\(.?)/g, function(s, n1) {
		switch(n1) {
			case '\\':
				return '\\';
			case '0':
				return '\u0000';
			case '':
				return '';
			default:
				return String(n1);
		} //end switch
	});
	//--
} //END FUNCTION


/**
 * Encodes an ISO-8859-1 string to UTF-8
 *
 * @method utf8_encode
 * @static
 * @param 	{String} 	string 			The string to be processed
 * @return 	{String} 					The processed string
 */
this.utf8_encode = function(string) {
	//--
	if((typeof string == 'undefined') || (string == undefined) || (string == null)) {
		string = '';
	} else {
		string = String(string); // force string
	} //end if else
	//--
	var utftext = '';
	//--
	string = string.replace(/\r\n/g,"\n");
	for(var n = 0; n < string.length; n++) {
		var c = string.charCodeAt(n);
		if (c < 128) {
			utftext += String.fromCharCode(c);
		} else if((c > 127) && (c < 2048)) {
			utftext += String.fromCharCode((c >> 6) | 192);
			utftext += String.fromCharCode((c & 63) | 128);
		} else {
			utftext += String.fromCharCode((c >> 12) | 224);
			utftext += String.fromCharCode(((c >> 6) & 63) | 128);
			utftext += String.fromCharCode((c & 63) | 128);
		} //end if else
	} //end for
	//--
	return String(utftext); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Decodes an UTF-8 string to ISO-8859-1
 *
 * @method utf8_decode
 * @static
 * @param 	{String} 	string 			The string to be processed
 * @return 	{String} 					The processed string
 */
this.utf8_decode = function(utftext) {
	//--
	if((typeof utftext == 'undefined') || (utftext == undefined) || (utftext == null)) {
		utftext = '';
	} else {
		utftext = String(utftext); // force string
	} //end if else
	//--
	var string = '';
	//--
	var i = 0;
	var c = c1 = c2 = 0;
	while ( i < utftext.length ) {
		c = utftext.charCodeAt(i);
		if (c < 128) {
			string += String.fromCharCode(c);
			i++;
		} else if((c > 191) && (c < 224)) {
			c2 = utftext.charCodeAt(i+1);
			string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
			i += 2;
		} else {
			c2 = utftext.charCodeAt(i+1);
			c3 = utftext.charCodeAt(i+2);
			string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
			i += 3;
		} //end if else
	} //end while
	//--
	return String(string); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Convert binary data into hexadecimal representation.
 * This is compatible with PHP bin2hex() function.
 *
 * @method bin2hex
 * @static
 * @param 	{String} 	s 			The string to be processed
 * @return 	{String} 				The processed string
 */
this.bin2hex = function(s) {
	//--
	s = String(_class.utf8_encode(s)); // force string and make it unicode safe
	//--
	var hex = '';
	var i, l, n;
	for(i = 0, l = s.length; i < l; i++) {
		n = s.charCodeAt(i).toString(16);
		hex += n.length < 2 ? '0' + n : n;
	} //end for
	//--
	return String(hex); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Decodes a hexadecimally encoded binary string.
 * This is compatible with PHP hex2bin() function.
 *
 * @method hex2bin
 * @static
 * @param 	{String} 	hex 		The string to be processed
 * @return 	{String} 				The processed string
 */
this.hex2bin = function(hex) {
	//--
	hex = String(_class.stringTrim(hex)); // force string and trim to avoid surprises ...
	//--
	var bytes = [], str;
	//--
	for(var i=0; i< hex.length-1; i+=2) {
		bytes.push(parseInt(hex.substr(i, 2), 16));
	} //end for
	//--
	return String(_class.utf8_decode(String.fromCharCode.apply(String, bytes))); // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Quote regular expression characters in a string.
 * This is compatible with PHP preg_quote() function.
 *
 * @method preg_quote
 * @static
 * @param 	{String} 	str 		The string to be processed
 * @return 	{String} 				The processed string
 */
this.preg_quote = function(str) {
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//--
	// http://kevin.vanzonneveld.net
	// + original by: booeyOH
	// + improved by: Ates Goral (http://magnetiq.com)
	// + improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// + bugfixed by: Onno Marsman
	// *   example 1: preg_quote("$40");
	// *   returns 1: '\$40'
	// *   example 2: preg_quote("*RRRING* Hello?");
	// *   returns 2: '\*RRRING\* Hello\?'
	// *   example 3: preg_quote("\\.+*?[^]$(){}=!<>|:");
	// *   returns 3: '\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:'
	//--
	return String(str.replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, '\\$1'));
	//--
} //END FUNCTION


/*
 * Add to List
 *
 * @private : internal development only
 * @method addToList
 * @static
 * @param 	{String} 	newVal 		The new val to add to the List
 * @param 	{String} 	textList 	The string List to add newVal at
 * @param 	{String} 	splitBy 	The string separator, any of: , ;
 * @return 	{String} 				The processed string as List separed by separator
 */
this.addToList = function(newVal, textList, splitBy) {
	//--
	newVal = String(newVal);
	//--
	var terms = [];
	switch(splitBy) {
		case ',':
			terms = SmartJS_CoreUtils.stringSplitbyComma(textList);
			break;
		case ';':
			terms = SmartJS_CoreUtils.stringSplitbySemicolon(textList);
			break;
		default:
			console.error('ERROR: SmartJS/Core: Invalid splitBy separator. Must be any of [,;]');
			return '';
	} //end switch
	//--
	terms.pop(); // remove the current input
	var found = 0;
	if(terms.length > 0) {
		for(var i=0; i<terms.length; i++) {
			if(terms[i] == newVal) {
				found = 1;
				break;
			} //end if
		} //end for
	} //end if
	if(found == 0) {
		terms.push(newVal); // add the selected item
	} //end if
	terms.push(''); // add placeholder to get the comma-and-space at the end
	//--
	return String(terms.join(splitBy + ' '));
	//--
} //END FUNCTION


/**
 * Regexp Match All occurences.
 * This is compatible just with the PHP preg_match_all()
 *
 * @method stringRegexMatchAll
 * @static
 * @param 	{String} 	str 		The string to be searched
 * @param 	{Regex} 	regexp 		A valid regular expression
 * @return 	{Array} 				The array with matches
 */
this.stringRegexMatchAll = function(str, regexp) { // v.170922
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = String(str); // force string
	} //end if else
	//--
	var matches = [];
	//--
	var match = null;
	var loopidx = 0;
	var maxloops = 1000000; // set a safe value as in php.ini pcre.recursion_limit, but higher enough: 1 million
	while(match = regexp.exec(str)) {
		matches.push(match);
		loopidx++;
		if(loopidx >= maxloops) { // protect against infinite loop
			console.error('WARNING: stringRegexMatchAll has entered in a possible infinite loop. Max recursion depth reached at: ' + maxloops);
			break;
		} //end if
	} //end while
	//--
	/* this is a non safe alternative of the above code but does not have a protection against infinite loops !!!
	//var arguments = {}; // DON'T use it as arguments is a reserved word in JS and the minifiers will break it below if found as defined as var here ... (prior this was a fix, but not necessary: required init to avoid using using arguments from a global context !)
	str.replace(regexp, function() {
		var arr = ([]).slice.call(arguments, 0);
		var extras = arr.splice(-2);
		arr.index = extras[0];
		arr.input = extras[1];
		matches.push(arr);
	}); */
	//--
	return matches; // Array
	//--
} //END FUNCTION


/**
 * Render Simple Marker Template + Comments (only marker replacements with escaping or processing syntax ; have support for comments and special replacements: SPACE, TAB, R, N ; no support for IF / LOOP / INCLUDE syntax since it can be implemented at the js level)
 * This is compatible just with the REPLACE MARKER of Smart.Framework PHP server-side Markers Templating for substitutions on client-side, except the extended syntax as IF/LOOP/INCLUDE.
 * To be used together with the server-side marker templating, to avoid the server-side markers to be replaced as (####+/-MARKER+/-####) will need the template to be escape_url+escape_js and 3rd param: urlescaped = TRUE
 *
 * @method render_markers_template
 * @static
 * @param 	{String} 	template 	The string template with markers
 * @param 	{ArrayObj} 	arrobj 		The Object-Array with marker replacements as { 'MAR.KER_1' => 'Value 1', 'MARKER-2' => 'Value 2', ... }
 * @return 	{String} 				The processed string
 */
this.render_markers_template = function(template, arrobj, urlescaped) { // v.170921
	//--
	var debug = false;
	//--
	if((typeof template === 'string') && (typeof arrobj === 'object')) {
		//--
		if(urlescaped === true) {
			template = String(decodeURIComponent(template));
		} //end if
		//--
		template = _class.stringTrim(template);
		//-- remove comments: javascript regex miss the regex flags: s = single line: Dot matches newline characters ; U = Ungreedy: The match becomes lazy by default ; Now a ? following a quantifier makes it greedy
		// because missing the single line dot match and ungreedy is almost impossible to solve this with a regex in an optimum way, tus we use this trick :-)
		// because missing the /s flag, the extra \S have to be added to the \s to match new lines and the (.*) have become ([\s\S^]*)
		// because missing the /U flag, missing ungreedy, we need to split/join to solve this
		if((template.indexOf('[%%%%COMMENT%%%%]') >= 0) && (template.indexOf('[%%%%/COMMENT%%%%]') >= 0)) { // indexOf() :: if not found returns -1
			var arr_comments = [];
			arr_comments = template.split('[%%%%COMMENT%%%%]');
			for(var i=0; i<arr_comments.length; i++) {
				if(arr_comments[i].indexOf('[%%%%/COMMENT%%%%]') >= 0) { // indexOf() :: if not found returns -1
					arr_comments[i] = '[%%%%COMMENT%%%%]' + arr_comments[i];
					arr_comments[i] = arr_comments[i].replace(/[\s\S]?\[%%%%COMMENT%%%%\]([\s\S^]*)\[%%%%\/COMMENT%%%%\][\s\S]?/g, '');
				} //end if
			} //end for
			template = _class.stringTrim(arr_comments.join(''));
			arr_comments = null;
		} //end if
		//-- replace markers
		if(template != '') {
			//--
			var regexp = /\[####([A-Z0-9_\-\.]+)(\|bool|\|int|\|num|\|htmid|\|jsvar|\|json|\|substr[0-9]{1,5}|\|subtxt[0-9]{1,5})?(\|url)?(\|js|\|html)?(\|html|\|js)?(\|nl2br|\|url)?####\]/g; // {{{SYNC-REGEX-MARKER-TEMPLATES}}}
			//--
			var markers = _class.stringRegexMatchAll(template, regexp);
		//	if(debug) {
		//		console.log(markers);
		//	} //end if
			//--
			for(var i=0; i<markers.length; i++) {
				//--
				var marker = markers[i]; // expects array
		//		if(debug) {
		//			console.log(marker);
		//		} //end if
				//--
				var tmp_marker_val 		= '';										// just initialize
				var tmp_marker_id 		= marker[0] ? String(marker[0]) : ''; 	// [####THE-MARKER|escapings...####]
				var tmp_marker_key 		= marker[1] ? String(marker[1]) : ''; 	// THE-MARKER
				var tmp_match_1 		= marker[2] ? String(marker[2]) : ''; 	// |bool |int |num |htmid |jsvar |json |substr12345 |subtxt67890
				var tmp_match_2 		= marker[3] ? String(marker[3]) : ''; 	// |url
				var tmp_match_3 		= marker[4] ? String(marker[4]) : ''; 	// |js
				var tmp_match_4 		= marker[5] ? String(marker[5]) : ''; 	// |html
				var tmp_match_5 		= marker[6] ? String(marker[6]) : ''; 	// |nl2br
				//--
				if((tmp_marker_id != null) && (tmp_marker_id != '') && (tmp_marker_key != null) && (tmp_marker_key != '') && (template.indexOf(tmp_marker_id) >= 0)) { // indexOf() :: if not found returns -1 # check if exists because it does replaceAll on a cycle so another cycle can run without scope !
					//--
					if(debug) {
						console.log('Marker Found: ' + tmp_marker_id + ' :: ' + tmp_marker_key);
					} //end if
					//--
					if(tmp_marker_key in arrobj) {
						//-- prepare val from input array
						tmp_marker_val = arrobj[tmp_marker_key] ? arrobj[tmp_marker_key] : '';
						tmp_marker_val = String(tmp_marker_val);
						//-- protect against cascade recursion or undefined variables or n/a syntax
						if(tmp_marker_val.indexOf('[####') >= 0) { // indexOf() :: if not found returns -1
							console.error('WARNING: SmartJS/Core: {#### Undefined Markers detected in Replacement Key: ' + tmp_marker_key + ' in Template for Value: ^' + '\n' + tmp_marker_val + '\n' + template + '$ #####}');
						} //end if
						if(tmp_marker_val.indexOf('[%%%%') >= 0) { // indexOf() :: if not found returns -1
							console.error('WARNING: SmartJS/Core: {#### Undefined Marker Syntax detected in Replacement Key: ' + tmp_marker_key + ' in Template for Value: ^' + '\n' + tmp_marker_val + '\n' + template + '$ #####}');
						} //end if
						if(tmp_marker_val.indexOf('[@@@@') >= 0) { // indexOf() :: if not found returns -1
							console.error('WARNING: SmartJS/Core: {#### Undefined Marker Sub-Templates detected in Replacement Key: ' + tmp_marker_key + ' in Template for Value: ^' + '\n' + tmp_marker_val + '\n' + template + '$ #####}');
						} //end if
						tmp_marker_val = _class.stringReplaceAll('[####', '(####*', tmp_marker_val);
						tmp_marker_val = _class.stringReplaceAll('####]', '*####)', tmp_marker_val);
						tmp_marker_val = _class.stringReplaceAll('[%%%%', '(%%%%*', tmp_marker_val);
						tmp_marker_val = _class.stringReplaceAll('%%%%]', '*%%%%)', tmp_marker_val);
						tmp_marker_val = _class.stringReplaceAll('[@@@@', '(@@@@*', tmp_marker_val);
						tmp_marker_val = _class.stringReplaceAll('@@@@]', '*@@@@)', tmp_marker_val);
						//-- #1 Format
						if(tmp_match_1 == '|bool') { // Boolean
							if(tmp_marker_val) {
								tmp_marker_val = 'true';
							} else {
								tmp_marker_val = 'false';
							} //end if else
							if(debug) {
								console.log('Marker Format Bool: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #1 ' + tmp_match_1 + ' @ ' + tmp_marker_val);
							} //end if
						} else if(tmp_match_1 == '|int') { // Integer
							tmp_marker_val = parseInt(tmp_marker_val);
							if(!_class.isFiniteNumber(tmp_marker_val)) {
								tmp_marker_val = 0;
							} //end if
							tmp_marker_val = String(tmp_marker_val);
							if(debug) {
								console.log('Marker Format Int: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #1 ' + tmp_match_1 + ' @ ' + tmp_marker_val);
							} //end if
						} else if(tmp_match_1 == '|num') { // Number (Float / Decimal / Integer)
							tmp_marker_val = parseFloat(tmp_marker_val);
							if(!_class.isFiniteNumber(tmp_marker_val)) {
								tmp_marker_val = 0;
							} //end if
							tmp_marker_val = String(tmp_marker_val);
							if(debug) {
								console.log('Marker Format Number: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #1 ' + tmp_match_1 + ' @ ' + tmp_marker_val);
							} //end if
						} else if(tmp_match_1 == '|htmid') { // HTML ID
							tmp_marker_val = tmp_marker_val.replace(/[^a-zA-Z0-9_\-]/g, '');
							tmp_marker_val = String(tmp_marker_val);
							if(debug) {
								console.log('Marker Format HTML-ID: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #1 ' + tmp_match_1 + ' @ ' + tmp_marker_val);
							} //end if
						} else if(tmp_match_1 == '|jsvar') { // JS Variable
							tmp_marker_val = tmp_marker_val.replace(/[^a-zA-Z0-9_]/g, '');
							tmp_marker_val = String(tmp_marker_val);
							if(debug) {
								console.log('Marker Format JS-VAR: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #1 ' + tmp_match_1 + ' @ ' + tmp_marker_val);
							} //end if
						} else if(tmp_match_1 == '|json') { // Json Data ; expects pure JSON !!!
							var jsonObj = null;
							try {
								jsonObj = JSON.parse(tmp_marker_val); // it MUST be JSON !
							} catch(err){
								jsonObj = null;
							} //end try catch
							tmp_marker_val = JSON.stringify(jsonObj);
							tmp_marker_val = String(tmp_marker_val);
							// JSON stringify does not make the JSON to be HTML-Safe, thus we need several minimal replacements: https://www.drupal.org/node/479368 + escape /
							tmp_marker_val = tmp_marker_val.replace(/[\u0026]/g, '\\u0026');	// & 	JSON_HEX_AMP
							tmp_marker_val = tmp_marker_val.replace(/[\u003C]/g, '\\u003C'); 	// < 	JSON_HEX_TAG (use uppercase as in PHP)
							tmp_marker_val = tmp_marker_val.replace(/[\u003E]/g, '\\u003E'); 	// > 	JSON_HEX_TAG (use uppercase as in PHP)
							tmp_marker_val = tmp_marker_val.replace(/[\/]/g,     '\\/');	    // / 	JSON_UNESCAPED_SLASHES
							// this JSON string will not be 100% like the one produced via PHP with HTML-Safe arguments but at least have the minimum escapes to avoid conflicting HTML tags
							tmp_marker_val = _class.stringTrim(tmp_marker_val);
							if(tmp_marker_val == '') {
								tmp_marker_val = 'null'; // ensure a minimal json as empty string if no expr !
							} //end if
							if(debug) {
								console.log('Marker Format JSON: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #1 ' + tmp_match_1 + ' @ ' + tmp_marker_val);
							} //end if
						} else if((tmp_match_1.substring(0, 7) == '|substr') || (tmp_match_1.substring(0, 7) == '|subtxt')) { // Sub(String|Text) (0,num)
							var xnum = parseInt(tmp_match_1.substring(7));
							if(!_class.isFiniteNumber(xnum)) {
								xnum = 1;
							} //end if
							if(xnum < 1) {
								xnum = 1;
							} //end if
							if(xnum > 65535) {
								xnum = 65535;
							} //end if
							if(xnum >= 1 && xnum <= 65535) {
								var xlen = tmp_marker_val.length;
								if(xlen > xnum) {
									tmp_marker_val = tmp_marker_val.substring(0, xnum);
								} //end if
								if(tmp_match_1.substring(0, 7) == '|subtxt') {
									if(xlen > xnum) {
										var old_val = tmp_marker_val;
										var tmp_arr = tmp_marker_val.split(' ');
										tmp_arr.pop();
										tmp_marker_val = tmp_arr.join(' ');
										if(tmp_marker_val.length < Math.ceil(xnum / 1.5)) {
										//	if(debug) {
										//		console.log('Restoring the Sub-Txt, Too much ahead cut-off ...'); // if there is not a space in the last 1/3 or there are spaces {{{SYNC-CUT-BACKWARD-STR-BY-SPACE}}}
										//	} //end if
											tmp_marker_val = old_val; // restore, too much trim ahead
										} //end if
										tmp_marker_val = tmp_marker_val + '...';
										tmp_arr = null;
										old_val = null;
									} //end if
									if(debug) {
										console.log('Marker Sub-Text(' + xnum + '): ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #1 ' + tmp_match_1 + ' @ ' + tmp_marker_val);
									} //end if
								} else {
									if(debug) {
										console.log('Marker Sub-String(' + xnum + '): ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #1 ' + tmp_match_1 + ' @ ' + tmp_marker_val);
									} //end if
								} //end if
								xlen = null;
							} //end if
							xnum = null;
						} //end if else
						//-- #2 Escape URL
						if(tmp_match_2 == '|url') {
							tmp_marker_val = _class.escape_url(tmp_marker_val);
							if(debug) {
								console.log('Marker URL-Escape: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #2 ' + tmp_match_2 + ' @ ' + tmp_marker_val);
							} //end if
						} //end if
						//-- #3 Escape JS / HTML
						if(tmp_match_3 == '|js') {
							tmp_marker_val = _class.escape_js(tmp_marker_val);
							if(debug) {
								console.log('Marker JS-Escape: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #3 ' + tmp_match_3 + ' @ ' + tmp_marker_val);
							} //end if
						} else if(tmp_match_3 == '|html') {
							tmp_marker_val = _class.escape_html(tmp_marker_val);
							if(debug) {
								console.log('Marker HTML-Escape: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #3 ' + tmp_match_3 + ' @ ' + tmp_marker_val);
							} //end if
						} //end if else
						//-- #4 Escape HTML / JS
						if(tmp_match_4 == '|html') {
							tmp_marker_val = _class.escape_html(tmp_marker_val);
							if(debug) {
								console.log('Marker HTML-Escape: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #4 ' + tmp_match_4 + ' @ ' + tmp_marker_val);
							} //end if
						} else if(tmp_match_4 == '|js') {
							tmp_marker_val = _class.escape_js(tmp_marker_val);
							if(debug) {
								console.log('Marker JS-Escape: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #4 ' + tmp_match_4 + ' @ ' + tmp_marker_val);
							} //end if
						} //end if else
						//-- #5 NL2BR / URL
						if(tmp_match_5 == '|nl2br') {
							tmp_marker_val = _class.nl2br(tmp_marker_val);
							if(debug) {
								console.log('Marker NL2BR-Reflow: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #5 ' + tmp_match_5 + ' @ ' + tmp_marker_val);
							} //end if
						} else if(tmp_match_5 == '|url') {
							tmp_marker_val = _class.escape_url(tmp_marker_val);
							if(debug) {
								console.log('Marker URL-Escape: ' + tmp_marker_id + ' :: ' + tmp_marker_key + ' #5 ' + tmp_match_5 + ' @ ' + tmp_marker_val);
							} //end if
						} //end if else
						//--
						template = _class.stringReplaceAll(tmp_marker_id, tmp_marker_val, template);
						//--
					} //end if
					//--
				} //end if
				//--
			} //end for
			//--
			marker = null;
			markers = null;
			//-- replace SPACE TAB R N
			if(template.indexOf('[%%%%|') >= 0) { // indexOf() :: if not found returns -1
				template = _class.stringReplaceAll('[%%%%|R%%%%]', '\r', template);
				template = _class.stringReplaceAll('[%%%%|N%%%%]', '\n', template);
				template = _class.stringReplaceAll('[%%%%|T%%%%]', '\t', template);
				template = _class.stringReplaceAll('[%%%%|SPACE%%%%]', ' ', template);
			} //end if
			//--
			if(template.indexOf('[####') >= 0) { // indexOf() :: if not found returns -1
				var undef_markers = _class.stringRegexMatchAll(template, regexp);
			//	if(debug) {
			//		console.log(undef_markers);
			//	} //end if
				var undef_log = '';
				for(var i=0; i<undef_markers.length; i++) {
					var undef_marker = undef_markers[i]; // expects array
					var tmp_undef_marker_id = undef_marker[0] ? String(undef_marker[0]) : ''; // [####THE-MARKER|escapings...####]
					undef_log += tmp_undef_marker_id + '\n';
				} //end for
				console.error('WARNING: SmartJS/Core: {#### Undefined Markers detected in Template: ^' + '\n' + undef_log + '\n' + template + '$ #####}');
				undef_log = null;
				undef_marker = null;
				undef_markers = null;
			} //end if
			//--
			if(template.indexOf('[%%%%') >= 0) { // indexOf() :: if not found returns -1
				console.error('WARNING: SmartJS/Core: {#### Undefined Marker Syntax detected in Template: ^' + '\n' + template + '$ #####}');
			} //end if
			if(template.indexOf('[@@@@') >= 0) { // indexOf() :: if not found returns -1
				console.error('WARNING: SmartJS/Core: {#### Undefined Marker Sub-Templates detected in Template: ^' + '\n' + template + '$ #####}');
			} //end if
			//--
		} //end if else
		//--
	} else {
		//--
		console.error('ERROR: SmartJS/Core: {#### Invalid Markers-Template Arguments ####}');
		template = '';
		//--
	} //end if
	//--
	return String(template); // fix to return empty string instead of null [OK]
	//--
} //END FUNCTION


/**
 * Sort a stack (array / object / property) using String Sort algorithm
 *
 * @method textSort
 * @static
 * @param 	{Mixed} 	property 		The stack to be sorted
 * @return 	{Mixed} 					The sorted stack
 */
this.textSort = function(property) {
	//--
	return function(a, b) {
		//--
		if(a[property] == null) {
				a[property] = '';
		} //end if
		if(b[property] == null) {
				b[property] = '';
		} //end if
		//--
		try {
			var comparer = a[property].localeCompare(b[property]); // a better compare
			if(comparer < 0) {
					comparer = -1;
			} //end if
			if(comparer > 0) {
					comparer = 1;
			} //end if
		} catch(e) {
			comparer = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
		} //end try catch
		//--
		return comparer; // Mixed
		//--
	} //end function
	//--
} //END FUNCTION


/**
 * Sort a stack (array / object / property) using Numeric Sort algorithm
 *
 * @method numericSort
 * @static
 * @param 	{Mixed} 	property 		The stack to be sorted
 * @return 	{Mixed} 					The sorted stack
 */
this.numericSort = function(property) {
	//--
	return function(a, b) {
		//--
		if(a[property].toString() == '') {
				a[property] = 0;
		} //end if
		if(b[property].toString() == '') {
				b[property] = 0;
		} //end if
		//--
		a[property] = parseFloat(a[property]); // parse as number
		b[property] = parseFloat(b[property]); // parse as number
		//--
		if(a[property] > b[property]) {
			return 1;
		} //end if
		//--
		if(a[property] < b[property]) {
			return -1;
		} //end if
		//--
		return 0;
		//--
	} //end function
	//--
} //END FUNCTION


/**
 * Format a date object into a string value.
 *
 * Hint: The format can be combinations of the following:
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
 *
 * @method formatDate
 * @static
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
 *
 * Hint: Valid date objects or expressions:
 * new Date(1937, 1 - 1, 1) 	:: a date in the past, as object ;
 * '-1y -1m -1d' 				:: a date in the past as relative expression to the defaultDate ;
 * new Date(2037, 12 - 1, 31) 	:: a date in the future as object ;
 * '1y 1m 1d' 					:: a date in the future as relative expression to the defaultDate ;
 *
 * @method determineDate
 * @static
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
	var newDate = (date == null || date === '' ? defaultDate : (typeof date === 'string' ? offsetString(date) : (typeof date === 'number' ? (!_class.isFiniteNumber(date) ? defaultDate : offsetNumeric(date)) : new Date(date.getTime()))));
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


} //END CLASS

//==================================================================
//==================================================================

// #END
