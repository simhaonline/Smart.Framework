<?php
// [LIB - Smart.Framework / Blackhole (X-None) Persistent Cache]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

define('SMART_FRAMEWORK__INFO__PERSISTENT_CACHE_BACKEND', 'Blackhole: None');

/**
 * Class: SmartPersistentCache (Default)
 * The backends used for Persistent Cache must be very fast, must support large keys and must supply key expiration by time.
 * If the key expiration is not supported natively, then a custom function must be created to delete expired keys.
 * The Persistent Cache supports many adapters that can be enabled via config.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.20191110
 * @package 	Application:Caching
 *
 */
final class SmartPersistentCache extends SmartAbstractPersistentCache {

	// ::

	// Provides support for the Persistent Cache when not using any adapter.
	// This is provided just for compatibility.
	// When this class will be used instead of other persistent cache options it will function as a blackhole, meaning no Persistent Cache will be emulated, thus all variables set through this class will simply vanish ... in this blackhole :-)


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>