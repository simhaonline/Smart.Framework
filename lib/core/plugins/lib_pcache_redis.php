<?php
// [LIB - Smart.Framework / Plugins / Redis Persistent Cache]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Redis Persistent Cache
// DEPENDS:
//	* Smart::
//	* SmartRedisDb::
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Provides a persistent Cache (in-Redis-Memory), that can be shared and/or reused between multiple PHP executions.
 * Requires Redis to be set-up in config properly.
 *
 * THIS CLASS IS FOR PRIVATE USE ONLY (used as a backend for for SmartPersistentCache)
 * @access 		private
 * @internal
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	Smart, SmartRedisDb
 * @version 	v.20191209
 * @package 	Plugins:Database:Dba
 *
 */
class SmartRedisPersistentCache extends SmartAbstractPersistentCache {

	// ::

	// !!! THIS CLASS MUST NOT BE MARKED AS FINAL to allow the class SmartPersistentCache@DBA to be extended from this !!!
	// But this class have all PUBLIC Methods marked as FINAL to avoid being rewritten ...

	private static $redis 		= null; 	// Redis Object ; by default is null
	private static $is_active 	= null;		// Cache Active State ; by default is null ; on 1st check must set to TRUE or FALSE


	final public static function getVersionInfo() {
		//--
		return (string) 'Redis: Memory based, Persistent Cache';
		//--
	} //END FUNCTION


	final public static function isActive() {
		//--
		if(self::$is_active !== null) {
			return (bool) self::$is_active;
		} //end if
		//--
		$redis_cfg = (array) Smart::get_from_config('redis');
		//--
		if(Smart::array_size($redis_cfg) > 0) {
			self::$is_active = true;
		} else {
			self::$is_active = false;
		} //end if else
		//--
		return (bool) self::$is_active;
		//--
	} //END FUNCTION


	final public static function isMemoryBased() {
		//--
		return true; // Redis is a memory based cache backend, so it is TRUE
		//--
	} //END FUNCTION


	final public static function isFileSystemBased() {
		//--
		return false; // Redis is not a FileSystem based cache backend, so it is FALSE
		//--
	} //END FUNCTION


	final public static function isDbBased() {
		//--
		return false; // Redis is not quite a Database based cache backend, so it is FALSE
		//--
	} //END FUNCTION


	final public static function clearData() {
		//--
		if(!self::isActive()) {
			return false;
		} //end if
		//--
		if(!self::initCacheManager()) {
			Smart::log_warning(__METHOD__.' # Invalid Redis Instance');
			return false;
		} //end if
		//--
		return (bool) self::$redis->flushdb();
		//--
	} //END FUNCTION


	final public static function keyExists($y_realm, $y_key) {
		//--
		if(!self::isActive()) {
			return false;
		} //end if
		//--
		if(!self::validateRealm((string)$y_realm)) {
			Smart::log_warning(__METHOD__.' # Invalid Realm: '.$y_realm);
			return false;
		} //end if
		if(!self::validateKey((string)$y_key)) {
			Smart::log_warning(__METHOD__.' # Invalid Key: '.$y_key);
			return false;
		} //end if
		//--
		if(!self::initCacheManager()) {
			Smart::log_warning(__METHOD__.' # Invalid Redis Instance');
			return false;
		} //end if
		//--
		if((string)$y_realm == '') {
			return (bool) self::$redis->exists((string)$y_key);
		} else {
			return (bool) self::$redis->exists((string)$y_realm.':'.$y_key);
		} //end if else
		//--
	} //END FUNCTION


