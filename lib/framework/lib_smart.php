<?php
// [LIB - SmartFramework / Base]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.5')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Base
// DEPENDS:
//  * SmartUnicode::
// DEPENDS-PHP: 5.4.7 or later
// DEPENDS-EXT: XML, Json
//======================================================

// [REGEX-SAFE-OK]

//================================================================
if((!function_exists('json_encode')) OR (!function_exists('json_decode'))) {
	die('ERROR: The PHP JSON Extension is required for the SmartFramework / Base');
} //end if
if(!function_exists('hex2bin')) {
	die('ERROR: The PHP hex2bin Function is required for SmartFramework / Base');
} //end if
if(!function_exists('bin2hex')) {
	die('ERROR: The PHP bin2hex Function is required for SmartFramework / Base');
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
 * Class: Smart (Base Functions) - provides the base methods for an easy and secure development with SmartFramework and PHP.
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
 * @depends     extensions: PHP XML, PHP JSON ; classes: SmartUnicode
 * @version     v.170908
 * @package     Base
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
			$y_path = str_replace((string)DIRECTORY_SEPARATOR, '/', (string)$y_path); // convert \ to / on paths
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
	$y_path = trim((string)$y_path);
	//--
	$the_path = (string) @realpath($y_path);
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
	$y_path = trim((string)$y_path);
	//--
	$dir_name = (string) dirname($y_path);
	//--
	return (string) self::fix_path_separator($dir_name); // FIX: on Windows, is possible to return a backslash \ instead of slash /
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the FIXED basename(), in a safe way
 *
 * @param 	STRING 	$y_path 			:: The path name from to extract basename()
 *
 * @return 	STRING						:: The basename
 */
public static function base_name($y_path) {
	//--
	$y_path = trim((string)$y_path);
	//--
	$base_name = (string) basename($y_path);
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
 * Create a semantic URL like: (script.php)?/param1/value1/param2/value2
 *
 * @param 	STRING 	$y_url 				:: The standard URL in RFC3986 format as: (script.php)?param1=value1&param2=value2
 *
 * @return 	STRING						:: The semantic URL
 */
public static function url_make_semantic($y_url) {
	//--
	$y_url = (string) trim((string)$y_url);
	if((string)$y_url == '') {
		return ''; // if URL is empty nothing to do ...
	} //end if
	//--
	if(defined('SMART_FRAMEWORK_SEMANTIC_URL_DISABLE')) {
		return (string) $y_url;
	} //end if
	//--
	$ignore_script = '';
	$ignore_module = '';
	if(SMART_FRAMEWORK_ADMIN_AREA !== true) { // not for admin !
		if(defined('SMART_FRAMEWORK_SEMANTIC_URL_SKIP_SCRIPT')) {
			$ignore_script = (string) trim((string)SMART_FRAMEWORK_SEMANTIC_URL_SKIP_SCRIPT);
		} //end if
		if(defined('SMART_FRAMEWORK_SEMANTIC_URL_SKIP_MODULE')) {
			$ignore_module = (string) trim((string)SMART_FRAMEWORK_SEMANTIC_URL_SKIP_MODULE);
		} //end if
	} //end if
	//--
	$semantic_separator = '?/';
	//--
	if(strpos((string)$y_url, $semantic_separator) !== false) {
		return (string) $y_url; // it is already semantic or at least appear to be ...
	} // end if
	//--
	$arr = parse_url((string)$y_url);
	//print_r($arr);
	//--
	$arr['scheme'] = trim((string)$arr['scheme']); 	// http
	$arr['host'] = trim((string)$arr['host']); 		// 127.0.0.1
	$arr['port'] = trim((string)$arr['port']); 		// 80 / 443 / 8088 ...
	$arr['path'] = trim((string)$arr['path']); 		// /some/path
	$arr['query'] = trim((string)$arr['query']);	// page=some&op=other
	//--
	if((string)$arr['query'] == '') {
		return (string) $y_url; // there is no query string to format as semantic
	} //end if
	//--
	$semantic_url = '';
	//--
	if((string)$arr['host'] != '') {
		$semantic_url .= $arr['scheme'].'://'.$arr['host'];
		if(((string)$arr['port'] != '') AND ((string)$arr['port'] != '80') AND ((string)$arr['port'] != '443')) {
			$semantic_url .= ':'.$arr['port'];
		} //end if
	} //end if
	//--
	if((string)$ignore_script != '') {
		$len = strlen($ignore_script);
		if($len > 0) {
			if((string)$arr['path'] == (string)$ignore_script) {
				$arr['path'] = '';
			} elseif(substr($arr['path'], (-1*$len), $len) == (string)$ignore_script) {
				$len = strlen($arr['path']) - $len;
				if($len > 0) {
					$arr['path'] = substr($arr['path'], 0, $len);
				} //end if
			} //end if
		} //end if
	} //end if
	$semantic_url .= $arr['path'];
	//--
	$use_rewrite = false;
	$use_rfc_params = false;
	if(SMART_FRAMEWORK_ADMIN_AREA !== true) { // not for admin !
		if(defined('SMART_FRAMEWORK_SEMANTIC_URL_USE_REWRITE')) {
			if((string)SMART_FRAMEWORK_SEMANTIC_URL_USE_REWRITE == 'semantic') {
				$use_rewrite = true;
			} elseif((string)SMART_FRAMEWORK_SEMANTIC_URL_USE_REWRITE == 'standard') {
				$use_rewrite = true;
				$use_rfc_params = true;
			} //end switch
		} //end if
	} //end if
	//--
	$vars = explode('&', $arr['query']);
	$asvars = array(); // store params except page
	$detected_page = ''; // store page if found
	$parsing_ok = true;
	for($i=0; $i<self::array_size($vars); $i++) {
		//--
		$pair = explode('=', $vars[$i]);
		//--
		if(((string)$pair[0] == '') OR (!preg_match('/^[a-z0-9_]+$/', (string)$pair[0])) OR ((string)$pair[1] == '')) {
			$parsing_ok = false;
			break;
		} //end if
		//--
		if((string)$pair[0] == 'page') {
			$detected_page = (string) $pair[1];
		} else {
			$asvars[(string)$pair[0]] = (string) $pair[1];
		} //end if else
		//--
	} //end for
	$vars = array();
	//--
	if($parsing_ok !== true) {
		return (string) $y_url; // there is something wrong with the URL
	} //end if
	//--
	if(self::array_size($asvars) > 0) {
		$have_params = true;
	} else {
		$have_params = false;
	} //end if else
	//--
	$semantic_suffix = '';
	$have_semantic_separator = false;
	$page_rewrite_ok = false;
	//--
	if((string)$detected_page != '') {
		//--
		if(strpos((string)$detected_page, '.') !== false) {
			$arr_pg = explode('.', (string)$detected_page);
			$the_pg_mod = trim((string)$arr_pg[0]); 	// no controller, use the default one
			$the_pg_ctrl = trim((string)$arr_pg[1]); 	// page controller
			$the_pg_ext = trim((string)$arr_pg[2]); 	// page extension **OPTIONAL**
			$arr_pg = array();
		} else {
			$the_pg_mod = ''; 						// no controller, use the default one
			$the_pg_ctrl = (string) $detected_page; // page controller
			$the_pg_ext = ''; 						// page extension
		} //end if else
		//--
		$pg_link = '';
		if(((string)$the_pg_mod == '') OR ((string)$the_pg_mod == (string)$ignore_module)) {
			$pg_link .= (string) $the_pg_ctrl;
		} else {
			$pg_link .= (string) $the_pg_mod.'.'.$the_pg_ctrl;
		} //end if
		//--
		if(($use_rewrite === true) AND (((string)$semantic_url == '') OR (substr($semantic_url, -1, 1) == '/'))) { // PAGE (with REWRITE)
			//--
			if((string)$the_pg_ext == '') {
				$the_pg_ext = 'html';
			} //end if
			//--
			$page_rewrite_ok = true;
			$semantic_suffix .= $pg_link.'.'.$the_pg_ext;
			//--
		} else {
			//--
			$semantic_suffix .= $semantic_separator.'page'.'/'.$pg_link.'/';
			$have_semantic_separator = true;
			//--
		} //end if else
		//--
	} //end if
	//--
	if($have_params === true) {
		//--
		foreach($asvars as $key => $val) {
			//--
			if(($page_rewrite_ok === true) AND ($use_rfc_params === true)) {
				//--
				$semantic_suffix = self::url_add_suffix($semantic_suffix, $key.'='.$val);
				//--
			} else {
				//--
				$val = str_replace('/', self::escape_url('/'), $val);
				$val = str_replace(self::escape_url('/'), self::escape_url(self::escape_url('/')), $val); // needs double encode the / character for semantic URLs to avoid conflict with param/value
				//--
				if($have_semantic_separator !== true) {
					$semantic_suffix .= $semantic_separator;
					$have_semantic_separator = true;
				} //end if
				$semantic_suffix .= $key.'/'.$val.'/';
				//--
			} //end if else
			//--
		} //end foreach
		//--
	} //end if
	//--
	if((string)$semantic_suffix == '') {
		return (string) $y_url; // something get wrong with the conversion, maybe the URL query is formatted in a different way that could not be understood
	} //end if
	//--
	$semantic_url .= (string) $semantic_suffix;
	//--
	return (string) $semantic_url;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Add URL Params (Build a standard RFC3986 URL from script and parameters) as: script.xyz?a=b&param1=value1&param2=value2
 *
 * @param 	STRING 		$y_url				:: The base URL like: script.php or script.php?a=b or empty
 * @param 	ARRAY		$y_params 			:: Associative array as [param1 => value1, param2 => value2]
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
				if(preg_match('/^[a-z0-9_]+$/', (string)$key)) {
					if((string)$val != '') {
						$suffix = (string) $key.'=';
						if((substr((string)$val, 0, 3) == '{{{') AND (substr((string)$val, -3, 3) == '}}}')) { // this is {{{param}}}
							$tmp_val = substr((string)$val, 3);
							$tmp_val = substr((string)$tmp_val, 0, strlen($tmp_val)-3);
							$suffix .= '{{{'.self::escape_url((string)$tmp_val).'}}}';
							$tmp_val = '';
						} else {
							$suffix .= self::escape_url((string)$val);
						} //end if else
						$url = self::url_add_suffix($url, $suffix);
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
 * Add URL Suffix (to a standard RFC3986 URL) as: script.php?a=b&c=d&e=%20d
 *
 * @param 	STRING 		$y_url				:: The base URL to use as prefix like: script.php or script.php?a=b&c=d or empty
 * @param 	STRING		$y_suffix 			:: A RFC3986 URL segment like: a=b or e=%20d (without ? or not starting with & as they will be detected if need append ? or &; variable values must be encoded using rawurlencode() RFC3986)
 *
 * @return 	STRING							:: The prepared URL in the standard RFC3986 format (all values are escaped using rawurlencode() to be Unicode full compliant
 */
public static function url_add_suffix($y_url, $y_suffix) {
	//--
	$y_url = trim((string)$y_url);
	$y_suffix = trim((string)$y_suffix);
	//--
	if((strpos($y_suffix, '?') !== false) OR (substr($y_suffix, 0, 1) == '&')) {
		self::log_notice('[URL Add Suffix] WARNING: The URL Suffix should not contain ? or start with & :: [URL: '.$y_url.' :: Suffix: '.$y_suffix.']');
	} //end if
	//--
	if(strpos($y_url, '?') === false) {
		$url = $y_url.'?'.$y_suffix;
	} else {
		$url = $y_url.'&'.$y_suffix;
	} //end if else
	//--
	return (string) $url;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Add URL Anchor (to a standard RFC3986 URL) as: script.php?a=b&c=d&e=%20d
 *
 * @param 	STRING 		$y_url				:: The base URL to use as prefix like: script.php or script.php?a=b&c=d or empty
 * @param 	STRING		$y_anchor 			:: A RFC3986 URL anchor like: myAnchor
 *
 * @return 	STRING							:: The prepared URL as script.php?a=b&c=d&e=%20d#myAnchor
 */
public static function url_add_anchor($y_url, $y_anchor) {
	//--
	$y_url = (string) $y_url;
	$y_anchor = (string) $y_anchor;
	//--
	return (string) $y_url.'#'.self::escape_url($y_anchor);
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
	//-- v.150112
	// Default is: ENT_HTML401 | ENT_COMPAT
	// keep the ENT_HTML401 instead of ENT_HTML5 to avoid troubles with misc. HTML Parsers (robots, htmldoc, ...)
	// keep the ENT_COMPAT (replace only < > ") and not replace '
	// add ENT_SUBSTITUTE to avoid discard the entire invalid string (with UTF-8 charset) but substitute dissalowed characters with ?
	//--
	return (string) htmlspecialchars((string)$y_string,  ENT_HTML401 | ENT_COMPAT | ENT_SUBSTITUTE, SMART_FRAMEWORK_CHARSET); // use charset from INIT (to prevent XSS attacks)
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
		Smart::log_warning('Invalid Encoded Json in '.__METHOD__.'() for input: '.print_r($data,1));
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
		if($y_number < 0) { // {{{SYNC-SMART-INT+}}}
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
 * Test if the Array Type
 *
 * @param ARRAY 		$y_arr			:: The array to test
 *
 * @return ENUM 						:: The array type as: 0 = not an array ; 1 = non-associative (sequential) array ; 2 = associative array
 */
public static function array_type_test($y_arr) {
	//--
	if(!is_array($y_arr)) {
		return 0; // not an array
	} //end if
	//--
	$a = (array) array_keys($y_arr);
	if($a === array_keys($a)) { // memory-optimized
	//if(array_values($y_arr) === $y_arr) { // speed-optimized
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
	$yhtmlcode = (string) str_replace(array(' />', '/>', '>'), array('>', '>', '> '), (string)$yhtmlcode);
	//-- remove special tags
	$html_regex_h = array(
		'#<head[^>]*?>.*?</head[^>]*?>#si',				// head
		'#<style[^>]*?>.*?</style[^>]*?>#si',			// style
		'#<script[^>]*?>.*?</script[^>]*?>#si',			// script
		'#<noscript[^>]*?>.*?</noscript[^>]*?>#si',		// noscript
		'#<frameset[^>]*?>.*?</frameset[^>]*?>#si',		// frameset
		'#<frame[^>]*?>.*?</frame[^>]*?>#si',			// frame
		'#<iframe[^>]*?>.*?</iframe[^>]*?>#si',			// iframe
		'#<canvas[^>]*?>.*?</canvas[^>]*?>#si',			// canvas
		'#<audio[^>]*?>.*?</audio[^>]*?>#si',			// audio
		'#<video[^>]*?>.*?</video[^>]*?>#si',			// video
		'#<applet[^>]*?>.*?</applet[^>]*?>#si',			// applet
		'#<param[^>]*?>.*?</param[^>]*?>#si',			// param
		'#<object[^>]*?>.*?</object[^>]*?>#si',			// object
		'#<form[^>]*?>.*?</form[^>]*?>#si',				// form
		'#<link[^>]*?>#si',								// link
		'#<img[^>]*?>#si'								// img
	);
	$html_regex_r = array(
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' '
	);
	$yhtmlcode = (string) preg_replace((array)$html_regex_h, (array)$html_regex_r, (string)$yhtmlcode);
	$yhtmlcode = str_replace(["\r\n", "\r", "\t", "\f"], ["\n", "\n", ' ', ' '], $yhtmlcode);
	//-- replace new line tags
	if((string)$ynewline == 'yes') {
		$yhtmlcode = (string) str_ireplace(['<br>', '</br>'], ["\n", ''], (string)$yhtmlcode);
	} else {
		$yhtmlcode = (string) str_ireplace(['<br>', '</br>'], [' ', ''], (string)$yhtmlcode);
	} //end if else
	//-- strip the tags
	$yhtmlcode = (string) strip_tags((string)$yhtmlcode);
	//-- restore some usual html entities
	$regex_h = array(
		'&nbsp;',
		'&amp;',
		'&quot;',
		'&lt;',
		'&gt;',
		'&copy;',
		'&euro;',
		'&middot;'
	);
	$regex_r = array(
		' ',
		'&',
		'"',
		'<',
		'>',
		'(c)',
		'EURO',
		'.'
	);
	$yhtmlcode = (string) str_ireplace((array)$regex_h, (array)$regex_r, (string)$yhtmlcode);
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
	$yhtmlcode = (string) preg_replace('/[ \\t]+/', ' ', (string)$yhtmlcode); // replace any horizontal whitespace character ' since PHP 5.2.4 can be /[\h]+/
	$yhtmlcode = (string) preg_replace('/^\s*[\n]{2,}/m', '', (string)$yhtmlcode); // fix: replace multiple consecutive lines that may also contain before optional leading spaces
	$yhtmlcode = (string) preg_replace('/[^\S\r\n]+$/m', '', (string)$yhtmlcode); // remove trailing spaces on each line
	//--
	return (string) trim((string)$yhtmlcode);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Safe Path Name to be used to process dynamic build paths to avoid weird path character injections
 * To be used for full path to files or full path to dirs or just dir names
 * ALLOWED CHARS: [a-zA-Z0-9] _ - . @ # /
 *
 * @param STRING 		$y_path			:: Path to be processed
 * @param STRING 		$ysupresschar	:: The suppression character to replace weird characters ; optional ; default is ''
 *
 * @return STRING 						:: The safe path
 */
public static function safe_pathname($y_path, $ysupresschar='') {
	//-- v.160221
	if(preg_match('/^[_a-zA-Z0-9\-\.@#\/]+$/', (string)$y_path)) {
		return (string) $y_path;
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
	$y_path = (string) $y_path; // force string
	$y_path = (string) preg_replace((string)self::lower_unsafe_characters(), '', (string)$y_path); // remove dangerous characters
	$y_path = (string) SmartUnicode::utf8_to_iso($y_path); // bring STRING to ISO-8859-1
	$y_path = (string) stripslashes($y_path); // remove any possible back-slashes
	$y_path = (string) str_replace('?', $ysupresschar, $y_path); // replace questionmark (that may come from utf8 decode)
	$y_path = (string) preg_replace('/[^_a-zA-Z0-9\-\.@#\/]/', $ysupresschar, $y_path); // {{{SYNC-SAFE-PATH-CHARS}}} suppress any other characters than these, no unicode modifier
	$y_path = (string) preg_replace("/(\.)\\1+/", '.', $y_path); // suppress multiple . dots and replace with single dot
	$y_path = (string) preg_replace("/(\/)\\1+/", '/', $y_path); // suppress multiple // slashes and replace with single slash
	$y_path = (string) str_replace(array('../', './'), array('-', '-'), $y_path); // replace any unsafe path combinations (do not suppress but replace with a fixed character to avoid create security breaches)
	$y_path = (string) trim($y_path); // finally trim it
	//--
	return (string) $y_path;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Safe File Name to be used to process dynamic build file names or single dir names to avoid weird path character injections
 * To be used for file or single dir names
 * DO NOT USE for full paths or full dir paths because will break them, as the / character is supressed
 * ALLOWED CHARS: [a-zA-Z0-9] _ - . @ #
 *
 * @param STRING 		$y_fname		:: File Name or Single Dir Name to be processed
 * @param STRING 		$ysupresschar	:: The suppression character to replace weird characters ; optional ; default is ''
 *
 * @return STRING 						:: The safe file or single-dir name
 */
public static function safe_filename($y_fname, $ysupresschar='') {
	//-- v.160221
	if(preg_match('/^[_a-zA-Z0-9\-\.@#]+$/', (string)$y_fname)) {
		return (string) $y_fname;
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
	$y_fname = (string) $y_fname; // force string
	$y_fname = (string) self::safe_pathname($y_fname, $ysupresschar);
	$y_fname = (string) str_replace('/', '-', $y_fname); // replace the path character with a fixed character (do not suppress to avoid create security breaches)
	$y_fname = (string) trim($y_fname); // finally trim it
	//--
	return (string) $y_fname;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * RFC Safe compliant User Names ; Can be used also for email addresses
 * ALLOWED CHARS: [a-z0-9] _ - . @
 *
 * @param STRING 		$y_name			:: Variable to validate
 * @param STRING 		$ysupresschar	:: The suppression character to replace weird characters ; optional ; default is ''
 *
 * @return STRING 						:: The safe validated variable
 */
public static function safe_validname($y_name, $ysupresschar='') {
	//-- v.160221
	if(preg_match('/^[_a-z0-9\-\.@]+$/', (string)$y_name)) {
		return (string) $y_name;
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
	$y_name = (string) $y_name; // force string
	$y_name = (string) self::safe_filename($y_name, $ysupresschar);
	$y_name = (string) strtolower($y_name); // make all lower chars
	$y_name = (string) str_replace('#', '', $y_name); // replace also diez
	$y_name = (string) trim($y_name);
	//--
	return (string) $y_name;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Safe Valid User Names
 * ALLOWED CHARS: [a-z0-9] .
 *
 * @param STRING 		$y_name			:: Variable to validate
 *
 * @return STRING 						:: The safe validated variable
 */
public static function safe_username($y_name) {
	//-- v.160221
	if(preg_match('/^[a-z0-9\.]+$/', (string)$y_name)) {
		return (string) $y_name;
	} //end if
	//--
	$y_name = (string) $y_name; // force string
	$y_name = (string) self::safe_validname($y_name, '.');
	$y_name = (string) str_replace(array('@', '-', '_'), array('', '', ''), $y_name); // replace also @ - _
	$y_name = (string) trim($y_name);
	//--
	return (string) $y_name;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Safe Valid Variable Like Names
 * NOTICE: this have a special usage and must allow also 0..9 as prefix because is used for many purposes not just for real variables ...
 * ALLOWED CHARS: [a-z0-9] _
 *
 * @param STRING 		$y_name			:: Variable to validate
 *
 * @return STRING 						:: The safe validated variable
 */
public static function safe_varname($y_name) {
	//-- v.160221
	if(preg_match('/^[_a-z0-9]+$/', (string)$y_name)) {
		return (string) $y_name;
	} //end if
	//--
	$y_name = (string) $y_name; // force string
	$y_name = (string) self::safe_validname($y_name, '-');
	$y_name = (string) str_replace(array('.', '@', '-'), array('', '', ''), $y_name); // replace also . @ -
	$y_name = (string) trim($y_name);
	//--
	return (string) $y_name;
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
	if(abs(0+$y_number) > (int)PHP_INT_MAX) {
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
	if(abs(0+$y_number) > (0+$max_number)) {
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
 * The min is zero. The max is limited to 2147483647 on most of the platforms.
 *
 * @return INTEGER 						:: An integer random number
 */
public static function random_number($y_min=0, $y_max=-1) {
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
	return (int) mt_rand((int)$y_min, (int)$y_max);
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
public static function unique_entropy($y_suffix='') {
	//--
	return (string) 'Namespace:'.SMART_SOFTWARE_NAMESPACE.'NetServer#'.SMART_FRAMEWORK_NETSERVER_ID.';UUIDSequence='.self::uuid_10_seq().';UUIDRandStr='.self::uuid_10_str().';UUIDRandNum='.self::uuid_10_num().';'.$y_suffix;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Generates a random string (base36) UUID of 10 characters [0..9A..Z] ; Example: 0G1G74W362 .
 * Intended usage: Medium scale / Sequential / Non-Repeating (never repeats in a period cycle of 1000 years).
 * This is sequential, date and time based with miliseconds and a randomizer factor to ensure an ~ unique ID.
 * Duplicate values can occur just in the same milisecond (1000 miliseconds = 1 second) with a chance of ~ 3%
 * Values: 34k / sec ; 200k / min ; 120 mil / hour .
 *
 * Advantages: This is one of the most powerful UUID system as the ID will never repeat in a huge period of time.
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
 * Using such functionality with MySQL would be tricky as DDLs will break the transactions ;-).
 * And for SQLite it does not make sense since SQLite is designed for small DBs thus no need for such high scalability ...
 *
 * @return STRING 						:: the UUID
 */
public static function uuid_10_seq() { // v7
	//-- 00 .. RR
	$b10_thousands_year = (int) substr(date('Y'), -3, 3); // get last 3 digits from year 000 .. 999
	$b36_thousands_year = sprintf('%02s', base_convert($b10_thousands_year, 10, 36));
	//-- 00000 .. ITRPU
	$b10_day_of_year = (int) (date('z') + 1); // 1 .. 366
	$b10_second_of_day = (int) ((((int)date('H')) * 60 * 60) + ((int)date('i') * 60) + ((int)date('s'))); // 0 .. 86399
	$b10_second_of_year = (int) ($b10_day_of_year * $b10_second_of_day); // 0 .. 31622034
	$b36_second_of_year = sprintf('%05s', base_convert($b10_second_of_year, 10, 36));
	//-- 00 .. RR
	$microtime = explode('.', microtime(true));
	$b10_microseconds = (int) substr(trim($microtime[1]), 0, 3); // 000 .. 999
	$b36_microseconds = sprintf('%02s', base_convert($b10_microseconds, 10, 36));
	//-- 1 .. Z
	if(($b10_thousands_year == 0) AND ($b10_second_of_year == 0) AND ($b10_microseconds == 0)) {
		$rand = self::random_number(1,35); // avoid 0000000000
	} else {
		$rand = self::random_number(0,35);
	} //end if else
	$b36_randomizer = sprintf('%01s', base_convert($rand, 10, 36));
	//--
	$uid = trim($b36_thousands_year.$b36_second_of_year.$b36_microseconds.$b36_randomizer);
	//--
	return (string) strtoupper($uid);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Generates a random, almost unique numeric UUID of 10 characters [0..9] ; Example: 5457229400 .
 * Intended usage: Small scale.
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
 * Generates a random, almost unique string (base36) UUID of 10 characters [0..9A..Z] ; Example: Z4C9S6F1H1 .
 * Intended usage: Large scale.
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
		$uid .= base_convert($rand, 10, 36);
	} //end for
	//--
	return (string) strtoupper((string)$uid);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Generates an almost unique MD5 based UUID of 36 characters [0..9a..f] ; Example: cfcb6c2a-a6e0-f539-141d-083abee19a4e .
 * The uniqueness of this is based on a full unique signature in space and time: 2 random UUIDS, server name, server unique id, year/day/month hour:minute:seconds, time, microseconds, a random value 0...9999 .
 * For the same time moment, duplicates values can occur with a chance of 1 in ~ a 340282366920938586008062602462446642046 .
 * Intended usage: Very Large scale.
 *
 * @return STRING 						:: the UUID
 */
public static function uuid_36($prefix='') {
	//--
	$hash = md5($prefix.self::unique_entropy('uid36'));
	//--
	$uuid  = substr($hash,0,8).'-';
	$uuid .= substr($hash,8,4).'-';
	$uuid .= substr($hash,12,4).'-';
	$uuid .= substr($hash,16,4).'-';
	$uuid .= substr($hash,20,12);
	//--
	return (string) strtolower($uuid);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Generates an almost unique SHA1 based UUID of 45 characters [0..9a..f] ; Example: c02acc84-97f4-0807-b12c-ed6f28dd2078-400c1baf .
 * The uniqueness of this is based on a full unique signature in space and time: 2 random UUIDS, server name, server unique id, year/day/month hour:minute:seconds, time, microseconds, a random value 0...9999 .
 * For the same time moment, duplicates values can occur with a chance of 1 in ~ a 1461501637330903466848266086008062602462446642046 .
 * Intended usage: Huge scale.
 *
 * @return STRING 						:: the UUID
 */
public static function uuid_45($prefix='') {
	//--
	$hash = sha1($prefix.self::unique_entropy('uid45'));
	//--
	$uuid  = substr($hash,0,8).'-';
	$uuid .= substr($hash,8,4).'-';
	$uuid .= substr($hash,12,4).'-';
	$uuid .= substr($hash,16,4).'-';
	$uuid .= substr($hash,20,12);
	$uuid .= '-'.substr($hash,32,8);
	//--
	return (string) strtolower($uuid);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Separe the URL parts 			:: a better replacement for parse_url()
 *
 * @param STRING 	$y_url			:: The URL to be separed
 *
 * @return ARRAY 					:: The separed URL (associative array) as: protocol, server, port, path, scriptname
 */
public static function separe_url_parts($y_url) {
	//--
	$y_url = (string) $y_url;
	//--
	$parts = array();
	preg_match("~([a-z]*://)?([^:^/]*)(:([0-9]{1,5}))?(/.*)?~i", (string)$y_url, $parts);
	//--
	$protocol = (string) strtolower($parts[1]);
	$server = (string) $parts[2];
	$port = (string) $parts[4];
	$path = (string) $parts[5];
	//-- some fixes
	if((string)$port == '') {
		if((string)$protocol == 'https://') {
			$port = '443';
		} else {
			$port = '80';
		} //end if else
	} //end if
	//--
	if((string)$path == '') {
		$path = '/';
	} //end if
	//-- script name
	$tmp_arr = (array) explode('?', (string)$path);
	$scriptname = (string) trim((string)$tmp_arr[0]);
	//--
	return array('protocol' => $protocol, 'server' => $server, 'port' => $port, 'path' => $path, 'scriptname' => $scriptname); // script must be compatible with: SmartUtils::get_server_current_full_script() as they may be used as comparation for security purposes
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
	if((defined('SMART_FRAMEWORK_INFO_LOG')) AND (is_dir(dirname((string)SMART_FRAMEWORK_INFO_LOG)))) {
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
 * @param STRING 	$message		:: The message to be triggered
 *
 * @return -						:: This function does not return anything
 */
public static function raise_error($message_to_log, $message_to_display='See Error Log for More Details') {
	//--
	global $smart_____framework_____last__error;
	//--
	$smart_____framework_____last__error = (string) $message_to_display;
	@trigger_error('#SMART-FRAMEWORK.ERROR# '.$message_to_log, E_USER_ERROR);
	die('App Level Raise ERROR. Execution Halted. See the App Error log for more details.'); // normally this line will never be executed because the E_USER_ERROR via Smart Error Handler will die() before ... but this is just in case, as this is a fatal error and the execution should be halted here !
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 *
 * @access 		private
 * @internal
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
	if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
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
?>