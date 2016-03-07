
// [LIB - SmartFramework / JS / Core Utils]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3 r.2016.03.07

// DEPENDS: -

//==================================================================
//==================================================================

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
 * Trim a string (at begining or end by any whitespace: space \n \r \t)
 *
 * @method stringTrim
 * @static
 * @param {String} str The string to be trimmed
 * @return {String} The trimmed string
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
 * @param {String} str The string to be splitted by , (comma)
 * @return {Array} The array with string parts splitted
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
	return str.split( /,\s*/ );
	//--
} //END FUNCTION


/**
 * Split string by semicolon with trimming pre/post
 *
 * @method stringSplitbySemicolon
 * @static
 * @param {String} str The string to be splitted by ; (semicolon)
 * @return {Array} The array with string parts splitted
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
	return str.split( /;\s*/ );
	//--
} //END FUNCTION


/**
 * Replace all occurences in a string - Case Sensitive
 *
 * @method stringReplaceAll
 * @static
 * @param {String} token The string part to be replaced
 * @param {String} newToken The string part replacement
 * @param {String} str The string where to do the replacements
 * @return {String} The processed string
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
 * @param {String} token The string part to be replaced
 * @param {String} newToken The string part replacement
 * @param {String} str The string where to do the replacements
 * @return {String} The processed string
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
 * @param {Array} arr The array to be used
 * @return {Mixed} The first element from the array
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
 * @param {Array} arr The array to be used
 * @return {Mixed} The last element from the array
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
 * @param {Numeric} y_number A numeric value
 * @param {Boolean} y_allow_negatives If TRUE will allow negative values else will return just positive (unsigned) values
 * @return {Integer} An integer number
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
 * @param {Numeric} y_number A numeric value
 * @param {Integer} y_decimals The number of decimal to use (between 1 and 4)
 * @param {Boolean} y_allow_negatives *Optional* If TRUE will allow negative values else will return just positive (unsigned) values
 * @param {Boolean} y_keep_trailing_zeroes *Optional* If set to TRUE will keep trailing zeroes, otherwise will discard them
 * @return {Integer} A decimal number
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


/*
 * Add the Thousands Separator (comma ,) to a number
 *
 * @method add_number_ThousandsSeparator
 * @static
 * @param {Numeric} num The number to be formatted
 * @return {String} The formatted number as string with comma as thousands separator if apply (will keep the . dot as decimal separator if apply)
 */
this.add_number_ThousandsSeparator = function(num) {
	var num = '' + num.toString(); // this is a special case
	var parts = num.split('.');
	parts[0] = parts[0].replace(/(\d)(?=(\d{3})+$)/g, '$1,'); // add thousands separator
	return '' + parts.join('.'); // fix to return empty string instead of null
} //END FUNCTION


/**
 * Safe escape URL Variable (using RFC3986 standards to be full Unicode compliant).
 * This is a shortcut to the encodeURIComponent() to provide a standard into Smart.Framework/JS
 *
 * @method escape_url
 * @static
 * @param {String} str The URL variable value to be escaped
 * @return {String} The escaped URL variable
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


/**
 * Convert special characters to HTML entities.
 * This is like the Smart::escape_html() from the PHP Smart.Framework.
 * These values will be converted by default to safe HTML entities: < > & "
 *
 * @method escape_html
 * @static
 * @param {String} str The string to be escaped
 * @return {String} The safe escaped string to be injected in HTML code
 */
