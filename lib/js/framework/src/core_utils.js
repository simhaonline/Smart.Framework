
// [LIB - SmartFramework / JS / Core Utils]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.1.2 r.2017.04.11 / smart.framework.v.3.1

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
var SmartJS_CoreUtils = new function() { // START CLASS

// :: static

var _class = this; // self referencing


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
		str = '' + str; // force string
	} //end if else
	str = str.toString();
	//--
	return '' + str.replace(/^\s\s*/, '').replace(/\s\s*$/, ''); // fix to return empty string instead of null
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
		str = '' + str; // force string
	} //end if else
	str = str.toString();
	str = _class.stringTrim(str);
	//--
	return str.split(/,\s*/);
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
		str = '' + str; // force string
	} //end if else
	str = str.toString();
	str = _class.stringTrim(str);
	//--
	return str.split(/;\s*/);
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
		str = '' + str; // force string
	} //end if else
	str = str.toString();
	//--
	return '' + str.split(token).join(newToken); // fix to return empty string instead of null
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
		str = '' + str; // force string
	} //end if else
	str = str.toString();
	//--
	var i = -1;
	//--
	if((str != '') && (typeof token === 'string') && (typeof newToken === 'string')) {
		//--
		token = token.toLowerCase();
		//--
		while((i = str.toLowerCase().indexOf(token, i >= 0 ? i + newToken.length : 0)) !== -1) {
			str = '' + str.substring(0, i) + newToken + str.substring(i + token.length);
		} //end while
		//--
	} //end if
	//--
	return '' + str; // fix to return empty string instead of null
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
		return arr.shift();
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
		return arr.pop();
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
	if((typeof y_number == 'undefined') || (y_number == null) || (y_number == '') || (isNaN(y_number))) {
		y_number = 0;
	} //end if
	//--
	if(y_allow_negatives !== true) {
		y_allow_negatives = false;
	} //end if
	//--
	y_number = parseInt('' + y_number);
	if(isNaN(y_number)) {
		y_number = 0;
	} //end if
	//--
	if(y_allow_negatives !== true) { // force as positive
		if(y_number < 0) {
			y_number = parseInt(-1 * y_number);
		} //end if
		if(isNaN(y_number)) {
			y_number = 0;
		} //end if
		if(y_number < 0) {
			y_number = 0;
		} //end if
	} //end if
	//--
	return y_number;
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
	if((typeof y_number == 'undefined') || (y_number == null) || (y_number == '') || (isNaN(y_number))) {
		y_number = 0;
	} //end if
	//--
	if((typeof y_decimals == 'undefined') || (y_decimals == null) || (y_decimals == '')) {
		y_decimals = 2; // default;
	} //end if
	y_decimals = parseInt(y_decimals);
	if(isNaN(y_decimals)) {
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
	y_number = parseFloat('' + y_number).toFixed(y_decimals);
	if(isNaN(y_number)) {
		y_number = parseFloat(0).toFixed(y_decimals);
	} //end if
	//--
	if(y_allow_negatives !== true) { // force as positive
		if(y_number < 0) {
			y_number = parseFloat(-1 * y_number).toFixed(y_decimals);
		} //end if
		if(isNaN(y_number)) {
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
	return y_number;
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
	var num = '' + num.toString(); // this is a special case
	var parts = num.split('.');
	parts[0] = parts[0].replace(/(\d)(?=(\d{3})+$)/g, '$1,'); // add thousands separator
	return '' + parts.join('.'); // fix to return empty string instead of null
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
		str = '' + str; // force string
	} //end if else
	str = str.toString();
	//--
	return '' + encodeURIComponent(str); // fix to return empty string instead of null
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
		str = '' + str; // force string
	} //end if else
	str = str.toString();
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
	return '' + str; // fix to return empty string instead of null
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
	return _class.htmlspecialchars(str, 'ENT_COMPAT');
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
this.escape_js = function(str) { // v.160902
	//-- escape a string as unicode
	var escape_unicode = function(str) {
		return '\\u' + ('0000' + str.charCodeAt(0).toString(16)).slice(-4).toLowerCase();
	} //END FUNCTION
	//--
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = '' + str; // force string
	} //end if else
	str = str.toString();
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
	//-- replace special characters
	encoded = encoded.replace(/[\u0026]/g, '\\u0026');	// & 	JSON_HEX_AMP
	encoded = encoded.replace(/[\u0022]/g, '\\u0022');	// " 	JSON_HEX_QUOT
	encoded = encoded.replace(/[\u0027]/g, '\\u0027');	// ' 	JSON_HEX_APOS
	encoded = encoded.replace(/[\u003C]/g, '\\u003C'); 	// < 	JSON_HEX_TAG (use uppercase as in PHP)
	encoded = encoded.replace(/[\u003E]/g, '\\u003E'); 	// > 	JSON_HEX_TAG (use uppercase as in PHP)
	encoded = encoded.replace(/[\/]/g, '\\/');			// /
	//-- return string
	return '' + encoded; // fix to return empty string instead of null
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
		str = '' + str; // force string
	} //end if else
	str = str.toString();
	//--
	return '' + str.replace(/\r\n/g, /\n/).replace(/\r/g, /\n/).replace(/\n/g, '<br>'); // fix to return empty string instead of null
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
	//-- original written by: Kevin van Zonneveld (http://kevin.vanzonneveld.net) ; improved by: Ates Goral, marrtins, rezna ; fixed / bugfixed by: Mick@el, Onno Marsman, Brett Zamir, Rick Waldron, Brant Messenger
	return (str + '').replace(/\\(.?)/g, function(s, n1) {
		switch (n1) {
			case '\\':
				return '\\';
			case '0':
				return '\u0000';
			case '':
				return '';
			default:
				return n1;
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
	var utftext = '';
	//--
	string = string.replace(/\r\n/g,"\n");
	for (var n = 0; n < string.length; n++) {
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
	return '' + utftext; // fix to return empty string instead of null
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
	return '' + string; // fix to return empty string instead of null
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
	s = '' + _class.utf8_encode(s); // force string and make it unicode safe
	//--
	var hex = '';
	var i, l, n;
	for(i = 0, l = s.length; i < l; i++) {
		n = s.charCodeAt(i).toString(16);
		hex += n.length < 2 ? '0' + n : n;
	} //end for
	//--
	return '' + hex; // fix to return empty string instead of null
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
	hex = '' + _class.stringTrim(hex); // force string and trim to avoid surprises ...
	//--
	var bytes = [], str;
	//--
	for(var i=0; i< hex.length-1; i+=2) {
		bytes.push(parseInt(hex.substr(i, 2), 16));
	} //end for
	//--
	return '' + _class.utf8_decode(String.fromCharCode.apply(String, bytes)); // fix to return empty string instead of null
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
	return (str+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, '\\$1');
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
	var terms = [];
	//--
	switch(splitBy) {
		case ',':
			terms = SmartJS_CoreUtils.stringSplitbyComma(textList);
			break;
		case ';':
			terms = SmartJS_CoreUtils.stringSplitbySemicolon(textList);
			break;
		default:
		throw 'ERROR: SmartJS/Core: Invalid splitBy separator. Must be any of [,;]';
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
	return '' + terms.join(splitBy + ' ');
	//--
} //END FUNCTION


/**
 * Render Simple Marker Template (only marker replacements, no IF or LOOP syntax, not even marker escaping syntax)
 * This is compatible just with the SIMPLE REPLACEMENT of Smart.Framework PHP server-side Markers Templating for substitutions on client-side, except it can only process marker substitutions without extended syntax as IF/LOOP/INCLUDE.
 * Notice: It ONLY supports the marker syntax like [####MARKER####]
 * To be used together with the server-side marker templating to avoid the server-side markers to be replaced as (####+/-MARKER+/-####) need the: template to be escape_url+escape_js and 3rd param: urlescaped = TRUE
 *
 * @method render_markers_template
 * @static
 * @param 	{String} 	template 	The string template with markers
 * @param 	{ArrayObj} 	arrobj 		The Object-Array with marker replacements as { 'MAR.KER_1' => 'Value 1', 'MARKER-2' => 'Value 2', ... }
 * @return 	{String} 				The processed string
 */
this.render_markers_template = function(template, arrobj, urlescaped) { // v.170328
	//--
	if((typeof template === 'string') && (typeof arrobj === 'object')) {
		//--
		if(urlescaped === true) {
			template = '' + decodeURIComponent(template);
		} //end if
		//--
		var key;
		var tmp_parser;
		var tmp_val;
		var regex = /^[A-Z0-9_\-\.]+$/;
		//--
		template = _class.stringTrim(template);
		for(key in arrobj) {
			tmp_parser = '';
			tmp_val = '';
			if((key != null) && (arrobj[key] != null)) {
				//--
				tmp_parser = '' + key;
				//--
				if((tmp_parser != '') && (regex.test(tmp_parser))) {
					//-- prepare vars
					tmp_parser = '[####' + tmp_parser + '####]';
					tmp_val = '' + arrobj[key];
					tmp_val = tmp_val.toString();
					//-- protect against cascade recursion or undefined variables
					tmp_val = _class.stringReplaceAll('[####', '(####*', tmp_val);
					tmp_val = _class.stringReplaceAll('####]', '*####)', tmp_val);
					//-- inject into template
					template = _class.stringIReplaceAll(tmp_parser, tmp_val, template);
					//--
				} //end if
				//--
			} //end if
		} //end for
		//--
	} else {
		//--
		throw 'ERROR: SmartJS/Core: {#### Invalid Markers-Template Arguments ####}';
		return '';
		//--
	} //end if
	//--
	return '' + template; // fix to return empty string instead of null
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
	return function (a, b) {
			if(a[property] == null) {
					a[property] = '';
			} //end if
			if(b[property] == null) {
					b[property] = '';
			} //end if
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
			return comparer;
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
	return function (a, b) {
		if(a[property].toString() == '') {
				a[property] = 0;
		} //end if
		if(b[property].toString() == '') {
				b[property] = 0;
		} //end if
		a[property] = parseFloat(a[property]); // parse as number
		b[property] = parseFloat(b[property]); // parse as number
		if(a[property] > b[property]) {
				return 1;
		} //end if
		if(a[property] < b[property]) {
				return -1;
		} //end if
		return 0;
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
			var num = '' + value;
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
	return '' + output;
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
	var newDate = (date == null || date === "" ? defaultDate : (typeof date === "string" ? offsetString( date ) : (typeof date === "number" ? (isNaN(date) ? defaultDate : offsetNumeric(date)) : new Date(date.getTime()))));
	newDate = (newDate && newDate.toString() === "Invalid Date" ? defaultDate : newDate);
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
