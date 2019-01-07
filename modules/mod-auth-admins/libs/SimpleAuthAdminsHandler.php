<?php
// Class: \SmartModExtLib\AuthAdmins\SimpleAuthAdminsHandler
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

namespace SmartModExtLib\AuthAdmins;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//# Depends on:
//	* SmartAuth
//	* SmartUtils
//	* SmartComponents
//	* SmartFrameworkSecurity

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

//--
if(headers_sent()) {
	http_response_code(500);
	die(\SmartComponents::http_error_message('500 Internal Server Error', 'Authentication Failed, Headers Already Sent ...'));
} //end if
//--


/**
 * Simple Auth Admins Handler
 * This class provide a very simple authentication for admin area (admin.php) using a single account with username/password set in config-admin.php
 *
 * Required constants: APP_AUTH_ADMIN_USERNAME, APP_AUTH_ADMIN_PASSWORD (must be set in set in config-admin.php)
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20190107
 *
 */
final class SimpleAuthAdminsHandler {

	// ::

	//================================================================
	public static function Authenticate($enforce_ssl=false) {
		//--
		if(!defined('SMART_FRAMEWORK_ADMIN_AREA') OR (SMART_FRAMEWORK_ADMIN_AREA !== true)) {
			http_response_code(500);
			die(\SmartComponents::http_message_500_internalerror('Authentication system is designed for admin area only ...'));
		} //end if
		//--
		if($enforce_ssl === true) {
			if((string)\SmartUtils::get_server_current_protocol() !== 'https://') {
				http_response_code(403);
				die(\SmartComponents::http_error_message('This Web Area require SSL', 'You have to switch from http:// to https:// in order to use this Web Area'));
			} //end if
		} //end if
		//--
		if(!defined('APP_AUTH_ADMIN_USERNAME') OR !defined('APP_AUTH_ADMIN_PASSWORD')) {
			//--
			http_response_code(503);
			die(\SmartComponents::http_message_503_serviceunavailable('Authentication APP_AUTH_ADMIN_USERNAME / APP_AUTH_ADMIN_PASSWORD not set in config ...')); // must be set in config-admin.php
			//--
		} elseif((string)trim((string)APP_AUTH_ADMIN_USERNAME) == '') {
			//--
			http_response_code(503);
			die(\SmartComponents::http_message_503_serviceunavailable('Authentication APP_AUTH_ADMIN_USERNAME was set but is Empty ...'));
			//--
		} elseif((string)trim((string)APP_AUTH_ADMIN_PASSWORD) == '') {
			//--
			http_response_code(503);
			die(\SmartComponents::http_message_503_serviceunavailable('Authentication APP_AUTH_ADMIN_PASSWORD was set but is Empty ...'));
			//--
		} //end if
		//--
		if(((string)$_SERVER['PHP_AUTH_USER'] === (string)APP_AUTH_ADMIN_USERNAME) AND ((string)$_SERVER['PHP_AUTH_PW'] === (string)APP_AUTH_ADMIN_PASSWORD)) {
			//-- OK, loggen in
			$privileges = '<superadmin>,<admin>';
			if(defined('APP_AUTH_PRIVILEGES')) {
				$privileges .= ','.APP_AUTH_PRIVILEGES;
			} //end if
			$privileges = (array) \Smart::list_to_array(
				(string) $privileges,
				true
			);
			//--
			\SmartAuth::set_login_data(
				(string) $_SERVER['PHP_AUTH_USER'], 	// this should be always the user login ID (login user name)
				(string) $_SERVER['PHP_AUTH_USER'], 	// username alias (in this case is the same as the login ID, but may be different)
				'admin@smart.framework', 				// user email * Optional * (this may be also redundant if the login ID is actually the user email)
				'Super Admin', 							// user full name (Title + ' ' + First Name + ' ' + Last name) * Optional *
				(array) $privileges, 					// login privileges * Optional *
				0, 										// quota * Optional *
				[ // metadata
					'title' => 'Mr.',
					'name_f' => 'Super',
					'name_l' => 'Admin'
				],
				'ADMINS-AREA-SIMPLE', // realm
				'HTTP-BASIC', // method
				(string) $_SERVER['PHP_AUTH_PW'] // safe store password
			);
			//--
		} else {
			//-- NOT OK, display the Login Form and Exit
			header('WWW-Authenticate: Basic realm="Private Area"');
			http_response_code(401);
			die(\SmartComponents::http_message_401_unauthorized('Authorization Required', \SmartComponents::operation_notice('Login Failed. Either you supplied the wrong credentials or your browser doesn\'t understand how to supply the credentials required.', '100%')));
			//--
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