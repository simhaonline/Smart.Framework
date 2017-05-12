<?php
// [LIB - SmartFramework / Authentication Support]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.1 r.2017.05.12 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.5')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Authentication Support
// DEPENDS:
//	* Smart::
//	* SmartCipherCrypto::
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: Smart Authentication - provides a safe, in-memory object, to store the authentication data and to provide a standard way to work with authentication inside the smartframework / modules.
 *
 * It must be re-populated on each execution. This ensure using a good practice for Auth mechanisms.
 * It provides the core and only STATIC methods to integrate authentication with Smart.Framework
 *
 * The very important security concerns regarding the authentication protection against forgery,
 * the mechanism implemented in this class will offer a very good protection by using CONSTANTS,
 * so after a successful or failed authentication, the page needs to refresh or load another page
 * in order to change authentication data.
 * This comes as a result of more than 15 years research over web and client/server technologies ...
 *
 * The best practices are to never store Authentication objects in session because session can be forged.
 * To avoid such bad practices this object provide just STATIC methods !!
 * The best way is to store just the login ID and a safe password HASH (irreversible) in session
 * and re-check authentication each time when the page is loaded or to use the HTTP AUTH mechanism
 * to avoid store in session the username / password hash. Or this can be combined with cookies, but requires
 * much more atention to pay by avoiding session forgery or cookie leakage.
 * Session must be protected against forgery by implementing advanced detection mechanisms based on
 * IP address and the browser signature of the client. The Smart Session provides a good layer for this purpose.
 *
 * <code>
 * // Usage example:
 * SmartAuth::some_method_of_this_class(...);
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.160221
 * @package 	Application
 *
 */
final class SmartAuth {

	// ::

