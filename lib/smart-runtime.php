<?php
// [SmartFramework / App Runtime]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.5 r.2018.03.09 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - App Runtime (this should be loaded only from app web root)
// DEPENDS: SmartFramework + SmartFramework/Components
// DO NOT MODIFY THIS FILE OR ANY OTHER FILE(S) UNDER lib/* or index.php / admin.php [They will be all overwritten on any future framework upgrades] !!!
// YOU CAN ONLY CHANGE / CUSTOMIZE:
//	* Configurations: etc/*
//	* Modules: modules/*
//======================================================

// [REGEX-SAFE-OK]

//##### WARNING: #####
// Changing the code below is on your own risk and may lead to severe disrupts in the execution of this software !
//####################

//--
if(version_compare(phpversion(), '5.6') < 0) { // check PHP version, we need at least 5.4.20 to use anonymous functions at runtime, but mark 5.6  as minimum for latest optimizations ; since 5.4 / 5.5 are deprecated the framework is no more actively tested on PHP versions < 5.6
	die('PHP Runtime not supported : '.phpversion().' !'.'<br>PHP versions to run this software are: 5.6 / 7.0 / 7.1 / 7.2 or later');
} //end if
//--
if(!function_exists('preg_match')) {
	die('PHP PCRE Extension is missing. It is needed for Regular Expression ...');
} //end if
//--

//--
if(defined('SMART_FRAMEWORK_RELEASE_TAGVERSION') || defined('SMART_FRAMEWORK_RELEASE_VERSION') || defined('SMART_FRAMEWORK_RELEASE_URL') || defined('SMART_FRAMEWORK_RELEASE_MIDDLEWARE')) {
	die('Reserved Constants names have been used: SMART_FRAMEWORK_RELEASE_* is reserved !');
} //end if
//--
define('SMART_FRAMEWORK_RELEASE_TAGVERSION', 'v.3.7.5'); // version tag
define('SMART_FRAMEWORK_RELEASE_VERSION', 'r.2018.03.11'); // release tag (date)
define('SMART_FRAMEWORK_RELEASE_URL', 'http://demo.unix-world.org/smart-framework/');
//--

//--
if(!defined('SMART_FRAMEWORK_ADMIN_AREA')) {
	die('A required RUNTIME constant has not been defined: SMART_FRAMEWORK_ADMIN_AREA');
} //end if
if(SMART_FRAMEWORK_ADMIN_AREA === true) {
	define('SMART_FRAMEWORK_INFO_LOG', 'tmp/logs/adm/'.'info-'.date('Y-m-d@H').'.log');
} else {
	define('SMART_FRAMEWORK_INFO_LOG', 'tmp/logs/idx/'.'info-'.date('Y-m-d@H').'.log');
} //end if else
//--

//--
if(!headers_sent()) {
	header('X-Powered-By: '.'Smart.Framework PHP/Javascript :: '.SMART_FRAMEWORK_RELEASE_TAGVERSION.'-'.SMART_FRAMEWORK_RELEASE_VERSION.' @ '.((SMART_FRAMEWORK_ADMIN_AREA === true) ? '[A]' : '[I]'));
} //end if
//--

//--
if(!defined('SMART_FRAMEWORK_DEBUG_MODE')) { // {{{SYNC-DEFINE-DBGMODE}}}
	define('SMART_FRAMEWORK_DEBUG_MODE', 'no'); // if not explicit defined, set it here to avoid later modifications
} //end if
//--
if((file_exists('____APP_Install_Mode__Enabled')) OR (is_link('____APP_Install_Mode__Enabled'))) { // here must be file_exists() and is_link() as the file sys lib is not yet initialized ... {{{SYNC-SF-PATH-EXISTS}}}
	define('SMART_FRAMEWORK_INSTALL_MODE', 'yes');
} else {
	define('SMART_FRAMEWORK_INSTALL_MODE', 'no');
} //end if else
//--

//== CHECK: REQUIRED INIT CONSTANTS
//--
if(!defined('SMART_SOFTWARE_NAMESPACE')) {
	die('A required INIT constant has not been defined: SMART_SOFTWARE_NAMESPACE');
} //end if
if((strlen(SMART_SOFTWARE_NAMESPACE) < 10) OR (strlen(SMART_SOFTWARE_NAMESPACE) > 25)) {
	die('A required INIT constant must have a length between 10 and 25 characters: SMART_SOFTWARE_NAMESPACE');
} //end if
if(!preg_match('/^[_a-z0-9\-\.]+$/', (string)SMART_SOFTWARE_NAMESPACE)) {
	die('A required INIT constant contains invalid characters: SMART_SOFTWARE_NAMESPACE');
} //end if
//--
if(!defined('SMART_FRAMEWORK_TIMEZONE')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_TIMEZONE');
} //end if
//--
if(!defined('SMART_FRAMEWORK_SECURITY_KEY')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_SECURITY_KEY');
} //end if
//--
if(!defined('SMART_FRAMEWORK_SESSION_NAME')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_SESSION_NAME');
} //end if
if(!defined('SMART_FRAMEWORK_SESSION_LIFETIME')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_SESSION_LIFETIME');
} //end if
if(!defined('SMART_FRAMEWORK_SESSION_HANDLER')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_SESSION_HANDLER');
} //end if
//--
if(!defined('SMART_FRAMEWORK_MEMORY_LIMIT')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_MEMORY_LIMIT');
} //end if
if(!defined('SMART_FRAMEWORK_EXECUTION_TIMEOUT')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_EXECUTION_TIMEOUT');
} //end if
if(!defined('SMART_FRAMEWORK_NETSOCKET_TIMEOUT')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_NETSOCKET_TIMEOUT');
} //end if
if(!defined('SMART_FRAMEWORK_NETSERVER_ID')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_NETSERVER_ID');
} //end if
if(!defined('SMART_FRAMEWORK_SSL_MODE')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_SSL_MODE');
} //end if
//--
if(!defined('SMART_FRAMEWORK_FILE_LOCKTIME')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_FILE_LOCKTIME');
} //end if
if(!is_int(SMART_FRAMEWORK_FILE_LOCKTIME)) {
	die('Invalid INIT constant value for SMART_FRAMEWORK_FILE_LOCKTIME');
} //end if
if(!defined('SMART_FRAMEWORK_CHMOD_DIRS')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_CHMOD_DIRS');
} //end if
if(!is_int(SMART_FRAMEWORK_CHMOD_DIRS)) {
	die('Invalid INIT constant value for SMART_FRAMEWORK_CHMOD_DIRS');
} //end if
if(!defined('SMART_FRAMEWORK_CHMOD_FILES')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_CHMOD_FILES');
} //end if
if(!is_int(SMART_FRAMEWORK_CHMOD_FILES)) {
	die('Invalid INIT constant value for SMART_FRAMEWORK_CHMOD_FILES');
} //end if
//--
if(!defined('SMART_FRAMEWORK_DOWNLOAD_FOLDERS')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_DOWNLOAD_FOLDERS');
} //end if
if(!defined('SMART_FRAMEWORK_UPLOAD_PICTS')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_UPLOAD_PICTS');
} //end if
if(!defined('SMART_FRAMEWORK_UPLOAD_MOVIES')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_UPLOAD_MOVIES');
} //end if
if(!defined('SMART_FRAMEWORK_UPLOAD_DOCS')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_UPLOAD_DOCS');
} //end if
if(!defined('SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS');
} //end if
//--
if(!defined('SMART_FRAMEWORK_HTACCESS_NOEXECUTION')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_HTACCESS_NOEXECUTION');
} //end if
if(!defined('SMART_FRAMEWORK_HTACCESS_FORBIDDEN')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_HTACCESS_FORBIDDEN');
} //end if
if(!defined('SMART_FRAMEWORK_HTACCESS_NOINDEXING')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_HTACCESS_NOINDEXING');
} //end if
if(!defined('SMART_FRAMEWORK_IDENT_ROBOTS')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_IDENT_ROBOTS');
} //end if
//--
if(!defined('SMART_FRAMEWORK_URL_VALUE_ENABLED')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_URL_VALUE_ENABLED');
} //end if
if(!defined('SMART_FRAMEWORK_URL_PARAM_MODALPOPUP')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_URL_PARAM_MODALPOPUP');
} //end if
if(!defined('SMART_FRAMEWORK_URL_PARAM_PRINTABLE')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_URL_PARAM_PRINTABLE');
} //end if
//--
if(!defined('SMART_SOFTWARE_FRONTEND_ENABLED')) {
	define('SMART_SOFTWARE_FRONTEND_ENABLED', true); // if not explicit defined, set it here to avoid later modifications
} //end if
if(!defined('SMART_SOFTWARE_BACKEND_ENABLED')) {
	define('SMART_SOFTWARE_BACKEND_ENABLED', true); // if not explicit defined, set it here to avoid later modifications
} //end if
if(!defined('SMART_SOFTWARE_URL_ALLOW_PATHINFO')) {
	define('SMART_SOFTWARE_URL_ALLOW_PATHINFO', 0); // if not explicit defined, set it here to avoid later modifications
} //end if
//--
if(!defined('SMART_FRAMEWORK_CHARSET')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_CHARSET');
} //end if
if(!defined('SMART_FRAMEWORK_DBSQL_CHARSET')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_DBSQL_CHARSET');
} //end if
if(!defined('SMART_FRAMEWORK_LANGUAGES_CACHE_DIR')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_LANGUAGES_CACHE_DIR');
} //end if
if(!preg_match('/^[a-z\/]+$/', (string)SMART_FRAMEWORK_LANGUAGES_CACHE_DIR)) {
	die('A required INIT constant contains invalid characters: SMART_FRAMEWORK_LANGUAGES_CACHE_DIR');
} //end if
//--
//==

