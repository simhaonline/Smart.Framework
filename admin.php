<?php
// SmartFramework / Runtime / Admin
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.3.3 r.2016.07.08 / smart.framework.v.2.3

//##### WARNING: #####
// Changing the code below is on your own risk and may lead to severe disrupts in the execution of this software !
//####################

//==
//--
ini_set('display_errors', '1'); 											// temporary enable this to display bootstrap errors if any ; will be managed later by Smart Error Handler
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED); 			// on bootstrap show real-time errors (sync with Smart Error Handler)
//--
define('SMART_FRAMEWORK_SESSION_PREFIX', 	'adm-sess'); 					// session prefix
define('SMART_FRAMEWORK_ADMIN_AREA', 		true); 							// run app in private/admin mode
define('SMART_FRAMEWORK_RUNTIME_READY', 	microtime(true)); 				// semaphore, runtime can execute scripts
define('SMART_FRAMEWORK_APP_REQUEST', 		'lib/run/app-request.php'); 	// App Request Script
define('SMART_FRAMEWORK_APP_BOOTSTRAP', 	'lib/run/app-bootstrap.php'); 	// App Boostrap Script
define('SMART_APP_TEMPLATES_DIR', 			'etc/templates/'); 				// App Templates Dir
//--
require('etc/init.php'); 													// the PHP.INI local settings (they must be called first !!!)
require('lib/smart-error-handler.php'); 									// Smart Error Handler
require('lib/smart-runtime.php'); 											// Smart Runtime
require('etc/config-admin.php'); 											// Admin Config
require('lib/run/middleware-admin.php'); 									// Admin Service Handler
//--
//==
//--
if((string)SMART_FRAMEWORK_RELEASE_MIDDLEWARE != '[A]@'.SMART_FRAMEWORK_RELEASE_TAGVERSION) {
	die('SmartFramework // App [A] Service: Middleware service validation Failed ... Invalid Version !');
} //end if
//--
if((string)get_parent_class('SmartAppAdminMiddleware') != 'SmartAbstractAppMiddleware') {
	die('SmartFramework // App [A] Service: the Class SmartAppAdminMiddleware must be extended from the Class SmartAbstractAppMiddleware ...');
} //end if
//--
SmartAppAdminMiddleware::Run(); // Handle the Admin service
//--
//==
//#END
//==

// end of php code
?>