<?php
// [@[#[!NO-STRIP!]#]@]
// [CFG - SETTINGS / ADMIN]
// v.2.3.1.8 r.2016.04.22 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//--------------------------------------- Templates and Home Page
$configs['app']['admin-domain'] 					= 'localhost.local'; 		// admin domain as yourdomain.ext
$configs['app']['admin-home'] 						= 'samples.toolkit';		// admin home page action
$configs['app']['admin-default-module'] 			= 'samples';				// admin default module
$configs['app']['admin-template-path'] 				= 'default';				// default admin templates folder from etc/templates/
$configs['app']['admin-template-file'] 				= 'template.htm';			// default admin template file
$configs['app']['admin-template-modal-popup-file'] 	= 'template-modal.htm';		// default admin modal/popup template file
//---------------------------------------

// end of php code
?>