//== REGISTER REQUEST INPUT VARIABLES (GET, POST, COOKIE, SERVER)
// WARNING: This must be done before loading any configs or other files that contain variables that may be rewritten
//--------------------------------------- LOAD APP.REQUEST (HANDLER)
if(!defined('SMART_FRAMEWORK_APP_REQUEST')) {
	die('The App.Boostrap Script has not been defined: SMART_FRAMEWORK_APP_REQUEST');
} //end if
if(substr((string)SMART_FRAMEWORK_APP_REQUEST, -15, 15) != 'app-request.php') {
	die('Invalid App.Boostrap Script: '.SMART_FRAMEWORK_APP_REQUEST);
} //end if
require((string)SMART_FRAMEWORK_APP_REQUEST); // (This can be customized)
//---------------------------------------
//==

//=========================
//========================= ALL CODE BELOW: must be created, loaded or registered after GET/POST variables registration to avoid security leaks !!! Do not modify this order ...
//=========================

//--------------------------------------- CONFIG INITS
$configs = array();
$languages = array();
//---------------------------------------

//--------------------------------------- LOAD CONFIGS
require('etc/config.php'); // load the main configuration, after GET/POST registration
//---------------------------------------

//--------------------------------------- LOAD SMART-FRAMEWORK
require('lib/framework/lib__smart_framework.php');
//--------------------------------------- REGISTER AUTO-LOAD OF PLUGINS (by dependency injection)
require('lib/core/plugins/autoload.php');
//--------------------------------------- LOAD SMART-COMPONENTS
require('lib/core/lib_smart_components.php');
//--------------------------------------- CONDITIONAL LOAD (IF DEBUG: PROFILER)
if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
	require('lib/core/lib_debug_profiler.php');
} //end if
//---------------------------------------
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//---------------------------------------

//--------------------------------------- Monitor High Loads and if detected Return 503 Too Busy
SmartFrameworkRuntime::High_Load_Monitor();
//---------------------------------------

//--------------------------------------- create temporary dir (required by Smart.Framework)
SmartFrameworkRuntime::Create_Required_Dirs();
//---------------------------------------

//--------------------------------------- LOAD APP.BOOTSTRAP
if(!defined('SMART_FRAMEWORK_APP_BOOTSTRAP')) {
	die('The App.Boostrap Script has not been defined: SMART_FRAMEWORK_APP_BOOTSTRAP');
} //end if
if(substr((string)SMART_FRAMEWORK_APP_BOOTSTRAP, -17, 17) != 'app-bootstrap.php') {
	die('Invalid App.Boostrap Script: '.SMART_FRAMEWORK_APP_BOOTSTRAP);
} //end if
require((string)SMART_FRAMEWORK_APP_BOOTSTRAP); // (This can be customized)
//--------------------------------------- VARIOUS CHECKS FOR APP.BOOTSTRAP
if(!defined('SMART_SOFTWARE_APP_NAME')) {
	die('A required BOOTSTRAP Constant has not been defined: SMART_SOFTWARE_APP_NAME');
} //end if
//--
if(!class_exists('SmartPersistentCache')) {
	die('SmartFramework // Runtime: the Class SmartPersistentCache is missing ...');
} //end if
if((string)get_parent_class('SmartPersistentCache') != 'SmartAbstractPersistentCache') {
	die('SmartFramework // Runtime: the Class SmartPersistentCache must be extended from the Class SmartAbstractPersistentCache ...');
} //end if
//--
if(!class_exists('SmartAdapterTextTranslations')) {
	die('SmartFramework // Runtime: the Class SmartAdapterTextTranslations is missing ...');
} //end if
if(!is_subclass_of('SmartAdapterTextTranslations', 'SmartInterfaceAdapterTextTranslations', true)) {
	die('SmartFramework // Runtime: the Class SmartAdapterTextTranslations must implement the SmartInterfaceAdapterTextTranslations ...');
} //end if
//--
if(!class_exists('SmartAppInfo')) {
	die('SmartFramework // Runtime: the Class SmartAppInfo is missing ...');
} //end if
if(!is_subclass_of('SmartAppInfo', 'SmartInterfaceAppInfo', true)) {
	die('SmartFramework // Runtime: the Class SmartAppInfo must implement the SmartInterfaceAppInfo ...');
} //end if
//--
if(!class_exists('SmartAppBootstrap')) {
	die('SmartFramework // Runtime: the Class SmartAppBootstrap is missing ...');
} //end if
if(!is_subclass_of('SmartAppBootstrap', 'SmartInterfaceAppBootstrap', true)) {
	die('SmartFramework // Runtime: the Class SmartAppBootstrap must implement the SmartInterfaceAppBootstrap ...');
} //end if
//---------------------------------------

//######################### MONITOR: REDIRECTION CONTROLLER
SmartFrameworkRuntime::Redirection_Monitor();
//######################### REGISTER UNIQUE ID COOKIE (required before run)
SmartFrameworkRuntime::SetVisitorEntropyIDCookie(); // will define the constant SMART_APP_VISITOR_COOKIE ; cookie will be set only if SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME is non empty
//######################### APP.BOOTSTRAP: RUN
SmartAppBootstrap::Run();
//#########################
SmartCache::setKey('smart-app-runtime', 'visitor-cookie', (string)SMART_APP_VISITOR_COOKIE);
//#########################


