<?php
// [SmartFramework / App Runtime]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.7.2 r.2016.09.27 / smart.framework.v.2.3

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
if(version_compare(phpversion(), '5.4.20') < 0) { // check PHP version, we need at least 5.4.20 to use anonymous functions at runtime (not yet very well tested with PHP 7)
	die('PHP Runtime not supported : '.phpversion().' !'.'<br>PHP versions to run this software are: 5.4 / 5.5 / 5.6 / 7.0 / 7.1 or later');
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
define('SMART_FRAMEWORK_RELEASE_TAGVERSION', 'v.2.3.7.2'); // this is the real release version tag
define('SMART_FRAMEWORK_RELEASE_VERSION', 'r.2016.09.27'); // this is the real release version date
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
if(!defined('SMART_FRAMEWORK_DEBUG_MODE')) {
	define('SMART_FRAMEWORK_DEBUG_MODE', 'no'); // if not explicit defined, do it here to avoid later modifications
} //end if
//--
if((file_exists('____APP_Install_Mode__Enabled')) OR (is_link('____APP_Install_Mode__Enabled'))) {
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
//--
if(!defined('SMART_FRAMEWORK_URL_PARAM_MODALPOPUP')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_URL_PARAM_MODALPOPUP');
} //end if
if(!defined('SMART_FRAMEWORK_URL_PARAM_PRINTABLE')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_URL_PARAM_PRINTABLE');
} //end if
if(!defined('SMART_FRAMEWORK_URL_VALUE_ENABLED')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_URL_VALUE_ENABLED');
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
if((string)$configs['js']['popup-mode'] != 'popup') {
	$configs['js']['popup-mode'] = 'modal'; // default
} //end if else
if((string)$configs['js']['notifications'] != 'dialog') {
	$configs['js']['notifications'] = 'growl'; // default
} //end if
//---------------------------------------

//--------------------------------------- Monitor High Loads and if detected Return 503 Too Busy
SmartFrameworkRuntime::High_Load_Monitor();
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
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
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
 * Class Smart.Framework Registry.
 * This have to be able to run before loading the Smart.Framework and must not depend on it's classes.
 * This class functions may be used also in other libraries of SmartFramework: Core, Plugins and Application Modules.
 *
 * <code>
 * // Usage example:
 * SmartFrameworkRegistry::$somePublicVariableOfThisClass; SmartFrameworkRegistry::some_method_of_this_class();
 * </code>
 *
 * @usage  		static object: Class::$variable - This class provides only STATIC variables and methods
 *
 * @access 		private
 * @internal
 *
 * @depends 	-
 * @version 	v.160827
 * @package 	Application
 *
 */
final class SmartFrameworkRegistry {

	// ::

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
	private static $RequestVars = array(); 	// request registry


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
				@trigger_error('#SMART-FRAMEWORK-REGISTRY-DEBUG-GET-MSG#'."\n".'INVALID DEBUG AREA: '.$area, E_USER_NOTICE);
				return array();
		} //end switch
		//--
	} //END FUNCTION

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
				@trigger_error('#SMART-FRAMEWORK-REGISTRY-DEBUG-SET-MSG#'."\n".'INVALID DEBUG AREA: '.$area."\n".'Message Content: '.print_r($dbgmsg,1), E_USER_NOTICE);
		} //end switch
		//--
	} //END FUNCTION


	public static function lockRequestVar() {
		//--
		return self::$RequestLock = true;
		//--
	} //END FUNCTION


	public static function setRequestVar($key, $value) {
		//--
		if(self::$RequestLock !== false) {
			return false; // request registry is locked
		} //end if
		//--
		self::$RequestVars[(string)$key] = $value;
		//--
		return true; // OK
		//--
	} //END FUNCTION


	public static function getRequestVars() {
		//--
		return (array) self::$RequestVars; // array
		//--
	} //END FUNCTION


	public static function getRequestVar($key) {
		//--
		if((string)$key == '') {
			return null;
		} else {
			return self::$RequestVars[(string)$key]; // mixed
		} //end if else
		//--
	} //END FUNCTION


	public static function issetRequestVar($key) {
		//--
		if((string)$key == '') {
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
 * Class Smart.Framework Security.
 * This have to be able to run before loading the Smart.Framework and must not depend on it's classes.
 * This class functions may be used also in other libraries of SmartFramework: Core, Plugins and Application Modules.
 *
 * <code>
 * // Usage example:
 * SmartFrameworkSecurity::some_method_of_this_class(...);
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.160827
 * @package 	Application
 *
 */
final class SmartFrameworkSecurity {

	// ::


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
 *
 * @version		160827
 *
 */
final class SmartFrameworkRuntime {

	// ::

	private static $RequestProcessed = false; 				// after all request variables are processed this will be set to true to avoid re-process request variables which can be a huge security issue if re-process is called by mistake !
	private static $RequiredDirsCreated = false;			// after creating required dirs this will be set to true to avoid re-run that function again
	private static $RedirectionMonitorStarted = false; 		// after the redirection monitor have been started this will be set to true to avoid re-run it
	private static $HighLoadMonitorStats = null; 			// register the high load monitor caches


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
// This will run before loading the Smart.Framework and must not depend on it's classes
public static function Parse_Semantic_URL() {

	// PARSE SEMANTIC URL VIA GET v.160827
	// it limits the URL to 65535 and vars to 1000

	//-- check if can run
	if(self::$RequestProcessed !== false) {
		return; // avoid run after it was already processed
	} //end if
	//--

	//--
	$semantic_url = (string) $_SERVER['REQUEST_URI'];
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
					$location_arx[$i+1] = rawurldecode($location_arx[$i+1]); // fix
					$location_arx[$i+1] = str_replace(rawurlencode('/'), '/', $location_arx[$i+1]);
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

	// FILTER INPUT VARIABLES v.160122 (with collision fix and private space check)
	// This no more limits the input variables as it is handled via prior checks to PHP.INI: max_input_vars and max_input_nesting_level
	// If any of: GET / POST / COOKIE overflow the max_input_vars and max_input_nesting_level a PHP warning is issued !!
	// The max_input_vars applies separately to each of the input variables, includding array(s) keys
	// The max_input_nesting_level also must be at least 5

	//-- check if can run
	if(self::$RequestProcessed !== false) {
		return; // avoid run after it was already processed
	} //end if
	//--

	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		self::DebugRequestLog('######################### FILTER NEW REQUEST:'."\n".date('Y-m-d H:i:s O')."\n".$_SERVER['REQUEST_URI']."\n\n".'##### RAW REQUEST VARS:'."\n".'['.$filter_____info.']'."\n".print_r($filter_____arr, 1)."\n");
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
				if(SmartFrameworkSecurity::ValidateVariableName($filter_____key)) {
					//--
					if(is_array($filter_____val)) { // array
						//--
						if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
							self::DebugRequestLog('#EXTRACT-FILTER-VAR-ARRAY:'."\n".$filter_____key.'='.print_r($filter_____val,1)."\n");
						} //end if
						SmartFrameworkRegistry::setRequestVar(
							(string) $filter_____key,
							(array) SmartFrameworkSecurity::FilterGetPostCookieVars($filter_____val)
						) or Smart::log_warning('Failed to register an array request variable: '.$filter_____key.' @ '.$filter_____info);
						//--
					} else { // string
						//--
						if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
							self::DebugRequestLog('#EXTRACT-FILTER-VAR-STRING:'."\n".$filter_____key.'='.$filter_____val."\n");
						} //end if
						SmartFrameworkRegistry::setRequestVar(
							(string) $filter_____key,
							(string) SmartFrameworkSecurity::FilterGetPostCookieVars($filter_____val)
						) or Smart::log_warning('Failed to register a string request variable: '.$filter_____key.' @ '.$filter_____info);
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
			$tmp_sysload_avg[0] = (int) $tmp_sysload_avg[0];
			if($tmp_sysload_avg[0] > $tmp_max_load) { // protect against system overload over max
				@http_response_code(503);
				@trigger_error('#SMART-FRAMEWORK-HIGH-LOAD-PROTECT#'."\n".'SmartFramework // Web :: System Overload Protection: The System is Too Busy ... Try Again Later. The Load Averages reached the maximum allowed value by current settings ... ['.$tmp_sysload_avg[0].' of '.$tmp_max_load.']', E_USER_NOTICE);
				die('<h1>503 Service Unavailable - Too busy, try again later</h1><br><b>SmartFramework // Web :: System Overload Protection</b><br>The Load Averages reached the maximum allowed value by current settings ...');
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
	} //end if
	//--
	if(self::$RequiredDirsCreated !== false) {
		return; // avoid run after it was used by runtime
	} //end if
	self::$RequiredDirsCreated = true;
	//--
	@clearstatcache();
	//-- tmp dir
	$dir = 'tmp/';
	if(!is_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
		SmartFileSystem::write($dir.'.htaccess', trim((string)SMART_FRAMEWORK_HTACCESS_NOINDEXING)."\n".trim((string)SMART_FRAMEWORK_HTACCESS_NOEXECUTION)."\n".trim((string)SMART_FRAMEWORK_HTACCESS_FORBIDDEN)."\n");
	} else { // manage debug cleanup
		if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
			if(is_file('tmp/SMART-FRAMEWORK__DEBUG-ON')) {
				if(is_dir('tmp/logs/idx/')) {
					SmartFileSystem::dir_delete('tmp/logs/idx/', true);
				} //end if
				if(is_dir('tmp/logs/adm/')) {
					SmartFileSystem::dir_delete('tmp/logs/adm/', true);
				} //end if
				SmartFileSystem::delete('tmp/SMART-FRAMEWORK__DEBUG-ON');
			} //end if
		} else {
			SmartFileSystem::write_if_not_exists('tmp/SMART-FRAMEWORK__DEBUG-ON', 'DEBUG:ON');
		} //end if else
	} // end if
	if(!is_writable($dir)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !');
		die('General ERROR :: \''.$dir.'\' is NOT writable !');
	} //end if
	if(!is_file($dir.'.htaccess')) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'The .htaccess file is missing on FileSystem #TMP: '.$dir.'.htaccess');
		die('The .htaccess file is missing on FileSystem #TMP ...');
	} //end if
	//-- tmp locks dir
	$dir = 'tmp/locks/';
	if(!is_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!is_writable($dir)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !');
		die('General ERROR :: \''.$dir.'\' is NOT writable !');
	} //end if
	//-- tmp cache dir
	$dir = 'tmp/cache/';
	if(!is_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!is_writable($dir)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !');
		die('General ERROR :: \''.$dir.'\' is NOT writable !');
	} //end if
	//-- tmp logs dir
	$dir = 'tmp/logs/';
	if(!is_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!is_writable($dir)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !');
		die('General ERROR :: \''.$dir.'\' is NOT writable !');
	} //end if
	//-- tmp logs/admin dir
	$dir = 'tmp/logs/adm/';
	if(!is_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!is_writable($dir)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !');
		die('General ERROR :: \''.$dir.'\' is NOT writable !');
	} //end if
	//-- tmp logs/idx dir
	$dir = 'tmp/logs/idx/';
	if(!is_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!is_writable($dir)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !');
		die('General ERROR :: \''.$dir.'\' is NOT writable !');
	} //end if
	//-- tmp sessions dir
	$dir = 'tmp/sessions/';
	if(!is_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!is_writable($dir)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !');
		die('General ERROR :: \''.$dir.'\' is NOT writable !');
	} //end if
	//-- wpub dir
	$dir = 'wpub/'; // {{{SYNC-WPUB-DIR}}}
	$ctrlfile = $dir.'#wpub';
	$htfile = $dir.'.htaccess';
	$robotsfile = $dir.'robots.txt';
	if(!is_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
		SmartFileSystem::write($robotsfile, 'User-agent: *'."\n".'Disallow: *'); // avoid robots to index it
		SmartFileSystem::write($ctrlfile, 'FileName: #wpub (#WEB-PUBLIC)'."\n".'Created by: App-Runtime'."\n".date('Y-m-d H:i:s O'));
		SmartFileSystem::write($htfile, trim((string)SMART_FRAMEWORK_HTACCESS_NOEXECUTION)."\n"); // trim((string)SMART_FRAMEWORK_HTACCESS_NOINDEXING)."\n".
	} // end if
	if(!is_writable($dir)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: #WEB-PUBLIC Folder: \''.$dir.'\' is NOT writable !');
		die('General ERROR :: #WEB-PUBLIC Folder is NOT writable !');
	} //end if
	if(!is_file($ctrlfile)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'Cannot Connect to FileSystem #WEB-PUBLIC: '.$ctrlfile);
		die('General ERROR :: Cannot Connect to FileSystem #WEB-PUBLIC ...');
	} //end if
	if(!is_file($htfile)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'The .htaccess file is missing on FileSystem #WEB-PUBLIC: '.$htfile);
		die('The .htaccess file is missing on FileSystem #WEB-PUBLIC ...');
	} //end if
	//-- wpub/webapps-content
	$dir = 'wpub/webapps-content/'; // {{{SYNC-WEBAPPS-DIR}}}
	if(!is_dir($dir)) {
		SmartFileSystem::dir_create($dir);
		SmartFileSystem::write($dir.'index.html', '');
	} // end if
	if(!is_writable($dir)) {
		Smart::log_warning('#SMART-FRAMEWORK-CREATE-REQUIRED-DIRS#'."\n".'General ERROR :: \''.$dir.'\' is NOT writable !');
		die('General ERROR :: \''.$dir.'\' is NOT writable !');
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
	} //end if
	if((SMART_SOFTWARE_FRONTEND_ENABLED === false) AND ((string)$the_current_script == 'index.php')) {
		$url_redirect = $the_current_url.'admin.php';
	} //end if
	if((SMART_SOFTWARE_BACKEND_ENABLED === false) AND ((string)$the_current_script == 'admin.php')) {
		$url_redirect = $the_current_url.'index.php';
	} //end if
	//--
	if(((string)$url_redirect == '') AND (isset($_SERVER['PATH_INFO']))) {
		//--
		if(strlen($_SERVER['PATH_INFO']) > 0) {
			//--
			if((string)$the_current_script == 'index.php') {
				$the_current_script = '';
			} //end if
			$url_redirect = $the_current_url.$the_current_script.'?'.$_SERVER['PATH_INFO'];
			//--
		} //end if
		//--
	} //end if
	//--
	$gopage = '
		<!DOCTYPE html>
		<!-- template :: RUNTIME REDIRECTION / PATH SUFFIX -->
		<html>
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<meta http-equiv="refresh" content="3;URL='.Smart::escape_html($url_redirect).'">
			</head>
			<body>
				<h1>Redirecting to a valid URL ... wait ...</h1><br>
				<script type="text/javascript">setTimeout("self.location=\''.Smart::escape_js($url_redirect).'\'",1500);</script>
			</body>
		</html>
	';
	//--
	if(strlen($url_redirect) > 0) {
		@header('Location: '.$url_redirect);
		die($gopage);
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
	} //end if
	//--
	if(defined('SMART_APP_VISITOR_COOKIE')) {
		die('SetVisitorEntropyIDCookie :: SMART_APP_VISITOR_COOKIE must not be re-defined ...');
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
	if(is_dir((string)$the_dir)) {
		@file_put_contents((string)$the_log, $y_message."\n", FILE_APPEND | LOCK_EX); // init
	} //end if
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
 * Class Smart.Framework Abstract Middleware
 *
 * It must contain ONLY public functions to avoid late state binding (self:: vs static::)
 *
 * @access 		private
 * @internal
 *
 * @version		160920
 *
 */
abstract class SmartAbstractAppMiddleware {

	// :: ABSTRACT


//=====
public static function Run() {
	// THIS MUST IMPLEMENT THE MIDDLEWARE SERVICE HANDLER
} //END FUNCTION
//=====


//======================================================================
final public static function HeadersNoCache() {
	//--
	if(!headers_sent()) {
		header('Cache-Control: no-cache'); // HTTP 1.1
		header('Pragma: no-cache'); // HTTP 1.0
		header('Expires: '.gmdate('D, d M Y', @strtotime('-1 year')).' 09:05:00 GMT'); // HTTP 1.0
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	} else {
		Smart::log_warning('WARNING: Smart App Runtime :: Could not set No-Cache Headers, Headers Already Sent ...');
	} //end if else
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function HeadersCacheExpire($expiration, $modified=0) {
	//--
	if(!headers_sent()) {
		//--
		$expiration = (int) $expiration; // expire in seconds
		if($expiration < 60) {
			$expiration = 60;
		} //end if
		$expires = (int) time() + $expiration;
		$modified = (int) $modified; // last modification of the contents in seconds
		if($modified <= 0) {
			$modified = (int) time();
		} //end if
		//--
		header('Expires: '.gmdate('D, d M Y H:i:s', (int)$expires).' GMT'); // HTTP 1.0
		header('Pragma: cache'); // HTTP 1.0
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', (int)$modified).' GMT');
		header('Cache-Control: private, max-age='.(int)$expiration); // HTTP 1.1 (private will dissalow proxies to cache the content)
		//--
	} else {
		//--
		Smart::log_warning('WARNING: Smart App Runtime :: Could not set Expire Cache Headers, Headers Already Sent ...');
		//--
	} //end if else
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise400Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(400);
	} else {
		Smart::log_warning('Headers Already Sent before 400 ...');
	} //end if else
	die(SmartComponents::http_message_400_badrequest(Smart::escape_html((string)$y_msg)));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise401Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(401);
	} else {
		Smart::log_warning('Headers Already Sent before 401 ...');
	} //end if else
	die(SmartComponents::http_message_401_unauthorized(Smart::escape_html((string)$y_msg)));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise403Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(403);
	} else {
		Smart::log_warning('Headers Already Sent before 403 ...');
	} //end if else
	die(SmartComponents::http_message_403_forbidden(Smart::escape_html((string)$y_msg)));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise404Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(404);
	} else {
		Smart::log_warning('Headers Already Sent before 404 ...');
	} //end if else
	die(SmartComponents::http_message_404_notfound(Smart::escape_html((string)$y_msg)));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise429Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(429);
	} else {
		Smart::log_warning('Headers Already Sent before 429 ...');
	} //end if else
	die(SmartComponents::http_message_429_toomanyrequests(Smart::escape_html((string)$y_msg)));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise500Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(500);
	} else {
		Smart::log_warning('Headers Already Sent before 500 ...');
	} //end if else
	die(SmartComponents::http_message_500_internalerror(Smart::escape_html((string)$y_msg)));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise502Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(502);
	} else {
		Smart::log_warning('Headers Already Sent before 502 ...');
	} //end if else
	die(SmartComponents::http_message_502_badgateway(Smart::escape_html((string)$y_msg)));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise503Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(503);
	} else {
		Smart::log_warning('Headers Already Sent before 503 ...');
	} //end if else
	die(SmartComponents::http_message_503_serviceunavailable(Smart::escape_html((string)$y_msg)));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise504Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(504);
	} else {
		Smart::log_warning('Headers Already Sent before 504 ...');
	} //end if else
	die(SmartComponents::http_message_504_gatewaytimeout(Smart::escape_html((string)$y_msg)));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// This will handle the file downloads. The file PACKET will be sent to this function.
