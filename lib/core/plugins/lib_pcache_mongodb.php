<?php
// [LIB - Smart.Framework / Plugins / MongoDB Persistent Cache]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - MongoDB Persistent Cache
// DEPENDS:
//	* Smart::
//	* SmartMongoDb::
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Provides a persistent Cache (DB / big-data), that can be shared and/or reused between multiple PHP executions.
 * Requires MongoDB to be set-up in config properly.
 *
 * THIS CLASS IS FOR PRIVATE USE ONLY (used as a backend for for SmartPersistentCache)
 * @access 		private
 * @internal
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	Smart, SmartMongoDb
 * @version 	v.20191211
 * @package 	Plugins:PersistentCache:MongoDB
 *
 */
class SmartMongoDbPersistentCache extends SmartAbstractPersistentCache {

	// ::

	// !!! THIS CLASS MUST NOT BE MARKED AS FINAL to allow the class SmartPersistentCache@DBA to be extended from this !!!
	// But this class have all PUBLIC Methods marked as FINAL to avoid being rewritten ...

	private static $mongo 		= null; 							// MongoDB Object ; by default is null
	private static $collection 	= 'SmartFrameworkPersistentCache'; 	// The MongoDB Collection
	private static $is_active 	= null;								// Cache Active State ; by default is null ; on 1st check must set to TRUE or FALSE


	final public static function getVersionInfo() {
		//--
		return (string) 'MongoDB: NoSQL / BigData based, Persistent Cache';
		//--
	} //END FUNCTION


