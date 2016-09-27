<?php
// [APP - Bootstrap / SmartFramework]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.7.2 r.2016.09.27 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - App Bootstrap
// DEPENDS: SmartFramework, SmartFrameworkRuntime
//======================================================
// This file can be customized per App ...
// DO NOT MODIFY ! IT IS CUSTOMIZED FOR: Smart.Framework
//======================================================

// [REGEX-SAFE-OK]

//##### WARNING: #####
// Changing the code below is on your own risk and may lead to severe disrupts in the execution of this software !
//####################

//==
if(Smart::array_size($configs['redis']) > 0) {
	require('lib/app/persistent-cache-redis.php'); // load the redis based persistent cache
} else {
	require('lib/app/persistent-cache-x-blackhole.php'); // load the blackhole (x-none) persistent cache which will implement nothing but definitions and is required for compatibility
} //end if
//==
require('lib/app/translations-adapter-yaml.php'); // text translations (YAML based adapter)
//==
if((string)SMART_FRAMEWORK_SESSION_HANDLER === 'redis') {
	if(Smart::array_size($configs['redis']) > 0) {
		require('lib/app/custom-session-redis.php'); // use custom session based on Redis
	} else {
		Smart::raise_error(
			'ERROR: The Custom Session Handler is set for (user) mode Redis but the Redis config is not set ...',
			'ERROR: Invalid Settings for App Session Handler. See the Error Log for more details ...'
		);
		die('');
	} //end if else
} //end if
//==

//==
/**
 * Function AutoLoad Modules (Libs / Models) via Dependency Injection
 *
 * @access 		private
 * @internal
 *
 */
function autoload__SmartFrameworkModClasses($classname) {
	//--
	$classname = (string) $classname;
	//--
	if((strpos($classname, '\\') === false) OR (!preg_match('/^[a-zA-Z0-9_\\\]+$/', $classname))) { // if have no namespace or not valid character set
		return;
	} //end if
	//--
	if((strpos($classname, 'SmartModExtLib\\') === false) AND (strpos($classname, 'SmartModDataModel\\') === false)) { // must start with this namespaces only
		return;
	} //end if
	//--
	$parts = (array) explode('\\', $classname);
	//--
	$max = (int) count($parts) - 1; // the last is the class
	//--
	$dir = 'modules/mod';
	//--
	if((string)$parts[1] != '') {
		//--
		$dir .= strtolower(implode('-', preg_split('/(?=[A-Z])/', (string)$parts[1])));
		//--
		if((string)$parts[0] == 'SmartModExtLib') {
			//--
			$dir .= '/libs/';
			//--
		} elseif((string)$parts[0] == 'SmartModDataModel') {
			//--
			$dir .= '/models/';
			//--
		} else {
			//--
			return; // other namespaces are not managed here
			//--
		} //end if else
		//--
		if((string)$parts[2] != '') {
			for($i=2; $i<$max; $i++) {
				$dir .= $parts[$i].'/';
			} //end for
		} //end if
		//--
	} else {
		//--
		return; // no module detected
		//--
	} //end if
	//--
	$dir = (string) $dir;
	$file = (string) $parts[(int)$max];
	$path = (string) $dir.$file;
	$path = (string) str_replace(array('\\', "\0"), array('', ''), $path); // filter out null byte and backslash
	//--
	if(!preg_match('/^[_a-zA-Z0-9\-\/]+$/', $path)) {
		return; // invalid path characters in file
	} //end if
	//--
	if(!is_file($path.'.php')) {
		return; // file does not exists
	} //end if
	//--
	require_once($path.'.php');
	//--
} //END FUNCTION
//==
spl_autoload_register('autoload__SmartFrameworkModClasses', true, false); // throw / prepend
//==


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

// REQUIRED
define('SMART_SOFTWARE_APP_NAME', 'smart.framework.app'); // software version for DB Validation

/**
 * Class Smart.Framework App.BootStrap
 *
 * @access 		private
 * @internal
 *
 */
final class SmartAppBootstrap implements SmartInterfaceAppBootstrap {

	// ::
	// v.160120

	private static $isRunning 		= false;
	private static $authCompleted 	= false;


