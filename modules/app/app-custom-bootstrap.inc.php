<?php
// [APP - Custom Bootstrap]
// (c) 2006-2015 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// App Custom Bootstrap Middleware / Shared (for both: index.php / admin.php)
// v.2015.12.05
// This file must NOT USE Namespaces.
// The functionality of this Middleware is to:
//	* run custom code at app bootstrap
//======================================================
// This code will be loaded into the App Boostrap automatically.
// By default this code does not contain any classes or functions.
// If you include classes or functions here they must be called to run here as the app boostrap just include this file at runtime
//======================================================

//-------------------------------------------
// This file can be customized as you need.
// You can define extra auto-loaders for namespaces / classes
// You can define here a Custom Session using: class SmartCustomSession extends SmartAbstractCustomSession {}
// You can include custom code to be executed at the App.Boostrap level before any other code is executed, even before session starts
// It can be used by example for:
// 		* overall start of session (by default session starts just when needed)
// 		* pre-connect to a DB server at boot(strap) time (by default the connections start just when needed)
// 		... other purposes ...
//-------------------------------------------

define('SMART_APP_MODULES_RELEASE', 'r.2016.04.07'); // this must be used for tracking changes to custom app modules

// # Here can be loaded the Smart.Framework extra libs package from: https://github.com/unix-world/Smart.Framework.Modules
//require_once('modules/smart-extra-libs/autoload.php'); // autoload for Smart.Framework.Modules / (Smart) Extra Libs

// # Here can be loaded extra vendor libs with / without autoloaders
// require_once(__DIR__.'/../../../vendor/autoload.php'); // PSR standard namespace/class loader(s)

// end of php code
?>