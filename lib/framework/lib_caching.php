<?php
// [LIB - SmartFramework / Cache Support]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.1.1 r.2017.04.10 / smart.framework.v.3.1

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.1')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Cache Support
// DEPENDS:
//	* SmartParser::
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: SmartCache - Provides per Execution, Volatille Cache (in-PHP-Memory volatille cache).
 *
 * This cache type is volatille, not persistent and will reset on each PHP execution.
 * Because this kind of cache is per-execution, the key values may not be shared
 * between multiple instances, and offer 100% isolation in all cases.
 * It is intended to be used on per-execution optimizations to avoid repetitive
 * execution of complex high-cost functions that would output the same result under
 * the same execution conditions as: same environment, same parameters, same client.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.160117
 * @package 	Caching
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
	 * @return MIXED	The value of the stored key or NULL
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
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-CACHE', [
				'title' => '[SetKey]: '.$y_realm.' / '.$y_key,
				'data' => SmartParser::text_endpoints((string)print_r($y_value,1), 1024)
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
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
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
	 * This is non-standard but can be used for development ...
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getAll() {
		//--
		return (array) self::$CachedData;
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
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.170223
 * @package 	Caching
 *
 */
abstract class SmartAbstractPersistentCache {

	// :: ABSTRACT


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
	 *
	 * @return BOOLEAN	TRUE if is Memory Based (Ex: Redis / Memcache / ...) or FALSE if not (Ex: File Cache)
	 */
	public static function isMemoryBased() {
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
	 * Get a Key from the persistent Cache
	 *
	 * @param STRING	$y_realm	The Cache Realm
	 * @param STRING	$y_key		The Cache Key
	 *
	 * @return MIXED	The value of the stored key or NULL
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


	/**
	 * Validate persistent Cache Realm (can be empty or must comply with self::safeKey() charset)
	 *
	 * @param STRING 	$y_realm	The Cache Realm
	 *
	 * @return BOOLEAN	Returns True if the realm is valid or False if not
	 */
	public static function validateRealm($y_realm) {
		//--
		if(preg_match('/^[_a-zA-Z0-9\-\.@#\/]*$/', (string)$y_realm)) { // {{{SYNC-WITH-self::safeKey()}}} + allow empty * instead of +
			return true;
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Validate persistent Cache Realm (cannot be empty and must comply with self::safeKey() charset)
	 *
	 * @param STRING 	$y_key		The Cache Key
	 *
	 * @return BOOLEAN	Returns True if the key is valid or False if not
	 */
	public static function validateKey($y_key) {
		//--
		if((string)$y_key == '') {
			return false;
		} //end if
		//--
		if(preg_match('/^[_a-zA-Z0-9\-\.@#\/]+$/', (string)$y_key)) { // {{{SYNC-WITH-self::safeKey()}}}
			return true;
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Prepare a persistent Cache SAFE Key or Realm
	 *
	 * @param STRING 	$y_key_or_realm		The Cache Key or Realm
	 *
	 * @return STRING	Returns the safe prepared Key or Realm
	 */
	public static function safeKey($y_key_or_realm) {
		//--
		return (string) Smart::safe_pathname((string)$y_key_or_realm);
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
	public static function varEncode($y_var) {
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
	public static function varDecode($y_encoded_var) {
		//--
		return Smart::unseryalize((string)$y_encoded_var); // mixed
		//--
	} //END FUNCTION


	/**
	 * Compress + Encode a MIXED variable (array / string / number) to be stored in Persistent Cache
	 * To reverse the compressing + encoding use varUncompress()
	 *
	 * Use this function to store any type of variables: numbers, strings or arrays in a safe encoded + compressed format.
	 * By default the variable will be: encoded (serialized as Json), compressed (gzcompress/6/deflate) and finally B64-Encoded.
	 *
	 * @param MIXED 	$y_var			The Variable to be encoded + compressed
	 *
	 * @return STRING	Returns the safe serialized + compressed variable content
	 */
	public static function varCompress($y_var) {
		//--
		$raw_data = (string) Smart::seryalize($y_var);
		$y_var = ''; // free mem
		if((string)$raw_data == '') {
			return '';
		} //end if
		//-- compress
		$len_data = strlen((string)$raw_data);
		$arch_data = @gzcompress((string)$raw_data, 6, ZLIB_ENCODING_DEFLATE); // zlib compress (default compression of zlib is 6)
		$raw_data = ''; // free mem
		//-- check for possible compress errors
		if(($arch_data === false) OR ((string)$arch_data == '')) {
			Smart::log_warning('SmartPersistentCache / Cache Variable Compress :: ZLib Compress ERROR ! ...');
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
		if($ratio > 32768) { // check for this bug in ZLib {{{SYNC-GZDEFLATE-ERR-CHECK}}}
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
	 * By default the variable will be: B64-Decoded, uncompressed (gzcompress/inflate) and finally decoded (unserialized from Json).
	 *
	 * @param STRING 	$y_cache_arch_var		The compressed + encoded variable
	 *
	 * @return MIXED	Returns the original restored type and value of that variable
	 */
	public static function varUncompress($y_cache_arch_var) {
		//--
		$y_cache_arch_var = (string) trim((string)$y_cache_arch_var);
		//--
		if((string)$y_cache_arch_var == '') {
			return null; // no data to unarchive, return empty string
		} //end if
		//--
		$y_cache_arch_var = @base64_decode((string)$y_cache_arch_var);
		if(($y_cache_arch_var === false) OR (trim((string)$y_cache_arch_var) == '')) { // use trim, the deflated string can't contain only spaces
			return null; // something went wrong after b64 decoding ...
		} //end if
		//--
		$y_cache_arch_var = @gzuncompress((string)$y_cache_arch_var);
		if(($y_cache_arch_var === false) OR (trim((string)$y_cache_arch_var) == '')) { // use trim, the string before unseryalize can't contain only spaces
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