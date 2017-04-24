<?php
// [APP - Authenticate / Index]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.1.2 r.2017.04.11 / smart.framework.v.3.1

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// App Authenticate Middleware / Index Area Overall Authentication (index.php)
// This file must NOT USE Namespaces.
// The functionality of this Middleware is to:
//	* ask for overall authentication for Index Area and set if successful
//	* if not authenticated, display the login form
//======================================================
// This code will be loaded into the App Boostrap automatically, to provide the Authentication for the index.php ...
// By default this code does not contain any classes or functions.
// If you include classes or functions here they must be called to run here as the app boostrap just include this file at runtime
//======================================================

//-------------------------------------------
// This file can be customized as you need.
// Generally the Index Area is PUBLIC thus will not need an overall authentication.
// But in the case you need it, see an example here: modules/app/app-auth-admin.inc.php
//-------------------------------------------

// end of php code
?>