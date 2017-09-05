<?php
// [LIB - SmartFramework / Redis based Persistent Cache]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.5')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

define('SMART_FRAMEWORK__INFO__PERSISTENT_CACHE_BACKEND', 'Redis: Memory based');

/**
 * Class: SmartPersistentCache (Redis based Persistent Cache adapter) - provides a persistent Cache (in-Redis-Memory), that can be shared and/or reused between multiple PHP executions.
 * If Redis is not available it will be replaced by the Blackhole Persistent Cache adapter that will provide the compatibility adapter for the case there is no real Persistent Cache available.
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
 * @depends 	-
 * @version 	v.170307
 * @package 	Caching
 *
 */
final class SmartPersistentCache extends SmartAbstractPersistentCache {

	// ::

	private static $redis = null; 		// Redis Object
	private static $is_active = 0;		// Cache if Active (init to zero; then on 1st check set as TRUE or FALSE)


	/**
	 * Check if the persistent Cache is Active
	 *
	 * @return BOOLEAN	TRUE if is Active or FALSE if not
	 */
	public static function isActive() {
		//--
		global $configs;
		//--
		if((self::$is_active === true) OR (self::$is_active === false)) {
			return (bool) self::$is_active;
		} //end if
		//--
		if(is_array($configs['redis'])) {
			self::$is_active = true;
		} else {
			self::$is_active = false;
		} //end if else
		//--
		return (bool) self::$is_active;
		//--
	} //END FUNCTION


	/**
	 * Check if the persistent Cache is Memory Based
	 * This function must ALWAYS be used in conjunction with isActive() as it will return TRUE just if the backend is a Memory Based one and will not check if Backed is Active or not ...
	 *
	 * @return BOOLEAN	TRUE if is Memory Based (Ex: Redis / Memcache / ...) or FALSE if not (Ex: File Cache)
	 */
	public static function isMemoryBased() {
		//--
		return true; // Redis is a memory based cache backend, so it is TRUE
		//--
	} //END FUNCTION