// The PACKET (containing the File Download URL) is a data packet that have a structure like (see below: PACKET-STRUCTURE).
// All PACKETS are signed with an AccessKey based on a unique key SMART_FRAMEWORK_SECURITY_KEY, so they cant't be guessed or reversed.
// Event in the case that the AccessKey could be guessed, there is a two factor security layer that contains another key: UniqueKey (the unique client key, generated by the IP address and the unique browser signature).
// So the two factor security combination (secret server key: AccessKey based on SMART_FRAMEWORK_SECURITY_KEY / almost unique client key: UniqueKey) will assure enough protection.
// when used, the execution script must die('') after to avoid injections of extra content ...
// the nocache headers must be set before using this
// it returns the downloaded file path on success or empty string on error.
final public static function DownloadsHandler($encrypted_download_pack, $controller_key) {
	//--
	$encrypted_download_pack = (string) $encrypted_download_pack;
	$controller_key = (string) $controller_key;
	//--
	$client_signature = SmartUtils::get_visitor_signature();
	//--
	if((string)SMART_APP_VISITOR_COOKIE == '') {
		Smart::log_info('File Download', 'Failed: 400 / Invalid Visitor Cookie'.' on Client: '.$client_signature);
		self::Raise400Error('ERROR: Invalid Visitor UUID. Cookies must be enabled to enable this feature !');
		return '';
	} //end if
	//--
	$downloaded_file = ''; // init
	//--
	$decoded_download_packet = (string) trim((string)SmartUtils::crypto_decrypt(
		(string)$encrypted_download_pack,
		'SmartFramework//DownloadLink'.SMART_FRAMEWORK_SECURITY_KEY
	));
	//--
	if((string)$decoded_download_packet != '') { // if data is corrupted, decrypt checksum does not match, will return an empty string
		//--
		if(SMART_FRAMEWORK_ADMIN_AREA === true) { // {{{SYNC-DWN-CTRL-PREFIX}}}
			$controller_key = (string) 'AdminArea/'.$controller_key;
		} else {
			$controller_key = (string) 'IndexArea/'.$controller_key;
		} //end if
		//-- {{{SYNC-DOWNLOAD-ENCRYPT-ARR}}}
		$arr_metadata = explode("\n", (string)$decoded_download_packet, 6); // only need first 5 parts
		//print_r($arr_metadata);
		// #PACKET-STRUCTURE# [we will have an array like below, according with the: SmartUtils::create_download_link()]
		// [TimedAccess]\n
		// [FilePath]\n
		// [AccessKey]\n
		// [UniqueKey]\n
		// [SFR.UA]\n
		// #END#
		//--
		$crrtime = (string) trim((string)$arr_metadata[0]);
		$filepath = (string) trim((string)$arr_metadata[1]);
		$access_key = (string) trim((string)$arr_metadata[2]);
		$unique_key = (string) trim((string)$arr_metadata[3]);
		//--
		unset($arr_metadata);
		//--
		$timed_hours = 1; // default expire in 1 hour
		if(defined('SMART_FRAMEWORK_DOWNLOAD_EXPIRE')) {
			if((int)SMART_FRAMEWORK_DOWNLOAD_EXPIRE > 0) {
				if((int)SMART_FRAMEWORK_DOWNLOAD_EXPIRE <= 24) { // max is 24 hours (since download link is bind to unique browser signature + unique cookie ... make non-sense to keep more)
					$timed_hours = (int) SMART_FRAMEWORK_DOWNLOAD_EXPIRE;
				} //end if
			} //end if
		} //end if
		//--
		if((int)$timed_hours > 0) {
			if((int)$crrtime < (int)(time() - (60 * 60 * $timed_hours))) {
				Smart::log_info('File Download', 'Failed: 403 / Download expired at: '.date('Y-m-d H:i:s O', (int)$crrtime).' for: '.$filepath.' on Client: '.$client_signature);
				self::Raise403Error('ERROR: The Access Key for this Download is Expired !');
				return '';
			} //end if
		} //end if
		//--
		if((string)$access_key != (string)sha1('DownloadLink:'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.SMART_APP_VISITOR_COOKIE.':'.$filepath.'^'.$controller_key)) {
			Smart::log_info('File Download', 'Failed: 403 / Invalid Access Key for: '.$filepath.' on Client: '.$client_signature);
			self::Raise403Error('ERROR: Invalid Access Key for this Download !');
			return '';
		} //end if
		//--
		if((string)$unique_key != (string)SmartHashCrypto::sha1('Time='.$crrtime.'#'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.$access_key.'-'.SmartUtils::unique_auth_client_private_key().':'.$filepath.'+'.$controller_key)) {
			Smart::log_info('File Download', 'Failed: 403 / Invalid Client (Unique) Key for: '.$filepath.' on Client: '.$client_signature);
			self::Raise403Error('ERROR: Invalid Client Key to Access this Download !');
			return '';
		} //end if
		//--
		if(SmartFileSysUtils::check_file_or_dir_name($filepath)) {
			//--
			$skip_log = 'no'; // default log
			if(defined('SMART_FRAMEWORK_DOWNLOAD_SKIP_LOG')) {
				$skip_log = 'yes'; // do not log if accessed via admin area and user is authenticated
			} //end if
			//--
			$tmp_file_ext = (string) strtolower(SmartFileSysUtils::get_file_extension_from_path($filepath)); // [OK]
			$tmp_file_name = (string) strtolower(SmartFileSysUtils::get_file_name_from_path($filepath));
			//--
			$tmp_eval = SmartFileSysUtils::mime_eval($tmp_file_name);
			$mime_type = (string) $tmp_eval[0];
			$mime_disp = (string) $tmp_eval[1];
			//-- the path must not start with / but this is tested below
			$tmp_arr_paths = (array) explode('/', $filepath, 2); // only need 1st part for testing
			//-- allow file downloads just from specific folders like wpub/ or wsys/ (this is a very important security fix to dissalow any downloads that are not in the specific folders)
			if((substr((string)$filepath, 0, 1) != '/') AND (strpos((string)SMART_FRAMEWORK_DOWNLOAD_FOLDERS, '<'.trim((string)$tmp_arr_paths[0]).'>') !== false) AND (stripos((string)SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS, '<'.$tmp_file_ext.'>') === false)) {
				//--
				SmartFileSysUtils::raise_error_if_unsafe_path($filepath); // re-test finally
				//--
				@clearstatcache();
				//--
				if(is_file($filepath)) {
					//--
					if(!headers_sent()) {
						//--
						$fp = @fopen($filepath, 'rb');
						$fsize = @filesize($filepath);
						//--
						if((!$fp) || ($fsize <= 0)) {
							//--
							Smart::log_info('File Download', 'Failed: 404 / The requested File is Empty or Not Readable: '.$filepath.' on Client: '.$client_signature);
							self::Raise404Error('WARNING: The requested File is Empty or Not Readable !');
							return '';
							//--
						} //end if
						//-- set max execution time to zero
						ini_set('max_execution_time', 0); // we can expect a long time if file is big, but this will be anyway overriden by the WebServer Timeout Directive
						//--
						// cache headers are presumed to be sent by runtime before of this step
						//--
						header('Content-Type: '.$mime_type);
						header('Content-Disposition: '.$mime_disp);
						header('Content-Length: '.$fsize);
						//--
						@fpassthru($fp); // output without reading all in memory
						//--
						@fclose($fp);
						//--
					} else {
						//--
						Smart::log_info('File Download', 'Failed: 500 / Headers Already Sent: '.$filepath.' on Client: '.$client_signature);
						self::Raise500Error('ERROR: Download Failed, Headers Already Sent !');
						return '';
						//--
					} //end if else
					//--
					if((string)$skip_log != 'yes') {
						//--
						$downloaded_file = (string) $filepath; // return the file name to be logged
						//--
					} //end if
					//--
				} else {
					//--
					Smart::log_info('File Download', 'Failed: 404 / The requested File does not Exists: '.$filepath.' on Client: '.$client_signature);
					self::Raise404Error('WARNING: The requested File for Download does not Exists !');
					return '';
					//--
				} //end if else
			} else {
				//--
				Smart::log_info('File Download', 'Failed: 403 / Access to this File is Denied: '.$filepath.' on Client: '.$client_signature);
				self::Raise403Error('ERROR: Download Access to this File is Denied !');
				return '';
				//--
			} //end if else
			//--
		} else {
			//--
			Smart::log_info('File Download', 'Failed: 400 / Unsafe File Path: '.$filepath.' on Client: '.$client_signature);
			self::Raise400Error('ERROR: Unsafe Download File Path !');
			return '';
			//--
		} //end if else
		//--
	} else {
		//--
		Smart::log_info('File Download', 'Failed: 400 / Invalid Data Packet'.' on Client: '.$client_signature);
		self::Raise400Error('ERROR: Invalid Download Data Packet !');
		return '';
		//--
	} //end if else
	//--
	return (string) $downloaded_file;
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