<?php
// [APP - Authenticate / Admin]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// App Authenticate Middleware / Admin Area Overall Authentication (admin.php)
// This file must NOT USE Namespaces.
// The functionality of this Middleware is to:
//	* ask for overall authentication for Admin Area and set if successful
//	* if not authenticated, display the login form
//======================================================
// This code will be loaded into the App Boostrap automatically, to provide the Authentication for the admin.php ...
// By default this code does not contain any classes or functions.
// If you include classes or functions here they must be called to run here as the app boostrap just include this file at runtime
//======================================================

//-------------------------------------------
// This file can be customized as you need.
// It will set an overall authentication for the Admin Area.
// NOTICE: As this is just an example it uses a fixed authentication with:
// 		username = admin 	(ADMIN_AREA_USER 		as constant, set in config-admin.php)
// 		password = pass 	(ADMIN_AREA_PASSWORD 	as constant, set in config-admin.php)
// This sample can be extended to read the authentication from a database or to use session in combination with SmartAuth:: object.
// This is the best way to integrate with framework's authentication system by using SmartAuth:: object.
//-------------------------------------------
// v.181115 / below there is a sample Authentication (HTTP Basic) for Admin Area, overall with a fixed username and password
// if you need a more advanced Authentication solution with many accounts, see the Smart.Framework.Modules/mod-auth-admins/doc/README.md
//-------------------------------------------
if(headers_sent()) {
	//--
	http_response_code(500);
	die(SmartComponents::http_message_403_forbidden('Authentication Failed, Headers Already Sent ...'));
	//--
} //end if
if(!defined('ADMIN_AREA_USER') OR !defined('ADMIN_AREA_PASSWORD')) {
	//--
	http_response_code(403);
	die(SmartComponents::http_message_403_forbidden('Authentication ADMIN_AREA_USER / ADMIN_AREA_PASSWORD not set ...')); // must be set in config-admin.php
	//--
} elseif((string)trim((string)ADMIN_AREA_USER) == '') {
	//--
	http_response_code(500);
	die(SmartComponents::http_message_500_internalerror('Authentication ADMIN_AREA_USER was set but is Empty ...'));
	//--
} elseif((string)trim((string)ADMIN_AREA_PASSWORD) == '') {
	//--
	http_response_code(500);
	die(SmartComponents::http_message_500_internalerror('Authentication ADMIN_AREA_PASSWORD was set but is Empty ...'));
	//--
} //end if
if(((string)$_SERVER['PHP_AUTH_USER'] == (string)ADMIN_AREA_USER) AND ((string)$_SERVER['PHP_AUTH_PW'] == (string)ADMIN_AREA_PASSWORD)) {
	//-- OK, loggen in
	SmartAuth::set_login_data(
		(string) $_SERVER['PHP_AUTH_USER'], 	// this should be always the user login ID (login user name)
		(string) $_SERVER['PHP_AUTH_USER'], 	// username alias (in this case is the same as the login ID, but may be different)
		'admin@smart-framework.test', 			// user email * Optional * (this may be also redundant if the login ID is actually the user email)
		'Test Admin', 							// user full name (Title + ' ' + First Name + ' ' + Last name) * Optional *
		['admin', 'superadmin'], 				// login privileges * Optional *
		0, 										// quota * Optional *
		[ // metadata
			'title' => 'Mr.',
			'name_f' => 'Test',
			'name_l' => 'Admin'
		],
		'SMART-FRAMEWORK.TEST', // realm
		'HTTP-BASIC',
		(string) $_SERVER['PHP_AUTH_PW']
	);
	//--
} else {
	//-- NOT OK, display the Login Form and Exit
	header('WWW-Authenticate: Basic realm="Administration Area"');
	http_response_code(401);
	die(SmartComponents::http_message_401_unauthorized('Authorization Required', SmartComponents::operation_notice('Login Failed. Either you supplied the wrong credentials or your browser doesn\'t understand how to supply the credentials required.', '100%')));
	//--
} //end if
//-------------------------------------------

// end of php code
?>