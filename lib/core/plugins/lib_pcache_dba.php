<?php
// [LIB - Smart.Framework / Plugins / DBA Persistent Cache]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - DBA Persistent Cache
// DEPENDS:
//	* Smart::
//	* SmartDbaUtilDb::
//	* SmartDbaDb::
// DEPENDS-EXT: PHP DBA
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Provides a persistent Cache (in-DBA-Files), that can be shared and/or reused between multiple PHP executions.
 * Requires DBA to be set-up in config properly.
 *
 * THIS CLASS IS FOR PRIVATE USE ONLY (used as a backend for for SmartPersistentCache)
 * @access 		private
 * @internal
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	Smart, PHP DBA, SmartDbaUtilDb, SmartDbaDb
 * @version 	v.20191205
 * @package 	Plugins:Database:Dba
 *
 */
class SmartDbaPersistentCache extends SmartAbstractPersistentCache {

	// ::

	// !!! THIS CLASS MUST NOT BE MARKED AS FINAL to allow the class SmartPersistentCache@DBA to be extended from this !!!
	// But this class have all PUBLIC Methods marked as FINAL to avoid being rewritten ...

	const DBA_FOLDER 			= 'tmp/cache/pcache#dba/'; // base cached folder
	const DBA_FILE   			= 'p-cache.dba';		// base name for dba cache file

	private static $dba 		= null; 	// Array of DBA Objects ; by default is null
	private static $is_active 	= null;		// Cache Active State ; by default is null ; on 1st check must set to TRUE or FALSE


	final public static function getVersionInfo() {
		//--
		return (string) 'DBA: DB File based Persistent Cache, using the handler: '.SmartDbaUtilDb::getDbaHandler();
		//--
	} //END FUNCTION


