<?php
// [LIB - Smart.Framework / DBA based Persistent Cache]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: App.Custom.PersistentCacheAdapter.DBA adapter based on DBA - provides a persistent Cache (in-DBA-Files), that can be shared and/or reused between multiple PHP executions.
 * If DBA is not available it will be replaced by the Blackhole Persistent Cache adapter that will provide the compatibility adapter for the case there is no real Persistent Cache available.
 *
 * To use your own custom adapter for the persistent cache in Smart.Framework you have to build it by extending the SmartAbstractPersistentCache abstract class and define it in etc/init.php as SMART_FRAMEWORK_PERSISTENT_CACHE_HANDLER
 *
 * Requires DBA to be set-up in config properly.
 * This cache type is persistent will keep the cached values in DBA between multiple PHP executions.
 * The key names must be carefully choosen to avoid unwanted conflicts with another client instances,
 * as this kind of cache can be shared between multiple execution but also between multiple client instances.
 * This cache will not reset on each request except if the key values are programatically unset,
 * or the key values are already expired in DBA.
 * It is intended for advanced optimizations to provide a persistent cache layer to the App.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	SmartDbaDb, Smart, PHP DBA
 * @version 	v.20191207
 * @package 	Application:Caching
 *
 */
final class SmartPersistentCache extends SmartDbaPersistentCache {

	// ::

} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>