	final public static function isActive() {
		//--
		if(self::$is_active !== null) {
			return (bool) self::$is_active;
		} //end if
		//--
		$mongo_cfg = (array) Smart::get_from_config('mongodb');
		//--
		if(Smart::array_size($mongo_cfg) > 0) {
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
		return false; // MongoDB is not a memory based cache backend, so it is FALSE
		//--
	} //END FUNCTION


	final public static function isFileSystemBased() {
		//--
		return false; // MongoDB is not a FileSystem based cache backend, so it is FALSE
		//--
	} //END FUNCTION


	final public static function isDbBased() {
		//--
		return true; // MongoDB is a Database based cache backend, so it is TRUE
		//--
	} //END FUNCTION


	final public static function clearData() {
		//--
		if(!self::isActive()) {
			return false;
		} //end if
		//--
		if(!self::initCacheManager()) {
			Smart::log_warning(__METHOD__.' # Invalid MongoDB Instance');
			return false;
		} //end if
		//--
		try {
			$delete = self::$mongo->delete(
				(string) self::$collection,
				[] // find filter (all)
			);
		} catch(Exception $err) { // don't throw if MongoDB error !
			Smart::log_warning(__METHOD__.' # Delete All Error: '.$err->getMessage());
			return false;
		} //end try catch
		//--
		return true;
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
			Smart::log_warning(__METHOD__.' # Invalid MongoDB Instance');
			return false;
		} //end if
		//--
		$id = (string) SmartHashCrypto::sha512($y_realm.':'.$y_key);
		//--
		$rd = array();
		try {
			$rd = self::$mongo->findone(
				(string) self::$collection,
				[ // find filter (by Unique)
					'id' 		=> (string) $id,
					'key' 		=> (string) $y_key,
					'realm' 	=> (string) $y_realm
				],
				[ // projection
					'id' => 1,
					'key' => 1,
					'realm' => 1,
					'expire' => 1,
					'expire_at' => 1
				]
			);
		} catch(Exception $err) { // don't throw if MongoDB error !
			Smart::log_warning(__METHOD__.' # Read Error: '.$err->getMessage());
			return false;
		} //end try catch
		//--
		$ok = false;
		if(Smart::array_size($rd) > 0) {
			if(array_key_exists('id', (array)$rd)) {
				if(array_key_exists('key', (array)$rd)) {
					if(array_key_exists('realm', (array)$rd)) {
						if(array_key_exists('expire', (array)$rd)) {
							if(array_key_exists('expire_at', (array)$rd)) {
								if(((string)$id === (string)$rd['id']) AND ((string)$y_key === (string)$rd['key']) AND ((string)$y_realm === (string)$rd['realm'])) {
									if((string)$id === (string)SmartHashCrypto::sha512($rd['realm'].':'.$rd['key'])) {
										if(((int)$rd['expire'] <= 0) OR (((int)$rd['expire'] > 0) AND ((int)$rd['expire_at'] > 0) AND ((int)$rd['expire_at'] >= (int)time()))) {
											$ok = true;
										} //end if
									} //end if
								} //end if
							} //end if
						} //end if
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		return (bool) $ok;
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
			Smart::log_warning(__METHOD__.' # Invalid MongoDB Instance');
			return -3;
		} //end if
		//--
		$id = (string) SmartHashCrypto::sha512($y_realm.':'.$y_key);
		//--
		$rd = array();
		try {
			$rd = self::$mongo->findone(
				(string) self::$collection,
				[ // find filter (by Unique)
					'id' 		=> (string) $id,
					'key' 		=> (string) $y_key,
					'realm' 	=> (string) $y_realm
				],
				[ // projection
					'id' => 1,
					'key' => 1,
					'realm' => 1,
					'expire' => 1,
					'expire_at' => 1
				]
			);
		} catch(Exception $err) { // don't throw if MongoDB error !
			Smart::log_warning(__METHOD__.' # Read Error: '.$err->getMessage());
			return -3;
		} //end try catch
		//--
		$ttl = -2; // does not exists
		if(Smart::array_size($rd) > 0) {
			if(array_key_exists('id', (array)$rd)) {
				if(array_key_exists('key', (array)$rd)) {
					if(array_key_exists('realm', (array)$rd)) {
						if(array_key_exists('expire', (array)$rd)) {
							if(array_key_exists('expire_at', (array)$rd)) {
								if(((string)$id === (string)$rd['id']) AND ((string)$y_key === (string)$rd['key']) AND ((string)$y_realm === (string)$rd['realm'])) {
									if((string)$id === (string)SmartHashCrypto::sha512($rd['realm'].':'.$rd['key'])) {
										if(((int)$rd['expire'] <= 0) OR (((int)$rd['expire'] > 0) AND ((int)$rd['expire_at'] > 0) AND ((int)$rd['expire_at'] >= (int)time()))) {
											if((int)$rd['expire'] <= 0) {
												if((int)$rd['expire_at'] <= 0) {
													$ttl = -1; // does not expire
												} else {
													$ttl = -4; // error !!
												} //end if else
											} else {
												$ttl = (int) ((int)$rd['expire_at'] - (int)time()); // {{{SYNC-PCACHE-TTL}}}
												if($ttl < 0) {
													$ttl = 0;
												} //end if
											} //end if else
										} //end if
									} //end if
								} //end if
							} //end if
						} //end if
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		return (int) $ttl;
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
			Smart::log_warning(__METHOD__.' # Invalid MongoDB Instance');
			return null;
		} //end if
		//--
		$id = (string) SmartHashCrypto::sha512($y_realm.':'.$y_key);
		//--
		$rd = array();
		try {
			$rd = self::$mongo->findone(
				(string) self::$collection,
				[ // find filter (by Unique)
					'id' 		=> (string) $id,
					'key' 		=> (string) $y_key,
					'realm' 	=> (string) $y_realm
				]
			);
		} catch(Exception $err) { // don't throw if MongoDB error !
			Smart::log_warning(__METHOD__.' # Read Error: '.$err->getMessage());
			return null;
		} //end try catch
		//--
		$data = null;
		if(Smart::array_size($rd) > 0) {
			if(array_key_exists('id', (array)$rd)) {
				if(array_key_exists('key', (array)$rd)) {
					if(array_key_exists('realm', (array)$rd)) {
						if(array_key_exists('expire', (array)$rd)) {
							if(array_key_exists('expire_at', (array)$rd)) {
								if(((string)$id === (string)$rd['id']) AND ((string)$y_key === (string)$rd['key']) AND ((string)$y_realm === (string)$rd['realm'])) {
									if((string)$id === (string)SmartHashCrypto::sha512($rd['realm'].':'.$rd['key'])) {
										if(((int)$rd['expire'] <= 0) OR (((int)$rd['expire'] > 0) AND ((int)$rd['expire_at'] > 0) AND ((int)$rd['expire_at'] >= (int)time()))) {
											if(array_key_exists('pcache', (array)$rd)) {
												$rd['pcache'] = Smart::unseryalize((string)$rd['pcache']);
												if(is_array($rd['pcache'])) {
													if(array_key_exists('checksum', (array)$rd['pcache'])) {
														if(array_key_exists('data', (array)$rd['pcache'])) {
															if((string)$rd['pcache']['checksum'] === (string)sha1($y_realm.':'.$y_key.':'.$rd['pcache']['data'])) {
																$data = (string) $rd['pcache']['data'];
															} //end if
														} //end if
													} //end if
												} //end if
											} //end if
										} //end if
									} //end if
								} //end if
							} //end if
						} //end if
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		$rd = null; // free mem
		//--
		return $data; // mixed
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
			Smart::log_warning(__METHOD__.' # Invalid MongoDB Instance');
			return false;
		} //end if
		//--
		$y_value = (string) SmartUnicode::fix_charset((string)$y_value); // fix
		$y_expiration = Smart::format_number_int($y_expiration, '+');
		if((int)$y_expiration < 0) {
			$y_expiration = 0; // zero is for not expiring records
		} //end if
		//--
		$now = (int) time();
		//--
		if((int)$y_expiration > 0) {
			$expire = (int) $y_expiration;
			$expiration = (int) ((int)$now + (int)$y_expiration); // {{{SYNC-PCACHE-EXPIRE}}}
		} else {
			$expire = 0;
			$expiration = -1; // does not expire (compatible to Redis)
		} //end if else
		//--
		$id = (string) SmartHashCrypto::sha512($y_realm.':'.$y_key);
		//--
		$upsert = array();
		try {
			$upsert = (array) self::$mongo->upsert(
				(string) self::$collection,
				[ // filter by Unique
					'id' 		=> (string) $id,
					'key' 		=> (string) $y_key,
					'realm' 	=> (string) $y_realm
				],
				'$set', 			// operation
				[ // update array
					'_id' 		=> (string) SmartHashCrypto::sha256($y_realm.':'.$y_key).'-'.SmartHashCrypto::crc32b($y_key.':'.$y_realm), // ensure the same uuid to avoid 2 different uuids are upserted in the same time and generate duplicate error on high concurrency
					'id' 		=> (string) $id,
					'key' 		=> (string) $y_key,
					'realm' 	=> (string) $y_realm,
					//--
					'created' 	=> (string) date('Y-m-d H:i:s O'),
					'modified' 	=> (int)    $now,
					'expire' 	=> (int)    $expire,
					'expire_at' => (int)    $expiration,
					'pcache' 		=> (string) Smart::seryalize([
						'checksum' 	=> (string) sha1($y_realm.':'.$y_key.':'.$y_value),
						'data' 		=> (string) $y_value
					]) // data is serialized pcache as string
				]
			);
		} catch(Exception $err) { // don't throw if MongoDB error !
			Smart::log_warning(__METHOD__.' # Write Error: '.$err->getMessage());
			return false;
		} //end try catch
		//--
		if($upsert[1] != 1) {
			return false;
		} //end if
		//--
		return true;
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
			Smart::log_warning(__METHOD__.' # Invalid MongoDB Instance');
			return false;
		} //end if
		//--
		if((string)$y_key == '*') {
			try {
				$delete = self::$mongo->delete(
					(string) self::$collection,
					[ // find filter (all in realm)
						'realm' 	=> (string) $y_realm
					]
				);
			} catch(Exception $err) { // don't throw if MongoDB error !
				Smart::log_warning(__METHOD__.' # Delete Many Error: '.$err->getMessage());
				return false;
			} //end try catch
			// do not check $delete[1] >= 1, as it may already be deleted by other process
		} else {
			$id = (string) SmartHashCrypto::sha512($y_realm.':'.$y_key);
			try {
				$delete = self::$mongo->delete(
					(string) self::$collection,
					[ // find filter (by Unique)
						'id' 		=> (string) $id,
						'key' 		=> (string) $y_key,
						'realm' 	=> (string) $y_realm
					]
				);
			} catch(Exception $err) { // don't throw if MongoDB error !
				Smart::log_warning(__METHOD__.' # Delete One Error: '.$err->getMessage());
				return false;
			} //end try catch
			// do not check $delete[1] == 1, as it may already be deleted by other process
		} //end if else
		//--
		return true;
		//--
	} //END FUNCTION


	//===== PRIVATES


	private static function initCacheManager() {
		//--
		if(!self::isActive()) {
			Smart::log_warning(__METHOD__.' # MongoDB does not appear to be active in configs');
			return false;
		} //end if
		//--
		if((is_object(self::$mongo)) AND (self::$mongo instanceof SmartMongoDb)) {
			//--
			// OK, already instantiated ...
			//--
		} else {
			//--
			$is_fatal_err = false; // for a persistent cache do not use fatal errors, just log them
			//--
			self::$mongo = new SmartMongoDb(
				array(), 				// no custom config
				(bool) $is_fatal_err 	// fatal err
			); // use the connection values from configs
			//--
			$ping = self::$mongo->igcommand(
				[
					'ping' => 1,
				]
			);
			if(!self::$mongo->is_command_ok($ping)) {
				Smart::log_warning(__METHOD__.' # Server Failed to answer to ping after connect ...');
				self::$mongo = null;
				return false;
			} //end if
			//--
			$create_collection = self::$mongo->igcommand(
				[
					'create' => (string) self::$collection
				]
			);
			if(self::$mongo->is_command_ok($create_collection)) { // cmd is OK just when creates
				//--
				$create_indexes = self::$mongo->igcommand(
					[
						'createIndexes' => (string) self::$collection,
						'indexes' 		=> [
							[
								'name' 				=> 'id',
								'key' 				=> [ 'id' => 1 ]
							],
							[
								'name' 				=> 'key',
								'key' 				=> [ 'key' => 1 ]
							],
							[
								'name' 				=> 'realm',
								'key' 				=> [ 'realm' => 1 ]
							],
							[
								'name' 				=> 'unique_idx',
								'key' 				=> [ 'id' => 1, 'key' => 1, 'realm' => 1 ],
								'unique' 			=> true
							],
							[
								'name' 				=> 'created',
								'key' 				=> [ 'created' => -1 ]
							],
							[
								'name' 				=> 'modified',
								'key' 				=> [ 'modified' => -1 ]
							],
							[
								'name' 				=> 'expire',
								'key' 				=> [ 'expire' => -1 ]
							],
							[
								'name' 				=> 'expire_at',
								'key' 				=> [ 'expire_at' => -1 ]
							]
						]
					]
				);
				//--
				if(!self::$mongo->is_command_ok($create_indexes)) {
					$drop_collection = self::$mongo->igcommand(
						[
							'drop' => (string) self::$collection
						]
					);
					Smart::log_warning(__METHOD__.' # Failed to create collection indexes, dropping collection: '.(int)self::$mongo->is_command_ok($drop_collection));
					self::$mongo = null;
					return false;
				} //end if
				//--
			} //end if
			//--
			if(Smart::random_number(0, 10) == 1) { // 10% chance to delete
				try {
					$clear_expired = self::$mongo->delete(
						(string) self::$collection,
						[ // find filter: all expired
							'$and' => [
								[ 'expire' 		=> [ '$gt' 	=> 0 ] ], 				// expire > 0
								[ 'expire_at' 	=> [ '$gte' => 0 ] ], 				// expire_at >= 0
								[ 'expire_at' 	=> [ '$lt' 	=> (int)time() ] ] 		// expire_at < time()
							]
						]
					);
				} catch(Exception $err) { // don't throw if MongoDB error !
					Smart::log_warning(__METHOD__.' # Failed to delete expired sessions: '.$err->getMessage());
					return false;
				} //end try catch
			} //end if
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