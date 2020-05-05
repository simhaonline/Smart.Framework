<?php
// [LIB - Smart.Framework / Base]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.5.7.2 / smart.framework.v.5.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Base
// DEPENDS:
//  * SmartUnicode::
// DEPENDS-PHP: 5.6 or later
// DEPENDS-EXT: XML, Json
//======================================================

// [REGEX-SAFE-OK]

//================================================================
if((!function_exists('json_encode')) OR (!function_exists('json_decode'))) {
	@http_response_code(500);
	die('ERROR: The PHP JSON Extension is required for the Smart.Framework / Base');
} //end if
if(!function_exists('hex2bin')) {
	@http_response_code(500);
	die('ERROR: The PHP hex2bin Function is required for Smart.Framework / Base');
} //end if
if(!function_exists('bin2hex')) {
	@http_response_code(500);
	die('ERROR: The PHP bin2hex Function is required for Smart.Framework / Base');
} //end if
//================================================================


/***** PHP and Dynamic Variable Basics :: Comparing different type of variables can be tricky in PHP

//##### NOTICE !!! The PHP comparison between string and number is tricky with equal sign #####
$var = 0;
//-- incorrect
if($var == 'some-string') {
	echo 'This comparison will give unexpected results !';
} //end if
//-- correct use
if((string)$var == 'some-string') {
	echo 'This will avoid comparison problems';
} //end if
//#####

// never use break; return ...; // return will never get executed !! :: # pcregrep -rM 'break;\s*return' .

*****/


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: Smart (Base Functions) - provides the base methods for an easy and secure development with Smart.Framework and PHP.
 *
 * <code>
 * // Usage example:
 * Smart::some_method_of_this_class(...);
 * </code>
 *
 * @usage       static object: Class::method() - This class provides only STATIC methods
 * @hints       It is recommended to use the methods in this class instead of PHP native methods whenever is possible because this class will offer Long Term Support and the methods will be supported even if the behind PHP methods can change over time, so the code would be easier to maintain.
 *
 * @access      PUBLIC
 * @depends     extensions: PHP JSON ; classes: SmartUnicode
 * @version     v.20200505
 * @package     @Core
 *
 */
final class Smart {

	// ::

	private static $Cfgs = array(); // registry of cached config data


