<?php
// [LIB - SmartFramework / Session Management]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.5')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Session
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
	die('ERROR: PHP Session Module is required for the SmartFramework');
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
 * @version 	v.170411
 * @package 	Application
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
 * returns MIXED $_SESSION or $_SESSION[$yvariable]
 */
public static function get($yvariable=null) {
	//--
	self::start(); // start session if not already started
	//--
	if($yvariable === null) {
		return (array) $_SESSION;
	} else {
		return $_SESSION[(string)$yvariable];
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
 */
public static function set($yvariable, $yvalue) {
	//--
	self::start(); // start session if not already started
	//--
	if($yvalue === null) {
		unset($_SESSION[(string)$yvariable]);
	} else {
		$_SESSION[(string)$yvariable] = $yvalue;
	} //end if else
	//--
} //END FUNCTION
//==================================================


//==================================================
/**
 * Start the Session on request
 *
 */
public static function start() {
	//=====
	//--
	if(self::$started !== false) {
		return; // avoid start session if already started ...
	} //end if
	self::$started = true; // avoid run start again
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
		if(SMART_FRAMEWORK_SESSION_ROBOTS !== true) {
			return; // in this case start no session for robots (as they do not need to share info between many visits)
		} //end if
	} //end if
	//--
	//=====
	//-- no log as the cookies can be dissalowed by the browser
	if((string)SMART_APP_VISITOR_COOKIE == '') {
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
	if(!defined('SMART_FRAMEWORK_SESSION_LIFETIME')) {
		Smart::log_warning('FATAL ERROR: Invalid Session GC Lifetime :: SMART_FRAMEWORK_SESSION_LIFETIME');
		return;
	} //end if
	if(!is_int(SMART_FRAMEWORK_SESSION_LIFETIME)) {
		Smart::log_warning('Invalid INIT constant value for SMART_FRAMEWORK_SESSION_LIFETIME');
		return;
	} //end if
	//--
	if(!is_dir('tmp/sessions/')) {
		Smart::log_warning('FATAL ERROR: The Folder \'tmp/sessions/\' does not exists for use with Session !');
		return;
	} //end if
	//--
	$detected_session_mode = (string) ini_get('session.save_handler');
	if((string)$detected_session_mode === 'files') {
		if((string)SMART_FRAMEWORK_SESSION_HANDLER !== 'files') {
			Smart::log_warning('FATAL ERROR: The value set for SMART_FRAMEWORK_SESSION_HANDLER is not set to: files / but the value found in session.save_handler is: '.$detected_session_mode);
			return;
		} //end if
	} elseif((string)$detected_session_mode === 'user') {
		if((string)SMART_FRAMEWORK_SESSION_HANDLER === 'files') {
			Smart::log_warning('FATAL ERROR: The value set for SMART_FRAMEWORK_SESSION_HANDLER is set to: files / but the value found in session.save_handler is: '.$detected_session_mode);
			return;
		} //end if
	} else {
		Smart::log_warning('FATAL ERROR: The value set for session.save_handler must be set to one of these modes: files or user');
		return;
	} //end if
	//--
	//=====
	//--  generate a the client private key based on it's IP and Browser
	$the_sess_client_uuid = SmartUtils::unique_client_private_key(); // SHA512 key to protect session data agains forgers
	//-- a very secure approach based on a chain, derived with a secret salt from the framework security key:
	// (1) an almost unique client private key lock based on it's IP and Browser
	// (2) an entropy derived from the client random cookie combined with the (1)
	// (3) a unique session name suffix derived from (1) and (2)
	// (4) a unique session id composed from (1) and (2)
	//-- thus the correlation between the random public client cookie, the session name suffix and the session id makes impossible to forge it as it locks to IP+Browser, using a public entropy cookie all encrypted with a secret key and derived and related, finally composed.
	$the_sess_client_lock = SmartHashCrypto::sha1(SMART_FRAMEWORK_SECURITY_KEY.'#'.$the_sess_client_uuid);
	$the_sess_client_entropy = SmartHashCrypto::sha1(SMART_APP_VISITOR_COOKIE.'*'.$the_sess_client_uuid.'%'.SMART_FRAMEWORK_SECURITY_KEY);
	$the_sess_nsuffix = SmartHashCrypto::sha1($the_sess_client_uuid.':'.SMART_FRAMEWORK_SECURITY_KEY.'^'.$the_sess_client_entropy.'+'.$the_sess_client_lock.'$'.SMART_APP_VISITOR_COOKIE);
	$the_sess_id = $the_sess_client_entropy.'-'.$the_sess_client_lock; // session ID combines the secret client key based on it's IP / Browser and the Client Entropy Cookie
	//--
	$sf_sess_area = Smart::safe_filename((string)SMART_FRAMEWORK_SESSION_PREFIX);
	$sf_sess_dpfx = substr($the_sess_client_entropy, 0, 1).'-'.substr($the_sess_client_lock, 0, 1); // this come from hexa so 3 chars are 16x16x16=4096 dirs
	//--
	if((string)$browser_os_ip_identification['bw'] == '@s#') {
		$sf_sess_ns = '@sr-'.$sf_sess_dpfx;
	} elseif((string)$browser_os_ip_identification['bw'] == 'bot') {
		$sf_sess_ns = 'r0-'.$sf_sess_dpfx; // we just need a short prefix for robots (on disk is costly for GC to keep separate folders, but of course, not so safe)
	} else {
		$sf_sess_ns = 'c-'.substr($browser_os_ip_identification['bw'],0,3).'-'.$sf_sess_dpfx; // we just need a short prefix for clients (on disk is costly for GC to keep separate folders, but of course, not so safe)
	} //end if else
	$sf_sess_ns = Smart::safe_filename($sf_sess_ns);
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
	//--
	if(!is_dir($sf_sess_dir)) {
		SmartFileSystem::dir_recursive_create($sf_sess_dir);
	} //end if
	SmartFileSystem::write_if_not_exists('tmp/sessions/'.$sf_sess_area.'/'.'index.html', '');
	//=====
	//--
	@session_save_path($sf_sess_dir);
	@session_cache_limiter('nocache');
	//--
	$the_name_of_session = (string) SMART_FRAMEWORK_SESSION_NAME.'__Key_'.$the_sess_nsuffix; // protect session name data agains forgers
	//--
	@session_id((string)$the_sess_id);
	@session_name((string)$the_name_of_session);
	//--
	$tmp_exp_seconds = Smart::format_number_int(SMART_FRAMEWORK_SESSION_LIFETIME, '+');
	if($tmp_exp_seconds > 0) {
		@session_set_cookie_params((int)$tmp_exp_seconds, '/'); // session cookie expire and the path
	} // end if
	//-- be sure that session_write_close() is executed at the end of script if script if die('') premature and before pgsql shutdown register in the case of DB sessions
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
	if(((string)$_SESSION['SoftwareFramework_VERSION'] != (string)SMART_FRAMEWORK_VERSION) OR ((string)$_SESSION['website_ID'] != (string)SMART_SOFTWARE_NAMESPACE) OR (strlen($_SESSION['session_ID']) < 32)) {
		//--
		$_SESSION['SoftwareFramework_VERSION'] = (string) SMART_FRAMEWORK_VERSION; // software version
		$_SESSION['SoftwareFramework_SessionMode'] = (string) $sf_sess_mode; // session mode
		$_SESSION['website_ID'] = (string) SMART_SOFTWARE_NAMESPACE; // the website ID
		$_SESSION['uniqbrowser_ID'] = (string) $the_sess_client_uuid; // a true unique browser ID (this is a protection against sessionID forgers)
		$_SESSION['session_AREA'] = (string) $sf_sess_area; // session area
		$_SESSION['session_NS'] = (string) $sf_sess_ns; // session namespace
		$_SESSION['session_ID'] = (string) @session_id(); // read current session ID
		$_SESSION['session_STARTED'] = (string) date('Y-m-d H:i:s O'); // read current session ID
		//--
	} //end if
	//--
	if(!isset($_SESSION['visit_COUNTER'])) {
		$_SESSION['visit_COUNTER'] = 1;
	} else {
		$_SESSION['visit_COUNTER'] += 1;
	} //end if else
	//--
	$_SESSION['SmartFramework__Browser__Identification__Data'] = (array) $browser_os_ip_identification;
	//--
	if((string)$_SESSION['uniqbrowser_ID'] != (string)$the_sess_client_uuid) { // we need at least a md5 session
		//-- log, then unset old session (these are not well tested ...)
		Smart::log_notice('Session Security Breakpoint :: Session-BrowserUniqueID = '.$_SESSION['uniqbrowser_ID']."\n".'SessionSecurityUniqueID = '.$the_sess_client_uuid."\n".'Browser Ident = '.$browser_os_ip_identification['bw']."\n".'Cookies = '.print_r($_COOKIE,1)."\n".'SessID = '.$_SESSION['session_ID']."\n".'ClientIP = '.SmartUtils::get_ip_client().' @ '.$_SERVER['REMOTE_ADDR']."\n".'UserAgent = '.$_SERVER['HTTP_USER_AGENT']);
		$_SESSION = array(); // reset it
		//-- unset the cookie (from this below is tested)
		@setcookie($the_name_of_session, 'EXPIRED', 1, '/');
		//-- stop execution with message
		Smart::raise_error(
			'SESSION // SECURITY BREAK POINT: Possible Session Forgery Detected ...',
			'SESSION // SECURITY BREAK POINT: Possible Session Forgery Detected ! Please refresh the page ... A new session will be assigned ! If you are not trying to forge another user\' session this situation can occur also if you are behind a proxy and some of your navigation parameters has been changed ! If this problem persist try to restart your browser or use other browser. If still persist, contact the website administrator' // msg to display
		);
		die(''); // just in case
		return; // or is better to silent discard it ?
		//--
	} //end if
	//--
	self::$active = time(); // successfuly started
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
 *
 * @access 		private
 * @internal
 *
 */
abstract class SmartAbstractCustomSession {

	// -> ABSTRACT
	// v.170411

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