	private static $AuthCompleted = false;		// prevent re-authentication, ... the results may be unpredictable !!
	private static $AuthData = array(); 		// register Auth Data


//================================================================
/**
 * Set the (in-memory) Auth Login Data
 * It can be used just once per execution (session) as it stores the data using constants,
 * and the data cannot be changed after a successful or failed authentication has set.
 *
 * @param 	STRING 	$y_user_id 				:: The user (login) ID used to authenticate the user ; Mandatory ; it can be the UserID from DB or if not using a DB must supply a unique ID to identify the user like username
 * @param 	STRING 	$y_user_alias			:: The user (login) Alias, used to display the logged in user ; Mandatory ; can be the same as the login ID or different (Ex: login ID can be 'myUserName' and this 'myUserName' ; or: login ID can be 5017 and this 'myUserName')
 * @param 	STRING 	$y_user_email 			:: *OPTIONAL* The user Email ; if email is used as login ID this may be redundant !
 * @param 	STRING 	$y_user_fullname 		:: *OPTIONAL* The user Full Name (First Name + Last Name)
 * @param 	ARRAY 	$y_user_privileges_list :: *OPTIONAL* The user Privileges List as array that list all the current user privileges
 * @param 	STRING 	$y_user_quota 			:: *OPTIONAL* The user (storage) Quota
 * @param 	ARRAY 	$y_user_metadata 		:: *OPTIONAL* The user metainfo, associative array key => value
 * @param 	STRING 	$y_realm 				:: *OPTIONAL* The user Authentication Realm(s)
 * @param 	ENUM 	$y_method 				:: *OPTIONAL* The authentication method used: HTTP-BASIC / HTTP-DIGEST / OTHER
 * @param 	STRING 	$y_pass					:: *OPTIONAL* The user login password (will be stored in memory as Blowfish encrypted to avoid exposure)
 *
 * @return 	BOOLEAN							:: TRUE if all data is OK, FALSE if not or try to reauthenticate under the same execution (which is not allowed ; must be just once per execution)
 */
public static function set_login_data($y_user_id, $y_user_alias, $y_user_email='', $y_user_fullname='', $y_user_privileges_list=array('none','no-privilege'), $y_user_quota=-1, $y_user_metadata=array(), $y_realm='DEFAULT', $y_method='', $y_pass='') {
	//--
	if(self::$AuthCompleted !== false) { // avoid to re-auth
		Smart::log_warning('Re-Authentication is not allowed ...');
		return;
	} //end if
	self::$AuthCompleted = true;
	//--
	self::$AuthData = array(); // reset the auth data
	//--
	$y_user_id = trim((string)$y_user_id); // user ID
	$y_user_alias = trim((string)$y_user_alias); // username (user alias ; can be the same as userID or different)
	$y_user_email = trim((string)$y_user_email);
	$y_user_fullname = trim((string)$y_user_fullname);
	//--
	if(is_array($y_user_privileges_list)) {
		$y_user_privileges_list = (string) strtolower((string)Smart::array_to_list((array)$y_user_privileges_list));
	} else {
		$y_user_privileges_list = (string) strtolower((string)trim((string)$y_user_privileges_list)); // in this case can be provided a raw list of privileges (Example: '<none>, <no-privilege>')
	} //end if else
	//--
	$y_user_quota = Smart::format_number_int($y_user_quota); // can be also negative
	//--
	switch(strtoupper((string)$y_method)) {
		case 'HTTP-BASIC':
			$y_method = 'HTTP-BASIC';
			break;
		case 'HTTP-DIGEST':
			$y_method = 'HTTP-DIGEST';
			break;
		case 'OTHER':
		default:
			$y_method = 'OTHER';
	} //end switch
	//--
	$the_key = '#'.Smart::random_number(10000,99999).'#';
	$the_pass = '';
	if((string)$y_pass != '') {
		$the_pass = SmartCipherCrypto::encrypt('hash/sha1', (string)$the_key, (string)$y_pass);
	} //end if
	//--
	if((string)$y_user_id != '') {
		//--
		self::$AuthData['USER_ID'] 				= (string) $y_user_id;
		self::$AuthData['USER_EMAIL'] 			= (string) $y_user_email;
		self::$AuthData['USER_ALIAS'] 			= (string) $y_user_alias;
		self::$AuthData['USER_FULLNAME'] 		= (string) $y_user_fullname;
		self::$AuthData['USER_PRIVILEGES'] 		= (string) $y_user_privileges_list;
		self::$AuthData['USER_QUOTA'] 			= (int)    $y_user_quota;
		self::$AuthData['USER_METADATA'] 		= (array)  $y_user_metadata;
		self::$AuthData['USER_LOGIN_REALM'] 	= (string) $y_realm;
		self::$AuthData['USER_LOGIN_METHOD'] 	= (string) $y_method;
		self::$AuthData['USER_LOGIN_PASS'] 		= (string) $the_pass;
		self::$AuthData['KEY'] 					= (string) $the_key;
		//--
		return true;
		//--
	} else {
		//--
		return false;
		//--
	} //end if
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Check the (in-memory) Auth Login Data if the current user is logged-in
 *
 * @return 	BOOLEAN		:: TRUE if current user is Logged-in, FALSE if not
 */
public static function check_login() {
	//--
	$logged_in = false;
	//--
	if((string)self::$AuthData['USER_ID'] != '') {
		$logged_in = true;
	} //end if
	//--
	return (bool) $logged_in;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the auth user login method from (in-memory) Auth Login Data
 *
 * @return 	STRING		:: The user login method
 */
public static function get_login_method() {
	//--
	return (string) self::$AuthData['USER_LOGIN_METHOD'];
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the auth (safe) stored password from (in-memory)
 *
 * @return 	STRING		:: The plain password if was set or empty string
 */
public static function get_login_password() {
	//--
	if((string)self::$AuthData['USER_LOGIN_PASS'] == '') {
		return ''; // empty pass
	} else {
		return (string) SmartCipherCrypto::decrypt('hash/sha1', (string)self::$AuthData['KEY'], (string)self::$AuthData['USER_LOGIN_PASS']);
	} // end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the (in-memory) Auth Login Data
 *
 * @return 	ARRAY		:: a complete array containing all the meta-data of the current auth user
 */
public static function get_login_data() {
	//--
	return array(
		'is-logged-in' 		=> self::check_login(),
		'login-id' 			=> self::get_login_id(),
		'login-alias' 		=> self::get_login_alias(),
		'login-email' 		=> self::get_login_email(),
		'login-full-name' 	=> self::get_login_fullname(),
		'login-privileges' 	=> self::get_login_privileges(),
		'login-quota' 		=> self::get_login_quota(),
		'login-metadata' 	=> self::get_login_metadata(),
		'login-realm' 		=> self::get_login_realm(),
		'login-method' 		=> self::get_login_method(),
		'login-password' 	=> self::get_login_password()
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the current user (login) ID from the (in-memory) Auth Login Data
 *
 * @return 	STRING		:: if current user is Logged-in will get the user (login) ID which is mandatory, else an empty string
 */
public static function get_login_id() {
	//--
	return (string) self::$AuthData['USER_ID'];
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the current user email from the (in-memory) Auth Login Data
 *
 * @return 	STRING		:: returns the user login email or an empty string if not set
 */
public static function get_login_email() {
	//--
	return (string) self::$AuthData['USER_EMAIL'];
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the current user login alias (username) from the (in-memory) Auth Login Data
 *
 * @return 	STRING		:: returns the user login alias (username) or an empty string if not set
 */
public static function get_login_alias() {
	//--
	return (string) self::$AuthData['USER_ALIAS'];
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the current user Full Name (First + Last Name) from the (in-memory) Auth Login Data
 *
 * @return 	STRING		:: returns the user login full name or an empty string if not set
 */
public static function get_login_fullname() {
	//--
	return (string) self::$AuthData['USER_FULLNAME'];
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the list of the current user privileges stored in the (in-memory) Auth Login Data
 *
 * @return 	STRING		:: returns user login privileges as a list-string like: '<privilege_one>,<privilege_two>,...' or an empty string if not set
 */
public static function get_login_privileges() {
	//--
	return (string) self::$AuthData['USER_PRIVILEGES'];
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the array of the current user privileges stored in the (in-memory) Auth Login Data
 *
 * @return 	ARRAY		:: returns user login privileges as an array[privilege_one, privilege_two, ...] or an empty array if not set
 */
public static function get_login_arr_privileges() {
	//--
	return (array) Smart::list_to_array((string)self::$AuthData['USER_PRIVILEGES']);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Test if the current user privileges contain the tested one using the (in-memory) Auth Login Data
 *
 * @return 	BOOLEAN		:: TRUE if the current user have the tested privilege or FALSE if does not
 */
public static function test_login_privilege($y_privilege_to_test) {
	//--
	$y_privilege_to_test = trim((string)$y_privilege_to_test);
	//--
	$have_this_privilege = false;
	//--
	if((string)self::$AuthData['USER_PRIVILEGES'] != '') {
		if(stripos(self::$AuthData['USER_PRIVILEGES'], '<'.$y_privilege_to_test.'>') !== false) {
			$have_this_privilege = true;
		} //end if
	} //end if
	//--
	return (bool) $have_this_privilege;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the current user quota stored in the (in-memory) Auth Login Data
 *
 * @return 	INTEGER		:: returns the user (storage) quota
 */
public static function get_login_quota() {
	//--
	$login_quota = -1;
	//--
	if((int)self::$AuthData['USER_QUOTA'] >= 0) {
		$login_quota = (int) self::$AuthData['USER_QUOTA'];
	} //end if
	//--
	return (int) $login_quota;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the current user metadaya stored in the (in-memory) Auth Login Data
 *
 * @return 	ARRAY		:: returns an array with all current user metadata
 */
public static function get_login_metadata() {
	//--
	return (array) self::$AuthData['USER_METADATA'];
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get the auth realm of the current user stored in the (in-memory) Auth Login Data
 *
 * @return 	STRING		:: returns the current user auth realm or an empty string if not set
 */
public static function get_login_realm() {
	//--
	$login_realm = 'DEFAULT';
	//--
	if((string)self::$AuthData['USER_LOGIN_REALM'] != '') {
		$login_realm = strtoupper((string)self::$AuthData['USER_LOGIN_REALM']);
	} //end if
	//--
	return (string) $login_realm;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Build the associative Array of Auth Privileges
 *
 * @param 	MIXED 	$y_priv_list 			:: List of Privileges as ARRAY Array('priv_1', 'priv_2', ..., 'priv_n') or STRING '<priv_1>, <priv_2>, ..., <priv_n>'
 *
 * @return 	ARRAY							:: returns the associative array of auth privileges as Array('priv_1' => 'Priv 1', 'priv_2' => 'Priv 2', ..., 'priv_n' => 'Priv n')
 */
public static function build_arr_privileges($y_priv_list) {
	//--
	if(!is_array($y_priv_list)) {
		$y_priv_list = (array) Smart::list_to_array((string)$y_priv_list);
	} //end if
	//--
	$y_priv_list = (array) $y_priv_list;
	//--
	$out_arr = array();
	for($i=0; $i<Smart::array_size($y_priv_list); $i++) {
		//--
		$y_priv_list[$i] = (string) strtolower(trim((string)$y_priv_list[$i]));
		if((string)$y_priv_list[$i] != '') {
			$out_arr[(string)$y_priv_list[$i]] = (string) trim(ucwords(str_replace(array('_', '-'), array(' ', ' '), (string)$y_priv_list[$i])));
		} //end if
		//--
	} //end for
	//--
	return (array) $out_arr;
	//--
} //END FUNCTION
//================================================================


//##### DEBUG ONLY


//================================================================
/**
 *
 * @access 		private
 * @internal
 *
 */
public static function registerInternalCacheToDebugLog() {
	//--
	if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			$tmpAuthData = (array) self::$AuthData;
			$tmpAuthData['KEY'] = '*****'; // protect the key !
			SmartFrameworkRegistry::setDebugMsg('extra', '***SMART-CLASSES:INTERNAL-CACHE***', [
				'title' => 'SmartAuth // Internal Cached Vars',
				'data' => 'Dump of AuthCompleted: ['.print_r(self::$AuthCompleted,1).']'."\n".'Dump of AuthData:'."\n".print_r($tmpAuthData,1)
			]);
		} //end if
	} //end if
	//--
} //END FUNCTION
//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>