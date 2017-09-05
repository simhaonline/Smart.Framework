<?php
// [@[#[!NO-STRIP!]#]@]
// [CFG - SETTINGS / INDEX]
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//--------------------------------------- Templates and Home Page
$configs['app']['index-domain'] 					= 'localhost.local'; 		// index domain as yourdomain.ext
$configs['app']['index-home'] 						= 'samples.welcome';		// index home page action
$configs['app']['index-default-module'] 			= 'samples'; 				// index default module ; check also SMART_FRAMEWORK_SEMANTIC_URL_SKIP_MODULE
$configs['app']['index-template-path'] 				= 'default';				// default index templates folder from etc/templates/
$configs['app']['index-template-file'] 				= 'template.htm';			// default index template file
//---------------------------------------

// end of php code
?>