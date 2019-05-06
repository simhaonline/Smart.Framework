<?php
// [Smart.Framework / App - Custom Bootstrap]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// NOTICE: This file can be customized as you need.
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

//-- defines the modules version (required for AppReleaseHash and modules identification)
define('SMART_APP_MODULES_RELEASE', 'm.sf.2019-05-06'); // this can be used for tracking changes to custom app modules
//--

//-- checks the minimum version of the Smart.Framework to run on
define('SMART_APP_MODULES_MIN_FRAMEWORK_VER', 'v.3.7.8.r.2019.05.06'); // this must be used to validate the required minimum framework version
if(version_compare((string)SMART_FRAMEWORK_RELEASE_TAGVERSION.(string)SMART_FRAMEWORK_RELEASE_VERSION, (string)SMART_APP_MODULES_MIN_FRAMEWORK_VER) < 0) {
	@http_response_code(500);
	die('The Custom App Modules require the Smart.Framework '.SMART_APP_MODULES_MIN_FRAMEWORK_VER.' or later !');
} //end if
//--

// # Here can be loaded the Smart.Framework extra or vendor libs package from: https://github.com/unix-world/Smart.Framework.Modules
//require_once('modules/smart-extra-libs/autoload.php'); // autoload for Smart.Framework.Modules / (Smart) Extra Libs
//require_once('modules/vendor/autoload.php'); // autoload for Smart.Framework.Modules / Vendor Libs

// # Here can be loaded extra vendor libs with or without autoloaders
//require_once(__DIR__.'/../../vendor/autoload.php'); // PSR standard namespace/class loader(s), from vendor/ directory, in app root ; if using so, add the following security rule in .htaccess: RewriteRule ^vendor/ - [F,L]
//require_once(__DIR__.'/../../../vendor/autoload.php'); // PSR standard namespace/class loader(s), from vendor/ directory, outside of app root

// # Below is a sample code to handle languages by subdomains (www.dom.ext | ro.dom.ext | de.dom.ext ...): www => en ; ro => ro ; de => de ...
/*
//--
// Note that the default language (en) will be mapped by default to www ; all the rest of available languages like ro, de, ... will be mapped to each subdomain as above
// The language codes must be enabled as needed in etc/config.php prior to be used
//--
if(SMART_FRAMEWORK_ADMIN_AREA !== true) { // Handles the Language Detection by SubDomain (just for index, not for admin)
	AppSetLanguageBySubdomain();
} //end if
//--
function AppSetLanguageBySubdomain() { // r.20190117
	//--
	$sdom = (string) \SmartUtils::get_server_current_domain_name();
	if((string)\SmartValidator::validate_filter_ip_address($sdom) != '') {
		return; // if no domain but only IP, stop
	} //end if
	//--
	$dom = (string) \SmartUtils::get_server_current_basedomain_name();
	if((string)$dom == (string)$sdom) {
		return; // if not using sub-domain of domain, stop
	} //end if
	//--
	$pdom = (string) substr($sdom, 0, (strlen($sdom)-strlen($dom)-1));
	//--
	\SmartTextTranslations::setLanguage((string)\SmartTextTranslations::getDefaultLanguage()); // EN
	if((string)$pdom != 'www') {
		if(\SmartTextTranslations::validateLanguage($pdom)) {
			\SmartTextTranslations::setLanguage($pdom); // set only other languages if valid: RO, DE, ...
		} else {
			http_response_code(301); // permanent redirect if the language code is not valid
			header('Location: '.\SmartUtils::get_server_current_protocol().'www.'.\SmartUtils::get_server_current_basedomain_name()); // force redirect
		} //end if
	} //end if else
	//--
} //END FUNCTION
//--
*/

// end of php code
?>