	/**
	 * Check if a Key exists in the persistent Cache
	 *
	 * @param STRING	$y_realm	The Cache Realm
	 * @param STRING	$y_key		The Cache Key
	 *
	 * @return BOOLEAN	TRUE if Key Exists or FALSE if not
	 */
	public static function keyExists($y_realm, $y_key) {
		//--
		if(!self::isActive()) {
			return false;
		} //end if
		//--
		if(!self::validateRealm((string)$y_realm)) {
			return false;
		} //end if
		if(!self::validateKey((string)$y_key)) {
			return false;
		} //end if
		//--
		self::initCacheManager();
		//--
		if((string)$y_realm == '') {
			return (bool) self::$redis->exists((string)$y_key);
		} else {
			return (bool) self::$redis->exists((string)$y_realm.':'.$y_key);
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Get a Key from the persistent Cache
	 *
	 * By default only numbers and strings can be stored as flat values in Redis.
	 * To retrieve complex variables like Arrays, use SmartPersistentCache::varUncompress() after using this function.
	 *
	 * @param STRING	$y_realm	The Cache Realm
	 * @param STRING	$y_key		The Cache Key
	 *
	 * @return MIXED	The value of the stored key or NULL
	 */
	public static function getKey($y_realm, $y_key) {
		//--
		if(!self::isActive()) {
			return null;
		} //end if
		//--
		if(!self::validateRealm((string)$y_realm)) {
			Smart::log_warning('Persistent Cache / Invalid Realm: '.$y_realm);
			return null;
		} //end if
		if(!self::validateKey((string)$y_key)) {
			Smart::log_warning('Persistent Cache / Invalid Key: '.$y_key);
			return null;
		} //end if
		//--
		self::initCacheManager();
		//--
		if((string)$y_realm == '') {
			return self::$redis->get((string)$y_key);
		} else {
			return self::$redis->get((string)$y_realm.':'.$y_key);
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Set a Key into the persistent Cache
	 *
	 * By default only numbers and strings can be stored as flat values in Redis.
	 * To store complex variables like Arrays, use SmartPersistentCache::varCompress() before using this function.
	 *
	 * @param STRING 	$y_realm		The Cache Realm
	 * @param STRING 	$y_key			The Cache Key
	 * @param MIXED 	$y_value		The value to be stored
	 * @param INTEGER+ 	$y_expiration	Key Expiration in seconds (zero if key does not expire)
	 *
	 * @return BOOLEAN	Returns True if the key was set or false if not
	 */
	public static function setKey($y_realm, $y_key, $y_value, $y_expiration=0) {
		//--
		if(!self::isActive()) {
			return false;
		} //end if
		//--
		if(!self::validateRealm((string)$y_realm)) {
			Smart::log_warning('Persistent Cache / Invalid Realm: '.$y_realm);
			return false;
		} //end if
		if(!self::validateKey((string)$y_key)) {
			Smart::log_warning('Persistent Cache / Invalid Key: '.$y_key);
			return false;
		} //end if
		//--
		self::initCacheManager();
		//--
		$y_value = (string) SmartUnicode::fix_charset((string)$y_value); // fix
		$y_expiration = Smart::format_number_int($y_expiration, '+');
		//--
		$resexp = 1;
		if((string)$y_realm == '') {
			$result = self::$redis->set((string)$y_key, (string)$y_value);
			if($y_expiration > 0) {
				$resexp = self::$redis->expire((string)$y_key, (int)$y_expiration);
			} //end if
		} else {
			$result = self::$redis->set((string)$y_realm.':'.$y_key, (string)$y_value);
			if($y_expiration > 0) {
				$resexp = self::$redis->expire((string)$y_realm.':'.$y_key, (int)$y_expiration);
			} //end if
		} //end if else
		//--
		if((strtoupper(trim((string)$result)) == 'OK') AND ($resexp == 1)) {
			return true;
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Unset a Key into the persistent Cache
	 *
	 * @param STRING 	$y_realm	The Cache Realm
	 * @param STRING 	$y_key		The Cache Key ; Use * for All Keys in that Realm
	 *
	 * @return BOOLEAN	Returns True if the key(s) was/were unset or false if not
	 */
	public static function unsetKey($y_realm, $y_key) {
		//--
		if(!self::isActive()) {
			return false;
		} //end if
		//--
		if(!self::validateRealm((string)$y_realm)) {
			Smart::log_warning('Persistent Cache / Invalid Realm: '.$y_realm);
			return false;
		} //end if
		if((string)$y_key != '*') {
			if(!self::validateKey((string)$y_key)) {
				Smart::log_warning('Persistent Cache / Invalid Key: '.$y_key);
				return false;
			} //end if
		} //end if
		//--
		self::initCacheManager();
		//--
		if((string)$y_realm == '') {
			return (bool) self::$redis->del((string)$y_key);
		} else {
			if((string)$y_key != '*') {
				return (bool) self::$redis->del((string)$y_realm.':'.$y_key);
			} else {
				$rarr = (array) self::$redis->keys((string)$y_realm.':*');
				$err = 0;
				if(Smart::array_size($rarr) > 0) {
					foreach($rarr as $key => $rark) {
						if((string)$rark != '') {
							$del = self::$redis->del((string)$rark);
							if($del <= 0) {
								$err++;
							} //end if
						} //end if
					} //end foreach
				} //end if
				if($err > 0) {
					return false;
				} else {
					return true;
				} //end if else
			} //end if
		} //end if else
		//--
	} //END FUNCTION


	private static function initCacheManager() {
		//--
		if((is_object(self::$redis)) AND (self::$redis instanceof SmartRedisDb)) {
			//--
			// OK, already connected ...
			//--
		} else {
			//--
			if((defined('SMART_SOFTWARE_MEMDB_FATAL_ERR')) AND (SMART_SOFTWARE_MEMDB_FATAL_ERR === true)) {
				$ignore_conn_errs = false;
			} else {
				$ignore_conn_errs = true; // default
			} //end if
			//--
			$redis_cfg = (array) Smart::get_from_config('redis');
			//--
			self::$redis = new SmartRedisDb(
				(string) $redis_cfg['server-host'],
				(string) $redis_cfg['server-port'],
				(string) $redis_cfg['dbnum'],
				(string) $redis_cfg['password'],
				(string) $redis_cfg['timeout'],
				(string) $redis_cfg['slowtime'],
				'SmartPersistentCache',
				(bool) $ignore_conn_errs
			);
			//--
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>