<?php
// [@[#[!NO-STRIP!]#]@]
// [CFG - SETTINGS / INDEX]
// v.2.3.1.9 r.2016.04.29 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//--------------------------------------- Templates and Home Page
$configs['app']['index-domain'] 					= 'localhost.local'; 		// index domain as yourdomain.ext
$configs['app']['index-home'] 						= 'samples.toolkit';		// index home page action
$configs['app']['index-default-module'] 			= SMART_FRAMEWORK_SEMANTIC_URL_SKIP_MODULE; // index default module
$configs['app']['index-template-path'] 				= 'default';				// default index templates folder from etc/templates/
$configs['app']['index-template-file'] 				= 'template.htm';			// default index template file
$configs['app']['index-template-modal-popup-file'] 	= 'template-modal.htm';		// default index modal/popup template file
//---------------------------------------

// end of php code
?>