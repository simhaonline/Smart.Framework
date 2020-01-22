<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/404
// Route: ?/page/samples.404 (?page=samples.404)
// (c) 2006-2020 unix-world.org - all rights reserved
// r.5.7.2 / smart.framework.v.5.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'INDEX'); // INDEX, ADMIN, SHARED

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends \SmartModExtLib\Samples\ErrorXxx {

	protected $errcode = 404;
	protected $errtext = 'Not Found';

	// the Run() is inherited from \SmartModExtLib\Samples\ErrorXxx->Run()

} //END CLASS

// end of php code