this.escape_html = function(str, quote_style) { // v.141211
	//-- format sting
	if((typeof str == 'undefined') || (str == undefined) || (str == null)) {
		str = '';
	} else {
		str = '' + str; // force string
	} //end if else
	str = str.toString();
	//-- replace basics
	str = str.replace(/&/g, '&amp;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');
	//-- replace quotes, depending on quote_style
	if(quote_style == 'ENT_QUOTES') { // ENT_QUOTES
		//-- replace all quotes: ENT_QUOTES
		str = str.replace(/"/g, '&quot;');
		str = str.replace(/'/g, '&#039;');
		//--
	} else if (quote_style != 'ENT_NOQUOTES') { // ENT_COMPAT
		//-- default, replace just double quotes
		str = str.replace(/"/g, '&quot;');
		//--
	} //end if else
	//--
	return '' + str; // fix to return empty string instead of null
	//--
} //END FUNCTION


/**
 * Convert special characters to escaped entities for safe use with Javascript Strings.
 * This is like the Smart::escape_js() from the PHP Smart.Framework.
 *
 * @method escape_js
 * @static
 * @param {String} str The string to be escaped
 * @return {String} The escaped string using the json encode standard to be injected between single quotes '' or double quotes ""
 */
this.escape_js = function(str) { // v.151129
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
	encoded = encoded.replace(/[\u007F-\uFFFF]/g, function(c){return escape_unicode(c);});
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


// escape a string as unicode
var escape_unicode = function(str) {
	return '\\u' + ('0000' + str.charCodeAt(0).toString(16)).slice(-4).toLowerCase();
} //END FUNCTION


// replace new lines with <br> tag
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


// strip slashes: a PHP like stripslashes() compatible function
// original written by: Kevin van Zonneveld (http://kevin.vanzonneveld.net) ; improved by: Ates Goral, marrtins, rezna ; fixed / bugfixed by: Mick@el, Onno Marsman, Brett Zamir, Rick Waldron, Brant Messenger
this.stripslashes = function(str) {
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
} //END FUNCTION


// UTF-8 :: encode
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


// UTF-8 :: decode
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


// Bin2Hex compatible with PHP
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


// Hex2Bin compatible with PHP
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


// Render Marker Template v.160212 ; It have to be compatible with the server-side Markers Templating for substitutions on client-side
// This is like the SmartMarkersTemplating::render_template() in PHP Framework except that it will not allow sub-templates
// The [####MARKER####] cannot be rendered in a normal view as it will be replaced by server-side with (####+/-MARKER+/-####),
// but in other raw data exchange contexts like json it can be used ...
this.render_markers_template = function(template, arrobj) {
	//--
	if((typeof template === 'string') && (typeof arrobj === 'object')) {
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
					tmp_val = _class.stringReplaceAll('[####', '(####+', tmp_val);
					tmp_val = _class.stringReplaceAll('####]', '+####)', tmp_val);
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
		alert('Javascript :: {#### Invalid Markers-Template Arguments ####} ...');
		return '';
		//--
	} //end if
	//--
	return '' + template; // fix to return empty string instead of null
	//--
} //END FUNCTION


// Text (String) Sort for Data
this.TextSort = function(property) {
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
	} //END FUNCTION
} //END FUNCTION


// Numeric Sort for Data
this.NumericSort = function(property) {
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
	} //END FUNCTION
} //END FUNCTION


// returns a dump of the object
this.print_Object = function(obj, mode, level) {
	//--
	// mode : undefined | 'recursive'
	// level ; @auto@ : undefined
	//--
	var obj_key;
	var the_marker = '#########################' + '\n';
	var out_txt = '';
	var recursive;
	//--
	if((typeof(mode) != 'undefined') && (mode != null) && (mode === 'recursive')) {
		recursive = true;
		if((typeof(level) === 'undefined') || (level == null)) {
			level = 0;
			out_txt = the_marker;
		} //end if
	} else {
		recursive = false;
		level = 0;
	} //end if
	//--
	level = parseInt(level);
	//--
	for(obj_key in obj) {
		if((recursive) && (obj[obj_key] != null) && (typeof(obj[obj_key]) == 'object')) {
			out_txt = out_txt + _class.print_Object(obj[obj_key], mode, level) + the_marker;
			level++;
		} else {
			out_txt = out_txt + 'Object#' + level + '[' + obj_key + '] = ' + obj[obj_key] + '\n';
		} //end if else
	} //end for
	//--
	return out_txt;
	//--
} //END FUNCTION


} //END CLASS

//==================================================================
//==================================================================

// #END
