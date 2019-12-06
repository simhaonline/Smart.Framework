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


/**
 * Class: SmartPersistentCache (Default)
 * The backends used for Persistent Cache must be very fast, must support large keys and must supply key expiration by time.
 * If the key expiration is not supported natively, then this functionality must be custom implemented to delete expired keys.
 * The Persistent Cache supports many adapters that can be enabled via config.
 *
 * NOTICE: The Persistent Cache will share the keys between both areas (INDEX and ADMIN) ; It is programmer's choice and work to ensure realm separation for keys if required so (Ex: INDEX may use separate realms than ADMIN)
 * @hints The Persistent Cache adapter can be set in etc/init.php as SMART_FRAMEWORK_PERSISTENT_CACHE_HANDLER ; By default there are 3 options for the Persistent Cache Adapter in Smart.Framework ; 1st is Blackhole (inactive), the 2nd is DBA and 3rd is Redis ; you can also use your own custom adapter for the persistent cache that you must develop by extending the SmartAbstractPersistentCache abstract class
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.20191206
 * @package 	Application:Caching
 *
 */
final class SmartPersistentCache extends SmartAbstractPersistentCache {

	// ::

	// Provides support for the Persistent Cache when not using any adapter.
	// This is provided just for compatibility.
	// When this class will be used instead of other persistent cache options it will function as a blackhole, meaning no Persistent Cache will be emulated, thus all variables set through this class will simply vanish ... in this blackhole :-)

	public static function getVersionInfo() {
		//--
		return (string) 'BLACKHOLE: FAKE, EMULATED Persistent Cache ; THIS HAVE NO STORAGE ATTACHED ; Provides just compatibility support for the Persistent Cache when not using any real adapter to ensure the code requiring the class `'.__CLASS__.'` is functional ...';
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>