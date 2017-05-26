<?php
// [APP - Custom Bootstrap]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.1 r.2017.05.12 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// This file can be customized as you need.
//======================================================
// App Custom Bootstrap Middleware / Shared (for both: index.php / admin.php)
// This code will be loaded into the App Boostrap automatically.
// By default this code does not contain any classes or functions.
// If you include classes or functions here they must be called to run here as the app boostrap just include this file at runtime
//======================================================
// This file must NOT USE Namespaces.
// The functionality of this Middleware is to:
// 	* validate minimal framework requirements
//	* define extra auto-loaders for namespaces / classes
//	* run custom code at app bootstrap like:
// 		- include here some custom code to be executed at the App.Boostrap level before any other code is executed, even before session starts
// 		- define here a Custom Session using: class SmartCustomSession extends SmartAbstractCustomSession {}
// 		- overall start of session (by default session starts just when needed)
// 		- pre-connect to a DB server at boot(strap) time (by default the connections start just when needed)
// 		- ... other purposes ...
//======================================================


define('SMART_APP_MODULES_RELEASE', 'r.2017.05.26'); // this can be used for tracking changes to custom app modules
define('SMART_APP_MODULES_MIN_FRAMEWORK_VER', 'v.3.5.1'); // this must be used to validate the required minimum framework version

if(version_compare((string)SMART_FRAMEWORK_RELEASE_TAGVERSION, (string)SMART_APP_MODULES_MIN_FRAMEWORK_VER) < 0) {
	die('The Custom App Modules require the Smart.Framework '.SMART_APP_MODULES_MIN_FRAMEWORK_VER.' or later !');
} //end if

// # Here can be loaded the Smart.Framework extra libs package from: https://github.com/unix-world/Smart.Framework.Modules
//require_once('modules/smart-extra-libs/autoload.php'); // autoload for Smart.Framework.Modules / (Smart) Extra Libs

// # Here can be loaded extra vendor libs with or without autoloaders
// require_once(__DIR__.'/../../../vendor/autoload.php'); // PSR standard namespace/class loader(s)

// end of php code
?>