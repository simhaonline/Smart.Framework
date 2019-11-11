<?php
// [LIB - Smart.Framework / Plugins / Session Manager]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Session Manager
// DEPENDS:
//	* Smart::
//	* SmartHashCrypto::
//	* SmartFileSystem::
//	* SmartUtils::
// DEPENDS-EXT: PHP Session Module
//======================================================
//#NOTICE: GC is controlled via
//ini_get('session.gc_maxlifetime');
//ini_get('session.gc_divisor');
//ini_get('session.gc_probability');
//======================================================


//--
if(!function_exists('session_start')) {
	@http_response_code(500);
	die('ERROR: PHP Session Module is required for the Smart.Framework');
} //end if
//--

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

// NOTICE: sessions cleanup will use the Session GC
// INFO: if the SmartAbstractCustomSession is extended as SmartCustomSession it will detect and use it
// This session type have a very advanced mechanism based on IP and browser signature to protect against session forgery.


/**
 * Class: SmartSession - provides an Application Session Container.
 *
 * Depending on the Smart.Framework INIT settings, it can use [files] based session or [user] custom session (example: Redis based session).
 *
 * <code>
 * // ## DO NOT USE directly the $_SESSION because session may not be started automatically !!! It starts on first use only ... ##
 * // # SAMPLE USAGE #
 * //--
 * SmartSession::set('MyVariable', 'test'); // register a variable to session
 * echo SmartSession::get('MyVariable'); // will get and echo just the $_SESSION['MyVariable']
 * print_r(SmartSession::get()); // will get and prin_r all $_SESSION
 * SmartSession::set('MyVariable', null); // unregister (unset) a variable from session
 * //--
 * </code>
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	extensions: PHP Session Module ; classes: Smart, SmartUtils
 * @version 	v.20190605
 * @package 	Application:Session
 *
 */
final class SmartSession {

	// ::