//==================================================================================
//================================================================================== CLASS START
//==================================================================================

/**
 * Class Smart.Framework Security
 * It may be used anywhere inside Smart.Framework or by Plugins and Application Modules.
 *
 * <code>
 * // Usage example:
 * SmartFrameworkSecurity::some_method_of_this_class(...);
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 *
 * @depends 	-
 * @version 	v.180309
 * @package 	Application
 *
 */
final class SmartFrameworkSecurity {

	// ::
	// This class have to be able to run before loading the Smart.Framework and must not depend on it's classes.


//======================================================================
// Validate variable names (allow to register ONLY lowercase variables to avoid interfere with PHP reserved variables !! security fix !!)
public static function ValidateVariableName($y_varname) {

	// VALIDATE INPUT VARIABLE NAMES v.160204

	//--
	$y_varname = (string) $y_varname; // force string
	//--
	$regex_only_number = '/^[0-9_]+$/'; // not allowed as first character, especially the _ because $_ have a very special purpose in PHP
	$regex_var_name = '/^[a-z0-9_]+$/'; // allowed characters in a variable name (only small letters, numbers and _ ; in PHP upper letters for variables are reserved)
	//--

	//-- init
	$out = 0;
	//--

	//-- validate characters
	if(((string)$y_varname != '') AND ((string)$y_varname != '_') AND (preg_match((string)$regex_var_name, (string)$y_varname)) AND (!preg_match((string)$regex_only_number, (string)substr((string)$y_varname, 0, 1)))) {
		$out = 1;
	} //end if else
	//--

	//-- corrections (variable name must be between 1 char and 255 chars)
	if(strlen($y_varname) < 1) {
		$out = 0;
	} //end if
	if(strlen($y_varname) > 255) {
		$out = 0;
	} //end if
	//--

	//--
	return $out;
	//--

} //END FUNCTION
//======================================================================


//======================================================================
// Filter Input String
public static function FilterUnsafeString($y_value) {
	//--
	if((is_array($y_value)) || (is_object($y_value))) {
		return null;
	} //end if
	//--
	if(defined('SMART_FRAMEWORK_SECURITY_FILTER_INPUT')) {
		if((string)SMART_FRAMEWORK_SECURITY_FILTER_INPUT != '') {
			if((string)$y_value != '') {
				$y_value = preg_replace((string)SMART_FRAMEWORK_SECURITY_FILTER_INPUT, '', (string)$y_value);
			} //end if
		} //end if
	} //end if
	//--
	return (string) $y_value;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
/**
 * Return the filtered values for GET/POST REQUEST variables (Max: 3+1 levels for arrays).
 * It is used to prevent insecure variables.
 * All the input vars should be always filtered to avoid extremely long arrays or insecure characters.
 *
 * @param STRING/ARRAY 		$y_var	the input variable
 * @return STRING/ARRAY				[processed]
 */
public static function FilterGetPostCookieVars($y_var) {
	//-- v.150527 magic_quotes_gpc has been removed since PHP 5.4, no more check for it
	if(!isset($y_var)) {
		return $y_var; // fix for Illegal string offset
	} //end if
	//--
	if(is_array($y_var)) { // array
		//--
		$newvar = array();
		//--
		foreach($y_var as $key => $val) {
			//--
			if(is_array($val)) { // array
				//--
				foreach($val as $tmp_key => $tmp_val) {
					//--
					if(is_array($tmp_val)) { // array
						//--
						foreach($tmp_val as $tmpx_key => $tmpx_val) {
							//--
							$newvar[(string)$key][(string)$tmp_key][(string)$tmpx_key] = (string) self::FilterUnsafeString((string)$tmpx_val); // 1
							//--
						} //end while
						//--
					} else { // string
						//--
						$newvar[(string)$key][(string)$tmp_key] = (string) self::FilterUnsafeString((string)$tmp_val); // 2
						//--
					} //end if else
					//--
				} //end while
				//--
			} else { // string
				//--
				$newvar[(string)$key] = (string) self::FilterUnsafeString((string)$val); // 3
				//--
			} //end if else
			//--
		} //end while
		//--
	} else { // string
		//--
		$newvar = (string) self::FilterUnsafeString((string)$y_var); // 4
		//--
	} //end if
	//--
	return $newvar; // string or array
	//--
} //END FUNCTION
//======================================================================


//======================================================================
/**
 * Return the url decoded (+/- filtered) variable from RAWURLENCODE / URLENCODE
 * It may be used ONLY when working with RAW PATH INFO / RAW QUERY URLS !!!
 *
 * @param STRING 				$y_var		the input variable
 * @param BOOLEAN 				$y_filter 	*Optional* Default to TRUE ; if FALSE will only decode but not filter variable ; DO NOT DISABLE FILTERING EXCEPT WHEN YOU CALL IT LATER EXPLICIT !!!
 * @return STRING				[processed]
 */
public static function urlVarDecodeStr($y_urlencoded_str_var, $y_filter=true) {
	//--
	$y_urlencoded_str_var = (string) @urldecode((string)$y_urlencoded_str_var); // use urldecode() which decodes all % but also the + ; instead of rawurldecode() which does not decodes + !
	//--
	if($y_filter) {
		$y_urlencoded_str_var = (string) self::FilterUnsafeString($y_urlencoded_str_var);
	} //end if
	//--
	return (string) $y_urlencoded_str_var;
	//--
} //END FUNCTION
//======================================================================


} //END CLASS

//==================================================================================
//================================================================================== CLASS END
//==================================================================================



//==================================================================================
//================================================================================== CLASS START
//==================================================================================

/**
 * Class Smart.Framework Registry
 * It may be used anywhere inside Smart.Framework or by Plugins and Application Modules.
 * Normally there is no need to use this class as the controllers can access methods of this class directly.
 *
 * THIS CLASS IS FOR VERY ADVANCED USAGE ONLY !
 *
 * @access 		private
 * @internal
 *
 * @depends 	-
 * @version 	v.180309
 * @package 	Application
 *
 */
final class SmartFrameworkRegistry {

	// ::
	//  This class have to be able to run before loading the Smart.Framework and must not depend on it's classes.

	public static $Connections = array(); // connections registry

	private static $DebugMessages = array( // debug messages registry
		'stats' 			=> [],
		'optimizations' 	=> [],
		'extra' 			=> [],
		'db' 				=> [],
		'mail' 				=> [],
		'modules' 			=> []
	);

	private static $RequestLock = false; 	// request locking flag
	private static $RequestPath = '';		// request path (from path-info)
	private static $RequestVars = array(); 	// request registry
	private static $CookieVars  = array();  // cookie registry


	//##### Public Methods


	public static function setRequestPath($value) {
		//--
		if(self::$RequestLock !== false) {
			return false; // request registry is locked
		} //end if
		//--
		self::$RequestPath = (string) $value;
		//--
		return true; // OK
		//--
	} //END FUNCTION


	public static function getRequestPath() {
		//--
		return (string) self::$RequestPath;
		//--
	} //END FUNCTION


	public static function setRequestVar($key, $value) {
		//--
		if(self::$RequestLock !== false) {
			return false; // request registry is locked
		} //end if
		//--
		$key = (string) strtolower((string)trim((string)$key)); // {{{SYNC-REQVARS-LOWER-KEYS}}}
		if(((string)$key == '') OR (!SmartFrameworkSecurity::ValidateVariableName((string)$key))) {
			return false;
		} //end if
		//--
		self::$RequestVars[(string)$key] = $value;
		//--
		return true; // OK
		//--
	} //END FUNCTION