	//======================================================================
	// REQUIRED
	public static function Run() {
		//--
		global $configs;
		global $languages;
		//--
		if(self::$isRunning !== false) {
			http_response_code(500);
			die(SmartComponents::http_message_500_internalerror('App Boostrap is already running ...'));
		} //end if
		self::$isRunning = true;
		//--
		require('modules/app/app-custom-bootstrap.inc.php'); // custom boostrap code (this can permanently start session or connect to a DB server or ...)
		//--
	} //END FUNCTION
	//======================================================================


	//======================================================================
	public static function Authenticate($area) {
		//--
		global $configs;
		//--
		if(self::$authCompleted !== false) {
			http_response_code(500);
			die(SmartComponents::http_message_500_internalerror('App Boostrap Auth already loaded ...'));
		} //end if
		self::$authCompleted = true;
		//--
		switch((string)$area) {
			case 'index':
				require('modules/app/app-auth-index.inc.php');
				break;
			case 'admin':
				require('modules/app/app-auth-admin.inc.php');
				break;
			default:
				$msg = 'Invalid Authentication Realm: '.$area;
				Smart::raise_error(
					'App Bootstrap / Authenticate: '.$msg,
					'App Bootstrap / Authenticate: '.$msg // msg to display
				);
				die('Invalid Auth Realm'); // just in case
		} //end switch
		//--
	} //END FUNCTION
	//======================================================================


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartAppInfo - provides some methods for integration between the Smart.Framework App/Modules.
 *
 * <code>
 * // Usage example:
 * SmartAppInfo::some_method_of_this_class(...);
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.160122
 * @package 	Application
 *
 */
final class SmartAppInfo implements SmartInterfaceAppInfo {

	// ::

	private static $cache = array();


	//=====
	/**
	 * Test if Application Template Exists in etc/templates/
	 *
	 * @param 	STRING 	$y_template_name 	:: The template dir name (Ex: for 'etc/templates/something', this parameter would be: 'something'
	 *
	 * @return 	BOOLEAN						:: TRUE if template exists, FALSE if not detected
	 */
	public static function TestIfTemplateExists($y_template_name) {
		//--
		$y_template_name = Smart::safe_filename((string)$y_template_name);
		//--
		$test_cache = (string) self::$cache['TestIfTemplateExists:'.$y_template_name];
		//--
		if((string)$test_cache != '') { // get cached test
			//--
			if((string)$test_cache == 'YES') {
				$exists = true;
			} else {
				$exists = false;
			} //end if
			//--
		} else { // real test
			//--
			if(is_dir('etc/templates/'.$y_template_name.'/')) {
				$exists = true;
				self::$cache['TestIfTemplateExists:'.$y_template_name] = 'YES';
			} else {
				$exists = false;
				self::$cache['TestIfTemplateExists:'.$y_template_name] = 'NO';
			} //end if
			//--
		} //end if else
		//--
		return (bool) $exists;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Test if Application Module Exists in modules/
	 *
	 * @param 	STRING 	$y_module_name 		:: The short module name (Ex: for 'modules/mod-something', this parameter would be: 'mod-something'
	 *
	 * @return 	BOOLEAN						:: TRUE if module exists, FALSE if not detected
	 */
	public static function TestIfModuleExists($y_module_name) {
		//--
		$y_module_name = Smart::safe_filename((string)$y_module_name);
		//--
		$test_cache = (string) self::$cache['TestIfModuleExists:'.$y_module_name];
		//--
		if((string)$test_cache != '') { // get cached test
			//--
			if((string)$test_cache == 'YES') {
				$exists = true;
			} else {
				$exists = false;
			} //end if
			//--
		} else { // real test
			//--
			if(is_dir('modules/'.$y_module_name.'/')) {
				$exists = true;
				self::$cache['TestIfModuleExists:'.$y_module_name] = 'YES';
			} else {
				$exists = false;
				self::$cache['TestIfModuleExists:'.$y_module_name] = 'NO';
			} //end if
			//--
		} //end if else
		//--
		return (bool) $exists;
		//--
	} //END FUNCTION
	//=====


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>