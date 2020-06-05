<?php
// [LIB - Smart.Framework / Redis based Persistent Cache]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.7.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: App.Custom.PersistentCacheAdapter.Redis adapter based on Redis - provides a persistent Cache (in-Redis-Memory), that can be shared and/or reused between multiple PHP executions.
 * If Redis is not available it will be replaced by the Blackhole Persistent Cache adapter that will provide the compatibility adapter for the case there is no real Persistent Cache available.
 *
 * NOTICE: The Persistent Cache will share the keys between both areas (INDEX and ADMIN) ; It is programmer's choice and work to ensure realm separation for keys if required so (Ex: INDEX may use separate realms than ADMIN)
 * @hints To use your own custom adapter for the persistent cache in Smart.Framework you have to build it by extending the SmartAbstractPersistentCache abstract class and define it in etc/init.php as SMART_FRAMEWORK_PERSISTENT_CACHE_HANDLER
 *
 * Requires Redis to be set-up in config properly.
 * This cache type is persistent will keep the cached values in Redis between multiple PHP executions.
 * The key names must be carefully choosen to avoid unwanted conflicts with another client instances,
 * as this kind of cache can be shared between multiple execution but also between multiple client instances.
 * This cache will not reset on each request except if the key values are programatically unset,
 * or the key values are already expired in Redis.
 * It is intended for advanced optimizations to provide a persistent cache layer to the App.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	SmartRedisPersistentCache, SmartRedisDb, Smart
 * @version 	v.20200121
 * @package 	Application:Caching
 *
 */
final class SmartPersistentCache extends SmartRedisPersistentCache {

	// ::

} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
