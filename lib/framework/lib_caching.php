<?php
// [LIB - Smart.Framework / Cache Support]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Cache Support
// DEPENDS:
//	* Smart::
//	* SmartParser::
//======================================================


//--
// gzencode / gzdecode (rfc1952) is the gzip compatible algorithm which uses CRC32 minimal checksums (a bit safer and faster than ADLER32)
//--
if((!function_exists('gzencode')) OR (!function_exists('gzdecode'))) {
	@http_response_code(500);
	die('ERROR: The PHP ZLIB Extension (gzencode/gzdecode) is required for Smart.Framework / Lib Utils');
} //end if
//--


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: SmartCache - Provides per Execution, Volatille Cache (in-PHP-Memory).
 *
 * This cache type is volatille, not persistent and will reset after each PHP execution.
 * Because this kind of cache is per-execution, the key values will not be shared between executions.
 * It is intended to be used on per-execution optimizations to avoid repetitive
 * execution of complex high-cost methods that would output the same result under
 * the same execution conditions as: same environment / same parameters / same client.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.20191207
 * @package 	@Core
 *
 */
final class SmartCache {

	// ::

	private static $CachedData = array(); // registry of cached data


	/**
	 * Check if a Key exists in the non-persistent Cache
	 *
	 * @param STRING	$y_realm	The Cache Realm
	 * @param STRING	$y_key		The Cache Key
	 *
	 * @return BOOLEAN	TRUE if Key Exists or FALSE if not
	 */
	public static function keyExists($y_realm, $y_key) {
		//--
		if(is_array(self::$CachedData)) {
			if(is_array(self::$CachedData[(string)$y_realm])) {
				if(array_key_exists((string)$y_key, self::$CachedData[(string)$y_realm])) {
					return true;
				} else {
					return false;
				} //end if else
			} else {
				return false;
			} //end if else
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Get a Key from the non-persistent Cache
	 *
	 * @param STRING	$y_realm	The Cache Realm
	 * @param STRING	$y_key		The Cache Key
	 *
	 * @return MIXED	The value of the stored key or NULL if key not found in cache
	 */
	public static function getKey($y_realm, $y_key) {
		//--
		if(self::keyExists($y_realm, $y_key) === true) {
			return self::$CachedData[(string)$y_realm][(string)$y_key];
		} else {
			return null;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Set a Key into the non-persistent Cache
	 *
	 * @param STRING 	$y_realm	The Cache Realm
	 * @param STRING 	$y_key		The Cache Key
	 * @param MIXED 	$y_value	The value to be stored
	 *
	 * @return BOOLEAN	Always returns true
	 */
	public static function setKey($y_realm, $y_key, $y_value) {
		//--
		self::$CachedData[(string)$y_realm] = (array) self::$CachedData[(string)$y_realm];
		self::$CachedData[(string)$y_realm][(string)$y_key] = $y_value; // mixed
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-CACHE', [
				'title' => '[SetKey]: '.$y_realm.' / '.$y_key,
				'data' => Smart::text_cut_by_limit((string)print_r($y_value,1), 1024, true, '[...data-longer-than-1024-bytes-is-not-logged-all-here...]')
			]);
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Unset a Key into the non-persistent Cache
	 *
	 * @param STRING 	$y_realm	The Cache Realm
	 * @param STRING 	$y_key		The Cache Key
	 *
	 * @return BOOLEAN	Always returns true
	 */
	public static function unsetKey($y_realm, $y_key) {
		//--
		self::$CachedData[(string)$y_realm] = (array) self::$CachedData[(string)$y_realm];
		unset(self::$CachedData[(string)$y_realm][(string)$y_key]);
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-CACHE', [
				'title' => '[INFO] :: UnsetKey: '.$y_realm.' / '.$y_key,
				'data' => ''
			]);
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Get All Data from the non-persistent Cache.
	 * This is non-standard but can be used for very advanced development purposes ...
	 *
	 * @access 		private
	 * @internal
	 *
	 * @return ARRAY 	all the data ...
	 */
	public static function getAll() {
		//--
		return (array) self::$CachedData;
		//--
	} //END FUNCTION


	/**
	 * Empty the non-persistent Cache by deleting all existing keys.
	 * This is non-standard but can be used for very advanced development purposes ...
	 * Use this ONLY for extreme situations where you choose to manually reset all the non-persistent Cache operations just because you want so ;) ...
	 * Clearing all data will be very inefficient and not recommended at all !!!
	 *
	 * @access 		private
	 * @internal
	 *
	 * @return BOOLEAN	TRUE if is success or FALSE if fail
	 */
	public static function clearData() {
		//--
		self::$CachedData = array();
		//--
		return true;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: Smart.Framework Abstract Persistent Cache.
 * The backends used for Persistent Cache must be very fast, must support large keys and must supply key expiration by time.
 * If the key expiration is not supported natively, then a custom function must be created to delete expired keys.
 *
 * It must contain ONLY public functions to avoid late state binding (self:: vs static::)
 *
 * @access 		private
 * @internal
 *
 * @depends 	-
 * @version 	v.20191209
 * @package 	development:Application
 *
 */
abstract class SmartAbstractPersistentCache {

	// :: ABSTRACT


	/**
	 * Get the version info about the Persistent Cache
	 *
	 * @return STRING
	 */
	public static function getVersionInfo() {
		//--
		return 'N/A';
		//--
	} //END FUNCTION


	/**
	 * Check if the persistent Cache is Active
	 *
	 * @return BOOLEAN	TRUE if is Active or FALSE if not
	 */
	public static function isActive() {
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Check if the persistent Cache is Memory Based.
	 * This function must ALWAYS be used in conjunction with isActive() as it will return TRUE just if the backend is a Memory Based one and will not check if Backed is Active or not ...
	 * This function should be used in some caching scenarios to check if make sense to cache things in Persistent Cache or not (Ex: if something make sense to be stored in a Memory based Cache)
	 *
	 * @return BOOLEAN	TRUE if is Memory Based (Ex: Redis / Memcached / ...) or FALSE if not
	 */
	public static function isMemoryBased() {
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Check if the persistent Cache is FileSystem Based.
	 * Notice: DBA or SQLite are both FileSystem + Database based, this may return true to both - this function but aso isDbBased()
	 * This function must ALWAYS be used in conjunction with isActive() as it will return TRUE just if the backend is a FileSystem Based one and will not check if Backed is Active or not ...
	 * This function should be used in some caching scenarios to check if make sense to cache things in Persistent Cache or not (Ex: if something make sense to be stored in a FileSystem based Cache)
	 *
	 * @return BOOLEAN	TRUE if is FileSystem Based (Ex: SQLite / DBA / ...) or FALSE if not
	 */
	public static function isFileSystemBased() {
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Check if the persistent Cache is Database Based.
	 * Notice: DBA or SQLite are both FileSystem + Database based, this may return true to both - this function but aso isFileSystemBased()
	 * Notice: PostgreSQL / MySQL / MongoDB are real DB servers so must return TRUE to this function but must return FALSE to isFileSystemBased() because they have advanced storage systems not a typical prefixed folder storage on Disk
	 * This function must ALWAYS be used in conjunction with isActive() as it will return TRUE just if the backend is a FileSystem Based one and will not check if Backed is Active or not ...
	 * This function should be used in some caching scenarios to check if make sense to cache things in Persistent Cache or not (Ex: if something make sense to be stored in a Database based Cache)
	 *
	 * @return BOOLEAN	TRUE if is Database Based (Ex: PostgreSQL / MySQL / MongoDB / SQLite / DBA / ...) or FALSE if not
	 */
	public static function isDbBased() {
		//--
		return false;
		//--
	} //END FUNCTION


	/**
	 * Empty the persistent Cache by deleting all existing keys.
	 *
	 * @return BOOLEAN	TRUE if is success or FALSE if fail
	 */
	public static function clearData() {
		//--
		return false;
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
		return false;
		//--
	} //END FUNCTION


	/**
	 * get the TTL in seconds for a key from the persistent Cache or Error code
	 *
	 * @param STRING	$y_realm	The Cache Realm
	 * @param STRING	$y_key		The Cache Key
	 *
	 * @return INTEGER	number of seconds the key will expire ; -1 if the key does not expire (is persistent) ; -2 if the key does not exists ; -3 if N/A or ERR
	 */
	public static function getTtl($y_realm, $y_key) {
		//--
		return -3;
		//--
	} //END FUNCTION


	/**
	 * Get a Key from the persistent Cache
	 *
	 * @param STRING	$y_realm	The Cache Realm
	 * @param STRING	$y_key		The Cache Key
	 *
	 * @return MIXED	The value of the stored key or NULL if key not found in cache
	 */
	public static function getKey($y_realm, $y_key) {
		//--
		return null;
		//--
	} //END FUNCTION


	/**
	 * Set a Key into the persistent Cache
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
		return false;
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
		return false;
		//--
	} //END FUNCTION


	//===== BELOW ARE FINAL STATE FUNCTIONS THAT CANNOT BE OVERRIDEN


	/**
	 * Create a (safe path) Persistent Cache Path prefix for a given realm
	 * This is mainly designed to be used by the FileSystem based persistent cache implementations, but can be used also for other purposes
	 *
	 * @param INTEGER+ 	$y_len 			The Cache Prefix length (how many dirs in the expanded path) ; can be between 2..4 depending how will scale the cache structure
	 * @param STRING	$y_realm		The Cache Realm
	 * @param STRING 	$y_key			*Optional* The Cache Key
	 *
	 * @return STRING	The 2..4 letters cache path prefix (contains: 0..9 a..z) expanded to a path ; Ex: `x/y` or `x/y/9` or `x/y/9/z`
	 */
	final public static function cachePathPrefix($y_len, $y_realm, $y_key='') {
		//--
		$y_len = (int) $y_len;
		if((int)$y_len < 2) {
			$y_len = 2;
		} elseif((int)$y_len > 4) {
			$y_len = 4;
		} //end if
		//--
		$uuid = (string) $y_realm;
		if(((string)$y_key !== '') AND ((string)$y_key !== '*')) {
			$uuid .= ':'.$y_key;
		} //end if
		//--
		$prefix = (string) strtolower(
			(string) substr(
				(string) str_pad( // fix left padding to have a fixed length string of 4 chars after conversion from `0000` to `MH33`
					(string) base_convert( // will have after conversion a base36 number from 0 to MH33
						(string) substr( // 00000 to FFFFF
							(string) md5((string)$uuid), // calculate a hash based on realm and key for the purpose of balance the storage using this prefix
							0, // start from begining and get
							5 // the first 5 hexa characters of the hash
						),
						16, // convert the prefix from hexa (base-16) because is using only 0..9 and a..f
						36  // to base-36 as will use 9..9 and a..z instead
					),
					4,
					'0',
					STR_PAD_LEFT
				),
				0,
				(int) $y_len
			)
		); // ensure even higher entropy by using 1..9 and a..z instead of 1..f
		//--
		if((int)strlen((string)$prefix) < (int)$y_len) {
			$prefix = (string) str_pad((string)$prefix, (int)$y_len, '@', STR_PAD_RIGHT);
		} //end if
		//--
		return (string) Smart::safe_pathname((string)implode('/', (array)str_split((string)substr((string)$prefix, 0, (int)$y_len), 1)));
		//--
	} //END FUNCTION


	/**
	 * Validate persistent Cache Realm
	 * Can be empty or must comply with PersistentCache::safeKey() restricted charset: _ a-z A-Z 0-9 - . @ # /
	 * Must be between 0 (min) and 255 (max) characters long
	 *
	 * @param STRING 	$y_realm	The Cache Realm that must be previous prepared with PersistentCache::safeKey() if non-empty
	 *
	 * @return BOOLEAN	Returns TRUE if the realm is valid or FALSE if not
	 */
	final public static function validateRealm($y_realm) {
		//--
		if(strlen((string)$y_realm) > 255) {
			return false;
		} //end if
		//--
		if(!preg_match('/^[_a-zA-Z0-9\-\.@#\/]*$/', (string)$y_realm)) { // {{{SYNC-PCACHE-SAFE-KEY-OR-REALM}}} + allow empty * instead of +
			return false;
		} //end if else
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Validate persistent Cache Key
	 * Cannot be empty and must comply with self::safeKey() restricted charset: _ a-z A-Z 0-9 - . @ # /
	 *
	 * @param STRING 	$y_key		The Cache Key that must be previous prepared with PersistentCache::safeKey()
	 *
	 * @return BOOLEAN	Returns TRUE if the key is valid or FALSE if not
	 */
	final public static function validateKey($y_key) {
		//--
		if((string)$y_key == '') {
			return false;
		} //end if
		//--
		if(strlen((string)$y_key) > 255) {
			return false;
		} //end if
		//--
		if(!preg_match('/^[_a-zA-Z0-9\-\.@#\/]+$/', (string)$y_key)) { // {{{SYNC-PCACHE-SAFE-KEY-OR-REALM}}}
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Validate persistent Cache Value
	 * Should not be not empty and not oversized (max 16MB)
	 *
	 * @hints If the value is oversized try to archive it before SET using SmartPersistentCache::varCompress() and after GET use SmartPersistentCache::varUncompress()
	 *
	 * @param STRING 	$y_value	The Cache Value to be tested
	 *
	 * @return BOOLEAN	Returns TRUE if the value is valid or FALSE if not
	 */
	final public static function validateValue($y_value) {
		//--
		$len = (int) strlen((string)$y_value);
		//--
		if(((int)$len <= 0) OR ((int)$len > 16777216)) { // {{{SYNC-PCACHE-MAX-OBJ-SIZE}}}
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION


	/**
	 * Prepare a persistent Cache SAFE Key or Realm
	 * Works only for Keys (that can not be empty) and for non-empty Realms
	 * Will return a prepared string with a restricted charset: _ a-z A-Z 0-9 - . @ # /
	 *
	 * @param STRING 	$y_key_or_realm		The Cache Key or Realm
	 *
	 * @return STRING	Returns the safe prepared Key or Realm
	 */
	final public static function safeKey($y_key_or_realm) {
		//--
		$key_or_realm = (string) Smart::safe_pathname((string)$y_key_or_realm); // {{{SYNC-PCACHE-SAFE-KEY-OR-REALM}}}
		if((string)$key_or_realm == '') {
			$key_or_realm = 'InvalidName__Cache__Key/Realm__';
			Smart::log_warning(__METHOD__.'() :: Invalid/Empty parameter KeyOrRealm: '.$y_key_or_realm);
		} //end if
		//--
		return (string) $key_or_realm;
		//--
	} //END FUNCTION


	/**
	 * Encode a MIXED variable (array / string / number) to be stored in Persistent Cache
	 * To reverse encoding use varDecode()
	 *
	 * By default only numbers and strings can be stored as flat values.
	 * To store complex variables like Arrays, use this function before setKey() which will serialize the var as Json standard.
	 *
	 * @param MIXED 	$y_var				The Variable to be encoded
	 *
	 * @return STRING	Returns the safe serialized variable content
	 */
	final public static function varEncode($y_var) {
		//--
		return (string) Smart::seryalize($y_var);
		//--
	} //END FUNCTION


	/**
	 * Decode a previous encoded MIXED variable (array / string / number) that was stored in Persistent Cache
	 * To be used for variables previous encoded using varEncode()
	 *
	 * By default only numbers and strings can be stored as flat values.
	 * To retrieve complex variables like Arrays, use this function after getKey() which will unserialize the var from Json standard.
	 *
	 * @param STRING 	$y_encoded_var		The encoded variable
	 *
	 * @return MIXED	Returns the original restored type and value of that variable
	 */
	final public static function varDecode($y_encoded_var) {
		//--
		return Smart::unseryalize((string)$y_encoded_var); // mixed
		//--
	} //END FUNCTION


	/**
	 * Compress + Encode a MIXED variable (array / string / number) to be stored in Persistent Cache
	 * To reverse the compressing + encoding use varUncompress()
	 *
	 * Use this function to store any type of variables: numbers, strings or arrays in a safe encoded + compressed format.
	 * By default the variable will be: encoded (serialized as Json), compressed (gzencode/6/gzip) and finally B64-Encoded.
	 *
	 * @param MIXED 	$y_var			The Variable to be encoded + compressed
	 *
	 * @return STRING	Returns the safe serialized + compressed variable content
	 */
	final public static function varCompress($y_var) {
		//--
		$raw_data = (string) Smart::seryalize($y_var);
		$y_var = ''; // free mem
		if((string)$raw_data == '') {
			return '';
		} //end if
		//-- compress
		$len_data = strlen((string)$raw_data);
		$arch_data = @gzencode((string)$raw_data, -1, FORCE_GZIP); // don't make it string, may return false ; -1 = default compression of the zlib library is used which is 6
		$raw_data = ''; // free mem
		//-- check for possible zlib-pack errors
		if(($arch_data === false) OR ((string)$arch_data == '')) {
			Smart::log_warning('SmartPersistentCache / Cache Variable Compress :: Zlib GZ-Encode ERROR ! ...');
			return '';
		} //end if
		$len_arch = strlen((string)$arch_data);
		if(($len_data > 0) AND ($len_arch > 0)) {
			$ratio = $len_data / $len_arch;
		} else {
			$ratio = 0;
		} //end if
		if($ratio <= 0) { // check for empty input / output !
			Smart::log_warning('SmartPersistentCache / Cache Variable Compress :: ZLib Data Ratio is zero ! ...');
			return '';
		} //end if
		if($ratio > 32768) { // check for this bug in ZLib {{{SYNC-GZ-ARCHIVE-ERR-CHECK}}}
			Smart::log_warning('SmartPersistentCache / Cache Variable Compress :: ZLib Data Ratio is higher than 32768 ! ...');
			return '';
		} //end if
		//--
		return (string) base64_encode((string)$arch_data);
		//--
	} //END FUNCTION


	/**
	 * Uncompress + Decode a MIXED variable (array / string / number) to be stored in Persistent Cache
	 *
	 * Use this function to retrieve any type of variables: numbers, strings or arrays that were previous safe encoded + compressed.
	 * By default the variable will be: B64-Decoded, uncompressed (gzdecode) and finally decoded (unserialized from Json).
	 *
	 * @param STRING 	$y_cache_arch_var		The compressed + encoded variable
	 *
	 * @return MIXED	Returns the original restored type and value of that variable
	 */
	final public static function varUncompress($y_cache_arch_var) {
		//--
		$y_cache_arch_var = (string) trim((string)$y_cache_arch_var);
		//--
		if((string)$y_cache_arch_var == '') {
			return null; // no data to unarchive, return empty string
		} //end if
		//--
		$y_cache_arch_var = @base64_decode((string)$y_cache_arch_var, true); // STRICT ! don't make it string, may return false
		if(($y_cache_arch_var === false) OR ((string)trim((string)$y_cache_arch_var) == '')) { // use trim, the deflated string can't contain only spaces
			Smart::log_warning('SmartPersistentCache / Cache Variable Decompress :: Empty Data after B64-Decode ! ...');
			return null; // something went wrong after b64 decoding ...
		} //end if
		//--
		$y_cache_arch_var = @gzdecode((string)$y_cache_arch_var); // don't make it string, may return false
		if(($y_cache_arch_var === false) OR ((string)trim((string)$y_cache_arch_var) == '')) { // use trim, the string before unseryalize can't contain only spaces
			Smart::log_warning('SmartPersistentCache / Cache Variable Decompress :: Empty Data after Zlib GZ-Decode ! ...');
			return null;
		} //end if
		//--
		return Smart::unseryalize((string)$y_cache_arch_var); // mixed
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>