<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Webdav/TestCardDAV (CardDAV:FileSystem)
// Route: admin.php/page/webdav.test-carddav/~
// Author: unix-world.org
// v.3.7.5 r.2018.03.09 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // admin area only
define('SMART_APP_MODULE_AUTH', true); // requires auth always
define('SMART_APP_MODULE_DIRECT_OUTPUT', true); // do direct output

/**
 * Admin Controller (direct output)
 */
class SmartAppAdminController extends \SmartModExtLib\Webdav\ControllerAdmCardDavFs {

	// v.180309

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			http_response_code(503);
			echo \SmartComponents::http_message_503_serviceunavailable('ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--
		if(!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_DAVFS_TESTS') OR (SMART_FRAMEWORK_TESTUNIT_ALLOW_DAVFS_TESTS !== true)) {
			http_response_code(503);
			echo \SmartComponents::http_message_503_serviceunavailable('ERROR: CardDAV Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(!defined('SMART_SOFTWARE_URL_ALLOW_PATHINFO') OR ((int)SMART_SOFTWARE_URL_ALLOW_PATHINFO < 1)) {
			http_response_code(500);
			echo \SmartComponents::http_message_500_internalerror('ERROR: CardDAV requires PathInfo to be enabled into init.php for Admin Area ...');
			return;
		} //end if
		//--
		// !!! To SECURE the below folder for PRIVATE access, create a .htaccess in wpub/webapps-content to deny all access to this folder and sub-folders !!!
		$this->DavFsRunServer(
			'wpub/webapps-content/test-carddav',
			true // you may disable this on large webdav file systems to avoid huge calculations
		);
		//--

		//-- HINTS:
		// # CardDAV Folder Structure [test-carddav/]:
		// addressbooks/
		// addressbooks/{user}/
		// addressbooks/{user}/DefaultAddressBook/
		// principals/
		// principals/{user}/
		// # ThunderBird Sogo (connector, extension) Remote AddressBook URL: {http(s)://prefix-and-path}/admin.php/page/webdav.test-carddav/~/addressbooks/admin/DefaultAddressBook/
		// # MacOS Contacts / iOS Contacts URL: {http(s)://prefix-and-path}/admin.php/page/webdav.test-carddav/~/principals/admin/
		//--

	} //END FUNCTION

} //END CLASS

//end of php code
?>