	public static function issetRequestVar($key) {
		//--
		$key = (string) strtolower((string)trim((string)$key)); // {{{SYNC-REQVARS-LOWER-KEYS}}}
		if(((string)$key == '') OR (!SmartFrameworkSecurity::ValidateVariableName((string)$key))) {
			return false;
		} //end if
		//--
		if((array_key_exists((string)$key, self::$RequestVars)) AND (isset(self::$RequestVars[(string)$key])) AND ((is_array(self::$RequestVars[(string)$key])) OR ((string)self::$RequestVars[(string)$key] != ''))) { // if is set and (array or non-empty string) ; numbers from request comes as string too
			return true;
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION


	public static function getRequestVar($key, $defval=null, $type='') { // {{{SYNC-REQUEST-DEF-PARAMS}}}
		//--
		$key = (string) strtolower((string)trim((string)$key)); // {{{SYNC-REQVARS-LOWER-KEYS}}}
		if(((string)$key == '') OR (!SmartFrameworkSecurity::ValidateVariableName((string)$key))) {
			return null;
		} //end if
		//--
		if(self::issetRequestVar((string)$key) === true) {
			$val = self::$RequestVars[(string)$key]; // use the value from request :: mixed
		} else {
			$val = $defval; // init with the default value :: mixed
		} //end if
		//--
		if(is_array($type)) { // # if $type is array, then cast is ENUM LIST, it must contain an array with all allowed values as strings #
			//--
			if(((string)$val != '') AND (in_array((string)$val, (array)$type))) {
				$val = (string) $val; // force string
			} else {
				$val = ''; // set as empty string
			} //end if else
			//--
		} else { // # else $type must be a string with one of the following cases #
			//--
			switch((string)strtolower((string)$type)) {
				case 'array':
					if(!is_array($val)) {
						$val = array(); // set as empty array
					} else {
						$val = (array) $val; // force array
					} //end if else
					break;
				case 'string':
					$val = (string) $val;
					break;
				case 'boolean':
					$val = (string) strtolower((string)$val);
					if(((string)$val == 'true') OR ((string)$val == 't')) {
						$val = true;
					} elseif(((string)$val == 'false') OR ((string)$val == 'f')) {
						$val = false;
					} else {
						$val = (bool) $val;
					} //end if else
					break;
				case 'integer':
					$val = (int) $val;
					break;
				case 'integer+':
					$val = (int) $val;
					if($val < 0) { // {{{SYNC-SMART-INT+}}}
						$val = 0;
					} //end if
					break;
				case 'integer-':
					$val = (int) $val;
					if($val > 0) { // {{{SYNC-SMART-INT-}}}
						$val = 0;
					} //end if
					break;
				case 'decimal1':
					$val = (string) number_format(((float)$val), 1, '.', ''); // {{{SYNC-SMART-DECIMAL}}}
					break;
				case 'decimal2':
					$val = (string) number_format(((float)$val), 2, '.', ''); // {{{SYNC-SMART-DECIMAL}}}
					break;
				case 'decimal3':
					$val = (string) number_format(((float)$val), 3, '.', ''); // {{{SYNC-SMART-DECIMAL}}}
					break;
				case 'decimal4':
					$val = (string) number_format(((float)$val), 4, '.', ''); // {{{SYNC-SMART-DECIMAL}}}
					break;
				case 'numeric':
					$val = 0 + (float) $val;
					break;
				case 'mixed': // mixed variable types, can vary by context, leave as is
				case 'raw': // raw, alias for mixed, leave as is
				case '': // no explicit format (take as raw / mixed)
					// return as is ... (in this case extra validations have to be done explicit in the controller)
					if(is_object($val)) { // dissalow objects here !!!
						$val = '';
					} //end if
					break;
				default:
					@trigger_error(__CLASS__.'::'.__FUNCTION__.'() // Invalid Request Variable ['.$key.'] Type: '.$type, E_USER_WARNING);
			} //end switch
			//--
		} //end if else
		//--
		return $val; // mixed
		//--
	} //END FUNCTION


	public static function getRequestVars() {
		//--
		return (array) self::$RequestVars; // array
		//--
	} //END FUNCTION


	public static function setCookieVar($key, $value) {
		//--
		if(self::$RequestLock !== false) {
			return false; // request registry is locked
		} //end if
		//--
		$key = (string) trim((string)$key); // {{{SYNC-COOKIEVARS-KEYS}}}
		if((string)$key == '') {
			return false;
		} //end if
		//--
		self::$CookieVars[(string)$key] = (string) $value; // cookie vars can be only string types (also numeric will be stored as string)
		//--
		return true; // OK
		//--
	} //END FUNCTION


	public static function issetCookieVar($key) {
		//--
		$key = (string) trim((string)$key); // {{{SYNC-COOKIEVARS-KEYS}}}
		if((string)$key == '') {
			return false;
		} //end if
		//--
		if((array_key_exists((string)$key, self::$CookieVars)) AND (isset(self::$CookieVars[(string)$key]))) { // if is set and not null ; cookies are considered to be set if they exist and non-null
			return true;
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION


	public static function getCookieVar($key) {
		//--
		$key = (string) trim((string)$key); // {{{SYNC-COOKIEVARS-KEYS}}}
		if((string)$key == '') {
			return null;
		} //end if
		//--
		if(self::issetCookieVar((string)$key) === true) {
			$val = (string) self::$CookieVars[(string)$key]; // use the value from cookies :: string
		} else {
			$val = null; // init with the default value :: null
		} //end if
		//--
		return $val; // mixed
		//--
	} //END FUNCTION


	public static function getCookieVars() {
		//--
		return (array) self::$CookieVars; // array
		//--
	} //END FUNCTION


	//##### Internal Lock Control


	/**
	 * Locks the Request Object after parsing the $_REQUEST to avoid security injections in framework's runtime request after parsing
	 *
	 * @access 		private
	 * @internal
	 */
	public static function lockRequestVar() {
		//--
		return self::$RequestLock = true;
		//--
	} //END FUNCTION


	//##### Debugging


	/**
	 * Get Debug Message
	 *
	 * @access 		private
	 * @internal
	 */
	public static function getDebugMsgs($area) {
		//--
		switch((string)$area) {
			case 'stats':
				return (array) self::$DebugMessages['stats'];
				break;
			case 'optimizations':
				return (array) self::$DebugMessages['optimizations'];
				break;
			case 'extra':
				return (array) self::$DebugMessages['extra'];
				break;
			case 'db':
				return (array) self::$DebugMessages['db'];
				break;
			case 'mail':
				return (array) self::$DebugMessages['mail'];
				break;
			case 'modules':
				return (array) self::$DebugMessages['modules'];
				break;
			default:
				// invalid area - register a notice to log
				@trigger_error(__CLASS__.'::'.__FUNCTION__.'()'."\n".'INVALID DEBUG AREA: '.$area, E_USER_NOTICE);
				return array();
		} //end switch
		//--
	} //END FUNCTION


	/**
	 * Set Debug Message
	 *
	 * @access 		private
	 * @internal
	 */
	public static function setDebugMsg($area, $context, $dbgmsg, $opmode='') {
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
			return;
		} //end if
		//--
		if(!$dbgmsg) {
			return;
		} //end if
		//--
		$subcontext = '';
		if(strpos((string)$context, '|') !== false) {
			$arr = (array) explode('|', (string)$context, 3); // separe 1st and 2nd from the rest
			$context = (string) trim((string)$arr[0]);
			$subcontext = (string) trim((string)$arr[1]);
			unset($arr);
		} //end if
		if((string)$context == '') {
			$context = '-UNDEFINED-CONTEXT-';
		} //end if
		//--
		switch((string)$area) {
			case 'stats':
				self::$DebugMessages['stats'][(string)$context] = $dbgmsg; // stats will be always rewrite (as assign: =) to avoid duplicates
				break;
			case 'optimizations':
				self::$DebugMessages['optimizations'][(string)$context][] = $dbgmsg;
				break;
			case 'extra':
				self::$DebugMessages['extra'][(string)$context][] = $dbgmsg;
				break;
			case 'db': // can have sub-context
				if((string)$subcontext == '') {
					$subcontext = '-UNDEFINED-SUBCONTEXT-'; // db must have a sub-context always
				} //end if
				switch((string)$opmode) {
					case '+': // increment
						self::$DebugMessages['db'][(string)$context][(string)$subcontext] += (float) $dbgmsg;
						break;
					case '=': // assign
						self::$DebugMessages['db'][(string)$context][(string)$subcontext] = $dbgmsg;
						break;
					default: // default, add new entry []
						self::$DebugMessages['db'][(string)$context][(string)$subcontext][] = $dbgmsg;
				} //end switch
				break;
			case 'mail':
				self::$DebugMessages['mail'][(string)$context][] = $dbgmsg;
				break;
			case 'modules':
				self::$DebugMessages['modules'][(string)$context][] = $dbgmsg;
				break;
			default:
				// drop message and register a notice to log
				@trigger_error(__CLASS__.'::'.__FUNCTION__.'()'."\n".'INVALID DEBUG AREA: '.$area."\n".'Message Content: '.print_r($dbgmsg,1), E_USER_NOTICE);
		} //end switch
		//--
	} //END FUNCTION


	/**
	 * Register to Internal Cache
	 *
	 * @access 		private
	 * @internal
	 */
	public static function registerInternalCacheToDebugLog() {
		//--
		if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
			if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
				self::setDebugMsg('extra', '***SMART-CLASSES:INTERNAL-CACHE***', [
					'title' => 'SmartFrameworkRegistry // Internal Data',
					'data' => 'Dump of Request Lock: ['.print_r(self::$RequestLock,1).']'."\n".'Dump of Request Vars Keys: '.print_r(array_keys((array)self::$RequestVars),1)."\n".'Dump of Connections:'."\n".print_r(self::$Connections,1)
				]);
			} //end if
		} //end if
		//--
	} //END FUNCTION


} //END CLASS


//==================================================================================
//================================================================================== CLASS END
//==================================================================================


//==================================================================================
//================================================================================== CLASS START
//==================================================================================

/**
 * Class Smart.Framework Runtime
 *
 * @access 		private
 * @internal
 * @ignore		THIS CLASS IS FOR INTERNAL USE ONLY BY SMART-FRAMEWORK.RUNTIME !!!
 *
 * @depends 	classes: Smart
 * @version		180309
 * @package 	Application
 *
 */
final class SmartFrameworkRuntime {