	//================================================================
	/**
	 * Get the value for a Config parameter from the app $configs array.
	 *
	 * @param 	ENUM 		$param 			:: The selected configuration parameter. Example: 'app.info-url' will get value (STRING) from $configs['app']['info-url'] ; 'app' will get the value (ARRAY) from $configs['app']
	 *
	 * @return 	MIXED						:: The value for the selected parameter. If the Config parameter does not exists, will return an empty string.
	 */
	public static function get_from_config($param) {
		//--
		global $configs;
		//--
		if(array_key_exists((string)$param, self::$Cfgs)) {
			return self::$Cfgs[(string)$param]; // mixed
		} //end if
		//--
		$value = self::array_get_by_key_path($configs, strtolower((string)$param), '.'); // mixed
		//--
		if(is_object($value)) {
			$value = ''; // fix: dissalow objects in config ; allowed types: BOOL, NUMERIC, STRING, ARRAY
		} //end if
		//--
		self::$Cfgs[(string)$param] = $value; // mixed
		//--
		return $value; // mixed
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Fix for Directory Separator if on Windows
	 *
	 * @param 	STRING 	$y_path 			:: The path name to fix
	 *
	 * @return 	STRING						:: The fixed path name
	 */
	public static function fix_path_separator($y_path) {
		//--
		if((string)DIRECTORY_SEPARATOR == '\\') { // if on Windows, Fix Path Separator !!!
			if(strpos((string)$y_path, '\\') !== false) {
				$y_path = (string) str_replace((string)DIRECTORY_SEPARATOR, '/', (string)$y_path); // convert \ to / on paths
			} //end if
		} //end if
		//--
		return (string) $y_path;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Return the FIXED realpath(), also with fix on Windows
	 *
	 * @param 	STRING 	$y_path 			:: The path name from to extract realpath()
	 *
	 * @return 	STRING						:: The real path
	 */
	public static function real_path($y_path) {
		//--
		$y_path = (string) trim((string)$y_path);
		//--
		$the_path = (string) @realpath((string)$y_path);
		//--
		return (string) self::fix_path_separator($the_path); // FIX: on Windows, is possible to return a backslash \ instead of slash /
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Return the FIXED dirname(), also with fix on Windows
	 *
	 * @param 	STRING 	$y_path 			:: The path name from to extract dirname()
	 *
	 * @return 	STRING						:: The dirname or . or empty string
	 */
	public static function dir_name($y_path) {
		//--
		$y_path = (string) trim((string)$y_path);
		//--
		$dir_name = (string) dirname((string)$y_path);
		//--
		return (string) self::fix_path_separator($dir_name); // FIX: on Windows, is possible to return a backslash \ instead of slash /
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Return the FIXED basename(), in a safe way
	 * The directory separator character is the forward slash (/), except Windows where both slash (/) and backslash (\) are considered
	 *
	 * @param 	STRING 	$y_path 			:: The path name from to extract basename()
	 * @param 	STRING 	$y_suffix 			:: If the name component ends in suffix this will also be cut off
	 *
	 * @return 	STRING						:: The basename
	 */
	public static function base_name($y_path, $y_suffix='') {
		//--
		$y_path = (string) trim((string)$y_path);
		$y_suffix = (string) trim((string)$y_suffix);
		//--
		if((string)$y_suffix != '') {
			$base_name = (string) basename($y_path, $y_suffix);
		} else {
			$base_name = (string) basename($y_path);
		} //end if else
		//--
		return (string) $base_name;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Return the FIXED pathinfo(), also with fix on Windows
	 *
	 * @param 	STRING 	$y_path 			:: The path to process as pathinfo()
	 *
	 * @return 	ARRAY						:: The pathinfo array
	 */
	public static function path_info($y_path) {
		//--
		$y_path = trim((string)$y_path);
		//--
		$path_info = (array) pathinfo($y_path, PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME);
		//--
		$path_info['dirname'] = (string) self::fix_path_separator((string)$path_info['dirname']);
		//--
		return (array) $path_info;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Add URL Params (Build a standard RFC3986 URL from script and parameters) as: script.xyz?a=b&param1=value1&param2=value2
	 *
	 * @param 	STRING 		$y_url				:: The base URL like: script.php or script.php?a=b or empty
	 * @param 	ARRAY		$y_params 			:: Associative array as [param1 => value1, Param2 => Value2]
	 *
	 * @return 	STRING							:: The prepared URL in the standard RFC3986 format (all values are escaped using rawurlencode() to be Unicode full compliant
	 */
	public static function url_add_params($y_url, $y_params) {
		//--
		$url = (string) $y_url;
		//--
		if(is_array($y_params)) {
			foreach($y_params as $key => $val) {
				if((string)$key != '') {
					if(SmartFrameworkSecurity::ValidateVariableName((string)$key, true)) { // {{{SYNC-REQVARS-CAMELCASE-KEYS}}}
						$suffix = '';
						if(is_array($val)) {
							$arrtype = self::array_type_test($val); // 0: not an array ; 1: non-associative ; 2:associative
							if($arrtype === 1) { // 1: non-associative
								for($i=0; $i<Smart::array_size($val); $i++) {
									$suffix = (string) $key.'[]='.self::escape_url((string)$val[$i]);
									$url = (string) self::url_add_suffix((string)$url, (string)$suffix);
								} //end foreach
							} else { // 2: associative
								foreach($val as $kk => $vv) {
									$suffix = (string) $key.'['.self::escape_url((string)$kk).']='.self::escape_url((string)$vv);
									$url = (string) self::url_add_suffix((string)$url, (string)$suffix);
								} //end foreach
							} //end if else
						} elseif((string)$val != '') {
							$suffix = (string) $key.'=';
							if((substr((string)$val, 0, 3) == '{{{') AND (substr((string)$val, -3, 3) == '}}}')) { // this is {{{param}}}
								$tmp_val = (string) substr((string)$val, 3);
								$tmp_val = (string) substr((string)$tmp_val, 0, (int)strlen((string)$tmp_val)-3);
								$suffix .= (string) '{{{'.self::escape_url((string)$tmp_val).'}}}';
								$tmp_val = '';
							} else {
								$suffix .= (string) self::escape_url((string)$val);
							} //end if else
							$url = (string) self::url_add_suffix((string)$url, (string)$suffix);
						} //end if
					} //end if
				} //end if
			} //end foreach
		} else {
			self::log_notice('[URL Add Params] WARNING: The parameters must be Array !');
		} //end if
		//--
		return (string) $url;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Add URL Suffix (to a standard RFC3986 URL) as: script.php?a=b&C=D&e=%20d
	 *
	 * @param 	STRING 		$y_url				:: The base URL to use as prefix like: script.php or script.php?a=b&c=d or empty
	 * @param 	STRING		$y_suffix 			:: A RFC3986 URL segment like: a=b or E=%20d (without ? or not starting with & as they will be detected if need append ? or &; variable values must be encoded using rawurlencode() RFC3986)
	 *
	 * @return 	STRING							:: The prepared URL in the standard RFC3986 format (all values are escaped using rawurlencode() to be Unicode full compliant
	 */
	public static function url_add_suffix($y_url, $y_suffix) {
		//--
		$y_url = (string) trim((string)$y_url);
		$y_suffix = (string) trim((string)$y_suffix);
		//--
		if(((string)$y_suffix == '') OR ((string)$y_suffix == '?') OR ((string)$y_suffix == '&')) {
			if((string)$y_url != '') {
				return (string) $y_url.$y_suffix;
			} //end if
		} //end if
		//--
		if(((string)substr((string)$y_suffix, 0, 1) == '?') OR ((string)substr((string)$y_suffix, 0, 1) == '&')) {
			$y_suffix = (string) substr((string)$y_suffix, 1);
		} //end if
		//--
		if((strpos((string)$y_suffix, '?') !== false) OR ((string)substr((string)$y_suffix, 0, 1) == '&')) {
			self::log_notice('[URL Add Suffix] WARNING: The URL Suffix should not contain ? or start with & :: [URL: '.$y_url.' :: Suffix: '.$y_suffix.']');
		} //end if
		//--
		if(((string)substr((string)$y_url, -1, 1) == '?') OR ((string)substr((string)$y_url, -1, 1) == '&')) {
			$url = (string) $y_url.$y_suffix;
		} elseif(strpos((string)$y_url, '?') === false) {
			$url = (string) $y_url.'?'.$y_suffix;
		} else {
			$url = (string) $y_url.'&'.$y_suffix;
		} //end if else
		//--
		return (string) $url;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Add URL Anchor (to a standard RFC3986 URL) as: script.php?a=b&C=D&e=%20d
	 *
	 * @param 	STRING 		$y_url				:: The base URL to use as prefix like: script.php or script.php?a=b&c=d or empty
	 * @param 	STRING		$y_anchor 			:: A RFC3986 URL anchor like: myAnchor
	 *
	 * @return 	STRING							:: The prepared URL as script.php?a=b&c=d&e=%20d#myAnchor
	 */
	public static function url_add_anchor($y_url, $y_anchor) {
		//--
		$y_url = (string) trim((string)$y_url);
		$y_anchor = (string) trim((string)$y_anchor);
		//--
		if(((string)$y_anchor == '') OR ((string)$y_anchor == '#')) {
			return (string) $y_url.$y_anchor;
		} //end if
		//--
		if((string)substr((string)$y_anchor, 0, 1) == '#') {
			$y_anchor = (string) substr((string)$y_anchor, 1);
		} //end if
		//--
		if(strpos((string)$y_suffix, '#') !== false) {
			self::log_notice('[URL Add Anchor] WARNING: The URL Anchor should not contain # :: [URL: '.$y_url.' :: Suffix: '.$y_anchor.']');
		} //end if
		//--
		return (string) $y_url.'#'.self::escape_url((string)$y_anchor);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Safe escape URL Variable (using RFC3986 standards to be full Unicode compliant)
	 * This is a shortcut to the rawurlencode() to provide a standard into Smart.Framework
	 *
	 * @param 	STRING 		$y_string			:: The variable value to be escaped
	 *
	 * @return 	STRING							:: The escaped URL variable using the RFC3986 standard format (this variable can be appended to URL, by example: ?variable={escaped-value-returned-by-this-method}
	 */
	public static function escape_url($y_string) {
		//--
		return (string) rawurlencode((string)$y_string);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Safe escape strings to be injected in HTML code
	 * This is a shortcut to the htmlspecialchars() to avoid use long options each time and provide a standard into Smart.Framework
	 *
	 * @param 	STRING 		$y_string			:: The string to be escaped
	 *
	 * @return 	STRING							:: The escaped string using htmlspecialchars() standards with Unicode-Safe control
	 */
	public static function escape_html($y_string) {
		//-- v.181203
		// Default is: ENT_HTML401 | ENT_COMPAT
		// keep the ENT_HTML401 instead of ENT_HTML5 to avoid troubles with misc. HTML Parsers (robots, htmldoc, ...)
		// keep the ENT_COMPAT (replace only < > ") and not replace '
		// add ENT_SUBSTITUTE to avoid discard the entire invalid string (with UTF-8 charset) but substitute dissalowed characters with ?
		// enforce 4th param as TRUE as default (double encode)
		//--
		return (string) htmlspecialchars((string)$y_string, ENT_HTML401 | ENT_COMPAT | ENT_SUBSTITUTE, (string)SMART_FRAMEWORK_CHARSET, true); // use charset from INIT (to prevent XSS attacks) ; the 4th parameter double_encode is set to TRUE as default
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Safe escape strings to be injected in CSS code
	 *
	 * @param 	STRING 		$y_string			:: The string to be escaped
	 *
	 * @return 	STRING							:: The escaped string using the WD-CSS21-20060411 standard
	 */
	public static function escape_css($y_string) {
		//-- http://www.w3.org/TR/2006/WD-CSS21-20060411/syndata.html#q6
		return (string) addcslashes((string)$y_string, "\x00..\x1F!\"#$%&'()*+,./:;<=>?@[\\]^`{|}~"); // inspired from Latte Templating
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Safe escape strings to be injected in Javascript code as strings
	 *
	 * @param 	STRING 		$str			:: The string to be escaped
	 *
	 * @return 	STRING						:: The escaped string using a json_encode() standard to be injected between single quotes '' or double quotes ""
	 */
	public static function escape_js($str) {
		//-- v.151129
		// Prepare a string to pass in JavaScript Single or Double Quotes
		// By The Situation:
		// * Using inside tags as <a onClick="self.location = \''.Smart::escape_js($str).'\';"></a>
		// * Using with unsafe strings (come from GET / POST / DB / Untrusted): <script>var my = \''.Smart::escape_js($str).'\';</script>
		// * Using with safe strings (come from language files): <script>var my = \''.Smart::escape_js($str).'\';</script>
		// WARNING: strings may contain HTML Tags ... which if apply Smart::escape_html() may break them.
		// str_replace(array("\\", "\n", "\t", "\r", "\b", "\f", "'"), array('\\\\', '\\n', '\\t', '\\r', '', '', '\\\''), $str); // array('\\\\', '', ' ', '', '', '', '\\\'')
		//-- encode as json
		$encoded = (string) @json_encode((string)$str, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); // encode the string includding unicode chars, with all possible: < > ' " &
		//-- the above will provide a json encoded string as: "mystring" ; we get just what's between double quotes as: mystring
		return (string) substr(trim((string)$encoded), 1, -1);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * JSON Encode PHP variables to a JSON string
	 *
	 * @param 	MIXED 		$data				:: The variable to be encoded (mixed): numeric, string, array
	 * @param 	BOOLEAN 	$prettyprint		:: *Optional* Default to FALSE ; If TRUE will format the json as pretty-print (takes much more space, but sometimes make sense ...)
	 * @param 	BOOLEAN 	$unescaped_unicode 	:: *Optional* Default to TRUE ; If FALSE will escape unicode characters
	 * @param 	BOOLEAN 	$htmlsafe 			:: *Optional* Default to TRUE ; If FALSE the JSON will not be HTML-Safe as it will not escape: < > ' " &
	 *
	 * @return 	STRING							:: The JSON encoded string
	 */
	public static function json_encode($data, $prettyprint=false, $unescaped_unicode=true, $htmlsafe=true) {
		// encode json v.170503
		$options = 0;
		if(!$unescaped_unicode) {
			if($prettyprint) {
				if($htmlsafe) {
					$options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_PRETTY_PRINT;
				} else {
					$options = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
				} //end if else
			} else {
				if($htmlsafe) {
					$options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
				} else {
					$options = JSON_UNESCAPED_SLASHES;
				} //end if else
			} //end if else
		} else { // default
			if($prettyprint) {
				if($htmlsafe) {
					$options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
				} else {
					$options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
				} //end if else
			} else {
				if($htmlsafe) {
					$options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;
				} else {
					$options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
				} //end if else
			} //end if else
		} //end if else
		//--
		$json = (string) @json_encode($data, $options); // Fix: must return a string ; mixed data ; depth was added in PHP 5.5 only !
		if((string)$json == '') { // fix if json encode returns FALSE
			self::log_warning('Invalid Encoded Json in '.__METHOD__.'() for input: '.print_r($data,1));
			$json = 'null';
		} //end if
		//--
		return (string) $json;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Decode JSON strings to PHP native variable(s)
	 *
	 * @param 	STRING 		$json			:: The JSON string
	 * @param 	BOOLEAN		$return_array	:: *Optional* Default to FALSE ; When TRUE, returned objects will be converted into associative arrays (default to TRUE)
	 *
	 * @return 	MIXED						:: The PHP native Variable: NULL ; INT ; NUMERIC ; STRING ; ARRAY
	 */
	public static function json_decode($json, $return_array=true) {
		//-- decode json v.170503
		return @json_decode((string)$json, (bool)$return_array, 512, JSON_BIGINT_AS_STRING); // as json decode depth is added just in PHP 5.5 use the default depth = 512 by now ...
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Serialize PHP variables to a JSON string
	 * This is a safe replacement for PHP serialize() which can break the security if contain unsafe Objects
	 *
	 * @param 	MIXED 		$data			:: The variable to be encoded: numeric, string, array
	 *
	 * @return 	STRING						:: The JSON encoded string
	 */
	public static function seryalize($data) {
		//-- seryalize json v.170503
		return (string) self::json_encode($data, false, false, false); // no pretty print, escaped unicode is safer for Redis, no html safe, depth 512
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Unserialize JSON data to PHP native variable(s)
	 * This is a safe replacement for PHP unserialize() which can break the security if contain unsafe Objects
	 *
	 * @param 	STRING 		$y_json			:: The JSON string
	 *
	 * @return 	MIXED						:: The PHP native Variable
	 */
	public static function unseryalize($y_json) {
		//-- unseryalize json v.170503
		return self::json_decode((string)$y_json, true);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Format a number as INTEGER (NOTICE: On 64-bit systems PHP_INT_MAX is: 9223372036854775807 ; On 32-bit old systems the PHP_INT_MAX is just 2147483647)
	 *
	 * @param 	NUMERIC 	$y_number		:: A numeric value
	 * @param 	ENUM		$y_signed		:: Default to '' ; If set to '+' will return (enforce) an UNSIGNED/POSITIVE Integer, Otherwise if set to '' will return just a regular SIGNED INTEGER wich can be negative or positive
	 *
	 * @return 	INTEGER						:: An integer number
	 */
	public static function format_number_int($y_number, $y_signed='') {
		//--
		if((string)$y_signed == '+') { // unsigned integer
			if((int)$y_number < 0) { // {{{SYNC-SMART-INT+}}}
				$y_number = 0; // it must be zero if negative for the all logic in this framework
			} //end if
		} //end if
		//--
		return (int) $y_number;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Format a number as DECIMAL (NOTICE: The maximum PHP.INI precision is 14, includding decimals).
	 * This is a better replacement for the PHP's number_format() which throws a warning if first argument passed is a string since PHP 5.3
	 *
	 * @param 	NUMERIC 	$y_number			:: A numeric value
	 * @param 	INTEGER+	$y_decimals			:: The number of decimal to use (safe value is between 0..8, keeping in mind the 14 max precision)
	 * @param 	STRING		$y_sep_decimals 	:: The decimal separator symbol as: 	. or , (default is .)
	 * @param 	STRING 		$y_sep_thousands	:: The thousand separator symbol as: 	, or . (default is [none])
	 *
	 * @return 	DECIMAL							:: A decimal number
	 */
	public static function format_number_dec($y_number, $y_decimals=0, $y_sep_decimals='.', $y_sep_thousands='') {
		//-- by default number_format() returns string, so enforce string as output to keep decimals
		return (string) number_format(((float)$y_number), self::format_number_int($y_decimals,'+'), (string)$y_sep_decimals, (string)$y_sep_thousands); // {{{SYNC-SMART-DECIMAL}}}
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Safe array count(), for safety, with array type check ; this should be used instead of count() because count(string) returns a non-zero value and can confuse if a string is passed to count instead of an array
	 *
	 * @param ARRAY 		$y_arr			:: The array to count elements on
	 *
	 * @return INTEGER 						:: The array COUNT of elements, or zero if array is empty or non-array is provided
	 */
	public static function array_size($y_arr) {
		//--
		if(is_array($y_arr)) {
			return count($y_arr);
		} else {
			return 0;
		} //end if else
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Easy sort for NON-Associative arrays ...
	 *
	 * @param ARRAY 		$y_arr			:: The array to be sorted by a criteria (type, see below)
	 * @param ENUM 			$y_mode			:: The sort type: natsort, sort, rsort, asort, arsort, ksort, krsort
	 *
	 * @return ARRAY 						:: The sorted array
	 */
	public static function array_sort($y_arr, $y_mode) {
		//--
		if(self::array_size($y_arr) <= 0) {
			return array();
		} //end if
		//--
		switch(strtolower((string)$y_mode)) {
			case 'natsort': // natural sort
				@natsort($y_arr);
				break;
			case 'natcasesort': // natural case-sensitive sort
				@natcasesort($y_arr);
				break;
			case 'sort': // regular sort
				@sort($y_arr);
				break;
			case 'rsort': // regular reverse sort
				@rsort($y_arr);
				break;
			case 'asort': // associative sort
				@asort($y_arr);
				break;
			case 'arsort': // associative reverse sort
				@arsort($y_arr);
				break;
			case 'ksort': // key sort
				@ksort($y_arr);
				break;
			case 'krsort': // key reverse sort
				@krsort($y_arr);
				break;
			default:
				self::log_warning('WARNING: Invalid Sort Mode in '.__CLASS__.'::'.__FUNCTION__.'()'.' : '.$y_mode);
				return (array) $y_arr;
		} //end switch
		//--
		return (array) array_values((array)$y_arr);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Shuffle for NON-Associative arrays ...
	 *
	 * @param ARRAY 		$y_arr			:: The array to be sorted by a criteria (type, see below)
	 *
	 * @return ARRAY 						:: The sorted array
	 */
	public static function array_shuffle($y_arr) {
		//--
		if(self::array_size($y_arr) <= 0) {
			return array();
		} //end if
		//--
		shuffle($y_arr);
		//--
		return (array) $y_arr;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Array Get (value) By Key Path (case sensitive)
	 *
	 * @param ARRAY 		$y_arr 					:: The input array
	 * @param STRING 		$y_key_path 			:: The composed key path by levels (Ex: key1.key2) :: case-sensitive
	 * @param STRING 		$y_path_separator 		:: The key path separator (Example: .)
	 *
	 * @return MIXED [ NUMERIC / STRING / ARRAY ] 	:: The array value of the specified key path
	 */
	public static function array_get_by_key_path($y_arr, $y_key_path, $y_path_separator) {
		//--
		if(self::array_size($y_arr) <= 0) {
			return '';
		} //end if
		//--
		$y_key_path = (string) trim((string)$y_key_path);
		$y_path_separator = (string) trim((string)$y_path_separator);
		//--
		if((string)$y_key_path == '') {
			return ''; // dissalow empty key path
		} //end if
		//--
		if(strlen($y_path_separator) != 1) {
			return ''; // dissalow empty separator
		} //end if
		//--
		$arr = (array) explode((string)$y_path_separator, (string)$y_key_path);
		$max = count($arr);
		for($i=0; $i<$max; $i++) {
			if((string)trim((string)$arr[$i]) != '') {
				if(is_array($y_arr)) {
					if(array_key_exists($arr[$i], $y_arr)) {
						$y_arr = $y_arr[$arr[$i]]; // array, string or number
					} else {
						$y_arr = '';
						break;
					} //end if
				} else {
					$y_arr = '';
					break;
				} //end if
			} else {
				$y_arr = '';
				break;
			} //end if
		} //end for
		//--
		if($y_arr === null) {
			$y_arr = '';
		} //end if
		//--
		return $y_arr; // mixed
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Array Test if Key Exist By (Key) Path (case sensitive)
	 *
	 * @param ARRAY 		$y_arr 					:: The input array
	 * @param STRING 		$y_key_path 			:: The composed key path by levels (Ex: key1.key2) :: case-sensitive
	 * @param STRING 		$y_path_separator 		:: The key path separator (Example: .)
	 *
	 * @return BOOL 								:: TRUE if Key Exist / FALSE if NOT
	 */
	public static function array_test_key_by_path_exists($y_arr, $y_key_path, $y_path_separator) {
		//--
		if(self::array_size($y_arr) <= 0) {
			return false;
		} //end if
		//--
		$y_key_path = (string) trim((string)$y_key_path);
		$y_path_separator = (string) trim((string)$y_path_separator);
		//--
		if((string)$y_key_path == '') {
			return false; // dissalow empty key path
		} //end if
		//--
		if(strlen($y_path_separator) != 1) {
			return false; // dissalow empty separator
		} //end if
		//--
		$arr = (array) explode((string)$y_path_separator, (string)$y_key_path);
		$max = count($arr);
		$tarr = (array) $y_arr;
		for($i=0; $i<$max; $i++) {
			$arr[$i] = (string) trim((string)$arr[$i]);
			if((string)$arr[$i] != '') {
				if(!is_array($tarr)) {
					return false;
				} //end if
				if(!array_key_exists((string)$arr[$i], (array)$tarr)) {
					return false;
				} //end if
				$tarr = $tarr[(string)$arr[$i]];
			} //end if
		} //end for
		//--
		return true;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Array Recursive Change Key Case
	 *
	 * @param ARRAY 		$y_arr 					:: The input array
	 * @param ENUM 			$y_mode 				:: Change Mode: LOWER | UPPER
	 *
	 * @return ARRAY 								:: The modified array
	 */
	public static function array_change_key_case_recursive($y_arr, $y_mode) {
		//--
		if(self::array_size($y_arr) <= 0) { // fix bug if empty array / max nested level
			return array();
		} //end if
		//--
		switch((string)strtoupper((string)$y_mode)) {
			case 'UPPER':
				$case = CASE_UPPER;
				break;
			case 'LOWER':
				$case = CASE_LOWER;
				break;
			default:
				return (array) $y_arr;
		} //end if
		//--
		return (array) array_map(
			function($y_newarr) use($y_mode) {
				if(is_array($y_newarr)) {
					$y_newarr = self::array_change_key_case_recursive($y_newarr, $y_mode);
				} //end if
				return $y_newarr; // mixed
			},
			array_change_key_case($y_arr, $case)
		);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Test if the Array Type for being associative or non-associative sequential (0..n)
	 *
	 * @param ARRAY 		$y_arr			:: The array to test
	 *
	 * @return ENUM 						:: The array type as: 0 = not an array ; 1 = non-associative (sequential) array or empty array ; 2 = associative array or non-sequential, must be non-empty
	 */
	public static function array_type_test($y_arr) {
		//--
		if(!is_array($y_arr)) {
			return 0; // not an array
		} //end if
		//--
	//	$c = (int) count($y_arr);
	//	if(((int)$c <= 0) OR ((array)array_keys($y_arr) === (array)range(0, ((int)$c - 1)))) { // most elegant, but slow
		//--
	//	$a = (array) array_keys((array)$y_arr);
	//	if((array)$a === (array)array_keys((array)$a)) { // memory-optimized (prev OK)
		//--
		if((array)array_values((array)$y_arr) === (array)$y_arr) { // speed-optimized, 10x faster with non-associative large arrays, tested in all scenarios with large or small arrays
			return 1; // non-associative
		} else {
			return 2; // associative
		} //end if else
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Array recursive Diff (Dual-Way, from Left to Right and from Right to Left)
	 *
	 * @param ARRAY $array1
	 * @param ARRAY $array2
	 *
	 * @return ARRAY
	 */
	public static function array_diff_assoc_recursive($array1, $array2) {
		//--
		if(!is_array($array1)) {
			self::log_warning('WARNING: '.__CLASS__.'::'.__FUNCTION__.'()'.' array#1 is not array !');
			return array();
		} //end if
		if(!is_array($array2)) {
			self::log_warning('WARNING: '.__CLASS__.'::'.__FUNCTION__.'()'.' array#2 is not array !');
			return array();
		} //end if
		//--
		$diff_1 = (array) self::array_diff_assoc_oneway_recursive($array1, $array2);
		$diff_2 = (array) self::array_diff_assoc_oneway_recursive($array2, $array1);
		//--
		return (array) array_merge_recursive((array)$diff_1, (array)$diff_2);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Array recursive Diff (One Way Only, from Left to Right)
	 *
	 * @param ARRAY $array1
	 * @param ARRAY $array2
	 *
	 * @return ARRAY
	 */
	public static function array_diff_assoc_oneway_recursive($array1, $array2) {
		//--
		if(!is_array($array1)) {
			self::log_warning('WARNING: '.__CLASS__.'::'.__FUNCTION__.'()'.' array#1 is not array !');
			return array();
		} //end if
		if(!is_array($array2)) {
			self::log_warning('WARNING: '.__CLASS__.'::'.__FUNCTION__.'()'.' array#2 is not array !');
			return array();
		} //end if
		//--
		$difference = array();
		//--
		foreach($array1 as $key => $value) {
			if(is_array($value)) {
				if(!isset($array2[$key]) || !is_array($array2[$key])) {
					$difference[$key] = $value;
				} else {
					$new_diff = self::array_diff_assoc_oneway_recursive($value, $array2[$key]);
					if(!empty($new_diff)) {
						$difference[$key] = $new_diff;
					} //end if
				} //end if else
			} elseif(!array_key_exists($key, $array2) || $array2[$key] != $value) { // !==
				$difference[$key] = $value;
			} //end if else
		} //end foreach
		//--
		return (array) $difference;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Cut a text by a fixed length, if longer than allowed length.
	 * If cut words option is FALSE it may make the string shorted by rolling back until the last space and cutting off the last partial word.
	 * The default cut suffix is (...) but can be disabled using the last parameter.
	 *
	 * @param 	STRING 	$ytxt 				:: The text string to be processed
	 * @param 	STRING 	$ylen 				:: The fixed length of the string
	 * @param 	BOOLEAN	$y_cut_words		:: *Optional* Default TRUE ; if TRUE, will CUT last word to provide a fixed length ; if FALSE will eliminate unterminate last word ; default is TRUE
	 * @param 	ENUM 	$y_suffix 			:: *Optional* Default '...' ; Can be '' or '[...a cutoff-message...]'
	 *
	 * @return 	STRING						:: The processed string (text)
	 */
	public static function text_cut_by_limit($ytxt, $ylen, $y_cut_words=true, $y_suffix='...') {
		//--
		$ytxt = (string) trim((string)$ytxt);
		$ylen = self::format_number_int($ylen, '+');
		//--
		if((string)$y_suffix == '') {
			$cutoff = (int) $ylen;
		} else {
			$cutoff = (int) self::format_number_int(($ylen - SmartUnicode::str_len($y_suffix)), '+');
		} //end if else
		if($cutoff <= 0) {
			$cutoff = 1;
		} //end if
		//--
		if(SmartUnicode::str_len($ytxt) > $cutoff) {
			//--
			$ytxt = (string) SmartUnicode::sub_str($ytxt, 0, $cutoff);
			//--
			if($y_cut_words === false) {
				//-- {{{SYNC-REGEX-TEXT-CUTOFF}}}
				$ytxt = (string) preg_replace('/\s+?(\S+)?$/', '', (string)$ytxt); // cut off last word until first space (if no space, no cut)
				//--
			} //end if
			//--
			$ytxt .= (string) $y_suffix;
			//--
		} //end if
		//--
		return (string) $ytxt;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Easy HTML5 compliant nl2br() ; Will replace new lines (\n) with HTML5 <br> instead of XHTML <br />
	 *
	 * @param STRING 		$y_code			:: The string to apply nl2br()
	 *
	 * @return STRING 						:: The formatted string
	 */
	public static function nl_2_br($y_code) {
		//--
		return nl2br(trim($y_code), false); // 2nd param is false for not xhtml tags, since PHP 5.3 !!
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Enhanced strip_tags() :: will revert also special entities like nbsp; and more
	 *
	 * @param ARRAY 		$yhtmlcode		:: HTML Code to be stripped of tags
	 * @param YES/NO 		$y_mode			:: yes to convert <br> to new lines \n, otherwise (if no) will convert <br> to spaces
	 *
	 * @return STRING 						:: The processed HTML Code
	 */
	public static function striptags($yhtmlcode, $ynewline='yes') {
		//--
		$yhtmlcode = (string) $yhtmlcode;
		$ynewline = (string) $ynewline;
		//-- fix xhtml tag ends and add spaces between tags
		$yhtmlcode = (string) str_replace([' />', '/>', '>'], ['>', '>', '> '], (string)$yhtmlcode);
		//-- remove special tags
		$html_regex_h = [
			'#<head[^>]*?'.'>.*?</head[^>]*?'.'>#si',				// head
			'#<style[^>]*?'.'>.*?</style[^>]*?'.'>#si',				// style
			'#<script[^>]*?'.'>.*?</script[^>]*?'.'>#si',			// script
			'#<noscript[^>]*?'.'>.*?</noscript[^>]*?'.'>#si',		// noscript
			'#<frameset[^>]*?'.'>.*?</frameset[^>]*?'.'>#si',		// frameset
			'#<frame[^>]*?'.'>.*?'.'</frame[^>]*?'.'>#si',			// frame
			'#<iframe[^>]*?'.'>.*?'.'</iframe[^>]*?'.'>#si',		// iframe
			'#<canvas[^>]*?'.'>.*?'.'</canvas[^>]*?'.'>#si',		// canvas
			'#<audio[^>]*?'.'>.*?'.'</audio[^>]*?'.'>#si',			// audio
			'#<video[^>]*?'.'>.*?'.'</video[^>]*?'.'>#si',			// video
			'#<applet[^>]*?'.'>.*?'.'</applet[^>]*?'.'>#si',		// applet
			'#<param[^>]*?'.'>.*?'.'</param[^>]*?'.'>#si',			// param
			'#<object[^>]*?'.'>.*?'.'</object[^>]*?'.'>#si',		// object
			'#<form[^>]*?'.'>.*?'.'</form[^>]*?'.'>#si',			// form
			'#<link[^>]*?'.'>#si',									// link
			'#<img[^>]*?'.'>#si'									// img
		];
		$yhtmlcode = (string) preg_replace((array)$html_regex_h, ' ', (string)$yhtmlcode);
		$yhtmlcode = str_replace(["\r\n", "\r", "\t", "\f"], ["\n", "\n", ' ', ' '], $yhtmlcode);
		//-- replace new line tags
		if((string)$ynewline == 'yes') {
			$replchr = "\n"; // newline
		} else {
			$replchr = ' '; // space
		} //end if else
		$yhtmlcode = (string) str_ireplace(['<br>', '</br>'], [(string)$replchr, ''], (string)$yhtmlcode);
		//-- strip the tags
		$yhtmlcode = (string) strip_tags((string)$yhtmlcode);
		//-- restore some usual html entities
		$arr_replacements = [
			'&nbsp;' 	=> ' ',
			'&amp;' 	=> '&',
			'&quot;' 	=> '"',
			'&apos;' 	=> "'",
			'&#039;' 	=> "'",
			'&lt;' 		=> '<',
			'&gt;' 		=> '>',
			'&middot;' 	=> '.',
			'&bull;' 	=> '.',
			'&sdot;' 	=> '.',
			'&copy;' 	=> '(c)',
			'&reg;' 	=> '(R)',
			'&trade;' 	=> '(TM)',
			'&curren;' 	=> '¤',
			'&euro;' 	=> '€',
			'&cent;' 	=> '¢',
			'&pound;' 	=> '£',
			'&yen;' 	=> '¥',
			'&lsaquo;' 	=> '‹',
			'&rsaquo;' 	=> '›',
			'&laquo;' 	=> '«',
			'&raquo;' 	=> '»',
			'&lsquo;' 	=> '‘',
			'&rsquo;' 	=> '’',
			'&ldquo;' 	=> '“',
			'&rdquo;' 	=> '”',
			'&acute;' 	=> '`',
			'&prime;' 	=> '`',
			'&ndash;' 	=> '-',
			'&mdash;' 	=> '-',
			'&minus;' 	=> '-',
			'&macr;' 	=> '-',
			'&uml;' 	=> '..',
			'&hellip;' 	=> '...',
			'&tilde;' 	=> '~',
			'&sim;' 	=> '~',
			'&circ;' 	=> '^',
			'&spades;' 	=> '♠',
			'&clubs;' 	=> '♣',
			'&hearts;' 	=> '♥',
			'&diams;' 	=> '♦',
			'&fnof;' 	=> 'ƒ',
			'&radic;' 	=> '√',
			'&sum;' 	=> '∑',
			'&prod;' 	=> '∏',
			'&int;' 	=> '∫',
			'&infin;' 	=> '∞',
			'&lowast;' 	=> '*',
			'&divide;' 	=> '÷',
			'&times;' 	=> 'x',
			'&frac12;' 	=> '1/2',
			'&frac14;' 	=> '1/4',
			'&frac34;' 	=> '3/4',
			'&brvbar;' 	=> '¦',
			'&sect;' 	=> '§',
			'&para;' 	=> '¶',
			'&micro;' 	=> 'µ',
			'&iexcl;' 	=> '¡',
			'&iquest;' 	=> '¿',
			'&deg;' 	=> '°',
			'&ordm;' 	=> 'º',
			'&plusmn;' 	=> '±',
			'&sup1;' 	=> '¹',
			'&sup2;' 	=> '²',
			'&sup3;' 	=> '³',
			'&ordf;' 	=> 'ª',
			'&cedil;' 	=> '¸',
			'&not;' 	=> '¬',
			'&forall;' 	=> '∀',
			'&part;' 	=> '∂',
			'&exist;' 	=> '∃',
			'&empty;' 	=> '∅',
			'&nabla;' 	=> '∇',
			'&isin;' 	=> '∈',
			'&notin;' 	=> '∉',
			'&ni;' 		=> '∋',
			'&prop;' 	=> '∝',
			'&ang;' 	=> '∠',
			'&and;' 	=> '∧',
			'&or;' 		=> '∨',
			'&cap;' 	=> '∩',
			'&cup;' 	=> '∪',
			'&there4;' 	=> '∴',
			'&cong;' 	=> '≅',
			'&asymp;' 	=> '≈',
			'&ne;' 		=> '≠',
			'&equiv;' 	=> '≡',
			'&le;' 		=> '≤',
			'&ge;' 		=> '≥',
			'&sub;' 	=> '⊂',
			'&sup;' 	=> '⊃',
			'&nsub;' 	=> '⊄',
			'&sube;' 	=> '⊆',
			'&supe;' 	=> '⊇',
			'&oplus;' 	=> '+',
			'&otimes;' 	=> 'x',
			'&perp;' 	=> '⊥'
		];
		$yhtmlcode = (string) str_replace((array)array_keys((array)$arr_replacements), (array)array_values((array)$arr_replacements), (string)$yhtmlcode);
		//-- if new tags may appear after strip tags that is natural as they were encoded already with entities ... ; Anyway, the following can't be used as IT BREAKS TEXT THAT COMES AFTER < which was previous encoded as &lt; !!!
		//$yhtmlcode = (string) strip_tags((string)$yhtmlcode); // fix: after all fixes when reversing entities, new tags can appear that were encoded, so needs run again for safety ...
		//-- restore html unicode entities
		$html_accents = (array) SmartUnicode::accented_html_entities();
		$yhtmlcode = (string) str_replace((array)array_values($html_accents), (array)array_keys($html_accents), (string)$yhtmlcode);
		//-- try to convert other remaining html entities
		$yhtmlcode = (string) html_entity_decode((string)$yhtmlcode, ENT_HTML5, SMART_FRAMEWORK_CHARSET);
		//-- clean any other remaining html entities
		$yhtmlcode = (string) preg_replace('/&\#?([0-9a-z]+);/i', ' ', (string)$yhtmlcode);
		//-- cleanup multiple spaces with just one space
		$yhtmlcode = (string) preg_replace('/[ \\t]+/', ' ', (string)$yhtmlcode); // replace any horizontal whitespace character ' since PHP 5.4 can be /[\h]+/
		$yhtmlcode = (string) preg_replace('/^\s*[\n]{2,}/m', '', (string)$yhtmlcode); // fix: replace multiple consecutive lines that may also contain before optional leading spaces
		$yhtmlcode = (string) preg_replace('/[^\S\r\n]+$/m', '', (string)$yhtmlcode); // remove trailing spaces on each line
		//--
		return (string) trim((string)$yhtmlcode);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Test and Fix if unsafe detected for: safe path / safe filename / safe valid name / safe username / safe varname
	 * This is intended to be used against the result of above functions to avoid generate an unsafe file system path (ex: . or .. or / or /. or /..)
	 * Because all the above functions may return an empty (string) result, if unsafe sequences are detected will just fix it by clear the result (enforce empty string is better than unsafe)
	 * It should allow also both: absolute and relative paths, thus if absolute path should be tested later
	 *
	 * @access 		private
	 * @internal
	 *
	 * @return STRING 						:: The fixed (filesys) safe string
	 *
	 */
	public static function safe_fix_invalid_filesys_names($y_fsname) {
		//-- v.190105
		$y_fsname = (string) trim((string)$y_fsname);
		//-- {{{SYNC-SAFE-PATH-CHARS}}} {{{SYNC-CHK-SAFE-PATH}}}
		if(
			((string)$y_fsname == '.') OR
			((string)$y_fsname == '..') OR
			((string)$y_fsname == ':') OR
			((string)$y_fsname == '/') OR
			((string)$y_fsname == '/.') OR
			((string)$y_fsname == '/..') OR
			((string)$y_fsname == '/:') OR
			((string)ltrim((string)$y_fsname, '/') == '.') OR
			((string)ltrim((string)$y_fsname, '/') == '..') OR
			((string)ltrim((string)$y_fsname, '/') == ':') OR
			((string)trim((string)$y_fsname, '/') == '') OR
			((string)substr((string)$y_fsname, -2, 2) == '/.') OR
			((string)substr((string)$y_fsname, -3, 3) == '/..')
		) {
			$y_fsname = '';
		} //end if
		//--
		return (string) $y_fsname; // returns trimmed value or empty if non-safe
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Create a Safe Path Name to be used to process dynamic build paths to avoid weird path character injections
	 * This should be used for relative or absolute path to files or dirs
	 * It should allow also both: absolute and relative paths, thus if absolute path should be tested later
	 * NOTICE: It may return an empty string if all characters in the path are invalid or invalid path sequences detected, so if empty path name must be tested later
	 * ALLOWED CHARS: [a-zA-Z0-9] _ - . @ # /
	 *
	 * @param STRING 		$y_path			:: Path to be processed
	 * @param STRING 		$ysupresschar	:: The suppression character to replace weird characters ; optional ; default is ''
	 *
	 * @return STRING 						:: The safe path ; if invalid will return empty value
	 */
	public static function safe_pathname($y_path, $ysupresschar='') {
		//-- v.170920
		$y_path = (string) trim((string)$y_path); // force string and trim
		if((string)$y_path == '') {
			return '';
		} //end if
		//--
		if(preg_match('/^[_a-zA-Z0-9\-\.@#\/]+$/', (string)$y_path)) { // {{{SYNC-CHK-SAFE-PATH}}}
			return (string) self::safe_fix_invalid_filesys_names($y_path);
		} //end if
		//--
		$ysupresschar = (string) $ysupresschar; // force string and be sure is lower
		switch((string)$ysupresschar) {
			case '-':
			case '_':
				break;
			default:
				$ysupresschar = '';
		} //end if
		//--
		$y_path = (string) preg_replace((string)self::lower_unsafe_characters(), '', (string)$y_path); // remove dangerous characters
		$y_path = (string) SmartUnicode::utf8_to_iso($y_path); // bring STRING to ISO-8859-1
		$y_path = (string) stripslashes($y_path); // remove any possible back-slashes
		$y_path = (string) self::normalize_spaces($y_path); // normalize spaces to catch null seq.
		//$y_path = (string) str_replace('?', $ysupresschar, $y_path); // replace questionmark (that may come from utf8 decode) ; this is already done below
		$y_path = (string) preg_replace('/[^_a-zA-Z0-9\-\.@#\/]/', $ysupresschar, $y_path); // {{{SYNC-SAFE-PATH-CHARS}}} suppress any other characters than these, no unicode modifier
		$y_path = (string) preg_replace("/(\.)\\1+/", '.', $y_path); // suppress multiple . dots and replace with single dot
		$y_path = (string) preg_replace("/(\/)\\1+/", '/', $y_path); // suppress multiple // slashes and replace with single slash
		$y_path = (string) str_replace(array('../', './'), array('-', '-'), $y_path); // replace any unsafe path combinations (do not suppress but replace with a fixed character to avoid create security breaches)
		$y_path = (string) trim($y_path); // finally trim it
		//--
		return (string) self::safe_fix_invalid_filesys_names($y_path);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Create a Safe File Name to be used to process dynamic build file names or dir names to avoid weird path character injections
	 * To should be used only for file or dir names (not paths)
	 * NOTICE: DO NOT USE for full paths or full dir paths because will break them, as the / character is supressed
	 * NOTICE: It may return an empty string if all characters in the file/dir name are invalid or invalid path sequences detected, so if empty file/dir name must be tested later
	 * ALLOWED CHARS: [a-zA-Z0-9] _ - . @ #
	 *
	 * @param STRING 		$y_fname		:: File Name or Dir Name to be processed
	 * @param STRING 		$ysupresschar	:: The suppression character to replace weird characters ; optional ; default is ''
	 *
	 * @return STRING 						:: The safe file or dir name ; if invalid will return empty value
	 */
	public static function safe_filename($y_fname, $ysupresschar='') {
		//-- v.170920
		$y_fname = (string) trim((string)$y_fname); // force string and trim
		if((string)$y_fname == '') {
			return '';
		} //end if
		//--
		if(preg_match('/^[_a-zA-Z0-9\-\.@#]+$/', (string)$y_fname)) { // {{{SYNC-CHK-SAFE-FILENAME}}}
			return (string) self::safe_fix_invalid_filesys_names($y_fname);
		} //end if
		//--
		$ysupresschar = (string) $ysupresschar; // force string and be sure is lower
		switch((string)$ysupresschar) { // DO NOT ALLOW DOT . AS IS SECURITY RISK, replaced below
			case '-':
			case '_':
				break;
			default:
				$ysupresschar = '';
		} //end if
		//--
		$y_fname = (string) self::safe_pathname($y_fname, $ysupresschar);
		$y_fname = (string) str_replace('/', '-', $y_fname); // replace the path character with a fixed character (do not suppress to avoid create security breaches)
		$y_fname = (string) trim($y_fname); // finally trim it
		//--
		return (string) self::safe_fix_invalid_filesys_names($y_fname);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Creates a Safe Valid Variable Name
	 * NOTICE: this have a special usage and must allow also 0..9 as prefix because is can be used for other purposes not just for real safe variable names, thus if real safe valid variable name must be tested later (real safe variable names cannot start with numbers ...)
	 * NOTICE: It may return an empty string if all characters in the given variable name are invalid or invalid path sequences detected, so if empty variable name must be tested later
	 * ALLOWED CHARS: [a-zA-Z0-9] _
	 *
	 * @param STRING 		$y_name				:: Variable Name to be processed
	 *
	 * @return STRING 							:: The safe variable name ; if invalid should return empty value
	 */
	public static function safe_varname($y_name, $y_allow_upper=false) {
		//-- v.20200121
		$y_name = (string) trim((string)$y_name); // force string and trim
		if((string)$y_name == '') {
			return '';
		} //end if
		//--
		if(preg_match('/^[_a-zA-Z0-9]+$/', (string)$y_name)) {
			return (string) self::safe_fix_invalid_filesys_names($y_name);
		} //end if
		//--
		$y_name = (string) self::safe_filename($y_name, '-');
		$y_name = (string) str_replace(array('-', '.', '@', '#'), '', $y_name); // replace the invalid - . @ #
		$y_name = (string) trim($y_name);
		//--
		return (string) self::safe_fix_invalid_filesys_names($y_name);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Create a (RFC, ISO) Safe compliant User Name, Domain Name or Email Address
	 * NOTICE: It may return an empty string if all characters in the given name are invalid or invalid path sequences detected, so if empty name must be tested later
	 * ALLOWED CHARS: [a-z0-9] _ - . @
	 *
	 * @param STRING 		$y_name			:: Name to be processed
	 * @param STRING 		$ysupresschar	:: The suppression character to replace weird characters ; optional ; default is ''
	 *
	 * @return STRING 						:: The safe name ; if invalid should return empty value
	 */
	public static function safe_validname($y_name, $ysupresschar='') {
		//-- v.170920
		$y_name = (string) trim((string)$y_name); // force string and trim
		if((string)$y_name == '') {
			return '';
		} //end if
		//--
		if(preg_match('/^[_a-z0-9\-\.@]+$/', (string)$y_name)) {
			return (string) self::safe_fix_invalid_filesys_names($y_name);
		} //end if
		//--
		$ysupresschar = (string) $ysupresschar; // force string and be sure is lower
		switch((string)$ysupresschar) {
			case '-':
			case '_':
				break;
			default:
				$ysupresschar = '';
		} //end if
		//--
		$y_name = (string) self::safe_filename($y_name, $ysupresschar);
		$y_name = (string) strtolower($y_name); // make all lower chars
		$y_name = (string) str_replace('#', '', $y_name); // replace also diez
		$y_name = (string) trim($y_name);
		//--
		return (string) self::safe_fix_invalid_filesys_names($y_name);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Create a Safe Valid Strict User Name
	 * NOTICE: It may return an empty string if all characters in the given name are invalid or invalid path sequences detected, so if empty name must be tested later
	 * ALLOWED CHARS: [a-z0-9] .
	 *
	 * @param STRING 		$y_name			:: Name to be processed
	 *
	 * @return STRING 						:: The safe name ; if invalid should return empty value
	 */
	public static function safe_username($y_name) {
		//-- v.170920
		$y_name = (string) trim((string)$y_name); // force string and trim
		if((string)$y_name == '') {
			return '';
		} //end if
		//--
		if(preg_match('/^[a-z0-9\.]+$/', (string)$y_name)) {
			return (string) self::safe_fix_invalid_filesys_names($y_name);
		} //end if
		//--
		$y_name = (string) self::safe_validname($y_name, '.');
		$y_name = (string) str_replace(array('@', '-', '_'), '', $y_name); // replace also @ - _
		$y_name = (string) trim($y_name);
		//--
		return (string) self::safe_fix_invalid_filesys_names($y_name);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Creates a Slug (URL safe slug) from a string
	 *
	 * @param STRING 		$y_str			:: The string to be processed
	 * @param BOOLEAN 		$y_lowercase 	:: *OPTIONAL* If TRUE will return the slug with enforced lowercase characters ; DEFAULT is FALSE
	 *
	 * @return STRING 						:: The slug which will contain only: a-z 0-9 _ - (A-Z will be converted to a-z if lowercase is enforced)
	 */
	public static function create_slug($y_str, $y_lowercase=false) {
		//--
		$y_str = (string) SmartUnicode::deaccent_str((string)trim((string)$y_str));
		$y_str = (string) preg_replace('/[^a-zA-Z0-9_\-]/', '-', (string)$y_str);
		$y_str = (string) trim((string)preg_replace('/[\-]+/', '-', (string)$y_str)); // suppress multiple -
		//--
		if($y_lowercase === true) {
			$y_str = (string) strtolower((string)$y_str);
		} //end if
		//--
		return (string) $y_str;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Creates a compliant HTML-ID (HTML ID used for HTML elements) from a string
	 *
	 * @param STRING 		$y_str			:: The string to be processed
	 *
	 * @return STRING 						:: The HTML-ID which will contain only: a-z A-Z 0-9 _ -
	 */
	public static function create_htmid($y_str) {
		//--
		return (string) trim((string)preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$y_str));
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Creates a compliant Js-Var (JavaScript Variable Name) from a string
	 *
	 * @param STRING 		$y_str			:: The string to be processed
	 *
	 * @return STRING 						:: The Js-Var which will contain only: a-z A-Z 0-9 _
	 */
	public static function create_jsvar($y_str) {
		//--
		return (string) trim((string)preg_replace('/[^a-zA-Z0-9_]/', '', (string)$y_str));
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Normalize Spaces
	 * This will replace: "\r", "\n", "\t", "\x0B", "\0", "\f" with normal space ' '
	 *
	 * @param STRING 		$y_txt			:: Text to be normalized
	 *
	 * @return STRING 						:: The normalized text
	 */
	public static function normalize_spaces($y_txt) {
		//--
		return (string) str_replace(["\r\n", "\r", "\n", "\t", "\x0B", "\0", "\f"], ' ', (string)$y_txt);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Check if an integer number overflows the maximum safe int
	 * All numbers over this must use special operators from BCMath to avoid floating point precision issues
	 * On 32-bit platforms the INTEGER is between 		   -2147483648 		to 				 2147483647
	 * On 64-bit platforms the INTEGER is between -9223372036854775808 		to		9223372036854775807
	 *
	 * @param INTEGER NUMBER 	$y_number	:: The integer number to be checked
	 *
	 * @return BOOLEAN 						:: TRUE if overflows the max safe integer ; FALSE if is OK (not overflow maximum)
	 */
	public static function check_int_number_overflow_max($y_number) {
		//--
		if(abs((int)$y_number) > (int)PHP_INT_MAX) {
			$out = true;
		} else {
			$out = false;
		} //end if else
		//--
		return (bool) $out;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Check if a decimal number overflows the maximum safe of 14 precision
	 * All numbers over this must use special operators from BCMath to avoid floating point precision issues
	 * This is more intended for decimal numbers like financial operations where the significant decimal digits are important
	 *
	 * @param DECIMAL NUMBER 	$y_number	:: The decimal number to be checked
	 *
	 * @return BOOLEAN 						:: TRUE if overflows the max safe decimal ; FALSE if is OK (not overflow maximum)
	 */
	public static function check_dec_number_overflow_max($y_number) {
		//--
		$max_number = '999999999999.9900'; // DECIMAL [12].[4] (no higher decimal numbers than this are safe using a precision like 14, the max in PHP)
		//--
		if(abs((float)$y_number) > (float)$max_number) {
			$out = true;
		} else {
			$out = false;
		} //end if else
		//--
		return (bool) $out;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Generates an integer random number between min and max using mt_rand() which is4x times faster than rand().
	 * It may use a random seed based on microtime or custom using mt_srand() which uses MT_RAND_MT19937 for PHP >= 7.1
	 * NOTICE: using a time based seed may result in most of the calls to a random number may return the same number which perhaps is not what is expected !!
	 * The min is zero. The max is limited to 2147483647 on most of the platforms.
	 *
	 * @return INTEGER 						:: An integer random number
	 */
	public static function random_number($y_min=0, $y_max=-1, $y_seed=false) {
		//-- seed the mt_rand() using mt_srand()
		if($y_seed !== false) {
			if($y_seed === true) {
				$y_seed = (int) (microtime(true) * 10000);
			} //end if
			mt_srand((int)$y_seed);
		} //end if
		//-- the mt_rand() is 4x times faster than rand() ; but the max is limited to 2147483647 on most of the platforms
		if((int)$y_min < 0) {
			$y_min = 0;
		} //end if
		if((int)$y_max < 0) {
			$y_max = mt_getrandmax();
		} //end if
		if($y_min > $y_max) {
			$y_min = $y_max;
		} //end if
		//--
		return (int) mt_rand((int)$y_min, (int)$y_max);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Returns the valid Net Server ID (to be used in a cluster)
	 * Valid values are 0..1295 (or 00..ZZ if BASE36)
	 */
	public static function net_server_id($base36=false) { // {{{SYNC-MIN-MAX-NETSERVER-ID}}}
		//--
		$netserverid = (int) 0;
		if(defined('SMART_FRAMEWORK_NETSERVER_ID')) {
			$netserverid = (int) SMART_FRAMEWORK_NETSERVER_ID;
		} //end if
		//--
		if($netserverid < 0) {
			$netserverid = 0;
		} elseif($netserverid > 1295) {
			$netserverid = 1295;
		} //end if else
		//--
		if($base36 === true) {
			$netserverid = strtoupper((string)sprintf('%02s', base_convert($netserverid, 10, 36))); // 00..ZZ
		} //end if
		//--
		return (string) $netserverid; // return int as string
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Generates a time based entropy as replacement for uniqid() to ensure is unique in time and space.
	 * It is based on a full unique signature in space and time: server name, server unique id, a unique time sequence that repeats once in 1000 years and 2 extremely large (fixed length) random values .
	 * If a suffix is provided will append it.
	 *
	 * @return STRING 						:: variable length Unique Entropy string
	 */
	public static function unique_entropy($y_suffix='', $y_use_net_server_id=true) {
		//--
		$netserverid = '';
		if($y_use_net_server_id !== false) {
			$netserverid = (string) self::net_server_id();
		} //end if
		//--
		return (string) 'Namespace:'.SMART_SOFTWARE_NAMESPACE.'NetServer#'.$netserverid.'UUIDUSequence='.self::uuid_13_seq().';UUIDSequence='.self::uuid_10_seq().';UUIDRandStr='.self::uuid_10_str().';UUIDRandNum='.self::uuid_10_num().';'.$y_suffix;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	// converts a 64-bit integer number to base62 (string)
	private static function int10_to_base62_str($num) {
		//--
		$num = (int) $num;
		if($num < 0) {
			$num = 0;
		} //end if
		//--
		$b = 62;
		$base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		//--
		$r = (int) $num % $b;
		$res = (string) $base[$r];
		//--
		$q = (int) floor($num / $b);
		while ($q) {
			$r = (int) $q % $b;
			$q = (int) floor($q / $b);
			$res = (string) $base[$r].$res;
		} //end while
		//--
		return (string) $res;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Intended usage: Small scale.
	 * Generates a random, almost unique numeric UUID of 10 characters [0..9] ; Example: 5457229400 .
	 * For the same time moment, duplicate values can happen with a chance of 1 in a 9 million.
	 * Min is: 0000000001 ; Max id: 9999999999 .
	 * Values: 9999999998 .
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_10_num() {
		//--
		$uid = '';
		for($i=0; $i<9; $i++) {
			$rand = self::random_number(0,9);
			$uid .= $rand;
		} //end for
		$rand = self::random_number(1,9);
		$uid .= $rand;
		//--
		return (string) $uid;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Intended usage: Medium scale.
	 * Generates a random, almost unique string (base36) UUID of 10 characters [0..9A..Z] ; Example: Z4C9S6F1H1 .
	 * For the same time moment, duplicate values can occur with a chance of ~ 1 in a 3000 trillion.
	 * Min is: 0A0A0A0A0A (28232883707050) ; Max id: Z9Z9Z9Z9Z9 (3582752942424645) .
	 * Values YZYZYZYZYZ (3554520058717595) .
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_10_str() {
		//--
		$toggle = self::random_number(0,1);
		//--
		$uid = '';
		for($i=0; $i<10; $i++) {
			if(($i % 2) == $toggle) {
				$rand = self::random_number(0,9);
			} else { // alternate nums with chars (this avoid to have ugly words)
				$rand = self::random_number(10,35);
			} //end if else
			$uid .= (string) base_convert($rand, 10, 36);
		} //end for
		//--
		return (string) strtoupper((string)$uid);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Intended usage: Large scale.
	 * Generates a random string (base36) UUID of 10 characters [0..9A..Z] ; Example: 0G1G74W362 .
	 * Intended usage: Medium scale / Sequential / Non-Repeating (never repeats in a period cycle of 1000 years).
	 * This is sequential, date and time based with miliseconds and a randomizer factor to ensure an ~ unique ID.
	 * Duplicate values can occur just in the same milisecond (1000 miliseconds = 1 second) with a chance of ~ 3%
	 * Values: 34 k / sec ; 200 k / min ; 120 mil / hour .
	 *
	 * Advantages: This is one of the most powerful UUID system for medium scale as the ID will never repeat in a large period of time.
	 * Compared with the classic autoincremental IDs this UUID is much better as on the next cycle can fill up unallocated
	 * values and more, because the next cycle occur after so many time there is no risk to re-use some IDs if they were
	 * previous deleted or deactivated in terms of generating confusions with previous records.
	 * The classic autoincremental systems can NOT do this and also, once the max ID is reached the DB table is blocked
	 * as autoincremental records reach the max ID !!!
	 *
	 * Disadvantages: The database connectors require more complexity and must be able to retry within a cycle with
	 * double check before alocating, such UUIDs and must use a pre-alocation table since the UUIDs are time based and if
	 * in the same milisecond more than 1 inserts is allocated they can conflict each other without such pre-alocation !
	 *
	 * Smart.Framework implements the retry + cycle + pre-alocating table as a standard feature
	 * in the bundled PostgreSQL library (connector/plugin) as PostgreSQL can do DDLs in transactions.
	 * Using such functionality with MySQL would be tricky as DDLs will break the transactions, but still usable ;-).
	 * And for SQLite it does not make sense since SQLite is designed for small DBs thus no need for such high scalability ...
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_10_seq() { // v7
		//-- 00 .. RR
		$b10_thousands_year = (int) substr(date('Y'), -3, 3); // get last 3 digits from year 000 .. 999
		$b36_thousands_year = (string) sprintf('%02s', base_convert($b10_thousands_year, 10, 36));
		//-- 00000 .. ITRPU
		$b10_day_of_year = (int) (date('z') + 1); // 1 .. 366
		$b10_second_of_day = (int) ((((int)date('H')) * 60 * 60) + ((int)date('i') * 60) + ((int)date('s'))); // 0 .. 86399
		$b10_second_of_year = (int) ($b10_day_of_year * $b10_second_of_day); // 0 .. 31622399
		$b36_second_of_year = (string) sprintf('%05s', base_convert($b10_second_of_year, 10, 36));
		//-- 00 .. RR
		$microtime = (array) explode('.', (string)microtime(true));
		$b10_microseconds = (int) substr((string)trim((string)$microtime[1]), 0, 3); // 0 .. 999
		$b36_microseconds = (string) sprintf('%02s', base_convert($b10_microseconds, 10, 36));
		//-- 1 .. Z
		$rand = self::random_number(1, 35); // trick: avoid 0000000000
		$b36_randomizer = (string) sprintf('%01s', base_convert($rand, 10, 36));
		//--
		$uid = (string) trim((string)$b36_thousands_year.$b36_second_of_year.$b36_microseconds.$b36_randomizer);
		//--
		return (string) strtoupper((string)$uid);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Intended usage: Large scale, in a cluster.
	 * Generates a random string (base62) UUID of 15 characters [0..9a..zA..Z] ; Example: 0K4M6V04JM01 .
	 * It is based on Smart::uuid_10_seq() but will append the last two characters in base36 00..ZZ using Smart::net_server_id(true) that represent the Net Server ID in a cluster.
	 * To set the Net Server ID as unique per each running instance of Smart.Framework under the same domain,
	 * set the constant SMART_FRAMEWORK_NETSERVER_ID in etc/init.php with a number between 0..1295 to have a unique number for each instance of Smart.Framework
	 * where supposed all this instances will run in a cluster.
	 * If there is only one instance running and no plans at all to implement a multi-server cluster, makes non-sense to use this function, use instead the Smart::uuid_10_seq()
	 * For how is implemented, read the documentation for the functions: Smart::uuid_10_seq() and Smart::net_server_id(true)
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_12_seq() { // v7
		//--
		return (string) self::uuid_10_seq().self::net_server_id(true);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Intended usage: Very Large scale. Case sensitive.
	 * Generates a random string (base62) UUID of 13 characters [0..9a..zA..Z] ; Example: 00wA0whhw2e9L .
	 * Intended usage: Very Large scale / Sequential / Non-Repeating (never repeats in a period cycle of 9999999 years).
	 * This is sequential, date and time based with miliseconds and a randomizer factor to ensure an ~ unique ID.
	 * Duplicate values can occur just in the same milisecond (1000 miliseconds = 1 second) with a chance of ~ 0.3%
	 * Values: 340000 k / sec ; 2000000 k / min ; 1200000 mil / hour .
	 *
	 * Advantages: This is one of the most powerful UUID system for large scale as the ID will never repeat in a huge period of time.
	 * Compared with the classic autoincremental IDs this UUID is much better as on the next cycle can fill up unallocated
	 * values and more, because the next cycle occur after so many time there is no risk to re-use some IDs if they were
	 * previous deleted or deactivated in terms of generating confusions with previous records.
	 * The classic autoincremental systems can NOT do this and also, once the max ID is reached the DB table is blocked
	 * as autoincremental records reach the max ID !!!
	 *
	 * Disadvantages: The database connectors require more complexity and must be able to retry within a cycle with
	 * double check before alocating, such UUIDs and must use a pre-alocation table since the UUIDs are time based and if
	 * in the same milisecond more than 1 inserts is allocated they can conflict each other without such pre-alocation !
	 *
	 * Smart.Framework implements the retry + cycle + pre-alocating table as a standard feature
	 * in the bundled PostgreSQL library (connector/plugin) as PostgreSQL can do DDLs in transactions.
	 * Using such functionality with MySQL would be tricky as DDLs will break the transactions, but still usable ;-).
	 * And for SQLite it does not make sense since SQLite is designed for small DBs thus no need for such high scalability ...
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_13_seq() { // v1
		//-- YEAR: 0 .. 9999999 in base62 is 0000 .. FXsj
		$b10_10milion_year = (int) substr(date('Y'), -7, 7); // get last 7 digits of year
		$b62_10milion_year = (string) sprintf('%04s', self::int10_to_base62_str($b10_10milion_year));
		//-- SECOND OF YEAR: 0 .. 31622399 in base62 is 00000 .. 28GqH
		$b10_day_of_year = (int) (date('z') + 1); // 1 .. 366
		$b10_second_of_day = (int) ((((int)date('H')) * 60 * 60) + ((int)date('i') * 60) + ((int)date('s'))); // 0 .. 86399
		$b10_second_of_year = (int) ($b10_day_of_year * $b10_second_of_day); // 0 .. 31622399
		$b62_second_of_year = (string) sprintf('%05s', self::int10_to_base62_str($b10_second_of_year));
		//-- MICROSECOND: 0 .. 9999999 in base62 is 0000 .. FXsj
		$microtime = (array) explode('.', (string)microtime(true));

		$b10_microseconds = (string) sprintf('%04s', (int)substr((string)trim((string)$microtime[1]), 0, 4)); // 0000 .. 9999
		$rand = self::random_number(1, 999); // trick: avoid 0000000000000
		$b10_randomizer = (string) sprintf('%03s', $rand);
		$b10_microseconds .= $b10_randomizer; // append 0000 .. 9999 with 3 more digits 000 .. 999
		$b62_microseconds = (string) sprintf('%04s', self::int10_to_base62_str((int)$b10_microseconds));
		//--
		return (string) $b62_10milion_year.$b62_second_of_year.$b62_microseconds;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Intended usage: Very Large scale, in a cluster. Case sensitive.
	 * Generates a random string (base62) UUID of 15 characters [0..9a..zA..Z] ; Example: 00wA0whhw2e9L01 .
	 * It is based on Smart::uuid_13_seq() but will append the last two characters in base36 00..ZZ using Smart::net_server_id(true) that represent the Net Server ID in a cluster.
	 * To set the Net Server ID as unique per each running instance of Smart.Framework under the same domain,
	 * set the constant SMART_FRAMEWORK_NETSERVER_ID in etc/init.php with a number between 0..1295 to have a unique number for each instance of Smart.Framework
	 * where supposed all this instances will run in a cluster.
	 * If there is only one instance running and no plans at all to implement a multi-server cluster, makes non-sense to use this function, use instead the Smart::uuid_13_seq()
	 * For how is implemented, read the documentation for the functions: Smart::uuid_13_seq() and Smart::net_server_id(true)
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_15_seq() { // v1
		//--
		return (string) self::uuid_13_seq().self::net_server_id(true);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Generates an almost unique BASE36 based UUID of 32 characters [0..9A..Z] ; Example: Y123AY7WK5-9187139702-Z98W7T091K .
	 * This compose as: Smart::uuid_10_seq().'-'.Smart::uuid_10_num().'-'.Smart::uuid_10_str()
	 * Intended usage: Very Large scale.
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_32() {
		//--
		return (string) self::uuid_10_seq().'-'.self::uuid_10_num().'-'.self::uuid_10_str();
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Generates an almost unique BASE36 based UUID of 34 characters [0..9A..Z] ; Example: Y123AY7WK501-9187139702-Z98W7T091K .
	 * This compose as: Smart::uuid_12_seq().'-'.Smart::uuid_10_num().'-'.Smart::uuid_10_str()
	 * Intended usage: Very Large scale, in a cluster.
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_34() {
		//--
		return (string) self::uuid_12_seq().'-'.self::uuid_10_num().'-'.self::uuid_10_str();
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Generates an almost unique BASE62 + BASE36 based UUID of 35 characters [0..9a..zA..Z] ; Example: 00wA0whhw2e9L-9187139702-Z98W7T091K .
	 * This compose as: Smart::uuid_13_seq().'-'.Smart::uuid_10_num().'-'.Smart::uuid_10_str()
	 * Intended usage: Extremely Large scale. Case sensitive.
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_35() {
		//--
		return (string) self::uuid_13_seq().'-'.self::uuid_10_num().'-'.self::uuid_10_str();
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Generates an almost unique BASE62 + BASE36 based UUID of 35 characters [0..9a..zA..Z] ; Example: 00wA0whhw2e9L01-9187139702-Z98W7T091K .
	 * This compose as: Smart::uuid_15_seq().'-'.Smart::uuid_10_num().'-'.Smart::uuid_10_str()
	 * Intended usage: Extremely Large scale, in a cluster. Case sensitive.
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_37() {
		//--
		return (string) self::uuid_15_seq().'-'.self::uuid_10_num().'-'.self::uuid_10_str();
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Generates an almost unique MD5 based UUID of 36 characters [0..9a..f] ; Example: cfcb6c2a-a6e0-f539-141d-083abee19a4e .
	 * The uniqueness of this is based on a full unique signature in space and time: 2 random UUIDS, server name, year/day/month hour:minute:seconds, time, microseconds, a random value 0...9999 .
	 * For the same time moment, duplicates values can occur with a chance of 1 in ~ a 340282366920938586008062602462446642046 .
	 * The Net Server ID can be passed via the $prefix parameter
	 * Intended usage: Large scale. Standard.
	 *
	 * @param STRING $prefix 				:: A prefix to use for more uniqueness entropy ; Ex: can use the Smart::net_server_id()
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_36($prefix='') {
		//--
		$hash = (string) md5($prefix.self::unique_entropy('uid36', false)); // by default use no reference to net server id, which can be passed via prefix
		//--
		$uuid  = substr($hash,0,8).'-';
		$uuid .= substr($hash,8,4).'-';
		$uuid .= substr($hash,12,4).'-';
		$uuid .= substr($hash,16,4).'-';
		$uuid .= substr($hash,20,12);
		//--
		return (string) strtolower((string)$uuid);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Generates an almost unique SHA1 based UUID of 45 characters [0..9a..f] ; Example: c02acc84-97f4-0807-b12c-ed6f28dd2078-400c1baf .
	 * The uniqueness of this is based on a full unique signature in space and time: 2 random UUIDS, server name, year/day/month hour:minute:seconds, time, microseconds, a random value 0...9999 .
	 * For the same time moment, duplicates values can occur with a chance of 1 in ~ a 1461501637330903466848266086008062602462446642046 .
	 * The Net Server ID can be passed via the $prefix parameter
	 * Intended usage: Large scale. Standard.
	 *
	 * @param STRING $prefix 				:: A prefix to use for more uniqueness entropy ; Ex: can use the Smart::net_server_id()
	 *
	 * @return STRING 						:: the UUID
	 */
	public static function uuid_45($prefix='') {
		//--
		$hash = (string) sha1($prefix.self::unique_entropy('uid45', false)); // by default use no reference to net server id, which can be passed via prefix
		//--
		$uuid  = substr($hash,0,8).'-';
		$uuid .= substr($hash,8,4).'-';
		$uuid .= substr($hash,12,4).'-';
		$uuid .= substr($hash,16,4).'-';
		$uuid .= substr($hash,20,12);
		$uuid .= '-'.substr($hash,32,8);
		//--
		return (string) strtolower((string)$uuid);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Safe Parse URL 					:: a better replacement for parse_url()
	 *
	 * @param STRING 	$y_url			:: The URL to be separed
	 *
	 * @return ARRAY 					:: The separed URL (associative array) as: protocol, server, port, path, scriptname
	 */
	public static function url_parse($y_url) {
		//--
		$y_url = (string) $y_url;
		//--
		$parts = array();
		$parts = parse_url((string)$y_url);
		if(!is_array($parts)) {
			$parts = array();
		} //end if
		//print_r($parts); die();
		//--
		$scheme = (string) trim((string)$parts['scheme']);
		//--
		$protocol = (string) $scheme;
		if((string)$protocol != '') {
			$protocol .= ':';
		} //end if
		$protocol .= '//';
		//--
		$server = (string) trim((string)$parts['host']);
		//--
		$port = (string) trim((string)$parts['port']);
		if((string)$port == '') {
			if((string)$scheme == 'https') {
				$port = '443';
			} else {
				$port = '80';
			} //end if else
		} //end if
		//--
		$path = (string) trim((string)$parts['path']);
		$query = (string) trim((string)$parts['query']);
		$fragment = (string) trim((string)$parts['fragment']);
		//--
		$suffix = (string) $path;
		if((string)$query != '') {
			$suffix .= '?'.$query;
		} //end if
		if((string)$fragment != '') {
			$suffix .= '#'.$fragment;
		} //end if
		if((string)$suffix == '') {
			$suffix = '/'; // FIX: this is required as default http path for HTTP Cli requests !!
		} //end if
		//--
		return array('protocol' => $protocol, 'scheme' => $scheme, 'host' => $server, 'port' => $port, 'path' => $path, 'query' => $query, 'fragment' => $fragment, 'suffix' => $suffix); // script must be compatible with: parse_url() but may have extra entries
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Convert an Array to a List String
	 *
	 * @param ARRAY 	$y_arr			:: The Array to be converted: Array(elem1, elem2, ..., elemN)
	 *
	 * @return STRING 					:: The List String: '<elem1>, <elem2>, ..., <elemN>'
	 */
	public static function array_to_list($y_arr) {
		//--
		$out = '';
		//--
		if(self::array_size($y_arr) > 0) { // this also check if it is array
			//--
			$arr = array();
			//--
			foreach($y_arr as $key => $val) {
				//--
				if(!is_array($val)) {
					//--
					$val = trim((string)$val); // must not do strtolower as it is used to store both cases
					$val = str_replace(array('<', '>', ','), array('‹', '›', ';'), $val);
					if((string)$val != '') {
						if(!in_array('<'.$val.'>', $arr)) {
							$arr[] = '<'.$val.'>';
						} //end if
					} //end if
					//--
				} //end if
				//--
			} //end foreach
			//--
			$out = implode(', ', $arr);
			//--
			$arr = array();
			//--
		} //end if else
		//--
		return (string) trim((string)$out);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Convert a List String to Array
	 *
	 * @param STRING 	$y_list			:: The List String to be converted: '<elem1>, <elem2>, ..., <elemN>'
	 * @param BOOLEAN 	$y_trim 		:: *Optional* default is TRUE ; If set to FALSE will not trim the values in the list
	 *
	 * @return ARRAY 					:: The Array: Array(elem1, elem2, ..., elemN)
	 */
	public static function list_to_array($y_list, $y_trim=true) {
		//--
		if((string)trim((string)$y_list) == '') {
			return array(); // empty list
		} //end if
		//--
		$y_list = (string) trim((string)$y_list);
		//--
		$arr = (array) explode(',', (string)$y_list);
		$new_arr = array();
		for($i=0; $i<self::array_size($arr); $i++) {
			$arr[$i] = (string) str_replace(['<', '>'], ['', ''], (string)$arr[$i]);
			if($y_trim !== false) {
				$arr[$i] = (string) trim((string)$arr[$i]);
			} //end if
			if((string)$arr[$i] != '') {
				if(!in_array((string)$arr[$i], $new_arr)) {
					$new_arr[] = (string) $arr[$i];
				} //end if
			} //end if
		} //end for
		//--
		return (array) $new_arr;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * The Info logger.
	 * This will add messages to the App Info Log. (depending if on admin or index, will output into 'tmp/logs/adm/' or 'tmp/logs/idx/')
	 *
	 * @param STRING 	$title			:: The title of the message to be logged
	 * @param STRING 	$message		:: The message to be logged
	 *
	 * @return -						:: This function does not return anything
	 */
	public static function log_info($title, $message) {
		//--
		if((defined('SMART_FRAMEWORK_INFO_LOG')) AND (is_dir(self::dir_name((string)SMART_FRAMEWORK_INFO_LOG)))) { // must use is_dir here to avoid dependency with smart file system lib
			@file_put_contents((string)SMART_FRAMEWORK_INFO_LOG, '[INF]'."\t".date('Y-m-d H:i:s O')."\t".self::normalize_spaces($title)."\t".self::normalize_spaces($message)."\n", FILE_APPEND | LOCK_EX);
		} else {
			self::log_notice('INFO-LOG NOT SET :: Logging to Notices ... # Message: '.$title."\n".$message);
		} //end if else
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * A quick replacement for trigger_error() / E_USER_NOTICE.
	 * This is intended to log APP Level Notices.
	 * This will log the message as NOTICE into the App Error Log.
	 * Notices are logged ONLY if SMART_ERROR_HANDLER is set to 'dev' mode.
	 *
	 * @param STRING 	$message		:: The message to be triggered
	 *
	 * @return -						:: This function does not return anything
	 */
	public static function log_notice($message) {
		//--
		@trigger_error('#SMART-FRAMEWORK.NOTICE# '.$message, E_USER_NOTICE);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * A quick replacement for trigger_error() / E_USER_WARNING.
	 * This is intended to log APP Level Warnings.
	 * This will log the message as WARNING into the App Error Log.
	 *
	 * @param STRING 	$message		:: The message to be triggered
	 *
	 * @return -						:: This function does not return anything
	 */
	public static function log_warning($message) {
		//--
		@trigger_error('#SMART-FRAMEWORK.WARNING# '.$message, E_USER_WARNING);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * A quick replacement for trigger_error() / E_USER_ERROR.
	 * This is intended to log APP Level Errors.
	 * This will log the message as ERROR into the App Error Log and stop the execution (also in the Smart Error Handler will raise a HTTP 500 Code).
	 *
	 * @param STRING 	$message_to_log			:: The message to be triggered
	 * @param STRING 	$message_to_display 	:: *Optional* the message to be displayed (must be html special chars safe)
	 *
	 * @return -								:: This function does not return anything
	 */
	public static function raise_error($message_to_log, $message_to_display='') {
		//--
		global $smart_____framework_____last__error; // presume it is already html special chars safe
		//--
		if((string)trim((string)$message_to_display) == '') {
			$message_to_display = 'See Error Log for More Details'; // avoid empty message to display
		} //end if
		$smart_____framework_____last__error = (string) $message_to_display;
		@trigger_error('#SMART-FRAMEWORK.ERROR# '.$message_to_log, E_USER_ERROR);
		die('App Level Raise ERROR. Execution Halted. '.$message_to_display); // normally this line will never be executed because the E_USER_ERROR via Smart Error Handler will die() before ... but this is just in case, as this is a fatal error and the execution should be halted here !
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Returns the Regex Expr. with the lower unsafe characters
	 *
	 * @access 		private
	 * @internal
	 *
	 * @return STRING 						:: A regex expression
	 *
	 */
	public static function lower_unsafe_characters() {
		//--
		return '/[\x00-\x08\x0B-\x0C\x0E-\x1F]/'; // all lower dangerous characters: x00 - x1F except: \t = x09 \n = 0A \r = 0D
		//--
	} //END FUNCTION
	//================================================================


	//##### DEBUG ONLY


	//================================================================
	/**
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function registerInternalCacheToDebugLog() {
		//--
		if(SmartFrameworkRuntime::ifInternalDebug()) {
			if(SmartFrameworkRuntime::ifDebug()) {
				SmartFrameworkRegistry::setDebugMsg('extra', '***SMART-CLASSES:INTERNAL-CACHE***', [
					'title' => 'Smart (Base) // Internal Cache',
					'data' => 'Dump of Cfgs:'."\n".print_r(self::$Cfgs,1)
				]);
			} //end if
		} //end if
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
