<?php
// [APP - Authenticate / Admin]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.7 r.2017.02.22 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// App Authenticate Middleware / Admin Area (admin.php)
// This file must NOT USE Namespaces.
// The functionality of this Middleware is to:
//	* ask for authentication and set if successful
//	* if not authenticated, display the login form
//======================================================
// This code will be loaded into the App Boostrap automatically, to provide the Authentication for the admin.php ...
// By default this code does not contain any classes or functions.
// If you include classes or functions here they must be called to run here as the app boostrap just include this file at runtime
//======================================================

//-------------------------------------------
// This file can be customized as you need.
//-------------------------------------------
// NOTICE: As this is just a sample will use a fixed authentication with:
// 		username = admin 	(ADMIN_AREA_USER 		set in config-admin.php)
// 		password = pass 	(ADMIN_AREA_PASSWORD 	set in config-admin.php)
// This sample can be extended to read the authentication from a database or to use session in combination with SmartAuth.
// The best way to integrate with framework's authentication is using the SmartAuth:: object.
//-------------------------------------------
// v.170208 / Sample Auth based on Basic HTTP Authentication (for Admin Area, overall)
if(!defined('ADMIN_AREA_USER') OR !defined('ADMIN_AREA_PASSWORD')) {
	http_response_code(403);
	die(SmartComponents::http_message_403_forbidden('Authentication ADMIN_AREA_USER / ADMIN_AREA_PASSWORD not set in config-admin.php ...'));
} //end if
if(((string)$_SERVER['PHP_AUTH_USER'] == (string)ADMIN_AREA_USER) AND ((string)$_SERVER['PHP_AUTH_PW'] == (string)ADMIN_AREA_PASSWORD)) {
	//-- OK, loggen in
	SmartAuth::set_login_data(
		(string)$_SERVER['PHP_AUTH_USER'], 		// this should be always the user login ID (login user name)
		(string)$_SERVER['PHP_AUTH_USER'], 		// username alias to display (in this case is the same as the login ID, but may be different)
		'admin@smart-framework.test', 			// user email * Optional * (this may be also redundant if the login ID is actually the user email)
		'Test Admin', 							// user full name (Title + ' ' + First Name + ' ' + Last name) * Optional *
		array('admin','superadmin'), 			// login privileges * Optional *
		0, 										// quota * Optional *
		array( // metadata
			'title' => 'Mr.',
			'name_f' => 'Test',
			'name_l' => 'Admin'
		),
		'SMART-FRAMEWORK.TEST', // realm
		'HTTP-BASIC',
		(string)$_SERVER['PHP_AUTH_PW']
	);
	//--
} else {
	//-- NOT OK, display the Login Form and Exit
	header('WWW-Authenticate: Basic realm="Administration Area"');
	http_response_code(401);
	die(SmartComponents::http_message_401_unauthorized('Authorization Required<br>Login Failed. Either you supplied the wrong credentials or your browser doesn\'t understand how to supply the credentials required.'));
	//--
} //end if
//-------------------------------------------

// end of php code
?>