	private static $started = false; 	// semaphore that session start was initiated to avoid re-run of start() ; on start() the session can start (active) or not ; if successful started (active) will set the $active != 0
	private static $active = 0; 		// 0 if inactive or time() if session successful started and active


//==================================================
/**
 * [PUBLIC] Check if Session is Active
 *
 * returns BOOLEAN TRUE if active, FALSE if not
 */
public static function active() {
	//--
	if(self::$active === 0) {
		return false;
	} else {
		return true;
	} //end if else
	//--
} //END FUNCTION
//==================================================


//==================================================
/**
 * [PUBLIC] Session Get Variable
 *
 * @param STRING $yvariable 		variable name
 *
 * @return MIXED $_SESSION or $_SESSION[$yvariable]
 */
public static function get($yvariable=null) {
	//--
	self::start(); // start session if not already started
	//--
	if(!is_array($_SESSION)) { // fix for php 7.2+
		return null;
	} //end if
	//--
	if(($yvariable === null) OR ((string)trim((string)$yvariable) == '')) {
		return (array) $_SESSION; // array, all the session variables at once
	} else {
		return $_SESSION[(string)$yvariable]; // mixed
	} //end if else
	//--
} //END FUNCTION
//==================================================


//==================================================
/**
 * [PUBLIC] Session Set Variable
 *
 * @param STRING $yvariable 		variable name
 * @param ANY VARIABLE $yvalue 		variable value
 *
 * @return BOOLEAN 					TRUE if successful, FALSE if not
 */
public static function set($yvariable, $yvalue) {
	//--
	self::start(); // start session if not already started
	//--
	if(!is_array($_SESSION)) { // fix for php 7.2+
		return false;
	} //end if
	//--
	if($yvalue === null) {
		unset($_SESSION[(string)$yvariable]);
	} else {
		$_SESSION[(string)$yvariable] = $yvalue;
	} //end if else
	//--
	return true;
	//--
} //END FUNCTION
//==================================================


//==================================================
/**
 * Start the Session (if not already started).
 * This function is called automatically when set() or get() is used for a session thus is not mandatory to be called.
 * It should be called just on special circumstances (Ex: force start session without using set/get).
 *
 */
public static function start() {
	//=====
	//--
	if(self::$started !== false) {
		return; // avoid start session if already started ...
	} //end if
	self::$started = true; // mark session as started at the begining (will be marked as active at the end of this function)
	//--
	//=====
	//--
	$browser_os_ip_identification = SmartUtils::get_os_browser_ip(); // get browser and os identification
	//--
	if((string)$browser_os_ip_identification['bw'] == '@s#') {
		return; // this must be before identify bot ; in this case start no session for the self browser (session is blocked before a request to finalize thus it cannot be used !!!)
	} //end if
	//--
	if((string)$browser_os_ip_identification['bw'] == 'bot') {
		if(!defined('SMART_FRAMEWORK_SESSION_ROBOTS') OR SMART_FRAMEWORK_SESSION_ROBOTS !== true) {
			return; // in this case start no session for robots (as they do not need to share info between many visits)
		} //end if
	} //end if
	//--
	//=====
	//-- no log as the cookies can be dissalowed by the browser
	if(!defined('SMART_APP_VISITOR_COOKIE') OR ((string)SMART_APP_VISITOR_COOKIE == '') OR ((string)SMART_APP_VISITOR_COOKIE == '')) {
		return; // session need cookies
	} //end if
	//--
	//=====
	//--
	$sf_sess_mode = 'files';
	$sf_sess_area = 'default-sess';
	$sf_sess_ns = 'unknown';
	$sf_sess_dir = 'tmp/sess';
	//--
	//=====
	if(!defined('SMART_FRAMEWORK_SESSION_PREFIX')) {
		Smart::log_warning('FATAL ERROR: Invalid Session Prefix :: SMART_FRAMEWORK_SESSION_PREFIX');
		return;
	} //end if
	if((strlen(SMART_FRAMEWORK_SESSION_PREFIX) < 3) OR (strlen(SMART_FRAMEWORK_SESSION_PREFIX) > 9)) {
		Smart::log_warning('WARNING: Session Prefix must have a length between 3 and 9 characters :: SMART_FRAMEWORK_SESSION_PREFIX');
		return;
	} //end if
	if(!preg_match('/^[a-z\-]+$/', (string)SMART_FRAMEWORK_SESSION_PREFIX)) {
		Smart::log_warning('WARNING: Session Prefix contains invalid characters :: SMART_FRAMEWORK_SESSION_PREFIX');
		return;
	} //end if
	//--
	if(!defined('SMART_FRAMEWORK_SESSION_NAME')) {
		Smart::log_warning('FATAL ERROR: Invalid Session Name :: SMART_FRAMEWORK_SESSION_NAME');
		return;
	} //end if
	if((strlen(SMART_FRAMEWORK_SESSION_NAME) < 10) OR (strlen(SMART_FRAMEWORK_SESSION_NAME) > 25)) {
		Smart::log_warning('WARNING: Session Name must have a length between 10 and 25 characters :: SMART_FRAMEWORK_SESSION_NAME');
		return;
	} //end if
	if(!preg_match('/^[_A-Za-z0-9]+$/', (string)SMART_FRAMEWORK_SESSION_NAME)) {
		Smart::log_warning('WARNING: Session Name contains invalid characters :: SMART_FRAMEWORK_SESSION_NAME');
		return;
	} //end if
	if(!SmartFrameworkSecurity::ValidateVariableName(strtolower(SMART_FRAMEWORK_SESSION_NAME))) {
		Smart::log_warning('WARNING: Session Name have an invalid value :: SMART_FRAMEWORK_SESSION_NAME');
		return;
	} //end if
	//--
	if(!SmartFileSystem::is_type_dir('tmp/sessions/')) {
		Smart::log_warning('FATAL ERROR: The Folder \'tmp/sessions/\' does not exists for use with Session !');
		return;
	} //end if
	//--
	$ini_sess_mode = (string) ini_get('session.save_handler');
	if((string)SMART_FRAMEWORK_SESSION_HANDLER === 'files') {
		if((string)$ini_sess_mode !== 'files') {
			Smart::log_warning('FATAL ERROR: The value set for SMART_FRAMEWORK_SESSION_HANDLER is set to: files / but the value found in session.save_handler is: '.$ini_sess_mode);
			return;
		} //end if
		$detected_session_mode = 'files';
	} else { // redis or another
		if(((string)$ini_sess_mode !== 'files') AND ((string)$ini_sess_mode !== 'user')) {
			return; // can be memcached ...
		} //end if
		$detected_session_mode = 'user';
	} //end if
	//--
	//=====
	//--  generate a the client private key based on it's IP and Browser
	$the_sess_client_uuid = (string) SmartUtils::unique_client_private_key(); // SHA512 key to protect session data agains forgers
	//-- a very secure approach based on a chain, derived with a secret salt from the framework security key:
	// (1) an almost unique client private key hash based on it's IP and Browser and the Unique Visitor Tracking Cookie
	// (2) an almost unique client public key hash based on it's IP and Browser (1) and Session Name
	// (3) a unique session id composed from (1) and (2)
	//-- thus the correlation between the above makes almost impossible to forge it as it locks to IP+Browser, using a public entropy cookie all encrypted with a secret key and derived and related, finally composed.
	$the_sess_hash_priv_key = (string) SmartHashCrypto::sha1($the_sess_client_uuid.'^'.SMART_APP_VISITOR_COOKIE.'^'.SMART_FRAMEWORK_SECURITY_KEY);
	$the_sess_hash_pub_key = (string) SmartHashCrypto::sha1('^'.SMART_FRAMEWORK_SESSION_NAME.'&'.$the_sess_client_uuid.'&'.$the_sess_hash_priv_key.'&'.SMART_FRAMEWORK_SECURITY_KEY.'$');
	$the_sess_id = (string) $the_sess_hash_pub_key.'-'.SmartHashCrypto::sha1('^'.$the_sess_client_uuid.'&'.$the_sess_hash_pub_key.'&'.$the_sess_hash_priv_key.'$'); // session ID combines the secret client key based on it's IP / Browser and the Client Entropy Cookie
	//--
	$sf_sess_area = (string) Smart::safe_filename((string)SMART_FRAMEWORK_SESSION_PREFIX);
	if(((string)$sf_sess_area == '') OR (!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$sf_sess_area))) {
		Smart::raise_error(
			'SESSION // FATAL ERROR: Invalid/Empty Session Area: '.$sf_sess_area
		);
		die('');
		return;
	} //end if
	$sf_sess_dpfx = (string) substr($the_sess_hash_pub_key, 0, 1).'-'.substr($the_sess_hash_priv_key, 0, 1); // this come from hexa so 3 chars are 16x16x16=4096 dirs
	//--
	if((string)$browser_os_ip_identification['bw'] == '@s#') {
		$sf_sess_ns = '@sr-'.$sf_sess_dpfx;
	} elseif((string)$browser_os_ip_identification['bw'] == 'bot') {
		$sf_sess_ns = 'r0-'.$sf_sess_dpfx; // we just need a short prefix for robots (on disk is costly for GC to keep separate folders, but of course, not so safe)
	} else {
		$sf_sess_ns = 'c-'.substr($browser_os_ip_identification['bw'],0,3).'-'.$sf_sess_dpfx; // we just need a short prefix for clients (on disk is costly for GC to keep separate folders, but of course, not so safe)
	} //end if else
	$sf_sess_ns = Smart::safe_filename($sf_sess_ns);
	if(((string)$sf_sess_ns == '') OR (!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$sf_sess_ns))) {
		Smart::raise_error(
			'SESSION // FATAL ERROR: Invalid/Empty Session NameSpace: '.$sf_sess_ns
		);
		die('');
		return;
	} //end if
	//-- by default set for files
	$sf_sess_mode = 'files';
	$sf_sess_dir = 'tmp/sessions/'.$sf_sess_area.'/'.$sf_sess_ns.'/';
	if((string)$detected_session_mode === 'user') {
		if(class_exists('SmartCustomSession')) {
			if((string)get_parent_class('SmartCustomSession') == 'SmartAbstractCustomSession') {
				$sf_sess_mode = 'user-custom';
				$sf_sess_dir = 'tmp/sessions/'.$sf_sess_area.'/'; // here the NS is saved in DB so we do not need to complicate paths
			} else {
				Smart::log_warning('SESSION INIT ERROR: Invalid Custom Session Handler. The class SmartCustomSession must be extended from class SmartAbstractCustomSession ...');
				return;
			} //end if else
		} else {
			Smart::log_warning('SESSION INIT ERROR: Custom Session Handler requires the class SmartCustomSession ...');
			return;
		} //end if
	} //end if
	$sf_sess_dir = Smart::safe_pathname($sf_sess_dir);
	SmartFileSysUtils::raise_error_if_unsafe_path($sf_sess_dir);
	//--
	if(!SmartFileSystem::is_type_dir($sf_sess_dir)) {
		SmartFileSystem::dir_create($sf_sess_dir, true); // recursive
	} //end if
	SmartFileSystem::write_if_not_exists('tmp/sessions/'.$sf_sess_area.'/'.'index.html', '');
	//=====
	//--
	@session_save_path($sf_sess_dir);
	@session_cache_limiter('nocache');
	//--
	$the_name_of_session = (string) SMART_FRAMEWORK_SESSION_NAME.'__Key_'.$the_sess_hash_pub_key; // protect session name data agains forgers
	//--
	@session_id((string)$the_sess_id);
	@session_name((string)$the_name_of_session);
	//--
	$tmp_exp_seconds = 0;
	if(defined('SMART_FRAMEWORK_SESSION_LIFETIME')) {
		$tmp_exp_seconds = (int) SMART_FRAMEWORK_SESSION_LIFETIME;
		if($tmp_exp_seconds < 0) {
			$tmp_exp_seconds = 0;
		} //end if
	} //end if
	if(defined('SMART_FRAMEWORK_SESSION_DOMAIN') AND ((string)SMART_FRAMEWORK_SESSION_DOMAIN != '')) {
		if((string)SMART_FRAMEWORK_SESSION_DOMAIN == '*') {
			$cookie_domain = (string) SmartUtils::get_server_current_basedomain_name();
		} else {
			$cookie_domain = (string) SMART_FRAMEWORK_SESSION_DOMAIN;
		} //end if
		@session_set_cookie_params((int)$tmp_exp_seconds, '/', (string)$cookie_domain); // session cookie expire, the path and domain
	} else {
		@session_set_cookie_params((int)$tmp_exp_seconds, '/'); // session cookie expire and the path
	} // end if
	//-- be sure that session_write_close() is executed at the end of script if script if die premature and before pgsql shutdown register in the case of DB sessions
	register_shutdown_function('session_write_close');
	//-- handle custom session handler
	if((string)$sf_sess_mode === 'user-custom') {
		//--
		$sess_obj = new SmartCustomSession();
		$sess_obj->sess_area = (string) $sf_sess_area;
		$sess_obj->sess_ns = (string) $sf_sess_ns;
		$sess_obj->sess_expire = (int) $tmp_exp_seconds;
		//--
		session_set_save_handler(
			array($sess_obj, 'open'),
			array($sess_obj, 'close'),
			array($sess_obj, 'read'),
			array($sess_obj, 'write'),
			array($sess_obj, 'destroy'),
			array($sess_obj, 'gc')
		);
		//--
	} //end if else
	//-- start session
	@session_start();
	//--
	if((Smart::array_size($_SESSION) <= 0) OR ((string)$_SESSION['visitor_UUID'] != (string)SMART_APP_VISITOR_COOKIE) OR ((string)$_SESSION['uniqbrowser_ID'] != (string)$the_sess_client_uuid) OR (strlen($_SESSION['session_ID']) < 32)) {
		//--
		if(Smart::array_size($_SESSION) > 0) {
			//--
			if((string)$_SESSION['visitor_UUID'] != (string)SMART_APP_VISITOR_COOKIE) {
				Smart::log_warning('Session Reset: Unique Visitor UUID does not match ...');
			} //end if
			//--
			if((string)$_SESSION['uniqbrowser_ID'] != (string)$the_sess_client_uuid) {
				Smart::log_warning('Session Reset: Unique Browser ID does not match ...');
			} //end if
			//--
			if(strlen($_SESSION['session_ID']) < 32) {
				Smart::log_warning('Session Reset: Session ID must be at least 32 characters ...');
			} //end if
			//--
		} //end if
		//--
		$_SESSION = array(); // reset it
		//--
		$_SESSION['SoftwareFramework_VERSION'] 		= (string) SMART_FRAMEWORK_VERSION; 							// software version
		$_SESSION['SoftwareFramework_SessionMode'] 	= (string) $sf_sess_mode.':'.SMART_FRAMEWORK_SESSION_HANDLER; 	// session mode
		$_SESSION['website_ID'] 					= (string) SMART_SOFTWARE_NAMESPACE; 							// the website ID
		$_SESSION['visitor_UUID'] 					= (string) SMART_APP_VISITOR_COOKIE; 							// the visitor UUID
		$_SESSION['visit_COUNTER'] 					= (int)    0; 													// the session visit counter
		$_SESSION['session_AREA'] 					= (string) $sf_sess_area; 										// session area
		$_SESSION['session_NS'] 					= (string) $sf_sess_ns; 										// session namespace
		$_SESSION['session_ID'] 					= (string) @session_id(); 										// read current session ID
		$_SESSION['session_STARTED'] 				= (string) date('Y-m-d H:i:s O'); 								// read current session ID
		//--
	} //end if
	//--
	$_SESSION['visit_COUNTER'] += 1; // increment visit counter
	//--
	if(!isset($_SESSION['visitor_UUID'])) {
		$_SESSION['visitor_UUID'] = (string) SMART_APP_VISITOR_COOKIE; // set it only once
	} //end if
	//--
	if(!isset($_SESSION['uniqbrowser_ID'])) {
		$_SESSION['uniqbrowser_ID'] = (string) $the_sess_client_uuid; // set it only once
	} //end if
	//--
	$_SESSION['SmartFramework__Browser__Identification__Data'] = (array) $browser_os_ip_identification; // rewrite it each time
	//--
	self::$active = (int) time(); // successfuly started
	//--
} //END FUNCTION
//==================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Abstract Class Smart Custom Session
 * This is the abstract for extending the class SmartCustomSession
 *
 * @version 	v.20190605
 * @package 	development:Application
 */
abstract class SmartAbstractCustomSession {

	// -> ABSTRACT

	// NOTICE: This object MUST NOT CONTAIN OTHER FUNCTIONS BECAUSE WILL NOT WORK !!!


	//-- PUBLIC VARS
	public $sess_area;
	public $sess_ns;
	public $sess_expire;
	//--


	//==================================================
	final public function __construct() {
		//--
		// constructor (do not use it, this is not safe to use because changes between PHP versions ...)
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	// TO BE EXTENDED
	public function open() {
		//--
		return true;
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	// TO BE EXTENDED
	public function close() {
		//--
		return true;
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	// TO BE EXTENDED
	public function write($id, $data) {
		//--
		return true;
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	// TO BE EXTENDED
	public function read($id) {
		//--
		return (string) '';
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	// TO BE EXTENDED
	public function destroy($id) {
		//--
		return true;
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	// TO BE EXTENDED
	public function gc($lifetime) {
		//--
		return true;
		//--
	} //END FUNCTION
	//==================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>