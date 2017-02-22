<?php
// [APP - Authenticate / Index]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.7 r.2017.02.22 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// App Authenticate Middleware / Admin Area (index.php)
// This file must NOT USE Namespaces.
// The functionality of this Middleware is to:
//	* ask for authentication and set if successful
//	* if not authenticated, display the login form
//======================================================
// This code will be loaded into the App Boostrap automatically, to provide the Authentication for the index.php ...
// By default this code does not contain any classes or functions.
// If you include classes or functions here they must be called to run here as the app boostrap just include this file at runtime
//======================================================

//-------------------------------------------
// This file can be customized as you need.
//-------------------------------------------
// See an example here: modules/app/app-auth-admin.inc.php
//-------------------------------------------

// end of php code
?>