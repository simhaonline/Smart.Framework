<?php
// [@[#[!NO-STRIP!]#]@]
// [Smart.Framework / CFG - SETTINGS / ADMIN]
// r.7.2.1 / smart.framework.v.7.2

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
$configs['app']['admin-template-path'] 				= 'default';				// default admin template folder from etc/templates/
$configs['app']['admin-template-file'] 				= 'template.htm';			// default admin template file
//---------------------------------------

//-- sample auth credentials for the admin area (admin.php) ; change them !!!
define('APP_AUTH_ADMIN_USERNAME', 'admin');
define('APP_AUTH_ADMIN_PASSWORD', 'the-pass');
//define('APP_AUTH_ADMIN_ENCRYPTED_PRIVKEY', ''); // this is *optional* and used just by the Simple Admin Auth (hardcoded account) and may be required just for some extra modules
/* uncomment these for advanced authentication (must switch from simple to advanced authentication in modules/app/app-auth-admin.inc.php)
define('APP_AUTH_PRIVILEGES', '<admin>,<custom-priv1>,<custom-priv...>,<custom-privN>');
$configs['app-auth']['adm-namespaces'] = [
	'Admins Manager' => 'admin.php?page=auth-admins.manager.stml',
	// ...
];
*/
//--

// end of php code