	// ::
	// {{{SYNC-SMART-HTTP-STATUS-CODES}}}

	private static $AppReleaseHash = '';

	private static $HttpStatusCodesOK  = [200, 202, 203, 208]; 								// list of framework available HTTP OK Status Codes (sync with middlewares)
	private static $HttpStatusCodesRDR = [301, 302]; 										// list of framework available HTTP Redirect Status Codes (sync with middlewares)
	private static $HttpStatusCodesERR = [400, 401, 403, 404, 429, 500, 502, 503, 504]; 	// list of framework available HTTP Error Status Codes (sync with middlewares)

	private static $RequestProcessed 			= false; 	// after all request variables are processed this will be set to true to avoid re-process request variables which can be a huge security issue if re-process is called by mistake !
	private static $RequiredDirsCreated 		= false;	// after creating required dirs this will be set to true to avoid re-run that function again
	private static $RedirectionMonitorStarted 	= false; 	// after the redirection monitor have been started this will be set to true to avoid re-run it
	private static $HighLoadMonitorStats 		= null; 	// register the high load monitor caches


//======================================================================
// get the App Release Hash based on Framework Version.Release.ModulesRelease
public static function getAppReleaseHash() {
	//--
	if((string)self::$AppReleaseHash == '') {
		$crc32 = (int) crc32((string)SMART_FRAMEWORK_RELEASE_TAGVERSION.(string)SMART_FRAMEWORK_RELEASE_VERSION.(string)SMART_APP_MODULES_RELEASE);
		if($crc32 < 0) {
			$crc32 = (int) (-1 * $crc32); // fix for 32bit platforms
		} //end if
		self::$AppReleaseHash = (string) strtolower((string)base_convert((int)$crc32, 10, 36));
	} //end if
	//--
	return (string) self::$AppReleaseHash;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// the the array list with OK HTTP Status Codes (only codes that smart framework middlewares will handle)
public static function getHttpStatusCodesOK() {
	//--
	return (array) self::$HttpStatusCodesOK;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// the the array list with REDIRECT HTTP Status Codes (only codes that smart framework middlewares will handle)
public static function getHttpStatusCodesRDR() {
	//--
	return (array) self::$HttpStatusCodesRDR;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// the the array list with ERROR HTTP Status Codes (only codes that smart framework middlewares will handle)
public static function getHttpStatusCodesERR() {
	//--
	return (array) self::$HttpStatusCodesERR;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// the the array list with ALL(OK,REDIRECT,ERROR) HTTP Status Codes (only codes that smart framework middlewares will handle)
public static function getHttpStatusCodesALL() {
	//--
	return (array) array_merge(self::getHttpStatusCodesOK(), self::getHttpStatusCodesRDR(), self::getHttpStatusCodesERR());
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// This will run before loading the Smart.Framework and must not depend on it's classes
// After all Request are processed this have to be called to lock and avoid re-processing the Request variables
public static function Lock_Request_Processing() {
	//--
	self::$RequestProcessed = true; // this will lock the Request processing
	//--
	SmartFrameworkRegistry::lockRequestVar(); // this will lock the request registry
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// check if pathInfo is enabled (allowed)
public static function PathInfo_Enabled() {
	//--
	$status = false;
	//--
	if(defined('SMART_SOFTWARE_URL_ALLOW_PATHINFO')) {
		//--
		switch((int)SMART_SOFTWARE_URL_ALLOW_PATHINFO) {
			case 3: // only index enabled
				if(SMART_FRAMEWORK_ADMIN_AREA !== true) {
					$status = true;
				} //end if
				break;
			case 2: // both enabled: index & admin
				$status = true;
				break;
			case 1: // only admin enabled
				if(SMART_FRAMEWORK_ADMIN_AREA === true) {
					$status = true;
				} //end if
				break;
			case 0: // none enabled
			default:
				// not enabled
		} //end switch
		//--
	} //end if
	//--
	return (bool) $status;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// This will run before loading the Smart.Framework and must not depend on it's classes
public static function Parse_Semantic_URL() {

	// PARSE SEMANTIC URL VIA GET v.170519
	// it limits the URL to 65535 and vars to 1000

	//-- check overall
	if(defined('SMART_FRAMEWORK_SEMANTIC_URL_DISABLE')) {
		return;
	} //end if
	//--

	//-- check if can run
	if(self::$RequestProcessed !== false) {
		return; // avoid run after it was already processed
	} //end if
	//--

	//--
	if((self::PathInfo_Enabled() === true) AND (isset($_SERVER['PATH_INFO'])) AND ((string)$_SERVER['PATH_INFO'] != '')) {
		$semantic_url = '';
		$fix_pathinfo = (string) SmartFrameworkSecurity::FilterUnsafeString((string)trim((string)$_SERVER['PATH_INFO'])); // variables from PathInfo are already URL Decoded, so must be ONLY Filtered !
		$sem_path_pos = strpos((string)$fix_pathinfo, '/~');
		if($sem_path_pos !== false) {
			$semantic_url = (string) '?'.substr((string)$fix_pathinfo, 0, $sem_path_pos);
			$path_url = (string) substr((string)$fix_pathinfo, ($sem_path_pos + 2));
			SmartFrameworkRegistry::setRequestPath(
				(string) ($path_url ? (string)$path_url : '/')
			) OR @trigger_error(__CLASS__.'::'.__FUNCTION__.'() :: '.'Failed to register !path-info! variable', E_USER_WARNING);
		} //end if
	} else {
		$semantic_url = (string) $_SERVER['REQUEST_URI'];
	} //end if
	//--
	if(strlen($semantic_url) > 65535) { // limit according with Firefox standard which is 65535 ; Apache standard is much lower as 8192
		$semantic_url = substr($semantic_url, 0, 65535);
	} //end if
	//--
	if(strpos($semantic_url, '?/') === false) {
		return;
	} //end if
	//--

	//--
	$get_arr = (array) explode('?/', $semantic_url, 2); // separe 1st from 2nd by ?/ if set
	$location_str = (string) trim((string)$get_arr[1]);
	$get_arr = (array) explode('&', $location_str, 2); // separe 1st from 2nd by & if set
	$location_str = (string) trim((string)$get_arr[0]);
	$get_arr = array(); // cleanup
	//--

	//--
	if((string)$location_str != '') {
		//--
		$location_arx = (array) explode('/', (string)$location_str, 1001); // max is 1000, so separe them from the rest
		$cnt_arx = (int) count($location_arx);
		if($cnt_arx > 1000) {
			$cnt_arx = 1000;
		} //end if
		//--
		$location_arr = array();
		if(is_array($location_arx)) {
			for($i=0; $i<$cnt_arx; $i++) {
				if((trim((string)$location_arx[$i]) != '') AND (trim((string)$location_arx[$i+1]) != '')) {
					$location_arx[$i+1] = (string) SmartFrameworkSecurity::urlVarDecodeStr((string)$location_arx[$i+1], false); // do not filter here, will filter later when exracting to avoid double filtering !
					$location_arx[$i+1] = (string) str_replace((string)rawurlencode('/'), '/', (string)$location_arx[$i+1]);
					$location_arr[(string)$location_arx[$i]] = (string) $location_arx[$i+1];
				} //end if
				$i += 1;
			} //end for
		} //end if
		//--
		//print_r($location_arr);
		if(is_array($location_arr)) {
			if(count($location_arr) > 0) {
				self::Extract_Filtered_Request_Get_Post_Vars($location_arr, 'SEMANTIC-URL');
			} //end if
		} //end if
		//--
	} //end if
	//--

} //END FUNCTION
//======================================================================


//======================================================================
// This will run before loading the Smart.Framework and must not depend on it's classes
public static function Extract_Filtered_Request_Get_Post_Vars($filter_____arr, $filter_____info) {

	// FILTER INPUT GET/POST VARIABLES v.180218 (with collision fix and private space check)
	// This no more limits the input variables as it is handled via prior checks to PHP.INI: max_input_vars and max_input_nesting_level
	// If any of: GET / POST overflow the max_input_vars and max_input_nesting_level a PHP warning is issued !!
	// The max_input_vars applies separately to each of the input variables, includding array(s) keys
	// The max_input_nesting_level also must be at least 5

	//-- check if can run
	if(self::$RequestProcessed !== false) {
		@trigger_error(__CLASS__.'::'.__FUNCTION__.'() :: '.'Cannot Register Request/'.$filter_____info.' Vars, Registry is already locked !', E_USER_WARNING);
		return; // avoid run after it was already processed
	} //end if
	//--

	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		self::DebugRequestLog('######################### FILTER REQUEST:'."\n".date('Y-m-d H:i:s O')."\n".$_SERVER['REQUEST_URI']."\n\n".'##### RAW REQUEST VARS:'."\n".'['.$filter_____info.']'."\n".print_r($filter_____arr, 1)."\n");
	} //end if
	//--

	//-- process
	if(is_array($filter_____arr)) {
		//--
		foreach($filter_____arr as $filter_____key => $filter_____val) {
			//--
			$filter_____key = (string) $filter_____key; // force string
			//--
			if(substr($filter_____key, 0, 11) != 'filter_____') { // avoid collisions with the variables in this function
				//--
				if(((string)trim((string)$filter_____key) != '') AND (SmartFrameworkSecurity::ValidateVariableName($filter_____key))) {
					//--
					if(is_array($filter_____val)) { // array
						//--
						if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
							self::DebugRequestLog('#EXTRACT-FILTER-REQUEST-VAR-ARRAY:'."\n".$filter_____key.'='.print_r($filter_____val,1)."\n");
						} //end if
						SmartFrameworkRegistry::setRequestVar(
							(string) $filter_____key,
							(array) SmartFrameworkSecurity::FilterGetPostCookieVars($filter_____val)
						) OR @trigger_error(__CLASS__.'::'.__FUNCTION__.'() :: '.'Failed to register an array request variable: '.$filter_____key.' @ '.$filter_____info, E_USER_WARNING);
						//--
					} else { // string
						//--
						if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
							self::DebugRequestLog('#EXTRACT-FILTER-REQUEST-VAR-STRING:'."\n".$filter_____key.'='.$filter_____val."\n");
						} //end if
						SmartFrameworkRegistry::setRequestVar(
							(string) $filter_____key,
							(string) SmartFrameworkSecurity::FilterGetPostCookieVars($filter_____val)
						) OR @trigger_error(__CLASS__.'::'.__FUNCTION__.'() :: '.'Failed to register a string request variable: '.$filter_____key.' @ '.$filter_____info, E_USER_WARNING);
						//--
					} //end if else
					//--
				} //end if
				//--
			} //end if
			//--
		} //end foreach
		//--
	} //end if
	//--

	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		self::DebugRequestLog('########## END REQUEST FILTER ##########'."\n\n");
	} //end if
	//--

} //END FUNCTION
//======================================================================


//======================================================================
// This will run before loading the Smart.Framework and must not depend on it's classes
public static function Extract_Filtered_Cookie_Vars($filter_____arr) {

	// FILTER INPUT COOKIES VARIABLES v.180218 (with collision fix and private space check)

	//--
	$filter_____info = 'COOKIES';
	//--

	//-- check if can run
	if(self::$RequestProcessed !== false) {
		@trigger_error(__CLASS__.'::'.__FUNCTION__.'() :: '.'Cannot Register Cookie Vars, Registry is already locked !', E_USER_WARNING);
		return; // avoid run after it was already processed
	} //end if
	//--

	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		self::DebugRequestLog('######################### FILTER COOKIES:'."\n".date('Y-m-d H:i:s O')."\n".$_SERVER['REQUEST_URI']."\n\n".'##### RAW COOKIE VARS:'."\n".'['.$filter_____info.']'."\n".print_r($filter_____arr, 1)."\n");
	} //end if
	//--

	//-- process
	if(is_array($filter_____arr)) {
		//--
		$num = 0;
		//--
		foreach($filter_____arr as $filter_____key => $filter_____val) {
			//--
			$num++;
			//--
			$filter_____key = (string) trim((string)$filter_____key); // force string + trim (for cookies use no validate var name ...)
			//--
			if(substr($filter_____key, 0, 11) != 'filter_____') { // avoid collisions with the variables in this function
				//--
				if((string)$filter_____key != '') {
					//--
					if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
						self::DebugRequestLog('#EXTRACT-FILTER-COOKIE-VAR:'."\n".$filter_____key.'='.$filter_____val."\n");
					} //end if
					SmartFrameworkRegistry::setCookieVar(
						(string) $filter_____key,
						(string) SmartFrameworkSecurity::FilterGetPostCookieVars($filter_____val)
					) OR @trigger_error(__CLASS__.'::'.__FUNCTION__.'() :: '.'Failed to register a cookie variable: '.$filter_____key.' @ '.$filter_____info, E_USER_WARNING);
					//--
				} //end if
				//--
			} //end if
			//--
			if($num >= 1024) {
				@trigger_error(__CLASS__.'::'.__FUNCTION__.'() :: '.'Too many cookie variables detected. Stoped to register at: #'.$num, E_USER_WARNING);
				break;
			} //end if
			//--
		} //end foreach
		//--
	} //end if
	//--

	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		self::DebugRequestLog('########## END COOKIES FILTER ##########'."\n\n");
	} //end if
	//--

} //END FUNCTION
//======================================================================


//======================================================================
// This will run before loading the Smart.Framework and must not depend on it's classes
public static function High_Load_Monitor() {
	//--
	if(is_array(self::$HighLoadMonitorStats)) {
		return (array) self::$HighLoadMonitorStats; // avoid re-run and serve from cache
	} //end if
	//--
	$tmp_sysload_avg = array();
	//--
	if(defined('SMART_FRAMEWORK_NETSERVER_MAXLOAD')) {
		$tmp_max_load = (int) SMART_FRAMEWORK_NETSERVER_MAXLOAD;
	} else {
		$tmp_max_load = 0;
	} //end if
	if($tmp_max_load > 0) { // run only if set to a value > 0
		if(function_exists('sys_getloadavg')) {
			$tmp_sysload_avg = (array) @sys_getloadavg();
			$tmp_sysload_avg[0] = (float) $tmp_sysload_avg[0];
			if($tmp_sysload_avg[0] > $tmp_max_load) { // protect against system overload over max
				if(!headers_sent()) {
					http_response_code(503);
				} else {
					Smart::log_warning('#SMART-FRAMEWORK-HIGH-LOAD-PROTECT#'."\n".'SmartFramework // Web :: System Overload Protection: The System is Too Busy ... Try Again Later. The Load Averages reached the maximum allowed value by current settings ... ['.$tmp_sysload_avg[0].' of '.$tmp_max_load.']');
				} //end if else
				die(SmartComponents::http_message_503_serviceunavailable('The Service is Too busy, try again later ...', SmartComponents::operation_warn('<b>SmartFramework // Web :: System Overload Protection</b><br>The Load Averages reached the maximum allowed value by current settings ...', '100%')));
				return array();
			} //end if
		} //end if
	} //end if
	//--
	self::$HighLoadMonitorStats = (array) $tmp_sysload_avg;
	//--
	return (array) self::$HighLoadMonitorStats;
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// Avoid run this function before Smart.Framework was loaded, it depends on it
public static function Create_Required_Dirs() {
	//--
	if(!defined('SMART_FRAMEWORK_VERSION')) {
		die('Smart Runtime // Create Required Dirs :: Requires SmartFramework to be loaded ...');
		return;
	} //end if
	//--
	if(self::$RequiredDirsCreated !== false) {
		return; // avoid run after it was used by runtime
	} //end if
	self::$RequiredDirsCreated = true;
	//--
	clearstatcache(true); // do a full clear stat cache at the begining
	//-- tmp dir
	$dir = 'tmp/';
	if(!SmartFileSystem::is_type_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
		SmartFileSystem::write($dir.'.htaccess', trim((string)SMART_FRAMEWORK_HTACCESS_NOINDEXING)."\n".trim((string)SMART_FRAMEWORK_HTACCESS_NOEXECUTION)."\n".trim((string)SMART_FRAMEWORK_HTACCESS_FORBIDDEN)."\n");
	} else { // manage debug cleanup
		if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
			if(SmartFileSystem::is_type_file('tmp/SMART-FRAMEWORK__DEBUG-ON')) {
				if(SmartFileSystem::is_type_dir('tmp/logs/idx/')) {
					SmartFileSystem::dir_delete('tmp/logs/idx/', true);
				} //end if
				if(SmartFileSystem::is_type_dir('tmp/logs/adm/')) {
					SmartFileSystem::dir_delete('tmp/logs/adm/', true);
				} //end if
				SmartFileSystem::delete('tmp/SMART-FRAMEWORK__DEBUG-ON');
			} //end if
		} else {
			SmartFileSystem::write_if_not_exists('tmp/SMART-FRAMEWORK__DEBUG-ON', 'DEBUG:ON');
		} //end if else
	} // end if
	if(!SmartFileSystem::have_access_write($dir)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !',
			'App Init ERROR :: (Temporary Folder is Not Writable)' // this must be also as message !!!
		);
		die();
		return;
	} //end if
	if(!SmartFileSystem::is_type_file($dir.'.htaccess')) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'The .htaccess file is missing on FileSystem #TMP: '.$dir.'.htaccess',
			'App Init ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	//-- tmp cache dir
	$dir = 'tmp/cache/';
	if(!SmartFileSystem::is_type_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!SmartFileSystem::have_access_write($dir)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !',
			'App Init ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	//-- tmp logs dir
	$dir = 'tmp/logs/';
	if(!SmartFileSystem::is_type_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!SmartFileSystem::have_access_write($dir)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !',
			'App Init ERROR :: (Error Log Folder is Not Writable)' // this must be also as message !!!
		);
		die();
		return;
	} //end if
	//-- tmp logs/admin dir
	$dir = 'tmp/logs/adm/';
	if(!SmartFileSystem::is_type_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!SmartFileSystem::have_access_write($dir)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !',
			'App Init ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	//-- tmp logs/idx dir
	$dir = 'tmp/logs/idx/';
	if(!SmartFileSystem::is_type_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!SmartFileSystem::have_access_write($dir)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !',
			'App Init ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	//-- tmp sessions dir
	$dir = 'tmp/sessions/';
	if(!SmartFileSystem::is_type_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!SmartFileSystem::have_access_write($dir)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !',
			'App Init ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	//-- wpub dir
	$dir = 'wpub/'; // {{{SYNC-WPUB-DIR}}}
	$ctrlfile = $dir.'#wpub';
	$htfile = $dir.'.htaccess';
	$robotsfile = $dir.'robots.txt';
	if(!SmartFileSystem::is_type_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
		SmartFileSystem::write($robotsfile, 'User-agent: *'."\n".'Disallow: *'); // avoid robots to index it
		SmartFileSystem::write($ctrlfile, 'FileName: #wpub (#WEB-PUBLIC)'."\n".'Created by: App-Runtime'."\n".date('Y-m-d H:i:s O'));
		SmartFileSystem::write($htfile, trim((string)SMART_FRAMEWORK_HTACCESS_NOEXECUTION)."\n"); // trim((string)SMART_FRAMEWORK_HTACCESS_NOINDEXING)."\n".
	} // end if
	if(!SmartFileSystem::have_access_write($dir)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: #WEB-PUBLIC Folder: \''.$dir.'\' is NOT writable !',
			'App Init ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	if(!SmartFileSystem::is_type_file($ctrlfile)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'Cannot Connect to FileSystem #WEB-PUBLIC: '.$ctrlfile,
			'App Init ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	if(!SmartFileSystem::is_type_file($htfile)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'The .htaccess file is missing on FileSystem #WEB-PUBLIC: '.$htfile,
			'App Init ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	//-- wpub/webapps-content
	$dir = 'wpub/webapps-content/'; // {{{SYNC-WEBAPPS-DIR}}}
	if(!SmartFileSystem::is_type_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!SmartFileSystem::have_access_write($dir)) {
		Smart::raise_error(
			'#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !',
			'App Init ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// Avoid run this function before Smart.Framework was loaded, it depends on it
public static function Redirection_Monitor() {
	//--
	if(!defined('SMART_FRAMEWORK_VERSION')) {
		die('Smart Runtime // Redirection Monitor :: Requires SmartFramework to be loaded ...');
		return;
	} //end if
	//--
	if(self::$RedirectionMonitorStarted !== false) {
		return; // avoid run after it was used by runtime
	} //end if
	self::$RedirectionMonitorStarted = true;
	//--
	$url_redirect = '';
	//--
	$the_current_url = SmartUtils::get_server_current_url();
	$the_current_script = SmartUtils::get_server_current_script();
	//--
	if((SMART_SOFTWARE_FRONTEND_ENABLED === false) AND (SMART_SOFTWARE_BACKEND_ENABLED === false)) { // both frontend and backend are disabled
		die('FATAL ERROR: The FRONTEND but also the BACKEND of this application are DISABLED ! ...');
		return;
	} //end if
	if((SMART_SOFTWARE_FRONTEND_ENABLED === false) AND ((string)$the_current_script == 'index.php')) {
		$url_redirect = $the_current_url.'admin.php';
	} //end if
	if((SMART_SOFTWARE_BACKEND_ENABLED === false) AND ((string)$the_current_script == 'admin.php')) {
		$url_redirect = $the_current_url.'index.php';
	//	$url_redirect = $the_current_url; // reset index.php part of URL
	} //end if
	//--
	if(((string)$url_redirect == '') AND (isset($_SERVER['PATH_INFO']))) {
		//--
		if((string)$_SERVER['PATH_INFO'] != '') {
			//--
			if((string)$the_current_script == 'index.php') {
				if(self::PathInfo_Enabled() === true) {
					if((string)$_SERVER['PATH_INFO'] != '/') { // avoid common mistake to use just a / after script.php
						return;
					} //end if
				} //end if
			//	$the_current_script = ''; // reset index.php part of URL
			} elseif((string)$the_current_script == 'admin.php') {
				if(self::PathInfo_Enabled() === true) {
					if((string)$_SERVER['PATH_INFO'] != '/') { // avoid common mistake to use just a / after script.php
						return;
					} //end if
				} //end if
			} //end if
			//--
			$url_redirect = (string) $the_current_url.$the_current_script.'?'.$_SERVER['PATH_INFO'];
			//--
		} //end if
		//--
	} //end if
	//--
	if((string)$url_redirect != '') {
		//--
		$gopage = '<!DOCTYPE html>
		<!-- template :: RUNTIME REDIRECTION / PATH SUFFIX -->
		<html>
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<meta http-equiv="refresh" content="3;URL='.Smart::escape_html($url_redirect).'">
				<title>301 Moved Permanently</title>
			</head>
			<body>
				<h1>301 Moved Permanently</h1>
				<h2>Redirecting to a valid URL ... wait ...</h2><br>
				<script type="text/javascript">setTimeout("self.location=\''.Smart::escape_js($url_redirect).'\'",1500);</script>
			</body>
		</html>';
		//--
		if(!headers_sent()) {
			http_response_code(301); // permanent redirect
			header('Location: '.$url_redirect); // force redirect
		} //end if
		die((string)$gopage);
		return;
	} //end if
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// Avoid run this function before Smart.Framework was loaded, it depends on it
public static function SetVisitorEntropyIDCookie() {
	//--
	if(!defined('SMART_FRAMEWORK_VERSION')) {
		die('Smart Runtime // Set Visitor Entropy ID Cookie :: Requires SmartFramework to be loaded ...');
		return;
	} //end if
	//--
	if(defined('SMART_APP_VISITOR_COOKIE')) {
		die('SetVisitorEntropyIDCookie :: SMART_APP_VISITOR_COOKIE must not be re-defined ...');
		return;
	} //end if
	//--
	$cookie = '';
	//-- {{{SYNC-SMART-UNIQUE-COOKIE}}}
	if((defined('SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME')) AND (!defined('SMART_FRAMEWORK_UNIQUE_ID_COOKIE_SKIP'))) {
		if((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME != '') {
			if(SmartFrameworkSecurity::ValidateVariableName(strtolower((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME))) {
				//--
				$cookie = (string) trim(strtolower(SmartFrameworkSecurity::FilterUnsafeString((string)$_COOKIE[(string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME])));
				if(((string)$cookie == '') OR (strlen((string)$cookie) != 40) OR (!preg_match('/^[a-f0-9]+$/', (string)$cookie))) {
					$entropy = (string) sha1((string)Smart::unique_entropy('uuid-cookie')); // generate a random unique key ; cookie was not yet set or is invalid
					if((defined('SMART_FRAMEWORK_UNIQUE_ID_COOKIE_DOMAIN')) AND ((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_DOMAIN != '')) {
						@setcookie((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME, (string)$entropy, 0, '/', (string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_DOMAIN); // set it using domain
					} else {
						@setcookie((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME, (string)$entropy, 0, '/'); // set it
					} //end if else
					$cookie = (string) $entropy;
				} //end if
				//--
			} //end if
		} //end if
	} //end if
	//-- #end# sync
	define('SMART_APP_VISITOR_COOKIE', (string)$cookie); // empty or cookie ID
	//--
} //END FUNCTION
//======================================================================


#####


//======================================================================
public static function DebugRequestLog($y_message) {
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
		return;
	} //end if
	//--
	if(SMART_FRAMEWORK_ADMIN_AREA === true) {
		$the_dir = 'tmp/logs/adm/';
		$the_log = $the_dir.date('Y-m-d@H').'-debug-requests.log';
	} else {
		$the_dir = 'tmp/logs/idx/';
		$the_log = $the_dir.date('Y-m-d@H').'-debug-requests.log';
	} //end if else
	//--
	if(is_dir((string)$the_dir)) { // here must be is_dir() and file_put_contents() as the smart framework libs are not yet initialized in this phase ...
		@file_put_contents((string)$the_log, $y_message."\n", FILE_APPEND | LOCK_EX); // init
	} //end if
	//--
} //END FUNCTION
//======================================================================


} //END CLASS


//==================================================================================
//================================================================================== CLASS END
//==================================================================================





//#########################
//==
if(defined('SMART_FRAMEWORK_APP_RUNTIME')) {
	die('SmartFramework / App-Runtime already loaded ...');
} //end if
//==
define('SMART_FRAMEWORK_APP_RUNTIME', 'SET');
//==
//#########################


//end of php code
?>