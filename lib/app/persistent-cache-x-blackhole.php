<?php
// [LIB - SmartFramework / Blackhole (X-None) Persistent Cache]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.5 r.2018.03.09 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
	define('SMART_FRAMEWORK__INFO__PERSISTENT_CACHE_BACKEND', 'Blackhole: None');
} //end if

/**
 * Class: SmartPersistentCache (Blackhole: None) - provides a Blackhole persistent Cache for the case another Persistent Cache is not available.
 *
 * This is provided just for compatibility.
 * When this class will be used instead of other persistent cache options it will function as a blackhole, meaning no Persistent Cache will be available,
 * thus all variables set to this class will simply vanish ... in this blackhole :-)
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.160215
 * @package 	Caching
 *
 * @ignore
 *
 * @access 		private
 * @internal
 *
 */
final class SmartPersistentCache extends SmartAbstractPersistentCache {

	// ::

} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>