	final public static function getTtl($y_realm, $y_key) {
		//--
		if(!self::isActive()) {
			return -3;
		} //end if
		//--
		if(!self::validateRealm((string)$y_realm)) {
			Smart::log_warning(__METHOD__.' # Invalid Realm: '.$y_realm);
			return -3;
		} //end if
		if(!self::validateKey((string)$y_key)) {
			Smart::log_warning(__METHOD__.' # Invalid Key: '.$y_key);
			return -3;
		} //end if
		//--
		if(!self::initCacheManager()) {
			Smart::log_warning(__METHOD__.' # Invalid Redis Instance');
			return -3;
		} //end if
		//--
		if((string)$y_realm == '') {
			return (int) self::$redis->ttl((string)$y_key);
		} else {
			return (int) self::$redis->ttl((string)$y_realm.':'.$y_key);
		} //end if else
		//--
	} //END FUNCTION


	final public static function getKey($y_realm, $y_key) {
		//--
		if(!self::isActive()) {
			return null;
		} //end if
		//--
		if(!self::validateRealm((string)$y_realm)) {
			Smart::log_warning(__METHOD__.' # Invalid Realm: '.$y_realm);
			return null;
		} //end if
		if(!self::validateKey((string)$y_key)) {
			Smart::log_warning(__METHOD__.' # Invalid Key: '.$y_key);
			return null;
		} //end if
		//--
		if(!self::initCacheManager()) {
			Smart::log_warning(__METHOD__.' # Invalid Redis Instance');
			return null;
		} //end if
		//--
		if((string)$y_realm == '') {
			return self::$redis->get((string)$y_key);
		} else {
			return self::$redis->get((string)$y_realm.':'.$y_key);
		} //end if else
		//--
	} //END FUNCTION


	final public static function setKey($y_realm, $y_key, $y_value, $y_expiration=0) {
		//--
		if(!self::isActive()) {
			return false;
		} //end if
		//--
		if(!self::validateRealm((string)$y_realm)) {
			Smart::log_warning(__METHOD__.' # Invalid Realm: '.$y_realm);
			return false;
		} //end if
		if(!self::validateKey((string)$y_key)) {
			Smart::log_warning(__METHOD__.' # Invalid Key: '.$y_key);
			return false;
		} //end if
		//--
		if(!self::validateValue((string)$y_value)) { // {{{SYNC-PCACHE-MAX-OBJ-SIZE}}}
			Smart::log_warning(__METHOD__.' # Invalid Value: must be not EMPTY or OVERSIZED (max 16MB) ; size='.strlen((string)$y_value));
			return false;
		} //end if
		//--
		if(!self::initCacheManager()) {
			Smart::log_warning(__METHOD__.' # Invalid Redis Instance');
			return false;
		} //end if
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
		if(((string)strtoupper((string)trim((string)$result)) == 'OK') AND ($resexp == 1)) {
			return true;
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION


	final public static function unsetKey($y_realm, $y_key) {
		//--
		if(!self::isActive()) {
			return false;
		} //end if
		//--
		if(!self::validateRealm((string)$y_realm)) {
			Smart::log_warning(__METHOD__.' # Invalid Realm: '.$y_realm);
			return false;
		} //end if
		if((string)$y_key != '*') {
			if(!self::validateKey((string)$y_key)) {
				Smart::log_warning(__METHOD__.' # Invalid Key: '.$y_key);
				return false;
			} //end if
		} //end if
		//--
		if(!self::initCacheManager()) {
			Smart::log_warning(__METHOD__.' # Invalid Redis Instance');
			return false;
		} //end if
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


	//##### PRIVATES


	private static function initCacheManager() {
		//--
		if(!self::isActive()) {
			Smart::log_warning(__METHOD__.' # Redis does not appear to be active in configs');
			return false;
		} //end if
		//--
		if((is_object(self::$redis)) AND (self::$redis instanceof SmartRedisDb)) {
			//--
			// OK, already instantiated ...
			//--
		} else {
			//--
			$is_fatal_err = false; // for a persistent cache do not use fatal errors, just log them
			//--
			self::$redis = new SmartRedisDb(
				(string) get_called_class(), 	// desc (late state binding to get this class or class that extends this)
				(bool)   $is_fatal_err 			// fatal err
			); // use the connection values from configs
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