	final public static function isActive() {
		//--
		if(self::$is_active !== null) {
			return (bool) self::$is_active;
		} //end if
		//--
		$dba_cfg = (array) Smart::get_from_config('dba');
		//--
		if((Smart::array_size($dba_cfg) > 0) && (SmartDbaUtilDb::isDbaAndHandlerAvailable() === true)) {
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
		return false; // DBA is not a memory based cache backend (it is file based), so it is FALSE
		//--
	} //END FUNCTION


	final public static function isFileSystemBased() {
		//--
		return true; // DBA is a hybrid FileSystem/Database based cache backend, so it is TRUE
		//--
	} //END FUNCTION


	final public static function isDbBased() {
		//--
		return true; // DBA is a hybrid FileSystem/Database based cache backend, so it is TRUE
		//--
	} //END FUNCTION


	final public static function clearData() {
		//--
		if(!self::isActive()) {
			return false;
		} //end if
		//--
		self::$dba = []; // reset all connections
		//--
		return (bool) SmartFileSystem::dir_delete(
			(string) SmartFileSysUtils::add_dir_last_slash(self::DBA_FOLDER),
			true // recursive delete all p-cache folder
		);
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
		$realm = self::initCacheManager($y_realm);
		if(!$realm) {
			Smart::log_warning(__METHOD__.' # Invalid DBA Instance: '.$realm);
			return false;
		} //end if
		//--
		return (bool) self::$dba[(string)$realm]->keyExists((string)$y_key);
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
		$realm = self::initCacheManager($y_realm);
		if(!$realm) {
			Smart::log_warning(__METHOD__.' # Invalid DBA Instance: '.$realm);
			return -3;
		} //end if
		//--
		return (int) self::$dba[(string)$realm]->getTtl((string)$y_key);
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
		$realm = self::initCacheManager($y_realm);
		if(!$realm) {
			Smart::log_warning(__METHOD__.' # Invalid DBA Instance: '.$realm);
			return null;
		} //end if
		//--
		return self::$dba[(string)$realm]->getKey((string)$y_key);
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
		if(strlen((string)$y_value) > 16777216) { // {{{SYNC-PCACHE-MAX-OBJ-SIZE}}}
			Smart::log_warning(__METHOD__.' # Invalid Value: OVERSIZED, more than 16MB');
			return false;
		} //end if
		//--
		$realm = self::initCacheManager($y_realm);
		if(!$realm) {
			Smart::log_warning(__METHOD__.' # Invalid DBA Instance: '.$realm);
			return false;
		} //end if
		//--
		$y_value = (string) SmartUnicode::fix_charset((string)$y_value); // fix
		$y_expiration = Smart::format_number_int($y_expiration, '+');
		if((int)$y_expiration < 0) {
			$y_expiration = 0; // zero is for not expiring records
		} //end if
		//--
		return (bool) self::$dba[(string)$realm]->setKey((string)$y_key, (string)$y_value, (int)$y_expiration);
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
		$realm = self::initCacheManager($y_realm);
		if(!$realm) {
			Smart::log_warning(__METHOD__.' # Invalid DBA Instance: '.$realm);
			return false;
		} //end if
		//--
		if((string)$y_key == '*') { // delete all keys in this realm
			return (bool) self::$dba[(string)$realm]->truncateDb(); // for each realm there is a separate DB file, so truncate it
		} else { // delete just one key
			return (bool) self::$dba[(string)$realm]->unsetKey($y_key);
		} //end if else
		//--
	} //END FUNCTION


	//##### PRIVATES


	private static function getValidatedDbaRealm($y_realm) {
		//--
		if(((string)trim((string)$y_realm) == '') OR (!self::validateRealm((string)$y_realm))) {
			$y_realm = '!'; // invalid or empty realm ; NOTICE: validateRealm will validate a realm if empty string or match the pattern !!
		} //end if
		//--
		return (string) $y_realm;
		//--
	} //END FUNCTION


	private static function initCacheManager($y_realm) {
		//--
		if(!is_array(self::$dba)) {
			self::$dba = [];
		} //end if
		//--
		if(!self::isActive()) {
			Smart::log_warning(__METHOD__.' # DBA does not appear to be active in configs');
			return '';
		} //end if
		//--
		$realm = (string) self::getValidatedDbaRealm($y_realm);
		//--
		if((is_object(self::$dba[(string)$realm])) AND (self::$dba[(string)$realm] instanceof SmartDbaDb)) {
			//--
			return (string) $realm; // OK, already instantiated ...
			//--
		} else {
			//--
			$is_fatal_err = false; // for a persistent cache do not use fatal errors, just log them
			//--
			$handler = (string) SmartDbaUtilDb::getDbaHandler();
			//-- {{{SYNC-PREFIXES-FOR-FS-CACHE}}}
			if(((string)trim((string)$realm) != '') AND ((string)$realm != '!') AND (SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$realm))) {
				$tmp_1_prefix = (string) strtolower((string)substr((string)Smart::safe_varname($realm), 0, 1));
				$tmp_2_prefix = (string) strtolower((string)substr((string)Smart::safe_varname($realm), 1, 1));
				$tmp_3_prefix = (string) strtolower((string)substr((string)Smart::safe_varname($realm), 2, 1));
				if(((string)trim((string)$tmp_1_prefix) == '') OR (!preg_match('/^[a-z0-9]+$/', (string)$tmp_1_prefix))) {
					$tmp_1_prefix = '@';
				} //end if
				if(((string)trim((string)$tmp_2_prefix) == '') OR (!preg_match('/^[a-z0-9]+$/', (string)$tmp_2_prefix))) {
					$tmp_2_prefix = '@';
				} //end if
				if(((string)trim((string)$tmp_3_prefix) == '') OR (!preg_match('/^[a-z0-9]+$/', (string)$tmp_3_prefix))) {
					$tmp_3_prefix = '@';
				} //end if
				$db_file_folder = (string) SmartFileSysUtils::add_dir_last_slash(self::DBA_FOLDER).SmartFileSysUtils::add_dir_last_slash($tmp_1_prefix).SmartFileSysUtils::add_dir_last_slash($tmp_2_prefix).SmartFileSysUtils::add_dir_last_slash($tmp_3_prefix);
				$dba_fname = (string) substr((string)self::DBA_FILE, 0, -4).'-@-'.substr(Smart::safe_filename(strtolower((string)$realm)), 0, 50).'#'.SmartHashCrypto::crc32b($realm).substr((string)self::DBA_FILE, -4, 4); // ~ 79 chars ; NOTICE: $realm can contain slashes as they are allowed by validateRealm !!
			} else {
				$db_file_folder = (string) SmartFileSysUtils::add_dir_last_slash(self::DBA_FOLDER);
				$dba_fname = (string) self::DBA_FILE;
			} //end if else
			SmartFileSysUtils::raise_error_if_unsafe_path($db_file_folder);
			//--
			$db_file_path = (string) $db_file_folder.$dba_fname;
			SmartFileSysUtils::raise_error_if_unsafe_path((string)$db_file_path);
			//--
			self::$dba[(string)$realm] = new SmartDbaDb(
				(string) $db_file_path, 		// file :: for each realm there is a separate DB file (in a separate sub-folder)
				(string) get_called_class(), 	// desc (late state binding to get this class or class that extends this)
				(bool)   $is_fatal_err 			// fatal err
			); // use the connection values from configs
			//--
		} //end if
		//--
		return (string) $realm;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>