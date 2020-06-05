<?php
// Smart.Framework / Runtime / Admin
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//##### WARNING: #####
// Changing the code below is on your own risk and may lead to severe disrupts in the execution of this software !
//####################

//== v.20200605
//--
ini_set('display_errors', '1'); 											// temporary enable this to display bootstrap errors if any ; will be managed later by Smart Error Handler
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED); 			// on bootstrap show real-time errors (sync with Smart Error Handler)
//--
if(is_file('maintenance.html')) {
	@http_response_code(503); // 503 maintenance mode
	if(!@readfile('maintenance.html', false)) {
		echo('<h1>503 Service under Maintenance ...</h1>');
	} //end if
	die('<!-- Smart.Framework [A] 503 Maintenance -->');
} //end if
//--
define('SMART_FRAMEWORK_SESSION_PREFIX', 	'adm-sess'); 					// session prefix
define('SMART_FRAMEWORK_ADMIN_AREA', 		true); 							// run app in private/admin mode
define('SMART_FRAMEWORK_RUNTIME_READY', 	microtime(true)); 				// semaphore, runtime can execute scripts
define('SMART_FRAMEWORK_APP_REQUEST', 		'lib/run/app-request.php'); 	// App Request Script
define('SMART_FRAMEWORK_APP_BOOTSTRAP', 	'lib/run/app-bootstrap.php'); 	// App Boostrap Script
define('SMART_APP_TEMPLATES_DIR', 			'etc/templates/'); 				// App Templates Dir
//--
require('etc/init.php'); 													// the PHP.INI local settings (they must be called first !!!)
//--
// Set Locales to Default: C
// WARNING: NEVER CHANGE LOCALES in this framework ; THEY MUST BE 'C' (default) ; you should work with overall C and never mix locales as the results will be unpredictable
// If you ever change locales with other values it may break many things like Example: 3.5 may become become 3,5 or dates may become uncompatible as format in the overall context
// HINTS: if you need to display localised values never use setlocale() but instead write your own formatters to just format the displayed values in Views
setlocale(LC_ALL, 'C'); // DON'T CHANGE THIS !!! THIS IS COMPATIBLE WILL ALL UTF-8 UNICODE CONTEXTS !!!
//--
require('lib/smart-error-handler.php'); 									// Smart Error Handler
require('lib/smart-runtime.php'); 											// Smart Runtime
require('etc/config-admin.php'); 											// Admin Config
require('lib/run/middleware.php'); 											// Service Handler Definition
require('lib/run/middleware-admin.php'); 									// Admin Service Handler
//--
//==
//--
if((string)SMART_FRAMEWORK_RELEASE_MIDDLEWARE != '[A]@'.SMART_FRAMEWORK_RELEASE_TAGVERSION) {
	@http_response_code(500);
	die('Smart.Framework // App [A] Service: Middleware service validation Failed ... Invalid Version !');
} //end if
//--
if((string)get_parent_class('SmartAppAdminMiddleware') != 'SmartAbstractAppMiddleware') {
	@http_response_code(500);
	die('Smart.Framework // App [A] Service: the Class SmartAppAdminMiddleware must be extended from the Class SmartAbstractAppMiddleware ...');
} //end if
//--
$run = SmartAppAdminMiddleware::Run(); // Handle the Admin service
if(SmartFrameworkRuntime::ifDebug()) {
	if($run !== false) {
		SmartAppAdminMiddleware::DebugInfoSet('adm', (bool)$run);
	} //end if
} //end if
//--
if((string)setlocale(LC_ALL, 0) != 'C') { // {{{SYNC-LOCALES-CHECK}}}
	@trigger_error(
		'#SMART-FRAMEWORK-LOCALES-NOTICE#'."\n".
		'Invalid PHP Locales (other than C) detected: ['.setlocale(LC_ALL, 0).'].'."\n".
		'The locale information is maintained per process, not per thread'."\n".
		'Thus if other external PHP scripts changed locales while Smart.Framework was running in the same (server) process this will generate unpredictable results.'."\n".
		'Solution: You should assure to run Smart.Framework in an isolated (server) process context to avoid this issue !'."\n".
		'If you changed the locales using setlocale() in your PHP scripts inside Smart.Framework you should stop doing this as other locales than C are not supported ...',
		E_USER_NOTICE
	);
} //end if
//--
//==
//#END
//==

// end of php code
