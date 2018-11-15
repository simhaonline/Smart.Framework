<?php
// [@[#[!NO-STRIP!]#]@]
// [CFG - SETTINGS / ADMIN]
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//--------------------------------------- Templates and Home Page
$configs['app']['admin-domain'] 					= 'localhost.local'; 		// admin domain as yourdomain.ext
$configs['app']['admin-home'] 						= 'samples.welcome';		// admin home page action
$configs['app']['admin-default-module'] 			= 'samples';				// admin default module
$configs['app']['admin-template-path'] 				= 'default';				// default admin templates folder from etc/templates/
$configs['app']['admin-template-file'] 				= 'template.htm';			// default admin template file
//---------------------------------------

//-- auth credentials for the admin area (admin.php): Sample Authentication only !!!
define('ADMIN_AREA_USER', 'admin');
define('ADMIN_AREA_PASSWORD', 'pass');
//--

// end of php code
?>